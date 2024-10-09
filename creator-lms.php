<?php
/**
 * Plugin Name:     Creator LMS
 * Plugin URI:      https://coderex.co
 * Description:     All in one LMS solution for Coaches
 * Version:         1.0.1
 * Author:          Code Rex
 * Author URI:      https://coderex.co
 * Text Domain:     creator-lms
 * Domain Path:     /languages
 * Requires PHP:    7.1
 * Requires WP:     6.0.0
 * Namespace:       CreatorLms
 */

defined( 'ABSPATH' ) || exit;


if ( ! defined( 'CRLMS_FILE' ) ) {
	define('CRLMS_FILE', __FILE__);
}

if ( ! defined( 'CREATOR_LMS_DIR' ) ) {
	define( 'CREATOR_LMS_DIR', __DIR__ );
}

if ( ! defined( 'CREATOR_LMS_API_VERSION' ) ) {
	define( 'CREATOR_LMS_API_VERSION', 'v1' );
}

if ( ! defined( 'CREATOR_LMS_API_URL' ) ) {
	define( 'CREATOR_LMS_API_URL', 'creator-lms/' . CREATOR_LMS_API_VERSION );
}

// Load core packages and autoloader.
require __DIR__ . '/includes/Packages.php';

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	include_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

// Include the main WooCommerce class.
if ( ! class_exists( 'CreatorLms', false ) ) {
	include_once dirname( CRLMS_FILE ) . '/includes/CreatorLMS.php';
}


/**
 * Initialize the main plugin.
 *
 * @since 1.0.0
 *
 * @return \CreatorLms
 */
function CRLMS() {
	return CreatorLms::instance();
}

/*
 * Kick-off the plugin.
 *
 * @since 1.0.0
 */
CRLMS();


$GLOBALS['creator_lms'] = CRLMS();
