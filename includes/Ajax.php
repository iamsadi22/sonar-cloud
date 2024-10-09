<?php

namespace CreatorLms;

use CreatorLms\user\UserHelper;
use CreatorLms\user\UserValidator;
use function CodeRex\Ecommerce\ecommerce;

defined( 'ABSPATH' ) || exit();

/**
 * Ajax class
 *
 * @package CreatorLms
 * @since 1.0.0
 */
class Ajax {

	/**
	 * Init
	 *
	 * @since 1.0.0
	 */
	public static function init(): void {
		self::add_ajax_actions();
	}


	/**
	 * Add ajax actions
	 *
	 * @since 1.0.0
	 */
	public static function add_ajax_actions(): void {
		$ajax_events_nopriv = array(
			'add_to_cart',
			'checkout',
			'purchase_membership',
		);

		foreach ( $ajax_events_nopriv as $ajax_event ) {
			add_action( 'wp_ajax_creator_lms_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			add_action( 'wp_ajax_nopriv_creator_lms_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}

		$ajax_events = array(
			'search_pages'
		);
		foreach ( $ajax_events as $ajax_event ) {
			add_action( 'wp_ajax_creator_lms_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}
	}

	/**
	 * Search pages with ajax request
	 *
	 * @since 1.0.0
	 */
	public static function search_pages(): void {
		ob_start();

		check_ajax_referer( 'search-pages', 'security' );

		if ( ! current_user_can( 'manage_creator_lms' ) ) {
			wp_die( -1 );
		}

		$search_text = isset( $_GET['term'] ) ? crlms_clean( wp_unslash( $_GET['term'] ) ) : '';
		$limit       = isset( $_GET['limit'] ) ? absint( wp_unslash( $_GET['limit'] ) ) : -1;
		$exclude_ids = ! empty( $_GET['exclude'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['exclude'] ) ) : array();

		$args                 = array(
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'posts_per_page'         => $limit,
			'post_type'              => 'page',
			'post_status'            => array( 'publish', 'private', 'draft' ),
			's'                      => $search_text,
			'post__not_in'           => $exclude_ids,
		);
		$search_results_query = new \WP_Query( $args );

		$pages_results = array();
		foreach ( $search_results_query->get_posts() as $post ) {
			$pages_results[ $post->ID ] = sprintf(
			/* translators: 1: page name 2: page ID */
				__( '%1$s (ID: %2$s)', 'woocommerce' ),
				get_the_title( $post ),
				$post->ID
			);
		}

		wp_send_json( $pages_results );
	}


	public static function purchase_membership() {

		$membership_id = crlms_clean( wp_unslash( $_POST['membership_id'] ) );
		ecommerce()->membership()->request_membership($membership_id);
		die();
	}


	/**
	 * Add to cart course actions
	 *
	 * @since 1.0.0
	 */
	public static function add_to_cart(): void {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['course_id'] ) ) {
			return;
		}
		$course_id		= absint( $_POST['course_id'] );
		$quantity		= empty( $_POST['quantity'] ) ? 1 : $_POST['quantity'];
		$product_status	= get_post_status( $course_id );

		//=== validate enrollment availability before adding to cart ===//
		if (is_user_logged_in()) {
			$enrolled = UserHelper::is_user_enrolled($course_id, get_current_user_id());
			if($enrolled) {
				$response = [
					'status' => 'error',
					'message' => __('Already enrolled in this course.', 'creator-lms'),
				];

				wp_send_json($response);
			}
		}

		$course			= crlms_get_course( $course_id );
		if ( !$course->is_purchasable() ) {
			$response = [
				'status' => 'error',
				'message' => __('Sorry! This course is not purchasable.', 'creator-lms'),
			];

			wp_send_json($response);
		}

		if ( ! $course->is_in_stock() ) {
			$response = [
				'status' => 'error',
				'message' => __('Sorry! The number of enrolled students has reached its limit.', 'creator-lms'),
			];

			wp_send_json($response);
		}


		if ( false !== ecommerce()->cart->add_to_cart( $course_id, $quantity ) && 'publish' === $product_status ) {

			do_action( 'creator_lms_ajax_added_to_cart', $course_id );

			$response = [
				'status' => 'success',
				'redirect_url'	=> get_permalink( crlms_get_page_id('checkout') ),
				'message' => __('Successfully added to cart.', 'creator-lms'),
			];

			wp_send_json($response);

		} else {

			// If there was an error adding to the cart, redirect to the product page to show any errors.
			$response = [
				'status' => 'error',
				'message' => __('Add to cart failed.', 'creator-lms'),
			];

			wp_send_json($response);
		}
		// phpcs:enable
	}


	/**
	 * Place order AJAX call
	 * @return void
	 * @since 1.0.0
	 */
	public static function checkout(): void {
		ecommerce()->checkout()->process_checkout();
		die();

	}
}

Ajax::init();
