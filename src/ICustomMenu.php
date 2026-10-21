<?php

namespace BlueSpice\CustomMenu;

use MWStake\MediaWiki\Component\DataStore\RecordSet;

interface ICustomMenu {
	public const NUM_ENTRIES_UNLIMITED = -1;

	/**
	 * @return \BlueSpice\Renderer
	 */
	public function getRenderer();

	/**
	 * @return RecordSet
	 */
	public function getData();

	/**
	 * @return string
	 */
	public function getKey();

	/**
	 * @return null
	 */
	public function invalidate();

	/**
	 * @return int
	 */
	public function numberOfLevels();

	/**
	 * @return int
	 */
	public function numberOfMainEntries();

	/**
	 * @return int
	 */
	public function numberOfSubEntries();

	/**
	 * @return string|null
	 */
	public function getEditURL();

	/**
	 * Message key for an explanatory text shown below the "no entries" label
	 *
	 * @return string|null
	 */
	public function getEmptyMenuDescriptionMessageKey();
}
