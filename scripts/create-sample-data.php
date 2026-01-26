<?php
/**
 * Create sample galleries and images for development/testing
 */

require_once '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// Sample gallery data
$galleries = [
    [
        'title' => 'Nature Photography',
        'description' => 'Beautiful landscapes and nature scenes from around the world.',
        'images' => [
            ['title' => 'Mountain Sunrise', 'desc' => 'Golden light over mountain peaks'],
            ['title' => 'Forest Path', 'desc' => 'Misty morning in the forest'],
            ['title' => 'Ocean Waves', 'desc' => 'Powerful waves crashing on rocks'],
            ['title' => 'Autumn Leaves', 'desc' => 'Colorful fall foliage'],
        ]
    ],
    [
        'title' => 'Urban Architecture',
        'description' => 'Modern buildings and cityscapes showcasing urban design.',
        'images' => [
            ['title' => 'Glass Tower', 'desc' => 'Modern skyscraper reflection'],
            ['title' => 'City Lights', 'desc' => 'Downtown at night'],
            ['title' => 'Bridge View', 'desc' => 'Iconic bridge at sunset'],
            ['title' => 'Street Corner', 'desc' => 'Historic district charm'],
        ]
    ],
    [
        'title' => 'Wildlife Collection',
        'description' => 'Amazing wildlife photography from various habitats.',
        'images' => [
            ['title' => 'Eagle in Flight', 'desc' => 'Majestic bird soaring'],
            ['title' => 'Deer at Dawn', 'desc' => 'Wildlife in morning mist'],
            ['title' => 'Butterfly Garden', 'desc' => 'Colorful wings on flowers'],
            ['title' => 'Fox Portrait', 'desc' => 'Curious red fox'],
        ]
    ],
    [
        'title' => 'Abstract Art',
        'description' => 'Creative abstract photography and artistic compositions.',
        'images' => [
            ['title' => 'Color Splash', 'desc' => 'Vibrant paint swirls'],
            ['title' => 'Light Trails', 'desc' => 'Long exposure magic'],
            ['title' => 'Geometric Shapes', 'desc' => 'Patterns in architecture'],
            ['title' => 'Water Droplets', 'desc' => 'Macro water art'],
        ]
    ],
];

// Use picsum.photos for real sample images (returns JPG, no processing needed)
$picsum_ids = [
    10, 11, 12, 13, 14, 15, 16, 17,
    20, 21, 22, 23, 24, 25, 26, 27
];

global $wpdb;
$table_gallery = $wpdb->prefix . 'zul_image_gallery';
$table_images = $wpdb->prefix . 'zul_image_gallery_images';
$upload_dir = wp_upload_dir();
$created_pages = [];
$img_counter = 0;

foreach ($galleries as $index => $gallery_data) {
    // Insert gallery
    $wpdb->insert($table_gallery, [
        'title' => $gallery_data['title'],
        'description' => $gallery_data['description'],
        'created_by' => 1,
        'source' => 'WP',
        'status' => 'active',
        'create_dt' => current_time('mysql'),
    ]);
    $gallery_id = $wpdb->insert_id;

    echo "Created gallery: {$gallery_data['title']} (ID: $gallery_id)\n";

    // Create images for this gallery
    foreach ($gallery_data['images'] as $img_index => $img_data) {
        $picsum_id = $picsum_ids[$img_counter % count($picsum_ids)];
        $img_counter++;

        // Use picsum.photos - returns actual JPG images
        $image_url = "https://picsum.photos/id/{$picsum_id}/800/600";

        // Download image
        $response = wp_remote_get($image_url, [
            'timeout' => 30,
            'redirection' => 5,
        ]);

        if (is_wp_error($response)) {
            echo "  Warning: Could not download image for {$img_data['title']}: {$response->get_error_message()}\n";
            continue;
        }

        $image_data = wp_remote_retrieve_body($response);
        if (empty($image_data)) {
            echo "  Warning: Empty response for {$img_data['title']}\n";
            continue;
        }

        // Save to uploads directory
        $filename = sanitize_file_name($img_data['title']) . '.jpg';
        $file_path = $upload_dir['path'] . '/' . $filename;

        // Ensure directory exists
        wp_mkdir_p($upload_dir['path']);

        // Write file
        file_put_contents($file_path, $image_data);

        // Check file type
        $wp_filetype = wp_check_filetype($filename, null);

        // Prepare attachment data
        $attachment = [
            'post_mime_type' => $wp_filetype['type'] ?: 'image/jpeg',
            'post_title'     => $img_data['title'],
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        // Insert attachment
        $attachment_id = wp_insert_attachment($attachment, $file_path);

        if (is_wp_error($attachment_id) || !$attachment_id) {
            echo "  Warning: Could not create attachment for {$img_data['title']}\n";
            @unlink($file_path);
            continue;
        }

        // Generate metadata (thumbnails etc) - may fail without GD but attachment still works
        $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        if (!empty($attach_data)) {
            wp_update_attachment_metadata($attachment_id, $attach_data);
        }

        // Insert gallery image record
        $wpdb->insert($table_images, [
            'gallery_id' => $gallery_id,
            'title' => $img_data['title'],
            'attachment_id' => $attachment_id,
            'attachment_url' => wp_get_attachment_url($attachment_id),
            'description' => $img_data['desc'],
            'created_by' => 1,
            'status' => 'active',
            'create_dt' => current_time('mysql'),
        ]);

        echo "  Added image: {$img_data['title']}\n";
    }

    // Create a page for this gallery
    $page_id = wp_insert_post([
        'post_title' => $gallery_data['title'],
        'post_content' => "<!-- wp:shortcode -->[zul_gallery id=\"{$gallery_id}\"]<!-- /wp:shortcode -->",
        'post_status' => 'publish',
        'post_type' => 'page',
    ]);

    if ($page_id) {
        $created_pages[] = [
            'id' => $page_id,
            'title' => $gallery_data['title'],
            'gallery_id' => $gallery_id,
        ];
    }
}

// Save page info for later output
file_put_contents('/tmp/zul_gallery_pages.json', json_encode($created_pages));
echo "\nSample data created successfully!\n";
