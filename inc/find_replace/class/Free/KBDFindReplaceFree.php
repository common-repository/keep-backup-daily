<?php

namespace WPMechanic\KBFNR\Free;

use WPMechanic\KBFNR\Common\Plugin\Menu;
use WPMechanic\KBFNR\Container;
use WPMechanic\KBFNR\KBDFindReplace;

class KBDFindReplaceFree extends KBDFindReplace {

	public function __construct( $pro = false ) {
		parent::__construct( false );
	}

	public function register() {
		parent::register();
		$container = Container::getInstance();


		//Menu


//		echo "hello";exit;

		$container->get( 'migration_manager' )->register();
		$container->get( 'plugin_manager_base' )->register();
//		$container->get( 'plugin_manager' )->register();
//		$container->get( 'menu' )->register();
//		$container->get( 'free_template' )->register();

//		$filesystem = $container->get( 'filesystem' );
//		$filesystem->register();
	}
}
