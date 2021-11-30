<?php

namespace Code_Manager {

	/**
	 * Class Code_Manager
	 *
	 * Implements list mode view for Code Manager. Extends WordPress class WP_List_Table.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class Code_Manager_List extends WP_List_Table {

		/**
		 * Row number in the list
		 *
		 * @var int
		 */
		protected static $list_number = 0;

		protected $code_types = [];

		/**
		 * WP nonce to (de)activate preview mode for a specific code id
		 *
		 * @var string
		 */
		protected $wpnonce;

		public function __construct( $args = array() ) {
			parent::__construct( $args );

			$this->wpnonce           = wp_create_nonce( 'code-manager-' . Code_manager::get_current_user_login() );
			$code_manager_tabs_class = CODE_MANAGER_TAB_CLASS;
			$code_manager_tabs       = new $code_manager_tabs_class();
			$code_type_groups        = $code_manager_tabs->get_code_types();
			$code_types              = [''];
			foreach ( $code_type_groups as $code_type_group ) {
				foreach ( $code_type_group as $key => $value ) {
					$this->code_types[ $key ] = $value;
				}
			}
		}

		/**
		 * Show list table
		 *
		 * @since   1.0.0
		 */
		public function show() {
			?>
			<div class="wrap">
				<?php
					$this->show_title();
					$this->show_body();
				?>
			</div>
			<script type="text/javascript">
				var wpnonce = '<?php echo $this->wpnonce; ?>';
				jQuery(function () {
					jQuery('#cb-select-all-1').on('click', function (event) {
						if (jQuery('#cb-select-all-1').is(':checked')) {
							jQuery('[name="bulk-selected[]"]').prop('checked', true);
							jQuery('#cb-select-all-2').prop('checked', true);
						} else {
							jQuery('[name="bulk-selected[]"]').prop('checked', false);
							jQuery('#cb-select-all-2').prop('checked', false);
						}
					});
					jQuery('#cb-select-all-2').on('click', function (event) {
						if (jQuery('#cb-select-all-2').is(':checked')) {
							jQuery('[name="bulk-selected[]"]').prop('checked', true);
							jQuery('#cb-select-all-1').prop('checked', true);
						} else {
							jQuery('[name="bulk-selected[]"]').prop('checked', false);
							jQuery('#cb-select-all-1').prop('checked', false);
						}
					});
				});
			</script>
			<?php
		}

		/**
		 * Add title to page
		 *
		 * @since   1.0.0
		 */
		protected function show_title() {
			?>
			<h1 class="wp-heading-inline">
				<span>
					<span class="cm_page_title">
						<?php echo CODE_MANAGER_H1_TITLE . ' - list mode'; ?>
					</span>
					<?php
					if ( ! Code_Manager_Dashboard::dashboard_enabled() ) {
					?>
						<a href="?page=<?php echo CODE_MANAGER_MENU_SLUG; ?>&tabmode=on"
						   title="Switch to tab mode to open multiple code editors simultaneously">
							<span class="material-icons cm_menu_title">tab</span></a>
						<a href="?page=<?php echo CODE_MANAGER_MENU_SLUG; ?>&action=new"
						   title="Add new code"
						   id="header_new">
							<span class="material-icons cm_menu_title">add_circle_outline</span></a>
						<a href="javascript:void(0)" title="Import code"
						   onclick="jQuery('#upload_file_container').toggle()">
							<span class="material-icons cm_menu_title">arrow_circle_up</span></a>
						<a href="<?php echo CODE_MANAGER_HELP_URL; ?>" target="_blank"
						   title="Plugin help - opens in a new tab or window">
							<span class="material-icons cm_menu_title">help_outline</span></a>
					<?php
					}
					?>
				</span>
			</h1>
			<?php
		}

