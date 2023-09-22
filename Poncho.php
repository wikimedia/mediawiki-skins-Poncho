<?php

use MediaWiki\MediaWikiServices;

// Class needed for 1.35 support
class SkinPoncho extends SkinTemplate {
	public $template = 'PonchoTemplate';
}

class PonchoTemplate extends BaseTemplate {

	/**
	 * Enable OOUI
	 */
	static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		if ( $skin->getSkinName() === 'poncho' ) {
			$out->enableOOUI();
		}
	}

	/**
	 * Add classes to the body
	 */
	static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$bodyAttrs ) {
		if ( $skin->getSkinName() !== 'poncho' ) {
			return; // Don't run for other skins
		}
		$user = $skin->getUser();
		$request = $skin->getRequest();
		$embed = $request->getText( 'embed' );
		if ( $embed ) {
			$bodyAttrs['class'] .= ' poncho-embed-mode';
		}
		$services = MediaWikiServices::getInstance();
		$userOptionsLookup = $services->getUserOptionsLookup();
		$darkMode = $user->isAnon() ? $request->getCookie( 'PonchoDarkMode' ) : $userOptionsLookup->getOption( $user, 'poncho-dark-mode' );
		if ( $darkMode ) {
			$bodyAttrs['class'] .= ' poncho-dark-mode';
		}
		$readMode = $user->isAnon() ? $request->getCookie( 'PonchoReadMode' ) : $userOptionsLookup->getOption( $user, 'poncho-read-mode' );
		if ( $readMode ) {
			$bodyAttrs['class'] .= ' poncho-read-mode';
		}
		if ( $out->isTOCEnabled() ) {
			$bodyAttrs['class'] .= ' poncho-has-toc'; // We need this for responsive styling of the table of contents
		}
	}

	/**
	 * Echo the logo
	 */
	function logo() {
		global $wgLogos, $wgLogo, $wgSitename;

		// Make icon
		$src = $wgLogo;
		if ( $wgLogos && array_key_exists( 'icon', $wgLogos ) ) {
			$src = $wgLogos['icon'];
		}
		$width = 42;
		$height = 42;
		$attrs = [ 'id' => 'poncho-icon', 'src' => $src, 'width' => $width, 'height' => $height, 'alt' => $wgSitename ];
		$icon = Html::rawElement( 'img', $attrs );

		// Make wordmark
		if ( $wgLogos && array_key_exists( 'wordmark', $wgLogos ) ) {
			$src = $wgLogos['wordmark']['src'];
			$width = $wgLogos['wordmark']['width'];
			$height = $wgLogos['wordmark']['height'];
			$attrs = [ 'id' => 'poncho-wordmark', 'src' => $src, 'width' => $width, 'height' => $height, 'alt' => $wgSitename ];
			$wordmark = Html::rawElement( 'img', $attrs );
		} else {
			$wordmark = Html::rawElement( 'div', [ 'id' => 'poncho-wordmark' ], $wgSitename );
		}

		// Make tagline
		$tagline = null;
		if ( $wgLogos && array_key_exists( 'tagline', $wgLogos ) ) {
			$src = $wgLogos['tagline']['src'];
			$width = $wgLogos['tagline']['width'];
			$height = $wgLogos['tagline']['height'];
			$attrs = [ 'id' => 'poncho-tagline', 'src' => $src, 'width' => $width, 'height' => $height, 'alt' => $wgSitename ];
			$tagline = Html::rawElement( 'img', $attrs );
		}

		// Make wrapper span
		$span = Html::rawElement( 'span', [], $wordmark . $tagline );

		// Make link
		$attrs = Linker::tooltipAndAccesskeyAttribs( 'p-logo' );
		$attrs['id'] = 'poncho-logo';
		$attrs['href'] = htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] );
		$logo = Html::rawElement( 'a', $attrs, $icon . $span );

		// Allow extensions to completely override the logo
		Hooks::run( 'PonchoLogo', [ &$logo, $this ] );

		echo $logo;
	}

	/**
	 * Echo the search bar
	 */
	function searchInput() {
		echo new MediaWiki\Widget\SearchInputWidget( [
			'name' => 'search',
			'placeholder' => wfMessage( 'search' )
		] );
	}

	/**
	 * Echo the Talk button
	 */
	function talkButton() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		if ( !$title->canHaveTalkPage() ) {
			return;
		}
		if ( $title->isTalkPage() ) {
			return;
		}
		$context = $skin->getContext();
		$action = Action::getActionName( $skin->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
		$namespaces = array_values( $this->data['content_navigation']['namespaces'] );
		$button = $title->isTalkPage() ? $namespaces[0] : $namespaces[1];
		echo new OOUI\ButtonWidget( [
		    'id' => $button['id'],
		    'title' => $button['text'],
		    'href' => $button['href'],
		    'flags' => $button['class'] === 'new' ? 'destructive' : '',
		    'icon' => 'userTalk',
			'framed' => false
		] );
	}

	/**
	 * Echo the content action buttons
	 */
	function actionButtons() {
		$actions = $this->data['content_navigation']['views'];

		// Unset the current action per generally useless
		$skin = $this->getSkin();
		$context = $skin->getContext();
		$action = Action::getActionName( $context );
		unset( $actions[ $action ] );

		// Hack! VisualEditor includes JavaScript that replaces the contents
		// of the Edit <a> tag for plain text, which effectively removes
		// the HTML of our Edit button, so we echo this decoy first to trick it
		echo '<span id="ca-ve-edit"></span>';

		// Set the default icons
		$icons = [
			'view' => 'article',
			'viewsource' => 'wikiText',
			'edit' => array_key_exists( 've-edit', $actions ) ? 'wikiText' : 'edit',
			've-edit' => 'edit',
			'history' => 'history',
			'addsection' => 'add',
		];

		// Print the buttons
		foreach ( $actions as $key => $action ) {
			$icon = $action['icon'] ?? $icons[ $key ] ?? null;
			echo new OOUI\ButtonWidget( [
				'id' => $action['id'],
				'label' => $icon ? '' : $action['text'],
				'title' => $icon ? $action['text'] : '',
				'href' => $action['href'],
				'icon' => $icon,
				'framed' => false
			] );
		}
	}

	/**
	 * Echo the More button
	 */
	function moreButton() {
		$menu = $this->getMoreMenu();
		if ( ! $menu ) {
			return;
		}
		$skin = $this->getSkin();
		$context = $skin->getContext();
		$action = Action::getActionName( $context );
		echo new OOUI\ButtonWidget( [
			'id' => 'poncho-more-button',
			'title' => wfMessage( 'poncho-more' )->plain(),
			'icon' => 'ellipsis',
			'framed' => false
		] );
	}

	/**
	 * Return the more actions menu
	 */
	function getMoreMenu() {
		$menu = array_merge(
			$this->data['content_navigation']['actions'],
			$this->data['content_navigation']['variants'],
			$this->data['sidebar']['TOOLBOX']
		);
		return $menu;
	}

	/**
	 * Echo the title
	 */
	function title() {
		$Title = $this->getSkin()->getTitle();
		$title = $this->data['title'];
		$displayTitle = strip_tags( $title );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		if ( $displayTitle !== $Title->getFullText() ) {
			// DISPLAYTITLE is set, so do nothing
		} else if ( $Title->isTalkPage() && $Title->getSubjectPage()->exists() ) {
			$talk = str_replace( '_', ' ', $Title->getNsText() );
			$Subject = $Title->getSubjectPage();
			$subject = $Subject->getText();
			$link = $linkRenderer->makeLink( $Subject, $subject );
			$title = $talk . '<span class="poncho-title-colon">:</span>' . $link;
		} else if ( $Title->isSubpage() ) {
			$title = $Title->getSubpageText();
			while ( $Title->isSubpage() ) {
				$Title = $Title->getBaseTitle();
				if ( $Title->isSubpage() ) {
					$text = $Title->getSubpageText();
				} else {
					$text = $Title->getFullText();
				}
				$link = $linkRenderer->makeLink( $Title, $text );
				$title = $link . '<span class="poncho-title-dash">/</span>' . $title;
			}
		}
		echo $title;
	}

	/**
	 * Echo the path to the given image
	 */
	function image( $name ) {
		echo $this->getSkin()->getConfig()->get( 'StylePath' ) . '/Poncho/images/' . $name;
	}

	/**
	 * Return the main menu of the header
	 */
	function getNavigationMenu() {
		$sidebar = $this->data['sidebar'];
		$navigation = $sidebar['navigation'];
		return $navigation;
	}

	/**
	 * Return the user menu of the header
	 */
	function getUserMenu() {
		$userMenu = array_slice( $this->getPersonalTools(), 0, -1 );

		$skin = $this->getSkin();
		$user = $skin->getUser();
		$request = $skin->getRequest();
		$services = MediaWikiServices::getInstance();
		$userOptionsLookup = $services->getUserOptionsLookup();

		$darkMode = $user->isAnon() ? $request->getCookie( 'PonchoDarkMode' ) : $userOptionsLookup->getOption( $user, 'poncho-dark-mode' );
		$userMenu['dark-mode'] = [
			'id' => 'poncho-dark-mode',
			'text' => $darkMode ? wfMessage( 'poncho-disable-dark-mode' ) : wfMessage( 'poncho-enable-dark-mode' ),
			'class' => 'text',
		];

		$readMode = $user->isAnon() ? $request->getCookie( 'PonchoReadMode' ) : $userOptionsLookup->getOption( $user, 'poncho-read-mode' );
		$userMenu['read-mode'] = [
			'id' => 'poncho-read-mode',
			'text' => $readMode ? wfMessage( 'poncho-disable-read-mode' ) : wfMessage( 'poncho-enable-read-mode' ),
			'class' => 'text',
		];

		$userMenu += array_slice( $this->getPersonalTools(), -1 );

		// Unset irrelevant and repeated options
		unset( $userMenu['anonuserpage'] );
		unset( $userMenu['uls'] ); // Universal Language Selector
		unset( $userMenu['talk-alert'] );
		unset( $userMenu['notifications-alert'] );
		unset( $userMenu['notifications-notice'] );

		return $userMenu;
	}

	/**
	 * Get the languages menu
	 */
	function getLanguagesMenu() {
		$languages = $this->data['sidebar']['LANGUAGES'];
		if ( !$languages ) {
			$languages[] = [
				'text' => wfMessage( 'poncho-no-languages' ),
				'class' => 'text'
			];
		}
		return $languages;
	}

	/**
	 * Get the latest notifications in a format fit for BaseTemplate::makeListItem
	 */
	function getNotifications() {
		$notifications = [];
		$skin = $this->getSkin();
		$user = $skin->getUser();
		$title = $skin->getTitle();
		if ( $this->data['newtalk'] && $title->getFullText() !== $user->getTalkPage()->getFullText() ) {
			$link = [
				'text' => wfMessage( 'poncho-new-message' ),
				'href' => $user->getUserPage()->getTalkPage()->getFullURL()
			];
			$item = [
				'links' => [ $link ],
				'class' => 'link',
				'active' => true,
			];
			$notifications[] = $item;
		}
		// @todo This method uses an internal API call which should be replaced by direct calls to Echo classes
		if ( ExtensionRegistry::getInstance()->isLoaded( 'Echo' ) && $user->isRegistered() ) {
			global $wgRequest;
			$params = new DerivativeRequest( $wgRequest, [
				'action' => 'query',
				'meta' => 'notifications',
				'notformat' => 'model',
				'notlimit' => 10,
				'format' => 'json',
			] );
			$api = new ApiMain( $params );
			$api->execute();
			$data = $api->getResult()->getResultData();
			$list = $data['query']['notifications']['list'];
			$list = array_reverse( $list );
			foreach ( $list as $key => $item ) {
				if ( !is_int( $key ) ) {
					continue;
				}
				$content = $item['*'];
				$text = htmlspecialchars_decode( strip_tags( $content['header'] ), ENT_QUOTES );
				$href = $content['links']['primary']['url'] ?? null;
				$active = array_key_exists( 'read', $item ) ? false : true;
				$link = [
					'text' => $text,
					'href' => $href,
				];
				$item = [
					'links' => [ $link ],
					'class' => $href ? 'link' : 'text',
					'active' => $active,
				];
				$notifications[] = $item;
			}
		}
		if ( !$notifications ) {
			if ( ExtensionRegistry::getInstance()->isLoaded( 'Echo' ) && $user->isRegistered() ) {
				$link = [
					'text' =>  wfMessage( 'echo-none' ),
					'href' => Title::newFromText( 'Special:Notifications' )->getFullURL()
				];
				$item = [
					'links' => [ $link ],
					'class' => 'link',
				];
			} else {
				$item = [
					'text' => wfMessage( 'poncho-no-notifications' ),
					'class' => 'text'
				];
			}
			$notifications[] = $item;
		}
		return $notifications;
	}

	/**
	 * Add preferences
	 */
	static function onGetPreferences( $user, &$preferences ) {
		$preferences['poncho-dark-mode'] = [
			'hide-if' => [ '!==', 'skin', 'poncho' ],
			'section' => 'rendering/skin/skin-prefs',
			'type' => 'toggle',
			'label-message' => 'poncho-enable-dark-mode',
		];
		$preferences['poncho-read-mode'] = [
			'hide-if' => [ '!==', 'skin', 'poncho' ],
			'section' => 'rendering/skin/skin-prefs',
			'type' => 'toggle',
			'label-message' => 'poncho-enable-read-mode',
		];
	}

	/**
	 * Echo the footer
	 */
	function footer() {
		$footer = '';
		$links = $this->getFooterLinks();
		foreach ( $links as $items ) {
			$list = [];
			foreach ( $items as $item ) {
				$list[] = $this->get( $item );
			}
			$footer .= '<div>' . implode( ' · ', $list ) . '</div>';
		}
		echo $footer;
	}

	/**
	 * Output the page
	 */
	function execute() {
		include 'Poncho.phtml';
	}
}
