<?php

namespace BlueSpice\CustomMenu\MenuEditor;

use MediaWiki\Extension\MenuEditor\Menu\MediawikiSidebar;
use MediaWiki\Title\Title;

class Header extends MediawikiSidebar {

	/**
	 * @inheritDoc
	 */
	public function appliesToTitle( Title $title ): bool {
		return $title->getNamespace() === NS_MEDIAWIKI &&
			$title->getDBkey() === 'CustomMenu/Header';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModule(): string {
		return 'ext.bluespice.custom-menu.menueditor';
	}

	/**
	 * @inheritDoc
	 */
	public function getJSClassname(): string {
		return 'ext.custommenu.menueditor.HeaderTree';
	}

	/**
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'custommenu';
	}
}
