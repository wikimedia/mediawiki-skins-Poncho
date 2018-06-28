<?php

class SkinPoncho extends SkinTemplate {

	public $skinname = 'poncho',
		$template = 'PonchoTemplate';

	/**
	 * Initializes OutputPage and sets up skin-specific parameters
	 *
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		$out->addMeta( 'viewport', 'width=device-width' );

		$out->addModuleScripts( 'skins.poncho.scripts' );
	}

	/**
	 * @param OutputPage $out
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		$out->addModuleStyles( 'skins.poncho.styles' );
	}
}

class PonchoTemplate extends BaseTemplate {

	/**
	 * Merge together the views, actions and variants
	 * and remove the current action, per useless and confusing
	 */
	function getActions() {
		global $mediaWiki;
		$actions = array_merge(
			$this->data['content_navigation']['views'],
			$this->data['content_navigation']['actions'],
			$this->data['content_navigation']['variants']
		);
		$action = $mediaWiki->getAction();
		unset( $actions[$action] ); // Remove the current action (doesn't work with Move)
		return $actions;
	}

	/**
	 * Output the page
	 */
	function execute() {
		include 'Poncho.phtml';
	}
}