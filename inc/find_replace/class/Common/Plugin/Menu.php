<?php

namespace WPMechanic\KBFNR\Common\Plugin;

use WPMechanic\KBFNR\Common\Properties\Properties;
use WPMechanic\KBFNR\Container;

class Menu {

	/**
	 * @var Properties
	 */
	private $properties;
	/**
	 * @var PluginManagerBase
	 */
	private $plugin_manager_base;
	/**
	 * @var Assets
	 */
	private $assets;
	private $template;

	/**
	 * Menu constructor.
	 *
	 * @param PluginManagerBase $plugin_manager_base
	 */
	public function __construct(
		PluginManagerBase $plugin_manager_base
	) {

		$this->plugin_manager_base = $plugin_manager_base;
	}

	public function register() {


		add_action( 'admin_menu', array( $this, 'admin_head_connection_info' ) );



	}




	function admin_menu() {

		add_action( 'admin_head-settings_page_kbd_download', array( $this->plugin_manager_base, 'admin_head_connection_info' ) );
	}
}
