<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Sanitizer;
use MediaWiki\Title\Title;

class MenuParser {

	/**
	 * @var Title
	 */
	private $currentTitle = null;

	/**
	 * @var Title
	 */
	protected $sourceTitle = null;

	/**
	 * @param Title|null $currentTitle
	 */
	public function __construct( $currentTitle = null ) {
		$this->currentTitle = $currentTitle;
		if ( $this->currentTitle == null ) {
			$this->currentTitle = RequestContext::getMain()->getTitle();
		}
	}

	/** @var array */
	public static $aNavigationSiteTemplate = [
		'id' => '',
		'href' => '',
		'text' => '',
		'active' => false,
		'level' => 1,
		'containsactive' => false,
		'external' => false,
	];

	/**
	 * Getter for $aNavigationSites array
	 * @param Title|null $title
	 * @return array
	 */
	public function getNavigationSites( ?Title $title ) {
		if ( !$this->currentTitle ) {
			return [];
		}
		$menu = [];

		if ( !$title || !$title->exists() ) {
			return $menu;
		}
		$this->sourceTitle = $title;
		$sContent = BsPageContentProvider::getInstance()
			->getContentFromTitle( $title );

		$aLines = explode( "\n", trim( $sContent ) );

		$menu = $this->parseArticleContentLines(
			$aLines,
			// scaling will be done on rendering
			9999,
			// scaling will be done on rendering
			9999,
			// scaling will be done on rendering
			9999
		);
		return $menu;
	}

	/**
	 * Returns recursively all parsed menu items
	 * TODO: Clean up
	 * @param array $aLines
	 * @param int $iAllowedLevels
	 * @param int $iMaxMainEntries
	 * @param int $iMaxSubEntries
	 * @param array $aApps
	 * @param int $iPassed
	 * @return array
	 */
	protected function parseArticleContentLines(
		$aLines,
		$iAllowedLevels = 2,
		$iMaxMainEntries = 5,
		$iMaxSubEntries = 20,
		$aApps = [],
		$iPassed = 0
	) {
		$iMaxEntrys = ( $iPassed === 0 ) ? $iMaxMainEntries - 1 : $iMaxSubEntries - 1;

		if ( $iAllowedLevels < 1 || $iMaxEntrys < 1 ) {
			return $aApps;
		}

		$iPassed++;
		$aChildLines = [];
		$iCount = count( $aLines );
		$i = 0;
		for ( $i; $i < $iCount; $i++ ) {
			$aLines[$i] = trim( $aLines[$i] );
			// prevents from lines without * and list starts without parent item
			if ( strpos( $aLines[$i], '*' ) !== 0 || ( strpos( $aLines[$i], '**' ) === 0 && $i == 0 ) ) {
				continue;
			}

			if ( strpos( $aLines[$i], '**' ) === 0 ) {
				if ( $iPassed < $iAllowedLevels ) {
					$aChildLines[] = substr( $aLines[$i], 1 );
				}
				continue;
			}
			if ( !empty( $aChildLines ) ) {
				$iLastKey = key( array_slice( $aApps, -1, 1, true ) );
				$aApps[$iLastKey]['children'] = $this->parseArticleContentLines(
					$aChildLines,
					$iAllowedLevels,
					$iMaxMainEntries,
					$iMaxSubEntries,
					[],
					$iPassed
				);
				foreach ( $aApps[$iLastKey]['children'] as $aChildApps ) {
					if ( !$aChildApps['active'] && !$aChildApps['containsactive'] ) {
						continue;
					}
					$aApps[$iLastKey]['containsactive'] = true;
					break;
				}
				$aChildLines = [];
			}

			if ( count( $aApps ) > $iMaxEntrys ) {
				continue;
			}

			$aApp = $this->parseSingleLine( substr( $aLines[$i], 1 ) );
			if ( empty( $aApp ) ) {
				continue;
			}

			$aApp['level'] = $iPassed;
			$aApps[] = $aApp;
		}
		// add childern to the last element
		if ( !empty( $aChildLines ) ) {
			$iLastKey = key( array_slice( $aApps, -1, 1, true ) );
			$aApps[$iLastKey]['children'] = $this->parseArticleContentLines( $aChildLines,
				$iAllowedLevels,
				$iMaxMainEntries,
				$iMaxSubEntries,
				[],
				$iPassed
			);
			foreach ( $aApps[$iLastKey]['children'] as $aChildApps ) {
				if ( !$aChildApps['active'] && !$aChildApps['containsactive'] ) {
					continue;
				}
				$aApps[$iLastKey]['containsactive'] = true;
				break;
			}
		}

		return $aApps;
	}

	/** @var int */
	private static $idCounter = 0;

	/**
	 * @return string|false
	 */
	private function makeId() {
		$base = strtolower( $this->sourceTitle->getPrefixedDBkey() );
		$id = Sanitizer::escapeIdForAttribute( $base . static::$idCounter++ );
		return $id;
	}

