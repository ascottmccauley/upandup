// App.js
// IE8 ployfill for GetComputed Style (for Responsive Script below)
if (!window.getComputedStyle) {
    window.getComputedStyle = function(el, pseudo) {
        this.el = el;
        this.getPropertyValue = function(prop) {
            var re = /(\-([a-z]){1})/g;
            if (prop === 'float') {
            	prop = 'styleFloat';
            }
            if (re.test(prop)) {
                prop = prop.replace(re, function () {
                    return arguments[2].toUpperCase();
                });
            }
            return el.currentStyle[prop] ? el.currentStyle[prop] : null;
        };
        return this;
    };
}

//IE8 polyfill for requestAnimationFrame
(function() {
  var lastTime = 0;
  var vendors = ['webkit', 'moz'];
  for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
      window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
      window.cancelAnimationFrame =
        window[vendors[x]+'CancelAnimationFrame'] || window[vendors[x]+'CancelRequestAnimationFrame'];
  }

  if (!window.requestAnimationFrame)
      window.requestAnimationFrame = function(callback, element) {
          var currTime = new Date().getTime();
          var timeToCall = Math.max(0, 16 - (currTime - lastTime));
          var id = window.setTimeout(function() { callback(currTime + timeToCall); },
            timeToCall);
          lastTime = currTime + timeToCall;
          return id;
      };

  if (!window.cancelAnimationFrame)
      window.cancelAnimationFrame = function(id) {
          clearTimeout(id);
      };
}());

/*! A fix for the iOS orientationchange zoom bug.
 Script by @scottjehl, rebound by @wilto.
 MIT License.
*/
(function(w){
	// This fix addresses an iOS bug, so return early if the UA claims it's something else.
	if( !( /iPhone|iPad|iPod/.test( navigator.platform ) && navigator.userAgent.indexOf( "AppleWebKit" ) > -1 ) ){ return; }
    var doc = w.document;
    if( !doc.querySelector ){ return; }
    var meta = doc.querySelector( "meta[name=viewport]" ),
        initialContent = meta && meta.getAttribute( "content" ),
        disabledZoom = initialContent + ",maximum-scale=1",
        enabledZoom = initialContent + ",maximum-scale=10",
        enabled = true,
		x, y, z, aig;
    if( !meta ){ return; }
    function restoreZoom(){
        meta.setAttribute( "content", enabledZoom );
        enabled = true; }
    function disableZoom(){
        meta.setAttribute( "content", disabledZoom );
        enabled = false; }
    function checkTilt( e ){
		aig = e.accelerationIncludingGravity;
		x = Math.abs( aig.x );
		y = Math.abs( aig.y );
		z = Math.abs( aig.z );
		// If portrait orientation and in one of the danger zones
        if( !w.orientation && ( x > 7 || ( ( z > 6 && y < 8 || z < 8 && y > 6 ) && x > 5 ) ) ){
			if( enabled ){ disableZoom(); } }
		else if( !enabled ){ restoreZoom(); } }
	w.addEventListener( "orientationchange", restoreZoom, false );
	w.addEventListener( "devicemotion", checkTilt, false );
})( this );

