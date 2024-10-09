<?php
namespace CodeRex\Ecommerce\Payment;

/**
 * Paypal payment
 * @since 1.0.0
 */
class PayPalPayment {
	private $client_id;
	private $client_secret;
	private $is_live;

	/**
	 * The single instance of the class.
	 *
	 * @var PayPalPayment
	 * @since 1.0.0
	 */
	protected static $_instance = null;


	/**
	 * @return PayPalPayment
	 * @since 1.0.0
	 */
	public static function instance($client_id, $client_secret, $is_live) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self($client_id, $client_secret, $is_live);
		}
		return self::$_instance;
	}

	public function __construct($client_id, $client_secret, $is_live) {

		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->is_live = $is_live;

	}

	/**
	 * Get API URL - Live or Test mode
	 * @param $endpoint
	 * @return string
	 * @since 1.0.0
	 */
	private function get_api_url($endpoint) {
		$base_url = $this->is_live ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
		return $base_url . $endpoint;
	}

	/**
	 * Get access token
	 * @return null
	 * @since 1.0.0
	 */
	private function get_access_token() {
		$url = $this->get_api_url('/v1/oauth2/token');

		$response = wp_remote_post($url, [
			'body' => 'grant_type=client_credentials',
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode("$this->client_id:$this->client_secret"),
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
		]);

		if (is_wp_error($response)) {
			return null;
		}

		$body = json_decode(wp_remote_retrieve_body($response));
		return $body->access_token ?? null;
	}

	/**
	 * Create order
	 * @param $amount
	 * @param $return_url
	 * @param $cancel_url
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function create_order($amount, $return_url, $cancel_url) {
		$access_token = $this->get_access_token();
		if (!$access_token) {
			return null;
		}

		$url = $this->get_api_url('/v2/checkout/orders');

		$response = wp_remote_post($url, [
			'body' => json_encode([
				'intent' => 'CAPTURE',
				'purchase_units' => [[
					'amount' => [
						'currency_code' => 'USD',
						'value' => $amount,
					],
				]],
				'application_context' => [
					'return_url' => $return_url,
					'cancel_url' => $cancel_url,
				],
			]),
			'headers' => [
				'Authorization' => "Bearer $access_token",
				'Content-Type' => 'application/json',
			],
		]);

		if (is_wp_error($response)) {
			return null;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}

	/**
	 * Capture order
	 * @param $orderID
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function capture_order($orderID) {
		$access_token = $this->get_access_token();
		if (!$access_token) {
			return null;
		}

		$url = $this->get_api_url("/v2/checkout/orders/$orderID/capture");

		$response = wp_remote_post($url, [
			'headers' => [
				'Authorization' => "Bearer $access_token",
				'Content-Type' => 'application/json',
			],
		]);

		if (is_wp_error($response)) {
			return null;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}

	/**
	 * Create product
	 * @param $name
	 * @param $description
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function create_product($name, $description): mixed
	{
		$access_token = $this->get_access_token();
		if (!$access_token) {
			return null;
		}

		$url = $this->get_api_url('/v1/catalogs/products');

		$response = wp_remote_post($url, [
			'body' => json_encode([
				'name' => $name,
				'description' => $description,
				'type' => 'SERVICE'
			]),
			'headers' => [
				'Authorization' => "Bearer $access_token",
				'Content-Type' => 'application/json',
			],
		]);

		if (is_wp_error($response)) {
			return null;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}

	/**
	 * Create plan for product
	 * @param $product_id
	 * @param $name
	 * @param $description
	 * @param $billing_cycles
	 * @param $payment_preferences
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function create_plan($product_id, $name, $description, $billing_cycles, $payment_preferences) {
		$access_token = $this->get_access_token();
		if (!$access_token) {
			return null;
		}

		$url = $this->get_api_url('/v1/billing/plans');

		$response = wp_remote_post($url, [
			'body' => json_encode([
				'product_id' => $product_id,
				'name' => $name,
				'description' => $description,
				'billing_cycles' => $billing_cycles,
				'payment_preferences' => $payment_preferences,
			]),
			'headers' => [
				'Authorization' => "Bearer $access_token",
				'Content-Type' => 'application/json',
			],
		]);

		if (is_wp_error($response)) {
			return null;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}

	/**
	 * Activate plan
	 * @param $plan_id
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function activate_plan($plan_id): mixed
	{
		$access_token = $this->get_access_token();
		if (!$access_token) {
			return null;
		}

		$url = $this->get_api_url("/v1/billing/plans/$plan_id/activate");

		$response = wp_remote_post($url, [
			'method' => 'POST',
			'headers' => [
				'Authorization' => "Bearer $access_token",
				'Content-Type' => 'application/json',
			],
		]);

		if (is_wp_error($response)) {
			return null;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}

	/**
	 * Create subscription
	 * @param $plan_id
	 * @param $subscriber
	 * @param $return_url
	 * @param $cancel_url
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function create_subscription($plan_id, $subscriber, $return_url, $cancel_url): mixed
	{
		$access_token = $this->get_access_token();
		if (!$access_token) {
			return null;
		}

		$url = $this->get_api_url('/v1/billing/subscriptions');

		$response = wp_remote_post($url, [
			'body' => json_encode([
				'plan_id' => $plan_id,
				'subscriber' => $subscriber,
				'application_context' => [
					'return_url' => $return_url,
					'cancel_url' => $cancel_url,
				],
			]),
			'headers' => [
				'Authorization' => "Bearer $access_token",
				'Content-Type' => 'application/json',
			],
		]);

		if (is_wp_error($response)) {
			return null;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}

	/**
	 * Capture subscription
	 * @param $subscription_id
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function capture_subscription($subscription_id): mixed
	{
		$access_token = $this->get_access_token();
		if (!$access_token) {
			return null;
		}

		$url = $this->get_api_url("/v1/billing/subscriptions/$subscription_id/capture");

		$response = wp_remote_post($url, [
			'headers' => [
				'Authorization' => "Bearer $access_token",
				'Content-Type' => 'application/json',
			],
		]);

		if (is_wp_error($response)) {
			return null;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}
}
