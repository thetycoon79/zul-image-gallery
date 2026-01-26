<?php
/**
 * Fancybox gallery renderer
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Renderers;

use Zul\Gallery\Interfaces\GalleryRendererInterface;
use Zul\Gallery\Domain\Entities\Gallery;

class FancyboxGalleryRenderer implements GalleryRendererInterface
{
    public function getId(): string
    {
        return 'fancybox';
    }

    public function render(Gallery $gallery, array $images, array $options = []): string
    {
        if (empty($images)) {
            return '';
        }

        $columns = absint($options['columns'] ?? 3);
        $columns = max(1, min(6, $columns)); // Clamp between 1-6
        $cssClass = esc_attr($options['class'] ?? '');
        $galleryId = esc_attr('zul-gallery-' . $gallery->getId());
        $showCaptions = $options['show_captions'] ?? true;

        ob_start();
        ?>
        <div id="<?php echo $galleryId; ?>"
             class="zul-gallery zul-gallery-columns-<?php echo $columns; ?> <?php echo $cssClass; ?>"
             data-gallery-id="<?php echo esc_attr($gallery->getId()); ?>">
            <?php foreach ($images as $imageData): ?>
                <?php $this->renderImage($imageData, $galleryId, $showCaptions); ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderImage(array $imageData, string $galleryId, bool $showCaptions): void
    {
        $image = $imageData['image'];
        $url = esc_url($imageData['url'] ?? '');
        $thumbnail = esc_url($imageData['thumbnail'] ?? $url);
        $alt = esc_attr($imageData['alt'] ?? '');
        $caption = $image->getDescription() ?? '';
        $title = $image->getTitle() ?? '';
        ?>
        <div class="zul-gallery-item">
            <a href="<?php echo $url; ?>"
               data-fancybox="<?php echo $galleryId; ?>"
               data-caption="<?php echo esc_attr($caption); ?>"
               class="zul-gallery-link">
                <img src="<?php echo $thumbnail; ?>"
                     alt="<?php echo $alt; ?>"
                     class="zul-gallery-image"
                     loading="lazy" />
            </a>
            <?php if ($showCaptions && $title): ?>
                <span class="zul-gallery-caption">
                    <?php echo esc_html($title); ?>
                </span>
            <?php endif; ?>
        </div>
        <?php
    }

    public function getRequiredAssets(): array
    {
        return [
            'styles' => ['fancybox', 'zul-gallery-frontend'],
            'scripts' => ['fancybox', 'zul-gallery-frontend'],
        ];
    }
}
