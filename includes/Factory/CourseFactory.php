<?php

namespace CreatorLms\Factory;

use CreatorLms\Data\Course;

class CourseFactory {

	/**
	 * Get course object
	 *
	 * @param bool $course_id
	 * @return bool|Course
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function get_course( $course_id = false ) {
		$course_id = $this->get_course_id( $course_id );
		if ( ! $course_id ) {
			return false;
		}
		return new Course($course_id);
	}


	/**
	 * Get course id
	 *
	 * @param $course
	 * @return bool|int
	 * @since 1.0.0
	 */
	private function get_course_id( $course ) {
		global $post;
		if ( false === $course && isset( $post, $post->ID ) && CREATOR_LMS_COURSE_CPT === get_post_type( $post->ID ) ) {
			return absint( $post->ID );
		} elseif ( is_numeric( $course ) ) {
			return $this->is_course_exist( $course ) ? $course : false;
		} elseif ( $course instanceof Course ) {
			$id = $course->get_id();
			return $this->is_course_exist( $id ) ? $id : false;
		} elseif ( ! empty( $course->ID ) ) {
			return $this->is_course_exist( $course->ID ) ? $course->ID : false;
		} else {
			return false;
		}
	}

	/**
	 * Check whether the course exist or not
	 * 
	 * @param $course_id Course ID
	 * 
	 * @return bool If course is exist then return true, otherwise return false
	 * 
	 * @since 1.0.0
	 */
	public function is_course_exist( $course_id ){
		if( !$course_id ){
			return false;
		}

		$course = get_post( $course_id );
		
		// Check if the post exists and the post type is CREATOR_LMS_COURSE_CPT
		if ( $course && CREATOR_LMS_COURSE_CPT === get_post_type( $course_id ) ) {
			return true;  // Post exists and is of the correct type
		} else {
			return false; // Either post doesn't exist or the post type doesn't match
		}
	}
}