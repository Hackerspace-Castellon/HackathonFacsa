/**
 * JavaScript code to build Code Manager IDE in tab mode
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */

const PHP_DEFAULT = '<?php\n\n?>';

var user_has_edited = {};

var current_url = window.location.href.split('?');
var default_url = current_url[0] + '?page=code_manager';

var href = window.location.href;
var pathname = href.substring(0, href.lastIndexOf('/')) + '/admin-ajax.php';

var tabs = 0;
var cm_editors = {};
var new_label_index = 0;

function option_in_code_list(option_value) {
	found = false;
	jQuery('#code_manager_code_list option').each(function() {
		if (this.value === option_value) {
			found = true;
			return true;
		}
	});
	return found;
}

function get_code_list(callback) {
	jQuery.ajax({
		method: 'POST',
		url: pathname + '?action=code_manager_get_code_list',
		data: {
			wpnonce: wpnonce,
			page: 'code_manager_post'
		}
	}).done(
		function(msg) {
			try {
				obj = JSON.parse(msg);

				if (Array.isArray(obj)) {
					jQuery('#code_manager_code_list').empty();
					maxLength = 0;
					codeTypes = {};
					// Determine max length
					for (var key in obj) {
						if (obj[key].code_name.length>maxLength) {
							maxLength = obj[key].code_name.length;
						}
					}
					// Add option - use monospace to right align code types
					for (var key in obj) {
						if (!is_code_edited(obj[key].code_id)) {
							jQuery('#code_manager_code_list')
							.append(
								jQuery('<option>', {value: obj[key].code_id})
								.html(obj[key].code_name + "&nbsp;".repeat( maxLength - obj[key].code_name.length) + "&nbsp;&nbsp;&minus;&nbsp;&nbsp;" + obj[key].code_type)
								.attr('data-name', obj[key].code_name)
								.attr('data-type', obj[key].code_type)
								.attr('data-enabled', obj[key].code_enabled)
								.attr('data-preview', obj[key].preview_enabled)
							);
							codeTypes[obj[key].code_type] = true;
						}
					}
					// Update filters
					jQuery("#code_manager_filter_code_type").empty();
					jQuery("#code_manager_filter_code_type")
					.append(
						jQuery("<option>", {value: ""})
						.html("Filter code type")
					);
					Object.keys(codeTypes).sort().forEach(function(codeType, index) {
						jQuery("#code_manager_filter_code_type")
						.append(
							jQuery("<option>", {value: codeType})
							.html(codeType)
						);
					});
					callback();
				}
			}
			catch (e) {
				if (msg.substr(0, 3) === 'ERR') {
					jQuery.notify(msg, "error");
				} else {
					jQuery.notify(e, "error");
				}
			}
		}
	);
}

function is_code_edited(code_id) {
	for (var editor in cm_editors) {
		if (cm_editors[editor].tab_code_index==code_id) {
			return true;
		}
	}
	return false;
}

function get_code(code_id, code_name, code_type, code_enabled, preview_enabled) {
	jQuery.ajax({
		method: 'POST',
		url: pathname + '?action=code_manager_get_code',
		data: {
			wpnonce: wpnonce_get_code,
			code_id: code_id,
			page: 'code_manager_post'
		}
	}).done(
		function(msg) {
			if (msg==='ERR-Not authorized') {
				jQuery.notify('Not authorized', 'error');
			} else if (msg==='ERR-Wrong arguments') {
				jQuery.notify('Wrong arguments', 'error');
			} else {
				tab_load(code_id, code_name, code_type, msg, code_enabled, preview_enabled);
			}
		}
	);
}

