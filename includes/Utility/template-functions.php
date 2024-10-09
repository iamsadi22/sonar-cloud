<?php


/**
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 */
function crlms_template_redirect() {
	global $wp_query, $wp;

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	// When default permalinks are enabled, redirect shop page to post type archive url.
	if ( ! empty( $_GET['page_id'] ) && '' === get_option( 'permalink_structure' ) && crlms_get_page_id( 'course' ) === absint( $_GET['page_id'] ) && get_post_type_archive_link( 'crlms-course' ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'course' ) );
		exit;
	}

}
add_action( 'template_redirect', 'crlms_template_redirect' );


/**
 * Render registration fields
 *
 * @since 1.0.0
 */
function creator_lms_student_details() {
	$checkout = CRLMS()->checkout();
	crlms_get_template( 'checkout/register.php', array( 'checkout' => $checkout ) );
}


function creator_lms_checkout_order_review() {

}


/**
 * Payment method of the course
 *
 * @see 1.0.0
 */
function creator_lms_checkout_payment() {
	if ( \CodeRex\Ecommerce\ecommerce()->cart->needs_payment() || isset($_GET['membership_id']) ) {
		$available_gateways = \CodeRex\Ecommerce\ecommerce()->payment_gateways()->get_available_payment_gateways();
		\CodeRex\Ecommerce\ecommerce()->payment_gateways()->set_current_gateway( $available_gateways );
	}
	else {
		$available_gateways = array();
	}

	crlms_get_template(
		'checkout/payment.php',
		array(
			'checkout'           => \CodeRex\Ecommerce\ecommerce()->checkout(),
			'available_gateways' => $available_gateways,
			'order_button_text'  => apply_filters( 'creator_lms_order_button_text', __( 'Place order', 'creator-lms' ) ),
		)
	);
}

/**
 * Put $course as global variable when the the_post data is set
 *
 * @param $post
 * @return \CreatorLms\Course|void
 *
 * @since 1.0.0
 */
function crlms_setup_course_data( $post ) {
	unset( $GLOBALS['course'] );

	if ( is_int( $post ) ) {
		$post = get_post( $post );
	}

	if ( empty( $post->post_type ) ) {
		return;
	}

	$GLOBALS['course'] = crlms_get_course( $post );

	return $GLOBALS['course'];
}
add_action( 'the_post', 'crlms_setup_course_data' );



/**
 * Open course link
 *
 * @since 1.0.0
 */
function creator_lms_loop_course_link_open(): void {
	$link = apply_filters( 'creator_lms_loop_product_link', get_the_permalink() );

	echo '<a href="' . esc_url( $link ) . '" class="creator-lms-loop-product__link">';
}


/**
 * Close course link
 *
 * @since 1.0.0
 */
function creator_lms_loop_course_link_close(): void {
	echo '</a>';
}

/**
 * Course title
 *
 * @since 1.0.0
 */
function creator_lms_loop_course_title(): void {
	echo '<div class="course-title">' . get_the_title() . '</div>';
}


/**
 * No course found
 *
 * @since 1.0.0
 */
function creator_lms_no_products_found(): void {
	crlms_get_template( 'loop/no-course-found.php' );
}


/**
 * Add to cart button
 *
 * @since 1.0.0
 */
function creator_lms_loop_add_to_cart( $args = array() ): void {
	global $course;

	if ( $course ) {
		$defaults = array(
			'quantity'   => 1,
			'class'      => implode(
				' ',
				array_filter(
					array(
						'button',
						$course->is_purchasable() && $course->is_in_stock() ? 'add_to_cart_button enroll-button' : 'enroll-button'
					)
				)
			),
			'attributes' => array(
				'data-course_id'	=> $course->get_id(),
				'rel'              	=> 'nofollow',
			),
		);

		$args = apply_filters( 'creator_lms_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $course );
		crlms_get_template( 'loop/add-to-cart.php', $args );
	}
}

/**
 * Load difficulty level for loop item
 *
 * @return void
 * @since 1.0.0
 */
function creator_lms_loop_difficulty_level(): void {
	crlms_get_template( 'loop/difficulty-level.php' );
}

/**
 * Load duration for loop item
 *
 * @return void
 * @since 1.0.0
 */
function creator_lms_loop_duration(): void {
	crlms_get_template( 'loop/duration.php' );
}


/**
 * Load price template for loop
 *
 * @return void
 * @since 1.0.0
 */
function creator_lms_loop_price(): void {
	crlms_get_template( 'loop/price.php' );
}


/**
 * Load guest checkout email field
 *
 * @since 1.0.0
 */
function creator_lms_checkout_guest_checkout($checkout) {
	crlms_get_template(
		'checkout/guest-checkout',
		array(
			'checkout' => $checkout,
		)
	);
}


/**
 * Load login and registration field
 *
 * @param $checkout
 * @since 1.0.0
 */
function creator_lms_checkout_authentication( $checkout ) {
	crlms_get_template(
		'checkout/student-login-signup-options.php',
		array(
			'checkout' => $checkout,
		)
	);
}


/**
 * Student login form
 *
 * @param $checkout
 * @since 1.0.0
 */
function creator_lms_checkout_account_login( $checkout ) {
	crlms_get_template(
		'checkout/account-login',
		array(
			'checkout' => $checkout,
		)
	);
}

/**
 * Billing form
 * @return void
 * @since 1.0.0
 */
function creator_lms_checkout_billing_form() {
	crlms_get_template('checkout/billing-form');
}

/**
 * Order summary
 * @return void
 * @since 1.0.0
 */
function creator_lms_checkout_order_summary() {
	crlms_get_template('checkout/order-summary');
}


/**
 * Load pagination.
 *
 * @return void
 * @since 1.0.0
 */
function creator_lms_pagination_after_course() {
	crlms_get_template('pagination.php');
}
