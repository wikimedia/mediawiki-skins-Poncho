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
	 * Print the attributes of the logo
	 */
	function logoAttributes() {
		global $wgLogo;
		$attributes = Linker::tooltipAndAccesskeyAttribs( 'p-logo' );
		$attributes['href'] = htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] );
		$attributes['style'] = 'background-image: url("' . $wgLogo . '");';
		$attributes = Xml::expandAttributes( $attributes );
		echo $attributes;
	}

	/**
	 * Print the path to the skin
	 */
	function skinPath() {
		global $wgResourceBasePath;
		echo $wgResourceBasePath . '/skins/Poncho/';
	}

	/**
	 * Return the main menu of the header
	 */
	function getMainMenu() {
		return array_shift( $this->data['sidebar'] );
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
	 * Select the main actions from the lot
	 * and remove the current action, per useless and confusing
	 */
	function getMainActions() {
		$actions = [];
		// Visual edit
		if ( array_key_exists( 've-edit', $this->data['content_navigation']['views'] ) ) {
			$actions[] = $this->data['content_navigation']['views']['ve-edit'];
		}
		// Source edit
		if ( array_key_exists( 'edit', $this->data['content_navigation']['views'] ) ) {
			$actions[] = $this->data['content_navigation']['views']['edit'];
		}
		// Talk page
		// The talk page has a different key in each namespace
		if ( count( $this->data['content_navigation']['namespaces'] ) > 1 ) {
			$actions[] = array_pop( $this->data['content_navigation']['namespaces'] );
		}
		return $actions;
	}

	/**
	 * Select the other actions
	 */
	function getMoreActions() {
		$actions = [];
		if ( array_key_exists( 've-edit', $this->data['content_navigation']['views'] ) ) {
			$actions[] = $this->data['content_navigation']['views']['edit'];
		}
		if ( array_key_exists( 'history', $this->data['content_navigation']['views'] ) ) {
			$actions[] = $this->data['content_navigation']['views']['history'];
		}
		$actions = array_merge(
			$actions,
			$this->data['content_navigation']['actions'],
			$this->data['content_navigation']['variants']
		);
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