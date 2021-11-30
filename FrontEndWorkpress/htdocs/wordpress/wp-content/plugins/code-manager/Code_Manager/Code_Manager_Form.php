<?php

namespace Code_Manager {

	/**
	 * Class Code_Manager_Form
	 *
	 * Implements data entry form for Code Manager.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class Code_Manager_Form {

		/**
		 * Actual code manager record
		 *
		 * @var null|array
		 */
		protected $row = null;

		/**
		 * Allowed values: view (read-only mode) and edit (update mode)
		 *
		 * @var string
		 */
		protected $action = 'edit';

		/**
		 * Allowed values: null (no DML action needed) and save (perform insert or update)
		 *
		 * @var null|string
		 */
		protected $action2 = null;

		/**
		 * Code ID. Must be entered to view or edit. Allows null when action = new (insert).
		 *
		 * @var int|null
		 */
		protected $code_id = null;

		/**
		 * WP Nonce used for DML actions.
		 *
		 * @var string
		 */
		protected $wpnone;

		// Default values
		protected $default_code_name        = '';
		protected $default_code_type        = 'php shortcode';
		protected $default_code             = "<?php\n\n?>";
		protected $default_code_enabled     = '0';
		protected $default_code_preview     = false;
		protected $default_code_author      = '';
		protected $default_code_description = '';


