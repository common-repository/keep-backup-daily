<?php

namespace WPMechanic\KBFNR\Common\Properties;

use WPMechanic\KBFNR\Common\Util\Singleton;

/**
 * Class DynamicProperties
 *
 * Property store for *global* properties that are modified throughout the codebase. Legacy support
 *
 * @TODO    remove once refactoring of global state is complete
 *
 * @package WPMechanic\KBFNR\Common\Properties
 */
class DynamicProperties {

	use Singleton;
	public $form_data, $fp, $find_replace_pairs, $maximum_chunk_size, $target_db_version, $doing_cli_migration, $addons, $attempting_to_connect_to, $is_addon;
}
