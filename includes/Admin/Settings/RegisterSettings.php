<?php

namespace CreatorLms\Admin\Settings;

use CreatorLms\Abstracts\Settings;

/**
 * RegisterSettings class.
 *
 * Handles the registration of settings.
 *
 * @since 1.0.0
 */
class RegisterSettings {

	/**
	 * The settings object.
	 *
	 * @var Settings
	 *
	 * @since 1.0.0
	 */
	protected $object;

	/**
	 * Constructor.
	 *
	 * @param Settings $object The settings object.
	 *
	 * @since 1.0.0
	 */
    public function __construct( $object ) {
        if ( ! is_object( $object ) ) {
            return;
        }

        $this->object = $object;

        add_filter( 'creator_lms_settings_groups', array( $this, 'register_settings_groups' ) );
        add_filter( 'creator_lms_settings-' . $this->object->get_id(), array( $this, 'register_settings' ) );
    }

	/**
	 * Register settings groups.
	 *
	 * @param array $groups The existing groups.
	 * @return array The modified groups.
	 *
	 * @since 1.0.0
	 */
	public function register_settings_groups( $groups ) {
		$groups[] = array(
			'id'    => $this->object->get_id(),
			'label' => $this->object->get_label(),
		);
		return $groups;
	}

	/**
	 * Register settings.
	 *
	 * @return array The filtered settings.
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {
		$settings = $this->object->get_settings();
		$filtered_settings = array();
		foreach ( $settings as $setting ) {
			if ( ! isset( $setting['id'] ) ) {
				continue;
			}
			$new_setting	= $this->register_setting( $setting );
			if ( $new_setting ) {
				$filtered_settings[] = $new_setting;
			}
		}
		return $filtered_settings;
	}


	/**
	 * Register a single setting.
	 *
	 * @param array $setting The setting array.
	 * @return array|false The new setting array or false if invalid.
	 *
	 * @since 1.0.0
	 */
	protected function register_setting( $setting ) {
		if ( ! isset( $setting['id'] ) ) {
			return false;
		}
		$new_setting = array(
			'id'		=> $setting['id'],
			'type'		=> $setting['type'],
			'default'	=> $setting['default'],
			'value'		=> $setting['value'],
		);

		return $new_setting;
	}
}
