<?php

namespace BlueSpice\CustomMenu\Renderer\Menu;

use MediaWiki\Linker\LinkRenderer;
use BlueSpice\Renderer\Params;
use BlueSpice\Data\RecordSet;

class Item extends \BlueSpice\CustomMenu\Renderer\Menu {
	const PARAM_HREF = 'href';
	const PARAM_CHILDREN = 'children';
	const PARAM_LEVEL = 'level';
	const PARAM_EXTERNAL = 'external';
	const PARAM_CONTAINS_ACTIVE = 'containsactive';
	const PARAM_IS_ACTIVE = 'active';
	const PARAM_TEXT = 'text';

	/**
	 *
	 * @param \Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 */
	public function __construct( \Config $config, Params $params, LinkRenderer $linkRenderer = null ) {
		parent::__construct( $config, $params, $linkRenderer );
		$this->args[static::PARAM_TAG] = 'li';
		$this->args[static::PARAM_HREF] = $params->get(
			static::PARAM_HREF,
			'#'
		);
		$this->args[static::PARAM_CHILDREN] = $params->get(
			static::PARAM_CHILDREN,
			false
		);
		$this->args[static::PARAM_LEVEL] = $params->get(
			static::PARAM_LEVEL,
			0
		);
		$this->args[static::PARAM_EXTERNAL] = $params->get(
			static::PARAM_EXTERNAL,
			false
		);
		$this->args[static::PARAM_CONTAINS_ACTIVE] = $params->get(
			static::PARAM_CONTAINS_ACTIVE,
			false
		);
		$this->args[static::PARAM_IS_ACTIVE] = $params->get(
			static::PARAM_IS_ACTIVE,
			false
		);
		$this->args[static::PARAM_TEXT] = $params->get(
			static::PARAM_TEXT,
			''
		);
		if (
			empty( $this->args[static::PARAM_TEXT] )
			&& !empty( $this->args[static::PARAM_ID] )
		) {
			$this->args[static::PARAM_TEXT] = $this->args[static::PARAM_ID];
		}
		$this->args[static::PARAM_CLASS]
			.= " level-{$this->args[static::PARAM_LEVEL]}";
	}

	protected function makeTagContent() {
		$content = '';
		$content .= \Html::element( 'a', [
			static::PARAM_HREF => $this->args[static::PARAM_HREF],
			'title' => $this->args[static::PARAM_TEXT]
		], $this->args[static::PARAM_TEXT] );

		if ( !$this->args[static::PARAM_CHILDREN] instanceof RecordSet ) {
			return $content;
		}
		$level = $this->args[static::PARAM_LEVEL] + 1;
		$content .= \Html::openElement( 'ul', [
			static::PARAM_CLASS => " child-menu level-$level",
		] );
		$counter = 0;
		$menu = $this->getCustomMenu();
		if ( $menu->numberOfLevels() >= $level ) {
			foreach ( $this->args[static::PARAM_CHILDREN]->getRecords() as $record ) {
				$counter ++;
				$content .= $this->renderItem( $record );
				if ( $menu->numberOfSubEntries() == $menu::NUM_ENTRIES_UNLIMITED ) {
					continue;
				}
				if ( $menu->numberOfSubEntries() <= $counter ) {
					break;
				}
			}
		}
		$content .= \Html::closeElement( 'ul' );

		return $content;
	}

}
