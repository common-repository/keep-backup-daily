<?php if ( ! defined( 'ABSPATH' ) ) exit; 
/*
Plugin Name: Keep Backup Daily
Plugin URI: http://androidbubble.com/blog/website-development/php-frameworks/wordpress/plugins/wordpress-plugin-keep-backup-daily/1046
Description: This plugin will backup the mysql tables and email to a specified email address daily, weekly, monthly or even yearly.
Version: 2.0.9
Author: Fahad Mahmood 
Author URI: https://www.androidbubbles.com
Text Domain: wpkbd
Domain Path: /languages/	
License: GPL2

This WordPress Plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. This free software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this software. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/ 

	if(
		(
			isset($_REQUEST['kbd_cron_process']) 
			&& $_REQUEST['kbd_cron_process']=1
		)
					
		||
		is_admin()
	){
	}else{
		return;
	}


	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	global $kbd_db_prefix, $kbd_backup_url, $kbd_backup_aliases;
	
	$kbd_db_prefix = 'database-backup-'.DB_NAME.'-';
	$kbd_backup_url = admin_url('options-general.php?page=kbd_download');
	$kbd_backup_aliases = get_option('kbd_backup_aliases', array());


	
	
	global $kbd_rc, $kbd_rs, $kbd_pro, $kbd_dir, $kbd_premium_link, $kbd_data, $kbd_buf, $kbd_url, $kbd_title;
	
	$kbd_data = get_plugin_data(__FILE__);

	$kbd_dir = plugin_dir_path( __FILE__ );

    $kbd_url = plugin_dir_url( __FILE__ );

	$kbd_pro = file_exists($kbd_dir.'pro/kbd_extended.php');

	$fnr_autoload = $kbd_dir . 'inc/find_replace/class/autoload.php';
	$fnr_setup = $kbd_dir . 'inc/find_replace/setup-mdb.php';


	if(file_exists($fnr_autoload) && $fnr_setup){

		include_once $fnr_autoload;
		include_once $fnr_setup;

	}


	if($kbd_pro){
		include ('pro/kbd_extended.php');
	}

    include('inc/functions.php');

    if(phpversion()>=5.3){
        include_once('inc/kbd_cron.php');
    }else{

    }

    $kbd_title = ''.$kbd_data['Name'].'  ('.$kbd_data['Version'].($kbd_pro ? ') '.__('Pro','wpkbd').'':')').'';

	$kbd_premium_link = 'https://shop.androidbubbles.com/product/keep-backup-daily-pro';//https://shop.androidbubble.com/products/wordpress-plugin?variant=36439507992731';//

		 
	$kbd_rc = requirements_check();		

	$kbd_rs = array();
	$kbd_rs[] = '<a class="premium_link" target="_blank" href="'.$kbd_premium_link.'">'.__('Get premium version now!', 'wpkbd').'</a>';
	$kbd_rs[] = '<a target="_blank" href="http://androidbubble.com/blog/website-development/keep-backup-daily-how-to-restore-your-backup-files/1363/">'.__('How to restore backup files?', 'wpkbd').'</a>';
	$kbd_rs[] = '<a target="_blank" href="plugin-install.php?tab=search&s=wp+mechanic&plugin-search-input=Search+Plugins">'.__('Install WP Mechanic', 'wpkbd').'</a>';
	$kbd_rs[] = '<a target="_blank" href="http://androidbubble.com/blog/contact">'.__('Contact Developer', 'wpkbd').'</a>';
	
	

	

	
	
	

	register_activation_hook(__FILE__, 'kbd_start');

	//KBD END WILL REMOVE .DAT FILES	
	register_deactivation_hook(__FILE__, 'kbd_end' );

	add_action('init', 'init_sessions');	

	add_action( 'admin_menu', 'kbd_menu' );	

	add_action( 'admin_enqueue_scripts', 'register_kbd_styles' );
			
	if(isset($_REQUEST['kbd_cron_process']) && $_REQUEST['kbd_cron_process']=1)
	{		
		//ACTION TIME FOR BACKUP ACTIVITY
		add_action('init', 'kbd_cron_process', 1);	
	}


	
	if(is_admin()){
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'kbd_plugin_links' );	
		
	}
	
	if(isset($_REQUEST['kbd_labs'])){
	
	}