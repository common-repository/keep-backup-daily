<?php

?>




<form class="nav-tab-content hide kbdfr" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

    <input type="hidden" name="save_computer" value="1">
    <input type="hidden" name="gzip_file" value="1">
    <input type="hidden" name="action" value="find_replace">
    <input type="hidden" name="connection_info" value="">
    <input type="hidden" name="remote_json_data" value="">
    <input type="hidden" name="create_new_profile" value="">
    <input type="hidden" name="save_migration_profile_option" value="new">


    <div class="step-heading">
	    <h5><?php _e('Find & Replace', 'wpkbd') ?></h2>
    </div>


    <div class="header-wrapper clearfix">
        <div class="option-heading find-heading"><?php _e('Find', 'wpkbd') ?></div>
        <div class="option-heading replace-heading"><?php _e('Replace', 'wpkbd') ?></div>
    </div>

	<table id="find-and-replace-sort" class="clearfix replace-fields">
		<tbody class="ui-sortable">


        <tr class="replace-row original-repeatable-field" style="">

            <td class="old-replace-col">
                <input type="text" size="40" name="replace_old[]" class="code" placeholder="Old value" autocomplete="off">
            </td>
            <td class="arrow-col" title="Copy Find to Replace">
                <span class="right-arrow"></span>
            </td>
            <td class="replace-right-col">
                <input type="text" size="40" name="replace_new[]" class="code" placeholder="New value" autocomplete="off">

            </td>
            <td class="row-action-buttons">
                <span class="replace-remove-row"></span>
            </td>
        </tr>


       <tr class="replace-row" style="">

            <td class="old-replace-col">
                <input type="text" size="40" name="replace_old[]" class="code" placeholder="Old value" autocomplete="off" value="">
            </td>
            <td class="arrow-col" title="Copy Find to Replace">
                <span class="right-arrow"></span>
            </td>
            <td class="replace-right-col">
                <input type="text" size="40" name="replace_new[]" class="code" placeholder="New value" autocomplete="off" value="">

            </td>
            <td class="row-action-buttons">
                <span class="replace-remove-row"></span>
            </td>

       </tr>







		<tr class="pin">
			<td colspan="4"><a class="button add-row"><?php _e('Add Row', 'wpkbd') ?></a></td>
		</tr>
		</tbody>
	</table>

    <hr>

    <div class="option-section advanced-options">
        <div class="header-expand-collapse clearfix mb-3">
            <div class="option-heading tables-header"><?php _e('Advanced Options', 'wpkbd') ?></div>
        </div>

        <div class="indent-wrap expandable-content" style="display: block;">

            <ul>
                <li>
                    <label for="replace-guids">
                        <input id="replace-guids" type="checkbox" value="1" name="replace_guids" checked="&quot;checked&quot;">
	                    <?php _e('Replace GUIDs', 'wpkbd') ?>
                    </label>

                </li>
                <li>
                    <label for="exclude-spam">
                        <input id="exclude-spam" type="checkbox" autocomplete="off" value="1" name="exclude_spam">
	                    <?php _e('Exclude spam comments', 'wpkbd') ?>

                    </label>
                </li>

                <li>
                    <label for="exclude-transients">
                        <input id="exclude-transients" type="checkbox" value="1" autocomplete="off" name="exclude_transients" checked="&quot;checked&quot;">
                        Exclude <a href="https://codex.wordpress.org/Transients_API" target="_blank">transients</a> (temporary cached data)
                    </label>
                </li>

                <li>
                    <label for="exclude-post-revisions">
                        <input id="exclude-post-revisions" type="checkbox" autocomplete="off" value="1" name="exclude_post_revisions">
	                    <?php _e('Exclude post revisions', 'wpkbd') ?>

                    </label>
                </li>
            </ul>

        </div>
    </div>




    <p>
        <input type="submit" class="button button-primary kbdfr_submit" name="kbdfr_submit" value="<?php _e('Find & Replace', 'wpkbd') ?>">
    </p>


</form>

<div id="find_replace_modal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <div class="modal-header">
            <span class="close fnr_cancel">&times;</span>
            <h2><?php _e('Find and Replace', 'wpkbd') ?></h2>
        </div>
        <div class="modal-body">

            <div class="fnr_status" style="margin-bottom: 10px; font-size: 14px;"><?php _e('Searching in tables', 'wpkbd') ?></div>

            <div id="myprogress"  class="kbd_progress">
                <div id="myBar" class="kbd_bar"></div>
            </div>

            <div class="" style="margin-top: 10px; font-size: 14px; float: right"><span class="fnr_tbl_no">1</span>/<span class="fnr_total_table">45</span></div>

        </div>

    </div>

</div>