<?php

namespace BlueSpice\CustomMenu\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddMigrateTopBarMenu extends LoadExtensionSchemaUpdates {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance( \BSCustomMenuMigrateTopBarMenu::class );
		return true;
	}

}
