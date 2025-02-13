<?php
/*
 *
 * Plugin Name:       Load HTML Files
 * Plugin URI:        https://fullworksplugins.com/products/load-html-files/
 * Description:       Autocreate posts from HTML files
 * Version:           1.1
 * Author:            Alan
 * Author URI:        https://fullworksplugins.com/
 * Requires at least: 4.9
 * Requires PHP:      7.4
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       load-html-files
 * Domain Path:       /languages
 *
 * @package load-html-files
 *
 */

namespace Load_HTML_Files;


// If this file is called directly, abort.
use Fullworks_WP_Autoloader\AutoloaderPlugin;
use Load_HTML_Files\Control\Plugin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'Load_HTML_Files\run_Load_HTML_Files' ) ) {
	define( 'LOAD_HTML_FILES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'LOAD_HTML_FILES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'LOAD_HTML_FILES_CONTENT_DIR', dirname( plugin_dir_path( __DIR__ ) ) );
	define( 'LOAD_HTML_FILES_PLUGINS_TOP_DIR', plugin_dir_path( __DIR__ ) );
	define( 'LOAD_HTML_FILES_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	define( 'LOAD_HTML_FILES_PLUGIN_VERSION', '1.1' );

//	require_once LOAD_HTML_FILES_PLUGIN_DIR . 'control/autoloader.php';
	require_once LOAD_HTML_FILES_PLUGIN_DIR . 'vendor/autoload.php';
	new AutoloaderPlugin(__NAMESPACE__, __DIR__);

	function run_load_html_files() {

		register_activation_hook( __FILE__, array( '\Load_HTML_Files\Control\Activator', 'activate' ) );
		register_deactivation_hook( __FILE__, array( '\Load_HTML_Files\Control\Deactivator', 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( '\Load_HTML_Files\Control\Uninstall', 'uninstall' ) );
		new \Fullworks_Free_Plugin_Lib\Main('load-html-files/load-html-files.php',
			admin_url( 'edit.php?post_type=html_files&page=load-html-files-settings' ),
			'LHF-Free',
			'html_files_page_load-html-files-settings',
		'Load HTML Files');

		$plugin = new Plugin( );
		$plugin->run();
	}


	run_load_html_files();
} else {
	die( esc_html__( 'Cannot execute as the plugin already exists, if you have a another version installed deactivate that and try again', 'load-html-files' ) );
}

