<?php
/*
Plugin Name: WP US Cities
Description: Text field with autocomplete and slug URL to US cities
Version:     0.1
Author:      Edgar Eler
Author URI:  http://edgar.systems
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

require_once(dirname( __FILE__ ) . '/includes/functions.php');

register_activation_hook( __FILE__, 'wp_us_cities_install' );
register_activation_hook( __FILE__, 'wp_us_cities_install_data' );

register_deactivation_hook( __FILE__, 'wp_us_cities_deactivation' );

register_uninstall_hook( __FILE__, 'wp_us_cities_uninstall' );

add_filter('rewrite_rules_array', 'wp_us_cities_create_rewrite_rules');
add_filter('query_vars', 'wp_us_cities_add_query_vars');

add_filter('admin_init', 'wp_us_cities_flush_rewrite_rules');

add_action( 'template_redirect', 'wp_us_cities_template_redirect_intercept' );

add_action( 'wp_enqueue_scripts', 'wp_us_cities_scripts' );