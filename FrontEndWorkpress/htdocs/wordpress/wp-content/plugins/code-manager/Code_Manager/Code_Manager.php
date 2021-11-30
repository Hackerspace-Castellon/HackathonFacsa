<?php

namespace Code_Manager {

	/**
	 * Class Code_Manager
	 *
	 * Add plugin actions and runs the code saved in the code manager table.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class Code_Manager {

		/**
		 * Add Code Manager specific actions
		 *
		 * @since   1.0.0
		 */
		public function add_actions( $loader ) {
			$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
			$code_manager_model = new $code_manager_model_class();

			if ( is_admin() ) {
				// Admin actions
				$loader->add_action( 'admin_action_code_manager_export', Code_Manager_Export::class, 'export' );
				$loader->add_action( 'wp_ajax_code_manager_export', Code_Manager_Export::class, 'export_ajax' );
				$loader->add_action( 'wp_ajax_nopriv_code_manager_export', Code_Manager_Export::class, 'export_ajax' );

				$loader->add_action( 'wp_ajax_code_manager_update_code', $code_manager_model, 'update_code' );
				$loader->add_action( 'wp_ajax_code_manager_activate_code', $code_manager_model, 'activate_code' );
				$loader->add_action( 'wp_ajax_code_manager_activate_code_preview', $code_manager_model, 'activate_code_preview' );
				$loader->add_action( 'wp_ajax_code_manager_deactivate_code_preview', $code_manager_model, 'deactivate_code_preview' );
				$loader->add_action( 'wp_ajax_code_manager_reset_preview', $code_manager_model, 'reset_preview' );
				$loader->add_action( 'wp_ajax_code_manager_get_code_list', $code_manager_model, 'get_code_list' );
				$loader->add_action( 'wp_ajax_code_manager_code_name_exists', $code_manager_model, 'code_name_exists' );
				$loader->add_action( 'wp_ajax_code_manager_is_code_preview_enabled', $code_manager_model, 'is_code_preview_enabled' );

				$loader->add_action( 'wp_ajax_code_manager_get_code', $code_manager_model, 'get_code' );
				$loader->add_action( 'wp_ajax_nopriv_code_manager_get_code', $code_manager_model, 'get_code' );
			} else {
				// Public actions
			}
		}

		/**
		 * Run shortcode
		 *
		 * @since   1.0.0
		 *
		 * @var array
		 */
		public function add_shortcode( $atts ) {
			if ( self::code_manager_disabled() ) {
				// Code manager disabled
				return '';
			}

			global $pagenow;
			if ( $pagenow === 'post.php' || $pagenow === 'edit.php'  || $pagenow === 'post-new.php' ) {
				// Prevent errors on execution if shortcode is shown in classic editor
				return '';
			}

			if ( isset( $_SERVER["CONTENT_TYPE"] ) && 'application/json' === $_SERVER["CONTENT_TYPE"] ) {
				// Prevent errors on execution if shortcode is shown in Gutenberg editor
				return null;
			}

			global $wpda_shortcode_args;
			$wpda_shortcode_args = $atts; // Allow user to define and use custom parameters

			$atts    = array_change_key_case( (array) $atts, CASE_LOWER );
			$wp_atts = shortcode_atts(
				[
					'id'   => '',
					'name' => '',
				], $atts
			);

			if ( '' === $wp_atts['id'] && '' === $wp_atts['name'] ) {
				return '';
			}

			ob_start();

			$ids = explode( ',', $wp_atts['id'] );
			foreach ( $ids as $id ) {
				$this->run_shortcode_id( $id );
			}

			$names = explode( ',', $wp_atts['name'] );
			foreach ( $names as $name ) {
				$this->run_shortcode_name( $name );
			}

			$content = ob_get_contents();
			ob_end_clean();

			return $content;
		}

		protected function run_shortcode_id( $id ) {
			if ( '' !== $id ) {
				$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
				$code_manager_model       = new $code_manager_model_class();
				$code_row                 = $code_manager_model::dml_query( $id );
				if ( 1 === sizeof( $code_row ) ) {
					if (
						1 == $code_row[0]['code_enabled'] ||
						Code_Manager_Preview::is_code_id_preview_enabled( $id )
					) {
						$this->run_shortcode( $code_row[0]['code_type'], $code_row[0]['code'] );
					}
				}
			}
		}

