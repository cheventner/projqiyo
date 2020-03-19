// Fade module for Slider Pro.
// 
// Adds the possibility to navigate through slides using a cross-fade effect.
;(function( window, $ ) {

	"use strict";

	var NS = 'TransitionEffects.' + $.SliderPro.namespace;

	var TransitionEffects = {

		// Reference to the original 'gotoSlide' method
		originalGotoSlideReference: null,

		initTransitionEffects: function() {
			this.on( 'update.' + NS, $.proxy( this._TransitionEffectsOnUpdate, this ) );
		},

		// If fade is enabled, store a reference to the original 'gotoSlide' method
		// and then assign a new function to 'gotoSlide'.
		_TransitionEffectsOnUpdate: function() {
                    this.originalGotoSlideReference = this.gotoSlide;
                    this.gotoSlide = this._gotoSlide;
		},

		// Will replace the original 'gotoSlide' function by adding a cross-fade effect
		// between the previous and the next slide.
		_gotoSlide: function( index ) {

			if( this.$slider.parent().data( 'isKeySliding' ) || index === this.selectedSlideIndex ) {
				return false;
			}
			
			// If the slides are being swiped/dragged, don't use fade, but call the original method instead.
			// If not, which means that a new slide was selected through a button, arrows or direct call, then
			// use fade.
			if ( this.$slider.hasClass( 'sp-swiping' ) ) {
				if( index === undefined ) {
					index = this.slidesOrder[0];
				}

				this.originalGotoSlideReference( index );
			} else {
				var that = this,
					$nextSlide,
					$previousSlide,
					newIndex = index;

				// Loop through all the slides and overlap the the previous and next slide,
				// and hide the other slides.
				$.each( this.slides, function( index, element ) {
					var slideIndex = element.getIndex(),
						$slide = element.$slide;

					if ( slideIndex === newIndex ) {
						$slide.css({ 'opacity': 0, 'left': 0, 'top': 0, 'z-index': 20, 'visibility': 'visible' });
						$nextSlide = $slide;
					} else if ( slideIndex === that.selectedSlideIndex ) {
						$slide.css({ 'opacity': 1, 'left': 0, 'top': 0, 'z-index': 10 });
						$previousSlide = $slide;
					} else {
						$slide.css( 'visibility', 'hidden' );
					}
				});

				// Set the new indexes for the previous and selected slides
				this.previousSlideIndex = this.selectedSlideIndex;
				this.selectedSlideIndex = index;

				// Re-assign the 'sp-selected' class to the currently selected slide
				this.$slides.find( '.sp-selected' ).removeClass( 'sp-selected' );
				this.$slides.find( '.sp-slide' ).eq( this.selectedSlideIndex ).addClass( 'sp-selected' );
			
				// Rearrange the slides if the slider is loopable
				if ( that.settings.loop === true ) {
					that._updateSlidesOrder();
				}

				// Move the slides container so that the cross-fading slides (which now have the top and left
				// position set to 0) become visible and in the center of the slider.
				this._moveTo( this.visibleOffset, true );
                                
                                var transition = $nextSlide.data( 'transition' ),
                                    duration = $nextSlide.data( 'duration' );
                            
				// Fade out the previous slide, if indicated, in addition to fading in the next slide
				// The previous slide is always faded out
				if ( this.settings.fadeOutPreviousSlide === true ) {
					this._transition_effect( $previousSlide, 'fadeOut', transition );
				}
                                
                                var _transition_callback = function() {
					// Reset the position of the slides and slides container
					that._resetSlidesPosition();

					// Wait before reposition is running
					var waitingTime = parseFloat( $nextSlide.data( 'duration' ) ) * 1000;

					that.$slider.parent().data( 'isKeySliding', true );
                                        
					setTimeout(function() {
						// After the animation is over, make all the slides visible again
						$.each( that.slides, function( index, element ) {
							var $slide = element.$slide;
							$slide.css({ 'visibility': '', 'opacity': '', 'z-index': '', 'transform' : '' });
						});

						// Fire the 'gotoSlideComplete' event
						that.trigger({ type: 'gotoSlideComplete', index: index, previousIndex: that.previousSlideIndex, slider: that.$slider });
						if ( $.isFunction( that.settings.gotoSlideComplete ) ) {
							that.settings.gotoSlideComplete.call( that, { type: 'gotoSlideComplete', index: index, previousIndex: that.previousSlideIndex, slider: that.$slider } );
						}

						/* After the animation is done, recalculate the heights */
						if ( that.settings.autoHeight === true ) {
							that._resizeHeight();
						}

						that.$slider.parent().data( 'isKeySliding', false );
					}, waitingTime );
				};

				/**
				 * This is where magic happens.
				 * Apply the transition effect to next slide
				 */
			
                                this._transition_effect( $nextSlide, transition, duration, _transition_callback );
				if ( this.settings.autoHeight === true ) {
					this._resizeHeight();
				}

				// Fire the 'gotoSlide' event
				this.trigger({ type: 'gotoSlide', index: index, previousIndex: this.previousSlideIndex });
				if ( $.isFunction( this.settings.gotoSlide ) ) {
					this.settings.gotoSlide.call( this, { type: 'gotoSlide', index: index, previousIndex: this.previousSlideIndex });
				}
			}
		},

		// slide effect
		_transition_effect: function( target, effect, duration, callback ) {
			var that = this,
                            sp_mask = target.closest( '.sp-mask' ),
                            initial_css = {}, // CSS properties applied before transition effect
				css = {}; // CSS properties to make the transition to

			if( effect === 'slideTop' ) {
				initial_css = { opacity : 1, top : '-' + sp_mask.height() + 'px' };
				css = { top : 0 };
			} else if( effect === 'slideBottom' ) {
				initial_css = { opacity : 1, top : sp_mask.height() + 'px' };
				css = { top : 0 };
			} else if( effect === 'slideLeft' ) {
				initial_css = { opacity : 1, left : '-' + sp_mask.width() + 'px' };
				css = { left : 0 };
			} else if( effect === 'slideRight' ) {
				initial_css = { opacity : 1, left : sp_mask.width() + 'px' };
				css = { left : 0 };
			} else if( effect === 'slideTopFade' ) {
				initial_css = { top : '-' + sp_mask.height() + 'px' };
				css = { top : 0, opacity : 1 };
			} else if( effect === 'slideBottomFade' ) {
				initial_css = { top : sp_mask.height() + 'px' };
				css = { top : 0, opacity : 1 };
			} else if( effect === 'slideLeftFade' ) {
				initial_css = { left : '-' + sp_mask.width() + 'px' };
				css = { left : 0, opacity : 1 };
			} else if( effect === 'slideRightFade' ) {
				initial_css = { left : sp_mask.width() + 'px' };
				css = { left : 0, opacity : 1 };
			} else if( effect === 'zoomOut' ) {
				initial_css[that.vendorPrefix + 'transform'] = 'scale(2)';
				css[ 'opacity' ] = 1;
				css[ that.vendorPrefix + 'transform' ] = 'scale(1)';
			} else if( effect === 'zoomTop' ) {
				initial_css[ that.vendorPrefix + 'transform' ] = 'scale(2)';
				initial_css[ 'top' ] = '-' + sp_mask.height() + 'px';
				css[ 'opacity' ] = 1;
				css[ that.vendorPrefix + 'transform' ] = 'scale(1)';
				css[ 'top' ] = 0;
			} else if( effect === 'zoomBottom' ) {
				initial_css[ that.vendorPrefix + 'transform' ] = 'scale(2)';
				initial_css[ 'top' ] = sp_mask.height() + 'px';
				css[ 'opacity' ] = 1;
				css[ that.vendorPrefix + 'transform' ] = 'scale(1)';
				css[ 'top' ] = 0;
			} else if( effect === 'zoomLeft' ) {
				initial_css[ that.vendorPrefix + 'transform' ] = 'scale(2)';
				initial_css[ 'left' ] = '-' + sp_mask.width() + 'px';
				css[ 'opacity' ] = 1;
				css[ that.vendorPrefix + 'transform' ] = 'scale(1)';
				css[ 'left' ] = 0;
			} else if( effect === 'zoomTop' ) {
				initial_css[ that.vendorPrefix + 'transform' ] = 'scale(2)';
				initial_css[ 'left' ] = sp_mask.width() + 'px';
				css[ 'opacity' ] = 1;
				css[ that.vendorPrefix + 'transform' ] = 'scale(1)';
				css[ 'left' ] = 0;
			} else if( effect === 'fadeOut' ) {
				initial_css[ 'opacity' ] = 1;
				css[ 'opacity' ] = 0;
			} else { // fadeIn, as fallback
                                css = { opacity : 1 };
			}
                        if(effect!=='fade'){
                            target.css( initial_css );
                        }
			// Use CSS transitions if they are supported. If not, use JavaScript animation.
			if ( this.supportedAnimation === 'css-3d' || this.supportedAnimation === 'css-2d' ) {

				// There needs to be a delay between the moment the opacity is set
				// and the moment the transitions starts.
				setTimeout(function(){
                                        var type = effect==='fade'?'opacity':'all';
					css[ that.vendorPrefix + 'transition' ] = type+' ' + duration + 's';
					target.css( css );
				}, 100 );

				target.on( this.transitionEvent, function( event ) {
					if ( event.target !== event.currentTarget ) {
						return;
					}
					target.off( that.transitionEvent ).css( that.vendorPrefix + 'transition', '' );

					if ( typeof callback === 'function' ) {
						callback();
					}
				});
			} else {
				target.stop().animate( css, duration, function() {
					if ( typeof callback === 'function' ) {
						callback();
					}
				});
			}
		},
		// Destroy the module
		destroyTransitionEffects: function() {
			this.off( 'update.' + NS );

			if ( this.originalGotoSlideReference !== null ) {
				this.gotoSlide = this.originalGotoSlideReference;
			}
		}
	};

	$.SliderPro.addModule( 'TransitionEffects', TransitionEffects );

})( window, jQuery );


