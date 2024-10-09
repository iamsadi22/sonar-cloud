<?php
namespace CreatorLms;

use CodeRex\Ecommerce\Checkout;
use CreatorLms\Membership\MembershipHelper;
use PaypalGateway;
use function CodeRex\Ecommerce\ecommerce;

defined( 'ABSPATH' ) || exit();

/**
 * manage ajax requests
 */
class Requests {
	public function __construct()
	{

		add_action('wp_ajax_crlms_add_to_cart',  [$this, 'crlms_add_to_cart_handler']);
		add_action('wp_ajax_nopriv_crlms_add_to_cart', [$this, 'crlms_add_to_cart_handler']);

		add_action('wp_ajax_crlms_after_paypal_payment_redirection',  [$this, 'crlms_after_paypal_payment_redirection']);
		add_action('wp_ajax_nopriv_crlms_after_paypal_payment_redirection', [$this, 'crlms_after_paypal_payment_redirection']);

		add_action('wp_ajax_crlms_after_paypal_subscription_redirection',  [$this, 'crlms_after_paypal_subscription_redirection']);
		add_action('wp_ajax_nopriv_crlms_after_paypal_subscription_redirection', [$this, 'crlms_after_paypal_subscription_redirection']);

		add_action('wp_ajax_crlms_process_order',  [$this, 'process_purchase']);
		add_action('wp_ajax_nopriv_crlms_process_order', [$this, 'process_purchase']);

		add_action('wp_ajax_crlms_save_membership_details',  [$this, 'crlms_save_membership_details']);

		add_action('wp_ajax_crlms_save_course_general_settings',  ['CreatorLms\Course\CourseHelper', 'crlms_save_course_general_settings']);
	}

	public function crlms_add_to_cart_handler() {

		$itemId = (int)sanitize_text_field($_POST['itemId']);

		try {
			CRLMS()->order_loader->cart->add_to_cart($itemId);
		}
		catch (\Exception $e) {
			$response = [
				'status' => 'error',
				'message' => __($e->getMessage(), 'creator-lms'),
			];
			wp_send_json($response);
		}

		$response = [
			'status' => 'success',
			'message' => __('Successfully added to cart', 'creator-lms'),
		];

		wp_send_json($response);

	}

	public function crlms_after_paypal_payment_redirection() {
		if (!is_user_logged_in() || !current_user_can('subscriber')) {
			$response = [
				'status' => 'error',
				'message' => __('Unauthorized', 'creator-lms'),
			];

			wp_send_json($response);
		}

		$orderID = sanitize_text_field($_POST['paypal_order_id']);
		$original_order_id = sanitize_text_field($_POST['original_order']);

		$paypal_config = new PaypalGateway ;
		$settings = $paypal_config->get_saved_settings();

		if(!$settings) {
			return [
				'status' => 'error',
				'message' => __('No valid configuration found. ', 'creator-lms'),
			];
		}

		$paypal = ecommerce()->paypal($settings['client_id'], $settings['client_secret'], false);

		$capture_result = $paypal->capture_order($orderID);

		if ($capture_result && isset($capture_result->status) && $capture_result->status == 'COMPLETED') {

			$order = ecommerce()->order($original_order_id);
			$order->update_order_status('complete');

			$payment_details = [

			];
			$order->save_order_payment('paypal', 'regular', null);
			update_post_meta( $original_order_id, 'crlms_order_payment_method', 'paypal' );
			update_post_meta( $original_order_id, 'crlms_order_payment_type', 'regular' );
			update_post_meta($original_order_id, 'crlms_order_status', 'complete');
			$order_items = get_post_meta($original_order_id, 'crlms_order_items', true);

			// Process successful payment, enroll user to the course, etc.
			$response = Checkout::handle_enrollment_after_payment($original_order_id, $order_items);

			wp_send_json($response);

		}

		$response = [
			'status' => 'error',
			'message' => __('Failed to make payment.', 'creator-lms'),
		];

		wp_send_json($response);
	}

