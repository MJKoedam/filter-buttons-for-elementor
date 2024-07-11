<?php
/**
 * Plugin Name: Button Filters for Elementor
 * Description: A custom Elementor plugin that adds extra attributes to button elements based on ACF settings.
 * Version: 1.0.0
 * Author: Mart-Jan Koedam
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include the main plugin class
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-elementor-custom-button-attribute.php' );

// Instantiate the plugin class
function elementor_custom_button_attribute_init() {
    new Elementor_Custom_Button_Attribute();
}
add_action( 'elementor/init', 'elementor_custom_button_attribute_init' );