function set_copy_shortcode_link(tab) {
	if (cm_editors[tab].tab_code_index!==-1 && jQuery("#tab-" + tab + "-code_type").val().includes("shortcode")) {
		code_name = jQuery("#tab-" + tab + "-label").text();
		jQuery("#tab-" + tab + "-shortcode_link").html(`
			<a href="javascript:void(0)" class="dashicons dashicons-image-rotate" onclick="jQuery('#cm_copy_id${tab}').toggle(); jQuery('#cm_copy_name${tab}').toggle();" style="vertical-align:middle"></a>
			<span id="cm_copy_id${tab}" style="display:none">
				[cmruncode id="${cm_editors[tab].tab_code_index}"]
				<a href="javascript:void(0)" class="dashicons dashicons-clipboard c2c" 
				onclick="jQuery.notify(&quot;Shortcode copied to clipboard&quot;, &quot;info&quot;)" 
				data-clipboard-text="[cmruncode id=&quot;${cm_editors[tab].tab_code_index}&quot;]" 
				title="Copy shortcode to clipboard" style="vertical-align:middle"
				></a>
			</span>
			<span id="cm_copy_name${tab}">
				[cmruncode name="${code_name}"]
				<a href="javascript:void(0)" class="dashicons dashicons-clipboard c2c" 
				onclick="jQuery.notify(&quot;Shortcode copied to clipboard&quot;, &quot;info&quot;)" 
				data-clipboard-text="[cmruncode name=&quot;${code_name}&quot;]" 
				title="Copy shortcode to clipboard" style="vertical-align:middle"
				></a>
			</span>
		`);
		new ClipboardJS('.c2c');
	} else {
		jQuery("#tab-" + tab + "-shortcode_link").html('');
	}
}

function tab_unselect() {
	jQuery('.nav-tab-active').removeClass('nav-tab-active');
	jQuery('.nav-tab-content').hide();
}

