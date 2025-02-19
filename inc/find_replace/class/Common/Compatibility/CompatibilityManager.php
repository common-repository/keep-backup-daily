<?php

namespace WPMechanic\KBFNR\Common\Compatibility;

use WPMechanic\KBFNR\Common\Filesystem\Filesystem;
use WPMechanic\KBFNR\Common\Http\Http;
use WPMechanic\KBFNR\Common\MigrationState\MigrationStateManager;
use WPMechanic\KBFNR\Common\Properties\Properties;
use WPMechanic\KBFNR\Common\Settings\Settings;
use WPMechanic\KBFNR\Common\UI\Notice;
use WPMechanic\KBFNR\Common\UI\TemplateBase;
use WPMechanic\KBFNR\Common\Util\Util;

/**
 * Class CompatibilityManager
 *
 * Class to handle the copying and removing of the Compatibility Mode MU plugin for Pro
 *
 */
class CompatibilityManager {

	/**
	 * @var string
	 */
	public $mu_plugin_source;
	/**
	 * @var string
	 */
	public $mu_plugin_dest;
	/**
	 * @var Filesystem
	 */
	public $filesystem;
	/**
	 * @var
	 */
	public $settings;
	/**
	 * @var string
	 */
	public $compatibility_plugin_version;
	/**
	 * @var string
	 */
	public $mu_plugin_dir;
	/**
	 * @var Properties
	 */
	public $props;
	/**
	 * @var Properties
	 */
	public static $static_props;
	/**
	 * @var Notice
	 */
	public $notices;
	/**
	 * @var TemplateBase
	 */
	public $template;
	/**
	 * @var Http
	 */
	public $http;
	/**
	 * @var MigrationStateManager
	 */
	public $migration_state;

	public function __construct(
		Filesystem $filesystem,
		Settings $settings,
		Notice $notice,
		Http $http,
		TemplateBase $template,
		MigrationStateManager $migration_state,
		Util $util,
		Properties $properties
	) {

		$this->filesystem      = $filesystem;
		$this->settings        = $settings->get_settings();
		$this->props           = $properties;
		self::$static_props    = $this->props;
		$this->template        = $template;
		$this->notices         = $notice;
		$this->http            = $http;
		$this->migration_state = $migration_state;
		$this->util            = $util;


		//Version of the compatibility plugin, to force an update of the MU plugin, increment this value
		$this->compatibility_plugin_version = '1.2';

		$this->mu_plugin_dir    = $this->props->mu_plugin_dir;
		$this->mu_plugin_source = $this->props->mu_plugin_source;
		$this->mu_plugin_dest   = $this->props->mu_plugin_dest;
	}

	public function register() {
		//Checks the compatibility mode MU plugin version and updates if it's out of date
		add_action( 'wp_ajax_kbfnr_plugin_compatibility', array( $this, 'ajax_plugin_compatibility' ) );
//		add_action( 'admin_init', array( $this, 'muplugin_version_check' ), 1 );
		add_action( 'kbfnr_notices', array( $this, 'template_muplugin_update_fail' ) );
		//Fired in the register_deactivation_hook() call in both the pro and non-pro plugins
		add_action( 'kb_migrate_db_remove_compatibility_plugin', array( $this, 'remove_muplugin_on_deactivation' ) );
	}

	/**
	 * Triggered with the `admin_init` hook on the Pro dashboard page
	 *
	 * The 'compatibility_plugin_version' option key signifies that the latest compatibility plugin has been installed. If it's not present, copy the plugin, enabling it by default.
	 *
	 * Otherwise check the 'compatibility_plugin_version' option to see if the MU plugin needs updating.
	 *
	 * @return bool|string
	 */
	public function muplugin_version_check() {

	}

	/**
	 * Checks if the compatibility mu-plugin requires an update based on the 'compatibility_plugin_version' setting in
	 * the database
	 *
	 * @param bool $kbfnr_settings
	 *
	 * @return bool
	 */
	public function is_muplugin_update_required( $kbfnr_settings = false ) {
		$update_required = false;

		if ( false === $kbfnr_settings ) {
			$kbfnr_settings = $this->settings;
		}

		if ( ! isset( $kbfnr_settings['compatibility_plugin_version'] ) ) {
			$update_required = true;
		} else if ( version_compare( $this->compatibility_plugin_version, $kbfnr_settings['compatibility_plugin_version'], '>' ) && $this->util->is_muplugin_installed() ) {
			$update_required = true;
		}

		return $update_required;
	}

