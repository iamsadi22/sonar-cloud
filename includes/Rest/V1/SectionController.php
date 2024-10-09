<?php
namespace CreatorLms\Rest\V1;

use CreatorLms\Abstracts\RestController;
use CreatorLms\Section\SectionHelper;
use CreatorLms\Section\SectionValidator;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Controller for handling section REST API endpoints.
 *
 * This class extends the RESTController abstract class and defines REST API routes
 * for course-related CRUD operations and many more.
 *
 * @since 1.0.0
 */
class SectionController extends RestController {

	/**
	 * The base route for section base endpoints.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $base = 'chapter';

	public function check_section_permission() {
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
			'/' . $this->base . '/add',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'add_section' ),
					'permission_callback' => array( $this, 'check_section_permission' ),
					'args'                => SectionValidator::validate_add_section_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/update/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_section' ),
					'permission_callback' => array( $this, 'check_section_permission' ),
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
					'callback'            => array( $this, 'delete_section' ),
					'permission_callback' => array( $this, 'check_section_permission' ),
				),
			)
		);
	}

	/**
	 * Add section
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function add_section( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{

		$title = sanitize_text_field($request['title']);
		$description = wp_kses_post( $request->get_param('description') );

		$section = SectionHelper::add_section($title, $description);

		return rest_ensure_response( $section );
	}

	/**
	 * Update section settings
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function update_section( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$chapter_id = $request['id'];
		$title = sanitize_text_field($request->get_param('title'));
		$description = wp_kses_post( $request->get_param('description') );

		$chapter_post = array(
			'ID'           => $chapter_id,
			'post_title'   => $title,
			'post_content' => $description,
			'post_type'    => 'crlms-section',
		);

		$updated_post_id = wp_update_post($chapter_post, true);

		if (is_wp_error($updated_post_id)) {
			$response = [
				'status' => "error",
				'message' => __( 'Failed to update chapter.', 'creator-lms' ),
			];
			return rest_ensure_response( $response );
		}

		$section_data = [
			'id' => $updated_post_id,
			'title' => $title,
			'description' => $description,
		];

		$response = [
			'status' => "success",
			'message' => __( 'Successfully updated chapter', 'creator-lms' ),
			'data' => $section_data
		];

		return rest_ensure_response( $response );
	}

	/**
	 * Delete section
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function delete_section( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$chapter_id = $request['id'];

		$chapter_post = get_post($chapter_id);
		if (!$chapter_post || $chapter_post->post_type !== 'crlms-section') {
			$response = [
				'status' => "error",
				'message' => __( 'Chapter not found.', 'creator-lms' ),
			];

			return rest_ensure_response( $response );
		}

		$deleted = wp_delete_post($chapter_id, true);

		if (!$deleted) {
			$response = [
				'status' => "error",
				'message' => __( 'Failed to delete chapter.', 'creator-lms' ),
			];

			return rest_ensure_response( $response );
		}

		$response = [
			'status' => "success",
			'message' => __( 'Chapter deleted', 'creator-lms' ),
		];

		return rest_ensure_response( $response );
	}
}
