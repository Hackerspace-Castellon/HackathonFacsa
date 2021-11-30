const TAB_DEFAULT_LABEL = 'New Query';
const VISUAL_CORRECTION = 6;

var editors = {};
var tabIndex = 0;
var tabs = [];
var isChanged = {};
var isVisual = {};
var dbHints = {}
var columnLink = {};

function tabActivate(activeIndex) {
	jQuery(".wpda_query_builder").hide();
	jQuery("#wpda_query_builder_" + activeIndex).show();

	jQuery(".nav-tab").removeClass("nav-tab-active");
	jQuery("#wpda_query_builder_label_" + activeIndex).addClass("nav-tab-active");
}

function tabNew(tabName = TAB_DEFAULT_LABEL, query = '', schema_name = wpda_default_database) {
	tabIndex++;
	if (tabName===TAB_DEFAULT_LABEL) {
		tabName += " (" + tabIndex + ")";
		dbsName = '';
	} else {
		dbsName = tabName;
	}

	tabLabel = `
		<a id="wpda_query_builder_label_${tabIndex}" class="nav-tab wpda_query_builder_label" data-id="${tabIndex}" href="javascript:void(0)">
			<span id="wpda_query_builder_label_value_${tabIndex}" 
				  class="wpda_query_builder_label_value"
				  contenteditable="true" 
				  data-dbs-name="${dbsName}"
				  onclick="tabActivate('${tabIndex}')"
				  ondblclick="selectContent(event)"
			>${tabName}</span>
			<span id="tab-${tabIndex}-icon"
				  class="dashicons dashicons-dismiss icon_close"
				  style="vertical-align: middle"
				  onclick="tabClose('${tabIndex}')"
			></span>
		</a>`;
	jQuery("#wpda_query_builder nav.nav-tab-wrapper").append(tabLabel);
	document.getElementById("wpda_query_builder_label_value_" + tabIndex).ondblclick = function(){
		event.preventDefault();
		var sel = window.getSelection();
		var range = document.createRange();
		range.selectNodeContents(this);
		sel.removeAllRanges();
		sel.addRange(range);
	};

	tabContent = `
		<div id="wpda_query_builder_${tabIndex}" class="wpda_query_builder" data-id="${tabIndex}">
			<div class="wpda_query_builder_taskbar">
				<label>
					Select database
					<select id="wpda_query_builder_dbs_${tabIndex}" onchange="setHints('${tabIndex}')">${wpda_databases}</select>
				</label>
				<span class="wpda_query_builder_wordpress_protect">
					<label>
						<input id="wpda_query_builder_wordpress_protect_${tabIndex}" type="checkbox" checked />
						Protect WordPress tables
					</label>
				</span>
				<span class="wpda_query_builder_actions">
					<label>
						<input id="use_max_rows_${tabIndex}" type="checkbox" checked/>
						Max rows:
						<input id="max_rows_${tabIndex}" type="number" value="100" min="1" onblur="if (jQuery(this).val()==='') { jQuery(this).val(100) }" style="width: 100px"/>
					</label>
					<a href="javascript:void(0)" onclick="executeQuery('${tabIndex}')" class="wpda_tooltip button button-primary" title="Execute query">
						<span class="material-icons wpda_icon_on_button">play_arrow</span> Execute</a>
					<span id="executing_query_${tabIndex}" style="display: none">
						<img src="${wpda_loader_url}" class="wpda_spinner" />
					</span>
					<a href="javascript:void(0)" onclick="saveQuery('${tabIndex}')" class="wpda_tooltip button button-primary" title="Save query">
						<span class="material-icons wpda_icon_on_button">cloud_upload</span> Save</a>
					<button href="javascript:void(0)" class="wpda_tooltip button button-primary wpda_copy_to_clipboard" title="Copy query to clipboard" data-clipboard-text="ABC">
						<span class="material-icons wpda_icon_on_button">content_copy</span> Copy to clipboard</button>
						
					<a href="javascript:void(0)" class="wpda_tooltip button button-primary wpda-query-help" title="Use / to separate multiple SQL commands:

select * from dept
/
select * from emp
/

The / must be on an empty line
">?</a>
				</span>
			</div>
			<div id="wpda_query_builder_sql_container_${tabIndex}" class="wpda_query_builder_sql">
				<textarea id="wpda_query_builder_sql_${tabIndex}">${query}</textarea>
			</div>
			<div id="wpda_query_builder_tabs_${tabIndex}" class="wpda_query_builder_tabs" style="display: none"></div>
			${queryResult(tabIndex)}
		</div>`;
	jQuery("#wpda_query_builder").append(tabContent);

	editors['tab' + tabIndex] = wp.codeEditor.initialize(jQuery('#wpda_query_builder_sql_' + tabIndex), cm_settings);
	editors['tab' + tabIndex].codemirror.setOption('tabindex', tabIndex);
	editors['tab' + tabIndex].codemirror.on('change', function(cm_editor) {
		isChanged[cm_editor.getOption('tabindex')] = true;
	});

	jQuery("#wpda_query_builder_dbs_" + tabIndex).val(schema_name);
	jQuery('.wpda_tooltip').tooltip();
	new ClipboardJS(".wpda_copy_to_clipboard");
	jQuery(".wpda_copy_to_clipboard").on("click", { tabIndex: tabIndex }, function() {
		cm = editors['tab' + tabIndex].codemirror;
		cm.save();
		jQuery(this).attr("data-clipboard-text", jQuery("#wpda_query_builder_sql_" + tabIndex).val());
	});

	tabActivate(tabIndex);
	isChanged[tabIndex] = false;
	isVisual[tabIndex] = false;
	setHints(tabIndex);
}

