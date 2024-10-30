<?php

$kbd_settings = get_option('kbd_settings', array());
global $kbd_title;

if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap kbd_settings">

<h2><?php echo esc_html($kbd_title); ?> - <?php _e('Settings','wpkbd'); ?></h2>
<?php echo wp_kses_post($settings['notification']); $wpurl = get_bloginfo('wpurl'); ?>

    <h2 class="nav-tab-wrapper">
        <a class="nav-tab nav-tab-active"><?php _e("General Settings","wpkbd"); ?></a>
        <a class="nav-tab"><?php _e("Find and Replace","wpkbd"); ?></a>
        <a class="nav-tab"><?php _e("Media Library","wpkbd"); ?></a>
    </h2>

<table style="margin-top: 20px">
    <tbody>
        <?php

        if($kbd_pro):

            ?>

            <tr valign="top">

                <th scope="row">
                    <label for="switch_to_premium">

                        <?php _e('BACKUP: THEMES, PLUGINS, UPLOADS AND CORE FILES','wpkbd'); ?>

                    </label>

                </th>
                <td colspan="2">
                    <fieldset>

                        <label for="switch_to_premium">
                            <input style="display: none" type="checkbox" value="1"  name="kbd_settings[switch_to_premium]" id="switch_to_premium" <?php echo array_key_exists('switch_to_premium', $kbd_settings) ? 'checked': ''; ?>>
                        </label>

                    </fieldset>

                </td>

            </tr>

        <?php

        endif;

        ?>
    </tbody>
</table>


<form class="nav-tab-content" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
    <input type="hidden" name="wpkbd_tn" value="<?php echo (int)(isset($_GET['t'])?esc_attr($_GET['t']):'0'); ?>" />


<?php wp_nonce_field( 'kbd_nonce_action', 'kbd_nonce_action_field' ); ?>


<input type="hidden" name="kbd_key" value="<?php echo esc_attr($settings['kbd_key']); ?>">

<table class="form-table">

<tbody>



<tr valign="top">

<th scope="row"><?php _e('Backup Required','wpkbd'); ?></th>

<td id="front-static-pages">
  

<div class="btn-group" data-toggle="buttons" style="float:left; margin:0 0 10px 0">
  <label class="btn btn-primary">
    <input type="radio" name="backup_required" id="cron_d" value="cron_d" <?php echo ($settings['backup_required']=='cron_d'?'checked="checked"':''); ?>> <?php _e('Daily','wpkbd'); ?>
  </label>
  <label class="btn btn-primary">
    <input type="radio" name="backup_required" id="cron_w" value="cron_w" <?php echo ($settings['backup_required']=='cron_w'?'checked="checked"':''); ?>> <?php _e('Weekly','wpkbd'); ?>
  </label>
  <label class="btn btn-primary">
    <input type="radio" name="backup_required" id="cron_m" value="cron_m" <?php echo ($settings['backup_required']=='cron_m'?'checked="checked"':''); ?>> <?php _e('Monthly','wpkbd'); ?>
  </label>
  <label class="btn btn-primary">
    <input type="radio" name="backup_required" id="cron_y" value="cron_y" <?php echo ($settings['backup_required']=='cron_y'?'checked="checked"':''); ?>> <?php _e('Yearly','wpkbd'); ?>
  </label>  
</div>
<div style="clear:both; float:left; margin:0px 0;">
<?php echo $settings['cron_d']['expected_backup']; ?>
<?php echo $settings['cron_w']['expected_backup']; ?>
<?php echo $settings['cron_m']['expected_backup']; ?>
<?php echo $settings['cron_y']['expected_backup']; ?>
&nbsp;
</div>

<fieldset>


	<p>
    <a id="cron_now" title="<?php echo __('Click here to email your backup now','wpkbd'); ?>"><?php _e('Email Backup Now','wpkbd'); ?></a>
    &nbsp;|&nbsp;
     <a id="kbd_backup_now" title="<?php echo __('Click here to download your backup now','wpkbd'); ?>"><?php _e('Download Backup Now','wpkbd'); ?></a>
    
    </p>
    
</fieldset></td>

<td colspan="2">
    <div class="kbd_rc_console">
    <strong><?php _e('Recommended Links','wpkbd'); ?></strong>
    <?php global $kbd_rs;?>
    <ul class="kbd_rd">
        <li><?php echo (implode('</li><li>', $kbd_rs)); ?></li>
    </ul>    
    </div>  
</td>

</tr>

<tr valign="top">

<th scope="row"><label for="recpient_email_address"><?php _e('Recipient Email Address','wpkbd'); ?></label></th>

<td colspan="2">

<input type="text" class="medium-text" value="<?php echo esc_attr(is_array($settings['recpient_email_address'])?implode(',', $settings['recpient_email_address']):$settings['recpient_email_address']); ?>" step="1" name="recpient_email_address" id="recpient_email_address">

<p class="description"><?php _e('Default','wpkbd'); ?>: <?php echo esc_html($default_email); ?></p>

</td>

<td rowspan="3">

  
  
  
  
  
  <div class="kbd_rc_console">
