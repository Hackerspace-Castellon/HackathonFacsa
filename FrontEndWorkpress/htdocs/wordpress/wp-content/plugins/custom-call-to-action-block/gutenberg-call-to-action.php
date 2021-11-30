<?php
/**
* Plugin Name: Custom Call to Action Block
* Plugin URI: https://github.com/BRdhanani/gutenberg-call-to-action
* Author: Brijesh Dhanani
* Author URI: https://github.com/BRdhanani
* Version: 1.0.0
* License: GPL2+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
* Description: Custom Call to Action Gutenberg Block
*/

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the block's assets for the editor.
 *
 * wp-blocks:  The registerBlockType() function to register blocks.
 * wp-element: The wp.element.createElement() function to create elements.
 *
 * @since 1.0.0
 */

function call_to_action_font_size()
{
    add_theme_support(
        'editor-font-sizes',
        array(
            array(
                'name' => 'Normal',
                'slug' => 'normal',
                'size' => 16
            ),
            array(
                'name' => 'Large',
                'slug' => 'large',
                'size' => 24
            )
        )
    );
}
add_action( 'init', 'call_to_action_font_size' );

function call_to_action(){
	wp_register_script( 
		'index-js',
		plugins_url( '/build/index.js', __FILE__ ),
		array( 'wp-blocks', 'wp-editor', 'wp-components' )
	);
    register_block_type( 'gutenberg-call-to-action/call-to-action-block', array(
        'editor_script' => 'index-js'
    ) );
}
add_action('init','call_to_action');