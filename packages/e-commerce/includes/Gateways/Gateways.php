<?php

namespace CodeRex\Ecommerce\Gateways;

class Gateways {

	/**
	 * Payment gateway classes.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $payment_gateways = array();


	/**
	 * The single instance of the class.
	 *
	 * @var Gateways
	 * @since 1.0.0
	 */
	protected static $_instance = null;


	/**
	 * @return Gateways
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Initialize payment gateways.
	 */
	public function __construct() {
		$this->init();
	}


	/**
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		$load_gateways = array(
			'OfflineGateway',
			'PaypalGateway'
		);

		$load_gateways = apply_filters( 'creator_lms_payment_gateways', $load_gateways );

		// Get sort order option.
		$ordering  = (array) get_option( 'creator_lms_gateway_order' );
		$order_end = 999;

		foreach ( $load_gateways as $gateway ) {
			if (is_string($gateway) && class_exists( $gateway )) {
				$gateway = new $gateway();

				if ( isset( $ordering[ $gateway->id ] ) && is_numeric( $ordering[ $gateway->id ] ) ) {

					$this->payment_gateways[ $ordering[ $gateway->id ] ] = $gateway;
				} else {
					// Add to end of the array.
					$this->payment_gateways[ $order_end ] = $gateway;
					$order_end++;
				}
			}
		}
	}

	/**
	 * Initialize paypal
	 * @return \PaypalGateway
	 * @since 1.0.0
	 */
	public function paypal(): \PaypalGateway
	{
		return new \PaypalGateway();
	}


	/**
	 * Get gateways.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function payment_gateways(): array {
		$_available_gateways = array();
		if ( count( $this->payment_gateways ) > 0 ) {
			foreach ( $this->payment_gateways as $gateway ) {
				$_available_gateways[ $gateway->id ] = $gateway;
			}
		}
		return $_available_gateways;
	}


	/**
	 * Get available gateways.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_available_payment_gateways(): array {
		$_available_gateways = array();
		foreach ( $this->payment_gateways as $gateway ) {
			if ( $gateway->is_available() ) {
				$_available_gateways[ $gateway->id ] = $gateway;
			}
		}
		return array_filter( (array) apply_filters( 'creator_lms_available_payment_gateways', $_available_gateways ), array( $this, 'filter_valid_gateway_class' ) );
	}


	/**
	 * Callback for array filter. Returns true if gateway is of correct type.
	 *
	 * @param object $gateway Gateway to check.
	 * @return bool
	 * @since 1.0.0
	 */
	protected function filter_valid_gateway_class( $gateway ) {
		return $gateway && is_a( $gateway, 'Abstracts\PaymentGateway' );
	}


	/**
	 * Save options in admin.
	 */
	public function process_admin_options() {
		$gateway_order = isset( $_POST['gateway_order'] ) ? crlms_clean( wp_unslash( $_POST['gateway_order'] ) ) : ''; // WPCS: input var ok, CSRF ok.
		$order         = array();

		if ( is_array( $gateway_order ) && count( $gateway_order ) > 0 ) {
			$loop = 0;
			foreach ( $gateway_order as $gateway_id ) {
				$order[ esc_attr( $gateway_id ) ] = $loop;
				$loop++;
			}
		}

		update_option( 'creator_lms_gateway_order', $order );
	}



	/**
	 * Set the current, active gateway.
	 *
	 * @param array $gateways Available payment gateways.
	 * @since 1.0.0
	 */
	public function set_current_gateway( $gateways ) {
		// Be on the defensive.
		if ( ! is_array( $gateways ) || empty( $gateways ) ) {
			return;
		}

		$current_gateway = false;

		if ( CRLMS()->session ) {
			$current = CRLMS()->session->get( 'chosen_payment_method' );

			if ( $current && isset( $gateways[ $current ] ) ) {
				$current_gateway = $gateways[ $current ];
			}
		}

		if ( ! $current_gateway ) {
			$current_gateway = current( $gateways );
		}

		// Ensure we can make a call to set_current() without triggering an error.
		if ( $current_gateway && is_callable( array( $current_gateway, 'set_current' ) ) ) {
			$current_gateway->set_current();
		}
	}
}
