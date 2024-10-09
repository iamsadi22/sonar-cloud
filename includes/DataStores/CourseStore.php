<?php

namespace CreatorLms\DataStores;

use CreatorLms\Abstracts\DataStore;
use CreatorLms\Data\Course;

defined( 'ABSPATH' ) || exit;

/**
 * Class CourseStore
 * @package CreatorLms\DataStores
 * @since 1.0.0
 */
class CourseStore extends DataStore {

	/**
	 * Create course
	 *
	 * @param Course $course
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function create( &$course ) {

		if ( ! $course->get_date_created( 'edit' ) ) {
			$course->set_date_created( time() );
		}

		$id = wp_insert_post(
			apply_filters(
				'creator_lms_new_course_data',
				array(
					'post_type'      => CREATOR_LMS_COURSE_CPT,
					'post_author'    => get_current_user_id(),
					'post_status'    => $course->get_status() ? $course->get_status() : 'draft',
					'post_title'     => $course->get_name() ? $course->get_name() : __( 'No Name', 'creator-lms' ),
					'post_content'   => $course->get_description(),
					'post_name'      => $course->get_slug( 'edit' ),
					'post_date'      => gmdate( 'Y-m-d H:i:s', $course->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt'  => gmdate( 'Y-m-d H:i:s', $course->get_date_created( 'edit' )->getTimestamp() ),
				)
			),
			true
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$course->set_id($id);

			$this->update_post_meta( $course );

			/**
			 * Fires after a new course is created.
			 *
			 * This action hook allows developers to perform additional actions after a course is created.
			 *
			 * @param int   $id     The ID of the newly created course.
			 * @param array $course The course data array, containing information about the created course.
			 *
			 * @since 1.0.0
			 */
			do_action( 'creator_lms_after_creating_new_course', $id, $course );
		}
	}


	/**
	 * Read data
	 *
	 * @param Course $course
	 * @return mixed|void
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function read( &$course ) {
		$post_object = get_post( $course->get_id() );
		if ( ! $course->get_id() || ! $post_object || CREATOR_LMS_COURSE_CPT !== $post_object->post_type ) {
			return;
//			throw new \Exception( __( 'Invalid course.', 'creator-lms' ) );
		}

		$course->set_props(
			array(
				'name'              => $post_object->post_title,
				'slug'              => $post_object->post_name,
				'status'            => $post_object->post_status,
				'date_created'      => $post_object->post_date_gmt,
				'date_modified'     => $post_object->post_modified_gmt,
				'description'       => $post_object->post_content,
				'thumbnail_id'      => get_post_thumbnail_id( $course->get_id() ),
			)
		);

		$this->read_course_data( $course );
	}


	/**
	 * Update course data
	 *
	 * @param Course $course The course object to update.
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function update(&$course) {

		$post_data = array(
			'post_content'   => $course->get_description( 'edit' ),
			'post_excerpt'   => $course->get_short_description( 'edit' ),
			'post_title'     => $course->get_name( 'edit' ),
			'post_status'    => $course->get_status( 'edit' ) ? $course->get_status( 'edit' ) : 'publish',
			'post_name'      => $course->get_slug( 'edit' ),
			'post_type'      => CREATOR_LMS_COURSE_CPT,
		);
		if ( $course->get_date_created( 'edit' ) ) {
			$post_data['post_date']     = gmdate( 'Y-m-d H:i:s', $course->get_date_created( 'edit' )->getOffsetTimestamp() );
			$post_data['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $course->get_date_created( 'edit' )->getTimestamp() );
		}
		$post_data['post_modified']     = current_time( 'mysql' );
		$post_data['post_modified_gmt'] = current_time( 'mysql', 1 );

		wp_update_post( array_merge( array( 'ID' => $course->get_id() ), $post_data ) );

		$this->update_post_meta( $course );

		/**
		 * Action hook to perform additional actions after a course is updated.
		 *
		 * @param int    $course_id The ID of the updated course.
		 * @param Course $course    The course object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_update_course', $course->get_id(), $course );
	}


	/**
	 * Update post meta for the course.
	 *
	 * @param Course $course The course object.
	 * @param bool $force Whether to force the update.
	 * @return void
	 *
	 * @since 1.0.0
	 */
	protected function update_post_meta( &$course, $force = false ) {
		$meta_key_to_props = array(
			'_regular_price'         => 'regular_price',
			'_sale_price'            => 'sale_price',
			'_sale_price_dates_from' => 'sale_price_dates_from',
			'_sale_price_dates_to'   => 'sale_price_dates_to',
			'_thumbnail_id'   		 => 'thumbnail_id',
			'_level'   		 		 => 'level',
			'_availability'   		 => 'availability',
			'_available_date'   	 => 'available_date',
			'_accessibility'   	 	 => 'accessibility',
			'_capacity'   	 	 	 => 'capacity',
			'_limit'   	 	 	 	 => 'limit',
		);

		$props_to_update = $meta_key_to_props;

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $course->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			switch ( $prop ) {
				case 'sale_price_dates_from':
				case 'sale_price_dates_to':
					$value = $value ? $value->getTimestamp() : '';
					break;
			}

			$this->update_or_delete_post_meta( $course, $meta_key, $value );
		}
	}


	/**
	 * Delete the course
	 *
	 * @param $course
	 * @param array $args
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function delete( &$course, $args = array() ) {
		if( $course ){
			$course_id = $course->get_id();
			if( $course_id ) {
				wp_delete_post( $course_id, true );
				/**
				 * Triggered after deleting a course.
				 *
				 * This action hook allows developers to perform additional actions after a course is deleted.
				 *
				 * @since 1.0.0
				 */
				do_action( 'creator_lms_after_deleting_a_course' );
			}
		}
	}


	/**
	 * Helper function that reads course data
	 *
	 * @param Course $course
	 * @return void
	 *
	 * @since 1.0.0
	 */
	protected function read_course_data( &$course ) {
		$id                = $course->get_id();
		$post_meta_values  = get_post_meta( $id );

		$meta_key_to_props = array(
			'_regular_price'			=> 'regular_price',
			'_sale_price'            	=> 'sale_price',
			'_sale_price_dates_from' 	=> 'date_on_sale_from',
			'_sale_price_dates_to'   	=> 'date_on_sale_to',
			'_average_rating'        	=> 'average_rating',
			'_rating_count'       		=> 'rating_counts',
			'_review_count'       		=> 'review_count',
			'_level'   		 			=> 'level',
			'_availability'   			=> 'availability',
			'_available_date'   	 	=> 'available_date',
			'_accessibility'   	 		=> 'accessibility',
			'_capacity'   	 	 		=> 'capacity',
			'_limit'   	 	 	 		=> 'limit',
		);

		foreach ( $meta_key_to_props as $meta_key => $prop ) {
			$meta_value         = isset( $post_meta_values[ $meta_key ][0] ) ? $post_meta_values[ $meta_key ][0] : null;
			$set_props[ $prop ] = maybe_unserialize( $meta_value );
		}

		$course->set_props( $set_props );
	}


	/**
	 * Get the chapters of the course.
	 *
	 * @param Course $course The course object.
	 * @return array The list of chapters.
	 *
	 * @since 1.0.0
	 */
	public function get_chapters( $course ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'crlms_chapter_relationship';
		$course_id 	= $course->get_id();
		$chapters   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE course_id = %d ORDER BY order_number ASC", $course_id ) );

		$filtered_chapters = array();

		if ($chapters) {
			foreach ( $chapters as $chapter ) {
				$chapter_obj = crlms_get_chapter( $chapter->chapter_id );
				$filtered_chapters[] = array(
					'id' 			=> $chapter_obj->get_id(),
					'name' 			=> $chapter_obj->get_name(),
					'description' 	=> $chapter_obj->get_description(),
					'order_number' 	=> (int) $chapter->order_number
				);
			}
		}
		return $filtered_chapters;
	}


	/**
	 * Set the chapters for the course.
	 *
	 * @param Course $course The course object.
	 * @param array $chapters The list of chapters to set, each containing 'id' and 'order_number'.
	 * @return bool True on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	public function set_chapters( $course, $chapters ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'crlms_chapter_relationship';
		$course_id = $course->get_id();

		$values = array();
		foreach ( $chapters as $index => $chapter ) {
			$values[] = $course_id;
			$values[] = $chapter['id'];
			$values[] = $chapter['order_number'];

			// Create placeholders for each set of values
			$placeholders[] = "(%d, %d, %d)";
		}

		// Construct the query with ON DUPLICATE KEY UPDATE
		$insert_query = "
			INSERT INTO $table_name (course_id, chapter_id, order_number)
			VALUES " . implode( ', ', $placeholders ) . "
			ON DUPLICATE KEY UPDATE
			order_number = VALUES(order_number)
		";

		// Execute the query using prepared statements to prevent SQL injection
		$wpdb->query( $wpdb->prepare( $insert_query, $values ) );

		return true;
	}
}
