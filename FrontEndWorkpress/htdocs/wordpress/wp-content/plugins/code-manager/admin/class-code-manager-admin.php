<?php

use Code_Manager\Code_Manager_Dashboard;

/**
 * Class Code_Manager_Admin
 *
 * Defines admin specific functionality for the Code Manager.
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
class Code_Manager_Admin {

	/**
	 * Current page (menu slug)
	 *
	 * @var null|string
	 */
	protected $page = null;

	/**
	 * Tab mode:
	 * on > Code Manager works in tab mode (edit multiple codes simultaneously)
	 * off > Code Manager works in list mode (edit single codes + enable/disable code)
	 *
	 * @var string
	 */
	protected $tabmode = 'off';

	/**
	 * Handle to list view
	 *
	 * @var Code_Manager\Code_Manager_List_View
	 */
	protected $cm_list_view = null;

	/**
	 * Code_Manager_Admin constructor.
	 *
	 * Checks if WP Data Access is available to add additional features.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {
		if ( isset( $_REQUEST['page'] ) ) {
			$this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // input var okay.
		}

		if ( isset( $_REQUEST['tabmode'] ) ) {
			$this->tabmode = sanitize_text_field( wp_unslash( $_REQUEST['tabmode'] ) ); // input var okay.
		}
	}

	/**
	 * Add stylesheets to back-end
	 *
	 * Only added when Code Manager is visible. Adds minimal CSS depending on actual page (menu slug).
	 *
	 * @since   1.0.0
	 */
	public function enqueue_styles() {
		if ( CODE_MANAGER_MENU_SLUG === $this->page ) {
			wp_enqueue_style( 'wp-codemirror' );

			if ( isset( $_REQUEST['action'] ) &&
			     ( 'new' === $_REQUEST['action'] || 'edit' === $_REQUEST['action'] )
			) {
				// Code Manager data entry form
				wp_enqueue_style(
					'code_manager',
					plugins_url( '../assets/css/code_manager.css', __FILE__ ),
					[],
					CODE_MANAGER_VERSION
				);
			} elseif ( 'on' === $this->tabmode ) {
				// Code Manager tab mode
				wp_enqueue_style(
					'code_manager_tabmode',
					plugins_url( '../assets/css/code_manager_tabmode.css', __FILE__ ),
					[],
					CODE_MANAGER_VERSION
				);
			}

			$this->load_global_css();
		} elseif ( CODE_MANAGER_SETTINGS_MENU_SLUG === $this->page ) {
			// Code Manager list mode
			wp_enqueue_style(
				'code_manager_settings',
				plugins_url( '../assets/css/code_manager_settings.css', __FILE__ ),
				[],
				CODE_MANAGER_VERSION
			);

			$this->load_global_css();
		}
	}

	/**
	 * Adds CSS needed by all Code Manager pages
	 *
	 * @since   1.0.0
	 */
	protected function load_global_css() {
		// Dashboard CSS
		wp_enqueue_style(
			'code_manager_dashboard',
			plugins_url( '../assets/css/code_manager_dashboard.css', __FILE__ ),
			[],
			CODE_MANAGER_VERSION
		);

		// Material icons are used in page headers
		wp_enqueue_style(
			'code_manager_material_icons',
			plugins_url( '../assets/icons/material-icons.css', __FILE__ ),
			[],
			CODE_MANAGER_VERSION
		);

		// Global Code Manager styling
		wp_enqueue_style(
			'code_manager_global',
			plugins_url( '../assets/css/code_manager_global.css', __FILE__ ),
			[],
			CODE_MANAGER_VERSION
		);

		// Add tooltips to add Code Manager pages
		wp_enqueue_style(
			'code_manager_tooltip_css',
			plugins_url( '../assets/css/jquery-ui.min.css', __FILE__ ),
			[],
			CODE_MANAGER_VERSION
		);

		// Load fontawesome icons
		wp_enqueue_style(
			'cm_fontawesome_icons',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/fontawesome.min.css',
			[]
		);
		wp_enqueue_style(
			'cm_fontawesome_icons_solid',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/solid.min.css',
			[]
		);
	}

	/**
	 * Add scripts to back-end
	 *
	 * Only added when Code Manager is visible. Adds relevant JS only depending on action and tabmode.
	 *
	 * @since   1.0.0
	 */
	public function enqueue_scripts() {
		if ( CODE_MANAGER_MENU_SLUG === $this->page ) {
			$this->load_global_js();

			// Register codeEditor (PHP = default editor, can be overridden on user request)
			$cm_settings['codeEditor'] =
				wp_enqueue_code_editor(
					[
						'type'       => 'application/x-httpd-php',
						'codemirror' => [
							'lineNumbers'  => true,
							'autoRefresh'  => true,
							'mode'         => 'php',
							'lineWrapping' => true,
						],
					]
				);
			wp_enqueue_script( 'wp-theme-plugin-editor' );
			wp_localize_script( 'wp-theme-plugin-editor', 'cm_settings', $cm_settings );

			if (
				isset( $_REQUEST['action'] ) &&
			    ( 'new' === $_REQUEST['action'] || 'edit' === $_REQUEST['action'] )
			) {
				// Code Manager data entry form
				wp_enqueue_script(
					'code_manager',
					plugins_url( '../assets/js/code_manager.js', __FILE__ ),
					[],
					CODE_MANAGER_VERSION
				);
			} elseif ( 'on' === $this->tabmode ) {
				// Code Manager tab mode
				wp_enqueue_script(
					'code_manager_tabmode',
					plugins_url( '../assets/js/code_manager_tabmode.js', __FILE__ ),
					[],
					CODE_MANAGER_VERSION
				);
			} else {
				// Code Manager list mode
				wp_enqueue_script(
					'code_manager_listmode',
					plugins_url( '../assets/js/code_manager_listmode.js', __FILE__ ),
					[],
					CODE_MANAGER_VERSION
				);
			}

			// Enqueue clipboard
			wp_enqueue_script( 'clipboard' );

			// Register external library notify.js
			wp_enqueue_script(
				'code_manager_notify',
				plugins_url( '../assets/js/notify.min.js', __FILE__ ),
				[ 'jquery' ],
				CODE_MANAGER_VERSION
			);
		} elseif ( CODE_MANAGER_SETTINGS_MENU_SLUG === $this->page ) {
			$this->load_global_js();
		}
	}

	protected function load_global_js() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		// Dashboard JS
		wp_enqueue_script(
			'code_manager_dashboard',
			plugins_url( '../assets/js/code_manager_dashboard.js', __FILE__ ),
			[],
			CODE_MANAGER_VERSION
		);
	}

	/**
	 * Add plugin menus (only accessible to admin users)
	 *
	 * @since   1.0.0
	 */
	public function add_menu_items() {
		if ( current_user_can( 'manage_options' ) ) {
			// Check if code  manager table is available
			$code_manager_model_class = CODE_MANAGER_MODEL_CLASS;
			$code_manager_model       = new $code_manager_model_class();
			$code_manager_table_found = $code_manager_model::table_exists();

			if ( $code_manager_table_found ) {
				// Determine view mode
				$code_manager_page = 'on' === $this->tabmode ? 'code_manager_tab_mode' : 'code_manager_page';
			} else {
				// Show error page if plugin table is not found
				$code_manager_page = 'code_manager_page_not_found';
			}

			// Add top level menu
			add_menu_page(
				CODE_MANAGER_MENU_SLUG,
				CODE_MANAGER_MENU_TITLE,
				'manage_options',
				CODE_MANAGER_MENU_SLUG,
				null,
				'dashicons-editor-code',
				999999999
			);

			// Add Code Manager submenu
			$cm_list_menu = add_submenu_page(
				CODE_MANAGER_MENU_SLUG,
				CODE_MANAGER_PAGE_TITLE,
				CODE_MANAGER_MENU_TITLE,
				'manage_options',
				CODE_MANAGER_MENU_SLUG,
				[ $this, $code_manager_page ]
			);

			// Create list view to handle screen options
			if ( $this->page === CODE_MANAGER_MENU_SLUG && $code_manager_table_found && 'on' !== $this->tabmode ) {
				header( "X-XSS-Protection: 0" );
				$code_manager_main_class = CODE_MANAGER_MAIN_CLASS;
				$this->cm_list_view = new $code_manager_main_class(
					[
						'page_hook_suffix' => $cm_list_menu,
					]
				);
			}
		}
	}

	public function user_admin_notices() {
		if ( ( 'code_manager' === $this->page || 'code_manager_settings' === $this->page ) ) {
			$code_manager_plugin_hide_foreign_notices = get_option( 'code_manager_plugin_hide_foreign_notices' );
			if (
				false === $code_manager_plugin_hide_foreign_notices ||
				'on' === $code_manager_plugin_hide_foreign_notices
			) {
				remove_all_actions('admin_notices');
				remove_all_actions('all_admin_notices');
			}
		}
	}

	public function submenu_filter( $submenu_file ) {
		if ( ! Code_Manager_Dashboard::menu_enabled() ) {
			$hidden_submenus = [
				'code_manager-account',
				'code_manager-wp-support-forum',
				'code_manager-pricing',
				'code_manager-contact',
				'code_manager',
			];
		} else {
			$hidden_submenus = [];
		}

		foreach ( $hidden_submenus as $submenu ) {
			remove_submenu_page( 'code_manager', $submenu );
		}

		return $submenu_file;
	}

	/**
	 * Register settings page
	 *
	 * @since   1.0.0
	 */
	public function code_manager_register_settings_page() {
		add_options_page(
			CODE_MANAGER_SETTINGS_PAGE_TITLE,
			CODE_MANAGER_SETTINGS_MENU_TITLE,
			'manage_options',
			CODE_MANAGER_SETTINGS_MENU_SLUG,
			[
				$this,
				'code_manager_settings_page'
			]
		);
	}

	/**
	 * Show settings page
	 *
	 * @since   1.0.0
	 */
	public function code_manager_settings_page() {
		Code_Manager_Dashboard::add_dashboard();

		$code_manager_settings_class = CODE_MANAGER_SETTINGS_CLASS;
		$cm_settings = new $code_manager_settings_class();
		$cm_settings->show();
	}

	/**
	 * Show Code Manager in list mode
	 *
	 * @since   1.0.0
	 */
	public function code_manager_page() {
		Code_Manager_Dashboard::add_dashboard();

		$this->cm_list_view->show();
	}

	/**
	 * Show Code Manager in tab mode
	 *
	 * @since   1.0.0
	 */
	public function code_manager_tab_mode() {
		Code_Manager_Dashboard::add_dashboard();

		$code_manager_tab_class = CODE_MANAGER_TAB_CLASS;
		$tabmode = new $code_manager_tab_class();
		$tabmode->show();
	}

	/**
	 * Plugin table not found > show error page
	 *
	 * @since   1.0.0
	 */
	public function code_manager_page_not_found() {
		Code_Manager_Dashboard::add_dashboard();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<span><?php echo CODE_MANAGER_H1_TITLE; ?></span>
				<a href="<?php echo CODE_MANAGER_HELP_URL; ?>" target="_blank" title="Plugin Help - open a new tab or window">
					<span class="dashicons dashicons-editor-help"
						  style="text-decoration:none;vertical-align:top;font-size:36px;">
					</span></a>
			</h1>
			<p>
				<?php echo __( 'ERROR: Repository table not found!', 'code-manager' ); ?>
			</p>
		</div>
		<?php
	}
}
