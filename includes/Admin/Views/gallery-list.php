<?php
/**
 * Gallery list view
 *
 * @package Zul\Gallery
 * @var array $galleries
 * @var int $total
 * @var int $totalPages
 * @var int $page
 * @var array $filters
 */

if (!defined('ABSPATH')) {
    exit;
}

use Zul\Gallery\Capabilities;

$canManage = Capabilities::userCanManage();
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('ZUL Image Galleries', 'zul-gallery'); ?></h1>

    <?php if ($canManage): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=zul-gallery-new')); ?>" class="page-title-action">
            <?php esc_html_e('Add New', 'zul-gallery'); ?>
        </a>
    <?php endif; ?>

    <hr class="wp-header-end">

    <?php settings_errors('zul_gallery_notices'); ?>

    <!-- Filters -->
    <form method="get" class="zul-gallery-filters">
        <input type="hidden" name="page" value="zul-galleries">

        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="status">
                    <option value=""><?php esc_html_e('All Statuses', 'zul-gallery'); ?></option>
                    <option value="active" <?php selected($filters['status'] ?? '', 'active'); ?>><?php esc_html_e('Active', 'zul-gallery'); ?></option>
                    <option value="inactive" <?php selected($filters['status'] ?? '', 'inactive'); ?>><?php esc_html_e('Inactive', 'zul-gallery'); ?></option>
                    <option value="draft" <?php selected($filters['status'] ?? '', 'draft'); ?>><?php esc_html_e('Draft', 'zul-gallery'); ?></option>
                </select>

                <select name="source">
                    <option value=""><?php esc_html_e('All Sources', 'zul-gallery'); ?></option>
                    <option value="WP" <?php selected($filters['source'] ?? '', 'WP'); ?>><?php esc_html_e('WordPress', 'zul-gallery'); ?></option>
                    <option value="External" <?php selected($filters['source'] ?? '', 'External'); ?>><?php esc_html_e('External', 'zul-gallery'); ?></option>
                </select>

                <?php submit_button(__('Filter', 'zul-gallery'), 'secondary', 'filter_action', false); ?>
            </div>

            <div class="alignright">
                <p class="search-box">
                    <input type="search" name="s" value="<?php echo esc_attr($filters['search'] ?? ''); ?>" placeholder="<?php esc_attr_e('Search galleries...', 'zul-gallery'); ?>">
                    <?php submit_button(__('Search', 'zul-gallery'), 'secondary', '', false); ?>
                </p>
            </div>
        </div>
    </form>

    <!-- Gallery Table -->
    <table class="wp-list-table widefat fixed striped zul-gallery-table">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e('Title', 'zul-gallery'); ?></th>
                <th scope="col" class="manage-column column-status"><?php esc_html_e('Status', 'zul-gallery'); ?></th>
                <th scope="col" class="manage-column column-source"><?php esc_html_e('Source', 'zul-gallery'); ?></th>
                <th scope="col" class="manage-column column-shortcode"><?php esc_html_e('Shortcode', 'zul-gallery'); ?></th>
                <th scope="col" class="manage-column column-author"><?php esc_html_e('Author', 'zul-gallery'); ?></th>
                <th scope="col" class="manage-column column-date"><?php esc_html_e('Date', 'zul-gallery'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($galleries)): ?>
                <tr>
                    <td colspan="6"><?php esc_html_e('No galleries found.', 'zul-gallery'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($galleries as $gallery): ?>
                    <tr>
                        <td class="column-title column-primary" data-colname="<?php esc_attr_e('Title', 'zul-gallery'); ?>">
                            <strong>
                                <?php if ($canManage): ?>
                                    <a href="<?php echo esc_url($this->getEditUrl($gallery->getId())); ?>" class="row-title">
                                        <?php echo esc_html($gallery->getTitle()); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo esc_html($gallery->getTitle()); ?>
                                <?php endif; ?>
                            </strong>

                            <?php if ($canManage): ?>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="<?php echo esc_url($this->getEditUrl($gallery->getId())); ?>">
                                            <?php esc_html_e('Edit', 'zul-gallery'); ?>
                                        </a> |
                                    </span>
                                    <span class="trash">
                                        <a href="<?php echo esc_url($this->getDeleteUrl($gallery->getId())); ?>" class="submitdelete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this gallery?', 'zul-gallery'); ?>');">
                                            <?php esc_html_e('Delete', 'zul-gallery'); ?>
                                        </a>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="column-status" data-colname="<?php esc_attr_e('Status', 'zul-gallery'); ?>">
                            <span class="zul-status zul-status-<?php echo esc_attr($gallery->getStatus()->value); ?>">
                                <?php echo esc_html($gallery->getStatus()->label()); ?>
                            </span>
                        </td>
                        <td class="column-source" data-colname="<?php esc_attr_e('Source', 'zul-gallery'); ?>">
                            <?php echo esc_html($gallery->getSource()->label()); ?>
                        </td>
                        <td class="column-shortcode" data-colname="<?php esc_attr_e('Shortcode', 'zul-gallery'); ?>">
                            <code>[zul_gallery id="<?php echo esc_attr($gallery->getId()); ?>"]</code>
                        </td>
                        <td class="column-author" data-colname="<?php esc_attr_e('Author', 'zul-gallery'); ?>">
                            <?php
                            $author = get_user_by('id', $gallery->getCreatedBy());
                            echo $author ? esc_html($author->display_name) : __('Unknown', 'zul-gallery');
                            ?>
                        </td>
                        <td class="column-date" data-colname="<?php esc_attr_e('Date', 'zul-gallery'); ?>">
                            <?php echo esc_html($gallery->getCreateDt()->format('Y/m/d')); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php printf(
                        _n('%s item', '%s items', $total, 'zul-gallery'),
                        number_format_i18n($total)
                    ); ?>
                </span>
                <span class="pagination-links">
                    <?php
                    echo paginate_links([
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $totalPages,
                        'current' => $page,
                    ]);
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>
