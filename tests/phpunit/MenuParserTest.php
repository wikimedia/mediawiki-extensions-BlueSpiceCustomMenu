<?php

namespace BlueSpice\CustomMenu\Tests;

use MediaWiki\Json\FormatJson;
use MediaWiki\MainConfigNames;
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
		$this->assertEquals( $expected, $actual );
	}
}
