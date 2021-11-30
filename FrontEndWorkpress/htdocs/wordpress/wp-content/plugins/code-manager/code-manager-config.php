<?php

/**
 * @author  Peter Schulz
 * @since   1.0.0
 *
 * This configuration file is used to define globally used variables in a central location.
 */
// Disable code manager

if ( !defined( 'CODE_MANAGER_DISABLED' ) ) {
    define( 'CODE_MANAGER_DISABLED', false );
    // true = disable
}

// Plugin settings
if ( !defined( 'CODE_MANAGER_HELP_URL' ) ) {
    define( 'CODE_MANAGER_HELP_URL', 'https://code-manager.com/blog/docs/index/getting-started/read-this-first/' );
}
if ( !defined( 'CODE_MANAGER_VERSION' ) ) {
    define( 'CODE_MANAGER_VERSION', '1.0.10' );
}
// Code Manager classes
if ( !defined( 'CODE_MANAGER_CLASS' ) ) {
    define( 'CODE_MANAGER_CLASS', 'Code_Manager\\Code_Manager' );
}
if ( !defined( 'CODE_MANAGER_MODEL_CLASS' ) ) {
    define( 'CODE_MANAGER_MODEL_CLASS', 'Code_Manager\\Code_Manager_Model' );
}
if ( !defined( 'CODE_MANAGER_LIST_CLASS' ) ) {
    define( 'CODE_MANAGER_LIST_CLASS', 'Code_Manager\\Code_Manager_List' );
}
if ( !defined( 'CODE_MANAGER_FORM_CLASS' ) ) {
    define( 'CODE_MANAGER_FORM_CLASS', 'Code_Manager\\Code_Manager_Form' );
}
if ( !defined( 'CODE_MANAGER_TAB_CLASS' ) ) {
    define( 'CODE_MANAGER_TAB_CLASS', 'Code_Manager\\Code_Manager_Tabs' );
}
if ( !defined( 'CODE_MANAGER_MAIN_CLASS' ) ) {
    define( 'CODE_MANAGER_MAIN_CLASS', 'Code_Manager\\Code_Manager_List_View' );
}
if ( !defined( 'CODE_MANAGER_SETTINGS_CLASS' ) ) {
    define( 'CODE_MANAGER_SETTINGS_CLASS', 'Code_Manager\\Code_Manager_Settings' );
}
// Code Manager shortcode settings
if ( !defined( 'CODE_MANAGER_SHORT_CODE' ) ) {
    define( 'CODE_MANAGER_SHORT_CODE', 'cmruncode' );
}
// Code Manager page (list table, data entry page and tab mode)
if ( !defined( 'CODE_MANAGER_MENU_SLUG' ) ) {
    define( 'CODE_MANAGER_MENU_SLUG', 'code_manager' );
}
if ( !defined( 'CODE_MANAGER_PAGE_TITLE' ) ) {
    define( 'CODE_MANAGER_PAGE_TITLE', 'Code Manager' );
}
if ( !defined( 'CODE_MANAGER_MENU_TITLE' ) ) {
    define( 'CODE_MANAGER_MENU_TITLE', 'Code Manager' );
}
if ( !defined( 'CODE_MANAGER_H1_TITLE' ) ) {
    define( 'CODE_MANAGER_H1_TITLE', 'Code Manager' );
}
// Code Manager settings page
if ( !defined( 'CODE_MANAGER_SETTINGS_MENU_SLUG' ) ) {
    define( 'CODE_MANAGER_SETTINGS_MENU_SLUG', 'code_manager_settings' );
}
if ( !defined( 'CODE_MANAGER_SETTINGS_PAGE_TITLE' ) ) {
    define( 'CODE_MANAGER_SETTINGS_PAGE_TITLE', 'Code Manager' );
}
if ( !defined( 'CODE_MANAGER_SETTINGS_MENU_TITLE' ) ) {
    define( 'CODE_MANAGER_SETTINGS_MENU_TITLE', 'Code Manager' );
}
if ( !defined( 'CODE_MANAGER_SETTINGS_H1_TITLE' ) ) {
    define( 'CODE_MANAGER_SETTINGS_H1_TITLE', 'Code Manager Settings' );
}
// Other Code Manager settings
if ( !defined( 'CODE_MANAGER_COOKIES_SEARCH' ) ) {
    define( 'CODE_MANAGER_COOKIES_SEARCH', 'Code_Manager_List_Table_Search' );
}
if ( !defined( 'CODE_MANAGER_COOKIES_LIST' ) ) {
    define( 'CODE_MANAGER_COOKIES_LIST', 'Code_Manager_List_Table_List' );
}
if ( !defined( 'CODE_MANAGER_SEARCH_ITEM_NAME' ) ) {
    define( 'CODE_MANAGER_SEARCH_ITEM_NAME', 'code_manager_search_item' );
}
if ( !defined( 'CODE_MANAGER_LIST_ITEM_NAME' ) ) {
    define( 'CODE_MANAGER_LIST_ITEM_NAME', 'selected_code_type' );
}