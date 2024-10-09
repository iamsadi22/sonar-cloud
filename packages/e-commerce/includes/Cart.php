<?php

namespace CodeRex\Ecommerce;

defined( 'ABSPATH' ) || exit;

class Cart {

	/**
	 * Contains cart contents
	 *
	 * @var array
	 */
	public $cart_contents = array();

	/**
	 * Session object
	 *
	 * @var CartSession
	 */
	public $session;


	/**
	 * @throws \Exception
	 */
	public function __construct() {
		$this->session  = new CartSession( $this );
		$this->session->init();
		add_action( 'creator_lms_add_to_cart', array( $this, 'calculate_totals' ), 10, 0 );
	}


	/**
	 * Set the contents of the cart
	 *
	 * @param array $value - cart array
	 * @return void
	 */
	public function set_cart_contents( $value ) {
		$this->cart_contents = (array) $value;
	}


	/**
	 * Get contents of the cart
	 *
	 * @return array
	 */
	public function get_cart_contents () {
		return $this->cart_contents;
	}


	/**
	 * Get cart data
	 *
	 * @return array
	 */
	public function get_cart() {
		if ( ! did_action( 'wp_loaded' ) ) {

		}
		if ( ! did_action( 'creator_lms_load_cart_from_session' ) ) {
//			$this->get_cart_from_session();
		}

		return array_filter( $this->get_cart_contents() );
	}



	/**
	 * Generate a unique id for cart
	 *
	 * @param int $course_id Id of course
	 * @param array $cart_item_data other cart item data
	 * @return string cart item key
	 *
	 * @since 1.0.0
	 */
	public function generate_cart_id( $course_id, $cart_item_data ) {
		$id_parts = array( $course_id );

		if ( is_array( $cart_item_data ) && ! empty( $cart_item_data ) ) {
			$cart_item_data_key = '';
			foreach ( $cart_item_data as $key => $value ) {
				if ( is_array( $value ) || is_object( $value ) ) {
					$value = http_build_query( $value );
				}
				$cart_item_data_key .= trim( $key ) . trim( $value );

			}
			$id_parts[] = $cart_item_data_key;
		}

		return md5( implode( '_', $id_parts ) );
	}



	/**
	 * Check if course is in the cart and return the cart item key
	 *
	 * @param mixed $cart_id id of course to find in the cart.
	 * @return string cart item key
	 *
	 * @since 1.0.0
	 */
	public function find_product_in_cart( $cart_id = false ) {
		if ( false !== $cart_id ) {
			if ( is_array( $this->cart_contents ) && isset( $this->cart_contents[ $cart_id ] ) ) {
				return $cart_id;
			}
		}
		return '';
	}


	/**
	 * Add to cart functionality of course
	 *
	 * @param $course_id
	 * @param int $quantity
	 * @param array $cart_item_data
	 * @return bool|string
	 *
	 * @since 1.0.0
	 */
	public function add_to_cart( $course_id, $quantity = 1, $cart_item_data = array() ) {
		try {

			$course_id		= absint( $course_id );
			$cart_id 		= $this->generate_cart_id( $course_id, $cart_item_data );
			$cart_item_key 	= $this->find_product_in_cart( $cart_id );

			if ( !$cart_item_key ) {
				$cart_item_key = $cart_id;
			}

			$this->cart_contents[ $cart_item_key ] = apply_filters(
				'creator_lms_add_cart_item',
				array_merge(
					$cart_item_data,
					array(
						'key'		=> $cart_item_key,
						'course_id'	=> $course_id,
					)
				),
				$cart_item_key
			);

			do_action( 'creator_lms_add_to_cart', $cart_item_key, $quantity, $course_id, $cart_item_data );

			return $cart_item_key;

		} catch ( \Exception $e ) {
			if ( $e->getMessage() ) {
				crlms_add_notice( $e->getMessage(), 'error' );
			}
			return false;
		}
	}


	public function calculate_totals() {

		if ( $this->is_empty() ) {
			$this->session->set_session();
			return;
		}

		do_action( 'creator_lms_before_calculate_totals', $this );

		$total =  self::get_total($this->get_cart_contents());

		do_action( 'creator_lms_after_calculate_totals', $this );

		return $total;
	}


	/**
	 * Check if cart is empty or not
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_empty() {
		return 0 === count( $this->get_cart() );
	}


	/**
	 * Returns the hash based on cart contents
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_cart_hash() {
		$cart_session = $this->session->get_cart_for_session();
		return $cart_session ? md5( wp_json_encode( $cart_session ) ) : '';
	}


	/**
	 * Empty cart data
	 *
	 * @param bool $clear_persistent_cart
	 */
	public function empty_cart( $clear_persistent_cart = true ) {

		do_action( 'creator_lms_before_cart_emptied', $clear_persistent_cart );


		$this->cart_contents              = array();
		if ( $clear_persistent_cart ) {
			$this->session->persistent_cart_destroy();
		}

		do_action( 'creator_lms_cart_emptied', $clear_persistent_cart );
	}


	/**
	 * Gets cart total after calculation
	 *
	 * @param array $cart_data
	 * @param int $discount
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public static function get_total($cart_data = [], $discount = 0) {
		$total_price = 0;

		do_action( 'creator_lms_before_calculate_totals', $cart_data );

		if(empty($cart_data)) {
			return $total_price;
		}

		foreach ($cart_data as $cart_item) {
			if(isset($cart_item['data'])) {
				$executable_price = $cart_item['data']->get_price();
				$total_price = isset($cart_item['quantity']) ? $executable_price * $cart_item['quantity'] : $executable_price;
			}
		}

		if ($discount > 0) {
			if ($discount >= $total_price) {
				return 0;
			}

			$total_price = $total_price - $discount;
		}

		do_action( 'creator_lms_after_calculate_totals', $cart_data );

		return $total_price;
	}


	/**
	 * Looks at the totals to see if payment is actually required.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function needs_payment($membership_mode = false) {
		if($membership_mode) {
			return true;
		}
		return apply_filters( 'creator_lms_cart_needs_payment', 0 < self::get_total($this->get_cart_contents()), $this );
	}
}