// Custom module to handle the resizing of the slider
;(function( window, $ ) {

	"use strict";
	
	var NS = 'Resizer.' + $.SliderPro.namespace;

	var Resizer = {

		original_width : '',
		
		original_height : '',

		initResizer: function() {
			this.original_width = this.$slider.closest( '.module-pro-slider' ).data( 'slider-width' );
			this.original_height = this.$slider.closest( '.module-pro-slider' ).data( 'slider-height' );

			if( this.settings.autoHeightOnReize == true ) {
				$( window ).on( 'resize.' + this.uniqueId + '.' + NS, $.proxy( this.resizerEvent, this ) );
				this.on( 'gotoSlideComplete.' + NS, $.proxy( function(){
					
				}, this ) );
			}
		},

		resizerEvent: function( event ) {
			var current_width = this.$slidesContainer.width();
			var new_height;

			/* default slider ratio */
			var height_ratio = 1.9;
			if( typeof builderSliderPro.height_ratio !== 'undefined' ) {
				height_ratio = parseFloat( builderSliderPro.height_ratio );
			}

			/* if width and height are set, use that to calculate the new height ratio */
			if( this.original_width != '' && this.original_height != '' ) {
				height_ratio = this.original_width / this.original_height;
			}
			/* if only height is set, use that to calculate the new height ratio */
			else if( this.original_height != '' ) {
				height_ratio = current_width / this.original_height;
			}

			/* calculate new height based on the width */
			new_height = current_width / height_ratio;
			this.settings.height = new_height;

			this.resize();
		},

		// Destroy the module
		destroyResizer: function() {
			$( window ).off( 'resize.' + this.uniqueId + '.' + NS, $.proxy( this.resizerEvent, this ) );
		},

		ResizerDefaults: {
			autoHeightOnReize : false
		}
	};

	$.SliderPro.addModule( 'Resizer', Resizer );
	
})(window, jQuery);

