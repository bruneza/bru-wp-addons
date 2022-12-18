<?php

/**
 * Plugin Name: BRUNEZA WP Addons
 * Plugin URI: https://bruneza.online/
 * Description: Additional Features to make the website more Amazing with less plugins.
 * Version:     1.3.1
 * Author: Bruce Mugwaneza
 * Author URI: https://bruneza.online/
 * Text Domain: bruneza
 * 
 * WordPress tested up to: 6.0.1
 * Elementor tested up to: 3.7.0
 * Elementor Pro tested up to: 3.7.0
 */


if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

function BRU_Addon()
{

	$version = '1.3.1';

	// Define COnstanst
	if ( is_admin() ) {
		if( !function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$var = get_plugin_data(__FILE__);

		$version = $var['Version'];
	}
	
	define('VERSION', $version);
	define('MINIMUM_PHP_VERSION', '7.4');
	define('MINIMUM_ELEMENTOR_VERSION', '3.6');
	define('BRU_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
	define('BRU_FILE', __FILE__);
	define('BRU_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
	define('BRU_BASENAME', plugin_basename(__FILE__));
	define('BRU_ASSETS', BRU_URL . '/assets/');
	
	// Load plugin file
	require_once(BRU_DIR . '/includes/plugin.php');
	
	// Run the plugin
	\BRU_Addons\Features::instance();
	
	// Run Git Updater Class
	require_once(BRU_DIR . '/includes/brxxx-plugin-updater.php');
	
	// Include our updater file
	$updater = new \BRU_Addons\Updater(__FILE__); // instantiate our class
	$updater->set_username('bruneza'); // set username
	$updater->set_repository('bru-wp-addons'); // set repo
	$updater->authorize('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'); // set Tokken
	$updater->initialize(); // initialize the updater

}
add_action('plugins_loaded', 'BRU_Addon');
