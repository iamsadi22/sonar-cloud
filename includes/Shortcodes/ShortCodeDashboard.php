<?php

namespace CreatorLms\Shortcodes;

use function CodeRex\Ecommerce\ecommerce;

defined( 'ABSPATH' ) || exit;

class ShortCodeDashboard {

	/**
	 * Render the checkout form
	 *
	 * @since 1.0.0
	 */
	public static function output( $atts ) {
		global $wp;

		// Check cart class is loaded or abort.
		if ( is_null( ecommerce()->cart ) ) {
			return;
		}

		// Show login form if not logged in.
//		if ( ! is_user_logged_in() ) {
//			crlms_get_template( 'dashboard/form-login.php' );
//			return;
//		}

		// Output the my account page.
		self::dashboard( $atts );
	}


	private static function dashboard( $atts ) {
		$args = shortcode_atts(
			$atts,
			'creator_lms_dashboard'
		);

		crlms_get_template(
			'profile/dashboard',
			array(
				'current_user' => get_user_by( 'id', get_current_user_id() )
			)
		);
	}

}
