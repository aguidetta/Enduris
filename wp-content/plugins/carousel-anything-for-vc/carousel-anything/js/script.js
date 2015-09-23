jQuery(document).ready(function($) {
	"use strict";

	$(".carousel-anything-container").each(function() {
		
		// VC 4.4 adds an empty div .vc_row-full-width somehow, get rid of them
		$(this).find('> .vc_row-full-width').remove();
		
		var $this = $(this);
				
		$(this).owlGambitCarousel({
			items : $(this).attr('data-items'),
			itemsDesktop : [1199, $(this).attr('data-items')],
			itemsDesktopSmall : [979, $(this).attr('data-items-small')],
			itemsTablet : [768, $(this).attr('data-items-tablet')],
			itemsMobile : [479, $(this).attr('data-items-mobile')],
			scrollPerPage : false,
			//scrollPerPage : $(this).attr('data-scroll_per_page') === 'true' ? true : false,
			autoPlay : $(this).attr('data-autoplay') === 'false' ? false : $(this).attr('data-autoplay'),
			pagination: $(this).attr('data-thumbnails') === 'none' || $(this).attr('data-thumbnails') === 'arrows' ? false : true,
			paginationNumbers: $(this).attr('data-thumbnail-numbers') === 'true' ? true : false,
			stopOnHover: $(this).attr('data-stop-on-hover') === 'true' ? true : false,
			paginationSpeed: $(this).attr('data-speed-scroll'),
			rewindSpeed: $(this).attr('data-speed-rewind'),
			autoHeight: false,
			navigation: $(this).attr('data-navigation') === 'true' ? true : false,
			navigationText : ["&nbsp;","&nbsp;"],
			// data-touchdrag signifies disabling of touch-dragging. If false, touch-dragging will be POSSIBLE.
			touchDrag: $(this).attr('data-touchdrag') === 'false' ? true : false,
			mouseDrag: $(this).attr('data-touchdrag') === 'false' ? true : false,
			// This fixes the height issues
	        afterInit: function(){
				setTimeout( function(){
			   	    $this.data('owlGambitCarousel').updateVars();
				 }, 500);
	        },
		});
		
		// Process if keyboard navigation is enabled.
		if ( $(this).attr('data-keyboard') == 'cursor' || $(this).attr('data-keyboard') == 'fps') {
			document.addEventListener('keydown', function(e) {

				var prevkey = 37;
				var nextkey = 39;

				if ( $(this).attr('data-keyboard') == 'fps' ) {
					prevkey = 65;
					nextkey = 68;					
				}

				if ( e.keyCode == prevkey ) {
					$(this).data( 'owlGambitCarousel' ).prev();
				}
				else if ( e.keyCode == nextkey ) {				
					$(this).data( 'owlGambitCarousel' ).next();
				}

			}.bind(this) );
		}
		
	});
	
});