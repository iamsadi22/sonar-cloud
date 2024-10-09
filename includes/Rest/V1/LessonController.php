<?php
namespace CreatorLms\Rest\V1;

use CreatorLms\Abstracts\RestController;
use CreatorLms\Data\Lesson;
use CreatorLms\DataException;
use CreatorLms\Lesson\LessonHelper;
use CreatorLms\Lesson\LessonValidator;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use WP_HTTP_Response;

/**
 * Controller for handling lesson REST API endpoints.
 *
 * This class extends the RESTController abstract class and defines REST API routes
 * for lesson-related CRUD operations and many more.
 *
 * @since 1.0.0
 */
class LessonController extends RestController {

	/**
	 * The base route for lesson base endpoints.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $base = 'lessons';

	/**
	 * Check if the current user has permission to edit posts.
	 *
	 * This function checks if the current user has the 'edit_posts' capability.
	 *
	 * @return bool True if the user has the 'edit_posts' capability, false otherwise.
	 * @since 1.0.0
	 */
	public function check_lesson_permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Registers REST API routes for lesson operations.
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->base . '/', array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'check_lesson_permission' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
			)
		);

		register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)' , array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the lesson.', 'creator-lms' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'check_lesson_permission' ),
				'args'                => $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array($this, 'delete_item'),
				'permission_callback' => array($this, 'check_lesson_permission'),
			),
		) );

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/content/save/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_lesson_content' ),
					'permission_callback' => array( $this, 'check_lesson_permission' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/content/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_lesson_content' ),
					'permission_callback' => array( $this, 'check_lesson_permission' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);
	}

	/**
	 * Add lesson
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function create_item( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error( "creator_lms_rest_lesson_exists", sprintf( __( 'Cannot create existing %s.', 'creator-lms' ), 'Lesson' ), array( 'status' => 400 ) );
		}
		try {
			$lesson_id 	= $this->save_lesson( $request );
			$post       = get_post( $lesson_id );
			/**
			 * Fires after a Lesson is inserted via the REST API.
			 *
			 * @param \WP_Post         $post    The post object for the lesson.
			 * @param \WP_REST_Request $request The request object.
			 * @param bool             $creating Whether the lesson is being created (true) or updated (false).
			 *
			 * @since 1.0.0
			 */
			do_action( 'creator_lms_rest_insert_lesson', $post, $request, true );

			$request->set_param( 'context', 'edit' );
			$response = $this->prepare_item_for_response( $post, $request );
			$response = rest_ensure_response( $response );
			if ( ! is_wp_error( $response ) ) {
				$response->set_status( 201 );
			}
			return $response;
		} catch ( DataException $e ) {
			return new WP_Error( 400, $e->getMessage(), array( 'status' => $e->getCode() ) );
		}
	}

	/**
	 * Update lesson settings
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function update_item( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$post_id = (int) $request['id'];

		if ( empty( $post_id ) || get_post_type( $post_id ) !== CREATOR_LMS_LESSON_CPT ) {
			return new WP_Error( "creator_lms_rest_lesson_invalid_id", __( 'ID is invalid.', 'creator-lms' ), array( 'status' => 400 ) );
		}

		try {
			$lesson_id 	= $this->save_lesson( $request );
			$post       = get_post( $lesson_id );
			$this->update_additional_fields_for_object( $post, $request );
			$this->update_post_meta_fields( $post, $request );
			$request->set_param( 'context', 'edit' );
			$response = $this->prepare_item_for_response( $post, $request );
			return rest_ensure_response( $response );

		} catch ( DataException $e ) {
			return new WP_Error( $e->getErrorCode(), $e->getMessage(), $e->getErrorData() );
		}
	}


	/**
	 * Update post meta fields for a lesson.
	 *
	 * This method updates the meta fields for a given lesson post based on the provided request data.
	 *
	 * @param \WP_Post $post The post object representing the lesson.
	 * @param \WP_REST_Request $request The REST request object containing the meta data.
	 * @return bool True on success, false on failure.
	 *
	 * @throws DataException
	 * @since 1.0.0
	 */
	protected function update_post_meta_fields( $post, $request ) {
		$lesson = crlms_get_lesson( $post );

		if ( isset( $request['image_id'] ) && intval( $request['image_id'] ) > 0 ) {
			$lesson = $this->set_lesson_cover_image( $lesson, $request['image_id'] );
		}

		// Save lesson meta fields.
		$lesson = $this->set_lesson_meta( $lesson, $request );

		// Save the lesson data.
		$lesson->save();

		/**
		 * Fires after the meta data for a lesson is updated.
		 *
		 * @param WP_Post $lesson The updated lesson object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_rest_lesson_meta_updated', $lesson );

		return true;
	}


	/**
	 * Set the cover image for a lesson.
	 *
	 * @param lesson $lesson The Lesson object.
	 * @param int $attachment_id The attachment ID of the image.
	 * @return lesson The updated lesson object.
	 * @throws DataException If the attachment ID is not a valid image.
	 *
	 * @since 1.0.0
	 */
	protected function set_lesson_cover_image( $lesson, $attachment_id ) {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			throw new DataException( 'creator_lms_lesson_invalid_image_id', sprintf( __( '#%s is an invalid image ID.', 'creator-lms' ), $attachment_id ), 400 );
		}

		$lesson->set_thumbnail_id( $attachment_id );

		return $lesson;
	}

	/**
	 * Set product meta data for a lesson.
	 *
	 * @param Lesson $lesson The lesson object.
	 * @param WP_REST_Request $request The REST request object containing the meta data.
	 * @return Lesson The updated lesson object.
	 *
	 * @since 1.0.0
	 */
	protected function set_lesson_meta( $lesson, $request ) {
		if ( isset( $request['type'] ) ) {
			$lesson->set_type( $request['type'] );
		}
		return $lesson;
	}


	/**
	 * Save lesson settings
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function save_lesson_content( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$params = $request->get_json_params();
		$post_id = (int)$request['id'];

		$response = LessonHelper::save_lesson($post_id, $params);

		return rest_ensure_response( $response );
	}

	/**
	 * Get lesson content
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function get_lesson_content( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$post_id = (int)$request['id'];

		$response = LessonHelper::get_lesson_content($post_id);

		return rest_ensure_response( $response );
	}

	/**
	 * Delete a single lesson.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 *
	 * @since 1.0.0
	 */
	public function delete_item( $request ): WP_Error|WP_REST_Response|WP_HTTP_Response
	{
		$lesson_id = isset( $request['id'] ) ? (int)$request['id'] : 0;

		if ( !$lesson_id ) {
			return new WP_Error( "creator_lms_rest_lesson_empty_id", __( 'ID is required.', 'creator-lms' ), array( 'status' => 400 ) );
		}

		$lesson = crlms_get_lesson( $lesson_id );

		if( !($lesson instanceof Lesson) ) {
			return new WP_Error( "creator_lms_rest_lesson_invalid_id", __( 'ID is invalid.', 'creator-lms' ), array( 'status' => 400 ) );
		}

		$lesson->delete();

		/**
		 * Executes the 'creator_lms_rest_delete_lesson' action hook.
		 * This hook is triggered when a lesson is being deleted via the REST API.
		 *
		 * @param array $request The request array.
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_rest_delete_lesson', $request );

		$response = array(
			'status'  => 'success',
			'message' => __( 'Lesson has been deleted successfully.', 'creator-lms' ),
		);
		return rest_ensure_response( $response );
	}

	/**
	 * Save lesson
	 * @param WP_REST_Request $request
	 * @return bool
	 * @since 1.0.0
	 */
	public function save_lesson($request)
	{
		$lesson = $this->prepare_item_for_database( $request );
		return $lesson->save();
	}

	/**
	 * Prepare a lesson for database.
	 *
	 * @param WP_REST_Request $request
	 * @return Lesson
	 *
	 * @since 1.0.0
	 */
	protected function prepare_item_for_database($request ) {
		$id = isset( $request['id'] ) ? absint( $request['id'] ) : 0;

		if ( isset( $request['id'] ) ) {
			$lesson = crlms_get_lesson( $id );
		} else {
			$lesson = new Lesson();
		}

		if ( isset( $request['name'] ) ) {
			$lesson->set_name( wp_filter_post_kses( $request['name'] ) );
		}

		if ( isset( $request['description'] ) ) {
			$lesson->set_description( wp_filter_post_kses( $request['description'] ) );
		}

		if ( isset( $request['slug'] ) ) {
			$lesson->set_slug( wp_filter_post_kses( $request['slug'] ) );
		}

		if ( isset( $request['status'] ) ) {
			$lesson->set_status( get_post_status_object( $request['status'] ) ? $request['status'] : 'draft' );
		}
		
		if ( isset( $request['type'] ) ) {
			$lesson->set_status( $request['type'] );
		}
		
		return $lesson;
	}


	/**
	 * Get lesson data.
	 *
	 * @param Lesson $lesson
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected function get_lesson_data( $lesson ) {
		$data = array(
			'id'          	=> $lesson->get_id(),
			'name'        	=> $lesson->get_name(),
			'slug'        	=> $lesson->get_slug(),
			'status'      	=> $lesson->get_status(),
			'type'      	=> $lesson->get_type()
		);

		return $data;
	}


	/**
	 * Prepare a single lesson for response.
	 *
	 * @param \WP_Post $post The post object.
	 * @param \WP_REST_Request $request
	 * @return WP_REST_Response
	 *
	 * @since 1.0.0
	 */
	public function prepare_item_for_response( $post, $request ) {
		$lesson		= crlms_get_lesson( $post->ID );
		$data    	= $this->get_lesson_data( $lesson );
		$response 	= rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $lesson, $request ) );

		/**
		 * Filters the response for the lesson in the REST API.
		 *
		 * This filter allows developers to modify the lesson response data before it is returned by the REST API.
		 *
		 * @param array $response The response data for the lesson.
		 * @param \WP_Post $post The WP_Post object representing the lesson.
		 * @param \WP_REST_Request $request The request object containing information about the API request.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( "creator_lms_rest_prepare_lesson", $response, $post, $request );
	}


	/**
	 * Prepare links for the request.
	 *
	 * @param $product
	 * @param $request
	 * @return array[]
	 *
	 * @since 1.0.0
	 */
	protected function prepare_links( $product, $request ) {
		$links = array(
			'self' => array(
				'href' => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->base, $product->get_id() ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->base ) ),
			),
		);

		return $links;
	}
}
