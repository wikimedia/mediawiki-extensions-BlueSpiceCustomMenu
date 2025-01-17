<?php

namespace BlueSpice\CustomMenu\Tests;

use FormatJson;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MenuParser as GlobalMenuParser;

/**
 *
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

		$this->setMwGlobals( [
			'wgServer' => 'https://somewiki.local',
			'wgScriptPath' => '/w',
			'wgArticlePath' => '/wiki/$1',
			'wgUrlProtocols' => [ 'http://', 'https://', 'tel:', 'mglof://' ]
		] );
		$inputWikiText = file_get_contents( __DIR__ . '/data/Menu.wiki' );

		$currentTitle = Title::newFromText( 'CurrentTitle' );
		$sourceTitle = Title::newFromText( 'MenuParserTest' );

		$parser = new GlobalMenuParser( $currentTitle );

		$expected = FormatJson::decode( file_get_contents( __DIR__ . '/data/Menu.json' ), true );
		$actual = $parser->getNavigationSites( $sourceTitle );
		$this->assertEquals( $expected, $actual );
	}
}
