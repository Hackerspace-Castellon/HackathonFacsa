function showMenu() {
	if (jQuery("#wpcontent").width()<690) {
		jQuery("#cm-dashboard").hide();
		jQuery("#cm-dashboard-mobile").fadeIn(400);
	} else {
		if (jQuery("#wpcontent").width()<800) {
			wd = 46;
			fs = 24;
			tx = 7;
		} else if (jQuery("#wpcontent").width()<860) {
			wd = 54;
			fs = 30;
			tx = 8;
		} else {
			wd = 62;
			fs = 32;
			tx = 9;
		}

		jQuery("#cm-dashboard .cm-dashboard .cm-dashboard-group-code").css("width",
			(wd*jQuery("#cm-dashboard .cm-dashboard .cm-dashboard-group-code .cm-dashboard-item").length) + "px");

		jQuery("#cm-dashboard .cm-dashboard .cm-dashboard-group-settings").css("width",
			(wd*jQuery("#cm-dashboard .cm-dashboard .cm-dashboard-group-settings .cm-dashboard-item").length) + "px");

		jQuery("#cm-dashboard .cm-dashboard .cm-dashboard-group-support").css("width",
			(wd*jQuery("#cm-dashboard .cm-dashboard .cm-dashboard-group-support .cm-dashboard-item").length) + "px");

		jQuery("#cm-dashboard .cm-dashboard .cm-dashboard-group .cm-dashboard-item").css("width", wd + "px");
		jQuery("#cm-dashboard .cm-dashboard .cm-dashboard-group .cm-dashboard-item .label").css("font-size", tx + "px");
		jQuery("#cm-dashboard .cm-dashboard .cm-dashboard-group .cm-dashboard-item .material-icons").css("font-size", fs + "px");

		jQuery("#cm-dashboard-mobile").hide();
		jQuery("#cm-dashboard").fadeIn(400);

		jQuery(".cm_tooltip").tooltip({
			tooltipClass: "cm_tooltip_css",
		});
		jQuery(".cm_tooltip_icons").tooltip({
			tooltipClass: "cm_tooltip_icons_css",
			position: {
				my: "center bottom-18",
				at: "center top",
				using: function (position, feedback) {
					jQuery(this).css(position);
					jQuery("<div>")
					.addClass("arrow")
					.addClass(feedback.vertical)
					.addClass(feedback.horizontal)
					.appendTo(this);
				}
			}
		});
	}
}

function toggleMenu() {
	if (jQuery("#cm-dashboard-mobile ul").is(":visible")) {
		jQuery("#cm-dashboard-mobile ul").hide();
	} else {
		jQuery("#cm-dashboard-mobile ul").show();
	}
}

function toggleDashboard() {
	if (jQuery("#screen-meta").css("display")==="block") {
		jQuery("#cm-dashboard").hide();
		jQuery("#cm-dashboard-mobile").hide();
	} else {
		showMenu();
	}
}

jQuery(function() {
	jQuery("#show-settings-link").on("click", function() {
		setTimeout(function() { toggleDashboard(); }, 500);
	});

	jQuery(window).on("resize", function() { showMenu() });
	showMenu();
});