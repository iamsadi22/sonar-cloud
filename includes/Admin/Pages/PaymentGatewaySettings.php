<?php
namespace CreatorLms\Admin\Pages;

use CreatorLms\Abstracts\SettingsPage;
use Gateways\Gateways;
use function CodeRex\Ecommerce\ecommerce;

defined( 'ABSPATH' ) || exit;


class PaymentGatewaySettings extends SettingsPage {

	public $gateways;

	public function __construct() {
		$this->id 		= 'payments';
		$this->label 	= __('Payment Gateways', 'creator-lms');

		parent::__construct();
	}


	/**
	 * Get the sections for the settings page
	 *
	 * @return array|string[]
	 */
	public function get_own_sections(): array {
		return array(
			'' 					=> __( 'General', 'creator-lms' ),
			'offline_payment' 	=> __( 'Offline', 'creator-lms' ),
			'stripe' 			=> __( 'Stripe', 'creator-lms' ),
			'paypal' 			=> __( 'Paypal', 'creator-lms' ),
		);
	}


	/**
	 * Get default settings options
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function get_settings_for_default_section() {
		$settings = array(
			array(
				'title' => __( 'General Settings', 'creator-lms' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'payment_general_settings',
			),
			array(
				'title'         => __( 'Guest checkout', 'creator-lms' ),
				'desc'          => __( 'Enable guest checkout', 'creator-lms' ),
				'id'            => 'creator_lms_guest_checkout',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
			),
			array(
				'title'         => __( 'Account login', 'creator-lms' ),
				'desc'          => __( 'Enable login form for checkout', 'creator-lms' ),
				'id'            => 'creator_lms_enable_login',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'payment_general_settings',
			),
		);
		return apply_filters( 'creator_lms_payment_general_settings', $settings );
	}


	/**
	 * Output the settings fields
	 *
	 * @since 1.0.0
	 */
	public function output() {
		global $creator_lms_current_section;
		// Load gateways so we can show any global options they may have.
		$gateways = ecommerce()->payment_gateways();

		if ( $creator_lms_current_section ) {
			foreach ( $gateways->payment_gateways as $gateway ) {

				if ( in_array( $creator_lms_current_section, array( $gateway->id, sanitize_title( get_class( $gateway ) ) ), true ) ) {
					if ( isset( $_GET['toggle_enabled'] ) ) {
						$enabled = $gateway->get_option( 'enabled' );
						if ( $enabled ) {
							$gateway->settings['enabled'] = crlms_string_to_bool( $enabled ) ? 'no' : 'yes';
						}
					}
					$gateway->admin_options();
					break;
				}
			}
		}
		parent::output();
	}


	/**
	 * Save settings.
	 *
	 * @since 1.0.0
	 */
	public function save() {
		global $creator_lms_current_section;

		$payment_gateways = ecommerce()->payment_gateways();

		$this->save_settings_for_current_section();

		if ( ! $creator_lms_current_section ) {
			// If section is empty, we're on the main settings page. This makes sure 'gateway ordering' is saved.
			$payment_gateways->process_admin_options();
			$payment_gateways->init();
		} else {
			// There is a section - this may be a gateway or custom section.
			foreach ( $payment_gateways->payment_gateways() as $gateway ) {
				if ( in_array( $creator_lms_current_section, array( $gateway->id, sanitize_title( get_class( $gateway ) ) ), true ) ) {
					do_action( 'creator_lms_update_options_payment_gateways_' . $gateway->id );
					$payment_gateways->init();
				}
			}
		}

		$this->do_update_options_action();
	}
}
