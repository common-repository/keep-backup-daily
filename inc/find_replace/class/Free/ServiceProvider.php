<?php

namespace WPMechanic\KBFNR\Free;

use WPMechanic\KBFNR\Free\Plugin\PluginManager;
use WPMechanic\KBFNR\Free\UI\Template;

class ServiceProvider extends \WPMechanic\KBFNR\ServiceProvider {

	/**'
	 * @var PluginManager
	 */
	public $plugin_manager;
	/**
	 * @var Template
	 */
	public $free_template;

	public function __construct() {
		parent::__construct();
		$this->plugin_manager = new PluginManager(
			$this->settings,
			$this->assets,
			$this->util,
			$this->table,
			$this->http,
			$this->filesystem,
			$this->multisite,
			$this->properties
		);

		$this->free_template = new Template(
			$this->settings,
			$this->util,
			$this->profile_manager,
			$this->filesystem,
			$this->table,
			$this->properties,
			$this->plugin_manager
		);
	}
}
