<?php
namespace CreatorLms\Admin\Settings;

use CreatorLms\Abstracts\Settings;

/**
 * General settings class.
 *
 * Handles general settings for the Creator LMS.
 *
 * @since 1.0.0
 */
class General extends Settings {

	/**
	 * The settings ID.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $id = 'general';

	/**
	 * Constructor.
	 *
	 * Initializes the general settings.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->id 		= 'general';
		$this->label 	= __( 'General', 'creator-lms' );
	}

	/**
	 * Get the general settings.
	 *
	 * @return array The settings array.
	 *
	 * @since 1.0.0
	 */
	public function get_settings() {
		$settings = array(
			array(
				'id'		=> 'crlms_courses_per_page',
				'type'		=> 'number',
				'default'	=> '10',
				'value'     => '',
				'options'   => array(),
			),
		);

		return $settings;
	}
}
