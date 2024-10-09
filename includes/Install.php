<?php

namespace CreatorLms;

defined( 'ABSPATH' ) || exit;

class Install {

	private static $db_updates = array(
		'2.0.0' => array(
			'crlms_update_200_db_version'
		)
	);


	/**
	 * Install Creator LMS
	 *
	 * @since 1.0.0
	 */
	public static function install() {
		self::create_tables();
		self::create_roles();
		self::update_crlms_version();
		self::maybe_update_db_version();
		self::maybe_set_activation_transients();

		add_option( 'crlms_admin_install_timestamp', time() );
	}


	/**
	 * Update Creator LMS version to current.
	 *
	 * @since 1.0.0
	 */
	public static function update_crlms_version() {
		update_option( 'crlms_version', CRLMS()::VERSION );
	}


	/**
	 * See if we need to show or run database updates during install.
	 *
	 * @since 1.0.0
	 */
	public static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			// the db upgrade/alter will be placed here with action scheduler
		} else {
			self::update_db_version();
		}
	}


	/**
	 * See if we need to set redirect transients for activation or not.
	 *
	 * @since 1.0.0
	 */
	public static function maybe_set_activation_transients() {
		if ( self::is_new_install() ) {
			set_transient( '_crlms_activation_redirect', 1, 30 );
		}
	}


	/**
	 * Update DB version to current.
	 *
	 * @param string|null $version
	 * @since 1.0.0
	 */
	public static function update_db_version( $version = null ) {
		update_option( 'crlms_db_version', is_null( $version ) ? CRLMS()::VERSION : $version );
	}


	/**
	 * Is a DB update needed?
	 *
	 * @since  1.0.0
	 * @return boolean
	 */
	public static function needs_db_update() {
		$current_db_version = get_option( 'crlms_db_version', null );
		$updates            = self::get_db_update_callbacks();
		$update_versions    = array_keys( $updates );
		usort( $update_versions, 'version_compare' );
		return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
	}


	/**
	 * Get list of DB update callbacks.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}


	/**
	 * Is this a brand new Creator LMS install?
	 *
	 * @since  1.0.0
	 * @return boolean
	 */
	public static function is_new_install() {
		return is_null( get_option( 'crlms_version', null ) );
	}


	/**
	 * Create necessary database tables for Creator LMS.
	 *
	 * @since 1.0.0
	 */
	public static function create_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( self::get_schema() );
		dbDelta( self::create_users_enrolled_in_courses_table() );
		dbDelta( self::create_courses_with_sections() );
		dbDelta( self::create_sections_with_lessons() );
		dbDelta( self::create_order_items_table() );
	}

	/**
	 * Get schema
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private static function get_schema() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$tables = "
CREATE TABLE {$wpdb->prefix}crlms_sessions (
  session_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  session_key char(32) NOT NULL,
  session_value longtext NOT NULL,
  session_expiry bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (session_id),
  UNIQUE KEY session_key (session_key)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}crlms_chapter_relationship (
  id bigint(20) unsigned NOT NULL auto_increment,
  course_id INT(11) NOT NULL,
  chapter_id INT(11) NOT NULL,
  order_number INT(11) DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY unique_course_chapter (course_id, chapter_id),
  KEY chapter_id (chapter_id),
  KEY course_id (course_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}crlms_content_relationship (
  id bigint(20) unsigned NOT NULL auto_increment,
  chapter_id INT(11) NOT NULL,
  content_id INT(11) NOT NULL,
  content_type VARCHAR(225) NOT NULL,
  order_number INT(11) DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY unique_chapter_content (chapter_id, content_id, content_type),
  KEY chapter_id (chapter_id),
  KEY content_id (content_id)
) $charset_collate;
";
		return $tables;
	}

	/**
	 * creates user enrollment record table
	 * status active as progress will be counted for active enrollment
	 * (retake will make it expire and make new active enrollment)
	 * inactive will pause the progress and any subscription
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private static function create_users_enrolled_in_courses_table() {
		global $wpdb;

		$table        = $wpdb->prefix . 'crlms_users_enrolled_in_courses';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				course_id BIGINT(20),
				user_id BIGINT(20),
				meta_data LONGTEXT NULL,
				enrollment_source ENUM('single', 'membership') DEFAULT 'single',
				plan_id VARCHAR(200) DEFAULT NULL,
				last_order BIGINT(20) DEFAULT NULL,
				status ENUM('active', 'inactive', 'expired') DEFAULT 'inactive',
				created_at DATETIME DEFAULT '0000-00-00 00:00:00',
				created_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				created_by VARCHAR(225) DEFAULT NULL,
				updated_by VARCHAR(225) DEFAULT NULL,
				PRIMARY KEY (id)
			  ) $charset_collate; ";

		return $sql;
	}

	/**
	 * Course & section relationship table
	 * @return string
	 * @since 1.0.0
	 */
	private static function create_courses_with_sections() {
		global $wpdb;

		$table        = $wpdb->prefix . 'crlms_courses_with_sections';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				course_id BIGINT(20),
				section_id BIGINT(20),
				created_at DATETIME DEFAULT '0000-00-00 00:00:00',
				created_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				created_by VARCHAR(225) DEFAULT NULL,
				updated_by VARCHAR(225) DEFAULT NULL,
				PRIMARY KEY (id)
			  ) $charset_collate; ";

		return $sql;
	}

	/**
	 * Add section lesson relation table
	 * @return string
	 * @since 1.0.0
	 */
	private static function create_sections_with_lessons() {
		global $wpdb;

		$table        = $wpdb->prefix . 'crlms_sections_with_lessons';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				section_id BIGINT(20),
				lesson_id BIGINT(20),
				created_at DATETIME DEFAULT '0000-00-00 00:00:00',
				created_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				created_by VARCHAR(225) DEFAULT NULL,
				updated_by VARCHAR(225) DEFAULT NULL,
				PRIMARY KEY (id)
			  ) $charset_collate; ";

		return $sql;
	}

	/**
	 * creates order items table
	 * @return void
	 * @since 1.0.0
	 */
	private static function create_order_items_table() {
		global $wpdb;

		$table        = $wpdb->prefix . 'crlms_order_items';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				order_id BIGINT(20),
				order_item_id BIGINT(20),
				order_item_name VARCHAR(225) DEFAULT NULL,
				created_at DATETIME DEFAULT '0000-00-00 00:00:00',
				created_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				created_by VARCHAR(225) DEFAULT NULL,
				updated_by VARCHAR(225) DEFAULT NULL,
				PRIMARY KEY (id)
			  ) $charset_collate; ";

		return $sql;
	}


	/**
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		$admin = get_role( 'administrator' );

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}


	/**
	 * Get capabilities for Creator LMS
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_core_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_creator_lms'
		);

		$capability_types = array( 'crlms-course', 'crlms-order', 'crlms-coupon', 'crlms-lesson', 'crlms-topic', 'crlms-question' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type.
				"edit_{$capability_type}s",
				"read_{$capability_type}s",
				"delete_{$capability_type}s",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
			);
		}

		return $capabilities;
	}

}
