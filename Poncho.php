<?php

class SkinPoncho extends SkinTemplate {

	public $skinname = 'poncho';

	public $template = 'PonchoTemplate';

	static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		if ( $skin->getUser()->getOption( 'skin' ) === 'poncho' ) {
			$out->enableOOUI();
			$out->addModuleStyles([
				'skins.poncho.styles',
				'oojs-ui-core.styles'
			]);
			$out->addModules( 'skins.poncho' );
			$out->addMeta( 'viewport', 'width=device-width' );
		}
	}
}

class PonchoTemplate extends BaseTemplate {

	/**
	 * Echo the search bar
	 */
	function searchInput() {
		echo new MediaWiki\Widget\SearchInputWidget([
			'name' => 'search'
		]);
	}

	/**
	 * Echo the User button
	 */
	function userButton() {
		echo new OOUI\ButtonWidget([
			'id' => 'user-button',
			'label' => wfMessage( 'poncho-my-account' )->text()
		]);
	}

	/**
	 *
	 */
	function skinPath() {
		global $wgResourceBasePath;
		echo $wgResourceBasePath . '/skins/Poncho/';
	}

	/**
	 * Customize the user menu
	 */
	function getUserMenu() {
		$userMenu = $this->getPersonalTools();
		unset( $userMenu['uls'] );
		unset( $userMenu['notifications-alert'] );
		unset( $userMenu['notifications-notice'] );
/*
		unset( $userMenu['userpage'] );
		unset( $userMenu['mytalk'] );
		unset( $userMenu['mycontris'] );
		unset( $userMenu['watchlist'] );
		unset( $userMenu['anontalk'] );
		unset( $userMenu['anoncontribs'] );
*/
		return $userMenu;
	}

	/**
	 * Merge together the views, actions and variants
	 * and remove the current action, per useless and confusing
	 */
	function getActions() {
		global $mediaWiki;
		$actions = array_merge(
			$this->data['content_navigation']['views'],
			$this->data['content_navigation']['actions'],
			$this->data['content_navigation']['variants'],
			$this->data['content_navigation']['namespaces']
		);
		$action = $mediaWiki->getAction();
		if ( $action === 'view' ) {
			unset( $actions['view'] ); // Remove the View action when viewing
		}
		unset( $actions['main'] ); // Remove the View action when viewing
		return $actions;
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