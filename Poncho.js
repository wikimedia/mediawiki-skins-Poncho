/* global mw, jQuery */

var Poncho = {

	/**
	 * Initialization script
	 */
	init: function () {
		Poncho.bind();
		Poncho.ring();
		mw.hook( 've.activationComplete' ).add( function () { $( '#poncho-edit-button' ).hide(); } );
		mw.hook( 've.deactivationComplete' ).add( function () { $( '#poncho-edit-button' ).show(); } );
		jQuery( '#poncho-search-form input' ).attr( 'list', 'poncho-search-suggestions' );
	},

	/**
	 * Bind events
	 */
	bind: function () {
		jQuery( '#poncho-bell-icon' ).mouseenter( Poncho.readNotifications );
		jQuery( '#poncho-search-form input' ).keyup( Poncho.searchSuggestions );
	},

	/**
	 * Suggest pages while searching
	 */
	searchSuggestions: function () {
		jQuery( '#poncho-search-suggestions' ).empty();
		var query = jQuery( this ).val();
		new mw.Api().get( {
			action: 'opensearch',
			search: query
		} ).done( function ( data ) {
			var suggestions = data.slice( 1, 2 )[0];
			suggestions.forEach( function ( suggestion ) {
				suggestion = jQuery( '<option>' ).val( suggestion );
				jQuery( '#poncho-search-suggestions' ).append( suggestion );
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
		jQuery( '#poncho-bell-icon' ).parent().removeClass( 'active' );
	},

	/**
	 * Mark the bell icon if the current user has unread notifications
	 */
	ring: function () {
		var notificationsItem = jQuery( '#poncho-bell-icon' ).parent();
		if ( jQuery( 'li.active', notificationsItem ).length ) {
			notificationsItem.addClass( 'active' );
		}
	}
};

mw.loader.using( [
	'mediawiki.api',
], Poncho.init );