function setHints(activeIndex) {
	var schemaName = jQuery("#wpda_query_builder_dbs_" + activeIndex).val();
	if (!dbHints[schemaName]) {
		jQuery.ajax({
			method: 'POST',
			url: wpda_home_url + "?action=wpda_query_builder_get_db_hints",
			data: {
				wpda_wpnonce: wpda_wpnonce,
				wpda_schemaname: schemaName
			}
		}).done(
			function (msg) {
				if (msg.status && msg.tables && msg.status === "OK") {
					tabTables = Object.assign({}, msg.tables);
					for (var table in msg.tables) {
						for (var i = 0; i < msg.tables[table].length; i++) {
							tabTables[msg.tables[table][i]] = [];
						}
					}
					editors['tab' + activeIndex].codemirror.options.hintOptions = {
						tables: tabTables
					}
					// Save tables for new tabs
					dbHints[schemaName] = tabTables;
					// Update visual component if enabled
					if (isVisual[activeIndex]) {
						updateVisual(activeIndex);
					}
				} else {
					editors['tab' + activeIndex].codemirror.options.hintOptions = {
						tables: null
					}
				}
			}
		).fail(
			function (msg) {
				console.log("WP Data Access ERROR:");
				console.log(msg);
				editors['tab' + activeIndex].codemirror.options.hintOptions = {
					tables: null
				}
			}
		);
	} else {
		editors['tab' + activeIndex].codemirror.options.hintOptions = {
			tables: dbHints[schemaName]
		}
		// Update visual component if enabled
		if (isVisual[activeIndex]) {
			updateVisual(activeIndex);
		}
	}

	editors['tab' + activeIndex].codemirror.on('keyup', (cm, event) => {
		if (!jQuery("#wpda_sql_hints").is(":checked")) {
			return;
		}

		if (
			event.key==="Backspace" ||
			event.key==="Escape" ||
			event.key==="ArrowUp" ||
			event.key==="ArrowDown"
		) {
			return;
		}

		editors['tab' + activeIndex].codemirror.execCommand('autocomplete');
	});
}

function queryResult(activeIndex) {
	return `
		<div id="wpda_query_builder_menubar_${activeIndex}" class="wpda_query_builder_menubar" style="display: none">
			<label>Export to</label>
			<button class="button button-primary" onclick="exportTable('CSV', ${activeIndex})">CSV</button>
			<button class="button button-primary" onclick="exportTable('JSON', ${activeIndex})">JSON</button>
			<button class="button button-primary" onclick="exportTable('XML', ${activeIndex})">XML</button>
		</div>
		<div id="wpda_query_builder_result_${activeIndex}" class="wpda_query_builder_result"></div>
		<div id="wpda_query_builder_statusbar_${activeIndex}" style="display: none" class="wpda_query_builder_statusbar">
			<a href="javascript:void(0)" onclick="jQuery('#wpda_query_builder_viewer_${activeIndex}').toggle(); jQuery('html, body').animate({ scrollTop: jQuery(window).height()-200}, 600);" class="wpda_tooltip button button-primary" title="View raw output">
				<span class="material-icons wpda_icon_on_button">code</span></a>
			<span class="wpda_query_builder_statusbar_message"></span>
		</div>
		<div id="wpda_query_builder_viewer_${activeIndex}" style="display: none" class="wpda_query_builder_viewer">
			<pre id="wpda_query_builder_json_${activeIndex}"></pre>
		</div>
	`;
}

function tabClose(activeIndex) {
	if (isChanged[activeIndex]) {
		if (!confirm('Your changes will not be saved! Are you sure you want to leave this page?')) {
			return;
		}
	}
	jQuery("#wpda_query_builder_label_" + activeIndex).remove();
	jQuery("#wpda_query_builder_" + activeIndex).remove();
	delete editors['tab' + activeIndex];
	delete isChanged[activeIndex];
	if (jQuery(".wpda_query_builder").length>0) {
		if (jQuery(".nav-tab-active").data("id")===undefined) {
			tabActivate(jQuery(".nav-tab").data("id"));
		}
	} else {
		tabNew();
	}
}

function tabOpen() {
	tabNew(
		jQuery("#wpda_query_builder_open_select").find(":selected").text(),
		jQuery("#wpda_query_builder_open_select").find(":selected").data("sql"),
		jQuery("#wpda_query_builder_open_select").find(":selected").data("dbs")
	);
	closeQuery();
}

function tabOpenAll() {
	jQuery("#wpda_query_builder_open_select option").each(
		function() {
			tabNew(
				jQuery(this).text(),
				jQuery(this).data("sql"),
				jQuery(this).data("dbs")
			);
			closeQuery();
		}
	);
}

function showData(activeIndex, msg) {
	if ( msg.tabs.length > 0 ) {
		// Multiple SQL commands
		jQuery("#wpda_query_builder_result_" + activeIndex).html('');
		showTabs(activeIndex, msg);
	} else {
		// Single SQL command
		jQuery("#wpda_query_builder_tabs_" + activeIndex).empty().hide();
		showRows(activeIndex, msg);
	}
}

function queryTabClose(activeIndex, tabIndex) {
	jQuery("li#litab" + activeIndex + "-" + tabIndex).remove();
	jQuery("div#tab" + activeIndex + "-" + tabIndex).remove();
	var li = jQuery("div#tabs" + activeIndex + " ul li")[0].id;
	jQuery("#" + li).find("a").click();
}

function showTabs(activeIndex, msg) {
	var ul = jQuery("<ul/>");
	for (var i=0; i<msg.tabs.length; i++) {
		if (msg.tabs[i]['cmd']===null || msg.tabs[i]['cmd']===undefined) {
			sql = "SQL ERROR";
		} else {
			sql = msg.tabs[i]['cmd'];
		}
		ul.append(jQuery("<li/>", { "id": "litab" + activeIndex + "-" + i })
			.append(jQuery("<a/>", { "href": "#tab" + activeIndex + "-" + i, "title": sql, "class": "wpda_tooltip" })
			.html("<span class='dashicons dashicons-database-view'></span> " + (i+1) + ". sql cmd <span class='dashicons dashicons-dismiss icon_close' style='vertical-align: middle' onclick='queryTabClose(" + activeIndex + "," + i + ")'></span>")));
	}
	var tabs = jQuery("<div/>", { "id": "tabs" + activeIndex }).append(ul);

	for (var i=0; i<msg.tabs.length; i++) {
		var tabResultDiv = queryResult("" + activeIndex + i);
		var style = i===0 ? "block" : "none";
		tabs.append(jQuery("<div/>", { "id": "tab" + activeIndex + "-" + i, "style": "display:"+style })
			.append(tabResultDiv));
	}
	jQuery("#wpda_query_builder_tabs_" + activeIndex).empty().append(tabs);
	jQuery("#wpda_query_builder_tabs_" + activeIndex).show();

	for (var i=0; i<msg.tabs.length; i++) {
		showRows("" + activeIndex + i, msg.tabs[i]);
	}
	jQuery("div#tabs" + activeIndex).tabs();
	jQuery('.wpda_tooltip').tooltip();
}

