<?php
/**
 * Populate the $kbfnr global with an instance of the kbfnr class and return it.
 *
 * @return WPMechanic\KBFNR\KBDFindReplace The one true global instance of the kbfnr class.
 */
function kb_migrate_db() {
	global $kbfnr;

	if ( ! is_null( $kbfnr ) ) {
		return $kbfnr;
	}

	$kbfnr = new WPMechanic\KBFNR\Free\KBDFindReplaceFree( false );
	$kbfnr->register();

	return $kbfnr;
}



function kb_migrate_db_loaded() {
	// exit quickly unless: standalone admin; one of our AJAX calls
	if ( ! is_admin() || ( is_multisite() && ! current_user_can( 'manage_network_options' ) && ! kbfnr_is_ajax() ) ) {
		return false;
	}
	if ( function_exists( 'kb_migrate_db' ) ) {
		// Remove the compatibility plugin when the plugin is deactivated

		kb_migrate_db();
	}
}

add_action( 'plugins_loaded', 'kb_migrate_db_loaded' );
