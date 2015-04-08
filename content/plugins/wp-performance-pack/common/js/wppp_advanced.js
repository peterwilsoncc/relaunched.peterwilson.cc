jQuery(document).ready(function($){

	function setDynimgQuality ( value ) {
		$( "#wppp-settings input[name='wppp_option[dynimg_quality]']").val(value);
	}

	$( "#dynimg-quality-slider" ).slider({
		orientation: "horizontal",
		value: wpppData["dynimg-quality"],
		min: 10,
		max: 100,
		step: 10,
		slide: function( event, ui ) {
			setDynimgQuality(ui.value);
		}
	}).slider( 'pips', {
		rest: "label",
		suffix: "%",
	});
	
	// Support Sticky
	var stickyNavTop = $('.wppp-sticky').offset().top - $('#wpadminbar').height();

	var stickyNav = function(){
		var scrollTop = $(window).scrollTop();
   
		if (scrollTop > stickyNavTop) { 
			$('.wppp-sticky').addClass('sticky');
		} else {
			$('.wppp-sticky').removeClass('sticky'); 
		}
	};

	stickyNav();

	$(window).scroll(function() {
		stickyNav();
	});
	
	// hide support box
	$( "#hidesupportbox" ).click( function() {
		var data = {
			action: 'hidewpppsupportbox',
		};
		$.post(ajaxurl, data);
		$( "#wppp-support-box" ).hide();
	});
	
	// CDN dropbox
	$( "#wppp-cdn-select" ).change( function() {
		$( ".wppp-cdn-div").hide();
		
		var value = $( "#wppp-cdn-select option:selected" ).val();

		// show cdn info
		if ( value == "coralcdn" ) {
			$( "#wppp-coralcdn" ).show();
			$( "#wppp-maxcdn-signup" ).hide();
		} else if ( value == "maxcdn" ) {
			$( "#wppp-maxcdn" ).show();
			$( "#wppp-maxcdn-signup" ).show();
		} else if ( value == "customcdn" ) {
			$( "#wppp-customcdn" ).show();
			$( "#wppp-maxcdn-signup" ).hide();
		} else {
			$( "#wppp-nocdn" ).show();
			$( "#wppp-maxcdn-signup" ).hide();
		}
	});
	
	// Set CDN url on submit
	$( "#wppp-settings" ).submit(function( event ) {
		var value = $( "#wppp-cdn-select option:selected" ).val();

		if ( value == "coralcdn" ) {
			$( "#cdn-url" ).val('');
		} else if ( value == "maxcdn" ) {
			$( "#cdn-url" ).val( $( "#maxcdn-url" ).val() );
		} else if ( value == "customcdn" ) {
			$( "#cdn-url" ).val( $( "#customcdn-url" ).val() );
		} else {
			$( "#cdn-url" ).val( '' );
		}
	});
});