function showRows(activeIndex, msg) {
	if (msg.status===null || msg.status===undefined) {
		jQuery("#wpda_query_builder_result_" + activeIndex).html("<strong>WP Data Access error:</strong> Query failed");
	} else {
		if (msg.status.last_result===null || msg.status.last_result===undefined) {
			if (typeof msg.status!=="string") {
				jQuery("#wpda_query_builder_result_" + activeIndex).html("<strong>WP Data Access error:</strong> Query OK");
			} else {
				jQuery("#wpda_query_builder_result_" + activeIndex).html(msg.status);
			}
		} else {
			if (msg.status.last_error==="") {
				if (msg.status.last_result.length > 0) {
					rows = msg.status.last_result;
					first_row = rows[0];
					header = "<tr>";
					for (var col in first_row) {
						header += "<th>" + col + "</th>";
					}
					header += "</tr>";
					body = "";
					for (var i = 0; i < rows.length; i++) {
						body += "<tr>";
						for (var col in rows[i]) {
							body += "<td>" + rows[i][col] + "</td>";
						}
						body += "</tr>";
					}
					table =
						jQuery('<table class="wpda_query_builder_table" data-id="' + activeIndex + '"/>')
						.append(jQuery('<thead/>').append(header))
						.append(jQuery('<tbody/>').append(body));
					jQuery("#wpda_query_builder_menubar_" + activeIndex).show();
					jQuery("#wpda_query_builder_result_" + activeIndex).html(table);
					rowLabel = rows.length === 1 ? "row" : "rows";
					html = rows.length + " " + rowLabel;
					if (msg.status.queries !== null) {
						html += " (" + msg.status.queries[msg.status.num_queries - 1][1].toFixed(5) + " sec)";
					}
					jQuery("#wpda_query_builder_statusbar_" + activeIndex + " span.wpda_query_builder_statusbar_message").html(
						html
					);
					jQuery("#wpda_query_builder_statusbar_" + activeIndex).show();
					jQuery("#wpda_query_builder_json_" + activeIndex).jsonViewer(msg.status);
					jQuery("#wpda_query_builder_json_" + activeIndex + " ul li a.json-toggle").click();

					setResultDivHeight(activeIndex);
				} else {
					rowLabel = msg.status.rows_affected === 1 ? "row" : "rows";
					html = "Query OK, " + msg.status.rows_affected + " " + rowLabel + " affected";
					if (msg.status.queries !== null) {
						html += " (" + msg.status.queries[msg.status.num_queries - 1][1].toFixed(5) + " sec)"
					}
					jQuery("#wpda_query_builder_result_" + activeIndex).html(
						html
					);

					jQuery("#wpda_query_builder_statusbar_" + activeIndex).show();
					jQuery("#wpda_query_builder_json_" + activeIndex).jsonViewer(msg.status);
					jQuery("#wpda_query_builder_json_" + activeIndex + " ul li a.json-toggle").click();
				}
			} else {
				error = `<strong>WordPress database error:</strong> ${msg.status.last_error}<br/><br/><code>${msg.status.last_query}</code>`;
				jQuery("#wpda_query_builder_result_" + activeIndex).html(error);
			}
		}
	}
}

function setResultDivHeight(activeIndex) {
	viewHeight = jQuery(window).height();
	positionX = jQuery("#wpda_query_builder_result_" + activeIndex).offset().top;
	if (positionX===0) {
		positionX = viewHeight/2;
	}
	divHeight = viewHeight - positionX - 140;
	if (divHeight<400) {
		divHeight = 400;
	}
	jQuery("#wpda_query_builder_result_" + activeIndex + " table.wpda_query_builder_table tbody").height(divHeight);
}

function showError(activeIndex,msg) {
	jQuery("#wpda_query_builder_menubar_" + activeIndex).hide();
	jQuery("#wpda_query_builder_result_" + activeIndex).html(msg.responseText);
	jQuery("#wpda_query_builder_statusbar_" + activeIndex).hide();
}

function executeQuery(activeIndex) {
	// Update first if using Visual Query Builder
	if (!updateQuery(activeIndex)) {
		return;
	}

	// Execute query
	cm = editors['tab' + activeIndex].codemirror;
	cm.save();

	sql = jQuery("#wpda_query_builder_sql_" + activeIndex).val();
	limit = '';

	if (jQuery("#use_max_rows_" + activeIndex).is(":checked")) {
		limit = jQuery("#max_rows_" + activeIndex).val();
	}

	jQuery("#executing_query_" + activeIndex).show();
	if (isVisualQueryBuilderActive(activeIndex)) {
		// Activate tab ???
		jQuery("#visualOutputContainer" + activeIndex).tabs("option", "active", 1);
	}

	jQuery.ajax({
		method: 'POST',
		url: wpda_home_url + "?action=wpda_query_builder_execute_sql",
		data: {
			wpda_wpnonce: wpda_wpnonce,
			wpda_schemaname: jQuery("#wpda_query_builder_dbs_" + activeIndex).val(),
			wpda_sqlquery: sql,
			wpda_sqllimit: limit,
			wpda_protect: jQuery("#wpda_query_builder_wordpress_protect_" + activeIndex).is(":checked")
		}
	}).done(
		function (msg) {
			jQuery("#executing_query_" + activeIndex).hide();
			showData(activeIndex, msg);
		}
	).fail(
		function (msg) {
			jQuery("#executing_query_" + activeIndex).hide();
			showError(activeIndex, msg);
		}
	);
}

