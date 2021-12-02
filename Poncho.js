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