// handle the display of videos in slides
;(function( window, $ ) {

	"use strict";
	
	var NS = 'BSP_Video.' + $.SliderPro.namespace;

	var BSP_Video = {

		initBSP_Video: function() {
			this.on( 'init.' + NS, $.proxy( this.video_handler, this ) );
			this.on( 'gotoSlideComplete.' + NS, $.proxy( this.video_handler, this ) );
		},

		video_handler: function( event ) {
			this.$slides.find( '.bsp-video-iframe' ).attr( 'src', '' );
			var $selected = this.$slider.find( '.sp-selected' );
			$selected.find( '.bsp-video-iframe' ).attr( 'src', $selected.find( '.bsp-video' ).attr( 'data-src' ) );
		},

		// Destroy the module
		destroyBSP_Video: function() {
			$( window ).off( 'gotoSlideComplete.' + this.uniqueId + '.' + NS, $.proxy( this.video_handler, this ) );
		},

		BSP_VideoDefaults: {}
	};

	$.SliderPro.addModule( 'BSP_Video', BSP_Video );
	
})(window, jQuery);

// fixes blank frame after 2 slides slider
;(function( window, $ ) {

	"use strict";

	var NS = 'TwoSlidesFixer.' + $.SliderPro.namespace;

	var TwoSlidesFixer = {

		initTwoSlidesFixer: function() {
			this.on( 'update.' + NS, $.proxy( this._onUpdate, this ) );
		},


		_fixerGotoSlide: function( index ) {

			// Dont do anything if number of slides are not 2 or its not touch device
			if ( !this.$slider.hasClass( 'sp-swiping' ) || this.$slider.find('.sp-slide').length !== 2 ) {
				if (index === undefined) {
					index = this.slidesOrder[0];
				}

				this.twoSlidesFixerOrigGotoSlide( index );
				return;
			}

			var that = this,
				origUpdateSlidesOrder = this._updateSlidesOrder,
			    origUpdateSlidesPosition = this._updateSlidesPosition;

			// Assign a no-op
			this._updateSlidesOrder = this._updateSlidesPosition = function(){};
			//Call goto function
			this.twoSlidesFixerOrigGotoSlide( index );
			// Revert original functions
			this._updateSlidesOrder = origUpdateSlidesOrder;
			this._updateSlidesPosition = origUpdateSlidesPosition;

			setTimeout(function () {
				that._updateSlidesOrder();
				that._updateSlidesPosition();
			}, this.settings.slideAnimationDuration)
		},

		_onUpdate: function() {
			this.twoSlidesFixerOrigGotoSlide = this.gotoSlide;
			this.gotoSlide = this._fixerGotoSlide;
		},

		// Destroy the module
		destroyTwoSlidesFixer: function() {
			this.off( 'update.' + NS );
		},

		TwoSlidesFixerDefaults: {}
	};

	$.SliderPro.addModule( 'TwoSlidesFixer', TwoSlidesFixer );

})(window, jQuery);