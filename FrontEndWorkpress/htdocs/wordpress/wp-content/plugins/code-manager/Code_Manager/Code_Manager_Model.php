<?php

namespace Code_Manager {

	/**
	 * Class Code_Manager_Model
	 *
	 * Interface between code manager front-end and code manager database table.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class Code_Manager_Model {

		/**
		 * Base table name without prefix
		 */
		const BASE_TABLE_NAME = 'code_manager';

		/**
		 * Base table name with prefix
		 *
		 * @since   1.0.0
		 *
		 * @return string Real base table name
		 */
		public static function get_base_table_name() {
			global $wpdb;
			return $wpdb->prefix . static::BASE_TABLE_NAME;
		}

		/**
		 * Check if base table exists
		 *
		 * @since   1.0.0
		 *
		 * @return bool TRUE = table found
		 */
		public static function table_exists() {
			global $wpdb;

			$wpdb->query(
				$wpdb->prepare( '
					select true
					  from `information_schema`.`tables`
					 where table_schema = %s
					   and table_name   = %s
				',
					[
						$wpdb->dbname,
						self::get_base_table_name(),
					]
				)
			);
			$wpdb->get_results();

			return 1 === $wpdb->num_rows;
		}

		/**
		 * Get record from code manager table for given Code ID
		 *
		 * @since   1.0.0
		 *
		 * @param integer $code_id Code ID
		 *
		 * @return array
		 */
		public static function dml_query( $code_id ) {
			global $wpdb;
			return $wpdb->get_results(
				$wpdb->prepare(
					'select * from `' . self::get_base_table_name() . '` ' .
					'where code_id = %d',
					[
						$code_id
					]
				),
				'ARRAY_A'
			);
		}

		/**
		 * Get record from code manager table for given code name
		 *
		 * @since   1.0.0
		 *
		 * @param integer $code_name Code name
		 *
		 * @return array
		 */
		public static function dml_query_by_name( $code_name ) {
			global $wpdb;
			return $wpdb->get_results(
				$wpdb->prepare(
					'select * from `' . self::get_base_table_name() . '` ' .
					'where code_name = %s',
					[
						$code_name
					]
				),
				'ARRAY_A'
			);
		}

		/**
		 * Insert new row into code manager table
		 *
		 * @since   1.0.0
		 *
		 * @param string $code_name Code name
		 * @param integer $code_type Code type
		 * @param string $code Code
		 * @param string $code_author Author
		 * @param string $code_description Description
		 *
		 * @return int Code ID if insert was successful or -1 if insert failed
		 */
		public static function dml_insert( $code_name, $code_type, $code, $code_author, $code_description, $code_enabled ) {
			global $wpdb;
			$rows = $wpdb->insert(
				self::get_base_table_name(),
				[
					'code_name'        => $code_name,
					'code_type'        => $code_type,
					'code_enabled'	   => $code_enabled,
					'code'             => $code,
					'code_author'      => $code_author,
					'code_description' => $code_description,
				]
			);
			return 1 === $rows ? $wpdb->insert_id : -1;
		}

		/**
		 * Update row in code manager table
		 *
		 * @since   1.0.0
		 *
		 * @param integer $code_id Code ID
		 * @param string $code_name Code name
		 * @param string $code_type Code type
		 * @param string $code Code
		 * @param string $code_author Author
		 * @param string $code_description Description
		 *
		 * @return integer Number of rows updated
		 */
		public static function dml_update( $code_id, $code_name, $code_type, $code, $code_author, $code_description, $code_enabled ) {
			$code_row          = self::dml_query( $code_id );
			$code_type_changed = false;

			if ( is_array( $code_row ) && 1 === sizeof( $code_row ) ) {
				if ( ! isset( $code_row[0]['code_type'] ) ) {
					return 0;
				} else {
					if ( $code_type !== $code_row[0]['code_type'] ) {
						$code_type_changed = true;
					}
				}
			} else {
				return 0;
			}

			$column_values = [
				'code_name'        => $code_name,
				'code_type'        => $code_type,
				'code_enabled'	   => $code_enabled,
				'code'             => $code,
				'code_author'      => $code_author,
				'code_description' => $code_description,
			];
			if ( $code_type_changed ) {
				$column_values['code_enabled'] = 0;
			}

			global $wpdb;
			return $wpdb->update(
				self::get_base_table_name(),
				$column_values,
				[
					'code_id' => $code_id
				]
			);
		}

