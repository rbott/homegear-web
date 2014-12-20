$( document ).one( "pagecreate", ".pages", function() {
	function navnext( next ) {
		$( ":mobile-pagecontainer" ).pagecontainer( "change", next + ".html", {
			transition: "slide"
		});
	}
	function navprev( prev ) {
		$( ":mobile-pagecontainer" ).pagecontainer( "change", prev + ".html", {
			transition: "slide",
		reverse: true
		});
	}
	$(document).on('click', '.next', function () {
		if ($( ":mobile-pagecontainer" ).pagecontainer("getActivePage" ).next('.pages').length !== 0) {
			var next = $( ":mobile-pagecontainer" ).pagecontainer("getActivePage" ).next('.pages');
			$( ":mobile-pagecontainer" ).pagecontainer( "change", next, { transition: 'slide' } );
		} 
	});

	$(document).on('click', '.prev', function () {
		if ($( ":mobile-pagecontainer" ).pagecontainer("getActivePage" ).prev('.pages').length !== 0) {
			var prev = $( ":mobile-pagecontainer" ).pagecontainer("getActivePage" ).prev('.pages');
			$( ":mobile-pagecontainer" ).pagecontainer( "change", prev, { transition: 'slide', reverse: true } );
		} 
	});
	$(document).on( "swipeleft", ".ui-page", function( event ) {
			if ($( ":mobile-pagecontainer" ).pagecontainer("getActivePage" ).next('.pages').length !== 0) {
			var next = $( ":mobile-pagecontainer" ).pagecontainer("getActivePage" ).next('.pages');
			$( ":mobile-pagecontainer" ).pagecontainer( "change", next, { transition: 'slide' } );
		}
	});
	$(document).on( "swiperight", ".ui-page", function( event ) {
			if ($( ":mobile-pagecontainer" ).pagecontainer("getActivePage" ).prev('.pages').length !== 0) {
			var prev = $( ":mobile-pagecontainer" ).pagecontainer("getActivePage" ).prev('.pages');
			$( ":mobile-pagecontainer" ).pagecontainer( "change", prev, { transition: 'slide', reverse: true } );
		}
	});

});

