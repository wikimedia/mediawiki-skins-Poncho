var poncho = {

	init: function () {
		poncho.bind();

		// Push the footer to the bottom
	    var heightDiff = $( window ).height() - $( 'body' ).height();
	    if ( heightDiff > 0 ) {
	        $( '#footer' ).css( 'margin-top', heightDiff + 50 );
	    }
	},

	/**
	 * Bind events
	 */
	bind: function () {
		$( '#menu > li' ).on( 'mouseover click', poncho.showSubmenu ).on( 'mouseout', poncho.hideSubmenu );
	},

	showSubmenu: function ( event ) {
		var menu = $( event.currentTarget ),
			submenu = menu.find( '.submenu' );
		if ( !submenu.is( ':visible' ) ) {
			submenu.show();
			event.stopPropagation();
		}
	},

	hideSubmenu: function ( event ) {
		var menu = $( event.currentTarget );
		menu.children( '.submenu' ).hide();
	}
}

$( poncho.init );