<?php

use MediaWiki\MediaWikiServices;

// Class needed for 1.35 support
class SkinPoncho extends SkinTemplate {
	public $template = 'PonchoTemplate';
}

class PonchoTemplate extends BaseTemplate {

	static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$out->enableOOUI();
		$out->addModuleStyles( [
			'oojs-ui.styles.icons-user',
			'oojs-ui.styles.icons-interactions',
			'oojs-ui.styles.icons-editing-core',
			'oojs-ui.styles.icons-editing-advanced'
		] );
		$out->addMeta( 'viewport', 'width=device-width,user-scalable=no' );
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
	 * Echo the page action buttons
	 */
	function pageActions() {
		$this->editButton();
		$this->printButton();
		$this->shareButton();
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
			echo new OOUI\ButtonWidget( [
				'id' => 'poncho-visual-edit-button',
				'label' => $button['text'],
				'href' => $button['href'],
				'flags' => [ 'primary', 'progressive' ],
				'icon' => 'edit'
			] );
		}
		if ( array_key_exists( 'edit', $this->data['content_navigation']['views'] ) ) {
			$button = $this->data['content_navigation']['views']['edit'];
			echo new OOUI\ButtonWidget( [
				'id' => 'poncho-edit-source-button',
				'label' => $button['text'],
				'href' => $button['href'],
				'icon' => 'wikiText'
			] );
		}
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
		    'label' => $button['text'],
		    'href' => $button['href'],
		    'flags' => $button['class'] === 'new' ? 'destructive' : '',
		    'icon' => 'userTalk'
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
		    'label' => wfMessage( 'poncho-share' )->plain(),
		    'icon' => 'heart',
		] );
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
		    'label' => wfMessage( 'poncho-print' )->plain(),
		    'icon' => 'printer',
		] );
	}

	/**
	 * Echo the attributes of the logo
	 */
	function logoAttributes() {
		global $wgLogos, $wgLogo;
		if ( $wgLogo ) {
			$logo = $wgLogo;
		}
		if ( array_key_exists( '1x', $wgLogos ) ) {
			$logo = $wgLogos['1x'];
		}
		if ( array_key_exists( 'icon', $wgLogos ) ) {
			$logo = $wgLogos['icon'];
		}
		if ( array_key_exists( 'wordmark', $wgLogos ) ) {
			$logo = $wgLogos['wordmark']['src'] ?? null;
		}
		$attributes = Linker::tooltipAndAccesskeyAttribs( 'p-logo' );
		$attributes['href'] = htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] );
		$attributes['style'] = 'background-image: url("' . $logo . '");';
		$attributes = Xml::expandAttributes( $attributes );
		echo $attributes;
	}

	/**
	 * Echo the title
	 */
	function title() {
		$Title = $this->getSkin()->getTitle();
		$title = $this->data['title'];
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		if ( $title !== $Title->getFullText() ) {
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
	 * Echo the name of the site
	 */
	function siteName() {
		global $wgSitename, $wgLogos;
		if ( $wgLogos && array_key_exists( 'wordmark', $wgLogos ) ) {
			return;
		}
		echo $wgSitename;
	}

	/**
	 * Return the main menu of the header
	 */
	function getMainMenu() {
		$sidebar = $this->data['sidebar'];
		unset( $sidebar['SEARCH'] );
		unset( $sidebar['TOOLBOX'] );
		unset( $sidebar['LANGUAGES'] );
		return $sidebar;
	}

	/**
	 * Return the user menu of the header
	 */
	function getUserMenu() {
		$userMenu = $this->getPersonalTools();
		unset( $userMenu['uls'] );
		unset( $userMenu['notifications-alert'] );
		unset( $userMenu['notifications-notice'] );
		return $userMenu;
	}

	/**
	 * Get the languages menu
	 */
	function getLanguagesMenu() {
		$languages = $this->data['sidebar']['LANGUAGES'];
		$link = [
			'text' => wfMessage( 'poncho-google-translate' ),
			'href' => 'https://translate.google.com/translate?u=' . $this->getSkin()->getTitle()->getFullURL(),
			'target' => '_blank'
		];
		$item = [
			'links' => [ $link ],
			'class' => 'link',
		];
		$languages[] = $item;
		return $languages;
	}

	/**
	 * Return the view options
	 */
	function getViewOptions() {
		$viewOptions = [];

		$skin = $this->getSkin();
		$user = $skin->getUser();
		$request = $skin->getRequest();
		$services = MediaWikiServices::getInstance();
		$userOptionsLookup = $services->getUserOptionsLookup();

		$darkMode = $user->isAnon() ? $request->getCookie( 'PonchoDarkMode' ) :
			$userOptionsLookup->getOption( $user, 'poncho-dark-mode' );
		$viewOptions['dark-mode'] = [
			'id' => 'poncho-dark-mode',
			'text' => $darkMode ? wfMessage( 'poncho-disable-dark-mode' ) : wfMessage( 'poncho-enable-dark-mode' ),
			'class' => 'text',
		];

		$readMode = $user->isAnon() ? $request->getCookie( 'PonchoReadMode' ) :
			$userOptionsLookup->getOption( $user, 'poncho-read-mode' );
		$viewOptions['read-mode'] = [
			'id' => 'poncho-read-mode',
			'text' => $readMode ? wfMessage( 'poncho-disable-read-mode' ) : wfMessage( 'poncho-enable-read-mode' ),
			'class' => 'text',
		];

		return $viewOptions;
	}

	/**
	 * Return the actions
	 */
	function getActions() {
		$actions = array_merge(
			$this->data['content_navigation']['views'],
			$this->data['content_navigation']['actions'],
			$this->data['content_navigation']['variants']
		);
		return $actions;
	}

	/**
	 * Return the page tools for the footer
	 */
	function getTools() {
		return $this->get( 'sidebar' )['TOOLBOX'];
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
		$ponchoPref = [
			'type' => 'toggle',
			'hide-if' => [ '!==', 'skin', 'poncho' ],
			'section' => 'rendering/skin/skin-prefs',
		];

		$preferences['poncho-hide-sidebar'] = $ponchoPref + [
			'label-message' => 'poncho-hide-sidebar',
		];
		$preferences['poncho-dark-mode'] = $ponchoPref + [
			'type' => 'toggle',
			'label-message' => 'poncho-enable-dark-mode',
		];
		$preferences['poncho-read-mode'] = $ponchoPref + [
			'type' => 'toggle',
			'label-message' => 'poncho-enable-read-mode',
		];
	}

	/**
	 * Add classes to the body
	 */
	static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$bodyAttrs ) {
		$user = $skin->getUser();
		$request = $skin->getRequest();
		$services = MediaWikiServices::getInstance();
		$userOptionsLookup = $services->getUserOptionsLookup();
		$hideSidebar = $user->isAnon() ? $request->getCookie( 'PonchoHideSidebar' ) :
			$userOptionsLookup->getOption( $user, 'poncho-hide-sidebar' );
		if ( $hideSidebar ) {
			$bodyAttrs['class'] .= ' poncho-hide-sidebar';
		}
		$darkMode = $user->isAnon() ? $request->getCookie( 'PonchoDarkMode' ) :
			$userOptionsLookup->getOption( $user, 'poncho-dark-mode' );
		if ( $darkMode ) {
			$bodyAttrs['class'] .= ' poncho-dark-mode';
		}
		$readMode = $user->isAnon() ? $request->getCookie( 'PonchoReadMode' ) :
			$userOptionsLookup->getOption( $user, 'poncho-read-mode' );
		if ( $readMode ) {
			$bodyAttrs['class'] .= ' poncho-read-mode';
		}
	}

	function footer() {
		$elements = [];
		$links = $this->getFooterLinks();
		$places = $links['places'];
		foreach ( $places as $place ) {
			$elements[] = $this->get( $place );
		}
		global $wgRightsText, $wgRightsPage, $wgRightsUrl;
		if ( $wgRightsText ) {
			$elements[] = $this->getMsg( 'copyright', $wgRightsText, $wgRightsPage, $wgRightsUrl );
		}
		echo implode( ' · ', $elements );
	}

	/**
	 * Output the page
	 */
	function execute() {
		include 'Poncho.phtml';
	}
}
