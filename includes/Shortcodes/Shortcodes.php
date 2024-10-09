<?php

namespace CreatorLms\Shortcodes;

/**
 * Class Shortcodes
 * @package CreatorLms\Shortcodes
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || exit;

class Shortcodes {

	/**
	 * Init shortcodes
	 */
	public static function init() {
		$shortcodes = array(
			'creator_lms_checkout'	=> __CLASS__ . '::checkout',
			'creator_lms_membership_plan'	=> __CLASS__ . '::membership_plan',
			'creator_lms_course_list'	=> __CLASS__ . '::course_list',
			'creator_lms_dashboard'	=> __CLASS__ . '::dashboard',
		);
		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}


	/**
	 * Shortcode wrapper
	 *
	 * @param $function
	 * @param array $atts
	 * @param array $wrapper
	 * @return false|string
	 * @since 1.0.0
	 */
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'creator-lms',
			'before' => null,
			'after'  => null,
		)
	) {
		ob_start();

		echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];
		return ob_get_clean();
	}


	/**
	 * Checkout page shortcode.
	 *
	 * @param $atts
	 * @return false|string
	 * @since 1.0.0
	 */
	public static function checkout( $atts ) {
		return self::shortcode_wrapper( array( 'CreatorLms\Shortcodes\ShortCodeCheckout', 'output' ), $atts );
	}

	/**
	 * Membership plan shortcode
	 * @param $atts
	 * @return false|string
	 * @since 1.0.0
	 */
	public static function membership_plan( $atts ) {
		return self::shortcode_wrapper( array( 'CreatorLms\Shortcodes\ShortCodeMembershipPlan', 'output' ), $atts );
	}

	/**
	 * Course list shortcode
	 * @param $atts
	 * @return false|string
	 * @since 1.0.0
	 */
	public static function course_list( $atts ) {
		return self::shortcode_wrapper( array( 'CreatorLms\Shortcodes\ShortcodeCourseList', 'output' ), $atts );
	}


	/**
	 * Dashboard page shortcode.
	 *
	 * @param array $atts
	 * @return false|string
	 * @since 1.0.0
	 */
	public static function dashboard($atts) {
		return self::shortcode_wrapper( array( 'CreatorLms\Shortcodes\ShortCodeDashboard', 'output' ), $atts );
	}
}
