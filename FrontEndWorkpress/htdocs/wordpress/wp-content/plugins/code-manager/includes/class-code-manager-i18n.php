<?php

/**
 * Class Code_Manager_i18n
 *
 * Loads internationalization files
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
class Code_Manager_i18n {

	/**
	 * Load plugin internationalization files
	 *
	 * @since   1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'code-manager',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

}
