<?php

namespace BlueSpice\CustomMenu\Tests;

use FormatJson;
use MediaWikiIntegrationTestCase;
use MenuParser as GlobalMenuParser;
use Title;

/**
 *
 * @group Database
 */
class MenuParserTest extends MediaWikiIntegrationTestCase {

	public function addDBDataOnce() {
		$this->insertPage(
			'MenuParserTest',
			file_get_contents( __DIR__ . '/data/Menu.wiki' )
		);
	}

	/**
	 * @covers MenuParser::getNavigationSites
	 * @dataProvider provideGetNavigationSitesData
	 */
	public function testGetNavigationSites( $expected ) {
		$this->setMwGlobals( [
			'wgArticlePath' => '/wiki/$1'
		] );
		$title = Title::newFromText( 'MenuParserTest' );
		$parser = new GlobalMenuParser( $title );
		$this->assertEquals( $expected, $parser->getNavigationSites( $title ) );
	}

	/**
	 *
	 * @return array
	 */
	public function provideGetNavigationSitesData() {
		return [
			[
				FormatJson::decode( file_get_contents( __DIR__ . '/data/Menu.json' ), true )
			],
		];
	}
}
