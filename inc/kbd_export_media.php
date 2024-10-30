<?php 
    global $kbd_url;

?>

<div class="nav-tab-content hide kbd_export_media">
    <div class="container-fluid">
        <form method="post">

            <?php 

                wp_nonce_field('kbd_nonce_action', 'kbd_nonce_field');

            ?>

            <div class="row mt-3">
                <div class="col-md-2 p-0 col-4 border-right kbd_side_nav">
                    <ul class="kbd_dir_list">

                        <?php 
                            $upload_dir = wp_upload_dir();

                            if(!empty($upload_dir) && isset($upload_dir['basedir'])){
                                kbd_get_dir_list_html($upload_dir['basedir']);
                            }
                        ?>
                    </ul>
                </div>

                <div class="col-md-10 col-8 kbd_sub_dir">
                    <div class="h4">
                        <span class="kbd_title d-inline-block mb-1"></span>
                         <button type="submit" name="kbd_export_selected" class="float-right btn btn-primary btn-sm mb-3 d-none"><?php _e('Download', 'wpkbd'); ?></button>
                    </div> 
                    <hr style="clear: both;">     
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="text-center kbd_media_loader">
                                <img src="<?php echo esc_url($kbd_url.'/images/loader.gif'); ?>" alt="" />
                            </div>
                            <ul class="kbd_sub_dir_list">

                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </form>

    </div>
</div>

    