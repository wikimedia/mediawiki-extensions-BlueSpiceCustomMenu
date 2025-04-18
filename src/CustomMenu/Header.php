<?php

namespace BlueSpice\CustomMenu\CustomMenu;

use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MenuParser;
use MWStake\MediaWiki\Component\DataStore\Record;
use MWStake\MediaWiki\Component\DataStore\RecordSet;

class Header extends \BlueSpice\CustomMenu\CustomMenu {

	/**
	 *
	 * @return Record[]
	 */
	protected function getRecords() {
		$title = Title::makeTitle(
			NS_MEDIAWIKI,
			// 'TopBarMenu' in the past
			"CustomMenu/Header"
		);

		if ( $title && $title->exists() ) {
			$menuObj = new MenuParser();
			$menu = $menuObj->getNavigationSites( $title );
			$records = [];
			foreach ( $menu as $entry ) {
				$records[] = $this->legacyParserItemToRecord( $entry );
			}
			return $records;
		}
		return $this->getDefaultRecords();
	}

	/**
	 * @return Menu
	 */
	public function getRenderer() {
		return MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'custommenuheader',
			$this->getParams()
		);
	}

	/**
	 *
	 * @param Record[] $records
	 * @return Record[]
	 */
	protected function getDefaultRecords( $records = [] ) {
		$currentTitle = RequestContext::getMain()->getTitle();
		$mainPage = Title::newMainPage();
		$active = $currentTitle ? $currentTitle->equals( $mainPage ) : false;
		$menu = [ [
			'id' => 'nt-wiki',
			'href' => $mainPage->getFullURL(),
			'text' => $this->config->get( 'Sitename' ),
			'active' => $active,
			'level' => 1,
			'containsactive' => false,
			'external' => false,
			'children' => [],
		] ];
		// legacy hook
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSTopMenuBarCustomizerRegisterNavigationSites',
			[
				&$menu
			]
		);

		foreach ( $menu as $entry ) {
			$records[] = $this->legacyParserItemToRecord( $entry );
		}
		return parent::getDefaultRecords( $records );
	}

	/**
	 *
	 * @param array $entry
	 * @return Record
	 */
	protected function legacyParserItemToRecord( $entry ) {
		if ( !empty( $entry['children'] ) ) {
			$children = [];
			foreach ( $entry['children'] as $child ) {
				$children[] = $this->legacyParserItemToRecord( $child );
			}
			$entry['children'] = new RecordSet( $children );
		}

		return new Record( (object)$entry );
	}

	public function numberOfLevels() {
		return $this->config->get( 'CustomMenuHeaderNumberOfLevels' );
	}

	public function numberOfMainEntries() {
		return $this->config->get( 'CustomMenuHeaderNumberOfMainEntries' );
	}

	public function numberOfSubEntries() {
		return $this->config->get( 'CustomMenuHeaderNumberOfSubEntries' );
	}

	/**
	 *
	 * @return string
	 */
	public function getEditURL() {
		$title = Title::makeTitle(
			NS_MEDIAWIKI,
			"CustomMenu/Header"
		);

		return $title->getEditURL();
	}

}
