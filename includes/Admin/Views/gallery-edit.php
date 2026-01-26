<?php
/**
 * Gallery edit/create view
 *
 * @package Zul\Gallery
 * @var \Zul\Gallery\Domain\Entities\Gallery|null $gallery
 * @var array $errors
 * @var array $images
 */

if (!defined('ABSPATH')) {
    exit;
}

use Zul\Gallery\Domain\ValueObjects\Status;
use Zul\Gallery\Domain\ValueObjects\GallerySource;
use Zul\Gallery\Support\Nonce;

$isEdit = $gallery !== null;
$nonce = new Nonce('zul_gallery_action');
$pageTitle = $isEdit ? __('Edit Gallery', 'zul-gallery') : __('Add New Gallery', 'zul-gallery');
?>
<div class="wrap">
    <h1><?php echo esc_html($pageTitle); ?></h1>

    <?php settings_errors('zul_gallery_notices'); ?>

    <?php if (!empty($errors)): ?>
        <div class="notice notice-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo esc_html($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" id="zul-gallery-form">
        <?php echo $nonce->field(); ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <!-- Main Content -->
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <label class="screen-reader-text" for="title"><?php esc_html_e('Gallery Title', 'zul-gallery'); ?></label>
                            <input type="text" name="title" id="title" size="30"
                                   value="<?php echo esc_attr($gallery?->getTitle() ?? ($_POST['title'] ?? '')); ?>"
                                   placeholder="<?php esc_attr_e('Enter gallery title here', 'zul-gallery'); ?>"
                                   autocomplete="off" required>
                        </div>
                    </div>

                    <div class="postbox">
                        <div class="postbox-header">
                            <h2><?php esc_html_e('Description', 'zul-gallery'); ?></h2>
                        </div>
                        <div class="inside">
                            <textarea name="description" id="description" rows="5" class="large-text"><?php
                                echo esc_textarea($gallery?->getDescription() ?? ($_POST['description'] ?? ''));
                            ?></textarea>
                        </div>
                    </div>

                    <?php if ($isEdit): ?>
                        <?php include ZUL_GALLERY_PLUGIN_DIR . 'includes/Admin/Views/partials/gallery-images.php'; ?>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div id="postbox-container-1" class="postbox-container">
                    <!-- Publish Box -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2><?php esc_html_e('Publish', 'zul-gallery'); ?></h2>
                        </div>
                        <div class="inside">
                            <div class="submitbox">
                                <div id="minor-publishing">
                                    <div class="misc-pub-section">
                                        <label for="status"><?php esc_html_e('Status:', 'zul-gallery'); ?></label>
                                        <select name="status" id="status">
                                            <?php foreach (Status::cases() as $status): ?>
                                                <option value="<?php echo esc_attr($status->value); ?>"
                                                    <?php selected($gallery?->getStatus()->value ?? 'active', $status->value); ?>>
                                                    <?php echo esc_html($status->label()); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="misc-pub-section">
                                        <label for="source"><?php esc_html_e('Source:', 'zul-gallery'); ?></label>
                                        <select name="source" id="source">
                                            <?php foreach (GallerySource::cases() as $source): ?>
                                                <option value="<?php echo esc_attr($source->value); ?>"
                                                    <?php selected($gallery?->getSource()->value ?? 'WP', $source->value); ?>>
                                                    <?php echo esc_html($source->label()); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <?php if ($isEdit): ?>
                                        <div class="misc-pub-section">
                                            <span><?php esc_html_e('Shortcode:', 'zul-gallery'); ?></span>
                                            <code id="gallery-shortcode">[zul_gallery id="<?php echo esc_attr($gallery->getId()); ?>"]</code>
                                            <button type="button" class="button button-small zul-copy-shortcode" data-clipboard-target="#gallery-shortcode">
                                                <?php esc_html_e('Copy', 'zul-gallery'); ?>
                                            </button>
                                        </div>

                                        <div class="misc-pub-section">
                                            <span><?php esc_html_e('Created:', 'zul-gallery'); ?></span>
                                            <?php echo esc_html($gallery->getCreateDt()->format('Y-m-d H:i')); ?>
                                        </div>

                                        <?php if ($gallery->getModifiedDt()): ?>
                                            <div class="misc-pub-section">
                                                <span><?php esc_html_e('Modified:', 'zul-gallery'); ?></span>
                                                <?php echo esc_html($gallery->getModifiedDt()->format('Y-m-d H:i')); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <div id="major-publishing-actions">
                                    <?php if ($isEdit): ?>
                                        <div id="delete-action">
                                            <a href="<?php echo esc_url($this->getDeleteUrl($gallery->getId())); ?>"
                                               class="submitdelete deletion"
                                               onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this gallery?', 'zul-gallery'); ?>');">
                                                <?php esc_html_e('Delete', 'zul-gallery'); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div id="publishing-action">
                                        <input type="submit" name="submit" class="button button-primary button-large"
                                               value="<?php echo $isEdit ? esc_attr__('Update', 'zul-gallery') : esc_attr__('Publish', 'zul-gallery'); ?>">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
