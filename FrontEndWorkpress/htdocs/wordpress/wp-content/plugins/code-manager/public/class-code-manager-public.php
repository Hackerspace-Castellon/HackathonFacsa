<?php

use Code_Manager\Code_Manager;

/**
 * Class Code_Manager_Public
 *
 * Defines public specific functionality for the Code Manager.
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
class Code_Manager_Public {

	/**
	 * Add stylesheets to front-end
	 *
	 * @since   1.0.0
	 */
	public function enqueue_styles() {
		// Future CSS stylesheets can be added here
	}

	/**
	 * Add scripts to front-end
	 *
	 * @since   1.0.0
	 */
	public function enqueue_scripts() {
		// Future JavaScript code can be added here
	}

	/**
	 * Register Code Manager shortcode
	 *
	 * @since   1.0.0
	 */
	public function register_shortcodes() {
		$code_manager = new Code_Manager();
		add_shortcode( CODE_MANAGER_SHORT_CODE, [ $code_manager, 'add_shortcode' ] );
	}

}
