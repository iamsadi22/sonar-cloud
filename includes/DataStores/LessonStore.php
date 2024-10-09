<?php

namespace CreatorLms\DataStores;

use CreatorLms\Abstracts\DataStore;
use CreatorLms\Data\Lesson;

defined( 'ABSPATH' ) || exit;

/**
 * Class LessonStore
 * @package CreatorLms\DataStores
 * @since 1.0.0
 */
class LessonStore extends DataStore {

	/**
	 * Create lesson
	 *
	 * @param Lesson $lesson
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function create( &$lesson ) {

		if ( ! $lesson->get_date_created( 'edit' ) ) {
			$lesson->set_date_created( time() );
		}

		$id = wp_insert_post(
			apply_filters(
				'creator_lms_new_lesson_data',
				array(
					'post_type'      => CREATOR_LMS_LESSON_CPT,
					'post_author'    => get_current_user_id(),
					'post_status'    => $lesson->get_status() ? $lesson->get_status() : 'draft',
					'post_title'     => $lesson->get_name() ? $lesson->get_name() : __( 'No Title', 'creator-lms' ),
					'post_content'   => $lesson->get_description(),
					'post_name'      => $lesson->get_slug( 'edit' ),
					'post_date'      => gmdate( 'Y-m-d H:i:s', $lesson->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt'  => gmdate( 'Y-m-d H:i:s', $lesson->get_date_created( 'edit' )->getTimestamp() ),
				)
			),
			true
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$lesson->set_id($id);

			$this->update_lesson_meta( $lesson );

			/**
			 * Fires after a new lesson is created.
			 *
			 * This action hook allows developers to perform additional actions after a lesson is created.
			 *
			 * @param int   $id     The ID of the newly created lesson.
			 * @param array $lesson The lesson data array, containing information about the created lesson.
			 *
			 * @since 1.0.0
			 */
			do_action( 'creator_lms_after_creating_new_lesson', $id, $lesson );
		}
	}


	/**
	 * Read data
	 *
	 * @param Lesson $lesson
	 * @return mixed|void
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function read( &$lesson ) {
		$post_object = get_post( $lesson->get_id() );
		if ( ! $lesson->get_id() || ! $post_object || CREATOR_LMS_LESSON_CPT !== $post_object->post_type ) {
			return ( __( 'Invalid lesson.', 'creator-lms' ) );
		}

		$lesson->set_props(
			array(
				'name'              => $post_object->post_title,
				'slug'              => $post_object->post_name,
				'status'            => $post_object->post_status,
				'date_created'      => $post_object->post_date_gmt,
				'date_modified'     => $post_object->post_modified_gmt,
				'description'       => $post_object->post_content,
				'thumbnail_id'      => get_post_thumbnail_id( $lesson->get_id() ),
			)
		);

		$this->read_lesson_data( $lesson );
	}


	/**
	 * Update lesson data
	 *
	 * @param Lesson $lesson The lesson object to update.
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function update(&$lesson) {

		$post_data = array(
			'post_content'   => $lesson->get_description( 'edit' ),
			'post_excerpt'   => $lesson->get_short_description( 'edit' ),
			'post_title'     => $lesson->get_name( 'edit' ),
			'post_status'    => $lesson->get_status( 'edit' ) ? $lesson->get_status( 'edit' ) : 'publish',
			'post_name'      => $lesson->get_slug( 'edit' ),
			'post_type'      => CREATOR_LMS_LESSON_CPT,
		);
		if ( $lesson->get_date_created( 'edit' ) ) {
			$post_data['post_date']     = gmdate( 'Y-m-d H:i:s', $lesson->get_date_created( 'edit' )->getOffsetTimestamp() );
			$post_data['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $lesson->get_date_created( 'edit' )->getTimestamp() );
		}
		$post_data['post_modified']     = current_time( 'mysql' );
		$post_data['post_modified_gmt'] = current_time( 'mysql', 1 );

		wp_update_post( array_merge( array( 'ID' => $lesson->get_id() ), $post_data ) );

		$this->update_lesson_meta( $lesson );

		/**
		 * Action hook to perform additional actions after a lesson is updated.
		 *
		 * @param int    $lesson_id The ID of the updated lesson.
		 * @param Lesson $lesson    The lesson object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_after_updating_lesson', $lesson->get_id(), $lesson );
	}


	/**
	 * Update post meta for the lesson.
	 *
	 * @param Lesson $lesson The lesson object.
	 * @param bool $force Whether to force the update.
	 * @return void
	 *
	 * @since 1.0.0
	 */
	protected function update_post_meta( &$lesson, $force = false ) {
		$meta_key_to_props = array();

		$props_to_update = $meta_key_to_props;

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $lesson->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;

			$this->update_or_delete_post_meta( $lesson, $meta_key, $value );
		}
	}

	/**
	 * Delete a lesson.
	 *
	 * This function deletes a lesson by its ID and triggers the 'creator_lms_after_deleting_a_lesson' action hook.
	 *
	 * @param Lesson $lesson The lesson object to be deleted.
	 * @param array  $args   Optional. Additional arguments for the delete operation. Default empty array.
	 *
	 * @since 1.0.0
	 */
	public function delete( &$lesson, $args = array() ) {
		if ($lesson) {
			$lesson_id = $lesson->get_id();
			if ($lesson_id) {
				wp_delete_post($lesson_id, true);
				/**
				 * Triggered after deleting a lesson.
				 *
				 * This action hook allows developers to perform additional actions after a lesson is deleted.
				 *
				 * @since 1.0.0
				 */
				do_action('creator_lms_after_deleting_a_lesson');
			}
		}
	}


	/**
	 * Helper function that reads lesson data
	 *
	 * @param Lesson $lesson
	 * @return void
	 *
	 * @since 1.0.0
	 */
	protected function read_lesson_data( &$lesson ) {
		$id                = $lesson->get_id();
		$post_meta_values  = get_post_meta( $id );

		$meta_key_to_props = array(
			'_thumbnail_id'    		=> 'thumbnail_id',
			'_type'       			=> 'type',
		);

		foreach ( $meta_key_to_props as $meta_key => $prop ) {
			$meta_value         = isset( $post_meta_values[ $meta_key ][0] ) ? $post_meta_values[ $meta_key ][0] : null;
			$set_props[ $prop ] = maybe_unserialize( $meta_value );
		}

		$lesson->set_props( $set_props );
	}


	/**
	 * Helper function that updates lesson meta
	 *
	 * @param Lesson $lesson
	 * @param bool $force
	 * @return void
	 *
	 * @since 1.0.0
	 */
	protected function update_lesson_meta( &$lesson, $force = false ) {
		$meta_key_to_props = array(
			'_type'         => 'type',
		);
		$meta_key_to_props = apply_filters( 'creator_lms_lesson_meta_key_to_props', $meta_key_to_props );
		$props_to_update   = $meta_key_to_props;
		
		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $lesson->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			$this->update_or_delete_post_meta( $lesson, $meta_key, $value );
		}


		/**
		 * Fires after the meta data for a lesson is updated.
		 *
		 * @param WP_Post $lesson The updated lesson object.
		 * 
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_lesson_meta_updated', $lesson );
	}
}
