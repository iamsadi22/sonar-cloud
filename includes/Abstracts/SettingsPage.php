<?php

namespace CreatorLms\Abstracts;

use CreatorLms\Admin\Pages\AdminSettings;

defined( 'ABSPATH' ) || exit;

abstract class SettingsPage {

	/**
	 * Settings page id
	 *
	 * @var $id
	 * @since 1.0.0
	 */
	protected $id;


	/**
	 * Settings page label
	 *
	 * @var $label
	 * @since 1.0.0
	 */
	protected $label;


	/**
	 * SettingsPage constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'creator_lms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'creator_lms_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'creator_lms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'creator_lms_settings_save_' . $this->id, array( $this, 'save' ) );
	}



	/**
	 * Get settings page ID
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_id() {
		return $this->id;
	}


	/**
	 * Get settings page label
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_label() {
		return $this->label;
	}


	/**
	 * Add page to settings page
	 *
	 * @param $pages
	 * @return mixed
	 * @since 1.0.0
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;
		return $pages;
	}


	/**
	 * Get settings array for section
	 *
	 * @param $section_id
	 * @return array
	 * @since 1.0.0
	 */
	public function get_settings_for_section( $section_id = '' ) {
		if ( '' === $section_id ) {
			$method_name = 'get_settings_for_default_section';
		} else {
			$method_name = "get_settings_for_{$section_id}_section";
		}

		if ( method_exists( $this, $method_name ) ) {
			$settings = $this->$method_name();
		} else {
			$settings = $this->get_settings_for_section_core( $section_id );
		}

		return apply_filters( 'creator_lms_get_settings_' . $this->id, $settings, $section_id );
	}


	/**
	 * Get the settings for a given section.
	 * This method is invoked from 'get_settings_for_section' when no 'get_settings_for_{current_section}_section'
	 * method exists in the class.
	 *
	 * @param string $section_id The section name to get the settings for.
	 * @return array Settings array, each item being an associative array representing a setting.
	 * @since 1.0.0
	 */
	protected function get_settings_for_section_core( $section_id ) {
		return array();
	}

	/**
	 * Get own sections for this page.
	 *
	 * @return string[]
	 * @since 1.0.0
	 */
	public function get_own_sections(): array {
		return array(
			''	=> __( 'General', 'creator-lms' )
		);
	}


	/**
	 * Get all sections of this page
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_sections(): array {
		$sections = $this->get_own_sections();
		return apply_filters( 'creator_lms_get_sections_' . $this->id, $sections );
	}



	/**
	 * Output sections
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function output_sections(): void {
		global $creator_lms_current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === count( $sections ) ) {
			return;
		}


		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			$url       = admin_url( 'admin.php?page=crlms-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) );
			$class     = ( $creator_lms_current_section === $id ? 'current' : '' );
			$separator = ( end( $array_keys ) === $id ? '' : '|' );
			$text      = esc_html( $label );
			echo "<li><a href='$url' class='$class'>$text</a> $separator </li>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '</ul><br class="clear" />';
	}


	/**
	 * Output the markup and settings fields for this page
	 *
	 * @since 1.0.0
	 */
	public function output() {
		global $creator_lms_current_section;
		$settings = $this->get_settings_for_section( $creator_lms_current_section );
		AdminSettings::output_fields( $settings );
	}


	/**
	 * Save admin settings
	 *
	 * @since 1.0.0
	 */
	public function save() {
		$this->save_settings_for_current_section();
		$this->do_update_options_action();
	}


	/**
	 * Save settings for current section
	 *
	 * @since 1.0.0
	 */
	public function save_settings_for_current_section() {
		global $creator_lms_current_section;
		$settings = $this->get_settings_for_section( $creator_lms_current_section );
		AdminSettings::save_fields( $settings );
	}


	/**
	 * Trigger creator_lms_update_options_ hook
	 *
	 * @param null $section_id
	 * @since 1.0.0
	 */
	public function do_update_options_action( $section_id = null ) {
		global $creator_lms_current_section;

		if ( is_null( $section_id ) ) {
			$section_id = $creator_lms_current_section;
		}

		if ( $section_id ) {
			do_action( 'creator_lms_update_options_' . $this->id . '_' . $section_id );
		}
	}
}
