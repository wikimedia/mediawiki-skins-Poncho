<?php

class SkinPoncho extends SkinTemplate {

	// @var string Needed for 1.35 support. This property and entire class can be removed in 1.36
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
		echo new MediaWiki\Widget\SearchInputWidget([
			'name' => 'search',
			'placeholder' => wfMessage( 'search' )
		]);
	}

	/**
	 * Print the attributes of the logo
	 */
	function logoAttributes() {
		global $wgLogo, $wgPonchoLogo;
		$attributes = Linker::tooltipAndAccesskeyAttribs( 'p-logo' );
		$attributes['href'] = htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] );
		$attributes['style'] = 'background-image: url("' . ( $wgPonchoLogo === false ? $wgLogo : $wgPonchoLogo ) . '");';
		$attributes = Xml::expandAttributes( $attributes );
		echo $attributes;
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
	 * Get the namespaces
	 */
	function getNamespaces() {
		$namespaces = [];
		if ( count( $this->data['content_navigation']['namespaces'] ) > 1 ) {
			$namespaces = $this->data['content_navigation']['namespaces'];
		}
		return $namespaces;
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
		unset( $actions[ 'view' ] ); // Remove the view action per useless
		return $actions;
	}

	/**
	 * Return the page tools for the footer
	 */
	function getTools() {
		return $this->get('sidebar')['TOOLBOX'];
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
		if ( $user->isLoggedIn() ) {
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
				if ( ! is_int( $key ) ) {
					continue;
				}
				$content = $item['*'];
				$id = "notification-$key";
				$text = strip_tags( $content['header'] );
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
		if ( ! $notifications ) {
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