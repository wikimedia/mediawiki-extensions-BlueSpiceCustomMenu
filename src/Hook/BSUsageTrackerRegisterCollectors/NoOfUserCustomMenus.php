<?php

namespace BlueSpice\CustomMenu\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class NoOfUserCustomMenus extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:usercustommenu'] = [
			'class' => 'Database',
			'config' => [
				'identifier' => 'no-of-user-custom-menu',
				'descKey' => 'no-of-user-custom-menu',
				'table' => 'page',
				'uniqueColumns' => [ 'page_title' ],
				'condition' => [ 'page_namespace' => NS_MEDIAWIKI,
				'page_title like "CustomMenu/%"'
				]
			]
		];
	}

}
