/* global mw, $ */

var Poncho = {

	/**
	 * Initialization script
	 */
	init: function () {
		Poncho.bind();
		Poncho.ring();
		mw.hook( 've.activationComplete' ).add( function () { $( '#poncho-visual-edit-button, #poncho-edit-source-button' ).hide(); } );
		mw.hook( 've.deactivationComplete' ).add( function () { $( '#poncho-visual-edit-button, #poncho-edit-source-button' ).show(); } );
		$( '#poncho-search-form input' ).attr( 'list', 'poncho-search-suggestions' );
		if ( window.location.hash === '#print' ) {
			window.print();
		}
	},

	/**
	 * Bind events
	 */
	bind: function () {
		$( '#poncho-sidebar-icon' ).click( Poncho.toggleSidebar );
		$( '#poncho-dark-mode' ).click( Poncho.toggleDarkMode );
		$( '#poncho-read-mode' ).click( Poncho.toggleReadMode );
		$( '#poncho-bell-icon' ).mouseenter( Poncho.readNotifications );
		$( '#poncho-search-form input' ).keyup( Poncho.searchSuggestions );
		$( 'a[href="#print"]' ).click( function () {
			window.print();
		} );

		// Hack to detect clicks on #poncho-search-suggestions
		// See https://stackoverflow.com/a/65073572/809356
		var searchSuggestionSelected = false;
		$( '#poncho-search-form input' ).keydown( function ( event ) {
			searchSuggestionSelected = false;
			if ( ! event.key ) {
				searchSuggestionSelected = true;
			}
		} );
		$( '#poncho-search-form input' ).change( function () {
			if ( searchSuggestionSelected ) {
				$( '#poncho-search-form' ).submit();
			}
		} );
	},

	/**
	 * Suggest pages while searching
	 */
	searchSuggestions: function () {
		$( '#poncho-search-suggestions' ).empty();
		var query = $( this ).val();
		new mw.Api().get( {
			action: 'opensearch',
			search: query
		} ).done( function ( data ) {
			var suggestions = data.slice( 1, 2 )[0];
			suggestions.forEach( function ( suggestion ) {
				suggestion = $( '<option>' ).val( suggestion );
				$( '#poncho-search-suggestions' ).append( suggestion );
			} );
		} );
	},

	/**
	 * Toggle the sidebar
	 */
	toggleSidebar: function () {
		var sidebar = mw.user.isAnon() ? mw.cookie.get( 'PonchoSidebar' ) : mw.user.options.get( 'poncho-sidebar' );
		if ( sidebar ) {
			$( 'body' ).removeClass( 'poncho-sidebar' );
			sidebar = null;
		} else {
			$( 'body' ).addClass( 'poncho-sidebar' );
			sidebar = 1;
		}
		if ( mw.user.isAnon() ) {
			mw.cookie.set( 'PonchoSidebar', sidebar );
		} else {
			mw.user.options.set( 'poncho-sidebar', sidebar );
			new mw.Api().saveOption( 'poncho-sidebar', sidebar );
		}
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
	 * Mark all notifications of the current user as read and unmark the bell icon
	 */
	readNotifications: function () {
		new mw.Api().postWithEditToken( {
			action: 'echomarkread',
			all: true,
		} );
		$( '#poncho-bell-icon' ).parent().removeClass( 'active' );
	},

	/**
	 * Mark the bell icon if the current user has unread notifications
	 */
	ring: function () {
		var notificationsItem = $( '#poncho-bell-icon' ).parent();
		if ( $( 'li.active', notificationsItem ).length ) {
			notificationsItem.addClass( 'active' );
		}
	}
};

mw.loader.using( [
	'mediawiki.api',
], Poncho.init );