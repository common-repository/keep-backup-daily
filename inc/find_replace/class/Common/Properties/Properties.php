<?php

namespace WPMechanic\KBFNR\Common\Properties;

use WPMechanic\KBFNR\Common\Util\Singleton;
use WPMechanic\KBFNR\Common\Util\Util;

/**
 * Class Properties
 *
 * This class acts as a global 'property store'. Legacy kbfnrPro had a confusing class hierarchy
 * with many class properties assigned all over. This class is intended to act as a 'storage' class
 * for these legacy properties.
 *
 * @TODO    Remove this class altogether with something more sane
 *
 * @package WPMechanic\KBFNR\Common
 */
class Properties {

	use Singleton;

	public $addons;
	public $plugin_file_path;
	public $plugin_dir_path;
	public $plugin_slug;
	public $plugin_folder_name;
	public $plugin_basename;
	public $plugin_base;
	public $plugin_version;
	public $template_dir;
	public $is_pro = false;
	public $plugin_title;
	public $mu_plugin_dir;
	public $mu_plugin_source;
	public $mu_plugin_dest;
	public $filesystem;
	public $core_slug;
	public $attempting_to_connect_to;
	public $temp_prefix = '_mig_';
	public $transient_timeout;
	public $transient_retry_timeout;
	public $dbrains_api_status_url = 'http://s3.amazonaws.com/cdn.deliciousbrains.com/status.json';
	public $multipart_boundary = 'bWH4JVmYCnf6GfXacrcc';
	public $invalid_content_verification_error;
	public $util;
	public $settingsClass;
	public $unhook_templates = [];

	public function __construct() {
		$is_pro = false;

		$this->transient_timeout       = 60 * 60 * 12;
		$this->transient_retry_timeout = 60 * 60 * 2;

		$pro_methods = [
			'kb_migrate_db_pro',
			'kb_migrate_db_pro_cli_addon_loaded',
			'kb_migrate_db_pro_media_files',
			'kb_migrate_db_pro_multisite_tools',
			'kb_migrate_db_pro_theme_plugin_files',
		];

		if ( ! Util::has_method_been_called( 'kb_migrate_db' ) ) {
			foreach ( $pro_methods as $method ) {
				if ( Util::has_method_been_called( $method ) || ( defined( 'DOING_kbfnr_TESTS' ) && DOING_kbfnr_TESTS ) ) {
					$is_pro = true;
				}
			}
		}


		$this->invalid_content_verification_error = __( 'Invalid content verification signature, please verify the connection information on the remote site and try again.', 'wpkbd' ) . sprintf( _x( ' Remote URL: %s ', 'Ex. Remote URL: http://wp.dev', 'wpkbd' ), home_url() );


		if ( $is_pro ) {
			$this->unhook_templates = [ 'exclude_post_revisions', 'wordpress_org_support', 'progress_upgrade', 'sidebar' ];
		}

		$this->plugin_dir_path    = plugin_dir_path( $this->plugin_file_path );
		$this->plugin_folder_name = basename( $this->plugin_dir_path );
		$this->plugin_basename    = plugin_basename( $this->plugin_file_path );
		$this->template_dir       = $this->plugin_dir_path . 'template' . DIRECTORY_SEPARATOR;
		$this->plugin_title       = ucwords( str_ireplace( '-', ' ', basename( $this->plugin_file_path ) ) );
		$this->plugin_title       = str_ireplace( array( 'db', 'wp', '.php' ), array( 'DB', 'WP', '' ), $this->plugin_title );

		$this->mu_plugin_dir    = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';

		// We need to set $this->plugin_slug here because it was set here
		// in Media Files prior to version 1.1.2. If we remove it the customer
		// cannot upgrade, view release notes, etc
		// used almost exclusively as a identifier for plugin version checking (both core and addons)
		$this->plugin_slug = basename( $this->plugin_file_path, '.php' );

		// used to add admin menus and to identify the core version in the $GLOBALS['kbfnr_meta'] variable for delicious brains api calls, version checking etc
		$this->core_slug = $is_pro ? '' : 'wpkbd';
		$this->is_pro    = $is_pro;

		if ( is_multisite() ) {
			$this->plugin_base = 'settings.php?page=' . $this->core_slug;
		} else {
			$this->plugin_base = 'tools.php?page=' . $this->core_slug;
		}

		if ( empty( $this->core_slug ) ) {
			$this->core_slug;
		}
//		$this->plugin_version = $GLOBALS['kbfnr_meta'][ $this->core_slug ]['version'];
	}
}
