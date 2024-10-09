<?php

namespace CodeRex\Ecommerce;

use CodeRex\Ecommerce\Payment\PayPalPayment;
use CreatorLms\user\UserHelper;
use CreatorLms\user\UserRepository;
use PaypalGateway;

class Checkout
{

	/**
	 * The single instance of the class.
	 *
	 * @var Checkout|null
	 */
	protected static $instance = null;

	/**
	 * Checkout fields are stored here.
	 *
	 * @var array|null
	 */
	protected $fields = null;


	/**
	 * Gets the main Checkout instance
	 *
	 * @return Checkout|null
	 * @since 1.0.0
	 */
	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();

			/**
			 * Trigger when Creator LMS checkout is first initiated
			 *
			 * @since 1.0.0
			 */
			do_action('creator_lms_checkout_init', self::$instance);
		}
		return self::$instance;
	}


	/**
	 * Get checkout fields
	 *
	 * @param string $fieldset
	 * @return array
	 * @since 1.0.0
	 */
	public function get_checkout_fields($fieldset = '')
	{
		return array();
	}


	/**
	 * Check guest checkout is enabled for course enrollment
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function is_guest_checkout_enabled()
	{
		return apply_filters('creator_lms_checkout_guest_checkout', 'yes' === get_option('creator_lms_guest_checkout'));
	}


	/**
	 * Check if registration is required for course checkout
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function is_registration_enabled()
	{
		return apply_filters('creator_lms_checkout_registration_required', 'yes' === get_option('creator_lms_enable_registration'));
	}

	/**
	 * Check if login is required for course checkout
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function is_login_enabled()
	{
		return apply_filters('creator_lms_checkout_login_required', 'yes' === get_option('creator_lms_enable_login'));
	}

	/**
	 * Process checkout
	 * @return void
	 * @since 1.0.0
	 */
	public function process_checkout()
	{
		try {
			// Check if the nonce is valid.
			if (!wp_verify_nonce($_POST['creator-lms-process-checkout-nonce'], 'creator-lms-process_checkout')) {
				// If the cart is empty, the nonce check failed because of session expiry.
				if (ecommerce()->cart->is_empty()) {
					$expiry_message = sprintf(
					/* translators: %s: shop cart url */
						__('Sorry, your session has expired. <a href="%s">Return to shop</a>', 'creator-lms'),
						esc_url(get_permalink(crlms_get_page_id('course')))
					);
					throw new \Exception($expiry_message);
				}

				ecommerce()->session->set('refresh_totals', true);
				throw new \Exception(__('We were unable to process your order, please try again.', 'creator-lms'));
			}

			// Check if there is an active logged-in user
			if (!is_user_logged_in()) {
				$response = [
					'status' => 'error',
					'message' => __('Please log in to purchase.', 'creator-lms'),
				];
				wp_send_json($response);
			}

			// Get the current user is not a teacher or administrator
			$current_user = wp_get_current_user();

			// Check if the current user has the 'subscriber' role
			if (!in_array('subscriber', $current_user->roles)) {
				$response = [
					'status' => 'error',
					'message' => __('Please become a student', 'creator-lms'),
				];
				wp_send_json($response);
			}

			do_action('creator_lms_before_checkout_process');

			$errors = new \WP_Error();
			$posted_data = $this->get_posted_data();

			// Update session for customer and totals.
			$this->update_session($posted_data);

			// Validate posted data and cart items before proceeding.
			$this->validate_checkout_data($posted_data, $errors);

			$cart_data = ecommerce()->cart->get_cart_contents();

			$total_price = ecommerce()->cart->get_total($cart_data, 0);

			//=== verify enrollment before order creation ===//
			$enrollment_verification = self::validate_enrollment($cart_data);
			if ($enrollment_verification['status'] == 'error') {
				wp_send_json($enrollment_verification);
			}


			$notice_count = 0;
			foreach ($errors->errors as $code => $messages) {
				$data = $errors->get_error_data($code);
				foreach ($messages as $message) {
					$notice_count++;
					//=== movie to main - it is not a package function ===//
					crlms_add_notice($message, 'error', $data);
				}
			}


			if ($notice_count === 0) {
				$order_id = $this->create_order($posted_data);
				$order = ecommerce()->order($order_id);

				$proceed_order_without_payment = apply_filters('creator_lms_proceed_order_without_payment', false, $order_id);

				if ($total_price > 0) {
					$order->update_order_status('pending');
					$response = self::process_order_payment($order_id, $posted_data['payment_method']);
				} else {
					$response = self::process_order_without_payment($order_id);
				}

				//=== Start enrolling students ===//
				if ($response['status'] == 'success') {
					$response = self::handle_enrollment_after_payment($order_id, $cart_data);
				}

				wp_send_json($response);
			}


		} catch (\Exception $e) {
			// phpcs:disable
			wp_send_json_error(array('message' => $e->getMessage()));
			// phpcs:enable
		}
	}

	/**
	 * Validate enrollment
	 * @param $cart_items
	 * @return array
	 * @since 1.0.0
	 */
	public static function validate_enrollment($cart_items): array
	{
		foreach ($cart_items as $cart_item) {
			if (isset($cart_item['course_id'])) {
				$course_id = (int)$cart_item['course_id'];
				$user_id = get_current_user_id();
				$enrolled = UserHelper::is_user_enrolled($course_id, $user_id);
				$course_title = esc_html(get_the_title($course_id));
				if ($enrolled) {
					return [
						'status' => 'error',
						'message' => __('Already enrolled in ' . $course_title, 'creator-lms'),
					];
				}
			}
		}

		return [
			'status' => 'success',
			'message' => __('Enrollment allowed.', 'creator-lms'),
		];
	}


	/**
	 * Handle enrollment after payment
	 * @param $order_id
	 * @param $order_items
	 * @return array|null
	 * @since 1.0.0
	 */
	public static function handle_enrollment_after_payment($order_id, $order_items)
	{
		$response = null;
		foreach ($order_items as $order_item) {
			if (isset($order_item['course_id'])) {
				$course_id = (int)$order_item['course_id'];
				$response = self::attempt_course_enrollment($course_id, $order_id);

				if ($response['status'] == 'error') {
					crlms_add_notice($response['message'], $response['status']);
					return $response;
				}
			}
		}

		return $response;
	}

	/**
	 * Attempt course enrollment
	 * @param $course_id
	 * @param $order_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function attempt_course_enrollment($course_id, $order_id): array
	{

		$user_id = get_current_user_id();

		$enrolled = UserHelper::is_user_enrolled($course_id, $user_id);

		if ($enrolled) {
			return [
				'status' => 'success',
				'message' => __('Already enrolled.', 'creator-lms'),
			];
		}

		$data = array(
			'course_id' => $course_id,
			'user_id' => $user_id,
			'meta_data' => null,
			'enrollment_source' => 'single', // or 'membership' membership for
			'plan_id' => null, // Add actual plan ID (only for membership plan)
			'last_order' => $order_id,
			'status' => 'active',  // active as progress will be counted for active enrollment (retake will make it expire and make new active enrollment)
			'created_at' => current_time('mysql'),
			'created_at_gmt' => current_time('mysql', 1),
			'updated_at' => current_time('mysql'),
			'updated_at_gmt' => current_time('mysql', 1),
			'created_by' => $user_id,
			'updated_by' => $user_id,
		);

		return UserRepository::insert_new_enrollment($data);
	}


	/**
	 * Get posted data
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_posted_data()
	{
		$data = array(
			'first_name' => isset($_POST['first-name']) ? crlms_clean(wp_unslash($_POST['first-name'])) : '',
			'last_name' => isset($_POST['last-name']) ? crlms_clean(wp_unslash($_POST['last-name'])) : '',
			'email' => isset($_POST['email']) ? crlms_clean(wp_unslash($_POST['email'])) : '',
			'address' => isset($_POST['address']) ? crlms_clean(wp_unslash($_POST['address'])) : '',
			'city' => isset($_POST['city']) ? crlms_clean(wp_unslash($_POST['city'])) : '',
			'state' => isset($_POST['state']) ? crlms_clean(wp_unslash($_POST['state'])) : '',
			'zip' => isset($_POST['zip']) ? crlms_clean(wp_unslash($_POST['zip'])) : '',
			'country' => isset($_POST['country']) ? crlms_clean(wp_unslash($_POST['country'])) : '',
			'payment_method' => isset($_POST['payment_method']) ? crlms_clean(wp_unslash($_POST['payment_method'])) : '',
		);
		return apply_filters('creator_lms_checkout_posted_data', $data);
	}


	/**
	 * Update session data
	 *
	 * @param $posted_data
	 * @return void
	 * @since 1.0.0
	 */
	public function update_session($posted_data): void
	{
		// Update payment method.
		ecommerce()->session->set('payment_method', $posted_data['payment_method']);
	}


	/**
	 * Validate checkout
	 *
	 * @param $posted_data
	 * @param $errors
	 * @return void
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function validate_checkout_data($posted_data, $errors): void
	{
		// Validate payment method.
		if (empty($posted_data['payment_method'])) {
			$errors->add('payment_method', __('Please select a payment method.', 'creator-lms'));
		}

		if ($errors->has_errors()) {
			throw new \Exception($errors->get_error_message());
		}
	}


	/**
	 * Create order
	 *
	 * @param $posted_data
	 * @return int|\WP_Error
	 * @since 1.0.0
	 */
	public function create_order($posted_data): \WP_Error|int
	{
		$cart_data = ecommerce()->cart->get_cart_contents();
		ecommerce()->cart->empty_cart();
		return ecommerce()->order()->create_order($cart_data, $posted_data);
	}

	/**
	 * Process order payment
	 * @param $order_id
	 * @param $payment_method
	 * @return array
	 * @since 1.0.0
	 */
	public static function process_order_payment($order_id, $payment_method): array
	{
		return match ($payment_method) {
			'offline_payment' => self::handle_offline_payment($order_id),
			'stripe' => self::handle_stripe_payment($order_id),
			'paypal' => self::handle_paypal_payment($order_id),
			default => [
				'status' => 'error',
				'order' => $order_id,
				'message' => __('Unknown payment method.', 'creator-lms'),
			],
		};
	}

	/**
	 * Handle paypal payment
	 * @param $order_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function handle_paypal_payment($order_id)
	{
		$package_order = ecommerce()->order($order_id);
		$amount = $package_order->amount;

		$return_url = crlms_get_page_url('thank_you');
		$cancel_url = crlms_get_page_url('checkout');

		$paypal_config = ecommerce()->payment_gateways()->paypal();
		$settings = $paypal_config->get_saved_settings();

		if(!$settings) {
			return [
				'status' => 'error',
				'order' => $order_id,
				'paypal' => null,
				'message' => __('No valid configuration found. ', 'creator-lms'),
			];
		}

		$paypal = ecommerce()->paypal($settings['client_id'], $settings['client_secret'], false);

		$order = $paypal->create_order($amount, $return_url, $cancel_url);

		if ($order && isset($order->id)) {
			return [
				'status' => 'pending',
				'order' => $order_id,
				'paypal' => [
					'orderID' => $order->id,
					'approval_url' => $order->links[1]->href,
				],
				'message' => __('Paypal order created.', 'creator-lms'),
			];
		} else {
			return [
				'status' => 'error',
				'order' => $order_id,
				'paypal' => null,
				'message' => __('Failed to create paypal order.', 'creator-lms'),
			];
		}
	}

	/**
	 * Handle offline payment
	 * @param $order_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function handle_offline_payment($order_id): array
	{
		$order = ecommerce()->order($order_id);
		$order->update_order_meta('crlms_order_payment_method', 'offline_payment');
		$order->update_order_meta('crlms_order_status', 'pending');

		return [
			'status' => 'success',
			'order' => $order_id,
			'message' => __('Payment confirmed.', 'creator-lms'),
		];
	}

	/**
	 * Process order without payment
	 * @param $order_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function process_order_without_payment($order_id): array
	{

		$order = ecommerce()->order($order_id);
		$order->update_order_meta('crlms_order_payment_method', 'free_payment');
		$order->update_order_meta('crlms_order_status', 'pending');

		return [
			'status' => 'success',
			'order' => $order_id,
			'message' => __('Payment confirmed.', 'creator-lms'),
		];
	}

}
