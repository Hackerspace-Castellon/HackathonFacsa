<?php

use Code_Manager\Code_Manager_Model;

/**
 * Class Code_Manager_Switch
 *
 * Switch to:
 * + activate plugin {@see Code_Manager_Switch::activate()}
 * + deactive plugin {@see Code_Manager_Switch::deactivate()}
 *
 * @author  Peter Schulz
 * @since   1.0.0
 *
 * @see Code_Manager_Switch::activate()
 * @see Code_Manager_Switch::deactivate()
 */
class Code_Manager_Switch {

	/**
	 * Activate Code Manager
	 *
	 * The user must have the appropriate privileges to perform this operation.
	 *
	 * For single site installation {@see Code_Manager_Switch::activate_blog()} will be called. For multi site
	 * installations {@see Code_Manager_Switch::activate_blog()} must be called for every blog.
	 *
	 * IMPORTANT!!!
	 *
	 * For blogs installed on multi site installations after activation of the plugin, activation of the plugin for
	 * that blog will not be performed if the plugin is network activated. In that case the admin user of the blog
	 * will receive a message when viewing a plugin page with an option to follow these steps manually.
	 *
	 * @since   1.0.0
	 *
	 * @see Code_Manager_Switch::activate_blog()
	 */
	public static function activate() {
		if ( current_user_can( 'activate_plugins' ) ) {
			// Activate plugin.
			if ( is_multisite() ) {
				global $wpdb;
				// Multisite installation.
				$blogids = $wpdb->get_col( "select blog_id from $wpdb->blogs" ); // db call ok; no-cache ok.
				foreach ( $blogids as $blog_id ) {
					// Uninstall blog.
					switch_to_blog( $blog_id );
					self::activate_blog();
					restore_current_blog();
				}
			} else {
				// Single site installation.
				self::activate_blog();
			}
		}
	}

	/**
	 * Activate blog
	 *
	 * The user must have the appropriate privileges to perform this operation.
	 *
	 * Creates plugin table (if not found) and updates plugin version. This action is performed on the
	 * 'active WordPress blog'. On single site there is only one blog. On multisite installations it must be
	 * executed for every blog.
	 *
	 * @since   1.0.0
	 */
	protected static function activate_blog() {
		if ( current_user_can( 'activate_plugins' ) ) {
			if ( CODE_MANAGER_VERSION !== get_option( 'code_manager_version' ) ) {
				$code_manager_model       = new Code_Manager_Model();
				if ( ! $code_manager_model::table_exists() ) {
					global $wpdb;

					$create_table =
						"CREATE TABLE {wp_prefix}code_manager
						( code_id mediumint(9) NOT NULL AUTO_INCREMENT
						, code_name varchar(100) NOT NULL
						, code_type varchar(30) NOT NULL
						, code_enabled tinyint(1) NOT NULL DEFAULT 0
						, code text
						, code_author varchar(100)
						, code_description text
						, PRIMARY KEY (code_id)
						, UNIQUE KEY (code_name)
						)";
					$wpdb->query( str_replace( '{wp_prefix}', $wpdb->prefix, $create_table ) );
				}

				update_option( 'code_manager_version', CODE_MANAGER_VERSION );
			}
		}
	}

	/**
	 * Deactivate plugin WP Data Access
	 *
	 * On deactivation we leave the repository and options as they are in case the user wants to reactivate the
	 * plugin later again. Tables and options are deleted when the plugin is uninstalled.
	 *
	 * @since   1.0.0
	 */
	public static function deactivate() {
		// Add future deactivation code here
	}

}
