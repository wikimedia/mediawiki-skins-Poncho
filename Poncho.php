<?php

use MediaWiki\MediaWikiServices;

// Class needed for 1.35 support, can be removed in 1.36
class SkinPoncho extends SkinTemplate {
	public $template = 'PonchoTemplate';
}

class PonchoTemplate extends BaseTemplate {

	static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$out->enableOOUI();
		$out->addMeta( 'viewport', 'width=device-width,user-scalable=no' );
	}

	/**
	 * Print the search bar
	 */
	function searchInput() {
		echo new MediaWiki\Widget\SearchInputWidget( [
			'name' => 'search',
			'placeholder' => wfMessage( 'search' )
		] );
	}

	/**
	 * Print the Edit button or buttons
	 */
	function editButton() {
		$action = Action::getActionName( $this->getSkin()->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
		if ( array_key_exists( 've-edit', $this->data['content_navigation']['views'] ) ) {
			$button = $this->data['content_navigation']['views']['ve-edit'];
			echo new OOUI\ButtonWidget( [
				'label' => $button['text'],
				'href' => $button['href'],
				'flags' => [ 'primary', 'progressive' ],
				'id' => 'poncho-visual-edit-button'
			] );
		}
		if ( array_key_exists( 'edit', $this->data['content_navigation']['views'] ) ) {
			$button = $this->data['content_navigation']['views']['edit'];
			$visual = array_key_exists( 've-edit', $this->data['content_navigation']['views'] );
			echo new OOUI\ButtonWidget( [
				'label' => $button['text'],
				'href' => $button['href'],
				'flags' => $visual ? null : [ 'primary', 'progressive' ],
				'id' => 'poncho-edit-source-button'
			] );
		}
	}

	/**
	 * Print the Talk button
	 */
	function talkButton() {
		$title = $this->getSkin()->getTitle();
		if ( $title->isSpecialPage() ) {
			return;
		}
		$action = Action::getActionName( $this->getSkin()->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
		$namespaces = array_values( $this->data['content_navigation']['namespaces'] );
		$button = $title->isTalkPage() ? $namespaces[0] : $namespaces[1];
		echo new OOUI\ButtonWidget( [
		    'label' => $button['text'],
		    'href' => $button['href'],
		    'flags' => $button['class'] === 'new' ? 'destructive' : 'progressive'
		] );
	}

	/**
	 * Print the Print button
	 */
	function printButton() {
		global $wgPonchoPrintButton;
		if ( ! $wgPonchoPrintButton ) {
			return;
		}
		$title = $this->getSkin()->getTitle();
		if ( $title->isSpecialPage() ) {
			return;
		}
		$action = Action::getActionName( $this->getSkin()->getContext() );
		if ( $action !== 'view' ) {
			return;
		}
		echo new OOUI\ButtonWidget( [
		    'label' => wfMessage( 'poncho-print' )->plain(),
		    'href' => '#print'
		] );
	}

	/**
	 * Print the attributes of the logo
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
	 * Print the title
	 */
	function title() {
		$Title = $this->getSkin()->getTitle();
		if ( $Title->isSubpage() ) {
			$title = $Title->getSubpageText();
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
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
			echo $title;
		} else {
			echo $this->html( 'title' );
		}
	}

	/**
	 * Print the name of the site
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
	 * Get the actions
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
		if ( $user->isRegistered() ) {
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
				$notifications[$id] = $notification;
			}
		}
		if ( !$notifications ) {
			$notifications[] = [
				'id' => 'notification-0',
				'text' => wfMessage( 'echo-none' ),
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
	function getNotificationsNotice() {
		$userLinks = $this->getPersonalTools();
		return $userLinks['notifications-notice'];
	}

	/**
	 * Output the page
	 */
	function execute() {
		include 'Poncho.phtml';
	}
}
