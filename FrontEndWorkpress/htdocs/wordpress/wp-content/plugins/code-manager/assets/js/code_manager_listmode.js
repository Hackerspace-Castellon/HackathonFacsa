/**
 * JavaScript code to support code activation in list mode
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */

var href = window.location.href;
var pathname = href.substring(0, href.lastIndexOf('/')) + '/admin-ajax.php';

jQuery(function() {
	jQuery('#doaction').click(function () {
		return cm_action_button();
	});

	jQuery('#doaction2').click(function () {
		return cm_action_button();
	});

	new ClipboardJS('.c2c');

	jQuery(function(){
		jQuery('.cm_menu_title').tooltip();
		jQuery('.c2c').tooltip();
		jQuery('.cm_tooltip').tooltip();
		jQuery('#disable_preview').tooltip();
		jQuery('#disable_preview').on('click', function() {
			html = "<div>This turns of preview mode for all code IDs for all users. Do you want to continue?</div>";
			var dialog = jQuery(html).dialog({
				dialogClass: 'no-close',
				title: 'Reset preview',
				buttons: {
					'Yes': function() {
						dialog.dialog('destroy');
						reset_preview();
					},
					'No':  function() {
						dialog.dialog('destroy');
					},
					'Cancel':  function() {
						dialog.dialog('destroy');
					}
				}
			});
		});
	});

	code_type_list = '<label for="selected_code_type" style="float:left;font-weight:bold;padding-top:6px;">Show&nbsp;</label>';
	code_type_list += '<select id="selected_code_type" name="selected_code_type" onchange="jQuery(\'#cm_list_table\').submit()">';
	code_type_list += '<option value="*">All</option>';
	for (var code_group in code_manager_code_groups) {
		code_type_list += '<optgroup label="' + code_group + '">';
		code_manager_code_group = code_manager_code_groups[code_group];
		for (var label in code_manager_code_group ) {
			if (code_manager_selected_code_type===label) {
				selected = ' selected';
			} else {
				selected = '';
			}
			code_type_list +=
				'<option value="' + label + '"' + selected + '>' +
				code_manager_code_group[label] +
				'</option>';
		}
		code_type_list += '</optgroup>';
	}
	code_type_list += '</select>';
	jQuery('#doaction').after('<span style="float:right">' + code_type_list + '</span>');
	jQuery('#doaction').after(
		'<span style="float:right">' +
		'<a id="disable_preview" href="javascript:void(0)" class="material-icons cm_menu_title" style="text-decoration:none" title="Turn of preview mode for all code IDs">visibility_off</a>' +
		'</span>'
	);
});

function reset_preview() {
	jQuery.ajax({
		type: 'POST',
		url: pathname + '?action=code_manager_reset_preview',
		data: {
			wpnonce: wpnonce,
			page: 'code_manager_post'
		}
	}).done(
		function (msg) {
			if ( msg === 'OK' ) {
				jQuery.notify(msg, 'success');

				window.location.href = window.location.href;
			} else {
				jQuery.notify(msg, 'info');
			}
		}
	).fail(
		function () {
			jQuery.notify(msg, 'error');
		}
	);
}

function set_code_preview(id, wpnonce, checked) {
	action = checked ? 'code_manager_activate_code_preview' : 'code_manager_deactivate_code_preview';
	mode = checked ? 'on' : 'off';
	jQuery.ajax({
		type: 'POST',
		url: pathname + '?action=' + action,
		data: {
			code_id: id,
			wpnonce: wpnonce,
			page: 'code_manager_post'
		}
	}).done(
		function (msg) {
			if ( msg === 'OK' ) {
				jQuery.notify('Preview ' + mode, 'success');
			} else {
				jQuery.notify(msg, 'info');
			}
		}
	).fail(
		function () {
			jQuery.notify('The request could not be handled', 'error');
		}
	);
}

function activate_code(id, wpnonce) {
	if (jQuery('#code_enabled_' + id).is('select')) {
		// Listbox
		code_item_value = jQuery('#code_enabled_' + id).val();
	} else {
		// Checkbox
		code_item_value = jQuery('#code_enabled_' + id).is(':checked') ? '1' : '0';
	}

	jQuery.ajax({
		type: 'POST',
		url: pathname + '?action=code_manager_activate_code',
		data: {
			code_id: id,
			code_item_value: code_item_value,
			wpnonce: wpnonce,
			page: 'code_manager_post'
		}
	}).done(
		function (msg) {
			if ( msg.substr(0, 3) === 'UPD' ) {
				if (jQuery('#code_enabled_' + id).is(':checked')) {
					jQuery.notify('Code enabled', 'success');
				} else {
					jQuery.notify('Code disabled', 'success');
				}
			} else {
				jQuery.notify('Settings not saved', 'error');
			}
		}
	).fail(
		function () {
			jQuery.notify('The request could not be handled', 'error');
		}
	);
}

function cm_show_notice( value ) {
	if ('bulk-delete'===value) {
		html = "<div>You are about to permanently delete the selected code from your site. This action cannot be undone. 'No' to stop, 'Yes' to delete.</div>";
		var dialog = jQuery(html).dialog({
			dialogClass: 'no-close',
			title: 'Delete code?',
			modal: true,
			buttons: {
				'Yes': function() {
					jQuery('#cm_list_table').submit();
					dialog.dialog('destroy');
				},
				'No':  function() {
					dialog.dialog('destroy');
				},
				'Cancel':  function() {
					dialog.dialog('destroy');
				}
			}
		});
		event.preventDefault();
		event.stopPropagation();
		return false;
	}
}

function cm_action_button() {
	action1 = jQuery("#cm_list_table :input[name='action']").val();
	if (action1!=='-1') {
		return cm_show_notice(action1);
	}
	action2 = jQuery("#cm_list_table :input[name='action2']").val();
	if (action2!=='-1') {
		return cm_show_notice(action2);
	}
	jQuery.notify('No bulk action selected', 'info');
	return false;
}
