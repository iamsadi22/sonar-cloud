<?php

namespace CodeRex\Ecommerce;

defined( 'ABSPATH' ) || exit;

class Order {

	/**
	 * Order id
	 * @var int
	 * @since 1.0.0
	 */
	public int $id = 0;

	/**
	 * Order items
	 * @var array
	 * @since 1.0.0
	 */
	public array $items = array();

	/**
	 * Order amount
	 * @var float
	 * @since 1.0.0
	 */
	public float $amount;

	/**
	 * Order status
	 * @var string
	 * @since 1.0.0
	 */
	public string $status;

	/**
	 * Order payment method
	 * @var string
	 * @since 1.0.0
	 */
	public string $payment_method;

	public function __construct($id = null) {
		if($id) {
			$this->id = $id;
			$this->items = $this->get_order_items();
			$this->amount = $this->get_order_amount();
			$this->status = $this->get_order_status();
			$this->payment_method = $this->get_order_payment_method();
		}
	}

	/**
	 * @return array|string
	 * @since 1.0.0
	 */
	public function get_order_items(): array|string
	{
		$items = get_post_meta($this->id, 'crlms_order_items', true);
		if(is_array($items)) {
			return $items;
		}

		return [];
	}

	/**
	 * Get order amount
	 * @return float
	 * @since 1.0.0
	 */
	public function get_order_amount(): float {
		$order_items = $this->items;
		$discount  = $this->get_order_discount();

		return ecommerce()->cart->get_total($order_items, $discount);
	}

	/**
	 * Get order discount
	 * @return float
	 * @since 1.0.0
	 */
	public function get_order_discount(): float {
		return (float)get_post_meta($this->id, 'crlms_order_discount', true);
	}

	/**
	 * Create new order
	 * @param $cart_data
	 * @param $posted_data
	 * @return int
	 * @since 1.0.0
	 */
	public static function create_order($cart_data, $posted_data): int
	{

		$order_id = wp_insert_post(array(
			'post_title' => 'Order #',
			'post_type' => 'crlms-order',
			'post_status' => 'publish',
			'meta_input' => array(),
		));

		if (is_wp_error($order_id)) {
			return $order_id;
		}

		$order_title = 'Order #' . $order_id;

		wp_update_post(array(
			'ID' => $order_id,
			'post_title' => $order_title,
		));

		update_post_meta($order_id, 'crlms_order_status', 'draft');
		update_post_meta($order_id, 'crlms_order_number', $order_title);
		update_post_meta($order_id, 'crlms_order_items', $cart_data);
		update_post_meta($order_id, 'crlms_order_discount', 0);
		update_post_meta($order_id, 'crlms_order_payment_method', $posted_data['payment_method']);
		update_post_meta($order_id, 'crlms_order_owner', get_current_user_id());

		(new Order($order_id))->save_billing_details($posted_data);

		return $order_id;
	}

	/**
	 * Save billing details
	 * @param $posted_data
	 * @return void
	 * @since 1.0.0
	 */
	private function save_billing_details($posted_data): void
	{
		foreach ($posted_data as $index => $billing_field) {
			$this->update_order_meta('crlms_billing_' . $index, $billing_field);
		}
	}

	/**
	 * Change order status
	 * @param $status
	 * @return void
	 * @since 1.0.0
	 */
	public function update_order_status($status): void
	{
		update_post_meta($this->id, 'crlms_order_status', $status);
	}

	/**
	 * Get order status
	 * @return string
	 * @since 1.0.0
	 */
	public function get_order_status(): string {
		return get_post_meta($this->id, 'crlms_order_status', true);
	}

	/**
	 * Get order payment method
	 * @return string
	 * @since 1.0.0
	 */
	public function get_order_payment_method(): string {
		return get_post_meta($this->id, 'crlms_order_payment_method', true);
	}

	/**
	 * Update order meta
	 * @param $key
	 * @param $value
	 * @return void
	 * @since 1.0.0
	 */
	public function update_order_meta($key, $value): void {
		update_post_meta($this->id, $key, $value);
	}

	/**
	 * Get order meta
	 * @param $key
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_order_meta($key): mixed
	{
		return get_post_meta($this->id, $key, true);
	}

}
