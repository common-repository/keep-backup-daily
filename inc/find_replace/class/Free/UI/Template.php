<?php

namespace WPMechanic\KBFNR\Free\UI;

use WPMechanic\KBFNR\Common\Filesystem\Filesystem;
use WPMechanic\KBFNR\Common\Profile\ProfileManager;
use WPMechanic\KBFNR\Common\Properties\Properties;
use WPMechanic\KBFNR\Common\Settings\Settings;
use WPMechanic\KBFNR\Common\Sql\Table;
use WPMechanic\KBFNR\Common\Util\Util;
use WPMechanic\KBFNR\Common\UI\TemplateBase;
use WPMechanic\KBFNR\Free\Plugin\PluginManager;

class Template extends TemplateBase {

	/**
	 * @var PluginManager
	 */
	public $plugin_manager;

	public function __construct(
		Settings $settings,
		Util $util,
		ProfileManager $profile,
		Filesystem $filesystem,
		Table $table,
		Properties $properties,
		PluginManager $plugin_manager
	) {
		$this->plugin_manager = $plugin_manager;
		parent::__construct( $settings, $util, $profile, $filesystem, $table, $properties );
	}

	public function register(){
		add_action( 'kbfnr_after_advanced_options', array( $this, 'mf_migration_form_controls' ) );
	}

	function template_import_radio_button( $loaded_profile ) {
		$args = array(
			'loaded_profile' => $loaded_profile,
		);
		$this->template( 'import-radio-button', 'kbfnr', $args );
	}

	function template_pull_push_radio_buttons( $loaded_profile ) {
		$args = array(
			'loaded_profile' => $loaded_profile,
		);
		$this->template( 'pull-push-radio-buttons', 'kbfnr', $args );
	}

	/**
	 * Adds the media settings to the migration setting page in core
	 */
	function mf_migration_form_controls() {
		$this->template( 'addon-upgrades', 'kbfnr', [] );
	}

}
