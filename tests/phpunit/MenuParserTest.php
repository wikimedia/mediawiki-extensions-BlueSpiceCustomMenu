<?php

namespace BlueSpice\CustomMenu\Tests;

use MediaWiki\Json\FormatJson;
use MediaWiki\MainConfigNames;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MenuParser as GlobalMenuParser;

/**
 * @group Database
 */
class MenuParserTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers MenuParser::getNavigationSites
	 */
	public function testGetNavigationSites() {
		$this->insertPage(
			'MenuParserTest',
			file_get_contents( __DIR__ . '/data/Menu.wiki' )
		);

		$this->overrideConfigValues( [
			MainConfigNames::Server => 'https://somewiki.local',
			MainConfigNames::ScriptPath => '/w',
			MainConfigNames::ArticlePath => '/wiki/$1',
			MainConfigNames::UrlProtocols => [ 'http://', 'https://', 'tel:', 'mglof://' ],
		] );
		$inputWikiText = file_get_contents( __DIR__ . '/data/Menu.wiki' );

		$currentTitle = Title::newFromText( 'CurrentTitle' );
		$sourceTitle = Title::newFromText( 'MenuParserTest' );

		$parser = new GlobalMenuParser( $currentTitle );

		$expected = FormatJson::decode( file_get_contents( __DIR__ . '/data/Menu.json' ), true );
		$actual = $parser->getNavigationSites( $sourceTitle );
		$this->assertMenuTitles( $actual );
		$actual = $this->stripMenuTitles( $actual );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * @param array $menuItems
	 */
	private function assertMenuTitles( array $menuItems ) {
		foreach ( $menuItems as $menuItem ) {
			if ( isset( $menuItem['_title'] ) ) {
				$this->assertInstanceOf( Title::class, $menuItem['_title'] );
			}

			if ( isset( $menuItem['children'] ) ) {
				$this->assertMenuTitles( $menuItem['children'] );
			}
		}
	}

	/**
	 * @param array $menuItems
	 * @return array
	 */
	private function stripMenuTitles( array $menuItems ): array {
		foreach ( $menuItems as &$menuItem ) {
			unset( $menuItem['_title'] );

			if ( isset( $menuItem['children'] ) ) {
				$menuItem['children'] = $this->stripMenuTitles( $menuItem['children'] );
			}
		}

		return $menuItems;
	}
}
