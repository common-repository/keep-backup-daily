<?php

namespace WPMechanic\KBFNR\Common\MigrationState;

use WPMechanic\KBFNR\Common\Util\Singleton;

/**
 * Class StateDataContainer
 *
 * Singleton class to store `$this->state_data` usage throughout codebase
 *
 * Generally, state_data is set in MigrationStateManager::set_post_data();
 *
 * @TODO In future, refactor usage of state_data globally
 *
 * @package WPMechanic\KBFNR\Common\MigrationState
 */
class StateDataContainer {
	use Singleton;

	public $state_data = [];
	public $migration_state_manager;

	public function __construct( ) { }

	public function setData( $data ) {
		$this->state_data = $data;
	}

	public function getData() {
		return $this->state_data;
	}
}
