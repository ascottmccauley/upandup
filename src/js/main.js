// TODO:
// Add a preloader .gif


jQuery(document).ready(function () {
	// Image Sizes
	var imageSizes = {
		small: {width: 400, height: 400},
		medium: {width: 600, height: 600},
		large: {width: 900, height: 900},
		xLarge: {width: 1200, height: 900},
		full: {width: 1500, height: 1200}
	};
	
	jQuery.loadImage = function (url) {
		var loadImage = function (deferred) {
			var image = new Image();
			image.onload = loaded;
			image.onerror = errored; // URL returns 404, etc
			image.onabort = errored; // IE may call this if user clicks "Stop"
			
			// Setting the src property begins loading the image.
			image.src = url;

			function loaded() {
				unbindEvents();
				// Calling resolve means the image loaded sucessfully and is ready to use.
				deferred.resolve(image);
			}

			function errored() {
				unbindEvents();
				// Calling reject means we failed to load the image (e.g. 404, server offline, etc).
				deferred.reject(image);
			}

			function unbindEvents() {
				// Ensures the event callbacks only get called once.
				image.onload = null;
				image.onerror = null;
				image.onabort = null;
			}
		};
		// Create the deferred object that will contain the loaded image.
		// We don't want callers to have access to the resolve() and reject() methods,
		// so convert to "read-only" by calling `promise()`.
		return jQuery.Deferred(loadImage).promise();
	};
	
	function replace_src(element, failed) {
		// get source
		var src = element.data('src') || element.attr('data-src');
		// check for active slide instead
		if (!src) {
			element = jQuery('.slide.active', element);
			src = element.data('src') || element.attr('data-src');
		}
		// check if it's already been loaded
		var loaded = element.data('loaded') || element.attr('data-loaded');
		if (src && loaded !== 'true') {
			var imageSize = null;
			failed = failed || [];
			// Change src based on viewport size to get an image barely larger than viewport
			// Do not change src if first try failed to find an image
			var viewport = {};
			viewport.width = jQuery(window).innerWidth() || document.documentElement.clientWidth || document.body.clientWidth;
			viewport.height = jQuery(window).innerHeight() || document.documentElement.clientHeight || document.body.clientHeight;
			for (var size in imageSizes) {
				if (imageSizes[size].width > viewport.width || imageSizes[size].height > viewport.height && jQuery.inArray(size, failed) == -1) {
					imageSize = size;
					src = src.replace('.jpg','-' + size + '.jpg');
					break;
				}
			}
		
			jQuery.loadImage(src)
				.done(function(image) {
					//console.log('replaced: ' + src);
					element.data('loaded', 'true');
					element.attr('data-loaded', 'true');
					if (element.is('img')) {
						element.attr('src', src);
					}else {
						element.css('background-image', 'url(' + src + ')');
					}
					// change background color with "vibrant"
					var vibrant = new Vibrant(image);
					var swatches = vibrant.swatches();
					var color = swatches.DarkMuted.getHex();
					element.data('color', color);
					element.attr('data-color', color);
				})
				.fail(function(image) {
					console.log('failed to load: ' + src);
					if (imageSize) {
						// try again with a different size, or original image
						failed.push(failed, imageSize);
						replace_src(element, failed);
					} else {
						//element.remove();
					}
				});
		}
	}
	
	function changeBackground(element) {
		var color = element.data('color') || element.attr('data-color');
		if (!color) {
			var currentSlide = jQuery('.slide.active', element)
			color = currentSlide.data('color') || currentSlide.attr('data-color');
		}
		if (color) {
			jQuery('body').css('background-color', color);
		}
	}
	
	// Fullpage
	jQuery('#main').fullpage({
		loopHorizontal: false,
		verticalCentered: false,
		afterLoad: function(anchorLink, index) {
			// Fade #footer
			if (jQuery(this).is(':last-child')) {
				jQuery('#footer').css('opacity', 1);
			} else {
				jQuery('#footer').css('opacity', 0);
			}
			
			// Load Current Image and Next Slides
			var slide = jQuery('.slide.active', this);
			if (slide) {
				replace_src(slide);
				replace_src(slide.next());
				slide.css('opacity', 1);
				changeBackground(slide);
			} else {
				replace_src(this);
				changeBackground(this);
			}
			replace_src(this.next());
			
			this.css('opacity', 1);
		},
		afterSlideLoad: function(anchorLink, index, slideAnchor, slideIndex) {
			console.log('afterSlideLoad');
			replace_src(this);
			changeBackground(this);
			replace_src(this.next());
		},
		onLeave: function(index, nextIndex, direction) {
			var nextSlide = this.parent().children().eq(nextIndex - 1);
			this.css('opacity', 0);
			nextSlide.css('opacity', 1);
		},
		onSlideLeave: function(anchorLink, index, slideIndex, direction, nextSlideIndex) {
			var nextSlide = jQuery(this).parent().children().eq(nextSlideIndex);
			this.css('opacity', 0);
			nextSlide.css('opacity', 1);
		},
		afterResize: function() {
			console.log('resized');
			// clear all data('loaded');
			var items = jQuery(this).find('.section', '.slide');
			jQuery.each(items, function(key, item) {
				jQuery.removeData(item, 'loaded');
			});
			var currentSlides = jQuery(this).find('.active');
			jQuery.each(currentSlides, function( key, slide ) {
				console.log(slide);
				replace_src(jQuery(slide));
			});
		}
	});
	
	// Menu
	jQuery('#nav-mobile').mmenu({
		// Options
	});
});
