<?php

namespace CreatorLms\Assets;

use CreatorLms\Abstracts\Assets;

defined( 'ABSPATH' ) || exit();


class FrontendAssets extends Assets {

	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'wp_print_scripts', array( $this, 'localize_printed_scripts' ), 5 );
	}

	public function get_scripts(){
		$suffix = '';
		$scripts = array(
			'crlms-frontend'      => array(
				'src'     => self::get_asset_url( 'assets/js/dist/frontend/frontend'.$suffix.'.js' ),
				'deps'    => array('jquery'),
				'version' => CREATOR_LMS_VERSION,
			),
			'crlms-add-to-cart'      => array(
				'src'     => self::get_asset_url( 'assets/js/dist/frontend/add-to-cart'.$suffix.'.js' ),
				'deps'    => array('jquery'),
				'version' => CREATOR_LMS_VERSION,
			),
			'crlms-checkout'      => array(
				'src'     => self::get_asset_url( 'assets/js/dist/frontend/checkout'.$suffix.'.js' ),
				'deps'    => array('jquery'),
				'version' => CREATOR_LMS_VERSION,
			),
		);
		return is_array( $scripts ) ? array_filter( $scripts ) : array();
	}

	public function get_styles() {
		$suffix = '';
		$styles = array(
			'crlms-frontend'      => array(
				'src'     => self::get_asset_url( 'assets/css/frontend/frontend'.$suffix.'.css' ),
				'deps'    => '',
				'version' => CREATOR_LMS_VERSION,
				'media'   => 'all',
				'has_rtl' => true,
			)
		);
		return is_array( $styles ) ? array_filter( $styles ) : array();
	}

	public function get_script_data( $handle ) {
		switch ( $handle ) {
			case 'frontend':
				$localized_data = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'creator-lms' ),
				);
				break;
			case 'crlms-add-to-cart':
				$localized_data = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'add-to-cart' ),
				);
				break;
			case 'crlms-checkout':
				$localized_data = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'add-to-cart' ),
				);
				break;
			default:
				$localized_data = false;
		}

		return apply_filters( 'creator_lms_get_script_data', $localized_data, $handle );
	}

	public function load_scripts() {
		$this->register_scripts();
		$this->register_styles();

		wp_enqueue_style('crlms-frontend');

		wp_enqueue_script('crlms-frontend');
		wp_enqueue_script('crlms-add-to-cart');
		wp_enqueue_script('crlms-checkout');
	}
}

(new FrontendAssets())->init();
