<?php
namespace CreatorLms\Rest\V1;

use CreatorLms\Abstracts\RestController;
use CreatorLms\Course\CourseHelper;
use CreatorLms\Data\Course;
use CreatorLms\Data\Chapter;
use CreatorLms\DataException;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Controller for handling course REST API endpoints.
 *
 * This class extends the RESTController abstract class and defines REST API routes
 * for course-related CRUD operations and many more.
 *
 * @since 1.0.0
 */
class CourseController extends RestController {

	/**
	 * The base route for course base endpoints.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $base = 'courses';

	public function check_course_permission() {
		return true;
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Registers REST API routes for course operations.
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->base . '/', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'check_course_permission' ),
				'args'                =>  $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'check_course_permission' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)' , array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the course.', 'creator-lms' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'check_course_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'check_course_permission' ),
				'args'                => $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'check_course_permission' ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>\d+)/status', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_status'),
				'permission_callback' => array($this, 'check_course_permission'),
				'args'                => array(
					'status' => array(
						'description' => __('The new status for the course.', 'creator-lms'),
						'type'        => 'string',
						'required'    => true,
					),
					'id' => array(
						'description' => __('Unique identifier for the course.', 'creator-lms'),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			),
		));

		register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/chapters' , array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the course.', 'creator-lms' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_chapters' ),
				'permission_callback' => array( $this, 'check_course_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_chapters' ),
				'permission_callback' => array( $this, 'check_course_permission' ),
			)
		) );

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/update-map/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_course_map' ),
					'permission_callback' => array( $this, 'check_course_permission' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/settings/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_course_settings' ),
					'permission_callback' => array( $this, 'check_course_permission' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/settings/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_course_settings' ),
					'permission_callback' => array( $this, 'check_course_permission' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/get-map/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_course_map' ),
					'permission_callback' => array( $this, 'check_course_permission' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/get-map/default',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_course_map_default' ),
					'permission_callback' => array( $this, 'check_course_permission' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/filtered-courses',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_filtered_courses' ),
					'permission_callback' => array( $this, 'check_course_permission' ),
					'args'                =>  $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/featured_image/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_featured_image' ),
					'permission_callback' => array( $this, 'check_course_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'remove_featured_image' ),
					'permission_callback' => array( $this, 'check_course_permission' ),
				),
			)
		);

	}

	/**
	 * Check if a given request has access to read an item.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 *
	 * @since 1.0.0
	 */
	public function get_item_permissions_check( $request ) {
		$post = get_post( (int) $request['id'] );

		if ( $post && ! current_user_can( 'read_post', $post->ID ) ) {
			return new WP_Error( 'creator_lms_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'creator-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}


	/**
	 * Get collection of courses.
	 *
	 * This method handles the retrieval of courses based on the provided request parameters.
	 * It supports various filters and pagination options to customize the query.
	 *
	 * @param \WP_REST_Request $request The REST request object containing query parameters.
	 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response The response object containing the courses data or an error.
	 *
	 * @since 1.0.0
	 */
	public function get_items( $request ) {
		$args = array(
			'offset' 				=> isset($request['offset']) ? intval($request['offset']) : 0,
			'order' 				=> isset($request['order']) ? sanitize_text_field($request['order']) : 'DESC',
			'orderby' 				=> isset($request['orderby']) ? sanitize_text_field($request['orderby']) : 'date',
			'paged' 				=> isset($request['page']) ? intval($request['page']) : 1,
			'post__in' 				=> isset($request['include']) ? array_map('intval', (array) $request['include']) : array(),
			'post__not_in' 			=> isset($request['exclude']) ? array_map('intval', (array) $request['exclude']) : array(),
			'posts_per_page'		=> isset($request['per_page']) ? intval($request['per_page']) : 10,
			'name' 					=> isset($request['slug']) ? sanitize_text_field($request['slug']) : '',
			'post_parent__in' 		=> isset($request['parent']) ? array_map('intval', (array) $request['parent']) : array(),
			'post_parent__not_in' 	=> isset($request['parent_exclude']) ? array_map('intval', (array) $request['parent_exclude']) : array(),
			's' 					=> isset($request['search']) ? sanitize_text_field($request['search']) : '',
			'post_type' 			=> CREATOR_LMS_COURSE_CPT,
			'post_status'           => isset($request['post_status']) ? sanitize_text_field($request['post_status']) : 'any',
		);

		$args['date_query'] = array();
		if (isset($request['before'])) {
			$args['date_query'][0]['before'] = sanitize_text_field($request['before']);
		}
		if (isset($request['after'])) {
			$args['date_query'][0]['after'] = sanitize_text_field($request['after']);
		}

		if (isset($request['filter']) && is_array($request['filter'])) {
			$args = array_merge($args, $request['filter']);
			unset($args['filter']);
		}

		$args = apply_filters("creator_lms_rest_crlms_course_query", $args, $request);
		$query_args = $this->prepare_items_query($args, $request);

		$posts_query = new WP_Query();
		$query_result = $posts_query->query($query_args);

		$posts = array();
		foreach ($query_result as $post) {
			if (!current_user_can('read_post', $post->ID)) {
				continue;
			}
			$data = $this->prepare_item_for_response($post, $request);
			$posts[] = $this->prepare_response_for_collection($data);
		}

		$page = (int) $query_args['paged'];
		$total_posts = $posts_query->found_posts;

		if ($total_posts < 1 && $page > 1) {
			unset($query_args['paged']);
			$count_query = new WP_Query();
			$count_query->query($query_args);
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil($total_posts / (int) $query_args['posts_per_page']);

		$response = rest_ensure_response($posts);
		$response->header('X-WP-Total', (int) $total_posts);
		$response->header('X-WP-TotalPages', (int) $max_pages);

		$request_params = $request->get_query_params();
		if (!empty($request_params['filter'])) {
			unset($request_params['filter']['posts_per_page']);
			unset($request_params['filter']['paged']);
		}
		$base = add_query_arg($request_params, rest_url(sprintf('/%s/%s', $this->namespace, $this->rest_base)));

		if ($page > 1) {
			$prev_page = $page - 1;
			if ($prev_page > $max_pages) {
				$prev_page = $max_pages;
			}
			$prev_link = add_query_arg('page', $prev_page, $base);
			$response->link_header('prev', $prev_link);
		}
		if ($max_pages > $page) {
			$next_page = $page + 1;
			$next_link = add_query_arg('page', $next_page, $base);
			$response->link_header('next', $next_link);
		}
		return $response;
	}


	/**
	 * Create a single course
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 *
	 * @since 1.0.0
	 */
	public function create_item( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error( "creator_lms_rest_course_exists", sprintf( __( 'Cannot create existing %s.', 'creator-lms' ), 'course' ), array( 'status' => 400 ) );
		}

		try {
			$course_id 	= $this->save_course( $request );
			$post       = get_post( $course_id );

			/**
			 * Fires after a course is inserted via the REST API.
			 *
			 * @param \WP_Post         $post    The post object for the course.
			 * @param \WP_REST_Request $request The request object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'creator_lms_rest_insert_course', $post, $request );

			$request->set_param( 'context', 'edit' );
			$response = $this->prepare_item_for_response( $post, $request );
			$response = rest_ensure_response( $response );
			$response->set_status( 201 );
			return $response;
		} catch ( DataException $e ) {
			return new WP_Error( 400, $e->getMessage(), array( 'status' => $e->getCode() ) );
		}
	}


	/**
	 * Retrieves a single course by ID.
	 *
	 * @param \WP_REST_Request $request The REST request object containing the course ID.
	 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response The response object containing the course data or an error.
	 *
	 * @since 1.0.0
	 */
	public function get_item( $request ) {
		$id   = (int) $request['id'];
		$post = get_post( $id );

		if ( empty( $id ) || empty( $post->ID ) || $post->post_type !== CREATOR_LMS_COURSE_CPT ) {
			return new WP_Error( 'creator_lms_rest_invalid_course_id', __( 'Invalid ID.', 'creator-lms' ), array( 'status' => 404 ) );
		}

		$data 		= $this->prepare_item_for_response( $post, $request );
		$response 	= rest_ensure_response( $data );

		$response->link_header( 'alternate', get_permalink( $id ), array( 'type' => 'text/html' ) );

		return $response;
	}


	/**
	 * Updates a single course.
	 *
	 * @param \WP_REST_Request $request The REST request object containing the course ID and data.
	 * @return WP_Error|\WP_REST_Response|\WP_HTTP_Response The response object containing the updated course data or an error.
	 *
	 * @since 1.0.0
	 */
	public function update_item( $request )  {
		$post_id = (int) $request['id'];

		if ( empty( $post_id ) || get_post_type( $post_id ) !== CREATOR_LMS_COURSE_CPT ) {
			return new WP_Error( "creator_lms_rest_course_invalid_id", __( 'ID is invalid.', 'creator-lms' ), array( 'status' => 400 ) );
		}

		try {
			$course_id 	= $this->save_course( $request );
			$post       = get_post( $course_id );
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
	 * Delete course
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function delete_item( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		// Get course id
		$course_id = isset($request['id']) ? (int)$request['id'] : 0;

		// Check the course id exist or not
		if ( !$course_id ) {
			return new WP_Error( "creator_lms_rest_course_empty_id", __( 'ID is required.', 'creator-lms' ), array( 'status' => 400 ) );
		}

		// Get existing course by course id
		$course = crlms_get_course( $course_id );

		// Check the course exist or not.
		if( !($course instanceof Course) ) {
			return new WP_Error( "creator_lms_rest_course_invalid_id", __( 'ID is invalid.', 'creator-lms' ), array( 'status' => 400 ) );
		}

		// Delete the course
		$course->delete();

		/**
		 * Executes the 'creator_lms_rest_delete_course' action hook.
		 * This hook is triggered when a course is being deleted via the REST API.
		 *
		 * @param string $course_id Course ID.
		 * 
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_rest_delete_course', $course_id );

		$response = array(
			'status'  => 'success',
			'message' => __( 'Course deleted successfully', 'creator-lms' ),
		);
		return rest_ensure_response( $response );
	}


	/**
	 * Retrieves the chapters of a course.
	 *
	 * This method fetches the chapters associated with a specific course ID.
	 *
	 * @param \WP_REST_Request $request The REST request object containing the course ID.
	 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response The response object containing the chapters data or an error.
	 *
	 * @since 1.0.0
	 */
	public function get_chapters( $request ) {

		$course_id = isset($request['id']) ? (int)$request['id'] : 0;

		// Check the course id exist or not
		if ( !$course_id ) {
			return new WP_Error( "creator_lms_rest_course_empty_id", __( 'ID is required.', 'creator-lms' ), array( 'status' => 400 ) );
		}

		// Get existing course by course id
		$course = crlms_get_course( $course_id );

		// Check the course exist or not.
		if( !( $course instanceof Course ) ) {
			return new WP_Error( "creator_lms_rest_course_invalid_id", __( 'ID is invalid.', 'creator-lms' ), array( 'status' => 400 ) );
		}

		$chapters = $course->get_chapters();

		$response = array(
			'status'  => 'success',
			'message' => __( 'Chapters fetched successfully', 'creator-lms' ),
			'data'    => $chapters,
		);

		return rest_ensure_response( $response );
	}


	/**
	 * Updates the chapters of a course.
	 *
	 * This method handles the updating of chapters for a specific course based on the provided request data.
	 *
	 * @param WP_REST_Request $request The REST request object containing the course ID and chapter data.
	 * @return WP_REST_Response The response object containing the result of the update operation.
	 *
	 * @since 1.0.0
	 */
	public function update_chapters( $request ) {
		$course_id = (int)$request['id'];

		// Check the course id exist or not
		if ( !$course_id ) {
			return new WP_Error( "creator_lms_rest_course_empty_id", __( 'ID is required.', 'creator-lms' ), array( 'status' => 400 ) );
		}

		// Get existing course by course id
		$course = crlms_get_course( $course_id );

		// Check the course exist or not.
		if( !( $course instanceof Course ) ) {
			return new WP_Error( "creator_lms_rest_course_invalid_id", __( 'ID is invalid.', 'creator-lms' ), array( 'status' => 400 ) );
		}
		$chapters = array();
		foreach ( $request->get_json_params() as $chapter ) {
			$chapter_obj = new Chapter( $chapter );
			$chapter_obj->set_id( $chapter['id'] );
			$chapter_obj->set_name( $chapter['name'] );
			$chapter_obj->set_description( $chapter['description'] );
			$chapter_obj->save();

			$chapters[] = $chapter;
		}

		$course->set_chapters( $chapters );
		$response = array(
			'status'  => 'success',
			'message' => __( 'Chapters updated successfully', 'creator-lms' )
		);
		return rest_ensure_response( $response );
	}


	/**
	 * Prepares the query arguments for fetching items.
	 *
	 * This function filters and constructs the query arguments based on the allowed query variables.
	 * It ensures that only valid query variables are included in the final query arguments.
	 *
	 * @param array $prepared_args The prepared arguments for the query.
	 * @param WP_REST_Request|null $request The REST request object.
	 *
	 * @return array The filtered and prepared query arguments.
	 * @since 1.0.0
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {

		$valid_vars = array_flip( $this->get_allowed_query_vars() );
		$query_args = array();
		foreach ( $valid_vars as $var => $index ) {
			if ( isset( $prepared_args[ $var ] ) ) {
				/**
				 * Filter the query_vars used in `get_items` for the constructed query.
				 *
				 * The dynamic portion of the hook name, $var, refers to the query_var key.
				 *
				 * @param mixed $prepared_args[ $var ] The query_var value.
				 */
				$query_args[ $var ] = apply_filters( "woocommerce_rest_query_var-{$var}", $prepared_args[ $var ] );
			}
		}

		$query_args['ignore_sticky_posts'] = true;

		if ( 'include' === $query_args['orderby'] ) {
			$query_args['orderby'] = 'post__in';
		} elseif ( 'id' === $query_args['orderby'] ) {
			$query_args['orderby'] = 'ID'; // ID must be capitalized.
		} elseif ( 'slug' === $query_args['orderby'] ) {
			$query_args['orderby'] = 'name';
		}

		return $query_args;
	}


	/**
	 * Get the allowed query variables for the REST API.
	 *
	 * This method retrieves the list of query variables that are allowed to be used
	 * in REST API requests for courses. It merges the public and private query variables
	 * and applies filters to allow customization.
	 *
	 * @return array The array of allowed query variables.
	 *
	 * @since 1.0.0
	 */
	protected function get_allowed_query_vars() {
		global $wp;

		/**
		 * Filter the publicly allowed query vars.
		 *
		 * Allows adjusting of the default query vars that are made public.
		 *
		 * @param array  Array of allowed WP_Query query vars.
		 */
		$valid_vars = apply_filters( 'query_vars', $wp->public_query_vars );

		$post_type_obj = get_post_type_object( CREATOR_LMS_COURSE_CPT );
		if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
			$valid_vars = array_merge( $valid_vars, $wp->private_query_vars );
		}
		$rest_valid = array(
			'date_query',
			'ignore_sticky_posts',
			'offset',
			'post__in',
			'post__not_in',
			'post_parent',
			'post_parent__in',
			'post_parent__not_in',
			'posts_per_page',
			'meta_query',
			'tax_query',
			'meta_key',
			'meta_value',
			'meta_compare',
			'meta_value_num',
		);
		$valid_vars = array_merge( $valid_vars, $rest_valid );

		/**
		 * Filter the valid query variables for the REST API.
		 *
		 * This filter allows developers to modify the list of valid query variables
		 * that can be used in REST API requests for courses.
		 *
		 * @param array $valid_vars The array of valid query variables.
		 */
		$valid_vars = apply_filters( 'creator_lms_rest_query_vars', $valid_vars );

		return $valid_vars;
	}


	/**
	 * Saves a course to the database.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public function save_course( $request ) {
		$course = $this->prepare_item_for_database( $request );
		return $course->save();
	}


	/**
	 * Update post meta fields for a course.
	 *
	 * This method updates the meta fields for a given course post based on the provided request data.
	 *
	 * @param \WP_Post $post The post object representing the course.
	 * @param \WP_REST_Request $request The REST request object containing the meta data.
	 * @return bool True on success, false on failure.
	 *
	 * @throws DataException
	 * @since 1.0.0
	 */
	protected function update_post_meta_fields( $post, $request ) {
		$course = crlms_get_course( $post );

		if ( isset( $request['image_id'] ) && intval( $request['image_id'] ) > 0 ) {
			$course = $this->set_course_cover_image( $course, $request['image_id'] );
		}

		// Save course meta fields.
		$course = $this->set_course_meta( $course, $request );

		// Save the course data.
		$course->save();

		return true;
	}


	/**
	 * Set product meta data for a course.
	 *
	 * @param Course $course The course object.
	 * @param WP_REST_Request $request The REST request object containing the meta data.
	 * @return Course The updated course object.
	 *
	 * @since 1.0.0
	 */
	protected function set_course_meta( $course, $request ) {
		if ( isset( $request['regular_price'] ) ) {
			$course->set_regular_price( $request['regular_price'] );
		}

		if ( isset( $request['sale_price'] ) ) {
			$course->set_sale_price( $request['sale_price'] );
		}

		if ( isset( $request['level'] ) ) {
			$course->set_level( $request['level'] );
		}

		if ( isset( $request['availability'] ) ) {
			$course->set_availability( $request['availability'] );
		}

		if ( isset( $request['available_date'] ) ) {
			$course->set_available_date( $request['available_date'] );
		}

		if ( isset( $request['accessibility'] ) ) {
			$course->set_accessibility( $request['accessibility'] );
		}

		if ( isset( $request['capacity'] ) ) {
			$course->set_capacity( $request['capacity'] );
		}

		if ( isset( $request['limit'] ) ) {
			$course->set_limit( $request['limit'] );
		}

		return $course;
	}


	/**
	 * Set the cover image for a course.
	 *
	 * @param Course $course The course object.
	 * @param int $attachment_id The attachment ID of the image.
	 * @return Course The updated course object.
	 * @throws DataException If the attachment ID is not a valid image.
	 *
	 * @since 1.0.0
	 */
	protected function set_course_cover_image( $course, $attachment_id ) {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			throw new DataException( 'creator_lms_course_invalid_image_id', sprintf( __( '#%s is an invalid image ID.', 'creator-lms' ), $attachment_id ), 400 );
		}

		$course->set_thumbnail_id( $attachment_id );

		return $course;
	}


	/**
	 * Update course map
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public  function update_course_map ( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$course_id = (int)$request['id'];
		$map = $request->get_json_params();

		$response = CourseHelper::update_course_map( $course_id, $map );
		return rest_ensure_response( $response );
	}

	/**
	 * Get course map
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function get_course_map ( $request ): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$course_id = (int)$request['id'];
		$response = CourseHelper::get_course_map( $course_id );
		return rest_ensure_response( $response );
	}

	/**
	 * Get default course map
	 * @param $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function get_course_map_default($request) {
		$course_id = (int)$request['id'];
		$response = CourseHelper::get_course_map( $course_id );
		return rest_ensure_response( $response );
	}

	/**
	 * Fetch course settings
	 * @param $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function get_course_settings($request): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$course_id = $request['id'];
		$course = get_post($course_id);

		if (!$course || $course->post_type !== 'crlms-course') {
			$response = [
				'status' => "error",
				'message' => __( 'Course not found.', 'creator-lms' ),
			];

			return rest_ensure_response( $response );
		}

		$settings_data = get_post_meta($course_id, 'crlms_course_settings', true);

		$response = [
			'status' => "success",
			'message' => __( 'Course settings fetched', 'creator-lms' ),
			'data' => $settings_data,
		];

		return rest_ensure_response( $response );
	}

	/**
	 * Save course settings
	 * @param $request
	 * @return WP_Error|WP_REST_Response|\WP_HTTP_Response
	 * @since 1.0.0
	 */
	public function save_course_settings($request): WP_Error|WP_REST_Response|\WP_HTTP_Response
	{
		$course_id = (int)$request['id'];
		$settings_data = $request->get_json_params();

		$response = CourseHelper::save_course_settings( $course_id, $settings_data );
		return rest_ensure_response( $response );
	}

	public function get_filtered_courses($request) {
		$args = array(
			'post_type' => 'crlms-course',
			's' => isset($request['search']) ? $request['search'] : '',
			'post__not_in' => isset($request['exclude']) ? explode(',', $request['exclude']) : array(),
		);

		$query = new WP_Query($args);

		$courses = array();

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$courses[] = array(
					'id' => get_the_ID(),
					'title' => get_the_title(),
					'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
				);
			}
		}

		wp_reset_postdata();

		return rest_ensure_response( $courses );
	}