		/**
		 * Add page body
		 *
		 * JavaScript depending on server variables not available in the browser are added here. Generic JavaScript
		 * code is added as a script file.
		 *
		 * @since   1.0.0
		 */
		protected function show_body() {
			$code_manager_selected_code_type = '';
			if ( isset( $_REQUEST['selected_code_type'] ) ) {
				$code_manager_selected_code_type = sanitize_text_field( wp_unslash( $_REQUEST['selected_code_type'] ) );
			} elseif ( isset( $_COOKIE[CODE_MANAGER_COOKIES_LIST] ) ) {
				$code_manager_selected_code_type = $_COOKIE[CODE_MANAGER_COOKIES_LIST];
			}

			$this->add_import_container();
			?>
			<iframe id="cm_stealth_mode" style="display:none;"></iframe>
			<div id="cm_invisible_container" style="display:none;"></div>
			<div>
				<form id="cm_list_table" method="post" action="?page=<?php echo CODE_MANAGER_MENU_SLUG; ?>">
					<?php
					$this->show_form();
					?>
				</form>
			</div>
			<script type="text/javascript">
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
				var code_manager_selected_code_type = '<?php echo $code_manager_selected_code_type; ?>';
			</script>
			<?php
		}

		/**
		 * Add form to page (including search box)
		 *
		 * @since   1.0.0
		 */
		protected function show_form() {
			$this->prepare_items();
			$this->search_box( 'search', 'search_id' );
			$this->display();
			wp_nonce_field( 'code-manager-export' . Code_manager::get_current_user_login(), '_expnonce', false );
			wp_nonce_field( 'code-manager-delete' . Code_manager::get_current_user_login(), '_delnonce', false );
		}

		/**
		 * Add import container to list table
		 *
		 * @since   1.0.0
		 */
		public function add_import_container() {
			$file_uploads_enabled = @ini_get( 'file_uploads' );
			?>
			<script type='text/javascript'>
				function before_submit_upload() {
					if (jQuery('#filename').val() == '') {
						alert('<?php echo __( 'No file to import!', 'code-manager' ); ?>');
						return false;
					}
					if (!(jQuery('#filename')[0].files[0].size < <?php echo Code_Manager_Export::convert_memory_to_decimal( @ini_get( 'upload_max_filesize' ) ); ?>)) {
						alert("<?php echo __( 'File exceeds maximum size of', 'code-manager' ); ?> <?php echo @ini_get( 'upload_max_filesize' ); ?>!");
						return false;
					}
				}
			</script>
			<style>
                .cm_upload {
                    background: #fff;
                    border-top: 1px solid #ccc;
                    border-bottom: 1px solid #ccc;
                    padding-left: 20px;
                    padding-bottom: 10px;
                }
			</style>
			<div id="upload_file_container" style="display: none">
				<br/>
				<div class="cm_upload">
					<?php if ( $file_uploads_enabled ) { ?>
						<p>
							<strong><?php echo __( 'IMPORTS CODE MANAGER DATA ONLY', 'code-manager' ); ?></strong>
						</p>
						<p>
							<?php
							echo __( 'Supports only file type', 'code-manager' ) .
								' <strong>sql</strong>. ' .
								__( 'Maximum supported file size is', 'code-manager' ) .
								' <strong>' . @ini_get( 'upload_max_filesize' ) . '</strong>.';
							?>
						</p>
						<form id="form_import_table" method="post" enctype="multipart/form-data">
							<input type="file" name="filename" id="filename" accept=".sql">
							<input type="submit" value="<?php echo __( 'Import file', 'code-manager' ); ?>"
								   class="button button-secondary"
								   onclick="return before_submit_upload()">
							<input type="button"
								   onclick="jQuery('#upload_file_container').hide()"
								   class="button button-secondary"
								   value="<?php echo __( 'Cancel', 'code-manager' ); ?>">
							<input type="hidden" name="action" value="import">
							<?php wp_nonce_field( 'code-manager-import' . Code_manager::get_current_user_login(), '_impnonce', false ); ?>
						</form>
					<?php } else { ?>
						<p>
							<strong><?php echo __( 'ERROR', 'code-manager' ); ?></strong>
						</p>
						<p>
							<?php
							echo __( 'Your configuration does not allow file uploads!', 'code-manager' );
							echo ' ';
							echo __( 'Set', 'code-manager' );
							echo ' <strong>';
							echo __( 'file_uploads', 'code-manager' );
							echo '</strong> ';
							echo __( 'to', 'code-manager' );
							echo ' <strong>';
							echo __( 'On', 'code-manager' );
							echo '.';
							echo '</strong>';
							?>
						</p>
					<?php } ?>
				</div>
				<div>&nbsp;</div>
			</div>
			<?php
		}

