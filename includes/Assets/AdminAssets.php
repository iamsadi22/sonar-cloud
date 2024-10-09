<?php

namespace CreatorLms\Assets;

use CreatorLms\Abstracts\Assets;
use CreatorLms\Membership\MembershipHelper;

class AdminAssets extends Assets {

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'admin_print_scripts', array( $this, 'localize_printed_scripts' ) );
	}

	public function get_scripts() {
		$suffix = '';

		$scripts = array(
			'crlms-select2' => array(
				'src'     => self::get_asset_url( 'assets/js/vendor/select2'.$suffix.'.js' ),
				'deps'    => array('jquery'),
				'version' => CREATOR_LMS_VERSION,
				'screens' => array()
			),
			'crlms-admin' => array(
				'src'     => self::get_asset_url( 'assets/js/dist/admin/admin'.$suffix.'.js' ),
				'deps'    => array('jquery'),
				'version' => CREATOR_LMS_VERSION,
				'screens' => array()
			),
			'creator-lms' => array(
				'src'     => self::get_asset_url( 'assets/js/dist/admin/creator-lms'.$suffix.'.js' ),
				'deps'    => array('react', 'react-dom', 'wp-element', 'wp-i18n', 'wp-url', 'wp-api-fetch'),
				'version' => CREATOR_LMS_VERSION,
				'screens' => array()
			),
			'crlms-settings' => array(
				'src'     => self::get_asset_url( 'assets/js/dist/admin/settings'.$suffix.'.js' ),
				'deps'    => array('jquery'),
				'version' => CREATOR_LMS_VERSION,
				'screens' => array()
			),
			'crlms-tools' => array(
				'src'     => self::get_asset_url( 'assets/js/dist/admin/tools'.$suffix.'.js' ),
				'deps'    => array('jquery'),
				'version' => CREATOR_LMS_VERSION,
				'screens' => array()
			),
			'crlms-membership' => array(
				'src'     => self::get_asset_url( 'assets/js/dist/admin/membership'.$suffix.'.js' ),
				'deps'    => array('jquery'),
				'version' => CREATOR_LMS_VERSION,
				'screens' => array()
			),
			'crlms-course' => array(
				'src'     => self::get_asset_url( 'assets/js/dist/admin/course'.$suffix.'.js' ),
				'deps'    => array('jquery'),
				'version' => CREATOR_LMS_VERSION,
				'screens' => array()
			),
		);

		return is_array( $scripts ) ? array_filter( $scripts ) : array();
	}

	public function get_styles() {
		$suffix = '';
		$styles = array(
			'crlms-frontend'      => array(
				'src'     => self::get_asset_url( 'assets/css/dist/admin/admin'.$suffix.'.css' ),
				'deps'    => '',
				'version' => CREATOR_LMS_VERSION,
				'media'   => 'all',
				'has_rtl' => true,
			),
			'crlms-select2' => array(
				'src'     => self::get_asset_url( 'assets/css/vendor/select2'.$suffix.'.css' ),
				'deps'    => '',
				'version' => CREATOR_LMS_VERSION,
				'media'   => 'all',
				'has_rtl' => false,
			),
		);
		return is_array( $styles ) ? array_filter( $styles ) : array();
	}

	public function get_script_data( $handle ) {
		switch ( $handle ) {
			case 'creator-lms':
				$localized_data = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'api_url' => get_rest_url() . CREATOR_LMS_API_URL,
					'nonce' => wp_create_nonce( 'creator-lms' ),
				);
				break;
			case 'crlms-settings':
				$localized_data = array(
					'ajax_url' 				=> admin_url( 'admin-ajax.php' ),
					'search_pages_nonce'	=> wp_create_nonce( 'search-pages' ),
				);
				break;
			case 'crlms-membership':
				$localized_data = array(
					'ajax_url' 				=> admin_url( 'admin-ajax.php' ),
					'nonce'	=> wp_create_nonce( 'crlms-membership' ),
					'courses' => MembershipHelper::get_courses_for_membership_plans(),
					'subscription_options' => MembershipHelper::subscription_options()
				);
				break;
			case 'crlms-course':
				$localized_data = array(
					'ajax_url' 				=> admin_url( 'admin-ajax.php' ),
					'nonce'	=> wp_create_nonce( 'crlms-course' ),
				);
				break;
			default:
				$localized_data = false;
		}

		return apply_filters( 'creator_lms_get_admin_script_data', $localized_data, $handle );
	}

	public function load_scripts() {
		$this->register_scripts();
		$this->register_styles();

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		wp_enqueue_media();
		wp_enqueue_script('wp-element');
		wp_enqueue_script('wp-data');

		foreach ( $this->get_scripts() as $handle => $script ) {
			if ( !$this->should_enqueue( $script, $screen_id ) ) {
				continue;
			}
			wp_enqueue_script($handle);
		}

		foreach ( $this->get_styles() as $handle => $style ) {
			if ( !$this->should_enqueue( $style, $screen_id ) ) {
				continue;
			}
			wp_enqueue_style($handle);
		}
	}

}

(new AdminAssets())->init();
