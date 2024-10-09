<?php

/**
 * Course loop
 *
 * @see creator_lms_shop_loop()
 */
add_action( 'creator_lms_no_products_found', 'creator_lms_no_products_found' );



/**
 * Course loop items
 *
 * @see creator_lms_loop_course_link_open()
 * @see creator_lms_loop_course_link_close()
 * @see creator_lms_loop_course_title()
 * @see creator_lms_loop_add_to_cart()
 * @see creator_lms_loop_difficulty_level()
 * @see creator_lms_loop_duration()
 * @see creator_lms_loop_price()
 * @see creator_lms_pagination_after_course()
 */
add_action( 'creator_lms_before_courses_loop_item_title', 'creator_lms_loop_course_link_open', 10 );
add_action( 'creator_lms_courses_loop_item_title', 'creator_lms_loop_course_title', 10 );
add_action( 'creator_lms_after_courses_loop_item_title', 'creator_lms_loop_course_link_close', 5 );
add_action( 'creator_lms_after_courses_loop_item_title', 'creator_lms_loop_difficulty_level', 10 );
add_action( 'creator_lms_after_courses_loop_item_title', 'creator_lms_loop_duration', 10 );
add_action( 'creator_lms_after_courses_loop_item_title', 'creator_lms_loop_price', 10 );
add_action( 'creator_lms_after_courses_loop_item', 'creator_lms_loop_add_to_cart', 10 );
add_action( 'creator_lms_pagination_after_course', 'creator_lms_pagination_after_course', 10 );



/**
 * Checkout form
 *
 * @see creator_lms_checkout_guest_checkout()
 * @see creator_lms_student_details()
 * @see creator_lms_checkout_order_review()
 * @see creator_lms_checkout_payment()
 *
 * @since 1.0.0
 */
add_action( 'creator_lms_before_checkout_form', 'creator_lms_checkout_guest_checkout', 5 );
add_action( 'creator_lms_before_checkout_form', 'creator_lms_checkout_account_login', 10 );
add_action( 'creator_lms_checkout_billing_form', 'creator_lms_checkout_billing_form');
add_action( 'creator_lms_checkout_order_review', 'creator_lms_checkout_order_review');
add_action( 'creator_lms_checkout_order_review', 'creator_lms_checkout_payment');
add_action( 'creator_lms_checkout_order_summary', 'creator_lms_checkout_order_summary');


/**
 * Guest checkout
 *
 * @see creator_lms_checkout_authentication
 *
 * @since 1.0.0
 */
add_action( 'creator_lms_after_guest_checkout_email_field', 'creator_lms_checkout_authentication' );