function saveQuery(activeIndex) {
	// Save query
	cm = editors['tab' + activeIndex].codemirror;
	cm.save();

	jQuery.ajax({
		method: 'POST',
		url: wpda_home_url + "?action=wpda_query_builder_save_sql",
		data: {
			wpda_wpnonce: wpda_wpnonce,
			wpda_schemaname: jQuery("#wpda_query_builder_dbs_" + activeIndex).val(),
			wpda_sqlqueryname: jQuery("#wpda_query_builder_label_value_" + activeIndex).html(),
			wpda_sqlqueryname_old: jQuery("#wpda_query_builder_label_value_" + activeIndex).data("dbs-name"),
			wpda_sqlquery: jQuery("#wpda_query_builder_sql_" + activeIndex).val()
		}
	}).done(
		function (msg) {
			jQuery("#wpda_query_builder_label_value_" + activeIndex)
				.attr("data-dbs-name", jQuery("#wpda_query_builder_label_value_" + activeIndex).text());
			isChanged[activeIndex] = false;
		}
	).fail(
		function (msg) {
			console.log(activeIndex, msg);
		}
	);
}

function openQuery() {
	activeDbsNames = [];
	jQuery(".wpda_query_builder_label_value").each(
		function() {
			activeDbsNames.push(jQuery(this).data("dbs-name"));
		}
	);

	jQuery.ajax({
		method: 'POST',
		url: wpda_home_url + "?action=wpda_query_builder_open_sql",
		data: {
			wpda_wpnonce: wpda_wpnonce,
			wpda_exclude: activeDbsNames.join(",")
		}
	}).done(
		function (msg) {
			jQuery("#wpda_query_builder_open_select").find("option").remove();
			if (!Array.isArray(msg.data)) {
				for (var queryName in msg.data) {
					jQuery("#wpda_query_builder_open_select")
					.append(
						jQuery("<option/>", {
							value: queryName,
							text: queryName
						})
						.attr("data-dbs", msg.data[queryName].schema_name)
						.attr("data-sql", msg.data[queryName].query)
					);
				}
				jQuery("#wpda_query_builder_open_select").attr("disabled", false);
				jQuery("#wpda_query_builder_open_open").attr("disabled", false);
				jQuery("#wpda_query_builder_open_openall").attr("disabled", false);
				jQuery("#wpda_query_builder_open_delete").attr("disabled", false);
			} else {
				jQuery("#wpda_query_builder_open_select")
				.append(
					jQuery("<option/>", {
						value: "",
						text: "Nothing found..."
					})
				);
				jQuery("#wpda_query_builder_open_select").attr("disabled", true);
				jQuery("#wpda_query_builder_open_open").attr("disabled", true);
				jQuery("#wpda_query_builder_open_openall").attr("disabled", true);
				jQuery("#wpda_query_builder_open_delete").attr("disabled", true);
			}
		}
	).fail(
		function (msg) {
			console.log("ERROR");
			console.log(msg);
		}
	);

	jQuery("#wpda_query_builder_open").show();
}

function closeQuery() {
	jQuery("#wpda_query_builder_open").hide();
}

function deleteQuery() {
	if ( confirm("Delete query? This action cannot be undone!") ) {
		wpda_sqlqueryname = jQuery("#wpda_query_builder_open_select").find(":selected").text();

		jQuery.ajax({
			method: 'POST',
			url: wpda_home_url + "?action=wpda_query_builder_delete_sql",
			data: {
				wpda_wpnonce: wpda_wpnonce,
				wpda_sqlqueryname: wpda_sqlqueryname
			}
		}).done(
			function (msg) {
				closeQuery();
			}
		).fail(
			function (msg) {
				console.log(msg);
			}
		);
	}
}

function exportTable(exportType, tabIndex) {
	switch (exportType) {
		case "CSV":
			downloadCSV(
				jQuery("#wpda_query_builder_result_" + tabIndex + " table").html(),
				jQuery("#wpda_query_builder_label_value_" + tabIndex).text() + ".csv"
			);
			break;
		case "JSON":
			downloadJSON(
				jQuery("#wpda_query_builder_result_" + tabIndex + " table").html(),
				jQuery("#wpda_query_builder_label_value_" + tabIndex).text() + ".json"
			);
			break;
		case "XML":
			downloadXML(
				jQuery("#wpda_query_builder_result_" + tabIndex + " table").html(),
				jQuery("#wpda_query_builder_label_value_" + tabIndex).text() + ".xml"
			);
	}
}

function downloadCSV(html, fileName) {
	csv = [];
	rows = jQuery(html).find("tr");
	for (i=0; i<rows.length; i++) {
		row = [];
		cols = jQuery(rows[i]).find("td, th");
		for (j=0; j<cols.length; j++) {
			row.push(cols[j].innerText);
		}
		csv.push(row);
	}
	downloadExport(fileName, "text/csv", encodeURIComponent(csv.join("\n")));
}

function createXML(html, fileName) {
	headerCols = [];
	header = jQuery(html).find("tr th");
	body = jQuery(html)[1];
	bodyRows = jQuery(body).find("tr");
	table = jQuery("<table/>");
	for (i=0; i<header.length; i++) {
		headerCols.push(header[i].innerText);
	}
	for (i=0; i<bodyRows.length; i++) {
		bodyCols = jQuery(bodyRows[i]).find("td");
		row = jQuery("<rows/>");
		for (j=0; j<bodyCols.length; j++) {
			row.append(jQuery("<" + headerCols[j] + "/>").text(bodyCols[j].innerText));
		}
		table.append(row);
	}
	xml = jQuery("<xml/>").append(table);
	return jQuery.parseXML(xml[0].outerHTML);
}

function downloadJSON(html, fileName) {
	xmlDoc = createXML(html, fileName);
	json = jQuery.xml2json(new XMLSerializer().serializeToString(xmlDoc.documentElement));
	downloadExport(fileName, "text/json", JSON.stringify(json));
}

function downloadXML(html, fileName) {
	xmlDoc = createXML(html, fileName);
	downloadExport(fileName, "text/xml", new XMLSerializer().serializeToString(xmlDoc.documentElement));
}

function downloadExport(fileName, mimeType, content) {
	download = jQuery("<a/>", {
		href: "data:" + mimeType + ";charset=utf-8," + content,
		download: fileName
	}).appendTo('body');
	download[0].click();
	download.remove();
}

