/* global mw, $ */

let Poncho = {

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
		$( '#poncho-bell-item' ).mouseenter( Poncho.readNotifications );
		$( '#poncho-search-form input' ).keyup( Poncho.searchSuggestions );
		$( '#poncho-share-button' ).click( Poncho.share ),
		$( '#poncho-print-button' ).click( Poncho.print );

		// Hack to detect clicks on #poncho-search-suggestions
		// See https://stackoverflow.com/a/65073572/809356
		let searchSuggestionSelected = false;
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

	print: function () {
		window.print();
	},

	/**
	 * Build the share dialog
	 */
	share: function () {
		// Define the dialog elements
		let $overlay = $( '<div>' ).attr( 'id', 'poncho-share-overlay' );
		let $dialog = $( '<div>' ).attr( 'id', 'poncho-share-dialog' );
		let $title = $( '<h2>' ).attr( 'id', 'poncho-share-title' ).text( 'Share this page' );
		let $buttons = $( '<div>' ).attr( 'id', 'poncho-share-buttons' );
		let $close = $( '<div>' ).attr( 'id', 'poncho-share-close' ).text( '✕' );

		// Define the buttons
		let stylepath = mw.config.get( 'stylepath' );
		let url = encodeURIComponent( location.href );
		let title = $( '#firstHeading' ).text();
		let $facebook = $( '<a>' ).attr( {
			id: 'poncho-facebook-button',
			target: '_blank',
			href: 'https://www.facebook.com/sharer.php?u=' + url
		} ).html( '<img src="' + stylepath + '/Poncho/images/facebook.png" /><div>Facebook</div>' );
		let $twitter = $( '<a>' ).attr( {
			id: 'poncho-twitter-button',
			target: '_blank',
			href: 'https://twitter.com/intent/tweet?url=' + url
		} ).html( '<img src="' + stylepath + '/Poncho/images/twitter.png" /><div>Twitter</div>' );
		let $reddit = $( '<a>' ).attr( {
			id: 'poncho-reddit-button',
			target: '_blank',
			href: 'https://www.reddit.com/submit?url=' + url + '&title=' + title,
		} ).html( '<img src="' + stylepath + '/Poncho/images/reddit.png" /><div>Reddit</div>' );
		let $email = $( '<a>' ).attr( {
			id: 'poncho-email-button',
			target: '_blank',
			href: 'mailto:?subject=' + title + '&body=' + url
		} ).html( '<img src="' + stylepath + '/Poncho/images/email.png" /><div>Email</div>' );
		let $permalink = $( '<a>' ).attr( {
			id: 'poncho-permalink-button',
			target: '_blank',
		} ).html( '<img src="' + stylepath + '/Poncho/images/permalink.png" /><div>Permalink</div>' );

		// Bind events
		$close.click( function () {
			$overlay.remove();
			$dialog.remove();
		} );
		$overlay.click( function () {
			$overlay.remove();
			$dialog.remove();
		} );
		$permalink.click( function () {
			let copied = mw.message( 'poncho-copied' ).plain();
			navigator.clipboard.writeText( location.href ).then( function() {
				$( 'div', $permalink ).text( copied );
			} );
		} );

		// Put everything together and add it to the DOM
		$buttons.append( $facebook, $twitter, $reddit, $email, $permalink );
		$dialog.append( $close, $title, $buttons );
		$( 'body' ).append( $overlay, $dialog );
	},

	/**
	 * Suggest pages while searching
	 */
	searchSuggestions: function () {
		let query = $( this ).val();
		new mw.Api().get( {
			action: 'opensearch',
			search: query
		} ).done( function ( data ) {
			$( '#poncho-search-suggestions' ).empty();
			let suggestions = data.slice( 1, 2 )[0];
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
		let hideSidebar = mw.user.isAnon() ? mw.cookie.get( 'PonchoHideSidebar' ) : mw.user.options.get( 'poncho-hide-sidebar' );
		if ( hideSidebar ) {
			$( 'body' ).removeClass( 'poncho-hide-sidebar' );
			hideSidebar = null;
		} else {
			$( 'body' ).addClass( 'poncho-hide-sidebar' );
			hideSidebar = 1;
		}
		if ( mw.user.isAnon() ) {
			mw.cookie.set( 'PonchoHideSidebar', hideSidebar );
		} else {
			mw.user.options.set( 'poncho-hide-sidebar', hideSidebar );
			new mw.Api().saveOption( 'poncho-hide-sidebar', hideSidebar );
		}
	},

	/**
	 * Toggle the dark mode
	 */
	toggleDarkMode: function () {
		let darkMode = mw.user.isAnon() ? mw.cookie.get( 'PonchoDarkMode' ) : mw.user.options.get( 'poncho-dark-mode' );
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
		let readMode = mw.user.isAnon() ? mw.cookie.get( 'PonchoReadMode' ) : mw.user.options.get( 'poncho-read-mode' );
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
	 * Mark the bell item if the current user has unread notifications
	 */
	ring: function () {
		let bellItem = $( '#poncho-bell-item' );
		if ( $( '.active', bellItem ).length ) {
			bellItem.addClass( 'active' );
		}
	}
};

$( Poncho.init );