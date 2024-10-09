<?php

namespace CreatorLms\Shortcodes;

use function CodeRex\Ecommerce\ecommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Checkout Shortcode
 *
 * Class ShortCodeCheckout
 * @package CreatorLms\Shortcodes
 * @since 1.0.0
 */
class ShortCodeCheckout {

	/**
	 * Render the checkout form
	 *
	 * @since 1.0.0
	 */
	public static function output() {
		// Check cart class is loaded or abort.
		if ( is_null( ecommerce()->cart ) ) {
			return;
		}
		self::checkout();
	}


	/**
	 * Show the checkout.
	 *
	 * @since 1.0.0
	 */
	private static function checkout() {

		// Check cart has contents.
		if ( ecommerce()->cart->is_empty() && ! is_customize_preview() && !isset($_GET['membership_id'])) {
			return;
		}

		// Calc totals.
		ecommerce()->cart->calculate_totals();

		// Get checkout object.
		$checkout = ecommerce()->checkout();

		crlms_get_template( 'checkout/form.php', array( 'checkout' => $checkout ) );
	}

}
