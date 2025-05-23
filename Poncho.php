<?php

use MediaWiki\Extension\Notifications\Mapper\NotificationMapper;
use MediaWiki\Extension\Notifications\DataOutputFormatter;
use MediaWiki\Html\Html;
use MediaWiki\Linker\Linker;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Title\Title;

// @todo Migrate to SkinMustache
class SkinPoncho extends SkinTemplate {
	public $template = 'Poncho';
}

class Poncho extends BaseTemplate {

	/**
	 * Add preferences
	 */
	static function onGetPreferences( $user, &$preferences ) {
		$preferences['poncho-dark-mode'] = [
			'hide-if' => [ '!==', 'skin', 'Poncho' ],
			'section' => 'rendering/skin/skin-prefs',
			'type' => 'toggle',
			'label-message' => 'poncho-enable-dark-mode',
		];
		$preferences['poncho-read-mode'] = [
			'hide-if' => [ '!==', 'skin', 'Poncho' ],
			'section' => 'rendering/skin/skin-prefs',
			'type' => 'toggle',
			'label-message' => 'poncho-enable-read-mode',
		];
	}

	/**
	 * Add classes to the body
	 */
	static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$bodyAttrs ) {
		if ( $skin->getSkinName() !== 'Poncho' ) {
			return; // Don't run for other skins
		}
		$user = $skin->getUser();
		$request = $skin->getRequest();
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

		echo $logo;
	}

	function getMainActions() {
		$actions = $this->data['content_navigation']['views'];

		// Unset the current action per generally useless
		$skin = $this->getSkin();
		$context = $skin->getContext();
		$action = Action::getActionName( $context );
		unset( $actions[ $action ] );

		return $actions;
	}

	/**
	 * Echo the content action buttons
	 */
	function makeActionButton( $key, $action ) {
		$icons = [
			'view' => 'article',
			'viewsource' => 'wikiText',
			'edit' => 'wikiText',
			've-edit' => 'edit',
			'history' => 'history',
			'addsection' => 'add',
		];
		$icon = $action['icon'] ?? $icons[ $key ] ?? null;
		return new OOUI\ButtonWidget( [
			'id' => $action['id'],
			'label' => $icon ? '' : $action['text'],
			'title' => $icon ? $action['text'] : '',
			'href' => $action['href'],
			'icon' => $icon,
			'framed' => false
		] );
	}

	/**
	 * Echo the More button
	 */
	function moreButton() {
		$menu = $this->getMoreMenu();
		if ( !$menu ) {
			return;
		}
		$skin = $this->getSkin();
		$context = $skin->getContext();
		$action = Action::getActionName( $context );
		echo new OOUI\ButtonWidget( [
			'id' => 'poncho-more-button',
			'title' => $skin->msg( 'poncho-more' )->plain(),
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
	 * Return the main menu of the header
	 */
	function getNavigationMenu() {
		$sidebar = $this->data['sidebar'];
		$navigation = reset( $sidebar );
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
			'text' => $darkMode ? $skin->msg( 'poncho-disable-dark-mode' ) : $skin->msg( 'poncho-enable-dark-mode' ),
			'class' => 'text',
		];

		$readMode = $user->isAnon() ? $request->getCookie( 'PonchoReadMode' ) : $userOptionsLookup->getOption( $user, 'poncho-read-mode' );
		$userMenu['read-mode'] = [
			'id' => 'poncho-read-mode',
			'text' => $readMode ? $skin->msg( 'poncho-disable-read-mode' ) : $skin->msg( 'poncho-enable-read-mode' ),
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
				'text' => $this->getSkin()->msg( 'poncho-no-languages' ),
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
				'text' => $skin->msg( 'poncho-new-message' ),
				'href' => $user->getUserPage()->getTalkPage()->getFullURL()
			];
			$item = [
				'links' => [ $link ],
				'class' => 'link',
				'active' => true,
			];
			$notifications[] = $item;
		}
		if ( ExtensionRegistry::getInstance()->isLoaded( 'Echo' ) ) {
			$attributeManager = MediaWikiServices::getInstance()->getService( 'EchoAttributeManager' );
			$events = $attributeManager->getUserEnabledEvents( $user, 'web' );
			$notificationMapper = new NotificationMapper;
			$notifs = $notificationMapper->fetchByUser( $user, 10, null, $events );
			$language = $this->getSkin()->getLanguage();
			foreach ( $notifs as $notif ) {
				$notification = DataOutputFormatter::formatOutput( $notif, 'model', $user, $language );
				$content = $notification['*'];
				$text = htmlspecialchars_decode( strip_tags( $content['header'] ), ENT_QUOTES );
				$href = $content['links']['primary']['url'] ?? null;
				$active = array_key_exists( 'read', $notification ) ? false : true;
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
			if ( ExtensionRegistry::getInstance()->isLoaded( 'Echo' ) ) {
				$link = [
					'text' => $skin->msg( 'echo-none' ),
					'href' => Title::newFromText( 'Special:Notifications' )->getFullURL()
				];
				$item = [
					'links' => [ $link ],
					'class' => 'link',
				];
			} else {
				$item = [
					'text' => $skin->msg( 'poncho-no-notifications' ),
					'class' => 'text'
				];
			}
			$notifications[] = $item;
		}
		return $notifications;
	}

	/**
	 * Echo the talk page button
	 */
	function talkButton() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		$talk = $title->getTalkPageIfDefined();
		if ( $talk && !$title->isTalkPage() ) {
			echo new OOUI\ButtonWidget( [
				'id' => 'poncho-talk-button',
				'label' => $skin->msg( 'talk' )->plain(),
				'href' => $talk->getLinkURL(),
				'title' => $skin->msg( 'viewtalkpage' )->plain(),
				'icon' => 'userTalk'
			] );
		}
	}

	/**
	 * Echo the footer
	 */
	function footer() {
		$footer = '';
		$links = $this->getFooterLinks();
		foreach ( $links as $key => $items ) {
			$list = [];
			foreach ( $items as $item ) {
				$list[] = $this->get( $item );
			}
			if ( $key === 'info' ) {
				$separator = ' ';
			} else {
				$separator = ' · ';
			}
			$footer .= '<div>' . implode( $separator, $list ) . '</div>';
		}
		echo $footer;
	}

	/**
	 * Output the page
	 */
	function execute() {
		$context = RequestContext::getMain();
		$out = $context->getOutput();
		$out->enableOOUI();
		include 'Poncho.phtml';
	}
}
