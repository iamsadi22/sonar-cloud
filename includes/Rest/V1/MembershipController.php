<?php
namespace CreatorLms\Rest\V1;

use CreatorLms\Abstracts\RestController;
use CreatorLms\Lesson\LessonHelper;
use CreatorLms\Lesson\LessonValidator;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Controller for handling membership REST API endpoints.
 *
 * This class extends the RESTController abstract class and defines REST API routes
 * for course-related CRUD operations and many more.
 *
 * @since 1.0.0
 */
class MembershipController extends RestController {

	/**
	 * The base route for membership base endpoints.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $base = 'membership';

	public function check_membership_permission() {
		return true;
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Registers REST API routes for course operations.
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_memberships' ),
					'permission_callback' => array( $this, 'check_membership_permission' ),
					'args'                =>  $this->get_collection_params(),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_membership_details' ),
					'permission_callback' => array( $this, 'check_membership_permission' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/save',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_membership' ),
					'permission_callback' => array( $this, 'check_membership_permission' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/delete/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_membership' ),
					'permission_callback' => array( $this, 'check_membership_permission' ),
				),
			)
		);
	}

	/**
	 * Get memberships
	 * @param $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function get_memberships($request) {

		$page = isset( $request['page'] ) ? intval( $request['page'] ) : 1;
		$per_page = isset( $request['per_page'] ) ? intval( $request['per_page'] ) : 10;
		$search_query = isset( $request['search'] ) ? sanitize_text_field( $request['search'] ) : '';

		$offset = ( $page - 1 ) * $per_page;

		$posts = get_posts( array(
			'post_type'   => 'crlms-membership',
			'posts_per_page' => $per_page,
			'offset'     => $offset,
			's'              => $search_query,
		));

		// Prepare the response
		$total_posts = wp_count_posts('crlms-membership')->publish;
		if ( $search_query ) {
			$total_posts = count( get_posts( array(
				'post_type'      => 'crlms-membership',
				'post_status'    => 'publish',
				's'              => $search_query,
				'posts_per_page' => $per_page,
			)));
		}
		$total_pages = ceil( $total_posts / $per_page );

		$response = array(
			'memberships'      => $posts,
			'total'        => $total_posts,
			'total_pages'  => $total_pages,
			'current_page' => $page,
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Get membership details
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function get_membership_details( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$id = absint($request['id']);
		$post = get_post($id);

		if ($post && $post->post_type === 'crlms-membership') {
			$membership_details = [
				'id' => $post->ID,
				'title' => $post->post_title,
				'description' => $post->post_content,
				'visibility' => ucfirst($post->post_status),
				'pricingType' => get_post_meta($post->ID, 'crlms_membership_pricing_type', true) ?: 'Free', // Default to 'Free'
				'regularPrice' => get_post_meta($post->ID, 'crlms_membership_regular_price', true) ?: 0, // Default to 0
				'billingPeriod' => get_post_meta($post->ID, 'crlms_membership_billing_period', true) ?: 'Monthly', // Default to 'Monthly'
				'thumbnail' => get_post_meta($post->ID, 'crlms_membership_thumbnail', true) ?: '', // Default to an empty string
				'selectedProducts' => apply_filters(
					'crlms_membership_selected_products',
					unserialize(get_post_meta($post->ID, 'crlms_membership_selected_products', true)) ?: [],
					$post->ID
				), // Default to an empty array and allow filters
			];

			$response = [
				'status' => "success",
				'message' => __( 'Membership details fetched', 'creator-lms' ),
				'data' => $membership_details,
			];

			return rest_ensure_response( $response );
		}

		$response = [
			'status' => "error",
			'message' => __( 'Membership details not found.', 'creator-lms' ),
		];

		return rest_ensure_response( $response );
	}

	/**
	 * Save membership
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function save_membership( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$params = $request->get_json_params();
		$id = isset($params['id']) ? absint($params['id']) : null;

		$post_data = [
			'post_title'   => sanitize_text_field($params['title']),
			'post_content' => wp_kses_post($params['description']),
			'post_status'  => isset($params['visibility']) ? sanitize_text_field($params['visibility']) : 'draft',
			'post_type'    => 'crlms-membership',
		];

		/**
		 * Fires before membership save
		 *
		 * @param number $id id to save
		 * @param array $params key value pair based items to update meta fields
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_membership_save', $id, $params );

		if ($id) {
			// Update existing post
			$post_data['ID'] = $id;
			$result = wp_update_post($post_data, true);
		} else {
			// Insert new post
			$result = wp_insert_post($post_data, true);
			$id = $result;
		}

		if (!is_wp_error($result) && $id) {

			update_post_meta($id, 'crlms_membership_pricing_type', sanitize_text_field($params['pricingType']));
			update_post_meta($id, 'crlms_membership_regular_price', floatval($params['regularPrice']));
			update_post_meta($id, 'crlms_membership_billing_period', sanitize_text_field($params['billingPeriod']));
			update_post_meta($id, 'crlms_membership_thumbnail', sanitize_text_field($params['thumbnail']));
			update_post_meta($id, 'crlms_membership_selected_products', maybe_serialize($params['selectedProducts']));


			/**
			 * Fires before membership save
			 *
			 * @param number $id id to save
			 * @param array $params key value pair based items to update meta fields
			 * @since 1.0.0
			 */
			do_action( 'crlms_after_membership_save', $id, $params );


			$response = [
				'status' => "success",
				'message' => __( 'Membership saved', 'creator-lms' ),
			];

			return rest_ensure_response( $response );
		}

		$response = [
			'status' => "success",
			'message' => __( 'Membership save failed', 'creator-lms' ),
		];

		return rest_ensure_response( $response );
	}

	/**
	 * Delete membership
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function delete_membership( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$id = absint($request['id']);
		$post = get_post( $id );

		if ( !$post || $post->post_type !== 'crlms-membership' ) {
			$response = [
				'status' => "error",
				'message' => __( 'Membership not found.', 'creator-lms' ),
			];

			return rest_ensure_response( $response );
		}

		/**
		 * Fires before membership delete
		 *
		 * @param string $post_id membership id to delete
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_membership_delete', $id );

		$deleted = wp_delete_post($id, true);

		if ($deleted) {

			/**
			 * Fires after question delete
			 *
			 * @param string $id Deleted Question id
			 * @since 1.0.0
			 */
			do_action( 'crlms_after_membership_delete', $id );

			$response = [
				'status' => "success",
				'message' => __( 'Membership deleted successfully', 'creator-lms' ),
			];

			return rest_ensure_response( $response );
		}

		$response = [
			'status' => "error",
			'message' => __( 'Failed to delete membership', 'creator-lms' ),
		];

		return rest_ensure_response( $response );
	}
}
