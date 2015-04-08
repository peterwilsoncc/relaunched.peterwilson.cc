jQuery(document).ready(function($){

	function lastDisplayedObject () {
		this.Label = null;
		this.Desc = null;
		this.Hint = null;
	}

	var lastL10n = new lastDisplayedObject();
	var lastDynimg = new lastDisplayedObject();
	wpppData = $.parseJSON( wpppData );

	function displaySetting ( last, idPrefix, level ) {
		if ( last.Label != null ) {
			$(last.Label).css({"font-weight": "normal", "font-size": "100%", "color": ""});
			$(last.Desc).hide();
			$(last.Hint).hide();
		}

		last.Label = $("#"+idPrefix+"-slider .ui-slider-label:eq("+level+")");
		last.Desc = $(".wppp-"+idPrefix+"-desc:eq("+level+")");
		last.Hint = $(".wppp-"+idPrefix+"-hint:eq("+level+")");

		$(last.Desc).show();
		$(last.Hint).show();
		$(last.Label).css({"font-weight": "bold", "font-size": "120%", "color": "#222"});
	}

	function setSettingInputValues ( settings, level ) {
		for ( var option in settings ) {
			$( "#wppp-settings input[name='wppp_option[" + option + "]']").val( level == 0 ? "false" : settings[option][level-1] );
		}
	}

	$( "#l10n-slider" ).slider({
		orientation: "vertical",
		value: wpppData.l10n.current,
		min: 0,
		max: 3,
		step: 1,
		slide: function( event, ui ) {
			displaySetting(lastL10n, 'l10n', ui.value );
			setSettingInputValues( wpppData.l10n.settings, ui.value);
		}
	}).slider( 'pips', {
		rest: 'label',
		labels: [ 	wpppData.labels.Off,
					wpppData.labels.Stable,
					wpppData.labels.Speed,
					wpppData.labels.Custom
				]
	});

	function updateDynImgSlider ( level ) {
		$( "#dynimg-slider" ).slider({
			orientation: "vertical",
			value: level,
			min: 0,
			max: 4,
			step: 1,
			slide: function( event, ui ) {
				displaySetting(lastDynimg, 'dynimg', ui.value);
				setSettingInputValues( wpppData.dynimg.settings, ui.value);
			}
		}).slider( 'pips', {
			rest: 'label',
			labels: [ wpppData.labels.Off,
					wpppData.labels.Stable,
					wpppData.labels.Speed,
					wpppData.labels.Webspace,
					wpppData.labels.Custom ],
		});

		displaySetting ( lastDynimg, "dynimg", level );
	}

	updateDynImgSlider( wpppData.dynimg.current );
	displaySetting( lastL10n, "l10n", wpppData.l10n.current );
	
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

		// set dynamic links
		if ( value == "coralcdn" || value == "maxcdn" || value == "customcdn" ) {
			$( "#dynamic-links" ).val( 'true' );
		} else {
			$( "#dynamic-links" ).val( 'false' );
		}

	});

	// Set CDN url on submit
	$( "#wppp-settings" ).submit(function( event ) {
		var value = $( "#wppp-cdn-select option:selected" ).val();

		// set cdn url
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