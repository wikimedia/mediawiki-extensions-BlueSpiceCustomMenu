<?php

namespace BlueSpice\CustomMenu\Hook\PageContentSaveComplete;

class InvalidateHeaderMenu extends \BlueSpice\Hook\PageContentSaveComplete {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$title = \Title::makeTitle(
			NS_MEDIAWIKI,
			// 'TopBarMenu' in the past
			"CustomMenu/Header"
		);
		if ( !$this->wikipage->getTitle()->equals( $title ) ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$menu = $this->getServices()->getService( 'BSCustomMenuFactory' )
			->getMenu( 'header' );
		if ( !$menu ) {
			return true;
		}
		$menu->invalidate();
		return true;
	}

}
