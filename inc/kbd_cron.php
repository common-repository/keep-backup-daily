<?php if ( ! defined( 'ABSPATH' ) ) exit;  ini_set('max_execution_time', 60*60*30);//ini_set('zlib.output_compression', 'Off');//ini_set('max_input_time', 60*60*11);

	if(!class_exists('Mysqldump')){
		include_once('Mysqldump.php');			
	}
	use Ifsnop\Mysqldump as IMysqldump;

	
	
	

	function kbd_cron_process(){
	
		global $kbd_db_prefix;
	
		$kbd_rc = requirements_check();	

		$settings = load_kbd_settings();	

		$default_email = get_bloginfo('admin_email');
		$configEmail = trim($settings['recpient_email_address']);

		$body = $_SERVER['HTTP_HOST'].' - Database Backup by Wordpress Plugin Keep Backup Daily';
		
		if($configEmail==''){
			$configEmail = $default_email;
			$body .= ' - "'.__('You are receiving backups on your Admin Email address.','wpkbd').'" ';
		}
		
		$backup_stats = get_backup_stats();
		$zip_file = $backup_stats['zip_file'];					
		//$subject = 'Database Backup - '.str_replace(array('.zip', '.gz', $kbd_db_prefix), '', basename($zip_file));
		$subject = 'Database Backup - ' . get_bloginfo('name') . ' ' . str_replace(array('.zip', '.gz', $kbd_db_prefix), '', basename($zip_file));
		
		$headers = array();
		$headers[] = 'From: Backup <'.get_bloginfo('admin_email').'>';
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		
		$wp_mail = false;
		
		$attachments = array($zip_file);
		
		add_filter( 'wp_mail_content_type', 'kbd_email_content_type' );	
		//pree($zip_file);exit;
		if(file_exists($zip_file))
		$wp_mail = wp_mail($configEmail, $subject, $body, $headers, $attachments);
		//pree($wp_mail.' | '.$configEmail.' | '.$subject.' | '.$body);
		if($wp_mail){
		
			$db_size = formatSizeUnits(filesize($zip_file));
			//unlink($zip_file);
							   

			if($settings['maintain_log']){	

				$string = 'File size: '.$db_size.',  Sent to <a mailto="'.$configEmail.'">'.$configEmail.'</a> at '.date('d M, Y h:i:s a');

				log_kbd($string);

			}					
			
			echo '<span style="color:green">'.strtolower($_SERVER['HTTP_HOST']).'</span>';

		}else{
			echo '<span style="color:red">'.strtoupper($_SERVER['HTTP_HOST']).'</span>';
		}
		
		exit;
	}
    
	function kbd_email_content_type() {
		return 'text/html';
	}
	
	function get_backup_stats($stats_only = false){
		
		
		
		$upload_dir = wp_upload_dir();
		extract($upload_dir);	
		
		$zipfiles = kdb_list_zipfiles($basedir);		
		
		if(empty($zipfiles)){
			kbd_db_backup_process();
			$zipfiles = kdb_list_zipfiles($basedir);		
		}
		
		
		
		$ret =  array();			
		$backup_file = $basedir.'/'.current($zipfiles);
		$ret['zip_file'] = $backup_file;

		return $ret;
	
	}
	
	
	function kbd_db_backup_process(){
		global $kbd_db_prefix;
		
		
		$dumpSettings = array(
			'exclude-tables' => array(),//array('/^travis*/'),
			'compress' => IMysqldump\Mysqldump::GZIP,//IMysqldump\Mysqldump::NONE|GZIP
			'no-data' => false,
			'add-drop-table' => true,
			'single-transaction' => true,
			'lock-tables' => true,
			'add-locks' => true,
			'extended-insert' => false,
			'disable-keys' => true,
			'skip-triggers' => false,
			'add-drop-trigger' => true,
			'routines' => true,
			'databases' => false,
			'add-drop-database' => false,
			'hex-blob' => true,
			'no-create-info' => false,
			'where' => ''
		);
		
		$dump = new IMysqldump\Mysqldump(
			"mysql:host=".DB_HOST.";dbname=".DB_NAME,
			DB_USER,
			DB_PASSWORD,
			$dumpSettings);
		
		//pree($dumpSettings);
		$upload_dir = wp_upload_dir();
		extract($upload_dir);
		

		switch($dumpSettings['compress']){
			default:
				$ext = 'sql';
			break;
			case 'Zip':
			case 'Gzip':
			case 'Bzip2':
				$ext = 'gz';
			break;			
			
		}
		
		//$filename = $kbd_db_prefix.date('Y-M-d-H-i').'-'.time().".".$ext;
		$filename = $kbd_db_prefix.wp_rand(12000,26000).'-'.time().".".$ext;
		$dump_file = $basedir."/".$filename;
		//pree($basedir);exit;
		
		$dump->start($dump_file);		
		exit;
	}
	
	function kdb_list_zipfiles($mydirectory) {
		
		global $kbd_db_prefix;
		
		$ret = array();
		// directory we want to scan
		$dircontents = scandir($mydirectory);
		
		// list the contents
		
		foreach ($dircontents as $file) {
			$extension = pathinfo($file, PATHINFO_EXTENSION);
			if ($extension == 'gz' && strpos($file, $kbd_db_prefix)>=0) {
				
				$t = filemtime($mydirectory.'/'.$file);
				$ret[$t] = $file;
			}
		}
		krsort($ret);
		return $ret;
	}
	
	function kbd_backup_url_encode_decode($str='', $action='encode'){
		
		switch($action){
			case 'encode':
			default:
				$str_arr = explode('-', $str);
			break;
			
			case 'decode':
				$str_arr = explode('|', $str);
			break;
		}
			
		
		
		
		$last = end($str_arr);
		
		$year = date('Y', $last);
		$month = date('M', $last);
		$day = date('d', $last);
		
		
		$mstr = $year.'-'.$month.'-'.$day.'-';
		
		switch($action){
			case 'encode':
			default:

				$str = (str_replace(array($mstr, '-'), array('*', '|'), $str));

			break;
			
			case 'decode':
				$str = (str_replace(array('*', '|'), array($mstr, '-'), $str));
			break;
		}
		
		
		return $str;
	}	
	
	function kbd_force_download_old()
	{
			global $kbd_db_prefix, $kbd_backup_url;		
			
			
				
			$upload_dir = wp_upload_dir();
			extract($upload_dir);	
			
			$zipfiles = kdb_list_zipfiles($basedir);

		
			if(empty($zipfiles) || isset($_GET['fresh'])){
				
				if(!isset($_GET['fresh'])){
					
					echo '<h1>'.__('There is no backup exists.','wpkbd').' '.__('Do you want to create one now?','wpkbd').'</h1>
					<a href="'.$kbd_backup_url.'&fresh" class="button button-primary button-large">'.__('Click here to start backup process','wpkbd').'</a>
					';
					exit;
				}else{			
					echo '<h1>'.__('Please wait a little more.','wpkbd').'</h1>';	
					echo '<script>setTimeout(function(){ document.location.href="'.$kbd_backup_url.'"; }, 3000);</script>';	
					kbd_db_backup_process();
						
				}
				
			}elseif(!isset($_GET['fresh']) && !isset($_GET['file'])){
				
					
					
					echo '<h1>'.__('Following database backups are available:','wpkbd').'</h1>';
					echo '<a style="float:right; margin-right:10px;" href="'.$kbd_backup_url.'&fresh" class="button button-primary">'.__('Click here for the latest backup','wpkbd').'</a>';
					echo '<ol class="kbd-bkup-list">';
					$b = 0;
					foreach($zipfiles as $files){ $b++;
						//pree($basedir);
						//pree($files);
						$prefix = '';
						$name = str_replace(array($prefix, '.gz'), '', $files);
						list($year, $month, $day, $h, $i) = explode('-', $name);
						
						$date = $day.' '.$month.', '.$year.' <small>('.$h.':'.$i.')</small>';
						
						$title = kbd_backup_aliases($name);
						
						$title_parts = explode('-', $title);
						
						array_pop($title_parts);						
						
						$title = implode('-', $title_parts);
						
						//pree($title);
						
						$title = ($title!=$name?$title:'Database Backup of '.$date);
						
						$file = $kbd_backup_url.'&file='.kbd_backup_url_encode_decode($name);
						
						$db_size = formatSizeUnits(filesize($basedir.'/'.$files));
						$stats = $db_size;
						
						echo '<li>';
						echo '<input type="text" value="'.$title.'" />';
						echo '<a title="'.__('Click here to edit this title','wpkbd').'" class="kbd-bkup-title" data-key="'.$name.'">'.$title.'</a>';
						echo '<a style="margin-left:100px; font-size:12px; color:blue;" href="'.$file.'" >'.__('Download','wpkbd').'</a>';
						echo '<a style="margin-left:100px; font-size:12px; color:red;" href="'.$file.'&rm">'.__('Delete','wpkbd').'</a>';
						echo '<span style="margin-left:100px">'.($b == 1 ? '[LATEST] ' : '').$stats.'</span>';
						echo '</li>';
					}
					echo '</ol>';					
			}elseif(isset($_GET['file'])){				
				$prefix = '';
				$filename = $basedir.'/'.$prefix.$_GET['file'].'.gz';
				
				if(isset($_GET['rm'])){
					if(file_exists($filename))
					unlink($filename);
					
					wp_redirect($kbd_backup_url);
					exit;
				}
				if(isset($_GET['file'])){
					ob_clean();ob_start();

					$prefix = '';
					$name = str_replace(array($prefix, '.gz'), '', $files);
					$title = kbd_backup_aliases($_GET['file']);
					
									
					
					$mime = "application/x-gzip";
					header( "Content-Type: " . $mime );
					header( 'Content-Disposition: attachment; filename="Backup-' . str_replace($kbd_db_prefix,  '', $title).'.gz' . '"' );					
					readfile($filename);		
				}
			}			
			
			exit;
			

			
	}	


	function kbd_force_download()
	{
			global $kbd_db_prefix, $kbd_backup_url, $kbd_url;		
			$upload_dir = wp_upload_dir();
			extract($upload_dir);	


			if(isset($_GET['file'])){	
			
				$_GET['file'] = kbd_backup_url_encode_decode($_GET['file'], 'decode');
				
				//pree($_GET['file']);exit;
				
				//pree($_GET);exit;

				$prefix = '';
				$filename = $basedir.'/'.$prefix.$_GET['file'].'.gz';
				
				if(isset($_GET['rm'])){
					//pree($filename);exit;
					if(file_exists($filename))
					unlink($filename);
				}

				if(!isset($_GET['rm']) && isset($_GET['file'])){
					ob_clean();ob_start();

					$prefix = '';
					$name = str_replace(array($prefix, '.gz'), '', $filename);
					$title = kbd_backup_aliases($_GET['file']);
					
					//pree($title);//exit;
				
					$title_parts = explode('-', $title);
					
					array_pop($title_parts);						
					
					$title = implode('-', $title_parts);	
					
					//pree($title);exit;
					
					$mime = "application/x-gzip";
					header( "Content-Type: " . $mime );
					header( 'Content-Disposition: attachment; filename="Backup-' . str_replace($kbd_db_prefix,  '', $title).'.gz' . '"' );					
					readfile($filename);		
				}

			}		
				

			
			$zipfiles = kdb_list_zipfiles($basedir);
			$kbd_btn_text = (empty($zipfiles) ? __('Click here to start backup process', 'wpkbd') : __('Click here for latest backup', 'wpkbd'));
			$main_url = admin_url('options-general.php?page=kbd_settings');
			?>

				<div class="wrap kbd_settings kbd_download">
					<div class="row mt-3">
						<div class="col-md-12 pl-4">
							<a class="btn btn-primary" href="<?php echo esc_url($main_url); ?>"><?php _e('Back', 'wpkbd'); ?></a>
						</div>
					</div>
					<h2 class="nav-tab-wrapper">
						<a class="nav-tab nav-tab-active"><?php _e("Fresh Backup","wpkbd"); ?></a>
						<a class="nav-tab"><?php _e("Backup List","wpkbd"); ?></a>
					</h2>

					<div class="nav-tab-content">

						<div class="row mt-3">
							<div class="col-md-12">
								<div class="h4">
									<?php _e('Latest Backup'); ?>:
									<div class="float-right kbd_btn_parent">
										<span class="dashicons dashicons-yes"></span>
										<img src="<?php echo esc_url($kbd_url.'/images/loader.gif'); ?>" alt="" />
										<button class="btn btn-primary wpkbd_download_latest_backup"><?php echo esc_html($kbd_btn_text); ?></button>
									</div>
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-12 kbd_latest_backup_container">
								<?php 
									kbd_database_backup_list(true);
								?>
							</div>
						</div>
						
					</div>

					<div class="nav-tab-content hide">

						<div class="row mt-3">
							<div class="col-md-12">
								<div class="h4"><?php _e('Old Backup List'); ?>:</div>
							</div>
						</div>
						<div class="row mt-3">
							<div class="col-md-12 kbd_old_backup_container">
								
								<?php 
									kbd_database_backup_list();
								?>

							</div>
						</div>

					</div>

				</div>


			<?php			
			
			
			
	}	


	function kbd_database_backup_list($latest = false){
		
		global $kbd_db_prefix, $kbd_backup_url;		
			
			
				
		$upload_dir = wp_upload_dir();
		extract($upload_dir);	
		
		$zipfiles = kdb_list_zipfiles($basedir);

		if($latest){
			$zipfiles = array(current($zipfiles));
			$zipfiles = array_filter($zipfiles);
		}

		$compare_count = ($latest ? 0 : 1);

		if(count($zipfiles) > $compare_count){

			echo '<ol class="kbd-bkup-list">';
			$b = 0;
			foreach($zipfiles as $files){ $b++;
				//pree($basedir);
				//pree($files);
	
				if(!$latest && $b == 1){
					continue;
				}
				
	
				$prefix = '';
				$name = str_replace(array($prefix, '.gz'), '', $files);
				list($year, $month, $day, $h, $i) = explode('-', $name);
				

				//pree($name);
				
				$title_alias = $title = kbd_backup_aliases($name);				
				
				//pree($title_alias);
				
				$title_parts = explode('-', $title);
				
				$date_str = array_pop($title_parts);		
								
				$title = implode('-', $title_parts);
				
				$title = (is_numeric($title)?date('d M, Y', $i).' ('.date('h:i A', $i).')':$title);
				
				$title = ($title_alias!='' && $title_alias!=$name  && !is_numeric(str_replace('-', '', $title_alias))?$title_alias:'Database Backup of '.(date('d M, Y', $i).' ('.date('h:i A', $i).')'));
				
				
				
				$file = $kbd_backup_url.'&file='.kbd_backup_url_encode_decode($name);
				
				$db_size = formatSizeUnits(filesize($basedir.'/'.$files));
				$stats = $db_size;
				
				echo '<li>';
				echo '<input type="text" value="'.$title.'" />';
				echo '<a title="'.__('Click here to edit this title','wpkbd').'" class="kbd-bkup-title" data-key="'.$name.'">'.$title.'</a>';
				echo '<a style="margin-left:100px; font-size:12px; color:blue;" href="'.$file.'" >'.__('Download','wpkbd').'</a>';
				echo '<a class="kbd_del_backup" style="margin-left:100px; font-size:12px; color:red;" href="'.$file.'&rm">'.__('Delete','wpkbd').'</a>';
				echo '<span style="margin-left:100px">'.($b == 1 ? '[LATEST] ' : '').$stats.'</span>';
				echo '</li>';
			}
			echo '</ol>';

		}else{
			
			?>
				<div class="alert alert-info mt-3 text-center">
					<?php _e('No Backup Found', 'wpkbd') ?>
				</div>
			<?php
		}


	}



	