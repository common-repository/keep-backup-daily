<?php

namespace WPMechanic\KBFNR\Free\Plugin;

use WPMechanic\KBFNR\Common\Plugin\Assets;
use WPMechanic\KBFNR\Common\Plugin\PluginManagerBase;
use WPMechanic\KBFNR\Common\Properties\Properties;
use WPMechanic\KBFNR\Container;
use WPMechanic\KBFNR\Common\Filesystem\Filesystem;
use WPMechanic\KBFNR\Common\Http\Http;
use WPMechanic\KBFNR\Common\Multisite\Multisite;
use WPMechanic\KBFNR\Common\Settings\Settings;
use WPMechanic\KBFNR\Common\Sql\Table;
use WPMechanic\KBFNR\Common\Util\Util;

class PluginManager extends PluginManagerBase {
	public function __construct(
		Settings $settings,
		Assets $assets,
		Util $util,
		Table $table,
		Http $http,
		Filesystem $filesystem,
		Multisite $multisite,
		Properties $properties
	) {

		parent::__construct( $settings,
			$assets,
			$util,
			$table,
			$http,
			$filesystem,
			$multisite,
			$properties
		);
	}

	public function register() {
		parent::register();
		$cli = Container::getInstance()->get('cli');
		$cli->register();
	}
}
