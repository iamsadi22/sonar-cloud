<?php

namespace CodeRex\Ecommerce;

use includes\Subscription\PaypalSubscription;
use PaypalGateway;

class Membership
{

	/**
	 * The single instance of the class.
	 *
	 * @var Membership|null
	 */
	protected static $instance = null;

	/**
	 * Gets the main Checkout instance
	 *
	 * @return Membership|null
	 * @since 1.0.0
	 */
	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Generate membership product
	 * @param $membership_id
	 * @param $membership_plans
	 * @return array|void
	 * @since 1.0.0
	 */
	public function generate_membership_product($membership_id, $membership_plans) {
		$paypal_config = new PaypalGateway ;
		$settings = $paypal_config->get_saved_settings();

		if(!$settings) {
			return [
				'status' => 'error',
				'message' => __('No valid configuration found.', 'creator-lms'),
			];
		}

		$paypal = ecommerce()->paypal($settings['client_id'], $settings['client_secret'], false);
		$product = $paypal->create_product($membership_id, 'Membership-'. $membership_id);
		die();
	}

	/**
	 * Request membership
	 * @param $membership_id
	 * @param $plan_id
	 * @return void
	 * @since 1.0.0
	 */
	public function request_membership($membership_id, $plan_id) {
		$checkout_page = get_permalink( crlms_get_page_id('checkout') );

		$query_array = [
			'membership_id' => $membership_id,
			'plan_id' => $plan_id,
		];

		$redirect_url = $checkout_page . '?' . http_build_query($query_array);

		$response = [
			'status' => 'success',
			'message' => __('Redirecting to checkout.', 'creator-lms'),
			'redirect_url' => $redirect_url
		];

		wp_send_json($response);
	}

}