		/**
		 * Perform database query
		 *
		 * Called from method prepare_items().
		 *
		 * @since   1.0.0
		 *
		 * @param int $per_page Items shown per page
		 * @param int $page_number Current page number
		 *
		 * @return array Contains rows found
		 */
		public static function get_codes( $per_page = 10, $page_number = 1 ) {
			global $wpdb;

			$where = '';

			$selected_code_type = '';
			if ( isset( $_REQUEST['selected_code_type'] ) && '' !== $_REQUEST['selected_code_type'] ) {
				if ( '*' === $_REQUEST['selected_code_type'] ) {
					$selected_code_type = '';
				} else {
					$selected_code_type = sanitize_text_field( wp_unslash( $_REQUEST['selected_code_type'] ) ); // input var okay.
				}
			} elseif ( isset( $_COOKIE[CODE_MANAGER_COOKIES_LIST] ) ) {
				if ( '*' === $_COOKIE[CODE_MANAGER_COOKIES_LIST] ) {
					$selected_code_type = '';
				} else {
					$selected_code_type = $_COOKIE[CODE_MANAGER_COOKIES_LIST];
				}
			}
			if ( '' !== $selected_code_type ) {
				$where .= $wpdb->prepare(
					" where `code_type` = %s",
					[
						$selected_code_type,
					]
				); // WPCS: unprepared SQL OK.
			}

			$search_value = '';
			if ( isset( $_REQUEST[ CODE_MANAGER_SEARCH_ITEM_NAME ] ) ) {
				$search_value = sanitize_text_field( wp_unslash( $_REQUEST[ CODE_MANAGER_SEARCH_ITEM_NAME ] ) ); // input var okay.
			} elseif ( isset( $_COOKIE[ CODE_MANAGER_COOKIES_SEARCH ] ) ) {
				$search_value = $_COOKIE[ CODE_MANAGER_COOKIES_SEARCH ];
			}
			if ( '' !== $search_value ) {
				$where_or_and = ''===$where ? 'where' : 'and';
				$where       .= $wpdb->prepare(
					" {$where_or_and} (`code_name` like '%s' " .
					" or `code_type` like '%s'" .
					" or `code` like '%s')",
					[
						'%' . esc_sql( $search_value ) . '%',
						'%' . esc_sql( $search_value ) . '%',
						'%' . esc_sql( $search_value ) . '%',
					]
				); // WPCS: unprepared SQL OK.
			}

			$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
			$code_manager_model       = new $code_manager_model_class();
			$sql = "select * from " . $code_manager_model::get_base_table_name();
			if ( '' !== $where ) {
				$sql .= $where;
			}
			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
			}
			$sql .= " LIMIT $per_page";
			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

