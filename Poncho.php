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
		global $wgPonchoLogo, $wgLogos, $wgLogo;
		if ( !empty( $wgPonchoLogo ) ) {
			$logo = $wgPonchoLogo;
		} elseif ( !empty( $wgLogos['wordmark']['src'] ) ) {
			$logo = $wgLogos['wordmark']['src'];
		} else {
			$logo = $wgLogo;
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
		if ( $Title->isTalkPage() && $Title->getSubjectPage()->exists() ) {
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
		global $wgSitename, $wgPonchoSitename;
		echo $wgPonchoSitename === false ? $wgSitename : $wgPonchoSitename;
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
		return $this->data['sidebar']['LANGUAGES'];
	}

	/**
	 * Return the view options
	 */
	function getViewOptions() {
		$viewOptions = [];

		$skin = $this->getSkin();
		$user = $skin->getUser();
		$request = $skin->getRequest();

		$darkMode = $user->isAnon() ? $request->getCookie( 'PonchoDarkMode' ) : $user->getOption( 'poncho-dark-mode' );
		$viewOptions['dark-mode'] = [
			'id' => 'poncho-dark-mode',
			'text' => $darkMode ? wfMessage( 'poncho-disable-dark-mode' ) : wfMessage( 'poncho-enable-dark-mode' ),
			'class' => 'text',
		];

		$readMode = $user->isAnon() ? $request->getCookie( 'PonchoReadMode' ) : $user->getOption( 'poncho-read-mode' );
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
	 * Get the latest notifications
	 * process them and return them in a format fit for BaseTemplate::makeListItem
	 *
	 * @todo This method uses an internal API call which should be replaced by the proper Echo classes
	 */
	function getNotifications() {
		$notifications = [];
		$user = $this->getSkin()->getUser();
		if ( $this->data['newtalk'] ) {
			$id = 'usermessage';
			$link = [
				'id' => $id,
				'text' => wfMessage( 'poncho-new-message' ),
				'href' => $user->getUserPage()->getTalkPage()->getFullURL()
			];
			$notification = [
				'id' => $id,
				'links' => [ $link ],
				'class' => 'link',
				'active' => true,
			];
			$notifications[ $id ] = $notification;
		}
		if ( ExtensionRegistry::getInstance()->isLoaded( 'Echo' ) && $user->isRegistered() ) {
			global $wgRequest;
			$params = new DerivativeRequest(
				$wgRequest,
				[
					'action' => 'query',
					'meta' => 'notifications',
					'notformat' => 'model',
					'notlimit' => 10,
					'format' => 'json',
				]
			);
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
				$id = "notification-$key";
				$text = htmlspecialchars_decode( strip_tags( $content['header'] ), ENT_QUOTES );
				$href = $content['links']['primary']['url'] ?? null;
				$active = array_key_exists( 'read', $item ) ? false : true;
				$link = [
					'id' => $id,
					'text' => $text,
					'href' => $href,
				];
				$notification = [
					'id' => $id,
					'links' => [ $link ],
					'class' => $href ? 'link' : 'text',
					'active' => $active,
				];
				$notifications[ $id ] = $notification;
			}
		}
		if ( !$notifications ) {
			$notifications[] = [
				'id' => 'notification-0',
				'text' => wfMessage( 'poncho-no-notifications' ),
				'class' => 'text',
			];
		}
		return $notifications;
	}

	/**
	 * Get the notifications alert of the Echo extension
	 */
	function getNotificationsAlert() {
		$userLinks = $this->getPersonalTools();
		return $userLinks['notifications-alert'];
	}

	/**
	 * Get the notifications alert of the Echo extension
	 */
	static function getNotificationsNotice() {
		$userLinks = $this->getPersonalTools();
		return $userLinks['notifications-notice'];
	}

	/**
	 * Add preferences
	 */
	static function onGetPreferences( $user, &$preferences ) {
		$preferences['poncho-hide-sidebar'] = [
			'type' => 'toggle',
			'label-message' => 'poncho-hide-sidebar',
			'section' => 'rendering/skin',
		];
		$preferences['poncho-dark-mode'] = [
			'type' => 'toggle',
			'label-message' => 'poncho-enable-dark-mode',
			'section' => 'rendering/skin',
		];
		$preferences['poncho-read-mode'] = [
			'type' => 'toggle',
			'label-message' => 'poncho-enable-read-mode',
			'section' => 'rendering/skin',
		];
	}

	/**
	 * Add classes to the body
	 */
	static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$bodyAttrs ) {
		$user = $skin->getUser();
		$request = $skin->getRequest();
		$hideSidebar = $user->isAnon() ? $request->getCookie( 'PonchoHideSidebar' ) : $user->getOption( 'poncho-hide-sidebar' );
		if ( $hideSidebar ) {
			$bodyAttrs['class'] .= ' poncho-hide-sidebar';
		}
		$darkMode = $user->isAnon() ? $request->getCookie( 'PonchoDarkMode' ) : $user->getOption( 'poncho-dark-mode' );
		if ( $darkMode ) {
			$bodyAttrs['class'] .= ' poncho-dark-mode';
		}
		$readMode = $user->isAnon() ? $request->getCookie( 'PonchoReadMode' ) : $user->getOption( 'poncho-read-mode' );
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
		global $wgRightsText;
		if ( $wgRightsText ) {
			$elements[] = $this->getMsg( 'copyright', $wgRightsText );
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