	/**
	 * Save featured image for a course.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function save_featured_image( \WP_REST_Request $request ) {
		$course_id = (int) $request['id'];
		$attachment_id = (int) $request->get_param('attachment_id');

		if ( ! $course_id || ! $attachment_id ) {
			return new \WP_Error( 'invalid_data', __( 'Invalid course ID or attachment ID.', 'creator-lms' ), array( 'status' => 400 ) );
		}

		$result = set_post_thumbnail( $course_id, $attachment_id );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error( 'failed_to_set_thumbnail', __( 'Failed to set featured image.', 'creator-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'status'  => 'success',
			'message' => __( 'Featured image set successfully.', 'creator-lms' ),
		) );
	}


	/**
	 * Remove featured image for a course.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
	 * @since 1.0.0
	 */
	public function remove_featured_image( \WP_REST_Request $request ) {
		$course_id = (int) $request['id'];
		$result = delete_post_thumbnail( $course_id );
		if ( is_wp_error( $result ) ) {
			return new \WP_Error( 'failed_to_remove_thumbnail', __( 'Failed to remove featured image.', 'creator-lms' ), array( 'status' => 500 ) );
		}
		return rest_ensure_response( array(
			'status'  => 'success',
			'message' => __( 'Featured image removed successfully.', 'creator-lms' ),
		) );
	}