		/**
		 * Delete row from code manager table
		 *
		 * @since   1.0.0
		 *
		 * @param integer $code_id Code ID
		 *
		 * @return integer Number of rows deleted
		 */
		public static function dml_delete( $code_id ) {
			global $wpdb;
			return $wpdb->query(
				$wpdb->prepare(
					'delete from `' . self::get_base_table_name() . '` ' .
					'where code_id = %d',
					[
						$code_id
					]
				)
			);
		}

		/**
		 * Get shortcode for a given code id
		 *
		 * @since   1.0.0
		 *
		 * @param integer $code_id Code ID
		 *
		 * @return string Code
		 */
		public static function get_code_from_id( $code_id, $action = null ) {
			if ( is_numeric( $code_id ) ) {
				global $wpdb;
				$query = 'select * from `' . self::get_base_table_name() . "` where code_id = %d";
				$code  =
					$wpdb->get_results(
						$wpdb->prepare(
							$query,
							[
								$code_id
							]
						),
						'ARRAY_A'
					);

				if ( 1 === $wpdb->num_rows ) {
					if ( null === $action ) {
						return $code[0]['code'];
					} else {
						return json_encode( $code[0] );
					}
				}
			}

			return '';
		}

		/**
		 * Get shortcode for a given code name
		 *
		 * @since   1.0.0
		 *
		 * @param integer $code_id Code ID
		 *
		 * @return string Code
		 */
		protected static function get_code_from_name( $code_name ) {
			if ( '' !== $code_name ) {
				global $wpdb;
				$query = 'select * from `' . self::get_base_table_name() . "`  where code_name = %s";
				$code  =
					$wpdb->get_results(
						$wpdb->prepare(
							$query,
							[
								$code_name
							]
						),
						'ARRAY_A'
					);

				if ( 1 === $wpdb->num_rows ) {
					return $code[0]['code'];
				}
			}

			return '';
		}

		/**
		 * Get codes for a given code type
		 *
		 * @since   1.0.0
		 *
		 * @param string $code_type Code type
		 *
		 * @return array List of code
		 */
		public static function get_codes( $code_type ) {
			global $wpdb;
			$query = 'select * from `' . self::get_base_table_name() . '` ' .
				"where code_type = '{$code_type}'"; // No prepare needed
			return $wpdb->get_results( $query, 'ARRAY_A' );
		}

		/**
		 * Get active codes (status = enabled) for a given code type
		 *
		 * @since   1.0.0
		 *
		 * @param string $code_type Code type
		 *
		 * @return array List of code
		 */
		public static function get_active_codes( $code_type ) {
			global $wpdb;
			$query = 'select * from `' . self::get_base_table_name() . '` ' .
			         "where code_type = '{$code_type}' and code_enabled > 0"; // No prepare needed
			return $wpdb->get_results( $query, 'ARRAY_A' );
		}

		/**
		 * Return only PHP, HTML and JS shortcodes
		 *
		 * @return mixed
		 */
		public static function get_active_shortcodes() {
			global $wpdb;
			$query = 'select * from `' . self::get_base_table_name() . '` ' .
				"where code_type like '%shortcode%' and code_type not like '%css%' and code_enabled > 0"; // No prepare needed
			return $wpdb->get_results( $query, 'ARRAY_A' );
		}

		/**
		 * Update code from ajax request (insert when new: code_id = -1)
		 *
		 * @since   1.0.0
		 */
		public static function update_code() {
			self::header_no_cache();

			if (
				isset( $_REQUEST['wpnonce'] ) ||
				isset( $_REQUEST['code_id'] ) ||
				isset( $_REQUEST['code_name'] ) ||
				isset( $_REQUEST['code_type'] ) ||
				isset( $_REQUEST['code'] )
			) {
				// All arguments available, start update process
				$code_id = sanitize_text_field( wp_unslash( $_REQUEST['code_id'] ) ); // input var okay.

				// Check if actions is allowed
				$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-' . Code_manager::get_current_user_login() ) ) {
					echo 'ERR-Token expired, please refresh page';
					wp_die();
				}

				$code_name = sanitize_text_field( wp_unslash( $_REQUEST['code_name'] ) ); // input var okay.
				$code_type = sanitize_text_field( wp_unslash( $_REQUEST['code_type'] ) ); // input var okay.
				$code      = wp_unslash( $_REQUEST['code'] ); // input var okay.

				global $wpdb;
				$wpdb->suppress_errors( true );

				if ( '-1' == $code_id ) {
					// Insert new code
					$rows_inserted = $wpdb->insert(
						self::get_base_table_name(),
						[
							'code_name' => $code_name,
							'code_type' => $code_type,
							'code'      => $code
						]
					);

					echo 1 === $rows_inserted ? 'INS-' . $wpdb->insert_id : 'ERR-' . $wpdb->last_error;
				} else {
					// Update existing code
					$code_row          = self::dml_query( $code_id );
					$code_type_changed = false;

					if ( is_array( $code_row ) && 1 === sizeof( $code_row ) ) {
						if ( ! isset( $code_row[0]['code_type'] ) ) {
							echo 'UPD-0';
							wp_die();
						} else {
							if ( $code_type !== $code_row[0]['code_type'] ) {
								$code_type_changed = true;
							}
						}
					} else {
						echo 'UPD-0';
						wp_die();
					}

					$set_columns = 'set code_name = %s, code_type = %s, code = %s ';
					if ( $code_type_changed ) {
						$set_columns .= ', code_enabled = 0 ';
					}

					$rows_updated = $wpdb->query(
						$wpdb->prepare(
							'update ' . self::get_base_table_name() . ' ' .
							$set_columns .
							'where code_id = %d',
							[
								$code_name,
								$code_type,
								$code,
								$code_id
							]
						)
					);

					echo '' === $wpdb->last_error ? "UPD-{$rows_updated}" : 'ERR-' . $wpdb->last_error;
				}
			} else {
				echo 'ERR-Wrong arguments';
			}

