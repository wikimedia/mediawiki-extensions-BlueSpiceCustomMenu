<?php

namespace BlueSpice\CustomMenu\Component;

use BlueSpice\CustomMenu\ICustomMenu;
use HtmlArmor;
use MediaWiki\Context\IContextSource;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Sanitizer;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\RestrictedTextLink;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardBody;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownIcon;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLinklistGroupFromArray;
use MWStake\MediaWiki\Component\CommonUserInterface\IRestrictedComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;
use MWStake\MediaWiki\Component\DataStore\Record;
use MWStake\MediaWiki\Component\DataStore\RecordSet;

class CustomMenuButton extends SimpleDropdownIcon implements IRestrictedComponent {

	/** @var IContextSource */
	protected $context = null;

	/**
	 * @param ICustomMenu $menu
	 */
	public function __construct( private readonly ICustomMenu $menu ) {
		parent::__construct( [] );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function shouldRender( IContextSource $context ): bool {
		$this->context = $context;

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'cm-btn';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getRequiredRLStyles(): array {
		return [ "ext.bluespice.custom-menu.styles" ];
	}

	/**
	 * @return array
	 */
	public function getContainerClasses(): array {
		return [ 'has-megamenu' ];
	}

	/**
	 * @return array
	 */
	public function getButtonClasses(): array {
		return [ 'ico-btn' ];
	}

	/**
	 * @return array
	 */
	public function getMenuClasses(): array {
		return [ 'megamenu' ];
	}

	/**
	 * @return array
	 */
	public function getIconClasses(): array {
		return [ 'bi-grid-3x3-gap-fill' ];
	}

	/**
	 * @return Message
	 */
	public function getTitle(): Message {
		return $this->context->msg( 'bs-custommenu-navbar-button-title' );
	}

	/**
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return $this->context->msg( 'bs-custommenu-navbar-button-aria-label' );
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$items = $this->populateItems( $this->menu->getData()->getRecords() );

		// Insert placeholder text
		if ( empty( $items ) ) {
			$items = [
				new SimpleCard( [
					'id' => "cm-menu-0",
					'classes' => [ 'card-mn' ],
					'items' => [
						new SimpleCardBody( [
							'id' => "cm-menu-0-head",
							'classes' => [ 'menu-title' ],
							'items' => [
								new Literal(
									"cm-empty-menu",
									Html::element( 'div', [ 'id' => 'cm-empty-menu', 'class' => 'cm-empty-menu' ] )
								),
								new Literal(
									"cm-menu-title-0",
									Html::element( 'p', [],
										Message::newFromKey( "bs-custommenu-no-entries-label" )->escaped() )
								)
							]
						] )
					]
				] )
			];
		}

		if ( !empty( $this->menu->getEditURL() ) ) {
			$items[] = new RestrictedTextLink( [
				'role' => 'link',
				'id' => "{$this->getId()}-edit-link",
				'href' => $this->menu->getEditURL(),
				'text' => $this->context->msg( 'bs-custommenu-editlink-text' ),
				'classes' => [ 'mm-edit-link' ],
				'title' => $this->context->msg( 'bs-custommenu-editlink-title' ),
				'aria-label' => $this->context->msg( 'bs-custommenu-editlink-title' ),
				'permissions' => [ 'editinterface' ]
			] );
		}

		return [
			new SimpleCard( [
				'id' => 'cm-mm',
				'classes' => [
					'mega-menu', 'd-flex', 'justify-content-center'
				],
				'items' => [
					new SimpleCardBody( [
						'id' => 'cm-mm-megamn-body',
						'classes' => [ 'd-flex', 'mega-menu-wrapper' ],
						'items' => $items
					] )
				]
			] ),
			new Literal(
				'cm-mm-div',
				Html::element( 'div', [ 'id' => 'cm-mm-div', 'class' => 'mm-bg' ] )
			)
		];
	}

	/**
	 * Populate the items for the menu from the records.
	 *
	 * @param Record[] $records
	 *
	 * @return array
	 */
	private function populateItems( array $records ): array {
		$items = [];
		foreach ( $records as $record ) {
			if ( !$record->get( 'children', false ) instanceof RecordSet ) {
				continue;
			}
			$text = $record->get( 'text', '' );
			if ( empty( $text ) ) {
				$text = $record->get( 'id', '' );
			}
			$text = HtmlArmor::getHtml( $text );
			$id = Sanitizer::escapeIdForAttribute( $record->get( 'id' ) );
			$items[] = new SimpleCard( [
				'id' => "cm-menu-$id",
				'classes' => [ 'card-mn' ],
				'items' => [
					new SimpleCardHeader( [
						'id' => "cm-menu-$id-head",
						'classes' => [ 'menu-title' ],
						'items' => [
							new Literal(
								"cm-menu-title-$id", $text
							)
						]
					] ),
					new SimpleLinklistGroupFromArray( [
						'id' => "cm-menu-list-items-$id",
						'classes' => [
							'menu-card-body',
							'menu-list',
							'll-dft'
						],
						'links' => $this->getRecordLinkDefinition( $record ),
						'role' => 'group',
						'item-role' => 'presentation',
						'aria' => [
							'labelledby' => "cm-menu-$id-head"
						],
					] )
				]
			] );
		}

		return $items;
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions(): array {
		return [ 'read' ];
	}

	/**
	 * @param Record $record
	 * @return array
	 */
	private function getRecordLinkDefinition( $record ): array {
		$links = [];
		foreach ( $record->get( 'children' )->getRecords() as $child ) {
			$id = Sanitizer::escapeIdForAttribute( $child->get( 'id', '' ) );
			$text = $child->get( 'text', '' );
			if ( empty( $text ) ) {
				$text = $child->get( 'id', '' );
			}
			$links[$id] = [
				'id' => $id,
				'href' => $child->get( 'href', '' ),
				'text' => $text,
				'title' => $text,
				'aria-label' => $text,
			];
		}
		$services = MediaWikiServices::getInstance();
		/** @var LinkFormatter */
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );
		return $linkFormatter->formatLinks( $links );
	}

}
