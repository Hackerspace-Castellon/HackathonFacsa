<?php

namespace WPDataAccess\Settings {

	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\WPDA;

	class WPDA_Settings_Dashboard extends WPDA_Settings {

		const DASHBOARD_ROLES = 'wpda_dashboard_roles';
		const DASHBOARD_USERS = 'wpda_dashboard_users';

		protected function add_content() {
			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-dashboard-settings-' . WPDA::get_current_user_login() ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {
					if ( isset( $_REQUEST['dashboard_roles'] ) ) {
						$dashboard_roles_request = isset( $_REQUEST['dashboard_roles'] ) ? $_REQUEST['dashboard_roles'] : null;
						if ( is_array( $dashboard_roles_request ) ) {
							$dashboard_roles = sanitize_text_field( wp_unslash( implode( ',', $dashboard_roles_request ) ) );
						} else {
							$dashboard_roles = '';
						}
					} else {
						$dashboard_roles = '';
					}
					update_option( self::DASHBOARD_ROLES, $dashboard_roles );

					if ( isset( $_REQUEST['dashboard_users'] ) ) {
						$dashboard_users_request = isset( $_REQUEST['dashboard_users'] ) ? $_REQUEST['dashboard_users'] : null;
						if ( is_array( $dashboard_users_request ) ) {
							$dashboard_users = sanitize_text_field( wp_unslash( implode( ',', $dashboard_users_request ) ) );
						} else {
							$dashboard_users = '';
						}
					} else {
						$dashboard_users = '';
					}
					update_option( self::DASHBOARD_USERS, $dashboard_users );
				} elseif ( 'setdefaults' === $action ) {
					delete_option( self::DASHBOARD_ROLES );
					delete_option( self::DASHBOARD_USERS );
				}

				$msg = new WPDA_Message_Box(
					[
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					]
				);
				$msg->box();
			}

			global $wp_roles;
			$roles = $wp_roles->roles;
			unset( $roles[ 'administrator' ] );

			$users = get_users();

			$granted_roles = get_option( self::DASHBOARD_ROLES );
			$granted_users = get_option( self::DASHBOARD_USERS );
			?>
			<form id="wpda_settings_dashboard" method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=dashboard">
				<table class="wpda-table-settings">
					<tr>
						<th><?php echo __( 'Dashboard role access', 'wp-data-access' ); ?></th>
						<td>
							<div style="margin-bottom:10px">
								Select roles to grant dashboard access:
							</div>
							<select name="dashboard_roles[]" multiple size="<?php echo esc_attr( min( 5, sizeof( $roles ) ) ); ?>">
								<?php
								foreach ( $roles as $key => $role ) {
									$selected = false !== strpos( $granted_roles, $key ) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php echo $selected; ?>>
										<?php echo esc_attr( $role['name'] ); ?>
									</option>
									<?php
								}
								?>
							</select>
							<ul style="list-style-type:disc;margin-left:20px">
								<li style="margin-bottom:0">Administrators have access by default</li>
								<li>Hold the control key to select multiple</li>
							</ul>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Dashboard user access', 'wp-data-access' ); ?></th>
						<td>
							<div style="margin-bottom:10px">
								Select users to grant dashboard access:
							</div>
							<select name="dashboard_users[]" multiple size="<?php echo esc_attr( min( 5, sizeof( $users ) ) ); ?>">
								<?php
								foreach ( $users as $user ) {
									$selected = false !== strpos( $granted_users, $user->data->user_login ) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr( $user->data->user_login ); ?>" <?php echo $selected; ?>>
										<?php echo esc_attr( $user->data->display_name ); ?>
									</option>
									<?php
								}
								?>
							</select>
							<ul style="list-style-type:disc;margin-left:20px">
								<li>No role access needed to grant access to specific users</li>
								<li>Hold the control key to select multiple</li>
							</ul>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php echo __( 'Save Dashboard Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
						   jQuery('input[name=&quot;action&quot;]').val('setdefaults');
						   jQuery('#wpda_settings_dashboard').trigger('submit')
						   }"
					   class="button">
						<?php echo __( 'Reset Dashboard Settings To Defaults', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-dashboard-settings-' . WPDA::get_current_user_login(), '_wpnonce', false ); ?>
			</form>
			<?php
		}

	}

}