function unsavedChanges() {
	hasEdited = false;
	for (var tabindex in isChanged) {
		if (isChanged[tabindex]===true) {
			hasEdited = true;
		}
	}
	return hasEdited;
}

jQuery(window).on('keydown', function(event) {
	if ((event.ctrlKey || event.metaKey) && String.fromCharCode(event.which).toLowerCase()==='s') {
		if (jQuery(event.target).hasClass('CodeMirror-code')) {
			saveQuery(jQuery(event.target).closest(".wpda_query_builder").data("id"))
			event.preventDefault();
		}
	}
});

jQuery(window).on('beforeunload', function() {
	if (unsavedChanges()) {
		return 'Your changes will not be saved! Are you sure you want to leave this page?';
	}
});

// Visual Query Builder Component

function addVisual(activeIndex) {
	visualContent = `
		<div id="visualContainer${activeIndex}" class="visualContainer">
			<div id="visualComponent${activeIndex}" class="visualComponent">
				<div id="visualTables${activeIndex}" class="visualTables ui-widget">
					<div class="visualTablesHeader ui-widget-header">
						<h4>TABLES & VIEWS</h4>
					</div>
					<div id="visualTableList${activeIndex}" class="visualTableList ui-widget-content"></div>
				</div>
				<div id="visualWorkspace${activeIndex}" class="visualWorkspace ui-widget">
					<div id="visualQuery${activeIndex}" class="visualQuery ui-widget-content" data-id="${activeIndex}">
						<svg id="visualSvg${activeIndex}" xmlns="http://www.w3.org/2000/svg" class="visualSvg"></svg>
					</div>
					<div id="visualSelection${activeIndex}" class="visualSelection ui-widget-content">
						<table id="visualSelection${activeIndex}table">
							<thead>
								<tr>
									<th></th>
									<th>Column</th>
									<th>Alias</th>
									<th>Filter</th>
									<th>Sort</th>
									<th>Group</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div id="visualOutputContainer${activeIndex}" class="visualOutputContainer">
			<ul>
				<li><a href="#visualOutputContainer${activeIndex}tab1">Query</a></li>
				<li><a href="#visualOutputContainer${activeIndex}tab2">Output</a></li>
				<div id="visualOutputContainer${activeIndex}tab1"></div>
				<div id="visualOutputContainer${activeIndex}tab2"></div>
			</ul>
		</div>
	`;

	jQuery("#wpda_query_builder_" + activeIndex + " .wpda_query_builder_taskbar").after(visualContent);

	jQuery("#visualQuery" + activeIndex).off();
	jQuery("#visualQuery" + activeIndex).on("resize", function(e) {
		e.stopPropagation();

		tabIndex = jQuery(this).data("id");
		resizeVisual(tabIndex);
	});

	jQuery("#visualTables" + activeIndex).resizable({
		minWidth: jQuery("#visualTables" + activeIndex).width(),
		handles: "e"
	});
	jQuery("#visualQuery" + activeIndex).resizable({
		maxHeight: jQuery("#visualQuery" + activeIndex).height(),
		handles: "s"
	});

	updateVisual(activeIndex);
	isVisual[activeIndex] = true;

	var sqlOutputContainerTab1 = jQuery("#visualOutputContainer" + activeIndex + "tab1");
	var sqlOutputContainerTab2 = jQuery("#visualOutputContainer" + activeIndex + "tab2");

	jQuery("#wpda_query_builder_sql_container_" + activeIndex).appendTo(sqlOutputContainerTab1);
	jQuery("#wpda_query_builder_tabs_" + activeIndex).appendTo(sqlOutputContainerTab2);
	jQuery("#wpda_query_builder_menubar_" + activeIndex).appendTo(sqlOutputContainerTab2);
	jQuery("#wpda_query_builder_result_" + activeIndex).appendTo(sqlOutputContainerTab2);
	jQuery("#wpda_query_builder_statusbar_" + activeIndex).appendTo(sqlOutputContainerTab2);
	jQuery("#wpda_query_builder_viewer_" + activeIndex).appendTo(sqlOutputContainerTab2);

	jQuery("#wpda_query_builder_result_" + activeIndex).html("Press the <strong>Execute</strong> button to view output");

	jQuery("#visualOutputContainer" + activeIndex).tabs({
		active: 1
	});

	jQuery("#visualOutputContainer" + activeIndex + " ul li:first-child a").on("click", function() {
		var cm = editors['tab' + activeIndex].codemirror;
		cm.refresh();

		return true;
	});
}

function resizeVisual(activeIndex) {
	workspace = jQuery("#visualWorkspace" + activeIndex);
	query = jQuery("#visualQuery" + activeIndex);
	selection = jQuery("#visualSelection" + activeIndex);
	selection.height(workspace.height() - query.height() - 19);
}

function updateVisual(activeIndex) {
	var tablesAndViews = jQuery("<ul class='visualTableUlist'/>");
	var schema = jQuery("#wpda_query_builder_dbs_" + activeIndex).val();
	var tables = dbHints[schema];
	for (var table in tables) {
		if (tables[table].length>0) {
			var li =
				jQuery("<li/>")
				.data("schema", schema)
				.data("table", table)
				.html("<span class='dashicons dashicons-menu'></span>" + table);
			li.appendTo(tablesAndViews); // Add table
		}
	}

	jQuery("#visualTableList" + activeIndex).empty().append(tablesAndViews);
	jQuery("#visualTableList" + activeIndex + " li").off();
	jQuery("#visualTableList" + activeIndex + " li").on("click", function() {
		var schema = jQuery(this).data("schema");
		var table = jQuery(this).data("table");

		addVisualTable(schema, table, activeIndex);
	});
}

function addVisualTable(schema, table, activeIndex) {
	getColumns(schema, table, activeIndex, addTableWidget);
}

