<?php

namespace CodeRex\Ecommerce;

use CodeRex\Ecommerce\Gateways\Gateways;
use CodeRex\Ecommerce\Payment\PayPalPayment;

defined( 'ABSPATH' ) || exit;

/**
 * Class CLMS_Order_Loader
 * Handles loading and instantiation of order-related classes.
 */
class Ecommerce {

	/**
	 * @var PostTypes
	 */
	public $post_types;


	/**
	 * @var SessionHandler
	 */
	public $session;


	/**
	 * @var $cart Cart
	 */
	public $cart;

	/**
	 * @var $payment Cart
	 */
	public $payment;

	/**
	 * @var Scheduler
	 */
	public $scheduler;

	/**
	 * Holds the singleton instance of this class.
	 *
	 * @var Ecommerce
	 */
	private static $instance;


	/**
	 * Singleton instance.
	 *
	 * @return CreatorLms
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 */
	public function __construct() {
		add_action('init', array($this, 'init'));
		add_action( 'add_meta_boxes', [$this, 'add_order_detail_metabox'] );
	}


	public function init() {
		$this->scheduler = new Scheduler();
		$this->register_services();
		$this->init_post_types();
		self::create_cron_jobs();
	}


	/**
	 * Register order-related services in the container.
	 *
	 * @since 1.0.0
	 */
	private function register_services() {
		$this->post_types 	= new PostTypes();
		$this->session		= new SessionHandler();
		$this->session->init();

		$this->cart			= new Cart();
	}


	/**
	 * Initialize the post types.
	 *
	 * @since 1.0.0
	 */
	public function init_post_types() {
		$this->post_types->register_post_types();
		$this->post_types->register_post_status();
	}

	/**
	 * Order detail metabox loaded
	 * @return void
	 * @since 1.0.0
	 */
	public function add_order_detail_metabox(): void
	{
		add_meta_box(
			'order_detail_meta_box',
			__( 'Order details', 'creator-lms' ),
			[$this, 'order_detail_meta_box_callback'],
			'crlms-order',
			'normal',
			'high'
		);
	}

