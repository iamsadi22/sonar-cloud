<?php
namespace CreatorLms\Rest\V1;

use CreatorLms\Abstracts\RestController;
use CreatorLms\Data\Quiz;
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
class QuizController extends RestController {

	/**
	 * The base route for lesson base endpoints.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $base = 'quiz';

	public function check_quiz_permission() {
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
					'callback'            => array( $this, 'add_quiz' ),
					'permission_callback' => array( $this, 'check_quiz_permission' ),
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
					'callback'            => array( $this, 'save_quiz' ),
					'permission_callback' => array( $this, 'check_quiz_permission' ),
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
					'callback'            => array( $this, 'delete_quiz' ),
					'permission_callback' => array( $this, 'check_quiz_permission' ),
				),
			)
		);
	}

	/**
	 * Add quiz
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function add_quiz( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$quiz_data = $request->get_json_params();

		try {
			$quiz = new Quiz(0); // New Quiz
			$quiz->set_title(sanitize_text_field($quiz_data['title']));
			$quiz->set_description(sanitize_text_field($quiz_data['description']));
			$quiz->set_questions(array_map('intval', $quiz_data['questions'] ?? []));
			$quiz->set_settings($quiz_data['settings'] ?? []);
			$quiz->save();
			$response = [
				'status' => "success",
				'message' => __( 'Quiz has been added.', 'creator-lms' ),
			];

		}
		catch ( \Throwable $exception ) {
			$response = [
				'status' => "error",
				'message' => __( 'Failed to add quiz.', 'creator-lms' ),
			];
		}

		return rest_ensure_response( $response );
	}

	/**
	 * save quiz data
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function save_quiz( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$quiz_id = (int) $request['id'];
		$quiz_data = $request->get_json_params();

		try {
			$quiz = new Quiz($quiz_id);
			$quiz->set_title(sanitize_text_field($quiz_data['title']));
			$quiz->set_description(sanitize_text_field($quiz_data['description']));
			$quiz->set_questions(array_map('intval', $quiz_data['questions'] ?? []));
			$quiz->set_settings($quiz_data['settings'] ?? []);

			$quiz->save();

			$response = [
				'status' => "success",
				'message' => __( 'Quiz has been saved.', 'creator-lms' ),
			];
		}
		catch ( \Throwable $exception ) {
			$response = [
				'status' => "error",
				'message' => __( 'Failed to save quiz.', 'creator-lms' ),
			];
		}



		return rest_ensure_response( $response );
	}

	/**
	 * Delete quiz
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function delete_quiz( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$quiz_id = (int) $request['id'];

		try {
			$quiz = new Quiz($quiz_id);
			$quiz->delete();

			$response = [
				'status' => "success",
				'message' => __( 'Quiz has been deleted.', 'creator-lms' ),
			];
		}
		catch ( \Throwable $exception ) {
			$response = [
				'status' => "error",
				'message' => __( 'Failed to delete quiz.', 'creator-lms' ),
			];
		}

		return rest_ensure_response( $response );
	}
}
