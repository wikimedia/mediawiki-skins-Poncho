<?php

class SkinPoncho extends SkinTemplate {

	var $skinname = 'poncho',
		$template = 'PonchoTemplate';

	static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$out->addModuleStyles( 'skins.poncho' );
		$out->addModuleScripts( 'skins.poncho' );
		$out->addMeta( 'viewport', 'width=device-width' );
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
		unset( $actions[ $action ] ); // Remove the current action (doesn't work with Move)
		return $actions;
	}

	/**
	 * Output the page
	 */
	function execute() {
		$Title = $this->getSkin()->getTitle();
		$Request = $this->getSkin()->getRequest();
		include 'Poncho.phtml';
	}
}