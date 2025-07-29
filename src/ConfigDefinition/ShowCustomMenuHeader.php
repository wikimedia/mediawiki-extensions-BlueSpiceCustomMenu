<?php

namespace BlueSpice\CustomMenu\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;
use BlueSpice\ConfigDefinition\IOverwriteGlobal;

class ShowCustomMenuHeader extends BooleanSetting implements IOverwriteGlobal {

	/**
	 * @return array
	 */
	public function getPaths(): array {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_SKINNING . '/BlueSpiceCustomMenu',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceCustomMenu/' . static::FEATURE_SKINNING,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceCustomMenu',
		];
	}

	/**
	 * @return string
	 */
	public function getLabelMessageKey(): string {
		return "bs-pref-custommenu-show-custom-menu-header";
	}

	/**
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-pref-custommenu-show-custom-menu-header-help';
	}

	/**
	 * @inheritDoc
	 */
	public function getGlobalName(): string {
		return "bsgShowCustomMenuHeader";
	}
}
