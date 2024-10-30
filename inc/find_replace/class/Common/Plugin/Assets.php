<?php

namespace WPMechanic\KBFNR\Common\Plugin;

use WPMechanic\KBFNR\Common\Error\ErrorLog;
use WPMechanic\KBFNR\Common\Filesystem\Filesystem;
use WPMechanic\KBFNR\Common\Http\Http;
use WPMechanic\KBFNR\Common\Properties\Properties;

class Assets {

	public $assets, $http, $filesystem, $settings, $props;
	/**
	 * @var ErrorLog
	 */
	private $error_log;

	public function __construct(
		Http $http,
		ErrorLog $error_log,
		Filesystem $filesystem,
		Properties $properties
	) {
		$this->http       = $http;
		$this->filesystem = $filesystem;
		$this->props      = $properties;
		$this->error_log  = $error_log;
	}

	/**
	 * Checks and sets up plugin assets.
	 * Filter actions, enqueue scripts, define configuration, and constants.
	 *
	 * @return void
	 */
	function load_assets() {

	}

	function admin_body_class( $classes ) {
		if ( ! $classes ) {
			$classes = array();
		} else {
			$classes = explode( ' ', $classes );
		}

		$version_class = 'kbfnr-not-pro';
		if ( true == $this->props->is_pro ) {
			$version_class = 'kbfnr-pro';
		}

		$classes[] = $version_class;

		// Recommended way to target WP 3.8+
		// http://make.wordpress.org/ui/2013/11/19/targeting-the-new-dashboard-design-in-a-post-mp6-world/
		if ( version_compare( $GLOBALS['wp_version'], '3.8-alpha', '>' ) ) {
			if ( ! in_array( 'mp6', $classes ) ) {
				$classes[] = 'mp6';
			}
		}

		return implode( ' ', $classes );
	}
}
