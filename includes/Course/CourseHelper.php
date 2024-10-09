<?php

namespace CreatorLms\Course;

/**
 * Responsible to handle all course related calculations
 * @since 1.0.0
 */
class CourseHelper
{
	/**
	 * Course general settings meta key constants
	 * @return string[]
	 * @since 1.0.0
	 */
	public static function course_meta_key_constants(): array
	{
		return [
			'course_duration',
			'course_price',
			'course_sale_price',
			'course_level',
			'course_max_student_allowed',
			'course_max_retake_allowed',
			'course_evaluation_type',
			'course_passing_grade',
			'course_requirements',
			'course_target_audiences',
			'course_faqs',
			'course_map',
		];
	}

	/**
	 * Add course
	 * @param $map_data
	 * @return array
	 * @since 1.0.0
	 */
	public static function add_course( $map_data ): array
	{

		if(empty($map_data['title'])){
			return  [
				'status' => "error",
				'message' => __( 'Please enter a title.', 'creator-lms' ),
			];
		}

		if(empty($map_data['description'])){
			return  [
				'status' => "error",
				'message' => __( 'Please provide a description.', 'creator-lms' ),
			];
		}

		/**
		 * Fires before course add
		 *
		 * @param array $map_data course data
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_add_course', $map_data );

		$post_id = wp_insert_post( array(
			'post_title' => $map_data['title'],
			'post_excerpt' => $map_data['description'],
			'post_type' => 'crlms-course',
			'post_status' => 'publish',
		));

		/**
		 * log the error when debug is enabled.
		 * $post_id->get_error_message()
		 */
		if ( is_wp_error( $post_id ) ) {
			return [
				'status' => "error",
				'message' => __( 'Failed to add course.', 'creator-lms' ),
			];
		}

		/**
		 * Fires after course add
		 *
		 * @param number $post_id new inserted course id
		 * @param array $map_data course data
		 * @since 1.0.0
		 */
		do_action( 'crlms_after_add_course', $post_id, $map_data );

		set_post_thumbnail( $post_id, $map_data['feature_image']['src'] );

		unset($map_data['feature_image']);
		unset($map_data['feature_video']);
		update_post_meta( $post_id, 'crlms_course_map', $map_data );

		return [
			'status' 	=> "success",
			'message' 	=> __( 'Course has been created successfully.', 'creator-lms' ),
			'course_id' => $post_id
		];
	}

	/**
	 * Update course
	 * @param $post_id
	 * @param array $map_data
	 * @return array
	 * @since 1.0.0
	 */
	public static function update_course($post_id, array $map_data = [] ): array
	{

		/**
		 * Fires before course update
		 *
		 * @param number $post_id new inserted course id
		 * @param array $map_data key value pair based items to update meta fields
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_course_update', $post_id, $map_data );

		if(empty($map_data['name'])){
			return  [
				'status' => "error",
				'message' => __( 'Title is required to save course.', 'creator-lms' ),
			];
		}

		if(empty($map_data['description'])){
			return  [
				'status' => "error",
				'message' => __( 'Description is required to save course.', 'creator-lms' ),
			];
		}

		$post = get_post($post_id);

		if ($post && $post->post_type === 'crlms-course') {
			wp_update_post([
				'ID'         => $post_id,
				'post_title' => sanitize_text_field($map_data['title']),
			]);
			update_post_meta( $post_id, 'crlms_course_map', $map_data );
		}
		else {
			$post_id = wp_insert_post([
				'post_title'   => sanitize_text_field($map_data['title']),
				'post_type'    => 'crlms-course',
				'post_status'  => 'publish',
			]);

			update_post_meta( $post_id, 'crlms_course_map', $map_data );
		}


		/**
		 * Fires after course update
		 *
		 * @param number $post_id new inserted course id
		 * @param array $map_data key value pair based items to update meta fields
		 * @since 1.0.0
		 */
		do_action( 'crlms_after_course_update', $post_id, $map_data );

		return  [
			'status' => "success",
			'message' => __( 'Successfully saved course.', 'creator-lms' ),
		];

	}

	/**
	 * Delete course
	 * @param $post_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function delete_course ($post_id): array
	{

		$post = get_post( $post_id );
		if ( !$post || $post->post_type !== 'crlms-course' ) {

			return [
				'status' => "error",
				'message' => __( 'Course not found.', 'creator-lms' ),
			];
		}

		/**
		 * Fires before course delete
		 *
		 * @param string $post_id course id to delete
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_course_delete', $post_id );

		$result = wp_delete_post( $post_id, true );

		if ( !$result ) {

			return [
				'status' => "error",
				'message' => __( 'Failed to delete course.', 'creator-lms' ),
			];
		}

		/**
		 * Fires after course delete
		 *
		 * @param string $post_id Deleted course id
		 * @since 1.0.0
		 */
		do_action( 'crlms_after_course_delete', $post_id );

