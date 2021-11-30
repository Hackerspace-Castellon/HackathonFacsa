<?php

namespace Code_Manager {

	/**
	 * Class Code_Manager_Export
	 *
	 * Add export feature to plugin (reached from list table).
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class Code_Manager_Export {

		public static function export() {
			$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : ''; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, 'code-manager-export' . Code_manager::get_current_user_login() ) ) {
				wp_die( __( 'ERROR: Not authorized', 'code-manager' ) );
			}

			if ( isset( $_REQUEST['cid'] ) && is_array( $_REQUEST['cid'] ) ) {
				$valid_ids = [];
				foreach ( $_REQUEST['cid'] as $code_id ) {
					if ( is_numeric( $code_id ) ) {
						$valid_ids[] = $code_id;
					}
				}
				self::export_rows( implode( ',', $valid_ids ) );
			}
		}

		public static function export_ajax() {
			self::export();
			wp_die();
		}

		protected static function export_rows( $code_ids ) {
			if ( defined( 'WP_MAX_MEMORY_LIMIT' ) ) {
				$wp_memory_limit      = WP_MAX_MEMORY_LIMIT;
				$current_memory_limit = @ini_set( 'memory_limit' );
				if ( false === $current_memory_limit ||
				     self::convert_memory_to_decimal( $current_memory_limit ) <
				     self::convert_memory_to_decimal( $wp_memory_limit )
				) {
					@ini_set( 'memory_limit', $wp_memory_limit );
				}
			}

			header( 'Content-type: text/plain; charset=utf-8' );
			header( "Content-Disposition: attachment; filename=code_manager_export.sql" );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			echo "--\n";
			echo "-- Code Manager table export\n";
			echo "-- Code IDs are not exported. New IDs are generated on import.\n";
			echo "--\n";

			global $wpdb;
			$query = 'select * from ' . Code_Manager_Model::get_base_table_name() . ' ' .
			         "where code_id in ({$code_ids})";
			$rows  = $wpdb->get_results( $query, 'ARRAY_A' );

			foreach ( $rows as $row ) {
				$table_name       = Code_Manager_Model::BASE_TABLE_NAME;
				$code             = str_replace( "\t", "\\t", $wpdb->remove_placeholder_escape( esc_sql( $row['code'] ) ) );
				$code_description = str_replace( "\t", "\\t", $wpdb->remove_placeholder_escape( esc_sql( $row['code_description'] ) ) );
				$insert     =
					"insert into {wp_prefix}{$table_name} " .
					"(code_name, code_type, code, code_author, code_description) " .
					"values " .
					"('{$row['code_name']}','{$row['code_type']}','{$code}','{$row['code_author']}','{$code_description}');\n";

				echo $insert;
			}
		}

		public static function convert_memory_to_decimal( $memory_value ) {
			if ( preg_match( '/^(\d+)(.)$/', $memory_value, $matches ) ) {
				if ( $matches[2] == 'G' ) {
					return $matches[1] * 1024 * 1024 * 1024;
				} else if ( $matches[2] == 'M' ) {
					return $matches[1] * 1024 * 1024;
				} else if ( $matches[2] == 'K' ) {
					return $matches[1] * 1024;
				}
			}
		}

	}

}