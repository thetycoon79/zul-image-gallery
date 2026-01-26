/**
 * ZUL Gallery Frontend JavaScript
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Fancybox for all galleries
        if (typeof Fancybox !== 'undefined') {
            Fancybox.bind('[data-fancybox]', {
                // Gallery grouping is handled by data-fancybox attribute
                Thumbs: {
                    type: 'classic'
                },
                Toolbar: {
                    display: {
                        left: ['infobar'],
                        middle: [],
                        right: ['slideshow', 'fullscreen', 'thumbs', 'close']
                    }
                },
                Images: {
                    zoom: true
                },
                Hash: false,
                // Show caption below image
                caption: function(fancybox, slide) {
                    var caption = slide.triggerEl?.dataset?.caption || '';
                    return caption;
                }
            });
        }

        // Add loading state when image is clicked
        document.querySelectorAll('.zul-gallery-link').forEach(function(link) {
            link.addEventListener('click', function() {
                var gallery = this.closest('.zul-gallery');
                if (gallery) {
                    gallery.classList.add('zul-loading');
                    // Remove loading state after Fancybox opens
                    setTimeout(function() {
                        gallery.classList.remove('zul-loading');
                    }, 500);
                }
            });
        });
    });
})();