	public function crlms_after_paypal_subscription_redirection() {
		if (!is_user_logged_in() || !current_user_can('subscriber')) {
			$response = [
				'status' => 'error',
				'message' => __('Unauthorized', 'creator-lms'),
			];

			wp_send_json($response);
		}

		$subscription_id = sanitize_text_field($_POST['subscription_id']);
		$order_id = sanitize_text_field($_POST['original_order']);

		$paypal_config = new PaypalGateway ;
		$settings = $paypal_config->get_saved_settings();

		if(!$settings) {
			return [
				'status' => 'error',
				'message' => __('No valid configuration found. ', 'creator-lms'),
			];
		}

		$paypal = ecommerce()->paypal($settings['client_id'], $settings['client_secret'], false);

		$subscription = $paypal->get_subscription_details($subscription_id);
		$subscription_details = [
			'id' => $subscription_id,
			'details' => $subscription,
		];
		if ($subscription->status == 'ACTIVE') {
			$order = ecommerce()->order($order_id);
			$order->update_order_status('complete');
//			$order->save_order_payment('paypal', 'subscription', null, $subscription_details);

//			$order_items = get_post_meta($order_id, 'crlms_order_items', true);

			// Process successful payment, enroll user to the course, etc.
//			$response = Checkout::handle_enrollment_after_payment($original_order_id, $order_items);

			wp_send_json($response);
		}

	}

	/**
	 * Saved membership details
	 * @return void
	 * @since 1.0.0
	 */
	public function crlms_save_membership_details()
	{

		check_ajax_referer( 'crlms-membership', 'nonce' );

		if ( ! current_user_can( 'edit_post', $_POST['post_id'] ) ) {
			$response = [
				'status' => 'error',
				'message' => __('You do not have permission to edit this post.', 'creator-lms'),
			];

			wp_send_json($response);
		}

		$membership_id = $_POST['post_id'];
		$membership_plans = $_POST['membership_plans'];

		$response = MembershipHelper::save_membership_details($membership_id, $membership_plans);

		wp_send_json($response);
	}


	public function process_purchase() {
		// Create order
		$cart = CRLMS()->order_loader->cart->get_cart();
		$name = sanitize_text_field($_POST['name']);
		$email = sanitize_email($_POST['email']);

		$order_id = wp_insert_post(
			array(
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type'		=> 'crlms-order',
				'post_content' 	=> 'This is the updated content.',
			)
		);
		wp_update_post(
			array(
				'ID'           => $order_id,
				'post_title'   => "Order #".$order_id,
			)
		);

		// Create user if not logged in
		$user_id = $_POST['user_id'];
		if ( !$user_id ) {
			// Create a new user with 'subscriber' role
			$username = sanitize_user(current(explode('@', $email)));
			$password = wp_generate_password();

			$user_id = wp_create_user($username, $password, $email);

			if (is_wp_error($user_id)) {
				wp_send_json_error(array('message' => 'User creation failed.'));
				return;
			}

			// Update user meta with full name
			wp_update_user(array(
				'ID' => $user_id,
				'display_name' => $name,
				'role' => 'subscriber'
			));

			// Send email to user for password reset
			$user = get_userdata($user_id);
			$reset_key = get_password_reset_key($user);

			$reset_url = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');

			$message = "Hi $name,\n\n";
			$message .= "Thank you for your order. To set your password, please click on the following link:\n\n";
			$message .= $reset_url . "\n\n";
			$message .= "If you did not request this, please ignore this email.\n\n";
			$message .= "Thanks,\n";
			$message .= "The Team";

			wp_mail($email, 'Set Your Password', $message);
		}


		$redirect_url = add_query_arg(array(
			'order_id' => $order_id,
		), home_url('/order-confirmation/'));


		CRLMS()->order_loader->cart->empty_cart();

		$response = array(
			'user_id' => $user_id,
			'name' => $name,
			'email' => $email,
			'cart' => $cart,
			'redirect'=> $redirect_url
		);

		wp_send_json_success($response);

	}
}
