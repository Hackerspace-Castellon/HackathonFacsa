<?php

namespace Code_Manager {

	/**
	 * Class Code_Manager_Tabs
	 *
	 * Builds the Code Manager tab mode IDE
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class Code_Manager_Tabs {

		protected $wpnonce;
		protected $wpnonce_get_code;

		/**
		 * Code_Manager_Tabs constructor
		 *
		 * Creates a number of wpnonces needed to authorize ajax calls
		 *
		 * @since   1.0.0
		 */
		public function __construct() {
			$this->wpnonce          = wp_create_nonce( 'code-manager-' . Code_manager::get_current_user_login() );
			$this->wpnonce_get_code = wp_create_nonce( 'code-manager-get-code' . Code_manager::get_current_user_login() );
		}

		/**
		 * Shows the tab mode IDE
		 *
		 * JavaScript depending on server variables not available in the browser are added here. Generic JavaScript
		 * code is added as a script file.
		 *
		 * @since   1.0.0
		 */
		public function show() {
			?>
			<script type="text/javascript">
				var wpnonce = '<?php echo $this->wpnonce; ?>';
				var wpnonce_get_code = '<?php echo $this->wpnonce_get_code; ?>';
				var code_manager_code_groups = [];
				<?php
				$code_manager_tab_class = CODE_MANAGER_TAB_CLASS;
				$code_manager_tab       = new $code_manager_tab_class();
				$code_types             = $code_manager_tab->get_code_types();
				foreach ( $code_types as $code_type_group => $value ) {
					echo 'code_manager_code_group = [];';
					foreach ( $value as $code_type => $code_type_label ) {
						echo "code_manager_code_group['{$code_type}'] = '{$code_type_label}';";
					}
					echo "code_manager_code_groups['{$code_type_group}'] = code_manager_code_group;";
				}
				?>
				function filter_code_list() {
					jQuery("#code_manager_code_list > option").each(function() {
						showCode = true;
						if (jQuery("#code_manager_filter_code_type").val()!=="") {
							if (jQuery("#code_manager_filter_code_type").val()!==jQuery(this).data("type")) {
								jQuery(this).hide();
								showCode = false;
							}
						}
						if (showCode && jQuery("#code_manager_filter_code_name").val()!=="") {
							if (!jQuery(this).data("name").toLowerCase().includes(jQuery("#code_manager_filter_code_name").val().toLowerCase())) {
								jQuery(this).hide();
								showCode = false;
							}
						}
						if (showCode) {
							jQuery(this).show();
						}
					});
				}
			</script>
			<div class="wrap">
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr>
						<td>
							<h1 class="wp-heading-inline">
								<span>
									<span class="cm_page_title">
										<?php echo CODE_MANAGER_H1_TITLE . ' - tab mode'; ?>
									</span>
									<?php
									if ( ! Code_Manager_Dashboard::dashboard_enabled() ) {
									?>
										<a href="?page=<?php echo CODE_MANAGER_MENU_SLUG; ?>"
										   title="Switch to list mode (standard WordPress list view)">
											<span class="material-icons cm_menu_title">menu</span></a>
										<a id="code_manager_new" href="javascript:void(0)">
											<span class="material-icons cm_menu_title"
												  title="Add new code">add_circle_outline</span></a>
										<a id="code_manager_open" href="javascript:void(0)">
											<span class="material-icons cm_menu_title"
												  title="Open existing code">arrow_circle_down</span></a>
										<a href="<?php echo CODE_MANAGER_HELP_URL; ?>" target="_blank"
										   title="Plugin help - opens in a new tab or window">
											<span class="material-icons cm_menu_title">help_outline</span></a>
									<?php
									}
									?>
								</span>
							</h1>
						</td>
						<td style="text-align:right;padding-right:10px;padding-top:4px">
							<a id="disable_preview"
							   href="javascript:void(0)"
							   class="material-icons cm_menu_title"
							   style="text-decoration:none"
							   title="Turn of preview mode for all code IDs"
							>visibility_off</a>
						</td>
					</tr>
				</table>
				<style>
					#code_manager_code_list {
                        background-image: none;
                        min-width: 100%;
                        width: 100%;
                        max-width: 100%;
						font-family: monospace;
                        font-size: 90%;
					}
                    #code_manager_code_list option {
                        width: 100%;
                    }
				</style>
				<div id="code_manager_open_frame" style="display: none; background-color: transparent;">
					<p style="margin-top:0">Hold ctrl key to select multiple code blocks, double click to select single code block</p>
					<div style="margin-right:-2px">
						<span>
							<input type="text" placeholder="Filter code name" id="code_manager_filter_code_name" onkeyup="filter_code_list()"/>
						</span>
						<span style="float: right">
							<select id="code_manager_filter_code_type" onchange="filter_code_list()"></select>
						</span>
					</div>
					<div>
						<select id="code_manager_code_list" size="14" multiple="multiple">
							<option>Loading data...</option>
						</select>
					</div>
				</div>
				<div id="code_manager_taskbar_tabmode" class="nav-tab-wrapper"></div>
				<div id="code_manager_workspace_tabmode"></div>
			</div>
			<?php
		}

		/**
		 * Get code types
		 *
		 * Code types are organized in group to allow grouping in list boxes.
		 *
		 * @since   1.0.0
		 *
		 * @return string[][]
		 */
		public function get_code_types() {
			return [
				'Shortcodes'     => [
					'php shortcode'        => 'PHP Shortcode',
					'javascript shortcode' => 'JavaScript Shortcode',
					'css shortcode'        => 'CSS Shortcode',
					'html shortcode'       => 'HTML Shortcode'
				],
			];
		}

	}

}