<?php

class SkinPoncho extends SkinTemplate {

	var $skinname = 'poncho',
		$stylename = 'Poncho',
		$template = 'PonchoTemplate',
		$useHeadElement = true;

	/**
	 * @param $out OutputPage object
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );
		$out->addModuleStyles( 'skins.poncho' );
	}

	function initPage( OutputPage $out ) {
	    parent::initPage( $out );
	    $out->addModules( 'skins.poncho' );
	}

	static function onOutputPageBeforeHTML( &$out, &$text ) {
		$out->addMeta( 'viewport', 'width=device-width' );
	}
}

class PonchoTemplate extends BaseTemplate {

	/**
	 * Merge together the views, actions and variants
	 * because distinguishing them is not relevant
	 * Also remove the current action, per useless and confusing
	 */
	public function getActions() {
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
	 * Outputs the entire contents of the page
	 */
	public function execute() {
		$Title = $this->getSkin()->getTitle();
		$Request = $this->getSkin()->getRequest();
		include 'Poncho.phtml';
	}
}