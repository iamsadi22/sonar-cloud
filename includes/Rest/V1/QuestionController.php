<?php
namespace CreatorLms\Rest\V1;

use CreatorLms\Abstracts\RestController;
use CreatorLms\Question\QuestionHelper;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Controller for handling lesson REST API endpoints.
 *
 * This class extends the RESTController abstract class and defines REST API routes
 * for course-related CRUD operations and many more.
 *
 * @since 1.0.0
 */
class QuestionController extends RestController {

	/**
	 * The base route for lesson base endpoints.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $base = 'question';

	public function check_question_permission() {
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
					'callback'            => array( $this, 'add_question' ),
					'permission_callback' => array( $this, 'check_question_permission' ),
					'args'                => $this->get_collection_params()
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/save/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_question' ),
					'permission_callback' => array( $this, 'check_question_permission' ),
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
					'callback'            => array( $this, 'delete_question' ),
					'permission_callback' => array( $this, 'check_question_permission' ),
				),
			)
		);
	}

	/**
	 * Add question
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function add_question( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{

		$title = sanitize_text_field($request['title']);
		$description = sanitize_text_field($request['description']);

		$response = QuestionHelper::add_question( $title, $description );

		return rest_ensure_response( $response );
	}

	/**
	 * save question data
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function save_quiz( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$params = $request->get_json_params();
		$post_id = (int)$request['id'];

		$response = QuestionHelper::save_question($post_id, $params);

		return rest_ensure_response( $response );
	}

	/**
	 * Delete question
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function delete_question( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$params = $request->get_json_params();
		$post_id = (int)$request['id'];

		$response = QuestionHelper::delete_question($post_id);

		return rest_ensure_response( $response );
	}
}