jQuery(document).ready(function() {
  jQuery('#back').on('click', function(e) {
    e.preventDefault()
    window.history.go(-2);
  });
	jQuery(document).foundation({
		accordion: { multi_expand: true }
	});

	/* Disable the Submit Button Until Forms Are Filled Out */
	jQuery('#commentform #submit').attr('disabled', true);
	jQuery('#commentform input, #commentform textarea').on('input', function() {
		if(jQuery('#commentform input:invalid, #commentform textarea:invalid').length <= 0) {
			jQuery('#commentform #submit').removeAttr('disabled');
		}else {
			jQuery('#commentform #submit').attr('disabled', true);
		}
	});

  // Archive Thumbnail image swap
  //TODO: duplicate thumbail and fade in.
  // KLUDGE
  var hoverInterval;
  var k = 1;
  var swap = null;
  var imageArray = null;
  function fadeBackgroundImage() {
    if(k >= imageArray.length) {
      k = 0;
    }
    console.log(k);
    jQuery(swap).css('background-image', "url(" + imageArray[k++] + ")");
  }

  jQuery('.archive-thumbnail').each(function() {
    var images = jQuery(this).data('images').split(' ');
    if(images[0]!=''){
      // preload alternate images
      images.forEach(function(img){
        new Image().src = img;
      });
      jQuery(this).on('mouseover', function() {
        k=1;
        swap = this;
        imageArray = images;
        hoverInterval = setInterval(fadeBackgroundImage, 3000);
        fadeBackgroundImage();
      });
      jQuery(this).on('mouseout', function() {
        clearInterval(hoverInterval);
        jQuery(this).css('background-image', "url(" + images[0] + ")");
      });
    }
  });

  // Single-Product zoom
  jQuery('.woocommerce-product-gallery__image').each(function() {
    var img = jQuery('.woocommerce-product-gallery__image img');
    var tolerance = 1.5; // amount that large_image has to be larger
    var large_image = img.data('src');
    var large_image_height = img.data('large_image_height');
    var large_image_width = img.data('large_image_width');
    var image_width = img.width();
    var image_height = img.height();
    if(large_image_height > tolerance * image_height && large_image_width > tolerance * image_width){
      new Image().src = large_image;
      jQuery(this).zoom({ on: 'mouseover' });
    }
  });

  // Single-Product Thumbnail image swap
  jQuery('.woocommerce-product-gallery__thumbnail a').each(function() {
    jQuery(this).on('click', function() {
      jQuery('.thumbnails').find('.active').removeClass('active');
      jQuery(this).addClass('active');
      var tolerance = 1.5; // amount that large_image has to be larger
      var medium_image = jQuery(this).data('medium_image');
      var img = new Image()
      var large_image = jQuery(this).data('large_image');
      var large_image_height = jQuery(this).data('large_image_height');
      var large_image_width = jQuery(this).data('large_image_width');
      img.onload = function() {
        var image_width = this.width;
        var image_height = this.height;
        new Image().src = large_image;
        var main_image = jQuery('.woocommerce-product-gallery__image');
        var newHTML = '<a href="#"><img src="' +  medium_image + '" data-src="' +  large_image + '" data-large_image_height="' +  large_image_height + '" data-large_image_width="' +  large_image_width + '"></a>';
        jQuery('.woocommerce-product-gallery__image').html(newHTML);
        if(large_image_height > tolerance * image_height && large_image_width > tolerance * image_width){
          new Image().src = large_image;
          jQuery('.woocommerce-product-gallery__image').zoom({ on: 'mouseover' });
        }
      }
      img.src = medium_image;
    });
  });

	// Slideshows
	jQuery('.slideshow').each(function() {
		jQuery(this).slick({ infinite: true, speed: 300, fade: true, slidesToShow: 1, autoplay: true, autoplaySpeed: 5000, arrows: false });
	});

	// Sticky Footer
	function stickyFooter() {
		var footer = jQuery('#footer');
		var pos = footer.position();
		var height = jQuery(window).height();
		height = height - pos.top;
		height = height - footer.outerHeight();
		if (height > 0) {
			footer.css({
				'margin-top': height + 'px'
			});
		}
	}
	jQuery(document).on("ready", stickyFooter);
	jQuery(window).on("load", stickyFooter);
	jQuery(window).on("resize", stickyFooter);
	setTimeout(stickyFooter, 3000); //kludge
	stickyFooter();

	// Collapsible Lists
	jQuery('dt').on('click', function() {
		jQuery(this).toggleClass('collapse');
		jQuery(this).siblings('dd').slideToggle();
	});

  // Quantity changer
  jQuery('.qty-all').on('input', function() {
    jQuery('.qty').val(jQuery(this).val());
  });

  // Change footer 'copyrights' to '©' + date.
  var currentYear = (new Date).getFullYear();
  jQuery('.menu-copyrights a').text('© ' + currentYear + ' Marathon Company, Inc.');

  // Change dropdowns in off-canvas menus to submenus
  var dropdowns = jQuery('.right-off-canvas-menu').find('.dropdown');
    dropdowns.removeClass('dropdown');
    dropdowns.addClass('right-submenu');
    dropdowns.prepend('<li class="back show-for-small-only"><a href="#">Back</a></li>');
  var dropdownButtons = jQuery('.right-off-canvas-menu').find('.has-dropdown');
    dropdownButtons.removeClass('has-dropdown');
    dropdownButtons.addClass('has-submenu');

}); // end document.ready