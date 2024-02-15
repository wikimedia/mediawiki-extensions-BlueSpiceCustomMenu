<?php

namespace BlueSpice\CustomMenu\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class NoOfCustomMegaMenus extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['custommegamenu'] = [
			'class' => 'Database',
			'config' => [
				'identifier' => 'no-of-custom-mega-menus',
				'internalDesc' => 'Number of Custom Mega Menus',
				'table' => 'page',
				'uniqueColumns' => 'page_title',
				'condition' => [
					'page_namespace' => NS_MEDIAWIKI,
					'page_title LIKE "CustomMenu/%"'
				]
			]
		];
	}

}
