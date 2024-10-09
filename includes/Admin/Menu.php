<?php

namespace CreatorLms\Admin;

use CreatorLms\Admin\Pages\AdminSettings;

class Menu {

	/**
	 * Admin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'init_menu' ) );
		add_action( 'admin_menu', array( $this, 'register_submenu' ) );
		add_action( 'admin_menu', array( $this, 'menu_highlight' ) );

		add_action( 'wp_loaded', array( $this, 'save_settings' ) );
	}


	/**
	 * Init menu
	 *
	 * @since 1.0.0
	 */
	public function init_menu() {
		global $submenu;

		$slug          = CREATOR_LMS_SLUG;
		$menu_position = 6;
		$capability    = 'manage_options';

		add_menu_page(
			__( 'Creator LMS', 'creator-lms' ),
			__( 'Creator LMS', 'creator-lms' ),
			$capability,
			$slug,
			[ $this, 'plugin_page' ],
			'dashicons-book-alt',
			$menu_position
		);

		if ( current_user_can( $capability ) ) {
			$submenu[ $slug ][] = [ esc_attr__( 'Courses', 'creator-lms' ), $capability, 'admin.php?page=' . $slug . '#' ]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}


	/**
	 * Register submenu
	 *
	 * @since 1.0.0
	 */
	public function register_submenu() {
		$slug          = CREATOR_LMS_SLUG;
		$capability    = 'manage_creator_lms';
//		add_submenu_page(
//			$slug,
//			__( 'Courses', 'creator-lms' ),
//			__( 'Courses', 'creator-lms' ),
//			$capability,
//			'edit.php?post_type=crlms-course',
//			null
//		);
//
//		add_submenu_page(
//			$slug,
//			__( 'Sections', 'creator-lms' ),
//			__( 'Sections', 'creator-lms' ),
//			$capability,
//			'edit.php?post_type=crlms-section',
//			null
//		);
//
//		add_submenu_page(
//			$slug,
//			__( 'Lessons', 'creator-lms' ),
//			__( 'Lessons', 'creator-lms' ),
//			$capability,
//			'edit.php?post_type=crlms-lesson',
//			null
//		);

//		add_submenu_page(
//			$slug,
//			__( 'Memberships', 'creator-lms' ),
//			__( 'Memberships', 'creator-lms' ),
//			$capability,
//			'edit.php?post_type=crlms-membership',
//			null
//		);
//
//		add_submenu_page(
//			$slug,
//			__( 'Orders', 'creator-lms' ),
//			__( 'Orders', 'creator-lms' ),
//			$capability,
//			'edit.php?post_type=crlms-order',
//			null
//		);
//
//		add_submenu_page(
//			$slug,
//			__( 'Coupons', 'creator-lms' ),
//			__( 'Coupons', 'creator-lms' ),
//			$capability,
//			'edit.php?post_type=crlms-coupon',
//			null
//		);

//		add_submenu_page(
//			$slug,
//			__( 'Tools', 'creator-lms' ),
//			__( 'Tools', 'creator-lms' ),
//			$capability,
//			'crlms-tools',
//			array($this, 'render_tools_page')
//		);

		add_submenu_page(
			$slug,
			__( 'Settings', 'creator-lms' ),
			__( 'Settings', 'creator-lms' ),
			$capability,
			'crlms-settings',
			array($this, 'render_settings_page')
		);
	}


	public function menu_highlight() {
		global $parent_file, $submenu_file, $post_type, $current_screen;

		switch ( $post_type ) {
			case 'crlms-course':
			case 'crlms-order':
			case 'crlms-coupon':
				$parent_file = 'creator_lms'; // WPCS: override ok.
				break;
		}
	}


	/**
	 * Render the plugin page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function plugin_page() {
		require_once  CREATOR_LMS_INCLUDES.'/Admin/views/app.php';
	}


	/**
	 * Render tools page
	 *
	 * @since 1.0.0
	 */
	public function render_tools_page(): void {
		require_once  CREATOR_LMS_INCLUDES.'/Admin/views/tools.php';
	}


	/**
	 * Render settings page
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page(): void {
		global $creator_lms_current_tab, $creator_lms_current_section;
		$creator_lms_current_tab	= empty( $_GET['tab'] ) ? 'general' : sanitize_title( wp_unslash( $_GET['tab'] ) ); // WPCS: input var okay, CSRF ok.
		$tabs 						= apply_filters( 'creator_lms_settings_tabs_array', array() );
		require_once  CREATOR_LMS_INCLUDES.'/Admin/views/settings.php';
	}


	/**
	 * Save settings data
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function save_settings(): void {
		global $creator_lms_current_tab, $creator_lms_current_section, $creator_lms_current_page;
		if ( is_crlm_admin_page() ) {
			$creator_lms_current_section = empty( $_GET['section'] ) ? '' : sanitize_title( wp_unslash( $_GET['section'] ) ); // WPCS: input var okay, CSRF ok.
			$creator_lms_current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( wp_unslash( $_GET['tab'] ) ); // WPCS: input var okay, CSRF ok.
			$creator_lms_current_page    = empty( $_GET['page'] ) ? 'crlms-settings' : sanitize_title( wp_unslash( $_GET['page'] ) ); // WPCS: input var okay, CSRF ok.

			if ( ! empty( $_POST['save'] )) {
				AdminSettings::save();
			}

		}
	}

}
