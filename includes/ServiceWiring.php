<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\MediaWikiServices;

return [

	'BSCustomMenuFactory' => static function ( MediaWikiServices $services ) {
		return new \BlueSpice\CustomMenu\Factory(
			new ExtensionAttributeBasedRegistry( 'BlueSpiceCustomMenuRegistry' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

];
