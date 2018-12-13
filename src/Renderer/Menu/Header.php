<?php

namespace BlueSpice\CustomMenu\Renderer\Menu;

use BlueSpice\Data\Record;
use BlueSpice\Services;
use BlueSpice\Renderer\Params;

class Header extends \BlueSpice\CustomMenu\Renderer\Menu {
	/**
	 * @param Record $record
	 * @return string
	 */
	protected function renderItem( Record $record ) {
		$params = array_merge(
			$record->getData(),
			[ static::PARAM_CUSTOM_MENU => $this->getCustomMenu() ]
		);
		return Services::getInstance()->getBSRendererFactory()->get(
			'custommenuheaderitem',
			new Params( $params )
		)->render();
	}
}
