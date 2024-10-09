<?php

namespace CreatorLms\Shortcodes;

use CreatorLms\Membership\MembershipHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Membership Plan Shortcode
 *
 * Class ShortCodeMembershipPlan
 * @package CreatorLms\Shortcodes
 * @since 1.0.0
 */
class ShortCodeMembershipPlan {

	/**
	 * Render the membership plan
	 *
	 * @since 1.0.0
	 */
	public static function output($atts): void
	{
		self::membership_plan($atts);
	}


	/**
	 * Show the plan.
	 *
	 * @since 1.0.0
	 */
	private static function membership_plan($atts)
	{
		crlms_get_template('checkout/membership-plan',  array( 'atts' => $atts ) );
	}

}
