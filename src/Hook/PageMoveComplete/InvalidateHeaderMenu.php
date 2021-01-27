<?php

namespace BlueSpice\CustomMenu\Hook\PageMoveComplete;

use BlueSpice\Hook\PageMoveComplete;
use Title;

class InvalidateHeaderMenu extends PageMoveComplete {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$title = Title::makeTitle(
			NS_MEDIAWIKI,
			// 'TopBarMenu' in the past
			"CustomMenu/Header"
		);
		$new = Title::newFromLinkTarget( $this->new );
		$old = Title::newFromLinkTarget( $this->old );
		if ( !$new || !$old ) {
			return true;
		}
		if ( !$new->equals( $title ) && !$old->equals( $title ) ) {
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
