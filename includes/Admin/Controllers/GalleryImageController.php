<?php
/**
 * Gallery image AJAX controller
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Admin\Controllers;

use Zul\Gallery\Capabilities;
use Zul\Gallery\Services\GalleryImageService;
use Zul\Gallery\Services\SourceResolver;
use Zul\Gallery\Support\Nonce;
use Zul\Gallery\Support\Validator;

class GalleryImageController
{
    private GalleryImageService $imageService;
    private SourceResolver $sourceResolver;

    public function __construct(GalleryImageService $imageService, SourceResolver $sourceResolver)
    {
        $this->imageService = $imageService;
        $this->sourceResolver = $sourceResolver;
    }

    public function registerAjaxHandlers(): void
    {
        add_action('wp_ajax_zul_gallery_add_images', [$this, 'addImages']);
        add_action('wp_ajax_zul_gallery_remove_image', [$this, 'removeImage']);
        add_action('wp_ajax_zul_gallery_update_image', [$this, 'updateImage']);
        add_action('wp_ajax_zul_gallery_get_images', [$this, 'getImages']);
    }

    public function addImages(): void
    {
        if (!Nonce::verifyAjax('zul_gallery_images', 'nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'zul-gallery')], 403);
        }

        if (!Capabilities::userCanManage()) {
            wp_send_json_error(['message' => __('Unauthorized.', 'zul-gallery')], 403);
        }

        $galleryId = Validator::sanitizeInt($_POST['gallery_id'] ?? 0);
        $attachmentIds = Validator::sanitizeIds($_POST['attachment_ids'] ?? []);

        if (!$galleryId) {
            wp_send_json_error(['message' => __('Gallery ID is required.', 'zul-gallery')], 400);
        }

        if (empty($attachmentIds)) {
            wp_send_json_error(['message' => __('No images selected.', 'zul-gallery')], 400);
        }

        try {
            $images = $this->imageService->addImagesFromAttachments(
                $galleryId,
                $attachmentIds,
                get_current_user_id()
            );

            $imageData = [];
            foreach ($images as $image) {
                $imageData[] = $this->formatImageForResponse($image);
            }

            wp_send_json_success([
                'message' => sprintf(
                    _n('%d image added.', '%d images added.', count($images), 'zul-gallery'),
                    count($images)
                ),
                'images' => $imageData,
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 400);
        }
    }

    public function removeImage(): void
    {
        if (!Nonce::verifyAjax('zul_gallery_images', 'nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'zul-gallery')], 403);
        }

        if (!Capabilities::userCanManage()) {
            wp_send_json_error(['message' => __('Unauthorized.', 'zul-gallery')], 403);
        }

        $imageId = Validator::sanitizeInt($_POST['image_id'] ?? 0);

        if (!$imageId) {
            wp_send_json_error(['message' => __('Image ID is required.', 'zul-gallery')], 400);
        }

        try {
            $this->imageService->deleteImage($imageId);

            wp_send_json_success([
                'message' => __('Image removed.', 'zul-gallery'),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 400);
        }
    }

    public function updateImage(): void
    {
        if (!Nonce::verifyAjax('zul_gallery_images', 'nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'zul-gallery')], 403);
        }

        if (!Capabilities::userCanManage()) {
            wp_send_json_error(['message' => __('Unauthorized.', 'zul-gallery')], 403);
        }

        $imageId = Validator::sanitizeInt($_POST['image_id'] ?? 0);

        if (!$imageId) {
            wp_send_json_error(['message' => __('Image ID is required.', 'zul-gallery')], 400);
        }

        $data = [];

        if (isset($_POST['title'])) {
            $data['title'] = Validator::sanitizeText($_POST['title']);
        }

        if (isset($_POST['description'])) {
            $data['description'] = Validator::sanitizeTextarea($_POST['description']);
        }

        if (isset($_POST['status'])) {
            $data['status'] = Validator::sanitizeText($_POST['status']);
        }

        try {
            $image = $this->imageService->updateImage($imageId, $data);

            wp_send_json_success([
                'message' => __('Image updated.', 'zul-gallery'),
                'image' => $this->formatImageForResponse($image),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 400);
        }
    }

    public function getImages(): void
    {
        if (!Nonce::verifyAjax('zul_gallery_images', 'nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'zul-gallery')], 403);
        }

        if (!Capabilities::userCanViewAdmin()) {
            wp_send_json_error(['message' => __('Unauthorized.', 'zul-gallery')], 403);
        }

        $galleryId = Validator::sanitizeInt($_GET['gallery_id'] ?? 0);

        if (!$galleryId) {
            wp_send_json_error(['message' => __('Gallery ID is required.', 'zul-gallery')], 400);
        }

        try {
            $images = $this->imageService->getImagesByGallery($galleryId);

            $imageData = [];
            foreach ($images as $image) {
                $imageData[] = $this->formatImageForResponse($image);
            }

            wp_send_json_success([
                'images' => $imageData,
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 400);
        }
    }

    private function formatImageForResponse($image): array
    {
        $data = $this->sourceResolver->resolveImageData($image);

        return [
            'id' => $image->getId(),
            'gallery_id' => $image->getGalleryId(),
            'title' => $image->getTitle(),
            'description' => $image->getDescription(),
            'status' => $image->getStatus()->value,
            'url' => $data['url'],
            'thumbnail' => $data['thumbnail'],
            'alt' => $data['alt'],
        ];
    }
}
