<?php

namespace BlueSpice\CustomMenu\Hook\OutputPageBodyAttributes;

class AddOutputPageBodyClass {

	/**
	 * Adds extra classes to custom menu body
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @param array &$bodyAttrs
	 */
	public function onOutputPageBodyAttributes( \OutputPage $out, \Skin $skin, &$bodyAttrs ) {
		$class = 'bs-cusom-menu-active';

		$items = explode( ' ', $bodyAttrs[ 'class' ] );

		$classes = [];
		foreach ( $items as $item ) {
			if ( ( $item === '' ) || ( $item === ' ' ) ) {
					continue;
			}
			$classes[] = trim( $item );
		}

		if ( !in_array( $class, $classes ) ) {
			$bodyAttrs[ 'class' ] .= ' ' . $class . ' ';
		}
	}
}
