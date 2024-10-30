<?php if ( ! defined( 'ABSPATH' ) ) exit; 

	//ENCRYPTION FUNCTION
	function sanitize_kbd_data( $input ) {

		if(is_array($input)){
		
			$new_input = array();
	
			foreach ( $input as $key => $val ) {
				$new_input[ $key ] = (is_array($val)?sanitize_kbd_data($val):sanitize_text_field( $val ));
			}
			
		}else{
			$new_input = sanitize_text_field($input);
		}
		
		return $new_input;
	}
	function kbd_backup_aliases($key){
		global $kbd_backup_aliases, $kbd_db_prefix;
		//pree($kbd_db_prefix);
		$key = str_replace($kbd_db_prefix, '', $key);
		//pree($key);
		
		return (isset($kbd_backup_aliases[$key])?$kbd_backup_aliases[$key]:$key);
	}

	if(!function_exists('kbd_encrypt')){


		function kbd_encrypt($decrypted, $password, $salt=''){


		 // Build a 256-bit $key which is a SHA256 hash of $salt and $password.


		 $key = hash('SHA256', $salt . $password, true);


		 // Build $iv and $iv_base64.  We use a block size of 128 bits (AES compliant) and CBC mode.  (Note: ECB mode is inadequate as IV is not used.)


		 srand(); 


                 if(function_exists('mcrypt_create_iv'))
                 $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
                 else
                 $iv = '鶵�^)W�D';


		 if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;


		 // Encrypt $decrypted and an MD5 of $decrypted using $key.  MD5 is fine to use here because it's just to verify successful decryption.


		 $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $decrypted . md5($decrypted), MCRYPT_MODE_CBC, $iv));


		 // We're done!


		 return $iv_base64 . $encrypted;


		 }


	}
	//FOR QUICK DEBUGGING


	if(!function_exists('pre')){
	function pre($data){
			if(isset($_GET['debug'])){
				pree($data);
			}
		}	 
	} 	
	if(!function_exists('pree')){
	function pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
		
		}	 
	} 

	
	function kbd_menu(){

	    global $kbd_pro, $kbd_data;

        $kbd_settings = get_option('kbd_settings', array());
        $menu_title = $kbd_data['Name'];
        $callback_function = 'kbd_settings';
        $slug = 'kbd_settings';


        if($kbd_pro && array_key_exists('switch_to_premium', $kbd_settings)){

            $callback_function = 'kbd_settings_pro_callback';
            $slug = 'kbd_settings_pro';
        }


        add_options_page($menu_title, $menu_title, 'activate_plugins', $slug, $callback_function);
        add_submenu_page( null, 'Download Backup Daily', 'Backup Now', 'activate_plugins', 'kbd_download', 'kbd_download' );

		add_submenu_page(
			'upload.php',
			__( 'Export', 'textdomain' ),
			__( 'Export', 'textdomain' ),
			'manage_options',
			'kbd-export-media',
			'kbd_export_media_page_callback'
		);


	}

	if(!function_exists('kbd_export_media_page_callback')){
		function kbd_export_media_page_callback(){
			$export_media_page = admin_url('admin.php?page=kbd_settings&t=2');
			wp_redirect($export_media_page);
			wp_die();
		}
	}
	
	function kbd_download() { 
		if ( !current_user_can( 'administrator' ) )  {

			wp_die( __( 'You do not have sufficient permissions to access this page.','wpkbd' ) );
		}	
		kbd_force_download();
	}

	//if(isset($_GET['page']) && $_GET['page']=='kbd_download')
	//kbd_force_download();


	
	function kbd_settings() { 

		if ( !current_user_can( 'administrator' ) )  {

			wp_die( __( 'You do not have sufficient permissions to access this page.','wpkbd' ) );
 			
		}

		 

		global $wpdb, $kbd_data, $kbd_pro; 
		$blog_info = get_bloginfo('admin_email');

		$salt = date('YmddmY')+date('m');

		//DEFAULT BACKUP RECIPIENT EMAIL ADDRESS	
		$default_email = get_bloginfo('admin_email');
		
		$default_email = $default_email!=''?$default_email:'info@'.str_replace('www.','',$_SERVER['HTTP_HOST']); 

		$kbd_settings_file = dirname(__FILE__).'/settings.dat';		//SETTINGS PARAMS TO BE STORED IN .DAT FILE		$settings = array();

		$settings['recpient_email_address']=array();

		$settings['backup_required'] = 'cron_d';

		$settings['maintain_log'] = 1;

		$settings['cron_server'] = 'default';				$settings['notification'] = '';

		$settings['notification_class'] = '';

		


		$kbd_log_file = dirname(__FILE__).'/log.dat';

		$settings['log'] = file_exists($kbd_log_file)?file_get_contents($kbd_log_file):'';

		 		//ENSURING THE VALID EMAIL ADDRESS	
		if(isset($_POST['recpient_email_address']) && isValidEmail($_POST['recpient_email_address']))

		{  

				//PREVENTING CSRF		
		
		if(isset($_POST['kbd_key']) && $_SESSION['kbd_key']==$_POST['kbd_key']) 
		{			$data = array(

			'backup_required'=>$_POST['backup_required'],

			'recpient_email_address'=>($_POST['recpient_email_address']==$default_email?'KBD':$_POST['recpient_email_address']),

			'maintain_log'=>$_POST['maintain_log'],

			'cron_server'=>$_POST['cron_server']			

			);

			//ACTION URL FOR BACKUP & EMAIL ACTIVITY			
			$submitted_url = update_kbd_cron($data);

			//STORING SETTINGS IN .DAT FILE			
			$data = serialize($data);

			$handle = fopen($kbd_settings_file,'wb+');

			fwrite($handle, $data);

			fclose($handle);			$settings['notification'] = ''.__('Settings saved.', 'wpkbd').'';
			copy($kbd_settings_file, WP_PLUGIN_DIR.'/kbd_settings.dat');
			$settings['notification_class'] = 'updated';
			
			//GETTING EXPECTED BACKUP EMAIL TIME FROM SERVER

			$remote_uri = 'https://www.androidbubbles.com/api/kbd.php?next_backup='.time().'&backup_time='.$_POST['backup_required'].'&domain_url='.base64_encode($submitted_url);

			$_SESSION['expected_backup']=@file_get_contents($remote_uri);

		}

		else

		{

			$settings['notification'] = ''.__('Access Denied.', 'wpkbd').'';

			$settings['notification_class'] = 'error';

		}

		

		}

		elseif(isset($_POST['recpient_email_address']))

		{

			$settings['notification'] = ''.__('Invalid Email Address.', 'wpkbd').'';

			$settings['notification_class'] = 'error';

		}

		//STORING ENCRYPTION KEY IN SESSION	
		$_SESSION['kbd_key'] = $settings['kbd_key'] = kbd_encrypt($_SERVER['HTTP_HOST'].date('m'), $_SERVER['HTTP_HOST'], $salt);

		//LOADING STORED SETTINGS FROM .DAT FILE		
		$settings = load_kbd_settings($settings);				
		//ENSURING THAT RECIPIENT IS ONLY ONE	
		if(isset($settings['recpient_email_address']) && is_array($settings['recpient_email_address']) && count($settings['recpient_email_address'])==0)		{			$settings['recpient_email_address'][] = $default_email;		}		

						$settings['notification'] = $settings['notification_class']!=''?'<div class="'.$settings['notification_class'].' settings-error" id="setting-error-settings_updated"> 

<p><strong>'.$settings['notification'].'</strong></p></div>':'';		

		

            $expected_backup = isset($_SESSION['expected_backup'])?$_SESSION['expected_backup']:'';				//EXPECTED BACKUP EMAIL GENERATION TIME	
			$settings['cron_d']['expected_backup'] = '';
			$settings['cron_w']['expected_backup'] = '';
			$settings['cron_m']['expected_backup'] = '';
			$settings['cron_y']['expected_backup'] = '';
			$settings[$settings['backup_required']]['expected_backup'] = ($expected_backup!=''?'<div class="alert alert-success">
      <strong>'.__('Well done!','wpkbd').'</strong> '.__('Your backup will be in your inbox on time.','wpkbd','wpkbd').'</div>
':'');						

		include('kbd_settings.php');			

	}
use WPMechanic\KBFNR\Common\Util\Util;


function register_kbd_styles() {

		global $kbd_url;

		if(isset($_GET['page']) && in_array($_GET['page'] , array('kbd_download', 'kbd_settings', 'kbd_settings_pro', 'kbd_backup_list_pro'))){

		}else{
			return;
		}
		
		
		
		wp_enqueue_script('kbd-pro-boostrap-script', plugins_url('js/bootstrap.min.js', dirname(__FILE__)), '1.0', true);
        wp_enqueue_script('kbd-pro-pooper-script', plugins_url('js/popper.min.js', dirname(__FILE__)), array('jquery'), '1.0', true);
        wp_enqueue_style('kbd-pro-boostrap-style', plugins_url('css/bootstrap.min.css?t='.time(), dirname(__FILE__)));


		wp_register_style( 'kbd-style', plugins_url('css/style.css?t='.time(), dirname(__FILE__)) );
		wp_enqueue_style( 'kbd-style' );
		wp_enqueue_script(
			'kbd-scripts',
			plugins_url('js/scripts.js', dirname(__FILE__)),
			array('jquery')
		);
		
		$kbd_obj = array(

						'kbd_ajax_nonce' => wp_create_nonce('kbd_ajax_nonce'),
						'kbd_restore_msg' => __('Are you sure, you want to replace existing files with this archive?', 'wpkbd'),
						'finalizing_msg' => __('Finalizing replacements in tables', 'wpkbd'),
						'flush_msg' => __('Flushing caches', 'wpkbd'),
						'done_msg' => __('Completed', 'wpkbd'),
						'kbd_backup_in_process' => __('Backup already in process. Please wait.', 'wpkbd'),
						'del_confirm' => __('Are your sure? You want to delete this item.', 'wpkbd'),
						'cancel_confirm' => __('Are your sure? You want to cancel find and replace process.', 'wpkbd'),
						'cancel' => __('The find & replace has been cancelled and all temporary data has been cleaned up.', 'wpkbd'),
						'required' => __('At least one find field is required to proceed.', 'wpkbd'),
						'this_url' => admin_url( 'admin.php?page=kbd_settings'),
						'kbd_download_url' => admin_url( 'options-general.php?page=kbd_download'),
						'ingtr_tab' => (isset($_GET['t'])?$_GET['t']:'0'),
						'initiate_migration_nonce' => Util::create_nonce( 'initiate-migration' ),
						'migrate_table_nonce' => Util::create_nonce( 'migrate-table' ),

					);

		wp_localize_script('kbd-scripts', 'kbd_obj', $kbd_obj);

		wp_localize_jquery_ui_datepicker();
	}
	
	if(!function_exists('init_sessions')){


		function init_sessions(){


			if (!session_id()){

				ob_start();
				@session_start();


			}


		}


	}

	if(!function_exists('load_kbd_settings')){

		function load_kbd_settings($settings=array()){

			$kbd_settings_file = dirname(__FILE__).'/settings.dat';

			if(!file_exists($kbd_settings_file) && file_exists(WP_PLUGIN_DIR.'/kbd_settings.dat')){
				copy(WP_PLUGIN_DIR.'/kbd_settings.dat', $kbd_settings_file);				
				//unlink(WP_PLUGIN_DIR.'/kbd_settings.dat');
			}
			
			if(file_exists($kbd_settings_file)){

				$data = file_get_contents($kbd_settings_file);

				if($data!=''){

					if(is_array(unserialize($data)))


					{

						$data = unserialize($data);

						

						$settings = array_merge($settings, $data);
						
						if($settings['recpient_email_address']=='KBD'){
						
						$settings['recpient_email_address'] = get_bloginfo('admin_email');
						}

					}

				}

				

			}	
			return $settings;

		}	

	}
	if(!function_exists('log_kbd')){

		function log_kbd($string){

			$kbd_log_file = dirname(__FILE__).'/log.dat';

			if($string!='')

			{				

				if(file_exists($kbd_log_file)){

					$string = $string.'<br>'.file_get_contents($kbd_log_file);					

				}

				

				$f = fopen($kbd_log_file, 'wb+');

				fwrite($f, $string);

				fclose($f);

				

			}

		}

	}
	if(!function_exists('kbd_start')){


		function kbd_start(){	

				

		}	


	}
	if(!function_exists('kbd_end')){

		function kbd_end(){	

			$kbd_log_file = dirname(__FILE__).'/log.dat';

			if(file_exists($kbd_log_file)){


				unlink($kbd_log_file);


			}
			
			$data = array();

			return update_kbd_cron($data);		
		}

		

	}	
	
	
	if(!function_exists('update_kbd_cron')){

		function update_kbd_cron($data){	


			$wpurl = get_bloginfo('wpurl');


			$return = $data['p'] = $wpurl.'/?kbd_cron_process=1';

			$data = http_build_query($data);

			if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']!='localhost'){
				
							
				$args = array(
					'body' => $data,
					'timeout' => '5',
					'redirection' => '5',
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'cookies' => array()
				);
				$url = 'https://www.androidbubbles.com/api/kbd.php';
				$response = wp_remote_post( $url, $args );
				
								
				if ( is_wp_error( $response ) ) {
				   $error_message = $response->get_error_message();
				  echo __("Something went wrong",'wpkbd').": $error_message";
				} else {
				   //$response['body'];
				}
				
				

			}


			return $return;

		}

	}
	if(!function_exists('isValidEmail')){

		function isValidEmail($email){

	    //return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $email);
		return is_email($email);

		}	

	}
	
	if(!function_exists('formatSizeUnits'))
	{
		function formatSizeUnits($bytes)
		{
			if ($bytes >= 1073741824)
			{
				$bytes = number_format($bytes / 1073741824, 2) . ' GB';
			}
			elseif ($bytes >= 1048576)
			{
				$bytes = number_format($bytes / 1048576, 2) . ' MB';
			}
			elseif ($bytes >= 1024)
			{
				$bytes = number_format($bytes / 1024, 2) . ' KB';
			}
			elseif ($bytes > 1)
			{
				$bytes = $bytes . ' bytes';
			}
			elseif ($bytes == 1)
			{
				$bytes = $bytes . ' byte';
			}
			else
			{
				$bytes = '0 bytes';
			}
		
			return $bytes;
		}
	}
	
	if(!function_exists('requirements_check'))
	{

		function requirements_check()
		{
			 $return = array();
			 $return['writable_dir'] = dirname(__FILE__);
			 
			 if(!is_writeable($return['writable_dir']))
			 @chmod($return['writable_dir'], 0777);
			 
			 $return['mcrypt_create_iv'] = function_exists('mcrypt_create_iv');
			 $return['ZipArchive'] = class_exists('ZipArchive');			 
			 $return['is_writable'] = is_writable($return['writable_dir']);
			 $return['finfo'] = class_exists('finfo');			 
			 
			 return $return;
		}
	}	
	
	
	if ( ! function_exists("file_parts"))
	{
		function file_parts($url,$params="ext")
		{
		
			if($params=="ext")
			{
			$parts = explode(".",$url);
			return end($parts);
			}
			elseif($params=="name")
			{
			$parts = explode("/",$url);
			return end($parts);
			}
			else
			{
			$parts = explode("/",$url);
			$file_name_ext = explode(".",end($parts));
			$file_name = array_pop($file_name_ext);
			return implode(".",$file_name);
			}
		}
	}	

	if ( ! function_exists("kbd_plugin_links"))
	{
		function kbd_plugin_links($links) { 
			global $kbd_premium_link, $kbd_pro;
			
			$settings_link = '<a href="options-general.php?page=kbd_settings">'.__('Settings','wpkbd').'</a>';
			
			if($kbd_pro){
				array_unshift($links, $settings_link); 
			}else{
				 
				$kbd_premium_link = '<a href="'.esc_url($kbd_premium_link).'" title="'.__('Go Premium','wpkbd').'" target="_blank">'.__('Go Premium','wpkbd').'</a>'; 
				array_unshift($links, $settings_link, $kbd_premium_link); 
			
			}
			
			
			return $links; 
		}
	}
		
	add_action( 'wp_ajax_update_kbd_bkup_alias', 'update_kbd_bkup_alias' );
	
	function update_kbd_bkup_alias() {
		global $wpdb, $kbd_backup_aliases, $kbd_db_prefix; // this is how you get access to the database
	
		if(isset($_POST['key']) && $_POST['key']!='' && isset($_POST['val']) && $_POST['val']!=''){
			
			
			
			$key = str_replace($kbd_db_prefix, '', $_POST['key']);
			$val = $_POST['val'];
			
			$kbd_backup_aliases[$key] = $val;
			

			if ( 
				! isset( $_POST['kbd_nonce'] ) 
				|| ! wp_verify_nonce( $_POST['kbd_nonce'], 'kbd_ajax_nonce' ) 
			) {
			
			   print __('Sorry, your nonce did not verify.','wpkbd');
			   exit;
			
			} else {
			
			   // process form data
			   update_option('kbd_backup_aliases', sanitize_kbd_data($kbd_backup_aliases));
			}			
			
		}
	
		wp_die(); // this is required to terminate immediately and return a proper response
	}

	//new code for find and replace


	if ( !function_exists( 'kbd_update_data' ) ) {
		function kbd_update_data($options, $old_data, $new_data){
			global $wpdb;
			$results = array();
			//pree($options);exit;
			$queries = array(
				'content' =>		array("UPDATE $wpdb->posts SET post_content = REPLACE(post_content, %s, %s)",  __('Content Items (Posts, Pages, Custom Post Types, Revisions)','wpkbd') ),
				'excerpts' =>		array("UPDATE $wpdb->posts SET post_excerpt = REPLACE(post_excerpt, %s, %s)", __('Excerpts','wpkbd') ),
				'attachments' =>	array("UPDATE $wpdb->posts SET guid = REPLACE(guid, %s, %s) WHERE post_type = 'attachment'",  __('Attachments','wpkbd') ),
				'links' =>			array("UPDATE $wpdb->links SET link_url = REPLACE(link_url, %s, %s)", __('Links','wpkbd') ),
				'custom' =>			array("UPDATE $wpdb->postmeta SET meta_value = REPLACE(meta_value, %s, %s)",  __('Custom Fields','wpkbd') ),
				'guids' =>			array("UPDATE $wpdb->posts SET guid = REPLACE(guid, %s, %s)",  __('GUIDs','wpkbd') )
			);

			foreach($options as $option){
				if( $option == 'custom' ){
					$n = 0;
					$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta" );
					$page_size = 10000;
					$pages = ceil( $row_count / $page_size );

					for( $page = 0; $page < $pages; $page++ ) {
						$current_row = 0;
						$start = $page * $page_size;
						$end = $start + $page_size;
						$pmquery = "SELECT * FROM $wpdb->postmeta WHERE meta_value <> ''";
						$items = $wpdb->get_results( $pmquery );
						foreach( $items as $item ){
							$value = $item->meta_value;
							if( trim($value) == '' )
								continue;

							$edited = kbd_unserialize_replace( $old_data, $new_data, $value );

							if( $edited != $value ){
								$fix = $wpdb->query("UPDATE $wpdb->postmeta SET meta_value = '".$edited."' WHERE meta_id = ".$item->meta_id );
								if( $fix )
									$n++;
							}
						}
					}
					$results[$option] = array($n, $queries[$option][1]);
				}else{
					switch($option){
						default:
							$result = $wpdb->query( $wpdb->prepare( $queries[$option][0], $old_data, $new_data) );
							$results[$option] = array($result, $queries[$option][1]);
						break;
						case 'content':
							$select_results = $wpdb->get_results( $wpdb->prepare( 'SELECT ID, REPLACE(post_content, %s, %s) AS updated_content FROM '.$wpdb->posts.' WHERE post_content LIKE "%'.$old_data.'%"', $old_data, $new_data, $old_data) );							
							if(!empty($select_results)){
								foreach( $select_results as $select_result ){
									$updated_content = $select_result->updated_content;
									$result = $wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->posts.' SET post_content="'.$updated_content.'" WHERE ID=%d', $select_result->ID) );
									$results[$option] = array($result, $queries[$option][1]);						
								}
							}
						break;
					}
				}
			}
			return $results;
		}
	}

	if ( !function_exists( 'kbd_unserialize_replace' ) ) {
		function kbd_unserialize_replace( $from = '', $to = '', $data = '', $serialised = false ) {
			try {
				if ( false !== is_serialized( $data ) ) {
					$unserialized = unserialize( $data );
					$data = kbd_unserialize_replace( $from, $to, $unserialized, true );
				}
				elseif ( is_array( $data ) ) {
					$_tmp = array( );
					foreach ( $data as $key => $value ) {
						$_tmp[ $key ] = kbd_unserialize_replace( $from, $to, $value, false );
					}
					$data = $_tmp;
					unset( $_tmp );
				}
				else {
					if ( is_string( $data ) )
						$data = str_replace( $from, $to, $data );
				}
				if ( $serialised )
					return serialize( $data );
			} catch( Exception $error ) {
			}
			return $data;
		}
	}

	add_action('admin_footer', 'kbd_add_export_media_button');

	if(!function_exists('kbd_add_export_media_button')){
		function kbd_add_export_media_button(){
			$current_screen = get_current_screen();

			
			if($current_screen->id == 'upload' && $current_screen->post_type == 'attachment'){

				?>
					<style type="text/css">
					

						a.kbd_export_media {
							margin-left: 4px;
							padding: 4px 8px;
							position: relative;
							top: -3px;
							text-decoration: none;
							border: 1px solid #0071a1;
							border-radius: 3px;
							text-shadow: none;
							font-weight: 600;
							font-size: 13px;
							line-height: normal;
							color: #0071a1;
							background: #f3f5f6;
							cursor: pointer;
						}
					
					</style>

					<script>
					
						jQuery(document).ready(function($){
							
							var header_end = $('hr.wp-header-end');

							if(header_end.length > 0){
								var kbd_export_action = `<a target="_blank" href="<?php echo esc_url(admin_url('admin.php?page=kbd_settings&t=2'));?>" class="kbd_export_media"><?php _e("Export","wpkbd"); ?></a>`;
								header_end.before(kbd_export_action);
							}


						})
					
					</script>

				<?php

			}
		} 
	}

	if(!function_exists('kbd_get_dir_list_html')){
		function kbd_get_dir_list_html($dir_path, $dir_type = 'main'){
				global $kbd_url;
			if(file_exists($dir_path) && is_dir($dir_path)){
				$dir_items = scandir($dir_path);
				$dir_counter = 0;
				if(!empty($dir_items)){

					$title = ($dir_type == 'main' ? __('Click to View Sub Directories', 'wpkbd') : '');

					foreach ($dir_items as $dir_index => $dir_name) {
						# code...
						
						
						if($dir_name == '.' || $dir_name == '..'){
							continue;
						}	
						
						$current_dir_path = $dir_path.'/'.$dir_name;

						if(is_dir($current_dir_path)){

						
							?>

								<li data-dir_name="<?php echo esc_attr($dir_name);?>" data-path="<?php echo esc_attr($current_dir_path); ?>">
									<input type="checkbox" name="kbd_export_dir_selection[<?php echo esc_attr($dir_type); ?>][]" value="<?php echo esc_attr($current_dir_path); ?>">
									<span class="d-inline-block" title="<?php echo esc_attr($title); ?>">
										<img src="<?php echo esc_url($kbd_url.'/images/folder.png');?>" alt="" /> <?php echo esc_html($dir_name); ?>
									</span>
								</li>

							<?php

							$dir_counter++;
							
						}			

						
					}
				}
				
				if($dir_counter == 0){
					?>
						<li><div class="alert alert-info text-center"><?php _e('Empty Directory', 'wpkbd');?></div></li>
					<?php
				}
				
			}

		}
	}

	add_action('wp_ajax_kbd_open_upload_dir', 'kbd_open_upload_dir');

	if(!function_exists('kbd_open_upload_dir')){
		function kbd_open_upload_dir(){

			$result_array = array('status' => false);
			if ( 
				! isset( $_POST['kbd_nonce'] ) 
				|| ! wp_verify_nonce( $_POST['kbd_nonce'], 'kbd_ajax_nonce' ) 
			) {
			
			   print __('Sorry, your nonce did not verify.','wpkbd');
			   exit;
			
			} else {
			
			   // process form data

				if(isset($_POST['kbd_path'])){
					$kbd_path = sanitize_kbd_data($_POST['kbd_path']);
					$kbd_path= str_replace('\\', '/', $kbd_path);
					$kbd_path= str_replace('\\', '/', $kbd_path);

					kbd_get_dir_list_html($kbd_path, 'sub');

					$html = ob_get_clean();

					$result_array['status'] = true;
					$result_array['html'] = $html;

				}


			  
			}

			wp_send_json($result_array);
		}
	}

	if(!function_exists('kbd_download_download_zip_bulk')){
		function kbd_download_download_zip_bulk($path_array){

			if(!empty($path_array)){

				//$exported_file_name = 'kbd_media_export_'.date('Y-m-d H-i-s', time()).'.zip';
				$exported_file_name = 'kbd_media_export_'.wp_rand(12000,26000).'-'.time().'.zip';
				
				$root_path_file = wp_upload_dir()['basedir'].'/'.$exported_file_name;
				$zip = new ZipArchive();
				$zip->open($root_path_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

				foreach ($path_array as $path_index => $kbd_path) {

					$kbd_path = str_replace('\\', '/', $kbd_path);
	
					$source = realpath($kbd_path);

					if(is_dir($source)) {
					  $main_folder = basename($source).DIRECTORY_SEPARATOR;
					  $zip->addEmptyDir($main_folder);					  
					  $iterator = new RecursiveDirectoryIterator($source);
					  $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
					  $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
					  foreach($files as $file) {
						$file = realpath($file);
						if(is_dir($file)) {
						  	$zip->addEmptyDir(str_replace($source . DIRECTORY_SEPARATOR, '', $main_folder.$file . DIRECTORY_SEPARATOR));
						}elseif(is_file($file)) {
						  $zip->addFile($file,str_replace($source . DIRECTORY_SEPARATOR, '', $main_folder.$file));
						}
					  }		
					}
				}

				
				$zip->close();
	
				header('Content-Type: application/zip');
				header('Content-disposition: attachment; filename='.$exported_file_name);
				header('Content-Length: ' . filesize($root_path_file));
				readfile($root_path_file);
			}

		}
	}

	add_action('init', 'kbd_download_backup_zip');

	if(!function_exists('kbd_download_backup_zip')){

		function kbd_download_backup_zip(){

			if(isset($_POST['kbd_export_selected'])){

				if(!isset($_POST['kbd_nonce_field']) || !wp_verify_nonce($_POST['kbd_nonce_field'], 'kbd_nonce_action')){

					print __('Sorry, your nonce did not verify.','wpkbd');
					exit;
				}else{
					$kbd_export_dir_selection = (isset($_POST['kbd_export_dir_selection']) ? sanitize_kbd_data($_POST['kbd_export_dir_selection']) : array());
					
					$path_to_zip = array();

					if(isset($kbd_export_dir_selection['main']) && !empty($kbd_export_dir_selection['main'])){
						$path_to_zip = $kbd_export_dir_selection['main'];
					}elseif(isset($kbd_export_dir_selection['sub']) && !empty($kbd_export_dir_selection['sub'])){
						$path_to_zip = $kbd_export_dir_selection['sub'];
					}

					if($path_to_zip){
						kbd_download_download_zip_bulk($path_to_zip);
					}

				}

			}
			

		}

	}

	add_action('wp_ajax_kbd_process_fresh_backup', 'kbd_process_fresh_backup');

	if(!function_exists('kbd_process_fresh_backup')){

		function kbd_process_fresh_backup(){
			$result_array = array(
				'latest_html' => '',
				'old_html' => '',
			);

			if ( 
				! isset( $_POST['kbd_nonce'] ) 
				|| ! wp_verify_nonce( $_POST['kbd_nonce'], 'kbd_ajax_nonce' ) 
			) {
			
			   print __('Sorry, your nonce did not verify.','wpkbd');
			   exit;
			
			} else {

				
				$kbd_type = sanitize_kbd_data($_POST['kbd_type']);

				if($kbd_type == 'backup_process'){

					kbd_db_backup_process();

				}elseif($kbd_type == 'backup_html'){

					ob_start();

					kbd_database_backup_list(true);
					$result_array['latest_html'] = ob_get_clean();
	
					ob_start();
					kbd_database_backup_list();
					$result_array['old_html'] = ob_get_clean();

				}

			}
					

			wp_send_json($result_array);
		
		}
	}
