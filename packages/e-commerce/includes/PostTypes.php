<?php

namespace CodeRex\Ecommerce;


class PostTypes  {

	/**
	 * Register all order-related post types.
	 *
	 * @since 1.0.0
	 */
	public function register_post_types() {

		$this->register_post_type(
			'crlms-order',
			array(
				'singular_name' 	=> _x( "Order", 'Post Type Singular Name', 'creator-lms' ),
				'menu_name'     	=> _x( 'Orders', 'Admin menu name', 'creator-lms' ),
			),
			array(
				'show_in_nav_menus' => false,
				'query_var'			=> false,
				'has_archive' 		=> false,
				'show_ui'           => true,
				'public'          	=> false,
				'show_in_menu'      => false,
			)
		);
		$this->register_post_type( 'crlms-coupon',
			array(
				'singular_name' => _x( "Coupon", 'Post Type Singular Name', 'creator-lms' ),
				'menu_name'     => _x( 'Coupons', 'Admin menu name', 'creator-lms' ),
			),
			array(
				'show_in_nav_menus' => false,
				'query_var'			=> false,
				'has_archive' 		=> false,
				'show_ui'           => true,
				'public'          	=> false,
				'show_in_menu'      => false,
			)
		);

		/**
		 * Action hook after creating order post types
		 */
		do_action( 'rex_after_registering_order_post_types' );
	}


	/**
	 * Register a custom post type.
	 *
	 * @param string $post_type Post type name.
	 * @param array  $custom_labels Optional. Custom labels for the post type.
	 * @param array  $custom_args Optional. Custom arguments for the post type.
	 *
	 * @since 1.0.0
	 */
	private function register_post_type( $post_type, $custom_labels = [], $custom_args = [] ) {
		$default_labels = [
			'name'               => _x( $post_type, 'Post Type General Name', 'creator-lms' ),
			'singular_name'      => _x( $post_type, 'Post Type Singular Name', 'creator-lms' ),
			'menu_name'          => __( $post_type, 'creator-lms' ),
			'name_admin_bar'     => __( $post_type, 'creator-lms' ),
			'add_new_item'       => __( 'Add New ' . $post_type, 'creator-lms' ),
			'new_item'           => __( 'New ' . $post_type, 'creator-lms' ),
			'edit_item'          => __( 'Edit ' . $post_type, 'creator-lms' ),
			'view_item'          => __( 'View ' . $post_type, 'creator-lms' ),
			'all_items'          => __( 'All ' . $post_type . 's', 'creator-lms' ),
			'search_items'       => __( 'Search ' . $post_type . 's', 'creator-lms' ),
			'not_found'          => __( 'No ' . $post_type . 's found.', 'creator-lms' ),
			'not_found_in_trash' => __( 'No ' . $post_type . 's found in Trash.', 'creator-lms' ),
		];

		$labels = wp_parse_args( $custom_labels, $default_labels );

		$default_args = [
			'label'               => __( $post_type, 'creator-lms' ),
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => [ 'slug' => $post_type ],
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ],
		];

		$args = wp_parse_args( $custom_args, $default_args );

		register_post_type( $post_type, $args );
	}



	public function register_post_status() {
		$order_statuses = array(
			'crlms-pending'    => array(
				'label'                     => _x( 'Pending payment', 'Order status', 'creator-lms' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
			),
			'crlms-processing' => array(
				'label'                     => _x( 'Processing', 'Order status', 'creator-lms' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
			),
			'crlms-on-hold'    => array(
				'label'                     => _x( 'On hold', 'Order status', 'creator-lms' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
			),
			'crlms-completed'  => array(
				'label'                     => _x( 'Completed', 'Order status', 'creator-lms' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
			),
			'crlms-cancelled'  => array(
				'label'                     => _x( 'Cancelled', 'Order status', 'creator-lms' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
			),
			'crlms-refunded'   => array(
				'label'                     => _x( 'Refunded', 'Order status', 'creator-lms' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
			),
			'crlms-failed'     => array(
				'label'                     => _x( 'Failed', 'Order status', 'creator-lms' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
			),
		);

		foreach ( $order_statuses as $order_status => $values ) {
			register_post_status( $order_status, $values );
		}
	}
}