function setColumnLink(activeIndex, e, elem, tableAlias) {
	e.stopPropagation();

	if (Object.keys(columnLink).length===0) {
		var parent = elem.closest("tr");

		columnLink = {
			id: parent.attr("id"),
			x: parent.offset().left,
			y: parent.offset().top,
			width: parent.width(),
			height: parent.height(),
			startElement: elem,
			tableAlias: tableAlias
		};

		jQuery(elem).find(".link_closed").hide();
		jQuery(elem).find(".link_open").show();
	} else {
		var parent = elem.closest("tr");
		var id = parent.attr("id");
		if (id===columnLink.id) {
			jQuery(columnLink.startElement).find(".link_closed").show();
			jQuery(columnLink.startElement).find(".link_open").hide();

			columnLink = {};

			return;
		}

		var container = jQuery("#visualQuery" + activeIndex);

		x1 = columnLink.x - container.offset().left;
		y1 = columnLink.y - container.offset().top + columnLink.height/2 - VISUAL_CORRECTION;

		x2 = parent.offset().left - container.offset().left;
		y2 = parent.offset().top - container.offset().top + parent.height()/2 - VISUAL_CORRECTION;

		if (x1<x2) {
			x1 += columnLink.width;
			x2 -= VISUAL_CORRECTION*2;
		} else {
			x1 -= VISUAL_CORRECTION*2;
			x2 += parent.width();
		}

		var line = document.createElementNS("http://www.w3.org/2000/svg", "line");
		var lineId = columnLink.id + "_" + id;
		line.setAttribute("id", lineId);
		line.setAttribute("class", tableAlias + " " + columnLink.tableAlias);
		line.setAttribute("data-from", columnLink.id);
		line.setAttribute("data-to", id);
		line.setAttribute("x1", x1);
		line.setAttribute("y1", y1);
		line.setAttribute("x2", x2);
		line.setAttribute("y2", y2);
		jQuery("#visualSvg" + activeIndex).append(line);

		var lineProperties = document.createElementNS("http://www.w3.org/2000/svg", "circle");
		var linePropertiesId = lineId + "_properties";
		lineProperties.setAttribute("id", linePropertiesId);
		lineProperties.setAttribute("class", tableAlias + " " + columnLink.tableAlias + " wpda-tooltip");
		lineProperties.setAttribute("title", "Edit join");
		lineProperties.setAttribute("cx", Math.abs(x2 + x1) / 2);
		lineProperties.setAttribute("cy", Math.abs(y2 + y1) / 2);
		lineProperties.setAttribute("r", 10);
		jQuery("#visualSvg" + activeIndex).append(lineProperties);
		jQuery("#" + linePropertiesId).data({
			lineId: lineId,
			joinFrom: true,
			joinTo: true
		});

		var tableAliasFrom = columnLink.tableAlias;

		jQuery("#" + lineId).off();
		jQuery("#" + lineId).on("click", function() {
			linkProperties(event, tableAliasFrom, tableAlias, activeIndex);
		});

		jQuery("#" + linePropertiesId).off();
		jQuery("#" + linePropertiesId).on("click", function() {
			linkProperties(event, tableAliasFrom, tableAlias, activeIndex);
		});

		jQuery(elem).find(".link_closed").hide();
		jQuery(elem).find(".link_open").show();

		setTimeout(function(startElement) {
			jQuery(elem).find(".link_closed").show();
			jQuery(elem).find(".link_open").hide();

			jQuery(startElement).find(".link_closed").show();
			jQuery(startElement).find(".link_open").hide();
		}, 2000, columnLink.startElement);

		columnLink = {};

		jQuery('.wpda_tooltip').tooltip();

		updateQuery(activeIndex);
	}
}

function linkProperties(e, tableFrom, tableTo, activeIndex) {
	var propertiesData = jQuery(e.currentTarget).data();
	var propertiesId = jQuery(e.currentTarget).attr("id");

	joinFrom = propertiesData.joinFrom ? "checked" : "";
	joinTo = propertiesData.joinTo ? "checked" : "";

	var popupHtml = `
		<table class="visualPopup">
			<tr class="from"><td><input id="popup_from_${activeIndex}" type="checkbox" ${joinFrom} /></td><td><label for="popup_from_${activeIndex}">Select all rows from ${tableFrom}</label></td></tr>
			<tr class="to"><td><input id="popup_to_${activeIndex}" type="checkbox" ${joinTo} /></td><td><label for="popup_to_${activeIndex}">Select all rows from ${tableTo}</label></td></tr>
			<tr class="delete"><td><span class="fas fa-trash"></span></td><td>Delete</td></tr>
			<tr class="quit"><td><span class="fas fa-times-circle"></span></td><td>Quit</td></tr>
		</table>
	`;

	var popup = jQuery(popupHtml).dialog({
		dialogClass: "wpda_visual_link_popup",
		position: {
			my: "left",
			at: "right",
			of: e
		},
		close: function(event, ui) {
			popup.dialog("destroy");
		},
		width: "auto",
		height: "auto",
		minHeight: 0
	});

	jQuery(".visualPopup tr").off();
	jQuery(".visualPopup tr").on("click", function(e) {
		// Set links
		if (jQuery(e.currentTarget).hasClass("from") || jQuery(e.currentTarget).hasClass("to")) {
			jQuery("#" + propertiesId).data({
				lineId: propertiesData.lineId,
				joinFrom: jQuery("#popup_from_" + activeIndex).is(":checked"),
				joinTo: jQuery("#popup_to_" + activeIndex).is(":checked")
			});
		}

		// Remove link
		if (jQuery(e.currentTarget).hasClass("delete")) {
			removeLink(propertiesData.lineId, propertiesId);
			popup.dialog("close");
		}

		// Quit popup
		if (jQuery(e.currentTarget).hasClass("quit")) {
			popup.dialog("close");
		}

		updateQuery(activeIndex);
	});
}

function removeLink(lineId, propertiesId) {
	jQuery("#" + lineId).remove();
	jQuery("#" + propertiesId).remove();

}

function redrawLinkPosition(elem, container) {
	var x = elem.offset().left - container.offset().left;
	var y = elem.offset().top - container.offset().top + elem.height()/2 - VISUAL_CORRECTION;

	if (elem.offset().top<elem.closest(".visualTable").offset().top) {
		y += elem.closest(".visualTable").offset().top - elem.offset().top;
	}
	if (elem.offset().top>elem.closest(".visualTable").offset().top+elem.closest(".visualTable").height()-VISUAL_CORRECTION*3) {
		y -= elem.offset().top - elem.closest(".visualTable").offset().top - elem.closest(".visualTable").height() + VISUAL_CORRECTION*3;
	}

	return {
		x: x,
		y: y
	};
}

