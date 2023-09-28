window.Poncho = {

	/**
	 * Initialization script
	 */
	init: function () {
		Poncho.bind();

		Poncho.ringNotificationsBell();

		// Add markup that can't be added from Poncho.phtml or Poncho.php
		$( '#poncho-search-form input' ).attr( 'list', 'poncho-search-suggestions' );
	},

	/**
	 * Bind events
	 */
	bind: function () {
		$( '#poncho-dark-mode' ).on( 'click', Poncho.toggleDarkMode );
		$( '#poncho-read-mode' ).on( 'click', Poncho.toggleReadMode );
		$( '#poncho-notifications-menu' ).on( 'mouseenter', Poncho.readNotifications );
		$( '#poncho-search-button' ).on( 'click', Poncho.search );
		$( '#poncho-search-form input' ).on( 'keyup', Poncho.searchSuggestions );

		$( window ).on( 'scroll', Poncho.updateTOC );

		mw.hook( 've.activationComplete' ).add( Poncho.toggleContentActions );
		mw.hook( 've.deactivationComplete' ).add( Poncho.toggleContentActions );

		// Hack to detect clicks on #poncho-search-suggestions
		// See https://stackoverflow.com/a/65073572/809356
		var searchSuggestionSelected = false;
		$( '#poncho-search-form input' ).on( 'keydown', function ( event ) {
			searchSuggestionSelected = false;
			if ( ! event.key ) {
				searchSuggestionSelected = true;
			}
		} );
		$( '#poncho-search-form input' ).on( 'change', function () {
			if ( searchSuggestionSelected ) {
				$( '#poncho-search-form' ).trigger( 'submit' );
			}
		} );
	},

	updateTOC: function () {
		var $toc = $( '#toc' );
		if ( $toc.css( 'position' ) === 'static' ) {
			return;
		}
		var windowTop = $( window ).scrollTop();
		$( ':header' ).each( function ( index ) {
			var headerTop = $( this ).offset().top;
			if ( headerTop > windowTop ) {
				var section = index - 1;
				$toc.find( '.toctext' ).css( 'font-weight', 'normal' );
				$toc.find( '.tocsection-' + section + ' > a > .toctext' ).css( 'font-weight', 'bold' );
				return false;
			}
		} );
	},

	search: function () {
		var $searchButton = $( this );
		var $searchInput = $( '#poncho-search-input' );
		var $searchForm = $( '#poncho-search-form' );
		var $searchWrapper = $( '#poncho-search-form-wrapper' );
		if ( $searchInput.is( ':visible' ) ) {
			$searchForm.trigger( 'submit' );
		} else {
			$searchWrapper.siblings().hide();
			$searchForm.css( 'max-width', '100%' );
			$searchInput.show().find( 'input' ).trigger( 'focus' );
			var closeButton = new OO.ui.ButtonWidget( { id: 'poncho-close-button', icon: 'close', framed: false } );
			closeButton.on( 'click', function () {
				closeButton.$element.remove();
				$searchForm.css( 'max-width', '600px' );
				$searchInput.hide();
				$searchWrapper.siblings().show();
			} );
			$searchButton.after( closeButton.$element );
		}
	},

	/**
	 * Suggest pages while searching
	 */
	searchSuggestions: function () {
		var query = $( this ).val();
		new mw.Api().get( {
			action: 'opensearch',
			search: query
		} ).done( function ( data ) {
			$( '#poncho-search-suggestions' ).empty();
			var suggestions = data.slice( 1, 2 )[0];
			suggestions.forEach( function ( suggestion ) {
				suggestion = $( '<option>' ).val( suggestion );
				$( '#poncho-search-suggestions' ).append( suggestion );
			} );
		} );
	},

	/**
	 * Toggle the content actions
	 */
	toggleContentActions: function () {
		$( '#poncho-content-actions' ).toggle();
	},

	/**
	 * Toggle the dark mode
	 */
	toggleDarkMode: function () {
		var darkMode = mw.user.isAnon() ? mw.cookie.get( 'PonchoDarkMode' ) : mw.user.options.get( 'poncho-dark-mode' );
		if ( darkMode ) {
			$( 'body' ).removeClass( 'poncho-dark-mode' );
			$( this ).text( mw.msg( 'poncho-enable-dark-mode' ) );
			darkMode = null;
		} else {
			$( 'body' ).addClass( 'poncho-dark-mode' );
			$( this ).text( mw.msg( 'poncho-disable-dark-mode' ) );
			darkMode = 1;
		}
		if ( mw.user.isAnon() ) {
			mw.cookie.set( 'PonchoDarkMode', darkMode );
		} else {
			mw.user.options.set( 'poncho-dark-mode', darkMode );
			new mw.Api().saveOption( 'poncho-dark-mode', darkMode );
		}
	},

	/**
	 * Toggle the read mode
	 */
	toggleReadMode: function () {
		var readMode = mw.user.isAnon() ? mw.cookie.get( 'PonchoReadMode' ) : mw.user.options.get( 'poncho-read-mode' );
		if ( readMode ) {
			$( 'body' ).removeClass( 'poncho-read-mode' );
			$( this ).text( mw.msg( 'poncho-enable-read-mode' ) );
			readMode = null;
		} else {
			$( 'body' ).addClass( 'poncho-read-mode' );
			$( this ).text( mw.msg( 'poncho-disable-read-mode' ) );
			readMode = 1;
		}
		if ( mw.user.isAnon() ) {
			mw.cookie.set( 'PonchoReadMode', readMode );
		} else {
			mw.user.options.set( 'poncho-read-mode', readMode );
			new mw.Api().saveOption( 'poncho-read-mode', readMode );
		}
	},

	/**
	 * Mark all notifications of the current user as read
	 */
	readNotifications: function () {
		new mw.Api().postWithEditToken( {
			action: 'echomarkread',
			all: true,
		} );
		$( this ).removeClass( 'active' );
	},

	/**
	 * Ring the notifications bell if the current user has unread notifications
	 */
	ringNotificationsBell: function () {
		var $menu = $( '#poncho-notifications-menu' );
		if ( $menu.find( '.active' ).length ) {
			$menu.addClass( 'active' );
		}
	}
};

$( Poncho.init );