			wp_die();
		}

		/**
		 * Activate code preview from ajax request for a given code_id
		 *
		 * @since   1.0.0
		 */
		public static function activate_code_preview() {
			self::header_no_cache();

			if ( is_user_logged_in() && isset( $_REQUEST['wpnonce'] ) && isset( $_REQUEST['code_id'] ) ) {
				// Check if action is allowed
				$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-' . Code_manager::get_current_user_login() ) ) {
					echo 'ERR-Token expired, please refresh page';
					wp_die();
				}

				$code_id = sanitize_text_field( wp_unslash( $_REQUEST['code_id'] ) ); // input var okay.

				Code_Manager_Preview::add_user_preview_code_id( $code_id );

				echo 'OK';
			} else {
				echo 'ERR-Wrong arguments';
			}

			wp_die();
		}

		/**
		 * Deactivate code preview from ajax request for a given code_id
		 *
		 * @since   1.0.0
		 */
		public static function deactivate_code_preview() {
			self::header_no_cache();

			if ( is_user_logged_in() && isset( $_REQUEST['wpnonce'] ) && isset( $_REQUEST['code_id'] ) ) {
				// Check if action is allowed
				$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-' . Code_manager::get_current_user_login() ) ) {
					echo 'ERR-Token expired, please refresh page';
					wp_die();
				}

				$code_id = sanitize_text_field( wp_unslash( $_REQUEST['code_id'] ) ); // input var okay.

				Code_Manager_Preview::remove_user_preview_code_id( $code_id );

				echo 'OK';
			} else {
				echo 'ERR-Wrong arguments';
			}

			wp_die();
		}

		/**
		 * Reset all previewed code IDs
		 *
		 * @since   1.0.0
		 */
		public static function reset_preview() {
			self::header_no_cache();

			if ( is_user_logged_in() && isset( $_REQUEST['wpnonce'] ) ) {
				// Check if action is allowed
				$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-' . Code_manager::get_current_user_login() ) ) {
					echo 'ERR-Token expired, please refresh page';
					wp_die();
				}

				global $wpdb;
				$wpdb->query( "delete from {$wpdb->prefix}usermeta where meta_key = 'code_manager_preview_code_ids'" );

				echo 'OK';
			} else {
				echo 'ERR-Wrong arguments';
			}

			wp_die();
		}

		/**
		 * Activate code from ajax request for a given code_id
		 *
		 * @since   1.0.0
		 */
		public static function activate_code() {
			self::header_no_cache();

			if (
				isset( $_REQUEST['wpnonce'] ) &&
				isset( $_REQUEST['code_id'] ) &&
				isset( $_REQUEST['code_item_value'] )
			) {
				$code_id   = sanitize_text_field( wp_unslash( $_REQUEST['code_id'] ) ); // input var okay.

				// Check if action is allowed
				$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-' . Code_manager::get_current_user_login() ) ) {
					echo 'ERR-Token expired, please refresh page';
					wp_die();
				}

				$code_item_value = sanitize_text_field( wp_unslash( $_REQUEST['code_item_value'] ) ); // input var okay.
				$update_values = [
					'code_enabled' => $code_item_value
				];

				global $wpdb;
				$wpdb->suppress_errors( true );
				$rows_update = $wpdb->update(
					self::get_base_table_name(),
					$update_values,
					[
						'code_id' => $code_id
					]
				);

				echo '' === $wpdb->last_error ? "UPD-{$rows_update}" : 'ERR-' . $wpdb->last_error;
			} else {
				echo 'ERR-Wrong arguments';
			}
		}

		/**
		 * Get a list with all available codes from ajax request
		 *
		 * @since   1.0.0
		 */
		public static function get_code_list() {
			self::header_no_cache();

			// Check if action is allowed
			$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : ''; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-' . Code_manager::get_current_user_login() ) ) {
				echo 'ERR-Token expired, please refresh page';
				wp_die();
			}

			$code_manager_tabs_class = CODE_MANAGER_TAB_CLASS;
			$code_manager_tabs       = new $code_manager_tabs_class();
			$code_type_groups        = $code_manager_tabs->get_code_types();
			$code_types              = [''];
			foreach ( $code_type_groups as $code_type_group ) {
				foreach ( $code_type_group as $key => $value ) {
					$code_types[] = $key;
				}
			}

			global $wpdb;
			$query = 'select code_id, code_name, code_type, code_enabled from ' . self::get_base_table_name() . ' ' .
			         "where code_type in ('" . implode( "','", $code_types ) . "') " .
			         'order by code_name';
			$rows  = $wpdb->get_results( $query, 'ARRAY_A' );

			$i = 0;
			while ( $i < sizeof( $rows ) ) {
				$rows[$i]['preview_enabled'] = Code_Manager_Preview::is_code_id_preview_enabled( $rows[$i]['code_id'] );
				$i++;
			}
			echo json_encode( $rows );

			wp_die();
		}

		public static function get_code() {
			if ( isset( $_POST['wpda_action'] ) ** 'all' === $_POST['wpda_action'] ) {
				self::header_no_cache( 'application/json' );
			} else {
				self::header_no_cache();
			}

			if ( isset( $_REQUEST['code_id'] ) ) {
				$code_id = sanitize_text_field( wp_unslash( $_REQUEST['code_id'] ) ); // input var okay.

				// Check if action is allowed
				$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-get-code' . Code_manager::get_current_user_login() ) ) {
					echo 'ERR-Token expired, please refresh page';
					wp_die();
				}

				echo self::get_code_from_id( $code_id, isset( $_POST['wpda_action'] ) ? $_POST['wpda_action'] : null );
			} else {
				echo 'ERR-Wrong arguments';
			}

			wp_die();
		}

		public static function is_code_preview_enabled() {
			self::header_no_cache();

			if (
				isset( $_REQUEST['wpnonce'] ) &&
				isset( $_REQUEST['code_id'] )
			) {
				$code_id   = sanitize_text_field( wp_unslash( $_REQUEST['code_id'] ) ); // input var okay.

				// Check if action is allowed
				$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-get-code' . Code_manager::get_current_user_login() ) ) {
					echo 'ERR-Token expired, please refresh page';
					wp_die();
				}

				echo Code_Manager_Preview::is_code_id_preview_enabled( $code_id ) ? "true" : "false";
			} else {
				echo 'ERR-Wrong arguments';
			}

			wp_die();
		}

		/**
		 * Check if code name exists from ajax request
		 *
		 * @since   1.0.0
		 */
		public static function code_name_exists() {
			self::header_no_cache();

			if ( isset( $_REQUEST['code_name'] ) ) {
				$code_name = sanitize_text_field( wp_unslash( $_REQUEST['code_name'] ) ); // input var okay.

				// Check if action is allowed
				$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-get-code' . Code_manager::get_current_user_login() ) ) {
					echo 'ERR-Token expired, please refresh page';
					wp_die();
				}

				if ( '' === self::get_code_from_name( $code_name ) ) {
					echo 'OK';
				} else {
					echo 'ERR-Exists';
				}
			} else {
				echo 'ERR-Wrong arguments';
			}

			wp_die();
		}

		/**
		 * Sends header to browser (allows content type changes)
		 *
		 * @since   1.0.0
		 */
		protected static function header_no_cache( $content_type = 'text/plain' ) {
			if ( ob_get_length() ) {
				// Clear buffer to prevent errors (not 100% proof)
				ob_clean();
			}

			if ( isset( $_REQUEST['code_manager_content_type'] ) ) {
				// Check if action is allowed
				$wp_nonce = isset( $_REQUEST['wpnonce_content_type'] ) ?
					sanitize_text_field( wp_unslash( $_REQUEST['wpnonce_content_type'] ) ) : ''; // input var okay.
				if ( wp_verify_nonce( $wp_nonce, 'code_manager_content_type' ) ) {
					$content_type =
						sanitize_text_field( wp_unslash( $_REQUEST['code_manager_content_type'] ) ); // input var okay.
				}
			}

			header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Content-Type: {$content_type}; charset=utf-8");
		}

	}

}