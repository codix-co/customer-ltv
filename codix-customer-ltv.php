<?php
/**
 * Plugin Name: Codix Customer LTV
 * Description: Assign users to user role based on their lifetime value orders.
 * Version: $VERSION
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Dor Shiff
 * Author URI: https://codix.co
 * Text Domain: cdx-customer-ltv
 */

// Exit if accessed directly.
use Codix\CustomerLTV\Includes\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'CDXCLTV_VERSION', '$VERSION' );
define( 'CDXCLTV_SLUG', 'cdx-ltv' );
define( 'CDXCLTV_FILE', __FILE__ );
define( 'CDXCLTV_PATH', plugin_dir_path( CDXCLTV_FILE ) );
define( 'CDXCLTV_URL', plugin_dir_url( CDXCLTV_FILE ) );

// Plugin autoloader.
require_once 'autoloader.php';



add_action( 'plugin_loaded', 'cdxltv_init' );

function cdxltv_init() {
	Plugin::get_instance();
}

/**
 * Load plugin textdomain to support i18n.
 */
function cdxltv_textdomain() {
	load_plugin_textdomain( 'cdx-ltv' );
}
add_action( 'plugin_loaded', 'cdxltv_textdomain' );
