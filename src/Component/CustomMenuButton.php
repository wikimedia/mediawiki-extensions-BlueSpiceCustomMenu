<?php

namespace BlueSpice\CustomMenu\Component;

use BlueSpice\CustomMenu\ICustomMenu;
use BlueSpice\Data\Record;
use Html;
use IContextSource;
use Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownIcon;
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
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param ICustomMenu $menu
	 */
	public function __construct( ICustomMenu $menu ) {
		$this->menu = $menu;
		parent::__construct( [] );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function shouldRender( IContextSource $context ) : bool {
		$this->context = $context;
		return !empty( $this->menu->getData()->getRecords() );
	}

	/**
	 * @inheritDoc
	 */
	public function getId() : string {
		return 'cm-btn';
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
						'id' => "cm-menu-head-$id",
						'classes' => [ 'menu-title' ],
						'items' => [
							new Literal(
								"cm-menu-title-$id",
								$text
							)
						]
					] ),
					new Literal(
						"cm-menu-list-items-$id",
						$this->getRecordHtml( $record )
					),
				]
			] );
		}
		return [
			new SimpleCard( [
				'id' => 'cm-mm',
				'classes' => [
					'mecm-menu',
					'async',
					'd-flex',
					'justify-content-center',
					'flex-row'
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
	 * @return string
	 */
	private function getRecordHtml( $record ) : string {
		if ( empty( $record->get( 'children' )->getRecords() ) ) {
			return '';
		}
		$id = Sanitizer::escapeIdForAttribute( $record->get( 'id' ) );
		$html = Html::openElement( 'ul', [
			'id' => "cm-menu-children-$id",
			'aria-label-by' => "cm-menu-head-$id",
			'class' => 'ist-group menu-card-body menu-list'
		] );

		foreach ( $record->get( 'children' )->getRecords() as $child ) {
			$text = $child->get( 'text', '' );
			if ( empty( $text ) ) {
				$text = $child->get( 'id' );
			}
			$html .= Html::openElement( 'li' );
			$html .= Html::element( 'a', [
				'href' => $child->get( 'href', '' ),
				'title' => $text,
				'arial-label' => $text,
			], $text );
			$html .= Html::closeElement( 'li' );
		}
		$html .= Html::closeElement( 'ul' );
		return $html;
	}
}