		return [
			'status' => "success",
			'message' => __( 'Course has been deleted.', 'creator-lms' ),
		];
	}

	/**
	 * Insert course section relation
	 * @param $course_id
	 * @param $section_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function insert_course_section_relation($course_id, $section_id): array {

		global $wpdb;

		$table = $wpdb->prefix . 'crlms_courses_with_sections';
		$result = $wpdb->insert(
			$table,
			array(
				'course_id' => $course_id,
				'section_id' => $section_id,
				'created_at' => current_time( 'mysql' ),
				'created_at_gmt' => current_time( 'mysql', 1 ),
				'updated_at' => current_time( 'mysql' ),
				'updated_at_gmt' => current_time( 'mysql', 1 ),
				'created_by' => get_current_user_id(),
				'updated_by' => get_current_user_id(),
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
			)
		);

		if ( false === $result ) {
			return [
				'status' => "error",
				'message' => __( 'Failed to insert course section relation.', 'creator-lms' ),
			];
		}

		return [
			'status' => "success",
			'message' => __( 'Course section relation inserted.', 'creator-lms' ),
		];

	}

	/**
	 * Update course map
	 * @param $course_id
	 * @param $map
	 * @return array
	 * @since 1.0.0
	 */
	public static function update_course_map( $course_id, $map ): array {

		$course = get_post( $course_id );
		if ( !$course || $course->post_type !== 'crlms-course' ) {

			return [
				'status' => "error",
				'message' => __( 'Course not found.', 'creator-lms' ),
			];
		}

		update_post_meta( $course_id, 'crlms_course_map', $map );

		return [
			'status' => "success",
			'message' => __( 'Course content saved', 'creator-lms' ),
		];
	}

	/**
	 * Get course map
	 * @param $course_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_course_map( $course_id ): array {
		$course = get_post( $course_id );
		if ( !$course || $course->post_type !== 'crlms-course' ) {

			return [
				'status' => "error",
				'message' => __( 'Course not found.', 'creator-lms' ),
			];
		}
		$map = get_post_meta( $course_id, 'crlms_course_map', true );
		if(empty($map)) {
			$map = self::get_default_map($course_id);
		}
		$map['feature_image'] 	= get_the_post_thumbnail_url($course_id, 'large');
		$map['feature_video'] 	= get_post_meta( $course_id, 'featured_video', true );
		$map['id']				= $course_id;
		return [
			'status' => "success",
			'message' => __( 'Fetched course map.', 'creator-lms' ),
			'data' => $map
		];
	}

	/**
	 * Fetch default course data map
	 * @param $course_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_default_map($course_id = null): array
	{

		if(!empty($course_id)) {
			$course = get_post( $course_id );
			$course_data = array(
				'id' => $course_id,
				'title' => get_the_title( $course_id ),
				'description' => $course->post_excerpt,
				'chapters' => array(),
			);
		}
		else {
			$course_data = array(
				'id' => null,
				'title' => '',
				'description' => '',
				'chapters' => array(),
			);
		}
		return $course_data;
	}

	public static function crlms_save_course_general_settings() {

		check_ajax_referer( 'crlms-course', 'nonce' );

		if ( ! current_user_can( 'edit_post', $_POST['post_id'] ) ) {
			$response = [
				'status' => 'error',
				'message' => __('You do not have permission to edit this post.', 'creator-lms'),
			];

			wp_send_json($response);
		}

		$general_settings_items = [];

		$course_id = $_POST['post_id'];
		$general_settings_items['course_duration'] = $_POST['course_duration'];
		$general_settings_items['course_price'] = $_POST['course_price'];
		$general_settings_items['course_sale_price'] = $_POST['course_sale_price'];
		$general_settings_items['course_max_student_allowed'] = $_POST['course_max_student_allowed'];
		$general_settings_items['course_max_retake_allowed'] = $_POST['course_max_retake_allowed'];
		$general_settings_items['course_passing_grade'] = $_POST['course_passing_grade'];

		$validated_response = CourseValidator::validate_course_general_settings_fields($general_settings_items);

		if(isset($validated_response['status']) && $validated_response['status'] == 'error') {
			wp_send_json($validated_response);
		}

		$response = self::update_course( $course_id, '', '', $general_settings_items );
		wp_send_json($response);
	}

	/**
	 * Save course settings
	 * @param int $course_id
	 * @param $settings_data
	 * @return array
	 * @since 1.0.0
	 */
	public static function save_course_settings(int $course_id, $settings_data): array
	{

		$course = get_post( $course_id );
		if ( !$course || $course->post_type !== 'crlms-course' ) {

			return [
				'status' => "error",
				'message' => __( 'Course not found.', 'creator-lms' ),
			];
		}

		update_post_meta( $course_id, 'crlms_course_settings', $settings_data );

		return [
			'status' => "success",
			'message' => __( 'Course settings saved', 'creator-lms' ),
		];

	}
}