function tab_new(tab_label, code_id = -1, code_type = 'PHP Shortcode', code = '', code_enabled = 0, preview_enabled = 'false') {
	tab_unselect();

	code_types = '';
	for (var code_group in code_manager_code_groups) {
		code_types += '<optgroup label="' + code_group + '">';
		code_manager_code_group = code_manager_code_groups[code_group];
		for (var label in code_manager_code_group ) {
			if (code_type===label) {
				selected = ' selected';
			} else {
				selected = '';
			}
			code_types +=
				'<option value="' + label + '"' + selected + '>' +
				code_manager_code_group[label] +
				'</option>';
		}
		code_types += '</optgroup>';
	}

	jQuery('#code_manager_taskbar_tabmode')
	.append(
		'<a id="tab-' + tabs + '" href="javascript:void(0)" class="nav-tab nav-tab-active" onclick="tab_clicked(' + tabs + ')" data-code-manager-tab="' + tabs + '">' +
		'<span id="tab-' + tabs + '-label" contenteditable="true" class="code_manager_tab_label cm_tooltip" data-code-manager-tab="' + tabs + '" title="Double click to change code name">' + tab_label + '</span>' +
		'<span id="tab-' + tabs + '-icon" class="dashicons dashicons-dismiss icon_close" onclick="tab_close(this,event,' + tabs + ')"></span>' +
		'</a>'
	);

	var editorSettings = cm_settings;
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

	code_id_displayed = code_id==-1 ? 'new' : code_id;
	code_is_enabled = code_enabled==0 ? '' : 'checked';
	code_preview_enabled = preview_enabled=='false' ? '' : 'checked';

	if (code_type.includes('file')) {
		element_code_enabled =
			'<select id="tab-' + tabs + '-cb-enable" ' + code_is_enabled + ' onchange="return tab_icon_enable(' + tabs + ')" title="Enable code" class="cm_tooltip" style="margin-right:15px">' +
			'<option value="0">Disabled</option>' +
			'<option value="1">Admin enabled</option>' +
			'<option value="2">Public enabled</option>' +
			'<option value="3">Both enabled</option>' +
			'</select>';
	} else {
		element_code_enabled =
			'<label class="cm_tooltip" style="padding-right:15px" title="Enable code">' +
			'<input type="checkbox" id="tab-' + tabs + '-cb-enable" ' + code_is_enabled + ' onclick="return tab_icon_enable(' + tabs + ')" />' +
			'Enable' +
			'</label>';
	}

	jQuery('#code_manager_workspace_tabmode')
	.append(
		'<div id="tab-' + tabs + '-div" class="nav-tab-content">' +
		'<div id="tab-' + tabs + '-taskbar" class="tab_task_bar">' +
		'<a href="javascript:void(0)" class="button non-active cm_tooltip" id="tab-' + tabs + '-icon-save" disabled="true" onclick="tab_icon_save(' + tabs + ')" title="Save code">' +
		'<span class="dashicons dashicons-yes-alt" style="padding-top: 4px;"></span>' +
		'</a>' +
		'&nbsp;' +
		'<select id="tab-' + tabs + '-code_type" class="cm_tooltip" title="Select code type">' +
		code_types +
		'</select>' +
		'&nbsp;' +
		'<span>ID: ' +
		'<span id="tab-' + tabs + '-code_id" style="font-weight: bold">' + code_id_displayed + '</span>' +
		'</span>' +
		'<span id="tab-' + tabs + '-shortcode_link" style="padding-left:10px"></span>' +
		'<span style="float:right;line-height:2">' +
		element_code_enabled +
		'<label class="cm_tooltip" style="padding-right:5px" title="Enable preview mode for this code"><input type="checkbox" class="cm-cb-preview" id="tab-' + tabs + '-cb-preview"' + code_preview_enabled + ' onclick="return tab_icon_preview(' + tabs + ')" />Preview</label>' +
		'<label class="material-icons cm_tooltip" title="Refresh tab" onclick="return tab_refresh(' + tabs + ')">refresh</label>' +
		'</span>' +
		'</div>' +
		'<div id="tab-' + tabs + '-code" class="tab_code" data-code-manager-tab="' + tabs + '">' +
		'<textarea id="tab-' + tabs + '-content">' +
		code.replace(/&(?!amp;)/g, '&amp;')+
		'</textarea>' +
		'</div>' +
		'</div>'
	);

	var tab_editor = wp.codeEditor.initialize(jQuery('#tab-' + tabs + '-content'), editorSettings);

	tab_editor.codemirror.focus();
	tab_editor.codemirror.setCursor(1);
	tab_editor.codemirror.setOption('tabindex', tabs);

	var editor = {
		tab_index: tabs,
		tab_code_index: code_id,
		tab_label: tab_label,
		tab_editor: tab_editor
	};
	cm_editors[tabs] = editor;
	user_has_edited[tabs] = false;

	tab_editor.codemirror.on('change', function(cm_editor){
		tab_changed(cm_editor.getOption('tabindex'));
	});

	// Enable tooltip
	jQuery('.cm_tooltip').tooltip();

	// Add shortcode link if applicable
	set_copy_shortcode_link(tabs);

	jQuery('#tab-' + tabs + '-code_type').on('focus', function(event) {
		jQuery(this).data({current_value:jQuery(this).val()});
	});

	jQuery('#tab-' + tabs + '-code_type').on('change', {tab:tabs,cid:code_id}, function(event) {
		if (event.data.cid===-1) {
			if (jQuery('#tab-' + event.data.tab + '-code_type option:selected').text().toLowerCase().includes('php')) {
				// Add php tags
				if (cm_editors[event.data.tab].tab_editor.codemirror.getValue()==='') {
					cm_editors[event.data.tab].tab_editor.codemirror.setValue(PHP_DEFAULT);
				}
			} else {
				// Remove php tags (clear field)
				if (cm_editors[event.data.tab].tab_editor.codemirror.getValue()===PHP_DEFAULT) {
					cm_editors[event.data.tab].tab_editor.codemirror.setValue('');
				}
			}

			tab_changed(event.data.tab);
			return;
		}

		html = '<div>Are you sure you want to change the code type? Due to possible errors, this code will be disabled!</div>';
		var dialog =
			jQuery(html)
				.data('current_element',jQuery(this))
				.data('current_value',jQuery.data(this, 'current_value'))
				.data('current_tab',event.data.tab)
				.dialog({
			dialogClass: 'no-close',
			title: 'Change code type?',
			buttons: {
				'Yes': function() {
					tab_changed(jQuery.data(this, 'current_tab'));
					jQuery("#tab-" + tab + "-cb-enable").prop("checked", false);
					set_copy_shortcode_link(tab);
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

	jQuery('#tab-' + tabs + '-label').on('input', {tab:tabs}, function(event) {
		tab_changed(event.data.tab);
	});

	jQuery('#tab-' + tabs + '-label').on('dblclick', function() {
		var cell = this;
		var range, selection;
		if (document.body.createTextRange) {
			range = document.body.createTextRange();
			range.moveToElementText(cell);
			range.select();
		} else if (window.getSelection) {
			selection = window.getSelection();
			range = document.createRange();
			range.selectNodeContents(cell);
			selection.removeAllRanges();
			selection.addRange(range);
		}
	});

	tabs++;
}

function tab_changed(tabindex) {
	user_has_edited[tabindex] = true;
	jQuery('#tab-' + tabindex + '-icon').addClass('tab_unsaved_changes');
	jQuery('#tab-' + tabindex + '-icon-save').removeClass('non-active').attr('disabled', false);
}

function activate_code_on_server(tab, val, txt) {
	jQuery.ajax({
		type: 'POST',
		url: pathname + '?action=code_manager_activate_code',
		data: {
			code_id: cm_editors[tab].tab_code_index,
			code_item_value: val,
			wpnonce: wpnonce,
			page: 'code_manager_post'
		}
	}).done(
		function (msg) {
			if ( msg.substr(0, 3) === 'UPD' ) {
				jQuery.notify(txt, 'success');
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

function tab_refresh(tab) {
	if (cm_editors[tab].tab_code_index===-1) {
		jQuery.notify('Code needs to be saved first', 'error');
	} else {
		jQuery.ajax({
			method: 'POST',
			url: pathname + '?action=code_manager_get_code',
			data: {
				wpnonce: wpnonce_get_code,
				code_id: cm_editors[tab].tab_code_index,
				page: 'code_manager_post',
				wpda_action: 'all'
			}
		}).done(
			function(data) {
				json = jQuery.parseJSON(data);

				cm_editors[tab].tab_label = json.code_name;

				cm = cm_editors[tab].tab_editor.codemirror;
				cm.setValue(json.code);
				cm.save();

				jQuery("#tab-" + tab + "-label").text(json.code_name);
				jQuery("#tab-" + tab + "-code_type").val(json.code_type);
				jQuery("#tab-" + tab + "-cb-enable").prop("checked", json.code_enabled==="1");

				mark_tab_as_unchanged(cm);

				set_copy_shortcode_link(tab);

				jQuery.notify('Refresh completed', 'success');
			}
		);
		jQuery.ajax({
			method: 'POST',
			url: pathname + '?action=code_manager_is_code_preview_enabled',
			data: {
				wpnonce: wpnonce_get_code,
				code_id: cm_editors[tab].tab_code_index,
				page: 'code_manager_post',
			}
		}).done(
			function(data) {
				jQuery("#tab-" + tab + "-cb-preview").prop("checked", data==="true");
			}
		);
	}
}

function activate_preview_on_server(tab) {
	jQuery.ajax({
		method: 'POST',
		url: pathname + '?action=code_manager_activate_code_preview',
		data: {
			wpnonce: wpnonce,
			code_id: cm_editors[tab].tab_code_index,
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

function tab_open() {
	get_code_list(tab_open_callback);
}

function tab_open_callback() {
	var dialog = jQuery(jQuery('#code_manager_open_frame')).dialog({
		dialogClass: 'no-close',
		width: "inherit",
		resizable: false,
		title: 'Select code from list',
		buttons: {
			'Open': function() {
				var code_list = jQuery('#code_manager_code_list').val();
				if (code_list.length===0) {
					alert("Nothing selected!")
				} else {
					if (code_list[0]==='Loading data...') {
						alert("Nothing selected!")
					} else {
						for (var i=0; i<code_list.length; i++) {
							get_code(
								code_list[i],
								jQuery('#code_manager_code_list option[value=' + code_list[i] + ']').attr('data-name'),
								jQuery('#code_manager_code_list option[value=' + code_list[i] + ']').attr('data-type'),
								jQuery('#code_manager_code_list option[value=' + code_list[i] + ']').attr('data-enabled'),
								jQuery('#code_manager_code_list option[value=' + code_list[i] + ']').attr('data-preview')
							);
						}
						dialog.dialog('close');
					}
				}
			},
			'Cancel':  function() {
				dialog.dialog('close');
			}
		}
	});
	jQuery("#code_manager_code_list").unbind("dblclick");
	jQuery("#code_manager_code_list").on("dblclick", function() {
		tab = jQuery(this).val()[0];
		get_code(
			tab,
			jQuery('#code_manager_code_list option[value=' + tab + ']').attr('data-name'),
			jQuery('#code_manager_code_list option[value=' + tab + ']').attr('data-type'),
			jQuery('#code_manager_code_list option[value=' + tab + ']').attr('data-enabled'),
			jQuery('#code_manager_code_list option[value=' + tab + ']').attr('data-preview')
		);
		dialog.dialog('close');
	});
}

function tab_close(elem, event, tab) {
	if (user_has_edited[tab]===true) {
		html = '<div>Your changes will not be saved!</div>';
		var dialog = jQuery(html).dialog({
			dialogClass: 'no-close',
			title: 'Close tab?',
			buttons: {
				'Yes': function() {
					close_tab(elem, tab);
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
	} else {
		close_tab(elem, tab);
	}
	event.stopPropagation();
}
function close_tab(elem, tab) {
	jQuery(elem).parent().remove();
	jQuery('#tab-' + tab + '-div').remove();

	cm = cm_editors[tab].tab_editor.codemirror;
	cm.setOption('mode', 'text/x-csrc');
	cm.getWrapperElement().parentNode.removeChild(cm.getWrapperElement());
	cm=null;
	delete cm_editors[tab];
	delete user_has_edited[tab];

	if (!jQuery('.nav-tab-active').length) {
		index_selected = -1;

		for (editor in cm_editors) {
			tab_index = cm_editors[editor].tab_editor.codemirror.getOption('tabindex');
			if (index_selected>tab) {
				break;
			}
			index_selected = tab_index;
		}

		if (index_selected>-1) {
			tab_activate(index_selected);
		}
	}
}

function tab_activate(tab) {
	jQuery('#tab-' + tab).addClass('nav-tab-active');
	jQuery('#tab-' + tab + '-div').show();
}

function tab_load(code_id, tab_label, code_type, code, code_enabled, preview_enabled) {
	tab_new(tab_label, code_id, code_type, code, code_enabled, preview_enabled);
}

function tab_clicked(tab) {
	source_id = jQuery('.nav-tab-active').attr('data-code-manager-tab');
	if (source_id===tab) {
		return;
	}

	jQuery('.nav-tab-active').removeClass('nav-tab-active');
	jQuery('.nav-tab-content').hide();
	jQuery('#tab-' + tab).addClass('nav-tab-active');
	jQuery('#tab-' + tab + '-div').show();
}

function tab_icon_save(tab) {
	tab_label = jQuery('#tab-' + tab + '-label').text();
	old_label = cm_editors[tab].tab_label;
	if (old_label!==tab_label) {
		code_name_exists(tab_label, old_label, tab)
	} else {
		save_code(tab);
	}
}

function code_name_exists(code_name, old_label, tab) {
	jQuery.ajax({
		method: 'POST',
		url: pathname + '?action=code_manager_code_name_exists',
		data: {
			wpnonce: wpnonce_get_code,
			code_name: code_name,
			page: 'code_manager_post'
		}
	}).done(
		function(msg) {
			if (msg!=='OK') {
				jQuery.notify('Name already exists', 'error');
				jQuery('#tab-' + tab + '-label').text(old_label);
			} else {
				save_code(tab);
			}
		}
	);
}

function save_code(tab, successmsg = 'Code saved') {
	if (user_has_edited[tab]===false) {
		return;
	}

	cm = cm_editors[tab].tab_editor.codemirror;
	cm.save();

	jQuery.ajax({
		method: 'POST',
		url: pathname + '?action=code_manager_update_code',
		data: {
			wpnonce: wpnonce,
			code_id: cm_editors[tab].tab_code_index,
			code_name: jQuery('#tab-' + tab + '-label').text(),
			code_type: jQuery('#tab-' + tab + '-code_type').val(),
			code: jQuery('#tab-' + tab + '-content').val(),
			page: 'code_manager_post'
		}
	}).done(
		function(msg) {
			if ( msg.substr(0, 3) === 'UPD' ) {
				jQuery.notify(successmsg, 'success');
				mark_tab_as_unchanged(cm);
				set_copy_shortcode_link(tab);
			} else if ( msg.substr(0, 3) === 'INS' ) {
				code_id = msg.substr(4);
				cm_editors[tab].tab_code_index = code_id;
				jQuery('#tab-' + tab + '-code_id').html(code_id);
				set_copy_shortcode_link(tab);
				jQuery.notify(successmsg, 'success');
				mark_tab_as_unchanged(cm);
			} else {
				if (msg.length>50) {
					errormsg = 'ERROR : ' + msg.substr(0,50) + '...';
				} else {
					errormsg = msg;
				}
				jQuery.notify(errormsg, 'error');
			}
		}
	);
}

function mark_tab_as_unchanged(cm_editor) {
	tabindex = cm_editor.getOption('tabindex');
	user_has_edited[tabindex] = false;
	jQuery('#tab-' + tabindex + '-icon').removeClass('tab_unsaved_changes');
	jQuery('#tab-' + tabindex + '-icon-save').addClass('non-active').attr('disabled', true);
}

function tab_icon_enable(tab) {
	if (cm_editors[tab].tab_code_index===-1) {
		jQuery.notify('Your code must be saved before it can be enabled', 'info');
		return false;
	}

	if (jQuery('#tab-' + tab + '-cb-enable').attr('type')==='checkbox') {
		if (jQuery('#tab-' + tab + '-cb-enable').is(':checked')) {
			val = '1';
			txt = 'Code enabled';
		} else {
			val = '0';
			txt = 'Code disabled';
		}
	} else {
		val = jQuery('#tab-' + tab + '-cb-enable').val();
		txt = 'Code ' + jQuery('#tab-' + tab + '-cb-enable :selected').text().toLowerCase();
	}

	activate_code_on_server(tab, val, txt);

	return true;
}

function tab_icon_preview(tab) {
	if (cm_editors[tab].tab_code_index===-1) {
		jQuery.notify('Your code must be saved before it can be previewed', 'info');
		return false;
	}

	if (jQuery('#tab-' + tab + '-cb-preview').is(':checked')) {
		// Activate preview
		activate_preview_on_server(tab);
	} else {
		// Deactivate preview
		tab_icon_deactivate(tab);
	}

	return true;
}

function tab_icon_deactivate(tab) {
	jQuery.ajax({
		method: 'POST',
		url: pathname + '?action=code_manager_deactivate_code_preview',
		data: {
			wpnonce: wpnonce,
			code_id: cm_editors[tab].tab_code_index,
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

function unsaved_changes() {
	has_edited = false;

	for (var tabindex in user_has_edited) {
		if (user_has_edited[tabindex]===true) {
			has_edited = true;
		}
	}

	return has_edited;
}

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
				jQuery('.cm-cb-preview').prop('checked', false);
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

jQuery(function() {
	tab_new('New');
	new_label_index++;

	jQuery('#code_manager_new').on('click', function(e) {
		tab_new('New_' + (new_label_index++), -1);
	});

	jQuery('#code_manager_open').on('click', function(e) {
		tab_open();
	});

	jQuery('#code_manager_cancel_file').on('click', function() {
		jQuery('#code_manager_open_frame').hide();
	});

	jQuery('#code_manager_taskbar_tabmode').sortable();

	jQuery(window).on('keydown', function(event) {
		if ((event.ctrlKey || event.metaKey) && String.fromCharCode(event.which).toLowerCase()==='s') {
			if (
				jQuery(event.target).hasClass('CodeMirror-code') ||
				jQuery(event.target).hasClass('code_manager_tab_label')
			) {
				if (jQuery(event.target).hasClass('CodeMirror-code')) {
					tab_code = jQuery(event.target).closest('.tab_code');
					tab = tab_code.attr('data-code-manager-tab');
				} else {
					tab = jQuery(event.target).attr('data-code-manager-tab');
				}
				if (typeof tab !== typeof undefined && tab !== false) {
					save_code(tab);
					event.preventDefault();
				}
			}
		} else if (
			(event.ctrlKey || event.metaKey) &&
			String.fromCharCode(event.which).toLowerCase()==='v' &&
			jQuery(event.target).hasClass('code_manager_tab_label')
		) {
			event.preventDefault(); // Prevent ctrl-v on tab label
		}
	});

	jQuery('.cm_menu_title').tooltip();
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

	jQuery(window).on('beforeunload', function() {
		if ( unsaved_changes() ) {
			return 'Your changes will not be saved! Are you sure you want to leave this page?';
		}
	});
});
