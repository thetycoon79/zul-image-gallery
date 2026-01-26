<?php
/**
 * Gallery admin controller
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Admin\Controllers;

use Zul\Gallery\Capabilities;
use Zul\Gallery\Services\GalleryService;
use Zul\Gallery\Services\GalleryImageService;
use Zul\Gallery\Support\Nonce;
use Zul\Gallery\Support\Validator;

class GalleryController
{
    private GalleryService $galleryService;
    private GalleryImageService $imageService;
    private Nonce $nonce;

    public function __construct(GalleryService $galleryService, GalleryImageService $imageService)
    {
        $this->galleryService = $galleryService;
        $this->imageService = $imageService;
        $this->nonce = new Nonce('zul_gallery_action');
    }

    public function listAction(): void
    {
        if (!Capabilities::userCanViewAdmin()) {
            wp_die(__('You do not have permission to view this page.', 'zul-gallery'));
        }

        // Handle delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            $this->deleteAction();
            return;
        }

        // Handle edit action
        if (isset($_GET['action']) && $_GET['action'] === 'edit') {
            $this->editAction();
            return;
        }

        // Get filters
        $filters = $this->getFiltersFromRequest();
        $page = max(1, absint($_GET['paged'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $galleries = $this->galleryService->list($filters, $perPage, $offset);
        $total = $this->galleryService->count($filters);
        $totalPages = ceil($total / $perPage);

        include ZUL_GALLERY_PLUGIN_DIR . 'includes/Admin/Views/gallery-list.php';
    }

    public function createAction(): void
    {
        if (!Capabilities::userCanManage()) {
            wp_die(__('You do not have permission to create galleries.', 'zul-gallery'));
        }

        $gallery = null;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->handleSave();
        }

        include ZUL_GALLERY_PLUGIN_DIR . 'includes/Admin/Views/gallery-edit.php';
    }

    public function editAction(): void
    {
        if (!Capabilities::userCanManage()) {
            wp_die(__('You do not have permission to edit galleries.', 'zul-gallery'));
        }

        $id = absint($_GET['id'] ?? 0);

        if (!$id) {
            wp_redirect(admin_url('admin.php?page=zul-galleries'));
            exit;
        }

        $gallery = $this->galleryService->findById($id);

        if (!$gallery) {
            wp_die(__('Gallery not found.', 'zul-gallery'));
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->handleSave($id);
            if (empty($errors)) {
                $gallery = $this->galleryService->findById($id);
            }
        }

        $images = $this->imageService->getImagesByGallery($id);

        include ZUL_GALLERY_PLUGIN_DIR . 'includes/Admin/Views/gallery-edit.php';
    }

    public function deleteAction(): void
    {
        if (!Capabilities::userCanManage()) {
            wp_die(__('You do not have permission to delete galleries.', 'zul-gallery'));
        }

        $id = absint($_GET['id'] ?? 0);

        if (!$id) {
            wp_redirect(admin_url('admin.php?page=zul-galleries'));
            exit;
        }

        if (!$this->nonce->verify($_GET['_wpnonce'] ?? '', '_wpnonce')) {
            wp_die(__('Security check failed.', 'zul-gallery'));
        }

        try {
            // Delete associated images first
            $this->imageService->deleteAllByGallery($id);
            $this->galleryService->delete($id);

            $this->addNotice(__('Gallery deleted successfully.', 'zul-gallery'), 'success');
        } catch (\Exception $e) {
            $this->addNotice($e->getMessage(), 'error');
        }

        wp_redirect(admin_url('admin.php?page=zul-galleries'));
        exit;
    }

    private function handleSave(?int $id = null): array
    {
        $errors = [];

        if (!$this->nonce->verify($_POST['_wpnonce'] ?? '', '_wpnonce')) {
            return ['Security check failed.'];
        }

        $data = $this->sanitizeInput($_POST);

        try {
            if ($id) {
                $this->galleryService->update($id, $data);
                $this->addNotice(__('Gallery updated successfully.', 'zul-gallery'), 'success');
            } else {
                $gallery = $this->galleryService->create($data, get_current_user_id());
                wp_redirect(admin_url('admin.php?page=zul-galleries&action=edit&id=' . $gallery->getId()));
                exit;
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        return $errors;
    }

    private function sanitizeInput(array $input): array
    {
        return [
            'title' => Validator::sanitizeText($input['title'] ?? ''),
            'description' => Validator::sanitizeTextarea($input['description'] ?? ''),
            'status' => Validator::sanitizeText($input['status'] ?? 'active'),
            'source' => Validator::sanitizeText($input['source'] ?? 'WP'),
        ];
    }

    private function getFiltersFromRequest(): array
    {
        $filters = [];

        if (!empty($_GET['status'])) {
            $filters['status'] = sanitize_text_field($_GET['status']);
        }

        if (!empty($_GET['source'])) {
            $filters['source'] = sanitize_text_field($_GET['source']);
        }

        if (!empty($_GET['s'])) {
            $filters['search'] = sanitize_text_field($_GET['s']);
        }

        if (!empty($_GET['orderby'])) {
            $filters['orderby'] = sanitize_text_field($_GET['orderby']);
        }

        if (!empty($_GET['order'])) {
            $filters['order'] = sanitize_text_field($_GET['order']);
        }

        return $filters;
    }

    private function addNotice(string $message, string $type = 'success'): void
    {
        add_settings_error(
            'zul_gallery_notices',
            'zul_gallery_notice',
            $message,
            $type
        );
    }

    public function getDeleteUrl(int $id): string
    {
        return wp_nonce_url(
            admin_url('admin.php?page=zul-galleries&action=delete&id=' . $id),
            'zul_gallery_action',
            '_wpnonce'
        );
    }

    public function getEditUrl(int $id): string
    {
        return admin_url('admin.php?page=zul-galleries&action=edit&id=' . $id);
    }
}
