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
	 * Echo the search bar
	 */
	function searchInput() {
		echo new MediaWiki\Widget\SearchInputWidget( [
			'name' => 'search',
			'placeholder' => wfMessage( 'search' )
		] );
	}

	/**
	 * Echo the Edit button or buttons
	 */
	function editButton() {
		$action = Action::getActionName( $this->getSkin()->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
		if ( array_key_exists( 've-edit', $this->data['content_navigation']['views'] ) ) {
			$button = $this->data['content_navigation']['views']['ve-edit'];
			echo '<span id="ca-edit"></span>' . new OOUI\ButtonWidget( [
				'id' => 'poncho-visual-edit-button',
				'title' => $button['text'],
				'href' => $button['href'],
				'icon' => 'edit',
				'framed' => false
			] );
		}
		if ( array_key_exists( 'edit', $this->data['content_navigation']['views'] ) ) {
			$button = $this->data['content_navigation']['views']['edit'];
			echo new OOUI\ButtonWidget( [
				'id' => 'poncho-edit-button',
				'title' => $button['text'],
				'href' => $button['href'],
				'icon' => 'wikiText',
				'framed' => false
			] );
		}
	}

	/**
	 * Echo the Print button
	 */
	function printButton() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		if ( ! $title->exists() ) {
			return;
		}
		if ( ! $title->isContentPage() ) {
			return;
		}
		$action = Action::getActionName( $skin->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
		echo new OOUI\ButtonWidget( [
		    'id' => 'poncho-print-button',
		    'title' => wfMessage( 'poncho-print' )->plain(),
		    'icon' => 'printer',
			'framed' => false
		] );
	}

	/**
	 * Echo the Translate button
	 */
	function translateButton() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		if ( ! $title->exists() ) {
			return;
		}
		$namespace = $title->getNamespace();
		if ( ! in_array( $namespace, [ 0, 4, 12 ] ) ) {
			return;
		}
		$action = Action::getActionName( $skin->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
		echo new OOUI\ButtonWidget( [
			'id' => 'poncho-translate-button',
			'title' => wfMessage( 'poncho-translate' )->plain(),
			'icon' => 'language',
			'framed' => false
		] );
	}

	/**
	 * Echo the Read Aloud button
	 */
	function readAloudButton() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		if ( ! $title->exists() ) {
			return;
		}
		$namespace = $title->getNamespace();
		if ( ! in_array( $namespace, [ 0, 2, 4, 12 ] ) ) {
			return;
		}
		$action = Action::getActionName( $skin->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
		echo new OOUI\ButtonWidget( [
			'id' => 'poncho-read-aloud-button',
			'title' => wfMessage( 'poncho-read-aloud' )->plain(),
			'icon' => 'play',
			'framed' => false
		] );
		echo new OOUI\ButtonWidget( [
			'id' => 'poncho-pause-reading-button',
			'title' => wfMessage( 'poncho-pause-reading' )->plain(),
			'icon' => 'pause',
			'framed' => false
		] );
	}

	/**
	 * Echo the Share button
	 */
	function shareButton() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		if ( ! $title->isContentPage() ) {
			return;
		}
		if ( ! $title->exists() ) {
			return;
		}
		$action = Action::getActionName( $skin->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
		echo new OOUI\ButtonWidget( [
		    'id' => 'poncho-share-button',
		    'title' => wfMessage( 'poncho-share' )->plain(),
		    'icon' => 'heart',
			'framed' => false
		] );
	}

	/**
	 * Echo the Talk button
	 */
	function talkButton() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		if ( $title->isSpecialPage() ) {
			return;
		}
		if ( ! $title->exists() && $title->getNamespace() !== NS_USER ) {
			return;
		}
		if ( $title->isTalkPage() ) {
			return;
		}
		$action = Action::getActionName( $skin->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
		$namespaces = array_values( $this->data['content_navigation']['namespaces'] );
		$button = $title->isTalkPage() ? $namespaces[0] : $namespaces[1];
		echo new OOUI\ButtonWidget( [
		    'id' => 'poncho-talk-button',
		    'title' => $button['text'],
		    'href' => $button['href'],
		    'flags' => $button['class'] === 'new' ? 'destructive' : '',
		    'icon' => 'userTalk',
			'framed' => false
		] );
	}

	/**
	 * Echo the More button
	 */
	function moreButton() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		if ( ! $title->exists() ) {
			return;
		}
		$action = Action::getActionName( $skin->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
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
			$this->data['content_navigation']['views'],
			$this->data['content_navigation']['actions'],
			$this->data['content_navigation']['variants'],
			$this->data['sidebar']['TOOLBOX']
		);

		// Remove undesired items
		unset( $menu['upload'] );
		unset( $menu['specialpages'] );
		unset( $menu['print'] );
		unset( $menu['edit'] );
		unset( $menu['ve-edit'] );

		return $menu;
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
		$attrs = [ 'id' => 'poncho-icon', 'src' => $src, 'width' => $width, 'height' => $height ];
		$icon = Html::rawElement( 'img', $attrs );

		// Make wordmark
		if ( $wgLogos && array_key_exists( 'wordmark', $wgLogos ) ) {
			$src = $wgLogos['wordmark']['src'];
			$width = $wgLogos['wordmark']['width'];
			$height = $wgLogos['wordmark']['height'];
			$attrs = [ 'id' => 'poncho-wordmark', 'src' => $src, 'width' => $width, 'height' => $height ];
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
			$attrs = [ 'id' => 'poncho-tagline', 'src' => $src, 'width' => $width, 'height' => $height ];
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
	 * Print wordmark or sitename
	 */
	function wordmark() {
		global $wgLogos, $wgSitename;
		if ( $wgLogos && array_key_exists( 'wordmark', $wgLogos ) ) {
			$wordmark = $wgLogos['wordmark']['src'];
			echo '<img src="' . $wordmark . '" />';
		} else {
			echo $wgSitename;
		}
	}

	/**
	 * Echo the tagline
	 */
	function tagline() {
		global $wgLogos, $wgSitename;
		if ( $wgLogos && array_key_exists( 'tagline', $wgLogos ) ) {
			$tagline = $wgLogos['tagline']['src'];
			echo '<img src="' . $tagline . '" />';
		}
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