	/**
	 * Preemptively shows a warning warning on kbfnr pages if the mu-plugins folder isn't writable
	 */
	function template_muplugin_update_fail() {
		if ( $this->is_muplugin_update_required() && false === $this->util->is_muplugin_writable() ) {
			$notice_links = $this->notices->check_notice( 'muplugin_failed_update_' . $this->compatibility_plugin_version, 'SHOW_ONCE' );
			if ( is_array( $notice_links ) ) {
				$this->template->template( 'muplugin-failed-update-warning', 'common', $notice_links );
			}
		}
	}

	/**
	 * Handler for ajax request to turn on or off Compatibility Mode.
	 */
	public function ajax_plugin_compatibility() {
		$this->http->check_ajax_referer( 'plugin_compatibility' );
		$message = false;

		$key_rules      = array(
			'action'  => 'key',
			'install' => 'numeric',
		);
		$state_data     = $this->migration_state->set_post_data( $key_rules );
		$do_install     = ( '1' === trim( $state_data['install'] ) ) ? true : false;
		$plugin_toggled = $this->toggle_muplugin( $do_install );

		//If there's an error message, display it
		if ( true !== $plugin_toggled ) {
			$message = $plugin_toggled;
		}

		$this->http->end_ajax( $message );
	}


	/**
	 *
	 * Toggles the compatibility plugin based on the $do_install param.
	 *
	 * @param $do_install
	 *
	 * @return bool|string|void
	 */
	public function toggle_muplugin( $do_install ) {
		if ( true === $do_install ) {
			return $this->copy_muplugin();
		} else {
			return $this->remove_muplugin();
		}
	}

	/**
	 *
	 * Copies the compatibility plugin as well as updates the version number in the database
	 *
	 * @return bool|string
	 */
	public function copy_muplugin() {
		$kbfnr_settings = $this->settings;

		// Make the mu-plugins folder if it doesn't already exist, if the folder does exist it's left as-is.
		if ( ! $this->filesystem->mkdir( $this->mu_plugin_dir ) ) {
			return sprintf( esc_html__( 'The following directory could not be created', 'wpkbd' ).': %s', $this->mu_plugin_dir );
		}

		if ( ! $this->filesystem->copy( $this->mu_plugin_source, $this->mu_plugin_dest ) ) {
			return sprintf( __( 'The compatibility plugin could not be activated because your mu-plugin directory is currently not writable.', 'wpkbd' ).'  '.__('Please update the permissions of the mu-plugins folder', 'wpkbd' ).':  %s', $this->mu_plugin_dir );
		}

		//Rename muplugin in header
		if ( ! $this->props->is_pro ) {
			$mu_contents = file_get_contents( $this->mu_plugin_dest );
			$mu_contents = str_replace( 'Plugin Name: Compatibility', 'Plugin Name: Compatibility', $mu_contents );			file_put_contents( $this->mu_plugin_dest, $mu_contents );
		}

		if ( $this->is_muplugin_update_required() ) {
			// Update version number in the database
			$kbfnr_settings['compatibility_plugin_version'] = $this->compatibility_plugin_version;

			// Remove blacklist_plugins key as it's no longer used.
			if ( isset( $kbfnr_settings['blacklist_plugins'] ) ) {
				unset( $kbfnr_settings['blacklist_plugins'] );
			}

			update_site_option( 'kbfnr_settings', $kbfnr_settings );
		}

		return true;
	}

	/**
	 *
	 * Removes the compatibility plugin
	 *
	 * @return bool|string
	 */
	public function remove_muplugin() {
		if ( $this->filesystem->file_exists( $this->mu_plugin_dest ) && ! $this->filesystem->unlink( $this->mu_plugin_dest ) ) {
			return sprintf( __( 'The compatibility plugin could not be deactivated because your mu-plugin directory is currently not writable.', 'wpkbd' ).'  '.__('Please update the permissions of the mu-plugins folder', 'wpkbd' ).': %s', $this->mu_plugin_dir );
		}

		return true;
	}

	/**
	 *
	 * Fired on the `kb_migrate_db_remove_compatibility_plugin` action. Removes the compatibility plugin on deactivation
	 *
	 * @return bool|string
	 */
	public function remove_muplugin_on_deactivation() {
		$plugin_removed = $this->remove_muplugin();

		if ( true === $plugin_removed ) {
			$kbfnr_settings = $this->settings;
			unset( $kbfnr_settings['compatibility_plugin_version'] );

			update_site_option( 'kbfnr_settings', $kbfnr_settings );

			return true;
		}

		return $plugin_removed;
	}
}