			$result = $wpdb->get_results( $sql, 'ARRAY_A' );
			return $result;
		}

		/**
		 * Return number of records found
		 *
		 * @since   1.0.0
		 *
		 * @param int $per_page Items shown per page
		 * @param int $page_number Current page number
		 *
		 * @return array Contains rows found
		 */
		public static function record_count() {
			global $wpdb;

			$where = '';

			$selected_code_type = '';
			if ( isset( $_REQUEST['selected_code_type'] ) && '' !== $_REQUEST['selected_code_type'] ) {
				if ( '*' === $_REQUEST['selected_code_type'] ) {
					$selected_code_type = '';
				} else {
					$selected_code_type = sanitize_text_field( wp_unslash( $_REQUEST['selected_code_type'] ) ); // input var okay.
				}
			} elseif ( isset( $_COOKIE[CODE_MANAGER_COOKIES_LIST] ) ) {
				if ( '*' === $_COOKIE[CODE_MANAGER_COOKIES_LIST] ) {
					$selected_code_type = '';
				} else {
					$selected_code_type = $_COOKIE[CODE_MANAGER_COOKIES_LIST];
				}
			}
			if ( '' !== $selected_code_type ) {
				$where .= $wpdb->prepare(
					" where `code_type` = %s",
					[
						$selected_code_type,
					]
				); // WPCS: unprepared SQL OK.
			}

			$search_value = '';
			if ( isset( $_REQUEST[ CODE_MANAGER_SEARCH_ITEM_NAME ] ) ) {
				$search_value = sanitize_text_field( wp_unslash( $_REQUEST[ CODE_MANAGER_SEARCH_ITEM_NAME ] ) ); // input var okay.
			} elseif ( isset( $_COOKIE[ CODE_MANAGER_COOKIES_SEARCH ] ) ) {
				$search_value = $_COOKIE[ CODE_MANAGER_COOKIES_SEARCH ];
			}
			if ( '' !== $search_value ) {
				$where_or_and = ''==$where ? 'where' : 'and';
				$where       .= $wpdb->prepare(
					" {$where_or_and} (`code_name` like '%s' " .
					" or `code_type` like '%s'" .
					" or `code` like '%s')",
					[
						'%' . esc_attr( $search_value ) . '%',
						'%' . esc_attr( $search_value ) . '%',
						'%' . esc_attr( $search_value ) . '%',
					]
				); // WPCS: unprepared SQL OK.
			}

			$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
			$code_manager_model       = new $code_manager_model_class();
			$sql = 'select count(*) from ' . $code_manager_model::get_base_table_name();
			if ( '' !== $where ) {
				$sql .= $where;
			}

			return $wpdb->get_var( $sql );
		}

		/**
		 * Overwrite method (WP_List_Table.)search_box to handle cookies in search
		 *
		 * @since   1.0.0
		 *
		 * @param string $text
		 * @param string $input_id
		 */
		public function search_box( $text, $input_id ) {
			// Always show search box!!!
			// This plugin uses cookies. If the search box is disabled when nothing is found, it is not possible to
			// remove the search criterion.

			// if ( empty( $_REQUEST[ CODE_MANAGER_SEARCH_ITEM_NAME ] ) && ! $this->has_items() ) {
			//	return;
			// }

			$input_id = $input_id . '-search-input';

			$search_value = '';
			if ( isset( $_REQUEST[ CODE_MANAGER_SEARCH_ITEM_NAME ] ) ) {
				$search_value = sanitize_text_field( wp_unslash( $_REQUEST[ CODE_MANAGER_SEARCH_ITEM_NAME ] ) ); // input var okay.
			} elseif ( isset( $_COOKIE[ CODE_MANAGER_COOKIES_SEARCH ] ) ) {
				$search_value = $_COOKIE[ CODE_MANAGER_COOKIES_SEARCH ];
			}

			if ( ! empty( $_REQUEST['orderby'] ) ) {
				echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
			}
			if ( ! empty( $_REQUEST['order'] ) ) {
				echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
			}
			if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
				echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
			}
			if ( ! empty( $_REQUEST['detached'] ) ) {
				echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
			}
			?>
			<p class="search-box">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
				<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo CODE_MANAGER_SEARCH_ITEM_NAME; ?>" value="<?php echo $search_value; ?>" />
				<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
			</p>
			<?php
		}

		/**
		 * Overwrite method (WP_List_Table.)display_tablenav to handle cookies
		 *
		 * If bulk actions are hidden when nothing is found, the user cannot change the code type selection.
		 *
		 * @since   1.0.0
		 *
		 * @param string $which
		 */
		protected function display_tablenav( $which ) {
			// Always show bulk actions
			if ( 'top' === $which ) {
				wp_nonce_field( 'bulk-' . $this->_args['plural'] );
			}
			?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">
					<div class="alignleft actions bulkactions">
						<?php $this->bulk_actions( $which ); ?>
					</div>
				<?php
				$this->extra_tablenav( $which );
				$this->pagination( $which );
				?>
				<br class="clear" />
			</div>
			<?php
		}

		/**
		 * Overwrite method (WP_List_Table.)column_default to:
		 * (1) Add edit code link (@see Code_Manager_List::column_default_add_action_edit())
		 * (2) Add delete code link (@see Code_Manager_List::column_default_add_action_delete())
		 * (3) Add copy shortcode link (for shortcodes only)
		 * (4) Add code enable/disable checkbox/listbox
		 *
		 * @since   1.0.0
		 *
		 * @param object $item
		 * @param string $column_name
		 *
		 * @return mixed|string|void
		 */
		public function column_default( $item, $column_name ) {
			if ( 'code_id' === $column_name ) {
				if ( ! isset( $this->code_types[ $item[ 'code_type' ] ] ) ) {
					$actions['noactions'] = 'No actions';
				} else {
					$actions['edit']   = $this->column_default_add_action_edit( $item, $column_name );
					$actions['delete'] = $this->column_default_add_action_delete( $item, $column_name );
				}

				return sprintf( '%1$s %2$s', $item[ $column_name ], $this->row_actions( $actions ) );
			}

			if ( 'code_type' === $column_name ) {
				if ( strpos( $item[ $column_name ], 'shortcode' ) !== false ) {
					$shortcode_id   = '[' . CODE_MANAGER_SHORT_CODE . " id=\"{$item['code_id']}\"]";
					$shortcode_name = '[' . CODE_MANAGER_SHORT_CODE . " name=\"{$item['code_name']}\"]";
					$title     = __( 'Copy shortcode to clipboard', 'code-manager' );
					$message   = __( 'Shortcode copied to clipboard', 'code-manager' );
					if ( isset( $this->code_types[ $item[ $column_name ] ] ) ) {
						$output =  $this->code_types[ $item[ $column_name ] ] . '<div style="height:10px"></div>';
					}
					$output    .= '<a href="javascript:void(0)" class="dashicons dashicons-image-rotate" ' .
					             'onclick="jQuery(\'#cm_copy_id_' . self::$list_number . '\').toggle(); ' .
					             'jQuery(\'#cm_copy_name_' . self::$list_number . '\').toggle();"' .
					             '></a>&nbsp;' .
					             '<span id="cm_copy_id_' . self::$list_number . '" style="display:none">' .
					             $shortcode_id . ' ' .
					             '<a href="javascript:void(0)" class="dashicons dashicons-clipboard c2c" ' .
					             "onclick='jQuery.notify(\"{$message}\", \"info\")' " .
					             "data-clipboard-text='{$shortcode_id}' title='{$title}'" .
					             '></a></span>' .
					             '<span id="cm_copy_name_' . self::$list_number . '">' .
					             $shortcode_name . ' ' .
					             '<a href="javascript:void(0)" class="dashicons dashicons-clipboard c2c" ' .
					             "onclick='jQuery.notify(\"{$message}\", \"info\")' " .
					             "data-clipboard-text='{$shortcode_name}' title='{$title}'" .
					             '></a></span>';
					return $output;
				} else {
					if ( isset( $this->code_types[ $item[ $column_name ] ] ) ) {
						return $this->code_types[ $item[ $column_name ] ];
					}
				}
				return __( 'Unknown code type', 'code-manager' ) . ': ' . $item[ $column_name ];
			}

			if ( 'code_enabled' === $column_name ) {
				$id       = $item[ 'code_id' ];
				$wp_nonce = wp_create_nonce( 'code-manager-' . Code_manager::get_current_user_login() );
				$checked  = 1 == $item['code_enabled'] ? 'checked' : '';
				return
					"<label style='white-space:nowrap' title='Enable code' class='cm_tooltip'>
						<input type='checkbox' 
							   id='code_enabled_{$id}' 
							   $checked 
							   onclick='activate_code($id,\"$wp_nonce\")'
						>Enable
					</label><br/>" .
					$this->add_preview_switch( $id );
			}

			if ( 'code' === $column_name ) {
				$code     = '<pre>' . esc_html( str_replace( '&', '&amp;', $item[ $column_name ] ) ) . '</pre>';
				$code     = str_replace( '`', '\`', $code );
				$title    = "({$item['code_id']}) {$item['code_name']} ({$item['code_type']})";

				$function = 'show_code_' . self::$list_number;
				$dialog   = 'dialog_' . self::$list_number;

				return
					"<script type='text/javascript'>
						function {$function}() {
							html = `<div>{$code}</div>`;
							height = jQuery(window).height() * 0.6;
							var {$dialog} = jQuery(html).dialog({
								dialogClass: 'no-close',
								title: '{$title}',
								modal: true,
								width: '60%',
								height: height,
								buttons: {
									'OK': function() {
										{$dialog}.dialog('destroy');
									}
								}
							});
							}
					</script>
					<a href='javascript:void(0)' onclick='{$function}()'>
						<span class='dashicons dashicons-editor-code cm_menu_title' 
							  title='Click to view code'
						></span>
					</a>";
			}

			return esc_html( str_replace( '&', '&amp;', $item[ $column_name ] ) );
		}

		protected function add_preview_switch( $id ) {
			if ( Code_Manager_Preview::is_code_id_preview_enabled( $id ) ) {
				$checked = 'checked';
			} else {
				$checked = '';
			}
			return "<label style='white-space:nowrap' title='Enable preview mode for this code' class='cm_tooltip'>
						<input type='checkbox' 
							   id='code_enabled_{$id}' 
							   $checked 
							   onclick='set_code_preview($id,\"{$this->wpnonce}\",jQuery(this).is(\":checked\"))'
						>Preview
					</label>";
		}

		/**
		 * Override method (WP_List_Table.)column_cb
		 *
		 * @since   1.0.0
		 *
		 * @param object $item
		 *
		 * @return string|void
		 */
		public function column_cb( $item ) {
			return "<input type='checkbox' name='bulk-selected[]' value='" . $item['code_id'] . "' />";
		}

		/**
		 * Adds edit code link to a given row
		 *
		 * @since   1.0.0
		 *
		 * @param $item
		 * @param $column_name
		 *
		 * @return string
		 */
		protected function column_default_add_action_edit( $item, $column_name ) {
			return '<a href="?page=' . CODE_MANAGER_MENU_SLUG .
				   '&action=edit&code_id=' . $item[ $column_name ] .
				   '" class="edit">Edit</a>';
		}

		/**
		 * Adds delete code link to a given row
		 *
		 * @since   1.0.0
		 *
		 * @param $item
		 * @param $column_name
		 *
		 * @return string
		 */
		protected function column_default_add_action_delete( $item, $column_name ) {
			$page = isset( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1;

			$wp_nonce        = wp_create_nonce( 'code-manager-delete' . Code_manager::get_current_user_login() );
			$form_id         = '_' . ( self::$list_number++ );
			$delete_form     =
				"<form" .
				" id='delete_form$form_id'" .
				" action='?page=" . CODE_MANAGER_MENU_SLUG . "'" .
				" method='post'>" .
				"<input type='hidden' name='code_id' value='{$item[ $column_name ]}'>" .
				"<input type='hidden' name='action' value='delete' />" .
				"<input type='hidden' name='paged' value='{$page}' />" .
				"<input type='hidden' name='_wpnonce' value='{$wp_nonce}'>" .
				"</form>";
			?>

			<script type='text/javascript'>
				jQuery("#cm_invisible_container").append("<?php echo $delete_form; ?>");
				function delete_code<?php echo $form_id; ?>() {
					html = "<div>You are about to permanently delete this code from your site. This action cannot be undone. 'No' to stop, 'Yes' to delete.</div>";
					var dialog = jQuery(html).dialog({
						dialogClass: 'no-close',
						title: 'Delete code?',
						buttons: {
							'Yes': function() {
								dialog.dialog('destroy');
								jQuery('#delete_form<?php echo $form_id; ?>').submit();
							},
							'No':  function() {
								dialog.dialog('destroy');
							},
							'Cancel':  function() {
								dialog.dialog('destroy');
							}
						}
					});
				}
			</script>

			<?php
			return sprintf(
				'<a href="javascript:void(0)" class="delete" onclick="delete_code%s()">Delete</a>',
				$form_id
			);
		}

		/**
		 * Overwrite method to remove class fixed (prevents wrapping shortcode)
		 *
		 * @since   1.0.0
		 *
		 * @return array|string[]
		 */
		protected function get_table_classes() {
			return array( 'widefat', 'striped', $this->_args['plural'] );
		}

		/**
		 * Overrides method (WP_List_Table.)prepare_items
		 *
		 * @since   1.0.0
		 */
		public function prepare_items() {
			$columns               = $this->get_columns();
			$hidden                = $this->get_hidden_columns();
			$sortable              = $this->get_sortable_columns();
			$primary               = $this->get_primary_column();
			$this->_column_headers = [ $columns, $hidden, $sortable, $primary ];

			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'code_manager_rows_per_page', 10 );
			$current_page = $this->get_pagenum();
			$total_items  = static::record_count();

			$this->set_pagination_args( [
				'total_items' => $total_items,
				'per_page'    => $per_page
			] );

			$this->items = static::get_codes( $per_page, $current_page );
		}

		/**
		 * Overrides method (WP_List_Table.)get_bulk_actions
		 *
		 * @since   1.0.0
		 *
		 * @return array|string[]
		 */
		public function get_bulk_actions() {
			$actions = [
				'bulk-delete' => 'Delete Permanently',
				'export'      => 'Export',
			];

			return $actions;
		}

		/**
		 * Implements bulk actions recognition
		 *
		 * @since   1.0.0
		 */
		public function process_bulk_action() {
			if ( 'delete' === $this->current_action() ) {
				$this->code_delete();
			} else if ( 'bulk-delete' === $this->current_action() ) {
				$this->code_bulk_delete();
			} elseif ( 'export' === $this->current_action() ) {
				$this->code_export();
			} elseif ( 'import' === $this->current_action() ) {
				$this->code_import();
			}
		}

		/**
		 * Implement code delete action
		 *
		 * @since   1.0.0
		 */
		protected function code_delete() {
			$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-delete' . Code_manager::get_current_user_login() ) ) {
				$msg = new Message_Box(
					[
						'message_text'           => __( 'Not authorized', 'code-manager' ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();

				return;
			}

			if ( isset( $_REQUEST['code_id'] ) ) {
				$code_id                  = sanitize_text_field( wp_unslash( $_REQUEST['code_id'] ) ); // input var okay.
				$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
				$code_manager_model       = new $code_manager_model_class();
				$delete_failed            = 1 !== $code_manager_model::dml_delete( $code_id );
			} else {
				$delete_failed = true;
			}

			if ( $delete_failed ) {
				$msg = new Message_Box(
					[
						'message_text'           => __( 'Delete action failed', 'code-manager' ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();
			} else {
				$msg = new Message_Box(
					[
						'message_text' => __( 'Succesfully deleted code', 'code-manager' ),
					]
				);
				$msg->box();
			}
		}

		/**
		 * Implement code delete bulk action
		 *
		 * @since   1.0.0
		 */
		protected function code_bulk_delete() {
			if ( isset( $_REQUEST['bulk-selected'] ) ) {
				$wp_nonce = isset( $_REQUEST['_delnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_delnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-delete' . Code_manager::get_current_user_login() ) ) {
					$msg = new Message_Box(
						[
							'message_text'           => __( 'Not authorized', 'code-manager' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();

					return;
				}

				$bulk_rows     = $_REQUEST['bulk-selected'];
				$delete_failed = 0;
				for ( $i = 0; $i < count( $bulk_rows ); $i ++ ) {
					$code_id                  = sanitize_text_field( wp_unslash( $_REQUEST['bulk-selected'][ $i ] ) ); // input var okay.
					$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
					$code_manager_model       = new $code_manager_model_class();

					if ( 1 !== $code_manager_model::dml_delete( $code_id ) ) {
						$delete_failed ++;
					}
				}

				if ( $delete_failed > 0 ) {
					if ( $delete_failed === count( $bulk_rows ) ) {
						$msg = new Message_Box(
							[
								'message_text'           => __( 'Some delete actions failed', 'code-manager' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					} else {
						$msg = new Message_Box(
							[
								'message_text'           => __( 'Could not delete code', 'code-manager' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					}
				} else {
					$msg = new Message_Box(
						[
							'message_text' => __( 'Succesfully deleted code', 'code-manager' ),
						]
					);
					$msg->box();
				}
			} else {
				$msg = new Message_Box(
					[
						'message_text' => __( 'Nothing to delete', 'code-manager' ),
					]
				);
				$msg->box();
			}
		}

		/**
		 * Export code.
		 */
		protected function code_export() {
			if ( isset( $_REQUEST['bulk-selected'] ) ) {
				$wp_nonce = isset( $_REQUEST['_expnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_expnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-export' . Code_manager::get_current_user_login() ) ) {
					$msg = new Message_Box(
						[
							'message_text'           => __( 'Not authorized', 'code-manager' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();

					return;
				}

				$bulk_rows  = $_REQUEST['bulk-selected'];
				$code_ids   = '';
				$rows       = 0;
				for ( $i = 0; $i < count( $bulk_rows ); $i ++ ) {
					$code_id = sanitize_text_field( wp_unslash( $_REQUEST['bulk-selected'][ $i ] ) ); // input var okay.
					if ( is_numeric( $code_id ) ) {
						$code_ids .= "&cid[{$rows}]={$code_id}";
						$rows++;
					}
				}

				// Prepare URL
				$querystring = admin_url() . "admin.php?action=code_manager_export&wpnonce={$wp_nonce}{$code_ids}";

				// Start export
				echo '
					<script type="text/javascript">
						jQuery(function() {
							jQuery("#cm_stealth_mode").attr("src","' . $querystring . '");
						});
					</script>
				';
			} else {
				$msg = new Message_Box(
					[
						'message_text' => __( 'Nothing to export', 'code-manager' ),
					]
				);
				$msg->box();
			}
		}

		/**
		 * Import code.
		 */
		protected function code_import() {
			$wp_nonce = isset( $_REQUEST['_impnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_impnonce'] ) ) : ''; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-import' . Code_manager::get_current_user_login() ) ) {
				$msg = new Message_Box(
					[
						'message_text'           => __( 'Not authorized', 'code-manager' ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();

				return;
			}

			Code_Manager_Import::import();
		}

		/**
		 * Overrides method (WP_List_Table.)get_columns
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public function get_columns() {
			return static::get_column_labels_default();
		}

		/**
		 * Returns all column labels
		 *
		 * Defined as static method to be used in multiple situations and maintained in one location.
		 *
		 * @since   1.0.0
		 *
		 * @return array
		 */
		public static function get_column_labels_default() {
			return [
				'cb'                     => '<input type="checkbox" />',
				'code_id'                => __( 'ID', 'code-manager' ),
				'code_name'              => __( 'Name', 'code-manager' ),
				'code_type'              => __( 'Shortcode', 'code-manager' ),
				'code_enabled'           => __( 'Status', 'code-manager' ),
				'code'                   => __( 'Code', 'code-manager' ),
				'code_author'            => __( 'Author', 'code-manager' ),
				'code_description'       => __( 'Description', 'code-manager' ),
			];
		}

		/**
		 * Returns hidden columns (taken from usermeta)
		 *
		 * @since   1.0.0
		 *
		 * @return string[]
		 */
		public function get_hidden_columns() {
			$hidden = get_user_meta(
				get_current_user_id(),
				'manage' . get_current_screen()->id . 'columnshidden'
			);

			return 0 === sizeof( $hidden ) ? self::get_hidden_columns_default() : $hidden[0];
		}

		/**
		 * Returns default hidden columns
		 *
		 * Can be changed by the user in screen options.
		 *
		 * @since   1.0.0
		 *
		 * @return string[]
		 */
		public static function get_hidden_columns_default() {
			return [
				'code_url',
				'code',
			];
		}

		/**
		 * Overrides method (WP_List_Table.)get_sortable_columns
		 *
		 * @since   1.0.0
		 *
		 * @return array|array[]
		 */
		public function get_sortable_columns() {
			return self::_get_sortable_columns();
		}

		/**
		 * Get sortable columns
		 *
		 * Defined as static method to be used in multiple situations and maintained in one location.
		 *
		 * @since   1.0.0
		 *
		 * @return array[]
		 */
		public static function _get_sortable_columns() {
			return [
				'code_id'                => ['code_id', false],
				'code_name'              => ['code_name', false],
				'code_type'              => ['code_type', false],
				'code_enabled'           => ['code_type', false],
				'code_author'            => ['code_author', false],
				'code_description'       => ['code_description', false],
			];
		}

		/**
		 * Overrides method (WP_List_Table.)get_primary_column
		 *
		 * @since   1.0.0
		 *
		 * @return int|string
		 */
		public function get_primary_column() {
			return 'code_id';
		}

	}

}