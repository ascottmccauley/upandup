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
	jQuery(document).foundation();

	/* Disable the Submit Button Until Forms Are Filled Out */
	jQuery('#commentform #submit').attr('disabled', true);
	jQuery('#commentform input, #commentform textarea').on('input', function() {
		if(jQuery('#commentform input:invalid, #commentform textarea:invalid').length <= 0) {
			jQuery('#commentform #submit').removeAttr('disabled');
		}else {
			jQuery('#commentform #submit').attr('disabled', true);
		}
	});
	
	// Magnific Popup Lightbox
	jQuery('.gallery').each(function() { // the containers for all your galleries
		jQuery(this).magnificPopup({
			delegate: 'a', // the selector for gallery item
			gallery:{enabled:true},
			type:'image',
			image: {
				verticalFit: true
			},
			focus: ''
		});
	});
	
	// Slideshows
	jQuery('.slideshow').each(function() {
		jQuery(this).slick({ infinite: true, speed: 300, fade: true, slidesToShow: 1, autoplay: true, autoplaySpeed: 5000, arrows: false });
	});
	
	// Sticky Footer
	function stickyFooter() {
		console.log('stickying');
		var footer = jQuery('#footer');
		var pos = footer.position();
		var height = jQuery(window).height();
		height = height - pos.top;
		height = height - footer.height();
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
});