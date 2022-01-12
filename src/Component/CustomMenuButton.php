<?php

namespace BlueSpice\CustomMenu\Component;

use BlueSpice\CustomMenu\ICustomMenu;
use BlueSpice\Data\Record;
use BlueSpice\Data\RecordSet;
use Html;
use IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownIcon;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLinklistGroupFromArray;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleTextLink;
use MWStake\MediaWiki\Component\CommonUserInterface\IRestrictedComponent;
use Sanitizer;

class CustomMenuButton extends SimpleDropdownIcon implements IRestrictedComponent {

	/**
	 *
	 * @var ICustomMenu
	 */
	protected $menu = null;

	/**
	 *
	 * @var PermissionManager
	 */
	protected $permissionManager = null;

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param ICustomMenu $menu
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( ICustomMenu $menu, PermissionManager $permissionManager ) {
		$this->menu = $menu;
		$this->permissionManager = $permissionManager;
		parent::__construct( [] );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function shouldRender( IContextSource $context ) : bool {
		$this->context = $context;
		if ( empty( $this->menu->getData()->getRecords() ) ) {
			return false;
		}
		foreach ( $this->menu->getData()->getRecords() as $record ) {
			if ( !$record->get( 'children', false ) instanceof RecordSet ) {
				continue;
			}
			return true;
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getId() : string {
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
	public function getContainerClasses() : array {
		return [ 'has-megamenu' ];
	}

	/**
	 * @return array
	 */
	public function getButtonClasses() : array {
		return [ 'ico-btn' ];
	}

	/**
	 * @return array
	 */
	public function getMenuClasses() : array {
		return [ 'megamenu' ];
	}

	/**
	 * @return array
	 */
	public function getIconClasses() : array {
		return [ 'bi-grid-3x3-gap-fill' ];
	}

	/**
	 * @return Message
	 */
	public function getTitle() : Message {
		return $this->context->msg( 'bs-custommenu-navbar-button-title' );
	}

	/**
	 * @return Message
	 */
	public function getAriaLabel() : Message {
		return $this->context->msg( 'bs-custommenu-navbar-button-aria-label' );
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents() : array {
		$items = [];
		foreach ( $this->menu->getData()->getRecords() as $record ) {
			if ( !$record->get( 'children', false ) instanceof RecordSet ) {
				continue;
			}
			$text = $record->get( 'text', '' );
			if ( empty( $text ) ) {
				$text = $record->get( 'id' );
			}
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
								"cm-menu-title-$id",
								$text
							)
						]
					] ),
					new SimpleLinklistGroupFromArray( [
						'id' => "cm-menu-list-items-$id",
						'classes' => [ 'menu-card-body', 'menu-list', 'll-dft' ],
						'links' => $this->getRecordLinkDefinition( $record ),
						'aria' => [
							'labelledby' => "cm-menu-list-items-$id-head"
						],
					] )
				]
			] );
		}
		$isAllowedEdit = $this->permissionManager->userHasRight(
			$this->context->getUser(),
			'editinterface'
		);
		if ( $isAllowedEdit && !empty( $this->menu->getEditURL() ) ) {
			$items[] = new SimpleTextLink( [
				'role' => 'link',
				'id' => "{$this->getId()}-edit-link",
				'href' => $this->menu->getEditURL(),
				'text' => $this->context->msg( 'bs-custommenu-editlink-text' ),
				'title' => $this->context->msg( 'bs-custommenu-editlink-title' ),
				'aria-label' => $this->context->msg( 'bs-custommenu-editlink-title' ),
			] );
		}
		return [
			new SimpleCard( [
				'id' => 'cm-mm',
				'classes' => [
					'mega-menu',
					'async',
					'd-flex',
					'justify-content-center',
					'flex-md-row',
					'flex-lg-row',
					'flex-xl-row',
					'flex-xxl-row'
				],
				'items' => $items
			] ),
			new Literal(
				'cm-mm-div',
				Html::element( 'div', [ 'id' => 'cm-mm-div', 'class' => 'mm-bg' ] )
			)
		];
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
			$id = Sanitizer::escapeIdForAttribute( $child->get( 'id' ) );
			$text = $child->get( 'text', '' );
			if ( empty( $text ) ) {
				$text = $child->get( 'id' );
			}
			$links[$id] = [
				'id' => $id,
				'href' => $child->get( 'href', '' ),
				'text' => $text,
				'title' => $text,
				'aria-label' => $text
			];
		}

		$services = MediaWikiServices::getInstance();
		/** @var LinkFormatter */
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );
		return $linkFormatter->formatLinks( $links );
	}

}
