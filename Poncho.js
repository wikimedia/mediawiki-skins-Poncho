/* global mw, $ */

window.Poncho = {

	/**
	 * Initialization script
	 */
	init: function () {
		Poncho.bindEvents();

		Poncho.markNotificationsBell();

		// Stop any running voice
		window.speechSynthesis.cancel();

		// Add markup that can't be added from Poncho.phtml or Poncho.php
		$( '#poncho-search-form input' ).attr( 'list', 'poncho-search-suggestions' );
	},

	/**
	 * Bind events
	 */
	bindEvents: function () {
		$( '#poncho-dark-mode' ).click( Poncho.toggleDarkMode );
		$( '#poncho-read-mode' ).click( Poncho.toggleReadMode );
		$( '#poncho-bell-item' ).one( 'mouseenter', Poncho.readNotifications );
		$( '#poncho-search-form input' ).keyup( Poncho.searchSuggestions );
		$( '#poncho-share-button' ).click( Poncho.share ),
		$( '#poncho-translate-button' ).click( Poncho.translate );
		$( '#poncho-read-aloud-button' ).click( Poncho.readAloud );
		$( '#poncho-more-button' ).click( Poncho.toggleMoreMenu );
		$( window ).click( Poncho.hideMoreMenu );
		$( window ).scroll( Poncho.updateTOC );

		mw.hook( 've.activationComplete' ).add( Poncho.toggleContentActions );
		mw.hook( 've.deactivationComplete' ).add( Poncho.toggleContentActions );

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

	updateTOC: function () {
		var windowTop = $( window ).scrollTop();
		console.log( 'windowTop', windowTop );
		$( ':header' ).each( function ( index ) {
			var headerTop = $( this ).offset().top;
			console.log( 'headerTop', headerTop );
			if ( headerTop > windowTop ) {
				console.log( 'index', index );
				var section = index - 1;
				$( '.toctext' ).css( 'font-weight', 'normal' );
				$( '#toc' ).find( '.tocsection-' + section + ' > a > .toctext' ).css( 'font-weight', 'bold' );
				return false;
			}
		} );
	},

	/**
	 * Read the current page aloud
	 */
	readAloud: function () {
		var $content = $( '#mw-content-text' ).clone();

		// Remove elements we don't want to read
		$content.find( '.mw-editsection, .dablink, .noprint, .thumb' ).remove();
		$content.find( 'style, table' ).remove();

		// Read the text but add silence between elements
		var text = $content.text();
		var textParts = text.split( '\n' );
		var currentIndex = 0;
		var speak = function ( textPart ) {
			var utterance = new SpeechSynthesisUtterance( textPart );
			utterance.lang = $content.attr( 'lang' );
			utterance.rate = 0.85;
			utterance.onend = function () {
				currentIndex++;
				if ( currentIndex < textParts.length ) {
					setTimeout( function () {
						speak( textParts[ currentIndex ] );
					}, 500 );
				}
			};
			window.speechSynthesis.speak( utterance );
		};
		window.speechSynthesis.cancel();
		speak( textParts[0] );

		$( this ).off().click( Poncho.pauseReading ).find( 'a' ).attr( 'title', mw.msg( 'poncho-pause-reading' ) );
	},

	/**
	 * Pause reading aloud
	 */
	pauseReading: function () {
		window.speechSynthesis.pause();
		$( this ).off().click( Poncho.resumeReading ).find( 'a' ).attr( 'title', mw.msg( 'poncho-resume-reading' ) );
	},

	/**
	 * Resume reading aloud
	 */
	resumeReading: function () {
		window.speechSynthesis.resume();
		$( this ).off().click( Poncho.pauseReading ).find( 'a' ).attr( 'title', mw.msg( 'poncho-pause-reading' ) );
	},

	/**
	 * Build the share dialog
	 */
	share: function () {
		// Define the dialog elements
		var $overlay = $( '<div>' ).attr( 'id', 'poncho-share-overlay' );
		var $dialog = $( '<div>' ).attr( 'id', 'poncho-share-dialog' );
		var $title = $( '<h2>' ).attr( 'id', 'poncho-share-title' ).text( 'Share this page' );
		var $buttons = $( '<div>' ).attr( 'id', 'poncho-share-buttons' );
		var $close = $( '<div>' ).attr( 'id', 'poncho-share-close' ).text( '✕' );

		// Define the buttons
		var stylepath = mw.config.get( 'stylepath' );
		var url = encodeURIComponent( location.href );
		var title = $( '#firstHeading' ).text();
		var $facebook = $( '<a>' ).attr( {
			id: 'poncho-facebook-button',
			target: '_blank',
			href: 'https://www.facebook.com/sharer.php?u=' + url
		} ).html( '<img src="' + stylepath + '/Poncho/images/facebook.png" /><div>Facebook</div>' );
		var $twitter = $( '<a>' ).attr( {
			id: 'poncho-twitter-button',
			target: '_blank',
			href: 'https://twitter.com/intent/tweet?url=' + url
		} ).html( '<img src="' + stylepath + '/Poncho/images/twitter.png" /><div>Twitter</div>' );
		var $reddit = $( '<a>' ).attr( {
			id: 'poncho-reddit-button',
			target: '_blank',
			href: 'https://www.reddit.com/submit?url=' + url + '&title=' + title,
		} ).html( '<img src="' + stylepath + '/Poncho/images/reddit.png" /><div>Reddit</div>' );
		var $email = $( '<a>' ).attr( {
			id: 'poncho-email-button',
			target: '_blank',
			href: 'mailto:?subject=' + title + '&body=' + url
		} ).html( '<img src="' + stylepath + '/Poncho/images/email.png" /><div>Email</div>' );
		var $permalink = $( '<a>' ).attr( {
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
			var copied = mw.message( 'poncho-copied' ).plain();
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
	 * Toggle the more actions menu
	 */
	toggleMoreMenu: function () {
		$( '#poncho-more-menu' ).toggle();
	},

	/**
	 * Hide the more actions menu if the click is outside it
	 */
	hideMoreMenu: function ( event ) {
		var $target = $( event.target );
		if ( !$target.closest( '#poncho-more-menu' ).length && !$target.closest( '#poncho-more-button' ).length ) {
			$( '#poncho-more-menu' ).hide();
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
	markNotificationsBell: function () {
		var $bell = $( '#poncho-bell-item' );
		if ( $bell.find( '.active' ).length ) {
			$bell.addClass( 'active' );
		}
	},

	/**
	 * Load Google Translate
	 */
	translate: function () {
		$.getScript( '//translate.google.com/translate_a/element.js?cb=Poncho.initGoogleTranslate' );

		// Add the necessary DOM element
		$( 'body' ).after( '<div hidden id="google-translate-element"></div>' );

		// Mark the main interface elements to prevent translation, since MediaWiki already does that
		$( '#poncho-header-wrapper, #poncho-sidebar-wrapper, #poncho-footer-wrapper, #poncho-content-actions' ).attr( 'translate', 'no' );
	},

	initGoogleTranslate: function () {
		new google.translate.TranslateElement( {
			pageLanguage: mw.config.get( 'wgPageContentLanguage' ),
			layout: google.translate.TranslateElement.InlineLayout.SIMPLE
		}, 'google-translate-element' );

		// If the user already translated a page
		// then Google will remember the language selection and translate immediately
		// else we ask the user to select a language
		if ( mw.cookie.get( 'googtrans', '' ) ) {
			Poncho.updateTranslateButton();
		} else {
			setTimeout( Poncho.openTranslationMenu, 1000 ); // For some reason the menu is not available immediately
		}
	},

	updateTranslateButton: function () {
		var $button = $( '#poncho-translate-button' );
		if ( mw.cookie.get( 'googtrans', '' ) ) {
			$button.find( 'a' ).attr( 'title', mw.msg( 'poncho-stop-translating' ) );
			$button.off().click( Poncho.stopTranslating );
		} else {
			$button.find( 'a' ).attr( 'title', mw.msg( 'poncho-translate' ) );
			$button.off().click( Poncho.openTranslationMenu );
		}
	},

	openTranslationMenu: function () {
		$( '.goog-te-gadget-simple' ).click();

		// If the user actually selects a language, update the button
		// but wait a second because the cookie is not set instantly
		$( '.goog-te-menu-frame' ).contents().click( function () {
			setTimeout( Poncho.updateTranslateButton, 1000 );
		} );
	},

	stopTranslating: function () {
		$( '.goog-te-banner-frame' ).contents().find( '.goog-close-link img' ).click();
		Poncho.updateTranslateButton();
	}
};

$( Poncho.init );