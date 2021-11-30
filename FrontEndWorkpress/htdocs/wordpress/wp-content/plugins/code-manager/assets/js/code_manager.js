/**
 * JavaScript code to add interactive features to Code Manager data entry page
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */

const PHP_DEFAULT = '<?php\n\n?>';

var user_has_edited = false;

var href = window.location.href;
var pathname = href.substring(0, href.lastIndexOf('/')) + '/admin-ajax.php';

var cm = null;

jQuery(function () {
	var editorSettings = cm_settings;
	var code_type = jQuery('#code_type').val()===null ? '' : jQuery('#code_type').val();
	if (code_type.includes('html')) {
		// Load HTML settings
		editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
		editorSettings.codemirror = _.extend(
			{},
			editorSettings.codemirror,
			{
				mode: 'html',
			}
		);
	} else if (code_type.includes('css')) {
		// Load CSS settings
		editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
		editorSettings.codemirror = _.extend(
			{},
			editorSettings.codemirror,
			{
				mode: 'css',
			}
		);
	} if (code_type.includes('javascript')) {
		// Load JavaScript settings
		editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
		editorSettings.codemirror = _.extend(
			{},
			editorSettings.codemirror,
			{
				mode: 'javascript',
			}
		);
	} else {
		// Load PHP settings (default = cm_settings)
		if (code==='') {
			code = PHP_DEFAULT;
		}
	}
	cm = wp.codeEditor.initialize(jQuery('#code'), editorSettings);

	jQuery('#code').parent().css('display','grid').css('width','100%');

	jQuery('#code_name').on('keydown', function() {
		user_has_edited = true;
	});
	jQuery('#code_type').on('focus', function(event) {
		jQuery(this).data({current_value:jQuery(this).val()});
	});
	jQuery('#code_type').on('change', function(event) {
		if (jQuery('#code_id').val()=='') {
			if (jQuery('#code_type option:selected').text().toLowerCase().includes('php')) {
				// Add php tags
				if (cm.codemirror.getValue()==='') {
					cm.codemirror.setValue(PHP_DEFAULT);
				}
			} else {
				// Remove php tags (clear field)
				if (cm.codemirror.getValue()===PHP_DEFAULT) {
					cm.codemirror.setValue('');
				}
			}

			user_has_edited = true;
			return;
		}

		html = '<div>Are you sure you want to change the code type? To prevent errors this code will be disabled!</div>';
		var dialog =
			jQuery(html)
			.data('current_element',jQuery(this))
			.data('current_value',jQuery.data(this, 'current_value'))
			.dialog({
				dialogClass: 'no-close',
				title: 'Change code type?',
				buttons: {
					'Yes': function() {
						user_has_edited = true;
						dialog.dialog('destroy');
					},
					'No':  function() {
						jQuery(jQuery.data(this, 'current_element')).val(jQuery.data(this, 'current_value'));
						dialog.dialog('destroy');
					},
					'Cancel':  function() {
						jQuery(jQuery.data(this, 'current_element')).val(jQuery.data(this, 'current_value'));
						dialog.dialog('destroy');
					}
				}
			});
	});
	cm.codemirror.on('change',function(){
		user_has_edited = true;
	});
	jQuery('#submit_button').on('click', function() {
		user_has_edited = false;
	});
	jQuery(window).on('beforeunload', function(){
		if ( user_has_edited === true ) {
			return 'Your changes will not be saved! Are you sure you want to leave this page?';
		}
	});

	jQuery('.cm_menu_title').tooltip();
	jQuery('td.label label').tooltip();
	jQuery('#code_manager_preview').tooltip();
});

function activate_code() {
	jQuery.ajax({
		method: 'POST',
		url: pathname + '?action=code_manager_activate_code_preview',
		data: {
			wpnonce: wpnonce,
			code_id: jQuery('#code_id').val(),
			page: 'code_manager_post'
		}
	}).done(
		function(msg) {
			if (msg==='OK') {
				jQuery.notify('Preview activated', 'success');
			} else {
				jQuery.notify(msg, 'error');
			}
		}
	);
}

function deactivate_code() {
	jQuery.ajax({
		method: 'POST',
		url: pathname + '?action=code_manager_deactivate_code_preview',
		data: {
			wpnonce: wpnonce,
			code_id: jQuery('#code_id').val(),
			page: 'code_manager_post'
		}
	}).done(
		function(msg) {
			if ( msg === 'OK' ) {
				jQuery.notify('Preview deactivated', 'success');
			} else {
				jQuery.notify(msg, 'error');
			}
		}
	);
}