		/**
		 * Code_Manager_Form constructor.
		 *
		 * Initializes data entry form and performs DML actions as requested by arguments.
		 *
		 * @since   1.0.0
		 */
		public function __construct() {
			$this->action  =
				isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'edit'; // input var okay.

			$this->action2  =
				isset( $_REQUEST['action2'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ) : null; // input var okay.

			$this->code_id =
				isset( $_REQUEST['code_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['code_id'] ) ) : null; // input var okay.

			switch( $this->action ) {
				case 'edit':
					if ( null === $this->code_id ) {
						wp_die( __( 'ERROR: Invalid arguments', 'code-manager' ) );
					}
					if ( 'save' === $this->action2 ) {
						$this->check_authorization(); // Dies if not authorized
						if (
							isset( $_REQUEST['code_id'] ) &&
							isset( $_REQUEST['code_name'] ) &&
							isset( $_REQUEST['code_type'] ) &&
							isset( $_REQUEST['code'] ) &&
							isset( $_REQUEST['code_author'] ) &&
							isset( $_REQUEST['code_description'] )
						) {
							// All data available, start update process
							$code_id          = sanitize_text_field( wp_unslash( $_REQUEST['code_id'] ) ); // input var okay.
							$code_name        = sanitize_text_field( wp_unslash( $_REQUEST['code_name'] ) ); // input var okay.
							$code_type        = sanitize_text_field( wp_unslash( $_REQUEST['code_type'] ) ); // input var okay.
							$code_enabled     = isset( $_REQUEST['code_enabled'] ) && 'on' === $_REQUEST['code_enabled'] ? '1' : '0';
							$code             = wp_unslash( $_REQUEST['code'] ); // input var okay.
							$code_author      = sanitize_text_field( wp_unslash( $_REQUEST['code_author'] ) ); // input var okay.
							$code_description = wp_unslash( $_REQUEST['code_description'] ); // input var okay.

							$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
							$code_manager_model       = new $code_manager_model_class();
							$numrows                  = $code_manager_model::dml_update( $code_id, $code_name, $code_type, $code, $code_author, $code_description, $code_enabled );

							$preview_enabled = Code_Manager_Preview::is_code_id_preview_enabled( $this->code_id );
							$preview_changed = false;
							if ( isset( $_REQUEST['code_preview'] ) && 'on' === $_REQUEST['code_preview'] ) {
								if ( ! $preview_enabled ) {
									Code_Manager_Preview::add_user_preview_code_id( $code_id );
									$msg = new Message_Box(
										[
											'message_text' => __( 'Preview enabled', 'code-manager' ),
										]
									);
									$msg->box();
									$preview_changed = true;
								}
							} else {
								if ( $preview_enabled ) {
									Code_Manager_Preview::remove_user_preview_code_id( $code_id );
									$msg = new Message_Box(
										[
											'message_text' => __( 'Preview disabled', 'code-manager' ),
										]
									);
									$msg->box();
									$preview_changed = true;
								}
							}

							if ( 0 === $numrows ) {
								if ( ! $preview_changed ) {
									$msg = new Message_Box(
										[
											'message_text' => __( 'Nothing to save', 'code-manager' ),
										]
									);
									$msg->box();
								}
							} elseif ( 1 === $numrows ) {
								$msg = new Message_Box(
									[
										'message_text' => __( 'Succesfully saved changes to database', 'code-manager' ),
									]
								);
								$msg->box();
							}
						} else {
							// No update possible, missing data
							$msg = new Message_Box(
								[
									'message_text'           => __( 'Update failed', 'code-manager' ),
									'message_type'           => 'error',
									'message_is_dismissible' => false,
								]
							);
							$msg->box();
						}
					}
					// Requery
					$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
					$code_manager_model       = new $code_manager_model_class();
					$this->row                = $code_manager_model::dml_query( $this->code_id );
					break;
				case 'new':
					if ( 'save' === $this->action2 ) {
						$this->check_authorization(); // Dies if not authorized
						if (
							isset( $_REQUEST['code_name'] ) &&
							isset( $_REQUEST['code_type'] ) &&
							isset( $_REQUEST['code'] ) &&
							isset( $_REQUEST['code_author'] ) &&
							isset( $_REQUEST['code_description'] )
						) {
							// All data available, start insert process
							$code_name        = sanitize_text_field( wp_unslash( $_REQUEST['code_name'] ) ); // input var okay.
							$code_type        = sanitize_text_field( wp_unslash( $_REQUEST['code_type'] ) ); // input var okay.
							$code_enabled     = isset( $_REQUEST['code_enabled'] ) && 'on' === $_REQUEST['code_enabled'] ? '1' : '0';
							$code             = wp_unslash( $_REQUEST['code'] ); // input var okay.
							$code_author      = sanitize_text_field( wp_unslash( $_REQUEST['code_author'] ) ); // input var okay.
							$code_description = wp_unslash( $_REQUEST['code_description'] ); // input var okay.

							$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
							$code_manager_model       = new $code_manager_model_class();
							$code_id                  = $code_manager_model::dml_insert( $code_name, $code_type, $code, $code_author, $code_description, $code_enabled );
							if ( -1 === $code_id ) {
								$msg = new Message_Box(
									[
										'message_text'           => __( 'Insert failed', 'code-manager' ),
										'message_type'           => 'error',
										'message_is_dismissible' => false,
									]
								);
								$msg->box();

								$this->default_code_name		= $code_name;
								$this->default_code_type		= $code_type;
								$this->default_code				= $code;
								$this->default_code_enabled		= $code_enabled;
								$this->default_code_preview		= isset( $_REQUEST['code_preview'] ) && 'on' === $_REQUEST['code_preview'];
								$this->default_code_author		= $code_author;
								$this->default_code_description	= $code_description;
							} else {
								$msg = new Message_Box(
									[
										'message_text' => __( 'Succesfully saved changes to database', 'code-manager' ),
									]
								);
								$msg->box();

								$this->code_id            = $code_id;
								$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
								$code_manager_model       = new $code_manager_model_class();
								$this->row                = $code_manager_model::dml_query( $this->code_id );
								$this->action             = 'edit';

								if ( isset( $_REQUEST['code_preview'] ) && 'on' === $_REQUEST['code_preview'] ) {
									Code_Manager_Preview::add_user_preview_code_id( $code_id );
									$msg = new Message_Box(
										[
											'message_text' => __( 'Preview enabled', 'code-manager' ),
										]
									);
									$msg->box();
								}
							}
						} else {
							// No insert possible, missing data
							$msg = new Message_Box(
								[
									'message_text'           => __( 'Insert failed', 'code-manager' ),
									'message_type'           => 'error',
									'message_is_dismissible' => false,
								]
							);
							$msg->box();
						}
					}
			}

			$this->wpnonce = wp_create_nonce( 'code-manager-' . Code_manager::get_current_user_login() );
		}

		/**
		 * Changes are only allow with proper authorization
		 *
		 * @since   1.0.0
		 */
		private function check_authorization() {
			$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, 'code_manager_editor' . Code_manager::get_current_user_login() ) ) {
				wp_die( __( 'ERROR: Not authorized', 'code-manager' ) );
			}
		}

		/**
		 * Build data entry form. Generates HTML only. JS actions are added from JS script file.
		 *
		 * @since   1.0.0
		 */
		public function show() {
			if ( null !== $this->row ) {
				$code_name        = $this->row[0]['code_name'];
				$code_type        = $this->row[0]['code_type'];
				$code_enabled     = $this->row[0]['code_enabled'];
				$code_preview     = Code_Manager_Preview::is_code_id_preview_enabled( $this->code_id );
				$code             = $this->row[0]['code'];
				$code_author      = $this->row[0]['code_author'];
				$code_description = $this->row[0]['code_description'];
			} else {
				$code_name        = $this->default_code_name;
				$code_type        = $this->default_code_type;
				$code             = $this->default_code;
				$code_enabled     = $this->default_code_enabled;
				$code_preview     = $this->default_code_preview;
				$code_author      = $this->default_code_author;
				$code_description = $this->default_code_description;
			}
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline">
					<span>
						<span class="cm_page_title">
							<?php echo CODE_MANAGER_H1_TITLE; ?>
						</span>
						<?php
						if ( ! Code_Manager_Dashboard::dashboard_enabled() ) {
							?>
							<a href="?page=<?php echo CODE_MANAGER_MENU_SLUG; ?>"
							   title="Back to list">
								<span class="material-icons cm_menu_title">menu</span></a>
							<a href="<?php echo CODE_MANAGER_HELP_URL; ?>" target="_blank"
							   title="Plugin help - opens in a new tab or window">
								<span class="material-icons cm_menu_title">help_outline</span></a>
							<?php
						}
						?>
					</span>
				</h1>
				<p></p>
				<div>
					<form method="post" enctype="multipart/form-data"
						  action="?page=<?php echo CODE_MANAGER_MENU_SLUG; ?>">
						<fieldset class="cm_fieldset">
							<table class="cm_simple_table" cellspacing="0" cellpadding="0">
								<tbody>
									<tr>
										<td class="label">
											<label for="code_id" title="Code ID must be entered">
												* Code ID
											</label>
										</td>
										<td class="data">
											<input name="code_id" id="code_id" type="text"
												   value="<?php echo esc_attr( $this->code_id ); ?>" readonly="">
										</td>
										<td class="icon">
											<span class="cm_data_type">123</span>
										</td>
									</tr>
									<tr>
										<td class="label">
											<label for="code_name" title="Name must be entered">
												* Name
											</label>
										</td>
										<td class="data">
											<input name="code_name" id="code_name" type="text" maxlength="100"
												   value="<?php echo esc_attr( $code_name ); ?>">
										</td>
										<td class="icon">
											<span class="cm_data_type">abc</span></td>
									</tr>
									<tr>
										<td class="label">
											<label for="code_type" title="Type must be entered">
												Type
											</label>
										</td>
										<td class="data">
											<select name="code_type" id="code_type">
												<?php
												$code_manager_tab_class = CODE_MANAGER_TAB_CLASS;
												$code_manager_tab       = new $code_manager_tab_class();
												$code_types             = $code_manager_tab->get_code_types();
												foreach ( $code_types as $code_type_group => $value ) {
													echo "<optgroup label='{$code_type_group}'>";
													foreach ( $value as $value_code_type => $value_code_label ) {
														echo "<option value='{$value_code_type}'>{$value_code_label}</option>";
													}
													echo '</optgroup>';
												}
												?>
											</select>
											<script type="text/javascript">
												jQuery('#code_type').val('<?php echo esc_attr( $code_type ); ?>');
											</script>
										</td>
										<td class="icon">
										</td>
									</tr>
									<tr>
										<td class="label">
											<label for="code_enabled">
												Status
											</label>
										</td>
										<td class="data" style="height: 30px">
											<label>
												<input type='checkbox'
													   name='code_enabled'
													   <?php echo '1'===$code_enabled ? 'checked' : ''; ?>
												>
												Enable code
											</label>
											&nbsp;
											<label>
												<input type='checkbox'
													   name='code_preview'
													<?php echo $code_preview ? 'checked' : ''; ?>
												>
												Enable preview mode
											</label>
										</td>
									</tr>
									<tr>
										<td class="label" style="vertical-align:top;padding-top:7px;">
											<label for="code" title="Code must be entered">
												Code
											</label>
										</td>
										<td class="data" style="display: grid; width: 100%;">
											<textarea name="code" id="code" style="vertical-align: top; display: none;"
													  maxlength="65535"><?php echo str_replace( "&", "&amp;", $code ); ?></textarea>
										</td>
										<td class="icon" style="vertical-align:top;padding-top:7px;">
											<span class="dashicons dashicons-editor-code"></span>
										</td>
									</tr>
									<tr>
										<td class="label">
											<label for="code_author" title="Optional">
												Author
											</label>
										</td>
										<td class="data">
											<input name="code_author" id="code_author" type="text" maxlength="100"
												   value="<?php echo esc_attr( $code_author ); ?>">
										</td>
										<td class="icon">
											<span class="cm_data_type">abc</span></td>
									</tr>
									<tr>
										<td class="label" style="vertical-align:top;padding-top:7px;">
											<label for="code_description" title="Optional">
												Description
											</label>
										</td>
										<td class="data">
											<textarea name="code_description" id="code_description" maxlength="65536"
											><?php echo esc_attr( $code_description ); ?></textarea>
										</td>
										<td></td>
									</tr>
								</tbody>
							</table>
						</fieldset>
						<p></p>
						<div>
							<input name="action" type="hidden" value="<?php echo $this->action; ?>">
							<input name="action2" type="hidden" value="save">
							<?php wp_nonce_field( 'code_manager_editor' . Code_manager::get_current_user_login(), '_wpnonce', false ); ?>
							<input type="submit" id="submit_button" value="Save changes to database"
								   class="button button-primary" name="submit_button" onclick="return submit_form();">
							<input type="button" onclick="javascript:location.href='?page=<?php echo CODE_MANAGER_MENU_SLUG; ?>'"
								   class="button button-secondary" value="Back to list">
						</div>
					</form>
				</div>
			</div>
			<script type="text/javascript">
				var wpnonce = '<?php echo $this->wpnonce; ?>';

				function submit_form() {
					if (jQuery('#code_name').val()==='') {
						alert('Name must be entered');
						return false;
					}
					user_has_edited = false;
					return true;
				}
			</script>
			<?php
		}

	}

}