	/**
	 * Order detail metabox callback
	 * @param $post
	 * @return void
	 * @since 1.0.0
	 */
	public function order_detail_meta_box_callback($post): void
	{
		$order = new Order($post->ID);
		$order_items = $order->get_order_items();
		$order_total = $order->get_order_amount();
		?>

		<div class="invoice-container">
			<style>
				.invoice-container {
					padding: 20px;
					border: 1px solid #ddd;
					margin-bottom: 20px;
					font-family: Arial, sans-serif;
					background-color: #f9f9f9;
					border-radius: 8px;
				}
				.invoice-header h2 {
					margin: 0 0 10px 0;
					font-size: 24px;
					color: #333;
				}
				.invoice-header p {
					margin: 5px 0;
					font-size: 14px;
					color: #666;
				}
				.invoice-table {
					width: 100%;
					border-collapse: collapse;
					margin-top: 20px;
					background-color: #fff;
					box-shadow: 0 2px 5px rgba(0,0,0,0.1);
				}
				.invoice-table th, .invoice-table td {
					border: 1px solid #ddd;
					padding: 12px;
					text-align: left;
				}
				.invoice-table th {
					background-color: #0073aa;
					color: #fff;
					font-weight: bold;
				}
				.invoice-table tfoot th {
					text-align: right;
				}
				.invoice-total {
					font-weight: bold;
					background-color: #f2f2f2;
				}
				.invoice-table tr:nth-child(even) {
					background-color: #f9f9f9;
				}
				.invoice-table tr:hover {
					background-color: #f1f1f1;
				}
			</style>
			<div class="invoice-header">
				<p><strong><?php _e('Order ID:', 'creator-lms'); ?></strong> <?php echo esc_html($post->ID); ?></p>
				<p><strong><?php _e('Date:', 'creator-lms'); ?></strong> <?php echo esc_html(get_the_date('F j, Y', $post->ID)); ?></p>
				<p><strong><?php _e('Status:', 'creator-lms'); ?></strong> <?php echo esc_html(get_post_meta($post->ID, 'crlms_order_status', true)); ?></p>
				<button id="crlms-generate-pdf" data-order-id="<?php echo esc_attr($post->ID); ?>"><?php _e('Download', 'creator-lms'); ?></button>
			</div>

			<ul>
				<h3>Billing details</h3>
				<li><strong><?php echo __('First Name: ', 'creator-lms'); ?></strong><?php echo $order->get_order_meta('crlms_billing_first_name'); ?></li>
				<li><strong><?php echo __('Last Name: ', 'creator-lms'); ?></strong><?php echo $order->get_order_meta('crlms_billing_last_name'); ?></li>
				<li><strong><?php echo __('Email: ', 'creator-lms'); ?></strong><?php echo $order->get_order_meta('crlms_billing_email'); ?></li>
				<li><strong><?php echo __('Address: ', 'creator-lms'); ?></strong><?php echo $order->get_order_meta('crlms_billing_address'); ?></li>
				<li><strong><?php echo __('City: ', 'creator-lms'); ?></strong><?php echo $order->get_order_meta('crlms_billing_city'); ?></li>
				<li><strong><?php echo __('State: ', 'creator-lms'); ?></strong><?php echo $order->get_order_meta('crlms_billing_state'); ?></li>
				<li><strong><?php echo __('Zip: ', 'creator-lms'); ?></strong><?php echo $order->get_order_meta('crlms_billing_zip'); ?></li>
				<li><strong><?php echo __('Country: ', 'creator-lms'); ?></strong><?php echo $order->get_order_meta('crlms_billing_country'); ?></li>
			</ul>

			<?php if (!empty($order_items)) : ?>
				<table class="invoice-table">
					<thead>
					<tr>
						<th><?php _e('Course', 'creator-lms'); ?></th>
						<th><?php _e('Price', 'creator-lms'); ?></th>
						<th><?php _e("Quantity", 'creator-lms'); ?></th>
						<th><?php _e('Total', 'creator-lms'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($order_items as $item) : ?>
						<?php
							$price = isset($item['data']) ? $item['data']->get_price() : 0;
							$total_price = $price * ($item['quantity'] ?? 1);
						?>

						<tr>
							<td><?php echo esc_html(get_the_title((int)$item['course_id'])); ?></td>
							<td><?php echo esc_html( '$'. number_format($price, 2)); ?></td>
							<td><?php echo esc_html($item['quantity'] ?? 1); ?></td>
							<td><?php echo esc_html( '$' . number_format($total_price, 2)); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
					<tfoot>
					<tr>
						<th colspan="3" class="invoice-total"><?php _e('Total:', 'creator-lms'); ?></th>
						<th class="invoice-total"><?php echo esc_html( '$'. number_format($order_total, 2)); ?></th>
					</tr>
					</tfoot>
				</table>
			<?php else : ?>
				<p><?php _e('No order items found.', 'creator-lms'); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Get Checkout Class.
	 *
	 * @return Checkout|null
	 */
	public function checkout() {
		return Checkout::instance();
	}

	/**
	 * Membership instance declared
	 * @return Membership|null
	 * @since 1.0.0
	 */
	public function membership() {
		return Membership::instance();
	}

	/**
	 * Get gateways class.
	 *
	 * @return \Gateways\Gateways
	 */
	public function payment_gateways() {
		return Gateways::instance();
	}

	/**
	 * Paypal payment
	 * @return PayPalPayment|null
	 * @since 1.0.0
	 */
	public function paypal($client_id, $client_secret, $is_live) {
		return PayPalPayment::instance($client_id, $client_secret, $is_live);
	}

	/**
	 * Order object
	 * @param $order_id
	 * @return Order
	 * @sincee 1.0.0
	 */
	public function order($order_id = null): Order
	{
		if($order_id) {
			return new Order($order_id);
		}
		return new Order();
	}


	/**
	 * Create cron jobs
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function create_cron_jobs() {
		if ( ! wp_next_scheduled( 'creator_lms_cleanup_sessions' ) ) {
			wp_schedule_event( time() + ( 6 * HOUR_IN_SECONDS ), 'twicedaily', 'creator_lms_cleanup_sessions' );
		}
	}

}

function ecommerce() {
	return Ecommerce::instance();
}

ecommerce(); // phpcs:ignore
