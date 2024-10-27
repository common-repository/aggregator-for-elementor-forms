<?php
/**
 * Plugin Name:         Aggregator for Elementor forms
 * Description:         All your Elementor forms in one place. This powerful WordPress plugin compiles all forms used in Elementor into a convenient admin page, allowing you to easily edit notification emails.
 * Author:              Luis Zarza
 * Author URI:          https://www.luiszarza.com
 * Requires PHP:        8.0
 * Requires at least:   5.0
 * Version:             1.0.1
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package EFSE
 * @version 1.0
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if the Elementor pro is active. 
 * Throw an error if it isn't
 */
add_action('admin_init', 'efse_check_if_elementor_pro_active');
function efse_check_if_elementor_pro_active() {
    /**
     * Set vars.
     */
    $required_plugin_name = 'elementor-pro/elementor-pro.php';
    if (!is_plugin_active($required_plugin_name)) {
    	/**
		 * Since the plugin we need is not active,
         * We will rightaway deactivate our plugin.
         */
        deactivate_plugins(plugin_basename(__FILE__));
		/**
		 * Gives out a error to user as why this plugin can't be activated.
         */
        echo '<div class="error"><p>This plugin requires Elementor Pro to be active.</p></div>';
    }
}
/**
 * Define some constants for our use.
 */
define('EFSE_PLUGIN_URL',plugin_dir_url( __FILE__ ));
define('EFSE_PLUGIN_PATH',plugin_dir_path( __FILE__ ));
/**
 * Include the required files.
 */
require_once('includes/functions_forms-list.php');
/**
 * Add submenu page for our front-end.
 *
 * @return void.
 */
add_action( 'admin_menu', 'efse_add_menu_page', 100, 0);
function efse_add_menu_page() {
    /**
     * Adds a submenu page in elementor menu
     * This only makes sense as we are extending functionality
     */
    add_submenu_page(
        'elementor',
        'Elementor Forms',
        'All Forms',
        'manage_options',
        'efse-all-forms',
        'efse_all_forms_content'
    );
}
/**
 * Filters the array of row meta for each/specific plugin in the Plugins list table.
 * Appends additional links below each/specific plugin on the plugins page.
 *
 * @access  public
 * @param   array       $links_array            An array of the plugin's metadata
 * @param   string      $plugin_file_name       Path to the plugin file
 * @return  array       $links_array
 */
add_filter( 'plugin_action_links', 'efse_prefix_append_plugin_row_links', 10, 2 );
function efse_prefix_append_plugin_row_links( $links_array, $plugin_file_name) {
	if ( strpos( $plugin_file_name, basename(__FILE__) ) ) {
        /**
         * Add link to all forms in plugin row. 
         */
        $new_link = '<a href="'.admin_url('admin.php?page=efse-all-forms').'">All forms</a>';
        array_unshift($links_array, $new_link);
	}
 
	return $links_array;
}
/**
 * Content callback function for our elementor sub page.
 *
 * @return void.
 */
function efse_all_forms_content() {
    /**
     * Include the template content file.
     * The content is to large to be displayed directly.
     */
    include(EFSE_PLUGIN_PATH.'/includes/temp/content_forms-list.php');
}
/**
 * Enqueue our scripts and styles in menu page.
 * @param string $slug. The slug of the current page.
 *
 * @return void.
 */
add_action('admin_enqueue_scripts', 'cfse_list_add_scripts_styles');
function cfse_list_add_scripts_styles($slug) {
    /**
     * Early exit if not our page.
     */
    if ($slug !== 'elementor_page_efse-all-forms') {
        return;
    }

    #Set vars
    $version = 1.0;

    #Styles.
    wp_enqueue_style('cfse-style-forms-list', EFSE_PLUGIN_URL.'assets/css/forms-list-style.css', null, $version);
    wp_enqueue_style('cfse-style-forms-list', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap', null, $version);

    #Scrtips

    wp_enqueue_script('cfse-functions-forms-list', EFSE_PLUGIN_URL.'assets/js/forms-list-functions.js', null, $version);
    wp_localize_script('cfse-functions-forms-list', 'cfseVars', [
        'ajaxURL' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce('cfse-form-editor-nonce')
    ]);
}
?>