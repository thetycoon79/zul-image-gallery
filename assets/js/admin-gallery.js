/**
 * ZUL Gallery Admin JavaScript
 */
(function($, wp) {
    'use strict';

    var ZulGalleryAdmin = {
        config: window.zulGalleryAdmin || {},
        frame: null,
        currentImageId: null,

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Add images button
            $(document).on('click', '#zul-add-images', this.openMediaFrame.bind(this));

            // Remove image button
            $(document).on('click', '.zul-remove-image', this.removeImage.bind(this));

            // Edit image button
            $(document).on('click', '.zul-edit-image', this.openEditModal.bind(this));

            // Modal events
            $(document).on('click', '.zul-save-image', this.saveImage.bind(this));
            $(document).on('click', '.zul-cancel-edit, .zul-modal-overlay', this.closeModal.bind(this));
            $(document).on('click', '.zul-modal-content', function(e) {
                e.stopPropagation();
            });

            // Copy shortcode
            $(document).on('click', '.zul-copy-shortcode', this.copyShortcode.bind(this));
        },

        openMediaFrame: function(e) {
            e.preventDefault();

            var self = this;
            var galleryId = $('#zul-gallery-images').data('gallery-id');

            if (!galleryId) {
                alert('Please save the gallery first.');
                return;
            }

            // Create media frame if it doesn't exist
            if (!this.frame) {
                this.frame = wp.media({
                    title: this.config.i18n.selectImages,
                    button: {
                        text: this.config.i18n.addToGallery
                    },
                    library: {
                        type: 'image'
                    },
                    multiple: true
                });

                // Handle selection
                this.frame.on('select', function() {
                    var attachments = self.frame.state().get('selection').toJSON();
                    var attachmentIds = attachments.map(function(att) {
                        return att.id;
                    });

                    self.addImages(galleryId, attachmentIds);
                });
            }

            this.frame.open();
        },

        addImages: function(galleryId, attachmentIds) {
            var self = this;
            var $container = $('#zul-gallery-images');

            $container.addClass('zul-loading');

            $.ajax({
                url: this.config.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'zul_gallery_add_images',
                    nonce: this.config.nonce,
                    gallery_id: galleryId,
                    attachment_ids: attachmentIds
                },
                success: function(response) {
                    if (response.success) {
                        // Remove "no images" message
                        $container.find('.zul-no-images').remove();

                        // Add new images
                        response.data.images.forEach(function(image) {
                            $container.append(self.renderImageItem(image));
                        });

                        // Update count
                        self.updateImageCount();
                    } else {
                        alert(response.data.message || self.config.i18n.error);
                    }
                },
                error: function() {
                    alert(self.config.i18n.error);
                },
                complete: function() {
                    $container.removeClass('zul-loading');
                }
            });
        },

        removeImage: function(e) {
            e.preventDefault();

            if (!confirm(this.config.i18n.confirmRemove)) {
                return;
            }

            var self = this;
            var $item = $(e.currentTarget).closest('.zul-gallery-image-item');
            var imageId = $item.data('image-id');

            $item.addClass('zul-loading');

            $.ajax({
                url: this.config.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'zul_gallery_remove_image',
                    nonce: this.config.nonce,
                    image_id: imageId
                },
                success: function(response) {
                    if (response.success) {
                        $item.fadeOut(300, function() {
                            $(this).remove();
                            self.updateImageCount();

                            // Show "no images" if empty
                            if ($('#zul-gallery-images .zul-gallery-image-item').length === 0) {
                                $('#zul-gallery-images').html(
                                    '<div class="zul-no-images">' +
                                    '<p>No images in this gallery yet.</p>' +
                                    '<p>Click "Add Images" to select images from the Media Library.</p>' +
                                    '</div>'
                                );
                            }
                        });
                    } else {
                        alert(response.data.message || self.config.i18n.error);
                        $item.removeClass('zul-loading');
                    }
                },
                error: function() {
                    alert(self.config.i18n.error);
                    $item.removeClass('zul-loading');
                }
            });
        },

        openEditModal: function(e) {
            e.preventDefault();

            var $item = $(e.currentTarget).closest('.zul-gallery-image-item');
            this.currentImageId = $item.data('image-id');

            var imageData = {
                title: $item.find('.zul-image-title').text().trim(),
                description: '',
                status: $item.find('.zul-image-status').hasClass('zul-status-active') ? 'active' : 'inactive'
            };

            if (imageData.title === 'Untitled') {
                imageData.title = '';
            }

            var template = _.template($('#zul-edit-image-template').html());
            var $modal = $('<div class="zul-modal-overlay"></div>').html(template(imageData));

            $('body').append($modal);
        },

        closeModal: function(e) {
            if (e) {
                e.preventDefault();
            }
            $('.zul-modal-overlay').remove();
            this.currentImageId = null;
        },

        saveImage: function(e) {
            e.preventDefault();

            var self = this;
            var $modal = $('.zul-modal-overlay');
            var $button = $modal.find('.zul-save-image');

            var data = {
                action: 'zul_gallery_update_image',
                nonce: this.config.nonce,
                image_id: this.currentImageId,
                title: $modal.find('#zul-image-title').val(),
                description: $modal.find('#zul-image-description').val(),
                status: $modal.find('#zul-image-status').val()
            };

            $button.prop('disabled', true).text(this.config.i18n.saving);

            $.ajax({
                url: this.config.ajaxUrl,
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        var $item = $('.zul-gallery-image-item[data-image-id="' + self.currentImageId + '"]');
                        var image = response.data.image;

                        $item.find('.zul-image-title').text(image.title || 'Untitled');
                        $item.find('.zul-image-status')
                            .removeClass('zul-status-active zul-status-inactive')
                            .addClass('zul-status-' + image.status)
                            .text(image.status.charAt(0).toUpperCase() + image.status.slice(1));

                        self.closeModal();
                    } else {
                        alert(response.data.message || self.config.i18n.error);
                        $button.prop('disabled', false).text(self.config.i18n.saved);
                    }
                },
                error: function() {
                    alert(self.config.i18n.error);
                    $button.prop('disabled', false).text('Save');
                }
            });
        },

        updateImageCount: function() {
            var count = $('#zul-gallery-images .zul-gallery-image-item').length;
            var text = count === 1 ? count + ' image' : count + ' images';
            $('.zul-images-count').text(text);
        },

        copyShortcode: function(e) {
            e.preventDefault();

            var $code = $($(e.currentTarget).data('clipboard-target'));
            var text = $code.text();

            navigator.clipboard.writeText(text).then(function() {
                var $btn = $(e.currentTarget);
                var originalText = $btn.text();
                $btn.text('Copied!');
                setTimeout(function() {
                    $btn.text(originalText);
                }, 2000);
            });
        },

        renderImageItem: function(image) {
            return '<div class="zul-gallery-image-item" data-image-id="' + image.id + '">' +
                '<div class="zul-image-preview">' +
                '<img src="' + image.thumbnail + '" alt="' + (image.alt || '') + '">' +
                '</div>' +
                '<div class="zul-image-info">' +
                '<span class="zul-image-title">' + (image.title || 'Untitled') + '</span>' +
                '<span class="zul-image-status zul-status-' + image.status + '">' +
                image.status.charAt(0).toUpperCase() + image.status.slice(1) +
                '</span>' +
                '</div>' +
                '<div class="zul-image-actions">' +
                '<button type="button" class="button button-small zul-edit-image" title="Edit">' +
                '<span class="dashicons dashicons-edit"></span>' +
                '</button>' +
                '<button type="button" class="button button-small zul-remove-image" title="Remove">' +
                '<span class="dashicons dashicons-trash"></span>' +
                '</button>' +
                '</div>' +
                '</div>';
        }
    };

    $(document).ready(function() {
        ZulGalleryAdmin.init();
    });

})(jQuery, wp);