		protected function run_shortcode_name( $name ) {
			if ( '' !== $name ) {
				$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
				$code_manager_model       = new $code_manager_model_class();
				$code_row                 = $code_manager_model::dml_query_by_name( $name );
				if ( 1 === sizeof( $code_row ) ) {
					if (
						1 == $code_row[0]['code_enabled'] ||
						Code_Manager_Preview::is_code_id_preview_enabled( $code_row[0]['code_id'] )
					) {
						$this->run_shortcode( $code_row[0]['code_type'], $code_row[0]['code'] );
					}
				}
			}
		}

		/**
		 * Adds code de pending on the code type
		 *
		 * @since   1.0.0
		 *
		 * @param string $code_type Code type (shortcodes only)
		 * @param string $code The code (PHP, JS, CSS or HTML)
		 */
		protected function run_shortcode( $code_type, $code ) {
			if ( strpos( $code_type, 'html' ) !== false ) {
				echo wp_unslash( $code );
			} elseif ( strpos( $code_type, 'css' ) !== false ) {
				echo '<style type="text/css">' . wp_unslash( $code ) . '</style>';
			} elseif ( strpos( $code_type, 'javascript' ) !== false ) {
				echo '<script type="text/javascript">' . wp_unslash( $code ) . '</script>';
			} elseif ( 'php shortcode' === $code_type) {
				$this->add_php_code( $code, false );
			}
		}

		public function run_shortcode_id_from_anywhere( $id ) {
			$this->run_shortcode_id( $id );
		}

		public function run_shortcode_name_from_anywhere( $name ) {
			$this->run_shortcode_name( $name );
		}

		/**
		 * Adds PHP code
		 *
		 * @since   1.0.0
		 *
		 * @param string $php_code PHP code to be added
		 * @param bool   $php7_required Indicates whether PHP7 is required for this code type
		 */
		protected function add_php_code( $php_code, $php7_required = true ) {
			if ( self::is_code_manager_page() ) {
				// Do not execute any code on Code Manager pages!!!
				// This is an admins rescue in case code fails.
			} else {
				eval( $this->strip_code( $php_code ) );
			}
		}

		/**
		 * Remove PHP opening and closing tags (when found) from given code
		 *
		 * @since   1.0.0
		 *
		 * @param string $php_code PHP source code
		 *
		 * @return string PHP code without PHP opening and closing tags
		 */
		protected function strip_code( $php_code ) {
			$php_code = rtrim( ltrim( $php_code ) );

			if ( '<?php' === strtolower( substr( $php_code, 0, 5 ) ) ) {
				$php_code = substr( $php_code, 5 );
			}

			if ( '?>' === substr( $php_code, strlen( $php_code ) - 2 ) ) {
				$php_code = substr( $php_code, 0, strlen( $php_code ) - 2 );
			}

			return $php_code;
		}

		/**
		 * Checks if Code Manager is disabled
		 *
		 * (1) Disabled in settings page
		 * (2) Disabled in config file
		 *
		 * @since   1.0.0
		 *
		 * @return bool TRUE - Code Manager is disabled
		 */
		public static function code_manager_disabled() {
			$plugin_code_execution = get_option('code_manager_plugin_code_execution');
			if ( false === $plugin_code_execution ) {
				$plugin_code_execution = 'on';
			}

			return 'on' !== $plugin_code_execution || ( defined( 'CODE_MANAGER_DISABLED' ) && CODE_MANAGER_DISABLED );
		}

		public static function is_code_manager_page() {
			return (
					is_admin() &&
					isset( $_REQUEST['page'] ) &&
					(
						CODE_MANAGER_MENU_SLUG === $_REQUEST['page'] ||
						CODE_MANAGER_SETTINGS_MENU_SLUG === $_REQUEST['page'] ||
						'code_manager_post' === $_REQUEST['page']
					)
			);
		}

		public static function get_current_user_login() {
			global $current_user;
			if ( isset( $current_user->user_login ) ) {
				return $current_user->user_login;
			} else {
				$wp_user = wp_get_current_user();
				if ( isset( $wp_user->data->user_login ) ) {
					return $wp_user->data->user_login;
				} else {
					return 'anonymous';
				}
			}
		}

	}

}
