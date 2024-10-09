<?php

namespace CreatorLms\Admin\Settings;

/**
 * AdminSettings class.
 *
 * Handles admin settings operations.
 *
 * @since 1.0.0
 */
class AdminSettings {

	/**
	 * Array of settings objects.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private static $settings = array();

	/**
	 * Get settings objects.
	 *
	 * @return array Array of settings objects.
	 *
	 * @since 1.0.0
	 */
	public static function get_settings_objects() {

		$settings = array();
		$settings[] = new General();


		self::$settings = apply_filters( 'creator_lms_get_settings_object', $settings );
		return self::$settings;
	}


	/**
	 * Get an option value.
	 *
	 * @param string $option_name The name of the option.
	 * @param mixed $default The default value if the option does not exist.
	 * @return mixed The option value or default if not found.
	 *
	 * @since 1.0.0
	 */
	public static function get_option( $option_name, $default = '' ) {
		if ( ! $option_name ) {
			return $default;
		}

		$option_value = get_option( $option_name, null );

		if ( is_array( $option_value ) ) {
			$option_value = wp_unslash( $option_value );
		} elseif ( ! is_null( $option_value ) ) {
			$option_value = stripslashes( $option_value );
		}

		return ( null === $option_value ) ? $default : $option_value;
	}
}