<strong><?php _e('Requirements List','wpkbd'); ?></strong>
<?php global $kbd_rc;?>
<ul class="kbd_rc">

	<li title="<?php echo $kbd_rc['is_writable']!=1?''.__('Backup file can not be created in','wpkbd').' ['.$kbd_rc['writable_dir'].' ('.decoct(fileperms($kbd_rc['writable_dir']) & 0777).')]"'.' class="cross':''.__('Everything is Good','wpkbd').'!"'.' class="tick';?>"> <?php _e('Write Permissions','wpkbd');?></li>

    <li class="hide" title="<?php echo $kbd_rc['ZipArchive']!=1?''.__('Backup file can not be compressed, you will get .sql file as backup.','wpkbd').'"'.' class="cross':''.__('Everything is Good','wpkbd').'!"'.' class="tick';?>"><?php _e('Zip Library','wpkbd'); ?></li> 

    <li class="hide" title="<?php echo $kbd_rc['mcrypt_create_iv']!=1?''.__('You are lacking an improved security measure but it will not affect in normal cases.','wpkbd').'"'.' class="cross':''.__('Everything is Good','wpkbd').'!"'.' class="tick';?>"><?php _e('MCRYPT Library','wpkbd'); ?></li>

    <li class="hide" title="<?php echo $kbd_rc['finfo']!=1?''.__('You might will not be able to download database backup with Backup Now option.','wpkbd').'"'.' class="cross':''.__('Everything is Good','wpkbd').'!"'.' class="tick';?>"><?php echo __('Fileinfo','wpkbd'); ?> <?php _e('Library','wpkbd'); ?></li>

</ul>
<div class="bottom_links"><a class="kbd_comments" href="http://androidbubble.com/blog/website-development/php-frameworks/wordpress/plugins/wordpress-plugin-keep-backup-daily/1046/#reply-title" target="_blank"><?php _e('Need Help?','wpkbd'); ?></a></div>
</div>  
</td>
</tr>

<tr valign="top">

<th scope="row"><?php _e('Maintain Log','wpkbd'); ?></th>

<td colspan="2"><fieldset>

	<label for="maintain_log"><input type="checkbox" value="1" <?php echo ($settings['maintain_log']==1?'checked="checked"':''); ?> name="maintain_log" id="maintain_log">

	<?php _e('You will be able to view log with date and time.','wpkbd'); ?></label>

	<p class="description"><?php _e('Only log file will be stored on your server.','wpkbd'); ?></p>

    

    <p class="description">

    <?php if($settings['log']!=''): ?>

    <div style="height:160px; background-color:#F3F3F3; overflow:auto; width:64%;">

    <?php echo esc_html(nl2br($settings['log'])); ?>

    </div>

    <?php endif; ?>

    </p>

</fieldset></td>

</tr>

<tr valign="top">

<th scope="row"><?php echo __('Cron Job','wpkbd'); ?> <?php _e('Settings','wpkbd'); ?> <span title="<?php echo __('By default we will access cron file placed on your server for your convenience.','wpkbd').' '.__("Because most of the users don't have idea that how to set a cron or conscious about their server performance.",'wpkbd'); ?>" style="color:red">(<?php _e('Important','wpkbd'); ?>)</span></th>

<td colspan="2"><fieldset>

	<p><label for="kbd_cron_default">

		<input <?php echo ($settings['cron_server']=='default'?'checked="checked"':''); ?> type="radio" class="tog" id="kbd_cron_default" value="default" name="cron_server"><?php _e('Default','wpkbd'); ?></label>

	</p>

    <p><label for="kbd_cron_custom">

		<input <?php echo ($settings['cron_server']=='custom'?'checked="checked"':''); ?> type="radio" class="tog" id="kbd_cron_custom" value="custom" name="cron_server"><?php _e('Custom','wpkbd'); ?> <a>(<?php _e('more','wpkbd'); ?>)</a></label>

	</p>

    <p class="description cron_line" style="display:none"><?php echo __('You have to run the following file, write the','wpkbd').' cron job '.__('command which is suitable on your server','wpkbd'); ?>. <input type="text" class="large-text" value="<?php echo esc_url($wpurl.'/?kbd_cron_process=1'); ?>"></p>

</fieldset></td>

</tr>
</tbody></table>

<p class="submit"><input type="submit" value="<?php _e('Save Changes','wpkbd'); ?>" class="button button-primary" id="submit" name="submit">
</form>


<?php

    include_once ('kbd_find_replace.php');

?>


<?php

    include_once ('kbd_export_media.php');

?>



</div>

<script type="text/javascript" language="javascript">
jQuery(document).ready(function($) {
	$('.tog:checked').click();

	<?php if(isset($_POST['wpkbd_tn'])): ?>

    $('.nav-tab-wrapper .nav-tab:nth-child(<?php echo esc_attr($_POST['wpkbd_tn']+1); ?>)').click();

	<?php endif; ?>

	<?php if(isset($_GET['t'])): ?>

    $('.nav-tab-wrapper .nav-tab:nth-child(<?php echo esc_attr($_GET['t']+1); ?>)').click();

	<?php endif; ?>



});
</script>
<style type="text/css">
#menu-settings li.current{
	background-color:#428bca;
}
<?php if($kbd_pro): ?>
.premium_link{ display:none; }
<?php else: ?>
.premium_link{ color:#F00; }
<?php endif; ?>
.woocommerce-message, .update-nag, #message, .notice.notice-error, .error.notice, div.notice, div.fs-notice, div.wrap > div.updated:not(#setting-error-settings_updated){ display:none !important; }
</style>