<?php

namespace CreatorLms\Hooks;

use CreatorLms\Abstracts\HookHandler;
use CreatorLms\Data\Chapter;

/**
 * Handles hooks related to courses in the Creator LMS plugin.
 *
 * @since 1.0.0
 */
class CourseHookHandler extends HookHandler {

	public function register_hooks() {
		add_action('creator_lms_rest_insert_course', [$this, 'create_default_chapter'], 10, 2);
		add_action('creator_lms_rest_delete_course', [$this, 'unlink_chapter_from_course'], 10 );
	}


	/**
	 * Create a default chapter for a course.
	 *
	 * @param \WP_Post $course The course post object.
	 * @param \WP_REST_Request $request The REST request object.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function create_default_chapter( $course, $request ) {

		if ( ! is_a( $course, 'WP_Post' ) ) {
			return;
		}
		$course_id = $course->ID;

		$chapter = new Chapter();
		$chapter->set_name(__('No Title', 'creator-lms'));
		$chapter->set_status('publish');
		$chapter->save();

		$this->update_chapter_relationship( $course_id, $chapter->get_id() );
	}


	/**
	 * Update the relationship between a course and a chapter.
	 *
	 * @param int $course_id The ID of the course.
	 * @param int $chapter_id The ID of the chapter.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function update_chapter_relationship( $course_id, $chapter_id, $order_number = 0 ) {
		if ( ! $course_id || ! $chapter_id ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . CREATOR_LMS_CHAPTER_RELATIONSHIP;

		// Check if the relationship already exists
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT ID FROM $table_name WHERE course_id = %d AND chapter_id = %d LIMIT 1", 
			$course_id, $chapter_id 
		));

		// If exists, update the relationship, otherwise insert a new one
		if ( $exists ) {
			$wpdb->update(
				$table_name,
				array(
					'order_number' => $order_number,
				),
				array(
					'course_id' => $course_id,
					'chapter_id' => $chapter_id,
				),
				array( '%d' ),
				array( '%d', '%d' )
			);
		} else {
			$wpdb->insert(
				$table_name,
				array(
					'course_id' 	=> $course_id,
					'chapter_id' 	=> $chapter_id,
					'order_number' 	=> $order_number,
				),
				array( '%d', '%d', '%d' )
			);
		}

		/**
		 * Action triggered after a course and chapter relationship is created.
		 *
		 * @since 1.0.0
		 *
		 * @param int $course_id The ID of the course.
		 * @param int $chapter_id The ID of the chapter.
		 */
		do_action('creator_lms_course_chapter_relationship_created', $course_id, $chapter_id);
	}


	/**
	 * Unlink a chapter from a course.
	 * Delete chapter and course relationship
	 * 
	 * @param int $course_id The ID of the course.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function unlink_chapter_from_course( $course_id ) {
		if ( ! $course_id ) {
			return;
		}
		
		global $wpdb;

		
		$table_name = $wpdb->prefix . CREATOR_LMS_CHAPTER_RELATIONSHIP;
		// Fetch all chapter IDs before deleting
		$chapter_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT chapter_id FROM $table_name WHERE course_id = %d",
				$course_id
			)
		);
		
		if( !empty( $chapter_ids ) ){
			$wpdb->delete(
				$table_name,
				array(
					'course_id' => $course_id,
				),
				array(
					'%d',
				)
			);

			foreach( $chapter_ids as $chapter_id ){
				/**
				 * Executes the 'creator_lms_after_remove_chapter_from_course' action hook.
				 * This hook is triggered when a chapter is being deleted via Hook.
				 *
				 * @param int $chapter_id The ID of the chapter being deleted.
				 * @since 1.0.0
				 */
				do_action( 'creator_lms_after_remove_chapter_from_course', $chapter_id );
			}
		}
		
	}
}
