(function ($) {
    "use strict";
    Themify.body.on('click', '.bsp-slide-button', function (e) {
        var $this = $(this);

        if ($this.is('[href="#next-slide"], [href="#prev-slide"]')) {
            e.preventDefault();

            var slider = $this.closest('.slider-pro').data('sliderPro'),
                    action = $this.is('[href="#next-slide"]') ? 'nextSlide' : 'previousSlide';

            typeof slider === 'object' && slider[action].call(slider);
        }

    });
    var isWorking = true,
            do_pro_slider = function (el) {
                var items = $('.module-pro-slider', el);

                if (el && el[0].classList.contains('module-pro-slider')) {
                    items = items.add(el);
                }
                var callback = function () {

                    var call_slider = function ($this, config) {
                        $this.find('.slider-pro')
                                .sliderPro(config)
                                .css('visibility', 'visible')
                                .trigger('resize');
                    };

                    items.each(function () {
                        var $this = $(this),
                                config = {
                                    buttons: !$this.hasClass('pager-none') && !$this.hasClass('pager-type-thumb'),
                                    arrows: true,
                                    responsive: true,
                                    thumbnailWidth: $this.data('thumbnail-width'),
                                    thumbnailHeight: $this.data('thumbnail-height'),
                                    width: $this.data('slider-width') !== '' ? $this.data('slider-width') !== '100%' ? parseInt($this.data('slider-width')) : $this.data('slider-width') : '100%', // set default slider width to 100%
                                    // forceSize : 'fullWidth', // force the slider to span the width of the page
                                    height: $this.data('slider-height') !== '' ? $this.data('slider-height') !== '100vh' ? parseInt($this.data('slider-height')) : $this.data('slider-height') : 400, // set default slider height to 400
                                    fadeOutPreviousSlide: false,
                                    touchSwipe: ( Themify.isTouch && $this.data( 'touch-swipe-mobile' ) === 'yes' ) || ( ! Themify.isTouch && $this.data( 'touch-swipe-desktop' ) === 'yes' ),
                                    init: function () {
                                        $this.find('.tb_slider_loader').remove();
                                    }
                                };

                        /* custom autoplay module settings */
                        config.autoHeightOnReize = true;
                        config.autoplay = $this.data('autoplay') !== 'off';
                        config.autoplayDelay = $this.data('autoplay');
                        config.timer_bar = $this.data('timer-bar') === 'yes';
                        config.autoplayOnHover = $this.data('hover-pause');

                        if ($this.data('timer-bar') === 'yes') {
                            $('.slider-pro', $this).prepend('<div class="bsp-timer-bar" />');
                        }

                        /* do not edit these configurations */
                        config.autoHeight = false;
                        config.thumbnailTouchSwipe = true; // this is required for the thumbnail click action to work

						$this.imagesLoaded( function() {
							call_slider($this, config);
						} );
                    });
                    isWorking = null;
                };
                if (items.length > 0) {
                    Themify.LoadAsync(themify_vars.includesURL + 'js/imagesloaded.min.js', function () {
						Themify.LoadAsync(builderSliderPro.url + 'jquery.sliderPro.js', function () {
							Themify.LoadAsync(builderSliderPro.url + 'sliderPro.helpers.js', callback, '1.2.1', null, function () {
								return $.inArray('TransitionEffects', $.SliderPro.modules) !== -1;
							});
						}, '1.2.1', null, function () {
							return 'undefined' !== typeof $.fn.sliderPro;
						});
					}, null, null, function() {
						return typeof $.fn.imagesLoaded !== 'undefined';
					} );
                } else {
                    isWorking = null;
                }
            };

    if (Themify.is_builder_active) {
        Themify.body.on('tb_module_sort tb_grid_changed', function(e,el){
            do_pro_slider(el);
        });
        if (Themify.is_builder_loaded) {
            do_pro_slider();
        }
        else {
            isWorking = null;
        }
    } else {
        do_pro_slider();
    }
    Themify.body.on('builder_load_module_partial', function (e, el, type) {
        if (isWorking === null) {
            do_pro_slider(el);
        }
    });
}(jQuery));