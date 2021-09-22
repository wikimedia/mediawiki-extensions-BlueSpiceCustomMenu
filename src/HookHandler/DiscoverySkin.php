<?php

namespace BlueSpice\CustomMenu\HookHandler;

use BlueSpice\CustomMenu\Component\CustomMenuButton;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class DiscoverySkin implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$factory = MediaWikiServices::getInstance()->getService( 'BSCustomMenuFactory' );
		$menu = $factory->getMenu( 'header' );
		if ( !$menu ) {
			return;
		}
		$registry->register(
			'NavbarPrimaryItems',
			[
				"cm-bluespice-item" => [
					'factory' => function () use ( $menu ) {
						return new CustomMenuButton( $menu );
					}
				]
			]
		);
	}
}
