<?php

namespace Code_Manager {

	/**
	 * Class Code_Manager_List_View
	 *
	 * Implements a layer between the menu and the list table/data entry form.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class Code_Manager_List_View {

		/**
		 * Page hook suffix
		 *
		 * @var object|boolean Reference to (sub) menu or false
		 */
		protected $page_hook_suffix;

		/**
		 * Action (taken from $_REQUEST)
		 *
		 * @var string
		 */
		protected $action;

		/**
		 * Code_Manager_List_View constructor
		 *
		 * page_hook_suffix
		 *
		 * @param array $args [
		 *     'page_hook_suffix' => (string|boolean) Page hook suffix or false (default = false)
		 * ]
		 */
		public function __construct( $args = [] ) {
			$this->action =
				isset( $_REQUEST['action'] ) ?
					sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) :
					''; // input var okay.

			if ( 'new' !== $this->action && 'edit' !== $this->action ) {
				$this->page_hook_suffix = isset( $args['page_hook_suffix'] ) ? $args['page_hook_suffix'] : false;
				if ( false !== $this->page_hook_suffix ) {
					add_action( 'load-' . $this->page_hook_suffix, [ $this, 'page_screen_options' ] );
				}
			}
		}

		/**
		 * Show the list table or data entry form
		 *
		 * Depending on the value of argument action:
		 * (1) The list table is shown when no actions is defined
		 * (2) The data entry form is shown for actions new and edit
		 *
		 * @since   1.0.0
		 */
		public function show() {
			switch ( $this->action ) {
				case 'new':
				case 'edit':
					$this->display_edit_form();
					break;
				default:
					$this->display_list_table();
			}
		}

		/**
		 * Show data entry form
		 *
		 * @since   1.0.0
		 */
		public function display_edit_form() {
			$formclass = CODE_MANAGER_FORM_CLASS;
			$form      = new $formclass();
			$form->show();
		}

		/**
		 * Show list table
		 *
		 * @since   1.0.0
		 */
		public function display_list_table() {
			$listclass = CODE_MANAGER_LIST_CLASS;
			$list      = new $listclass();
			$list->show();
		}

		/**
		 * Prepare screen options
		 *
		 * @since   1.0.0
		 */
		public function page_screen_options() {
			if ( is_admin() ) {
				add_filter('set-screen-option', [ $this, 'set_screen_option' ], 10, 3);

				set_screen_options();
				$screen = get_current_screen();

				if ( is_object( $screen ) && $screen->id === $this->page_hook_suffix ) {
					// Add column selection
					$hidden = get_user_meta(
						get_current_user_id(),
						'manage' . get_current_screen()->id . 'columnshidden'
					);

					if ( 0 === sizeof( $hidden ) ) {
						add_user_meta(
							get_current_user_id(),
							'manage' . get_current_screen()->id . 'columnshidden',
							[ 0 => 'code_author', 1 => 'code_description' ]
						);
					}

					add_filter(
						'manage_' . get_current_screen()->id . '_columns',
						[ Code_Manager_List::class, 'get_column_labels_default' ],
						0
					);

					// Add pagination
					$pagination = 10;
					$args       = [
						'label'   => __( 'Number of items per page', 'code-manager' ),
						'default' => $pagination,
						'option'  => 'code_manager_rows_per_page',
					];
					add_screen_option( 'per_page', $args );
				}
			}
		}

		function set_screen_option($status, $option, $value) {
			if ( 'code_manager_rows_per_page' == $option ) {
				return $value;
			}

			return $status;
		}

	}

}