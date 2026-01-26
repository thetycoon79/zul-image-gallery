<?php
/**
 * Gallery images management partial
 *
 * @package Zul\Gallery
 * @var \Zul\Gallery\Domain\Entities\Gallery $gallery
 * @var array $images
 */

if (!defined('ABSPATH')) {
    exit;
}

use Zul\Gallery\Services\SourceResolver;

$sourceResolver = new SourceResolver();
?>
<div class="postbox" id="zul-gallery-images-box">
    <div class="postbox-header">
        <h2><?php esc_html_e('Gallery Images', 'zul-gallery'); ?></h2>
    </div>
    <div class="inside">
        <div class="zul-gallery-images-toolbar">
            <button type="button" class="button button-primary" id="zul-add-images">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php esc_html_e('Add Images', 'zul-gallery'); ?>
            </button>
            <span class="zul-images-count">
                <?php printf(
                    _n('%d image', '%d images', count($images), 'zul-gallery'),
                    count($images)
                ); ?>
            </span>
        </div>

        <div class="zul-gallery-images-grid" id="zul-gallery-images" data-gallery-id="<?php echo esc_attr($gallery->getId()); ?>">
            <?php if (empty($images)): ?>
                <div class="zul-no-images">
                    <p><?php esc_html_e('No images in this gallery yet.', 'zul-gallery'); ?></p>
                    <p><?php esc_html_e('Click "Add Images" to select images from the Media Library.', 'zul-gallery'); ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($images as $image): ?>
                    <?php
                    $imageData = $sourceResolver->resolveImageData($image, 'medium', 'thumbnail');
                    ?>
                    <div class="zul-gallery-image-item" data-image-id="<?php echo esc_attr($image->getId()); ?>">
                        <div class="zul-image-preview">
                            <img src="<?php echo esc_url($imageData['thumbnail']); ?>"
                                 alt="<?php echo esc_attr($imageData['alt']); ?>">
                        </div>
                        <div class="zul-image-info">
                            <span class="zul-image-title"><?php echo esc_html($image->getTitle() ?: __('Untitled', 'zul-gallery')); ?></span>
                            <span class="zul-image-status zul-status-<?php echo esc_attr($image->getStatus()->value); ?>">
                                <?php echo esc_html($image->getStatus()->label()); ?>
                            </span>
                        </div>
                        <div class="zul-image-actions">
                            <button type="button" class="button button-small zul-edit-image" title="<?php esc_attr_e('Edit', 'zul-gallery'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="button button-small zul-remove-image" title="<?php esc_attr_e('Remove', 'zul-gallery'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Image Modal Template -->
<script type="text/template" id="zul-edit-image-template">
    <div class="zul-modal-content">
        <h2><?php esc_html_e('Edit Image', 'zul-gallery'); ?></h2>
        <div class="zul-form-row">
            <label for="zul-image-title"><?php esc_html_e('Title', 'zul-gallery'); ?></label>
            <input type="text" id="zul-image-title" name="title" value="<%= title %>">
        </div>
        <div class="zul-form-row">
            <label for="zul-image-description"><?php esc_html_e('Description', 'zul-gallery'); ?></label>
            <textarea id="zul-image-description" name="description" rows="3"><%= description %></textarea>
        </div>
        <div class="zul-form-row">
            <label for="zul-image-status"><?php esc_html_e('Status', 'zul-gallery'); ?></label>
            <select id="zul-image-status" name="status">
                <option value="active" <% if (status === 'active') { %>selected<% } %>><?php esc_html_e('Active', 'zul-gallery'); ?></option>
                <option value="inactive" <% if (status === 'inactive') { %>selected<% } %>><?php esc_html_e('Inactive', 'zul-gallery'); ?></option>
            </select>
        </div>
        <div class="zul-modal-actions">
            <button type="button" class="button button-primary zul-save-image"><?php esc_html_e('Save', 'zul-gallery'); ?></button>
            <button type="button" class="button zul-cancel-edit"><?php esc_html_e('Cancel', 'zul-gallery'); ?></button>
        </div>
    </div>
</script>