function redrawLink(activeIndex, tableAlias) {
	jQuery("#visualSvg" + activeIndex + " line." + tableAlias).each(function(i, obj) {
		// Redraw line
		var id = jQuery(obj).attr("id");
		var dataFrom = jQuery(obj).data("from");
		var dataTo = jQuery(obj).data("to");

		var container = jQuery("#visualQuery" + activeIndex);

		var elemFrom = jQuery("#" + dataFrom);
		var posFrom = redrawLinkPosition(elemFrom, container);
		var x1 = posFrom.x;
		var y1 = posFrom.y;

		var elemTo = jQuery("#" + dataTo);
		var posTo = redrawLinkPosition(elemTo, container);
		var x2 = posTo.x;
		var y2 = posTo.y;

		if (x1<x2) {
			x1 += elemFrom.width();
			x2 -= VISUAL_CORRECTION*2;
		} else {
			x1 -= VISUAL_CORRECTION*2;
			x2 += elemTo.width();
		}

		jQuery("#" + id).attr("x1", x1)
			.attr("y1", y1)
			.attr("x2", x2)
			.attr("y2", y2);

		// Redraw property circle
		var cx = Math.abs(x2 + x1) / 2;
		var cy = Math.abs(y2 + y1) / 2;
		jQuery("#" + id + "_properties")
			.attr("cx", cx)
			.attr("cy", cy);
	});
}

function addTableWidget(schema, table, activeIndex, data) {
	var columnRows = "";
	for (var col in data.columns) {
		columnName = data.columns[col]["column_name"];
		columnType = data.columns[col]["column_type"];

		indexes = "";
		for (var index in data.indexes) {
			if (data.indexes[index]["column_name"]==columnName) {
				if (data.indexes[index]["non_unique"]==="0") {
					indexes = "<span class='dashicons dashicons-admin-network' title='Unique index'></span>";
				} else {
					if (data.indexes[index]["index_type"]==="FULLTEXT") {
						indexes = "<span class='dashicons dashicons-superhero' title='Fulltext index'></span>";
					} else {
						indexes = "<span class='dashicons dashicons-search' title='Non-unique index'></span>";
					}
				}
			}
		}

		var tableUsed  = jQuery(".visualTable." + table).length;
		var tableAlias = table;
		if (tableUsed>0) {
			tableAlias += tableUsed+1;
		}

		columnRows +=`
			<tr id="tab${activeIndex}_${tableAlias}_${columnName}"
				class="visualSelectedColumn" 
				data-schema="${schema}"
				data-table="${table}"
				data-column="${columnName}"
			>
				<td class="columnName"><input type="checkbox"/> <span>${columnName}</span></td>
				<td class="columnType">${columnType}</td>
				<td>${indexes}</td>
				<td onclick="setColumnLink('${activeIndex}', event, jQuery(this), '${tableAlias}')">
					<span class='link_closed far fa-circle wpda_tooltip' title="Click to add a join"></span>
					<span class='link_open fas fa-circle' style="display: none"></span>
				</td>
			</tr>
		`;
	}

	var title = table===tableAlias ? table : tableAlias + " (" + table + ")";
	var widget = `
		<div id="wpda-widget${activeIndex}_${tableAlias}" data-table="${table}" data-alias="${tableAlias}" class="wpda_visual_table_widget ui-widget">
			<div class="wpda_visual_widget_content">
				<div class="ui-widget-header">
					<span>${title}</span>
					<span class="icons">
						<i class='fas fa-window-close wpda-widget-close wpda_tooltip' title='Close'></i>
					</span>
				</div>
				<div class="ui-widget-content">
					<div id="tab${activeIndex}_${tableAlias}" class="visualTable ${table}">
						<table class="tableWidget">
							${columnRows}
						</table>
					</div>
				</div>
			</div>
		</div>
	`;

	jQuery("#visualQuery" + activeIndex).append(widget);
	jQuery("#tab" + activeIndex + "_" + tableAlias).off();
	jQuery("#wpda-widget" + activeIndex + "_" + tableAlias).draggable({
		drag: function( event, ui ) {
			redrawLink(activeIndex, tableAlias);
		}
	});
	jQuery("#tab" + activeIndex + "_" + tableAlias).on("scroll", function() {
		redrawLink(activeIndex, tableAlias);
	});

	jQuery("#wpda-widget" + activeIndex + "_" + tableAlias + " .ui-widget-content").resizable({
		handles: "e,s",
		resize: function( event, ui ) {
			redrawLink(activeIndex, tableAlias);
		}
	});

	var panel = jQuery("#wpda-widget" + activeIndex + "_" + tableAlias + " .ui-widget-content");
	if (panel.height()>200) {
		panel.height(200); // max initial height
		panel.width(panel.width()+20); // scroll bar
	}

	jQuery(".wpda-widget-close").off();
	jQuery(".wpda-widget-close").on("click", function(event) {
		var elem = jQuery(this).closest(".wpda_visual_table_widget");

		var id = elem.attr("id");
		var alias = elem.data("alias"); // click event handles multiple element: get correct alias

		// Remove related links
		jQuery("#visualSvg" + activeIndex + " line." + alias).remove();
		jQuery("#visualSvg" + activeIndex + " circle." + alias).remove();

		// Remove selected columns
		jQuery("#visualSelection" + activeIndex + "table .wpda_alias_" + alias).remove();

		// Remove table/view
		elem.remove();

		updateQuery(activeIndex);
	});


	jQuery("#tab" + activeIndex + "_" + tableAlias + " .visualSelectedColumn").off();
	jQuery("#tab" + activeIndex + "_" + tableAlias + " .visualSelectedColumn").on("click", function(e) {
		var checkbox = jQuery(this).find("input");

		if (e.target.type!=="checkbox") {
			// Update checkbox
			if (checkbox.prop("checked")) {
				checkbox.prop("checked", false);
			} else {
				checkbox.prop("checked", true);
			}
		}

		var column = jQuery(this).data("column");

		if (checkbox.prop("checked")) {
			checkbox.closest("tr").addClass("selectedRow");
			addColumnToSelection(activeIndex, tableAlias, column);
		} else {
			checkbox.closest("tr").removeClass("selectedRow");
			removeColumnFromSelection(activeIndex, tableAlias, column);
		}

		updateQuery(activeIndex);
	});

	jQuery('.wpda_tooltip').tooltip({
		tooltipClass: "wpda_tooltip_dashboard",
		position: { my: "right bottom", at: "right top" }
	});

	updateQuery(activeIndex);
}

