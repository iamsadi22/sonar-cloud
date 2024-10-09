<?php

namespace Factory;

use CreatorLms\Order\Factory\Course;
use CreatorLms\Order\Order;

class OrderFactory {

	/**
	 * Get course object
	 *
	 * @param bool $course_id
	 * @return Course
	 *
	 * @since 1.0.0
	 */
	public function get_order( $order_id = false ) {
		$course_id = $this->get_order_id( $order_id );

		if ( ! $course_id ) {
			return false;
		}

		return new Order( $order_id );
	}


	/**
	 * Get course id
	 *
	 * @param $course
	 * @return bool|int
	 */
	private function get_order_id( $order ) {
		global $post;

		if ( false === $order && isset( $post, $post->ID ) && 'crlms_order' === get_post_type( $post->ID ) ) {
			return absint( $post->ID );
		} elseif ( is_numeric( $order ) ) {
			return $order;
		} elseif ( $order instanceof Order ) {
			return $order->get_id();
		} elseif ( ! empty( $order->ID ) ) {
			return $order->ID;
		} else {
			return false;
		}
	}
}
