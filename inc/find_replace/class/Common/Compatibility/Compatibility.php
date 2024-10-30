<?php

namespace WPMechanic\KBFNR\Common\Compatibility;

class Compatibility {

	/**
	 * @var string
	 */
	protected $muplugin_class_dir;
	/**
	 * @var string
	 */
	protected $muplugin_dir;
	/**
	 * @var
	 */
	protected $default_whitelisted_plugins;

	public function __construct() {

		$this->muplugin_class_dir = plugin_dir_path( __FILE__ );
		$this->muplugin_dir       = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';


	}

	/**
	 * During the `kbfnr_flush` and `kbfnr_remote_flush` actions, start output buffer in case theme spits out errors
	 */
	public function kbfnrc_plugins_loaded() {
		if ( $this->kbfnrc_is_kbfnr_flush_call() ) {
			ob_start();
		}
	}

	/**
	 * During the `kbfnr_flush` and `kbfnr_remote_flush` actions, if buffer isn't empty, log content and flush buffer.
	 */
	public function kbfnrc_after_theme_setup() {
		if ( $this->kbfnrc_is_kbfnr_flush_call() ) {
			if ( ob_get_length() ) {
				error_log( ob_get_clean() );
			}
		}
	}

	/**
	 *
	 * Disables the theme during MDB AJAX requests
	 *
	 * Called from the `stylesheet_directory` hook
	 *
	 * @param $stylesheet_dir
	 *
	 * @return string
	 */
	public function kbfnrc_disable_theme( $stylesheet_dir ) {
		$force_enable_theme = apply_filters( 'kbfnr_compatibility_enable_theme', false );

		if ( $this->kbfnrc_is_compatibility_mode_request() && ! $force_enable_theme ) {
			$theme_dir  = realpath( dirname( __FILE__ ) . '/../compatibility' );
			$stylesheet = 'temp-theme';
			$theme_root = "$theme_dir/$stylesheet";

			return $theme_root;
		}

		return $stylesheet_dir;
	}

	public function kbfnrc_set_default_whitelist() {

		// Allow users to filter whitelisted plugins
		$filtered_plugins = apply_filters( 'kbfnr_compatibility_plugin_whitelist', array() );

		// List of default plugins that should be whitelisted. Can be partial names or slugs
		$kbfnr_plugins = array(
			'kbfnr', // Some tweaks plugins start with this string
			'wpkbd',
		);

		$plugins                           = array_merge( $filtered_plugins, $kbfnr_plugins );
		$this->default_whitelisted_plugins = $plugins;
	}

