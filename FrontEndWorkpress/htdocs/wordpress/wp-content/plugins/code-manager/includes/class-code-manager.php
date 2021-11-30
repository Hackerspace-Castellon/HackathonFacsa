<?php

/**
 * Class Code_Manager
 *
 * Core plugin class used to define:
 * + admin specific functionality {@see Code_Manager_Admin}
 * + public specific functionality {@see Code_Manager_Public}
 * + internationalization {@see Code_Manager_i18n}
 * + plugin activation and deactivation {@see Code_Manager_Loader}
 *
 * @author  Peter Schulz
 * @since   1.0.0
 *
 * @see Code_Manager_Admin
 * @see Code_Manager_Public
 * @see Code_Manager_i18n
 * @see Code_Manager_Loader
 */
class Code_Manager {

	/**
	 * Handle to loader object
	 *
	 * @var Code_Manager_Loader
	 */
	protected $loader;

	/**
	 * Menu slug of current page
	 *
	 * @var string
	 */
	protected $page;

	/**
	 * Code_Manager constructor
	 *
	 * Calls method the following methods to setup plugin:
	 * + {@see Code_Manager::load_dependencies()}
	 * + {@see Code_Manager::set_locale()}
	 * + {@see Code_Manager::define_admin_hooks()}
	 * + {@see Code_Manager::define_public_hooks()}
	 *
	 * @since   1.0.0
	 *
	 * @see Code_Manager::load_dependencies()
	 * @see Code_Manager::set_locale()
	 * @see Code_Manager::define_admin_hooks()
	 * @see Code_Manager::define_public_hooks()
	 */
	public function __construct() {
		if ( isset( $_REQUEST['page'] ) ) {
			$this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // input var okay.
		}

		$this->load_dependencies();
		$this->set_locale();

		// Handle plugin cookies (handle before any response is send)
		$this->loader->add_action( 'admin_init', $this, 'handle_plugin_cookies' );

		// Add Code Manager specific actions
		$code_manager_class = CODE_MANAGER_CLASS;
		$code_manager       = new $code_manager_class();
		$code_manager->add_actions( $this->loader );

		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load required dependencies
	 *
	 * Loads required plugin files and initiates the plugin loader.
	 *
	 * @since   1.0.0
	 *
	 * @see Code_Manager_Loader
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-code-manager-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-code-manager-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-code-manager-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-code-manager-public.php';

		$this->loader = new Code_Manager_Loader();
	}

	/**
	 * Set locale for internationalization
	 *
	 * @since   1.0.0
	 *
	 * @see Code_Manager_i18n
	 */
	private function set_locale() {
		$code_manager_i18n = new Code_Manager_i18n();
		$this->loader->add_action( 'init', $code_manager_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Add admin hooks
	 *
	 * Initiates {@see Code_Manager_Admin} (admin functionality).
	 * Adds admin actions to loader (see below).
	 *
	 * @since   1.0.0
	 *
	 * @see Code_Manager_Admin
	 */
	private function define_admin_hooks() {
		if ( is_admin() && current_user_can( 'manage_options' ) ) {
			$plugin_admin = new Code_Manager_Admin();

			// Add plugin settings page
			$this->loader->add_action('admin_menu', $plugin_admin, 'code_manager_register_settings_page');

			// Admin plugin menu
			$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_items' );
			$this->loader->add_filter( 'submenu_file', $plugin_admin, 'submenu_filter' );

			// Add admin styles and scripts
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			$this->loader->add_action( 'in_admin_header', $plugin_admin, 'user_admin_notices' );

		}
	}

	/**
	 * Add public hooks
	 *
	 * Initiates {@see Code_Manager_Public} (public functionality).
	 *
	 * @since   1.0.0
	 *
	 * @see Code_Manager_Public
	 * @see Code_Manager
	 */
	private function define_public_hooks() {
		if ( ! is_admin() ) {
			// Shortcodes
			$plugin_public = new Code_Manager_Public();
			$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );

			// Public scripts
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		}
	}

	/**
	 * Start plugin loader
	 *
	 * @since   1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Handle plugin cookies to remember list table search value and selected code type
	 *
	 * @since   1.0.0
	 */
	public function handle_plugin_cookies() {
		if ( $this->page === CODE_MANAGER_MENU_SLUG ) {
			if ( isset( $_REQUEST[ CODE_MANAGER_SEARCH_ITEM_NAME ] ) ) {
				$search_value = sanitize_text_field( wp_unslash( $_REQUEST[ CODE_MANAGER_SEARCH_ITEM_NAME ] ) ); // input var okay.
				setcookie( CODE_MANAGER_COOKIES_SEARCH, $search_value, time() + 3600 );
			}

			if ( isset( $_REQUEST[ CODE_MANAGER_LIST_ITEM_NAME ] ) ) {
				$search_value = sanitize_text_field( wp_unslash( $_REQUEST[ CODE_MANAGER_LIST_ITEM_NAME ] ) ); // input var okay.
				setcookie( CODE_MANAGER_COOKIES_LIST, $search_value, time() + 3600 );
			}
		}
	}

}
