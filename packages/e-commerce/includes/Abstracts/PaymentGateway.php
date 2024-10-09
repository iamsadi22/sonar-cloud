<?php

namespace Abstracts;

use CreatorLms\Abstracts\SettingsApi;

defined( 'ABSPATH' ) || exit;

/**
 * Class PaymentGateway
 * @package CreatorLms\Order\Abstracts
 * @since 1.0.0
 */
abstract class PaymentGateway extends SettingsApi {


	/**
	 * Text of order button
	 *
	 * @var string $order_button_text
	 * @since 1.0.0
	 */
	public string $order_button_text;

	/**
	 * Id of the payment gateway
	 *
	 * @var string $id
	 * @since 1.0.0
	 */
	public string $id;

	/**
	 * Define if the gateway is enabled or not
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	public bool $is_enabled = false;


	/**
	 * Title of the gateway
	 *
	 * @var string $title
	 * @since 1.0.0
	 */
	public string $title;


	/**
	 * Description of the payment gateway
	 *
	 * @var string $description
	 * @since 1.0.0
	 */
	public string $description;


	/**
	 * Chosen payment method id.
	 *
	 * @var bool
	 */
	public bool $chosen;


	/**
	 * True if gateway shows fields on checkout
	 *
	 * @var bool $has_fields
	 */
	public bool $has_fields;


	/**
	 * Check if gateway has fields on checkout
	 *
	 * @return bool
	 */
	public function has_fields() {
		return (bool) $this->has_fields;
	}

	/**
	 * Get payment gateway title
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function get_title() {
		return apply_filters( 'creator_lms_payment_gateway_title', $this->title, $this->id );
	}


	/**
	 * Get payment gateway description
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function get_description() {
		return apply_filters( 'creator_lms_gateway_description', $this->description, $this->id );
	}


	/**
	 * Check if payment gateway is available
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_available() {
		return true;
	}


	/**
	 * Returns whether this gateways needs to setup
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function needs_setup() {
		return false;
	}


	/**
	 * Process Payment
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 * @since 1.0.0
	 */
	public function process_payment( $order_id ) {
		return array();
	}


	/**
	 * Process refund
	 *
	 * @param int $order_id Order ID.
	 * @return bool
	 * @since 1.0.0
	 */
	public function process_refund( $order_id ) {
		return false;
	}


	/**
	 * Set as current gateway.
	 *
	 * Set this as the current gateway.
	 */
	public function set_current() {
		$this->chosen = true;
	}
}