	/**
	 * Remove TGM Plugin Activation 'force_activation' admin_init action hook if present.
	 *
	 * This is to stop excluded plugins being deactivated after a migration, when a theme uses TGMPA to require a
	 * plugin to be always active. Also applies to the WDS-Required-Plugins by removing `activate_if_not` action
	 */
	public function kbfnrc_tgmpa_compatibility() {
		$remove_function = false;

		// run on kbfnr page
		if ( isset( $_GET['page'] ) && '' == $_GET['page'] ) {
			$remove_function = true;
		}
		// run on kbfnr ajax requests
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['action'] ) && false !== strpos( $_POST['action'], 'kbfnr' ) ) {
			$remove_function = true;
		}

		if ( $remove_function ) {
			global $wp_filter;
			$admin_init_functions = $wp_filter['admin_init'];
			foreach ( $admin_init_functions as $priority => $functions ) {
				foreach ( $functions as $key => $function ) {
					// searching for function this way as can't rely on the calling class being named TGM_Plugin_Activation
					if ( false !== strpos( $key, 'force_activation' ) || false !== strpos( $key, 'activate_if_not' ) ) {

						if ( is_array( $wp_filter['admin_init'] ) ) {
							// for core versions prior to WP 4.7
							unset( $wp_filter['admin_init'][ $priority ][ $key ] );
						} else {
							unset( $wp_filter['admin_init']->callbacks[ $priority ][ $key ] );
						}

						return;
					}
				}
			}
		}
	}

	/**
	 * remove blog-active plugins
	 *
	 * @param array $plugins numerically keyed array of plugin names
	 *
	 * @return array
	 */
	public function kbfnrc_include_plugins( $plugins ) {
		if ( ! is_array( $plugins ) || empty( $plugins ) ) {
			return $plugins;
		}

		if ( ! $this->kbfnrc_is_compatibility_mode_request() ) {
			return $plugins;
		}

		$whitelist_plugins = $this->kbfnrc_get_whitelist_plugins();
		$default_whitelist = $this->default_whitelisted_plugins;

		foreach ( $plugins as $key => $plugin ) {
			if ( true === $this->kbfnrc_plugin_in_default_whitelist( $plugin, $default_whitelist ) || isset( $whitelist_plugins[ $plugin ] ) ) {
				continue;
			}

			unset( $plugins[ $key ] );
		}

		return $plugins;
	}

	/**
	 * remove network-active plugins
	 *
	 * @param array $plugins array of plugins keyed by name (name=>timestamp pairs)
	 *
	 * @return array
	 */
	public function kbfnrc_include_site_plugins( $plugins ) {
		if ( ! is_array( $plugins ) || empty( $plugins ) ) {
			return $plugins;
		}

		if ( ! $this->kbfnrc_is_compatibility_mode_request() ) {
			return $plugins;
		}

		$whitelist_plugins = $this->kbfnrc_get_whitelist_plugins();

		if ( ! $this->default_whitelisted_plugins ) {
			$this->kbfnrc_set_default_whitelist();
		}

		$default_whitelist = $this->default_whitelisted_plugins;

		foreach ( array_keys( $plugins ) as $plugin ) {
			if ( true === $this->kbfnrc_plugin_in_default_whitelist( $plugin, $default_whitelist ) || isset( $whitelist_plugins[ $plugin ] ) ) {
				continue;
			}
			unset( $plugins[ $plugin ] );
		}

		return $plugins;
	}
	/**
	 *
	 * Checks if the current request is a kbfnr request
	 *
	 * @return bool
	 */
	public function is_kbfnr_ajax_call() {
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ( isset( $_POST['action'] ) && false !== strpos( $_POST['action'], 'kbfnr' ) ) ) {
			return true;
		}

		return false;
	}
	/**
	 * @return bool
	 */
	public function kbfnrc_is_kbfnr_ajax_call() {
		return $this->is_kbfnr_ajax_call();
	}

	/**
	 * @return bool
	 */
	public function kbfnrc_is_kbfnr_flush_call() {
		if ( $this->kbfnrc_is_kbfnr_ajax_call() && in_array( $_POST['action'], array(
				'kbfnr_flush',
				'kbfnr_remote_flush',
			) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Should the current request be processed by Compatibility Mode?
	 *
	 * @return bool
	 */
	public function kbfnrc_is_compatibility_mode_request() {
		//Requests that shouldn't be handled by compatibility mode
		if ( ! $this->kbfnrc_is_kbfnr_ajax_call() || in_array( $_POST['action'], array(
				'kbfnr_get_log',
				'kbfnr_maybe_collect_data',
				'kbfnr_flush',
				'kbfnr_remote_flush',
				'kbfnr_get_themes',
				'kbfnr_get_plugins',
			) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns an array of plugin slugs to be blacklisted.
	 *
	 * @return array
	 */
	public function kbfnrc_get_whitelist_plugins() {
		$whitelist_plugins = array();

		$kbfnr_settings = get_site_option( 'kbfnr_settings' );

		if ( ! empty( $kbfnr_settings['whitelist_plugins'] ) ) {
			$whitelist_plugins = array_flip( $kbfnr_settings['whitelist_plugins'] );
		}

		return $whitelist_plugins;
	}

	/**
	 *
	 * Checks if $plugin is in the $whitelisted_plugins property array
	 *
	 * @param $plugin
	 * @param $whitelisted_plugins
	 *
	 * @return bool
	 */
	public function kbfnrc_plugin_in_default_whitelist( $plugin, $whitelisted_plugins ) {

		if ( ! is_array( $whitelisted_plugins ) ) {
			return false;
		}

		if ( in_array( $plugin, $whitelisted_plugins ) ) {
			return true;
		}

		// strpos() check to see if the item slug is in the current $plugin name
		foreach ( $whitelisted_plugins as $item ) {
			if ( false !== strpos( $plugin, $item ) ) {
				return true;
			}
		}

		return false;
	}
}