function addColumnToSelection(activeIndex, tableAlias, column) {
	var columnAlias = initcap(column);
	var newColumn = `
		<tr id="sel_tab${activeIndex}_${tableAlias}_${column}" class="wpda_alias_${tableAlias}">
			<td><span class="dashicons dashicons-move"></span></td>
			<td class="column_definition">${tableAlias}.${column}</td>
			<td class="column_alias"><input type="text" value="${columnAlias}" name="columnAlias"/></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	`;

	jQuery("#visualSelection" + activeIndex + " tbody").append(newColumn);

	jQuery("#visualSelection" + activeIndex + "table").sortable({
		items: "tr",
		stop: function( event, ui ) {
			updateQuery(activeIndex);
		}
	});

	jQuery("#visualSelection" + activeIndex + "table input[name='columnAlias']").off();
	jQuery("#visualSelection" + activeIndex + "table input[name='columnAlias']").on("keyup", function() {
		updateQuery(activeIndex);
	});
}

function removeColumnFromSelection(activeIndex, tableAlias, column) {
	jQuery("#sel_tab" + activeIndex + "_" + tableAlias + "_" + column).remove();
}

function initcap(txt) {
	return txt.charAt(0).toUpperCase() + txt.slice(1);
}

function updateQuery(activeIndex) {
	if (!isVisualQueryBuilderActive(activeIndex)) {
		// Visual Query Builder not active
		return true;
	}

	var tables = jQuery("#visualQuery" + activeIndex + " .wpda_visual_table_widget");
	if (tables.length===0) {
		jQuery("#wpda_query_builder_result_" + activeIndex).html("No table selected");
		clearEditor(activeIndex)
		return false;
	}

	var selectedColumns = jQuery("#visualSelection" + activeIndex + "table tbody tr");
	var sqlQuery = "SELECT\t";

	for (var i=0; i<selectedColumns.length; i++) {
		var selectedColumn = jQuery(selectedColumns[i]);
		var columnName = selectedColumn.find(".column_definition").text();
		var columnAlias = selectedColumn.find(".column_alias input[type='text']").val();

		if (i>0) {
			sqlQuery += ",\t\t";
		}

		var columnNames = columnName.split(".");
		sqlQuery += "`" + columnNames[0] + "`.`" + columnNames[1] + "`";

		if (columnAlias.trim()!="") {
			sqlQuery += " as '" + columnAlias + "'";
		}

		sqlQuery += "\n";
	}

	if (selectedColumns.length===0) {
		sqlQuery += "*\n";
	}

	sqlQuery += "FROM\t";
	sqlQuery += addJoins(activeIndex, tables);

	var cm = editors["tab" + activeIndex].codemirror;
	cm.setValue(sqlQuery);
	cm.save();

	return true;
}

function addJoins(activeIndex, tables) {
	var sqlQuery = "";

	var tableAliasses = [];
	for (var i=0; i<tables.length; i++) {
		tableAliasses.push({
			tableName: jQuery(tables[i]).data("table"),
			tableAlias: jQuery(tables[i]).data("alias")
		});
	}

	for (var i=0; i<tableAliasses.length; i++) {
		var tableName = tableAliasses[i].tableName;
		var tableAlias = tableAliasses[i].tableAlias;

		if (i===0) {
			if (tableName == tableAlias) {
				sqlQuery += "`" + jQuery(tables[0]).data("table") + "`";
			} else {
				sqlQuery += "`" + jQuery(tables[0]).data("table") + "` " + jQuery(tables[0]).data("alias");
			}
		} else {
			var prevTableAlias = tableAliasses[i-1].tableAlias;
			var joins = jQuery("#visualQuery" + activeIndex + " line." + prevTableAlias + "." + tableAlias);

			var innerJoinTable = "";
			if (tableName===tableAlias) {
				innerJoinTable = "`" + tableName + "`";
			} else {
				innerJoinTable = "`" + tableName + "` " + tableAlias;
			}

			if (joins.length>0) {
				for (var j = 0; j < joins.length; j++) {
					var fromTable = jQuery(joins[j]).data("from");
					var fromTableColumn = jQuery("#" + fromTable + " td.columnName span").text();
					var fromTableName = jQuery("#" + fromTable).closest(".wpda_visual_table_widget").data("table");
					var fromTableAlias = jQuery("#" + fromTable).closest(".wpda_visual_table_widget").data("alias");

					var toTable = jQuery(joins[j]).data("to");
					var toTableColumn = jQuery("#" + toTable + " td.columnName span").text();
					var toTableName = jQuery("#" + toTable).closest(".wpda_visual_table_widget").data("table");
					var toTableAlias = jQuery("#" + toTable).closest(".wpda_visual_table_widget").data("alias");

					var preJoin = "";
					if (j === 0) {
						sqlQuery += "INNER JOIN " + innerJoinTable + " ON ";
					} else {
						sqlQuery += "\n\tAND ";
					}

					sqlQuery += preJoin + "`" + fromTableAlias + "`.`" + fromTableColumn + "` = `" + toTableAlias + "`.`" + toTableColumn + "`";
				}
			} else {
				sqlQuery += ",\t\t" + innerJoinTable;
			}
		}

		sqlQuery += "\n";
	}

	return sqlQuery;
}

function isVisualQueryBuilderActive(activeIndex) {
	return jQuery("#visualContainer" + activeIndex).length>0;
}

function clearEditor(activeIndex) {
	var cm = editors["tab" + activeIndex].codemirror;
	cm.setValue("");
	cm.save();
}

jQuery(window).on("resize", function(e) {
	for (var index in isVisual) {
		if (isVisual[index]) {
			resizeVisual(index);
		}
	}
});
