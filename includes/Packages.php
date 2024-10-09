<?php

namespace CreatorLms;

class Packages {

	protected static $packages = array(
		'e-commerce' => 'Package',
	);

	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'on_init' ) );
	}

	public static function on_init(): void {
		self::load_packages();
	}

	public static function package_exists( $package ): bool {
		return file_exists( CREATOR_LMS_DIR . '/packages/' . $package );
	}

	public static function load_packages(): void {
		foreach ( self::$packages as $package_name => $package_class ) {
			if ( ! self::package_exists( $package_name ) ) {
				continue;
			}
			if (file_exists( CREATOR_LMS_DIR.'/packages/'.$package_name.'/'.$package_class.'.php' )) {
				require_once CREATOR_LMS_DIR.'/packages/'.$package_name.'/'.$package_class.'.php';
				call_user_func( array( $package_class, 'init' ) );
			}
		}
	}
}


Packages::init();
