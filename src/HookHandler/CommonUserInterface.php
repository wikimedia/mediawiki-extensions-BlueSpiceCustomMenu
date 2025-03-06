<?php

namespace BlueSpice\CustomMenu\HookHandler;

use BlueSpice\CustomMenu\Component\CustomMenuButton;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$menu = $this->getServices()->getService( 'BSCustomMenuFactory' )->getMenu( 'header' );
		if ( !$menu ) {
			return;
		}
		$registry->register(
			'NavbarPrimaryItems',
			[
				"cm-bluespice-item" => [
					'factory' => static function () use ( $menu, ) {
						return new CustomMenuButton( $menu );
					}
				]
			]
		);
	}

	/**
	 *
	 * @return MediaWikiServices
	 */
	private function getServices() {
		return MediaWikiServices::getInstance();
	}
}