	/**
	 * Get course data.
	 *
	 * @param Course $course
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected function get_course_data( $course ) {
		$data = array(
			'id'          	=> $course->get_id(),
			'name'        	=> $course->get_name(),
			'slug'        	=> $course->get_slug(),
			'status'      	=> $course->get_status(),
			'description' 	=> $course->get_description(),
			'price' 		=> $course->get_price(),
			'regular_price' => $course->get_regular_price(),
			'sale_price' 	=> $course->get_sale_price(),
			'level' 		=> $course->get_level(),
			'availability' 	=> $course->get_availability(),
			'available_date'=> $course->get_available_date(),
			'accessibility'	=> $course->get_accessibility(),
			'capacity'		=> $course->get_capacity(),
			'limit'			=> $course->get_limit(),
			'image_id'		=> $course->get_thumbnail_id(),
			'date_created'	=> $course->get_date_created(),
			'date_modified'	=> $course->get_date_modified(),
			'image_src'		=> wp_get_attachment_image_src( $course->get_thumbnail_id(), 'large' ) ? wp_get_attachment_image_src( $course->get_thumbnail_id(), 'large' )[0] : '',
			'level'			=> $course->get_level(),
		);
		return $data;
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


	/**
	 * Prepare a single course for create or update.
	 *
	 * @param $request
	 * @return bool|Course|object|WP_Error
	 * @throws \Exception
	 * @since 1.0.0
	 */
	protected function prepare_item_for_database( $request ) {
		$id = isset( $request['id'] ) ? absint( $request['id'] ) : 0;

		if ( isset( $request['id'] ) ) {
			$course = crlms_get_course( $id );
		} else {
			$course = new Course();
		}

		if ( isset( $request['name'] ) ) {
			$course->set_name( wp_filter_post_kses( $request['name'] ) );
		}

		if ( isset( $request['description'] ) ) {
			$course->set_description( wp_filter_post_kses( $request['description'] ) );
		}

		if ( isset( $request['status'] ) ) {
			$course->set_status( get_post_status_object( $request['status'] ) ? $request['status'] : 'draft' );
		}

		return $course;
	}


