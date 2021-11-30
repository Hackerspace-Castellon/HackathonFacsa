<?php

namespace Code_Manager {

	/**
	 * Class Code_Manager_Settings
	 *
	 * Handles plugin settings page
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class Code_Manager_Settings {

		/**
		 * Builds the settings page and stores the settings in WP options
		 *
		 * @since   1.0.0
		 */
		public function show() {
			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-settings' . Code_manager::get_current_user_login() ) ) {
					wp_die( __( 'ERROR: Not authorized', 'code-manager' ) );
				}

				if ( 'save' === $action ) {
					// Save options
					update_option(
						'code_manager_plugin_navigation',
						isset( $_REQUEST['plugin_navigation'] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST['plugin_navigation'] ) ) : 'dashboard'
					); // input var okay.

					update_option(
						'code_manager_plugin_hide_foreign_notices',
						isset( $_REQUEST['plugin_hide_foreign_notices'] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST['plugin_hide_foreign_notices'] ) ) : 'off'
					); // input var okay.

					update_option(
						'code_manager_plugin_code_execution',
						isset( $_REQUEST['plugin_code_execution'] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST['plugin_code_execution'] ) ) : 'off'
					); // input var okay.

					update_option(
						'code_manager_plugin_tables',
						isset( $_REQUEST['plugin_tables'] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST['plugin_tables'] ) ) : 'off'
					); // input var okay.

					update_option(
						'code_manager_plugin_options',
						isset( $_REQUEST['plugin_options'] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST['plugin_options'] ) ) : 'off'
					); // input var okay.
				} elseif ( 'setdefaults' === $action ) {
					// Set all plugin settings back to their defaults
					delete_option('code_manager_plugin_navigation');
					delete_option('code_manager_plugin_hide_foreign_notices');
					delete_option('code_manager_plugin_code_execution');
					delete_option('code_manager_plugin_tables');
					delete_option('code_manager_plugin_options');
				}

				$msg = new Message_Box(
					[
						'message_text' => __( 'Settings saved', 'code-manager' ),
					]
				);
				$msg->box();
			}

			// Get options
			$plugin_navigation = get_option('code_manager_plugin_navigation');
			if ( false === $plugin_navigation ) {
				$plugin_navigation = 'dashboard';
			}

			$plugin_hide_foreign_notices = get_option('code_manager_plugin_hide_foreign_notices');
			if ( false === $plugin_hide_foreign_notices ) {
				$plugin_hide_foreign_notices = 'on';
			}

			$plugin_code_execution = get_option('code_manager_plugin_code_execution');
			if ( false === $plugin_code_execution ) {
				$plugin_code_execution = 'on';
			}

			$plugin_tables = get_option('code_manager_plugin_tables');
			if ( false === $plugin_tables ) {
				$plugin_tables = 'off';
			}

			$plugin_options = get_option('code_manager_plugin_options');
			if ( false === $plugin_options ) {
				$plugin_options = 'on';
			}
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline">
					<span>
						<span>
							<?php echo CODE_MANAGER_SETTINGS_H1_TITLE; ?>
						</span>
						<?php
						if ( ! Code_Manager_Dashboard::dashboard_enabled() ) {
						?>
							<a href="<?php echo CODE_MANAGER_HELP_URL; ?>" target="_blank"
							   title="Plugin help - opens in a new tab or window">
								<span class="material-icons cm_menu_title">help_outline</span></a>
						<?php
						}
						?>
					</span>
				</h1>
				<br/><br/>
				<form id="code_manager_settings" method="post" action="?page=<?php echo CODE_MANAGER_SETTINGS_MENU_SLUG; ?>">
					<table class="code-manager-table-settings">

						<tr>
							<th><?php echo __( 'Plugin navigation', 'wp-data-access' ); ?></th>
							<td>
								<select name="plugin_navigation">
									<option value="both" <?php echo 'both' === $plugin_navigation ? 'selected' : ''; ?>>Show submenus and dashboard</option>
									<option value="dashboard" <?php echo 'dashboard' === $plugin_navigation ? 'selected' : ''; ?>>Show dashboard only (hide submenus)</option>
									<option value="menu" <?php echo 'menu' === $plugin_navigation ? 'selected' : ''; ?>>Show submenus only (hide dashboard)</option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php echo __( 'Notices', 'wp-data-access' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="plugin_hide_foreign_notices" <?php echo $plugin_hide_foreign_notices==='on' ? 'checked' : ''; ?> />
									<?php echo __( 'Hide notices of other themes and plugins on Code Manager admin pages', 'wp-data-access' ); ?>
								</label>
							</td>
						</tr>

						<tr>
							<th>
								<?php echo __( 'Code execution' ); ?>
							</th>
							<td>
								<label for="plugin_code_execution">
									<input type="checkbox"
										   id="plugin_code_execution"
										   name="plugin_code_execution"
										<?php echo 'on' === $plugin_code_execution ? 'checked' : ''; ?>
									/>
									<?php echo __( 'Enabled (uncheck to disable)' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th>
								<?php echo __( 'On plugin uninstall' ); ?>
							</th>
							<td>
								<label for="plugin_tables">
									<input type="checkbox"
										   id="plugin_tables"
										   name="plugin_tables"
										   <?php echo 'on' === $plugin_tables ? 'checked' : ''; ?>
									/>
									<?php echo __( 'Remove plugin tables (this will delete all your code)' ); ?>
								</label>
								<br/>
								<label for="plugin_options">
									<input type="checkbox"
										   id="plugin_options"
										   name="plugin_options"
										   <?php echo 'on' === $plugin_options ? 'checked' : ''; ?>
									/>
									<?php echo __( 'Remove plugin options' ); ?>
								</label>
							</td>
						</tr>
					</table>
					<div class="code-manager-table-settings-button">
						<input type="hidden" name="action" value="save"/>
						<input type="submit"
							   value="<?php echo __( 'Save Code Manager settings', 'code-manager' ); ?>"
							   class="button button-primary"/>
						<a href="javascript:void(0)"
						   onclick="return reset_defaults()"
						   class="button">
							<?php echo __( 'Reset Code Manager settings to defaults', 'code-manager' ); ?>
						</a>
					</div>
					<?php wp_nonce_field( 'code-manager-settings' . Code_manager::get_current_user_login(), '_wpnonce', false ); ?>
				</form>
			</div>
			<script type="text/javascript">
				jQuery(function() {
					jQuery( '.cm_menu_title' ).tooltip();
				});
				function reset_defaults() {
					html = '<div>Reset to defaults?</div>';
					var dialog = jQuery(html).dialog({
						dialogClass: "no-close",
						buttons: {
							'Yes': function() {
								dialog.dialog('destroy');
								jQuery('input[name="action"]').val('setdefaults');
								jQuery('#code_manager_settings').trigger('submit');
							},
							'No':  function() {
								dialog.dialog('destroy');
							},
							'Cancel':  function() {
								dialog.dialog('destroy');
							}
						}
					});
					jQuery(".ui-dialog-titlebar").hide();
					return false;
				}
			</script>
			<?php
		}

	}

}