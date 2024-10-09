<?php

namespace CreatorLms\DataStores;

use CreatorLms\Abstracts\DataStore;
use CreatorLms\Data\Chapter;

defined( 'ABSPATH' ) || exit;

/**
 * Class ChapterStore
 * @package CreatorLms\DataStores
 * @since 1.0.0
 */
class ChapterStore extends DataStore {

	/**
	 * Create chapter
	 *
	 * @param Chapter $chapter
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function create( &$chapter ) {

		if ( ! $chapter->get_date_created( 'edit' ) ) {
			$chapter->set_date_created( time() );
		}
		$id = wp_insert_post(
			apply_filters(
				'creator_lms_new_chapter_data',
				array(
					'post_type'      => CREATOR_LMS_CHAPTER_CPT,
					'post_author'    => get_current_user_id(),
					'post_status'    => $chapter->get_status() ? $chapter->get_status() : 'draft',
					'post_title'     => $chapter->get_name() ? $chapter->get_name() : __( 'No Name', 'creator-lms' ),
					'post_content'   => $chapter->get_description(),
					'post_name'      => $chapter->get_slug( 'edit' ),
					'post_date'      => gmdate( 'Y-m-d H:i:s', $chapter->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt'  => gmdate( 'Y-m-d H:i:s', $chapter->get_date_created( 'edit' )->getTimestamp() ),
				)
			),
			true
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$chapter->set_id($id);

			$this->update_chapter_meta( $chapter );

			/**
			 * Fires after a new chapter is created.
			 *
			 * This action hook allows developers to perform additional actions after a chapter is created.
			 *
			 * @param int   $id     The ID of the newly created chapter.
			 * @param array $chapter The chapter data array, containing information about the created chapter.
			 *
			 * @since 1.0.0
			 */
			do_action( 'creator_lms_after_creating_new_chapter', $id, $chapter );
		}
	}


	/**
	 * Read data
	 *
	 * @param Chapter $chapter
	 * @return mixed|void
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function read( &$chapter ) {
		$post_object = get_post( $chapter->get_id() );
		if ( ! $chapter->get_id() || ! $post_object || CREATOR_LMS_CHAPTER_CPT !== $post_object->post_type ) {
			return;
		}

		$chapter->set_props(
			array(
				'name'              => $post_object->post_title,
				'slug'              => $post_object->post_name,
				'status'            => $post_object->post_status,
				'date_created'      => $post_object->post_date_gmt,
				'date_modified'     => $post_object->post_modified_gmt,
				'description'       => $post_object->post_content,
			)
		);

		$this->read_chapter_data( $chapter );
	}


	/**
	 * Update chapter data
	 *
	 * @param Chapter $chapter The chapter object to update.
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function update(&$chapter) {

		$post_data = array(
			'post_content'   => $chapter->get_description( 'edit' ),
			'post_title'     => $chapter->get_name( 'edit' ),
			'post_status'    => $chapter->get_status( 'edit' ) ? $chapter->get_status( 'edit' ) : 'publish',
			'post_name'      => $chapter->get_slug( 'edit' ),
			'post_type'      => CREATOR_LMS_CHAPTER_CPT,
		);
		if ( $chapter->get_date_created( 'edit' ) ) {
			$post_data['post_date']     = gmdate( 'Y-m-d H:i:s', $chapter->get_date_created( 'edit' )->getOffsetTimestamp() );
			$post_data['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $chapter->get_date_created( 'edit' )->getTimestamp() );
		}
		$post_data['post_modified']     = current_time( 'mysql' );
		$post_data['post_modified_gmt'] = current_time( 'mysql', 1 );

		wp_update_post( array_merge( array( 'ID' => $chapter->get_id() ), $post_data ) );

		$this->update_post_meta( $chapter );

		/**
		 * Action hook to perform additional actions after a chapter is updated.
		 *
		 * @param int    $chapter_id The ID of the updated chapter.
		 * @param Chapter $chapter    The chapter object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_update_chapter', $chapter->get_id(), $chapter );
	}


	/**
	 * Update post meta for the chapter.
	 *
	 * @param Chapter $chapter The chapter object.
	 * @param bool $force Whether to force the update.
	 * @return void
	 *
	 * @since 1.0.0
	 */
	protected function update_post_meta( &$chapter, $force = false ) {
		$meta_key_to_props = array();

		$props_to_update = $meta_key_to_props;

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $chapter->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			$this->update_or_delete_post_meta( $chapter, $meta_key, $value );
		}
	}


	/**
	 * Delete the chapter
	 *
	 * @param $chapter
	 * @param array $args
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public function delete( &$chapter, $args = array() ) {
		if( $chapter ){
			$chapter_id = $chapter->get_id();
			if( $chapter_id ) {
				wp_delete_post( $chapter_id, true );
				/**
				 * Triggered after deleting a chapter.
				 *
				 * This action hook allows developers to perform additional actions after a chapter is deleted.
				 *
				 * @since 1.0.0
				 */
				do_action( 'creator_lms_after_deleting_a_chapter' );
			}
		}
	}

	/**
	 * Set the contents of the chapter.
	 *
	 * @param Chapter $chapter The chapter object.
	 * @param array $lessons The lessons to set for the chapter.
	 * @return bool True on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	public function set_contents( $chapter, $lessons ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'crlms_content_relationship';
		$chapter_id = $chapter->get_id();
		$values = array();
		foreach ( $lessons as $index => $lesson ) {
			$values[] = $chapter_id;
			$values[] = $lesson['id'];
			$values[] = $lesson['order_number'];
			$values[] = 'text';

			// Create placeholders for each set of values
			$placeholders[] = "(%d, %d, %d, %s)";
		}

		// Construct the query with ON DUPLICATE KEY UPDATE
		$insert_query = "
			INSERT INTO $table_name (chapter_id, content_id, order_number, content_type)
			VALUES " . implode( ', ', $placeholders ) . "
			ON DUPLICATE KEY UPDATE
			order_number = VALUES(order_number)
		";

		// Execute the query using prepared statements to prevent SQL injection
		$wpdb->query( $wpdb->prepare( $insert_query, $values ) );

		return true;
	}

	/**
	 * Helper function that reads chapter data
	 *
	 * @param Chapter $chapter
	 * @return void
	 *
	 * @since 1.0.0
	 */
	protected function read_chapter_data( &$chapter ) {
		$meta_key_to_props = array();
		$set_props = array();
		foreach ( $meta_key_to_props as $meta_key => $prop ) {
			$meta_value         = isset( $post_meta_values[ $meta_key ][0] ) ? $post_meta_values[ $meta_key ][0] : null;
			$set_props[ $prop ] = maybe_unserialize( $meta_value );
		}

		$chapter->set_props( $set_props );
	}


	/**
	 * Helper function that updates chapter meta
	 *
	 * @param Chapter $chapter
	 * @param bool $force
	 * @return void
	 *
	 * @since 1.0.0
	 */
	protected function update_chapter_meta( &$chapter, $force = false ) {
		$meta_key_to_props = array();
		$meta_key_to_props = apply_filters( 'creator_lms_chapter_meta_key_to_props', $meta_key_to_props );
		$props_to_update   = $meta_key_to_props;

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $chapter->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			$this->update_or_delete_post_meta( $chapter, $meta_key, $value );
		}
	}


	/**
	 * Get the lessons of the chapter.
	 *
	 * @param Chapter $chapter The chapter object.
	 * @return array The list of lessons.
	 *
	 * @since 1.0.0
	 */
	public function get_lessons( &$chapter ) {
		global $wpdb;
		$table_name 	= $wpdb->prefix . 'crlms_content_relationship';
		$chapter_id 	= $chapter->get_id();
		$lessons   		= $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE chapter_id = %d ORDER BY order_number ASC", $table_name, $chapter_id ) );
		$filtered_lessons = array();

		if ($lessons) {
			foreach ( $lessons as $lesson ) {
				$lesson_obj = crlms_get_lesson( $lesson->content_id );
				$filtered_lessons[] = array(
					'id' 			=> $lesson_obj->get_id(),
					'name' 			=> $lesson_obj->get_name(),
					'description' 	=> $lesson_obj->get_description(),
					'order_number' 	=> $lesson->order_number,
				);
			}
		}
		return $filtered_lessons;
	}
}
