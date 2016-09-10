var poncho = {

	init: function () {
		poncho.bind();

		// Push the footer to the bottom
	    var heightDiff = $( window ).height() - $( 'body' ).height();
	    if ( heightDiff > 0 ) {
	        $( '#footer' ).css( 'margin-top', heightDiff + 50 );
	    }
	},

	bind: function () {
		$( '#menu > li' ).mouseover( poncho.showSubmenu ).mouseout( poncho.hideSubmenu );
		$( '#search-form button' ).click( poncho.showSearchInput );
		$( '#search-form input' ).focusout( poncho.hideSearchInput );
	},

	showSubmenu: function () {
		var menu = $( this ),
			submenu = $( '.submenu', menu );
		if ( !submenu.is( ':visible' ) ) {
			submenu.show();
		}
	},

	hideSubmenu: function () {
		var menu = $( this );
		menu.children( '.submenu' ).hide();
	},

	toggleSubmenu: function () {
		var menu = $( this ),
			submenu = $( '.submenu', menu );
		if ( submenu.is( ':visible' ) ) {
			submenu.hide();
		} else {
			submenu.show();
		}
	},

	showSearchInput: function () {
		var searchButton = $( this ),
			searchForm = searchButton.parent(),
			searchInput = $( 'input', searchForm );

		if ( !searchInput.is( ':visible' ) ) {
			searchInput.show().focus();
			searchButton.addClass( 'active' );
			return false;
		}
	},

	hideSearchInput: function () {
		var searchInput = $( this ),
			searchForm = searchInput.parent(),
			searchButton = $( 'button', searchForm );

		searchInput.hide();
		searchButton.removeClass( 'active' );
	}
}

$( poncho.init );