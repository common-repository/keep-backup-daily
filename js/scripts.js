// JavaScript Document
jQuery(document).ready(function($){
	var kbd_current_url = new URL(window.location.href);
	var kbd_current_page = kbd_current_url.searchParams.get('page');

	$('.dismiss_link').click(function(){
			$(this).parent().slideUp();
			$('.useful_link').fadeIn();
		});

		$('.useful_link').click(function(){
			$('.dismiss_link').parent().slideDown();
			$(this).fadeOut();
		});
	
	$('#kbd_backup_now').click(function(){
		document.location.href = 'options-general.php?page=kbd_download';
	});
	
	$('.tog').click(function(){
	
		switch($(this).val()){
			case 'default':
				$('.cron_line').hide();
			break;
			case 'custom':
				$('.cron_line').show();
			break;
		}
		
	
	});
	
	$('#cron_now').click(function(){
		
		var dh = $(this).html();
	
		$(this).parent().append('<p class="sending_backup">Sending to '+$('#recpient_email_address').val()+'</p>');
		$(this).html('Please wait...');
		
		var jqxhr = $.get($('.cron_line input').val(), function() {
		
		})
		.done(function() { $('.sending_backup').html('Successfully sent.'); })
		.fail(function() { $('.sending_backup').html('Failed.'); })
		.always(function() { $('.sending_backup').html('Please check your inbox.'); });
		
		$(this).html(dh);
	
		
	});
		
		$('input[name="backup_required"][checked="checked"]').parent().addClass('active');
		
	$('label.btn.btn-primary').click(function(){
		$(this).parent().find('label.btn.btn-primary.active').find('input').removeAttr('checked');
		$(this).parent().find('label.btn.btn-primary.active').removeClass('active');
		$(this).addClass('active');
		$(this).find('input').attr('checked', 'checked');
	});
	
	if($('.kbd-bkup-list').length>0){
		$('.kbd-bkup-list li a.kbd-bkup-title').on('click', function(){
			$(this).hide();
			$(this).parent().find('input[type="text"]').show().focus();
		});
		
		$('.kbd-bkup-list li input[type="text"]').on('blur', function(){
			$(this).hide();
			var obj = $(this).parent().find('a.kbd-bkup-title');
			var val = $(this).val();
			obj.html(val).show();
			
			if(val!=''){
				
				var data = {
					'action': 'update_kbd_bkup_alias',
					'key': obj.data('key'),
					'val': val,
					'kbd_nonce': kbd_obj.kbd_ajax_nonce
				};
		
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				$.post(ajaxurl, data, function(response) {
					
				});			
			}
		});
	}
	

	$('[name="kbd_settings[switch_to_premium]"]').on('change', function(){

		var this_check = $(this);
		var switch_to_premimum = this_check.prop('checked');

		var page_pro = 'kbd_settings_pro';
		var page = 'kbd_settings';
		var url = window.location.href;
		

		var data = {

			'action': 'kbd_premium_init',
			'kbd_switch_to_premium': switch_to_premimum,
			'kbd_ajax_nonce_field': kbd_obj.kbd_ajax_nonce

		};

		$.post(ajaxurl, data, function(response) {

			//console.log(response);
			response = JSON.parse(response);
			if(response.status == true){

				window.location.href = response.url;

			}

		});

	});
	
	$('.kbd-restore').on('click', function(event){
		
		var ask = confirm(kbd_obj.kbd_restore_msg);
		if(!ask){
			event.preventDefault();
		}
	});

	$('.kbd_settings a.nav-tab').click(function(){

		var this_url = (kbd_current_page == 'kbd_download' ? kbd_obj.kbd_download_url : kbd_obj.this_url);
		$(this).siblings().removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		// , form:not(.wrap.wc_settings_div .nav-tab-content)'
		$('.nav-tab-content').hide();
		$('.nav-tab-content').eq($(this).index()).show();
		window.history.replaceState('', '', this_url+'&t='+$(this).index());
		$('form input[name="ingtr_tn"]').val($(this).index());
		kbd_obj.ingtr_tab = $(this).index();

	});

	$(".kbd-delete-backup").on('click', function(){

		var del_settings_url = $(this).data('settings');
		var del_file_url = $(this).data('file');
		$('#kbd_del_modal .only-settings').prop('href', del_settings_url);
		$('#kbd_del_modal .settings-file').prop('href', del_file_url);

	});


	/*Find and Replace section code starts*/


	$('.kbdfr table .add-row').on('click', function(){

		var original_repeatable_field = $('.kbdfr .original-repeatable-field').clone();

		original_repeatable_field.removeClass('original-repeatable-field');

		$('.replace-row').last().after(original_repeatable_field);

		original_repeatable_field.find('input').first().focus();

	});


	$('body').on('click','.kbdfr .replace-remove-row', function () {

		var parent = $(this).parents('tr');

		if($('.kbdfr .replace-remove-row').length > 2){

			parent.prev().find('input').first().focus();
			parent.remove();

		}else{

			parent.find('input').val('');
			parent.find('input').first().focus();
		}

	})



	
	var tables = (typeof kbfnr_data != 'undefined' ? kbfnr_data.this_tables : {}) ;
	var table_length = tables.length;
	var migration_state_id;
	var modal = $("#find_replace_modal");
	var fnr_tbl_no = $(".fnr_tbl_no");
	var fnr_total_table = $(".fnr_total_table");
	var kbd_bar = $(".kbd_bar");
	var fnr_status = $(".fnr_status");
	var cancel_btn = $(".fnr_cancel");
	var cancel_status = false;
	var complete_status = false;
	var cancel_confirm = false;

	cancel_btn.on('click', function(){

		if(!cancel_status && !complete_status){

			cancel_status = confirm(kbd_obj.cancel_confirm);

		}

		if(complete_status || cancel_confirm){

			location.href = location.href;
		}

	});

	function check_input_box(){

		var replace_old = $('.replace-row:not(.original-repeatable-field) input[name^="replace_old"]');

		var all_val = [];

		for(let i = 0; i < replace_old.length; i++){

			var current_old = replace_old[i];


			current_old = $(current_old);


			if(current_old.val().length > 0){
				all_val.push("1");

			}else{

				all_val.push("0");

			}

		}


		if($.inArray("1", all_val) == -1){

			alert(kbd_obj.required);
			return false;
		}else{
			return  true;
		}


	}

	function cancel_request(){

		var data_cancel = {

			action:"kbfnr_cancel_migration",
			migration_state_id:migration_state_id,
			nonce:kbfnr_data.nonces.cancel_migration,

		}

		$.post(ajaxurl, data_cancel, function(rcancel) {
			cancel_confirm = true;
			fnr_status.html(kbd_obj.cancel);

		});

	}

	function flush_request(){

		fnr_status.html(kbd_obj.flush_msg);

		var data_flush = {

			action:"kbfnr_flush",
			migration_state_id:migration_state_id,
			nonce:kbfnr_data.nonces.flush,

		}

		$.post(ajaxurl, data_flush, function(rflush) {

			complete_status = true;
			fnr_status.html(kbd_obj.done_msg);
			setTimeout(function(){
				window.location.href = window.location.href;
			}, 3000);
		});
	}

	function final_request(){

		fnr_status.html(kbd_obj.finalizing_msg);

		var data_final = {

			action:"kbfnr_finalize_migration",
			migration_state_id:migration_state_id,
			tables: tables.join(","),
			nonce:kbfnr_data.nonces.finalize_migration,

		}

		$.post(ajaxurl, data_final, function(rf) {

			flush_request();

		});

	}

	function recursive_table_request(index){


		var data_table = {

			action:"kbfnr_migrate_table",
			migration_state_id:migration_state_id,
			table: tables[index],
			stage:"find_replace",
			current_row: index == 0 ? "-1" : "",
			last_table:index == (table_length - 1) ? "1" : "0",
			primary_keys:"",
			gzip:"0",
			nonce:kbfnr_data.nonces.migrate_table,

		}

		$.post(ajaxurl, data_table, function(rs) {

			if(cancel_status){

				cancel_request();

			}else{

				index++;
				fnr_tbl_no.html(index);
				
				var result_percent = Math.ceil((index/table_length)*100)+"%";
				
				kbd_bar.css("width", result_percent);
				kbd_bar.html(result_percent);
				if(index < table_length){

					recursive_table_request(index);

				}else{

					final_request();

				}

			}



		});

	}

	$('.kbdfr_submit').on('click', function(e){

		e.preventDefault();

		if(!check_input_box()) return;

		var form_data = $('form.kbdfr').serialize();
		fnr_total_table.html(table_length);
		modal.show();

		var data = {

			action:"kbfnr_initiate_migration",
			intent:"find_replace",
			url:"",
			form_data:form_data,
			stage:"find_replace",
			nonce:kbfnr_data.nonces.initiate_migration,
			site_details:{ local: kbfnr_data.site_details },
		}

		data.site_details = JSON.stringify(data.site_details);

		$.post(ajaxurl, data, function(response){

			response = JSON.parse(response);
			migration_state_id = response.migration_state_id;

			if(migration_state_id != undefined){

				recursive_table_request(0);

			}

		});


	});

	/*Find and replace code end*/

	// KBD Export Media Code Start


	$('body').on('click', '.kbd_export_media .kbd_side_nav ul li span', function(e){

		var parent_li = $(this).parents('li:first');
		var parent_ul = parent_li.parents('ul:first');
		var dir_name = parent_li.data('dir_name');
		var dir_path = parent_li.data('path');
		var sub_dir_section = $('.kbd_export_media .kbd_sub_dir');
		var sub_dir_title = sub_dir_section.find('span.kbd_title');
		var sub_dir_list = sub_dir_section.find('ul.kbd_sub_dir_list');

		parent_ul.find('li').removeClass('kbd_dir_active');
		parent_li.addClass('kbd_dir_active');

		if(parent_ul.hasClass('kbd_dir_list')){

			sub_dir_title.html(dir_name);
			var data = {
				action: 'kbd_open_upload_dir',
				kbd_nonce: kbd_obj.kbd_ajax_nonce,
				kbd_path: dir_path
			}

			$('div.kbd_media_loader').show();
			sub_dir_list.html('');				
			$.post(ajaxurl, data, function(resp, code){
				$('div.kbd_media_loader').hide();
				if(code == 'success'){
					sub_dir_list.html(resp.html);
				}

			});	

		}		

	});

	$('.kbd_export_media ul.kbd_dir_list li:first span').click();

	function kbd_show_hide_export_button(){

		var checked_list = $('.kbd_export_media form input:checkbox:checked');
		var export_button = $('.kbd_export_media form button[name="kbd_export_selected"]');

		if(checked_list.length > 0){
			export_button.removeClass('d-none');
		}else{
			export_button.addClass('d-none');
		}

	}

	$('.kbd_sub_dir_list').on('change', 'input:checkbox', function(){

		var checked_list = $('.kbd_sub_dir_list input:checkbox:checked');

		if(checked_list.length > 0){
			$('.kbd_dir_list input:checkbox').prop('checked', false);
		}

		kbd_show_hide_export_button();
	});

	$('.kbd_dir_list').on('change', 'input:checkbox', function(){

		var checked_list = $('.kbd_dir_list input:checkbox:checked');

		if(checked_list.length > 0){
			$('.kbd_sub_dir_list input:checkbox').prop('checked', false);
		}

		kbd_show_hide_export_button();
	});


	// KBD Export Media Code End

	if(kbd_current_page == 'kbd_download'){
		var Kbd_active_tab = kbd_current_url.searchParams.get('t');
		
		if(Kbd_active_tab != null){
			Kbd_active_tab++;
			$('.nav-tab-wrapper .nav-tab:nth-child('+Kbd_active_tab+')').click();
		}
	}

	var kbd_backup_in_process = false;

	$('.wpkbd_download_latest_backup').on('click', function(e){
		e.preventDefault();
		if(!kbd_backup_in_process){
			kbd_backup_in_process = true;
		}else{
			alert(kbd_obj.kbd_backup_in_process);

			return;
		}
		var loader = $('div.kbd_btn_parent img');
		var success = $('div.kbd_btn_parent .dashicons');

		success.hide();
		loader.show();

		var data = {
			action: 'kbd_process_fresh_backup',
			kbd_nonce: kbd_obj.kbd_ajax_nonce,
			kbd_type: 'backup_process'
		}

		$.post(ajaxurl, data, function(resp, code){

			if(code == 'success'){

				setTimeout(function(){

					var data = {
						action: 'kbd_process_fresh_backup',
						kbd_nonce: kbd_obj.kbd_ajax_nonce,
						kbd_type: 'backup_html'
					}
			
					$.post(ajaxurl, data, function(resp, code){
			
						
						loader.hide();
						kbd_backup_in_process = false;
						if(code == 'success'){
							success.show();
							$('.kbd_latest_backup_container').html(resp.latest_html);
							$('.kbd_old_backup_container').html(resp.old_html);

							setTimeout(function(){
								success.fadeOut();
							}, 3000);
						}
			
					});
				}, 3000);

			}

		});
	});

	$('.kbd_download').on('click', '.kbd_del_backup', function(e){
		e.preventDefault();

		var del_confirm = confirm(kbd_obj.del_confirm);

		if(del_confirm){

			var del_url = new URL($(this).attr('href'));
			var t = kbd_current_url.searchParams.get('t');
			t = t != null ? t : 0;
			del_url.searchParams.set('t', t);
			window.location.href = del_url.href;

		}


	});

});

