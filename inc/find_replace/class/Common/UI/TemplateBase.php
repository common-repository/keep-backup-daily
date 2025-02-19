<?php

namespace WPMechanic\KBFNR\Common\UI;

use WPMechanic\KBFNR\Common\Filesystem\Filesystem;
use WPMechanic\KBFNR\Common\Profile\ProfileManager;
use WPMechanic\KBFNR\Common\Properties\Properties;
use WPMechanic\KBFNR\Common\Settings\Settings;
use WPMechanic\KBFNR\Common\Sql\Table;
use WPMechanic\KBFNR\Common\Util\Util;

class TemplateBase {

	/**
	 * @var array
	 */
	public $plugin_tabs;
	/**
	 * @var Properties
	 */
	public $props;
	/**
	 * @var
	 */
	public $settings;
	/**
	 * @var Util
	 */
	public $util;
	/**
	 * @var
	 */
	public $compatibility_manager;
	/**
	 * @var
	 */
	public $template_pro;
	/**
	 * @var ProfileManager
	 */
	public $profile;
	/**
	 * @var Filesystem
	 */
	public $filesystem;
	/**
	 * @var bool
	 */
	public $lock_url_find_replace_row = false;

	/**
	 * @var Table
	 */
	private $table;

	public function __construct(
		Settings $settings,
		Util $util,
		ProfileManager $profile,
		Filesystem $filesystem,
		Table $table,
		Properties $properties
	) {
		$this->props      = $properties;
		$this->settings   = $settings->get_settings();
		$this->util       = $util;
		$this->profile    = $profile;
		$this->filesystem = $filesystem;

		if ( is_multisite() ) {
			$this->lock_url_find_replace_row = true;
		}

		$this->table = $table;

		$this->plugin_tabs = [
			[
				'slug'  => 'migrate',
				'title' => _x( 'Migrate', 'Configure a migration or export', 'wpkbd' ),
			],
			[
				'slug'  => 'settings',
				'title' => _x( 'Settings', 'Plugin configuration and preferences', 'wpkbd' ),
			],
			[
				'slug'  => 'addons',
				'title' => _x( 'Addons', 'Plugin extensions', 'wpkbd' ),
			],
			[
				'slug'  => 'help',
				'title' => _x( 'Help', 'Get help or contact support', 'wpkbd' ),
			],
		];
	}

	function template_compatibility() {
		$args = array(
			'plugin_compatibility_checked' => ( $this->util->is_muplugin_installed() ? true : false ),
		);
		$this->template( 'compatibility', 'common', $args );
	}

	function template_max_request_size() {
		$this->template( 'max-request-size', 'common' );
	}

	function template_debug_info() {
		$this->template( 'debug-info', 'common' );
	}

	function template_exclude_post_revisions( $loaded_profile ) {
		$args = array(
			'loaded_profile' => $loaded_profile,
		);
		$this->template( 'exclude-post-revisions', 'kbfnr', $args );
	}

	function template_wordpress_org_support() {
		$this->template( 'wordpress-org-support', 'kbfnr' );
	}

	function template_progress_upgrade() {
		$this->template( 'progress-upgrade', 'kbfnr' );
	}

	function template_sidebar() {
		$this->template( 'sidebar', 'kbfnr' );
	}

	/**
	 * Load Tools HTML template for tools menu on sites in a Network to help users find kbfnr in Multisite
	 *
	 */
	function subsite_tools_options_page() {
		$this->template( 'options-tools-subsite' );
	}

	function template_part( $methods, $args = false ) {
		$methods = array_diff( $methods, $this->props->unhook_templates );

		foreach ( $methods as $method ) {
			$method_name = 'template_' . $method;

			if ( method_exists( $this, $method_name ) ) {
				call_user_func( array( $this, $method_name ), $args );
			}
		}
	}

	public function plugin_tabs() {
		$i = 0;
		foreach ( $this->plugin_tabs as $tab ) {
			$active = 0 === $i ? ' nav-tab-active' : '';
			$tpl    = '<a href="#" class="nav-tab js-action-link%s %s" data-div-name="%s-tab">%s</a>';
			printf( $tpl, $active, $tab['slug'], $tab['slug'], $tab['title'] );
			$i ++;
		}
	}

	/**
	 * Returns HTML for setting a checkbox as checked depending on supplied option value.
	 *
	 * @param string|array $option      Options value or array containing $option_name as key.
	 * @param string       $option_name If $option is an array, the key that contains the value to be checked.
	 */
	public function maybe_checked( $option, $option_name = '' ) {
		if ( is_array( $option ) && ! empty( $option_name ) && ! empty( $option[ $option_name ] ) ) {
			$option = $option[ $option_name ];
		}
		echo esc_html( ( ! empty( $option ) && '1' == $option ) ? ' checked="checked"' : '' );
	}

	public function template( $template, $dir = '', $args = array(), $template_path = '' ) {
		// @TODO: Refactor to remove extract().
		extract( $args, EXTR_OVERWRITE );
		$dir       = ! empty( $dir ) ? trailingslashit( $dir ) : $dir;
		$base_path = ! empty( $template_path ) ? $template_path : $this->props->template_dir;

		$path = $base_path . $dir . $template . '.php';
		include $path;
	}

	public function options_page() {
		$this->template( 'options' );
	}

	function mixed_case_table_name_warning( $migration_type ) {
		ob_start();
		?>
		<h4><?php _e( "Warning: Mixed Case Table Names", 'wpkbd' ); ?></h4>

		<?php if ( 'pull' === $migration_type ) : ?>
			<p><?php echo __( "Whoa! We've detected that your", 'wpkbd' )." <b>".__("local", 'wpkbd' )."</b> ".__("site has the MySQL setting", 'wpkbd' )." <code>lower_case_table_names</code> ".__("set to", 'wpkbd' )." <code>1</code>."; ?></p>
		<?php else : ?>
			<p><?php echo __( "Whoa! We've detected that your", 'wpkbd' )." <b>".__("remote", 'wpkbd' )."</b> ".__("site has the MySQL setting", 'wpkbd' )." <code>lower_case_table_names</code> ".__("set to", 'wpkbd' )." <code>1</code>."; ?></p>
		<?php endif; ?>

		<p><?php _e( "As a result, uppercase characters in table names will be converted to lowercase during the migration.", 'wpkbd' ); ?></p>

		<p></p>
		<?php
		return wptexturize( ob_get_clean() );
	}
}
