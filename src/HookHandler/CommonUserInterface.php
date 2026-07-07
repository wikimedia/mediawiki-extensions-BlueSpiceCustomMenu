<?php

namespace BlueSpice\CustomMenu\HookHandler;

use BlueSpice\CustomMenu\Component\CustomMenuButton;
use BlueSpice\CustomMenu\Factory;
use Config;
use MediaWiki\Config\ConfigFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;
use RequestContext;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents {

	/** @var Config */
	private Config $config;

	/**
	 * @param Factory $menuFactory
	 * @param ConfigFactory $configFactory
	 */
	public function __construct(
		private readonly Factory $menuFactory,
		ConfigFactory $configFactory
	) {
		$this->config = $configFactory->makeConfig( 'bsg' );
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$menu = $this->menuFactory->getMenu( 'header' );
		if ( !$menu ) {
			return;
		}

		if ( !$this->config->get( 'ShowCustomMenuHeader' ) ) {
			return;
		}
		$skin = RequestContext::getMain()->getSkin();
		if ( is_a( $skin, 'SkinBlueSpiceEclipseSkin', true ) ) {
			$registry->register(
				'NavbarPrimaryCenterItems',
				[
					"z-custommenu-item" => [
						'factory' => static function () use ( $menu, ) {
							return new CustomMenuButton( $menu );
						}
					]
				]
			);
		}
		if ( is_a( $skin, 'BlueSpice\Discovery\Skin', true ) ) {
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
	}
}
