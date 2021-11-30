<?php

namespace Code_Manager {

	/**
	 * Class Code_Manager_Import_File
	 *
	 * Performs import, called from Code_Manager_Import.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class Code_Manager_Import_File {

		protected $file_pointer;

		public function __construct( $file_path ) {
			$this->file_pointer = fopen( $file_path, 'rb' );
		}

		public function __destruct() {
			fclose( $this->file_pointer );
		}

		public function import() {
			global $wpdb;

			$suppress    = $wpdb->suppress_errors();
			$rows        = 0;
			$rows_failed = 0;

			if ( false !== $this->file_pointer ) {
				while ( ! feof( $this->file_pointer ) ) {
					$sql = fgets( $this->file_pointer );
					if ( '--' !== substr( $sql, 0, 2 ) ) {
						if ( ";\n" === substr( $sql, -2 ) ) {
							$rows ++;
							// Write file content to array for security check
							$dml_check = explode( ' ', substr( trim( $sql ), 0, 150 ) );
							if ( ! isset( $dml_check[0] ) || ! isset( $dml_check[1] ) ) {
								// No content.
								$rows_failed ++;
							} else {
								// Check first two words (must be insert into, no other statements allowed)
								if ( strtolower( $dml_check[0] . $dml_check[1] ) !== 'insertinto' ) {
									// Only insert allowed
									$rows_failed ++;
								} else {
									// Check table name (only insert into code manager table allowed)
									if ( ! stristr( $dml_check[2], '{wp_prefix}' . Code_Manager_Model::BASE_TABLE_NAME ) ) {
										$rows_failed ++;
									} else {
										// Insert row
										if ( false === $wpdb->query( str_replace( '{wp_prefix}', $wpdb->prefix, $sql ) ) ) {
											$rows_failed ++;
										}
									}
								}
							}
						}
					}
				}
			}

			$wpdb->suppress_errors( $suppress );

			$msg = "Imported " . ( $rows - $rows_failed ) . " rows";
			if ( $rows_failed > 0 ) {
				$msg .= " ($rows_failed failed).";
			} else {
				$msg .= ".";
			}
			$msg = new Message_Box(
				[
					'message_text' => $msg,
				]
			);
			$msg->box();
		}

	}

}