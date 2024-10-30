<?php

namespace WPMechanic\KBFNR;

use WPMechanic\KBFNR\Common\BackupExport;
use WPMechanic\KBFNR\Common\Cli\CliManager;
use WPMechanic\KBFNR\Common\Compatibility\CompatibilityManager;
use WPMechanic\KBFNR\Common\Error\ErrorLog;
use WPMechanic\KBFNR\Common\Http\Helper;
use WPMechanic\KBFNR\Common\Http\RemotePost;
use WPMechanic\KBFNR\Common\Http\Scramble;
use WPMechanic\KBFNR\Common\Migration\FinalizeMigration;
use WPMechanic\KBFNR\Common\Migration\InitiateMigration;
use WPMechanic\KBFNR\Common\Migration\MigrationManager;
use WPMechanic\KBFNR\Common\MigrationState\MigrationStateManager;
use WPMechanic\KBFNR\Common\MigrationState\StateDataContainer;
use WPMechanic\KBFNR\Common\Plugin\Assets;
use WPMechanic\KBFNR\Common\Plugin\PluginManagerBase;
use WPMechanic\KBFNR\Common\Profile\ProfileManager;
use WPMechanic\KBFNR\Common\Properties\DynamicProperties;
use WPMechanic\KBFNR\Common\Replace;
use WPMechanic\KBFNR\Common\Settings\Settings;
use WPMechanic\KBFNR\Common\Settings\SettingsManager;
use WPMechanic\KBFNR\Common\Sql\Table;
use WPMechanic\KBFNR\Common\Sql\TableHelper;
use WPMechanic\KBFNR\Common\UI\Notice;
use WPMechanic\KBFNR\Common\UI\TemplateBase;

class ServiceProvider extends ServiceProviderAbstract {

	public $filesystem;
	public $properties;
	public $util;
	public $settings;
	public $settings_manager;
	public $error_log;
	public $dynamic_props;
	public $scrambler;
	public $migration_state;
	public $http;
	public $migration_state_manager;
	public $form_data;
	public $state_data_container;
	public $remote_post;
	public $table_helper;
	public $multisite;
	public $http_helper;
	public $table;
	public $backup_export;
	public $migration_manager;
	public $initiate_migration;
	public $finalize_migration;
	public $replace;
	public $notice;
	public $profile_manager;
	public $template_base;
	public $compatibility_manager;
	public $assets;
	public $plugin_manager_base;
	public $cli_manager;
	public $cli;

	public function __construct() {
		$this->state_data_container = new StateDataContainer();
		$this->filesystem           = new Common\Filesystem\Filesystem();
		$this->properties           = new Common\Properties\Properties();
		$this->util                 = new Common\Util\Util( $this->properties, $this->filesystem );
		$this->settings             = new Settings(
			$this->util
		);

		$this->error_log = new ErrorLog(
			$this->settings,
			$this->filesystem,
			$this->util,
			$this->properties
		);

		$this->dynamic_props   = new DynamicProperties();
		$this->scrambler       = new Scramble();
		$this->migration_state = new Common\MigrationState\MigrationState();
		$this->http            = new Common\Http\Http(
			$this->util,
			$this->filesystem,
			$this->scrambler,
			$this->properties
		);

		$this->migration_state_manager = new MigrationStateManager(
			$this->error_log,
			$this->util,
			new Common\MigrationState\MigrationState(),
			$this->http,
			$this->properties,
			$this->state_data_container
		);

		$this->form_data = new Common\FormData\FormData(
			$this->util,
			$this->migration_state_manager
		);

		$this->http_helper = new Helper(
			$this->settings
		);

		$this->multisite = new Common\Multisite\Multisite(
			$this->migration_state_manager,
			$this->properties,
			$this->util
		);

		$this->table_helper = new TableHelper(
			$this->form_data,
			$this->migration_state_manager,
			$this->http
		);

		//RemotePost
		$this->remote_post = new RemotePost(
			$this->util,
			$this->filesystem,
			$this->migration_state_manager,
			$this->settings,
			$this->error_log,
			$this->scrambler,
			$this->properties
		);

		$this->replace = new Replace(
			$this->migration_state_manager,
			$this->table_helper,
			$this->error_log,
			$this->util
		);

		// Notice
		$this->notice = new Notice();

		//Table
		$this->table = new Table(
			$this->filesystem,
			$this->util,
			$this->error_log,
			$this->migration_state_manager,
			$this->form_data,
			$this->table_helper,
			$this->multisite,
			$this->http,
			$this->http_helper,
			$this->remote_post,
			$this->properties,
			$this->replace
		);

		// BackupExport
		$this->backup_export = new BackupExport(
			$this->settings,
			$this->filesystem,
			$this->table_helper,
			$this->http,
			$this->form_data,
			$this->table,
			$this->properties,
			$this->migration_state_manager
		);

		//InitiateMigration
		$this->initiate_migration = new InitiateMigration(
			$this->migration_state_manager,
			new Common\MigrationState\MigrationState(),
			$this->table,
			$this->http,
			$this->http_helper,
			$this->util,
			$this->remote_post,
			$this->form_data,
			$this->filesystem,
			$this->error_log,
			$this->properties
		);

		//FinalizeMigration
		$this->finalize_migration = new FinalizeMigration(
			$this->migration_state_manager,
			$this->table,
			$this->http,
			$this->table_helper,
			$this->http_helper,
			$this->util,
			$this->remote_post,
			$this->form_data,
			$this->properties
		);

		// MigrationManager
		$this->migration_manager = new MigrationManager(
			$this->migration_state_manager,
			new Common\MigrationState\MigrationState(),
			$this->table,
			$this->http,
			$this->table_helper,
			$this->http_helper,
			$this->util,
			$this->remote_post,
			$this->form_data,
			$this->filesystem,
			$this->error_log,
			$this->backup_export,
			$this->multisite,
			$this->initiate_migration,
			$this->finalize_migration,
			$this->properties
		);

		// ProfileManager
		$this->profile_manager = new ProfileManager(
			$this->http,
			$this->properties,
			$this->settings,
			$this->migration_state_manager,
			$this->util,
			$this->error_log,
			$this->table,
			$this->form_data
		);


		// TemplateBase
		$this->template_base = new TemplateBase(
			$this->settings,
			$this->util,
			$this->profile_manager,
			$this->filesystem,
			$this->table,
			$this->properties
		);

		// CompatibilityManager
		$this->compatibility_manager = new CompatibilityManager(
			$this->filesystem,
			$this->settings,
			$this->notice,
			$this->http,
			$this->template_base,
			$this->migration_state_manager,
			$this->util,
			$this->properties
		);

		$this->settings_manager = new SettingsManager(
			$this->http,
			$this->settings,
			$this->migration_state_manager,
			$this->error_log
		);

		$this->assets = new Assets(
			$this->http,
			$this->error_log,
			$this->filesystem,
			$this->properties
		);

		$this->plugin_manager_base = new PluginManagerBase(
			$this->settings,
			$this->assets,
			$this->util,
			$this->table,
			$this->http,
			$this->filesystem,
			$this->multisite,
			$this->properties
		);

//		$this->cli_manager = new CliManager();
//
//		$this->cli = new Common\Cli\Cli(
//			$this->form_data,
//			$this->util,
//			$this->cli_manager,
//			$this->table,
//			$this->error_log,
//			$this->initiate_migration,
//			$this->finalize_migration,
//			$this->http_helper,
//			$this->migration_manager,
//			$this->migration_state_manager
//		);
	}
}
