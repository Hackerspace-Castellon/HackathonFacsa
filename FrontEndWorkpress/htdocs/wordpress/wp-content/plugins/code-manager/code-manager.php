<?php

/**
 * Plugin Name:       Code Manager
 * Plugin URI:        https://code-manager.com/
 * Description:       Create, edit and organize PHP, JavaScript, CSS and HTML code from your WordPress dashboard.
 * Version:           1.0.10
 * Author:            Passionate Programmers
 * Author URI:        https://code-manager.com/
 * Text Domain:       code-manager
 * License:
 * License URI:
 * Domain Path:       /languages
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
if ( !defined( 'WPINC' ) ) {
    die;
}
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'code_manager_fs' ) ) {
    code_manager_fs()->set_basename( false, __FILE__ );
} else {
    // Load namespaces
    require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
    // Set plugin version
    // Needs to be defined before activation, deactivation and uninstall hooks
    if ( !defined( 'CODE_MANAGER_VERSION' ) ) {
        define( 'CODE_MANAGER_VERSION', '1.0.10' );
    }
    // Create a helper function for easy SDK access.
    function code_manager_fs()
    {
        global  $code_manager_fs ;
        
        if ( !isset( $code_manager_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $code_manager_fs = fs_dynamic_init( array(
                'id'             => '6331',
                'slug'           => 'code-manager',
                'type'           => 'plugin',
                'public_key'     => 'pk_7a949f640a340d0addeee391d19d6',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 14,
                'is_require_payment' => false,
            ),
                'menu'           => array(
                'slug'    => 'code_manager',
                'contact' => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $code_manager_fs;
    }
    
    // Init Freemius.
    code_manager_fs();
    // Signal that SDK was initiated.
    do_action( 'code_manager_fs_loaded' );
    /**
     * Activate plugin
     *
     * @author  Peter Schulz
     * @since   1.0.0
     */
    function activate_code_manager()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-code-manager-switch.php';
        Code_Manager_Switch::activate();
        register_uninstall_hook( __FILE__, 'code_manager_uninstall' );
    }
    
    register_activation_hook( __FILE__, 'activate_code_manager' );
    /**
     * Deactivate plugin
     *
     * @author  Peter Schulz
     * @since   1.0.0
     */
    function deactivate_code_manager()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-code-manager-switch.php';
        Code_Manager_Switch::deactivate();
    }
    
    register_deactivation_hook( __FILE__, 'deactivate_code_manager' );
    /**
     * Uninstall blog
     *
     * This functions is called when the plugin is uninstalled. The following actions are performed:
     * + Drop plugin tables (unless settings indicate not to)
     * + Delete plugin options from $wpdb->options (unless settings indicate not to)
     *
     * Actions are processed on the current blog and are repeated for every blog on a multisite installation. Must be
     * called from the dashboard (WP_UNINSTALL_PLUGIN defined). User must have the proper privileges (activate_plugins).
     *
     * @author      Peter Schulz
     * @since       1.0.0
     */
    function code_manager_uninstall_blog()
    {
        global  $wpdb ;
        $wpdb->suppress_errors( true );
        $drop_tables = get_option( 'code_manager_uninstall_tables' );
        
        if ( !$drop_tables || 'on' === $drop_tables ) {
            $drop_table = "drop table if exists {wp_prefix}code_manager;";
            $wpdb->query( str_replace( '{wp_prefix}', $wpdb->prefix, $drop_table ) );
        }
        
        $delete_options = get_option( 'code_manager_uninstall_options' );
        
        if ( !$delete_options || 'on' === $delete_options ) {
            // Delete Code Manager options
            $wpdb->query( "delete from {$wpdb->options} where option_name like 'code_manager_%'" );
            // db call ok; no-cache ok.
        }
    
    }
    
    /**
     * Uninstall plugin
     *
     * @author      Peter Schulz
     * @since       1.0.0
     */
    function code_manager_uninstall()
    {
        
        if ( is_multisite() ) {
            global  $wpdb ;
            // Uninstall plugin for all blogs one by one (will fail silently for blogs having no plugin tables/options)
            $blogids = $wpdb->get_col( "select blog_id from {$wpdb->blogs}" );
            // db call ok; no-cache ok.
            foreach ( $blogids as $blog_id ) {
                // Uninstall blog.
                switch_to_blog( $blog_id );
                code_manager_uninstall_blog();
                restore_current_blog();
            }
        } else {
            // Uninstall single site installation
            code_manager_uninstall_blog();
        }
        
        code_manager_fs()->add_action( 'after_uninstall', 'code_manager_fs_uninstall_cleanup' );
    }
    
    /**
     * Remove preview code ids on login
     *
     * @since   1.0.0
     */
    function wpda_remove_user_preview_codes_on_login( $user_login )
    {
        $user = get_user_by( 'login', $user_login );
        $user_id = $user->ID;
        delete_user_meta( $user_id, 'code_manager_preview_code_ids' );
    }
    
    add_action( 'wp_login', 'wpda_remove_user_preview_codes_on_login', 99 );
    /**
     * Remove preview code ids on logout
     *
     * @since   1.0.0
     */
    function wpda_remove_user_preview_codes_on_logout( $user_id )
    {
        delete_user_meta( $user_id, 'code_manager_preview_code_ids' );
    }
    
    add_action( 'wp_logout', 'wpda_remove_user_preview_codes_on_logout', 10 );
    // Send user to support page
    function cm_support_forum_url( $wp_org_support_forum_url )
    {
        if ( code_manager_fs()->is_premium() ) {
            // Use different support page for premium version
            return 'https://users.freemius.com/store/2612';
        }
        return 'https://wordpress.org/support/plugin/code-manager/';
    }
    
    code_manager_fs()->add_filter( 'support_forum_url', 'cm_support_forum_url' );
    // Add Code Manager icon to freemius
    function cm_freemius_icon()
    {
        return dirname( __FILE__ ) . '/freemius/assets/img/code-manager.png';
    }
    
    code_manager_fs()->add_filter( 'plugin_icon', 'cm_freemius_icon' );
    // Handle freemius menu items
    function cm_freemius_menu_visible( $is_visible, $submenu_id )
    {
        // support, account, contact, pricing
        if ( $submenu_id === 'contact' ) {
            $is_visible = false;
        }
        if ( code_manager_fs()->is_premium() && $submenu_id === 'contact' ) {
            $is_visible = true;
        }
        return $is_visible;
    }
    
    code_manager_fs()->add_filter(
        'is_submenu_visible',
        'cm_freemius_menu_visible',
        10,
        2
    );
    /**
     * Start plugin after loading all other installed and activated plugins to get access to their libraries
     *
     * @author  Peter Schulz
     * @since   1.0.0
     */
    function run_code_manager()
    {
        require_once plugin_dir_path( __FILE__ ) . 'code-manager-config.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-code-manager.php';
        $code_manager = new Code_Manager();
        $code_manager->run();
    }
    
    add_action( 'plugins_loaded', 'run_code_manager' );
}