	/**
	 * Prepare a single course for response.
	 *
	 * @param \WP_Post $post The post object.
	 * @param \WP_REST_Request $request
	 * @return WP_REST_Response
	 *
	 * @since 1.0.0
	 */
	public function prepare_item_for_response( $post, $request ) {

		$course 	= crlms_get_course( $post );
		$data    	= $this->get_course_data( $course );
		$response 	= rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $course, $request ) );

		/**
		 * Filters the response for the course in the REST API.
		 *
		 * This filter allows developers to modify the course response data before it is returned by the REST API.
		 *
		 * @param array $response The response data for the course.
		 * @param \WP_Post $post The WP_Post object representing the course.
		 * @param \WP_REST_Request $request The request object containing information about the API request.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( "creator_lms_rest_prepare_course", $response, $post, $request );
	}

	/**
	 * Update the status of a course.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_Error|WP_REST_Response
	 *
	 * @since 1.0.0
	 */
	public function update_status( $request ){
		// Get the status and course ID from the request.
		$status    = sanitize_text_field( $request['status'] );
		$course_id = (int) $request['id'];

		// Get the course object.
		$course = crlms_get_course($course_id);

		if ( ! $course ) {
			return new WP_Error('rest_course_not_found', __('Course not found.', 'creator-lms'), array('status' => 404));
		}

		// Set and update the course status.
		$course->set_status($status);
		$saved = $course->save();

		if (! $saved) {
			return new WP_Error('rest_course_status_update_failed', __('Failed to update course status.', 'creator-lms'), array('status' => 500));
		}

		/*
		 * Fires after a course status is updated via the REST API.
		 *
		 * @param int $course_id The course ID.
		 * @param string $status The new status for the course.
		 *
		 * @since 1.0.0
		 */
		do_action('creator_lms_rest_course_status_updated', $course_id, $status);

		return rest_ensure_response(array('status' => 'success', 'message' => __('Course status updated.', 'creator-lms')));
	}
}
