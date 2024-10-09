<?php


defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class Package {

	const VERSION = '1.0.0';


	public static function init(): void {
		self::includes();
	}

	public static function includes(): void {
		require_once self::get_path() . '/e-commerce/vendor/woocommerce/action-scheduler/action-scheduler.php';
		require self::get_path() . '/e-commerce/vendor/autoload.php';
		require self::get_path() . '/e-commerce/includes/Ecommerce.php';
		require_once self::get_path() . '/e-commerce/includes/Utility/core-functions.php';
	}

	public static function get_version(): string {
		return self::VERSION;
	}

	public static function get_path(): string {
		return dirname( __DIR__ );
	}
}