	/**
	 * Parses a single menu item
	 * TODO: Clean up
	 * @param string $sLine
	 * @return array - Single parsed menu item (app)
	 */
	public function parseSingleLine( $sLine ) {
		$services = MediaWikiServices::getInstance();
		$config = $services->getConfigFactory()->makeConfig( 'bsg' );
		$newApp = static::$aNavigationSiteTemplate;

		$aAppParts = explode( '|', trim( $sLine ) );
		foreach ( $aAppParts as $key => $val ) {
			$aAppParts[$key ] = trim( $val );
		}
		if ( empty( $aAppParts[0] ) ) {
			return [];
		}

		// Just a text label -> `MediaWiki:Sidebar` compatible syntax
		if ( count( $aAppParts ) === 1 ) {
			$newApp['id'] = $this->makeId();
			$newApp['text'] = $aAppParts[0];
			return $newApp;
		}

		// Explicit ID omitted -> `MediaWiki:Sidebar` compatible syntax
		if ( count( $aAppParts ) === 2 ) {
			array_unshift( $aAppParts, $this->makeId() );
		}

		$newApp['id'] = $aAppParts[0];
		if ( !empty( $aAppParts[1] ) ) {
			// `wfParseUrl` already checks against `$wgUrlProtocols`.
			// If the protocol is not allowed, it will return `false`
			$urlUtils = $services->getUrlUtils();
			$aParsedUrl = $urlUtils->parse( $aAppParts[1] );
			if ( $aParsedUrl !== null ) {
				if ( preg_match( '# |\\*#', $aParsedUrl['host'] ) ) {
					// TODO: Use status ojb on BeforeArticleSave to detect parse errors
				}

				$sQuery = !empty( $aParsedUrl['query'] ) ? '?' . $aParsedUrl['query'] : '';
				if ( !isset( $aParsedUrl['path'] ) ) {
					$aParsedUrl['path'] = '';
				}

				$newBaseUrl = $aParsedUrl['scheme'] . $aParsedUrl['delimiter'] . $aParsedUrl['host'];
				if ( isset( $aParsedUrl['port'] ) ) {
					$newBaseUrl .= ':' . $aParsedUrl['port'];
				}

				$newApp['href'] = $newBaseUrl . $aParsedUrl['path'] . $sQuery;

				if ( isset( $aParsedUrl['fragment'] ) ) {
					$newApp['href'] .= '#' . $aParsedUrl['fragment'];
				}

				$newApp['external'] = true;
			} elseif ( strpos( $aAppParts[1], '?' ) === 0 ) {
				// ?action=blog
				$newApp['href'] = $config->get( 'Server' )
					. $config->get( 'ScriptPath' )
					. '/' . $aAppParts[1];
			} else {
				$oTitle = Title::newFromText( trim( $aAppParts[1] ) );
				if ( $oTitle === null || !$this->isTitleReadable( $oTitle ) ) {
					return [];
				} else {
					$newApp['href'] = $this->getTitleUrl( $oTitle );
					$newApp['_title'] = $oTitle;

					if ( $oTitle->equals( $this->currentTitle ) ) {
						$newApp['active'] = true;
					}
				}
			}
		} else {
			$newApp['href'] = $config->get( 'Server' ) . $config->get( 'ScriptPath' );
		}

		if ( !empty( $aAppParts[2] ) ) {
			$newApp['text'] = $aAppParts[2];
		}

		return $newApp;
	}

	/**
	 * @param Title $title
	 * @return string
	 */
	protected function getTitleUrl( Title $title ): string {
		$url = $title->getLocalURL();
		if ( $title->hasFragment() ) {
			$url .= $title->getFragmentForURL();
		}
		return $url;
	}

	/**
	 * Returns wikitext list from recursively processed array
	 * @param array $aNavigationSites
	 * @param string $sWikiText
	 * @param string $sPrefix
	 * @return string
	 */
	public function toWikiText( $aNavigationSites, $sWikiText = '', $sPrefix = '*' ) {
		foreach ( $aNavigationSites as $aNavigationSite ) {
			$sText = $sHref = '';

			if ( !empty( $aNavigationSite['href'] ) ) {
				$sHref = '|';

				if ( !isset( $aNavigationSite['external'] ) || !$aNavigationSite['external'] ) {
					// extract Title from url - maybe not 100% accurate
					global $wgArticlePath;
					$aInternalUrl = explode(
						// remove $1
						substr( $wgArticlePath, 0, -2 ),
						// Added A - url could be relative
						'A' . $aNavigationSite['href']
					);

					if ( !isset( $aInternalUrl[1] ) ) {
						$sHref .= $aNavigationSite['href'];
					} else {
						$sHref .= $aInternalUrl[1];
						/* TODO: Remove query - not yet needed
						if( strpos($aInternalUrl[1], '?') !== false ) {
							$sHref .= substr(
								$aInternalUrl[1],
								0,
								strpos( $aInternalUrl[1], '?')
							);
						} elseif( strpos($aInternalUrl[1], '&' ) !== false) {
							$sHref .= substr(
								$aInternalUrl[1],
								0,
								strpos( $aInternalUrl[1], '&')
							);
						}*/
					}
				} else {
					$sHref .= $aNavigationSite['href'];
				}
				if ( !empty( $aNavigationSite['text'] ) ) {
					$sText = '|' . $aNavigationSite['text'];
				}
			}

			$sWikiText .= "$sPrefix{$aNavigationSite['id']}$sHref$sText\n";
			if ( empty( $aNavigationSite['children'] ) ) {
				continue;
			}

			$sWikiText = $this->toWikiText( $aNavigationSite['children'], $sWikiText, "*$sPrefix" );
		}
		return $sWikiText;
	}

	/**
	 * @param Title $title
	 * @return true
	 */
	protected function isTitleReadable( Title $title ): bool {
		return true;
	}

}
