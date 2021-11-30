<?php

namespace Code_Manager {

	/**
	 * Class Code_Manager_Import
	 *
	 * Add import feature to plugin (reached from list table).
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class Code_Manager_Import {

		public static function import() {
			if ( isset( $_FILES['filename'] ) &&
			     UPLOAD_ERR_OK === $_FILES['filename']['error']
			     && is_uploaded_file( $_FILES['filename']['tmp_name'] )
			) {
				// Get file content
				$import_file = new Code_Manager_Import_File( $_FILES['filename']['tmp_name'] );
				$import_file->import();
			} else {
				// File upload failed
				$msg = new Message_Box(
					[
						'message_text'           => __( 'File upload failed', 'code-manager' ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();
			}
		}

	}

}