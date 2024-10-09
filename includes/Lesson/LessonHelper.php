<?php

namespace CreatorLms\Lesson;

use CreatorLms\Section\SectionHelper;

/**
 * Responsible to handle all lesson related calculations
 * @since 1.0.0
 */
class LessonHelper
{
	/**
	 * Add lesson
	 * @param $title
	 * @param $content
	 * @return array
	 * @since 1.0.0
	 */
	public static function add_lesson( $title, $content ): array
	{
		/**
		 * Fires before lesson add
		 *
		 * @param string $title lesson title
		 * @param string $content lesson title
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_add_lesson', $title, $content );

		$lesson_id = wp_insert_post( array(
			'post_title' => $title,
			'post_type' => 'crlms-lesson',
			'post_status' => 'publish',
		));

		/**
		 * log the error when debug is enabled.
		 * $post_id->get_error_message()
		 */
		if ( is_wp_error( $lesson_id ) ) {
			return [
				'status' => "error",
				'message' => __( 'Failed to add lesson', 'creator-lms' ),
			];
		}

		/**
		 * Fires after lesson add
		 *
		 * @param string $title lesson title
		 * @param string $content lesson title
		 * @param string $lesson_id newly inserted lesson id
		 * @since 1.0.0
		 */
		do_action( 'crlms_after_add_lesson', $title, $content, $lesson_id );

		$lesson_data = [
			'id' => $lesson_id,
			'title' => $title,
			'type' => 'lesson',
		];

		return [
			'status' => "success",
			'message' => __( 'Lesson has been added.', 'creator-lms' ),
			'data' => $lesson_data
		];
	}

	/**
	 * Update lesson
	 * @param mixed $post_id
	 * @param array $map_data
	 * @return array
	 * @since 1.0.0
	 */
	public static function update_lesson(mixed $post_id, array $map_data): array
	{

		/**
		 * Fires before lesson update
		 *
		 * @param number $post_id new inserted lesson id
		 * @param array $map_data key value pair based items to update meta fields
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_lesson_update', $post_id, $map_data );

		if(empty($map_data['title'])){
			return  [
				'status' => "error",
				'message' => __( 'Title is required to save lesson.', 'creator-lms' ),
			];
		}

		$post = get_post($post_id);

		if ($post && $post->post_type === 'crlms-lesson') {
			wp_update_post([
				'ID'         => $post_id,
				'post_title' => sanitize_text_field($map_data['title']),
			]);
		}

		/**
		 * Fires after lesson update
		 *
		 * @param number $post_id new inserted lesson id
		 * @param array $map_data key value pair based items to update meta fields
		 * @since 1.0.0
		 */
		do_action( 'crlms_after_lesson_update', $post_id, $map_data );

		$lesson_data = [
			'id' => $post_id,
			'title' => $map_data['title'],
			'type' => 'lesson',
		];

		return  [
			'status' => "success",
			'message' => __( 'Successfully saved lesson.', 'creator-lms' ),
			'data' => $lesson_data
		];
	}

	/**
	 * Save lesson
	 * @param mixed $post_id
	 * @param array $lesson_data
	 * @return array
	 * @since 1.0.0
	 */
	public static function save_lesson(mixed $post_id, array $lesson_data): array
	{

		/**
		 * Fires before lesson update
		 *
		 * @param number $post_id new inserted lesson id
		 * @param array $lesson_data key value pair based items to update meta fields
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_lesson_update', $post_id, $lesson_data );

		if(empty($lesson_data)){
			return  [
				'status' => "error",
				'message' => __( 'No data found to update.', 'creator-lms' ),
			];
		}

		$post = get_post($post_id);

		if($post->post_type !== 'crlms-lesson') {
			return  [
				'status' => "error",
				'message' => __( 'No lesson available. ', 'creator-lms' ),
			];
		}

		$post_update_data = array(
			'ID'           => $post_id,
			'post_title'    => sanitize_text_field($lesson_data['title']),
			'post_excerpt'  => sanitize_textarea_field($lesson_data['description']),
			'post_content'  => wp_kses_post($lesson_data['editorHTML']),
		);

		wp_update_post($post_update_data);

		update_post_meta($post_id, 'lesson_editor_content', wp_json_encode($lesson_data['editorContent']));
		update_post_meta($post_id, 'lesson_visibility', sanitize_text_field($lesson_data['visibility']));
		update_post_meta($post_id, 'lesson_enable_comments', (bool) $lesson_data['enableComments']);
		update_post_meta($post_id, 'lesson_prerequisites', (bool) $lesson_data['prerequisites']);
		update_post_meta($post_id, 'lesson_drip_feed', (bool) $lesson_data['dripFeed']);
		update_post_meta($post_id, 'lesson_download_resource', sanitize_text_field($lesson_data['downloadResource']));
		update_post_meta($post_id, 'lesson_thumbnail', sanitize_text_field($lesson_data['thumbnail']));

		/**
		 * Fires after lesson update
		 *
		 * @param number $post_id new inserted lesson id
		 * @param array $lesson_data key value pair based items to update meta fields
		 * @since 1.0.0
		 */
		do_action( 'crlms_after_lesson_update', $post_id, $lesson_data );

		return  [
			'status' => "success",
			'message' => __( 'Successfully saved lesson.', 'creator-lms' ),
		];
	}

	/**
	 * Get lesson content
	 * @param $post_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_lesson_content($post_id): array
	{

		$post = get_post($post_id);

		if($post->post_type !== 'crlms-lesson') {
			return  [
				'status' => "error",
				'message' => __( 'No lesson available. ', 'creator-lms' ),
			];
		}

		$lesson_data = array(
			'title' => $post->post_title,
			'description' => $post->post_excerpt,
			'editorContent' => json_decode(get_post_meta($post_id, 'lesson_editor_content', true), true),
			'editorHTML' => $post->post_content,
			'visibility' => get_post_meta($post_id, 'lesson_visibility', true),
			'enableComments' => (bool) get_post_meta($post_id, 'lesson_enable_comments', true),
			'prerequisites' => (bool) get_post_meta($post_id, 'lesson_prerequisites', true),
			'dripFeed' => (bool) get_post_meta($post_id, 'lesson_drip_feed', true),
			'downloadResource' => get_post_meta($post_id, 'lesson_download_resource', true),
			'thumbnail' => get_post_meta($post_id, 'lesson_thumbnail', true),
		);

		return  [
			'status' => "success",
			'message' => __( 'Successfully fetched lesson content.', 'creator-lms' ),
			'data' => $lesson_data
		];
	}

	/**
	 * Delete lesson
	 * @param int $post_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function delete_lesson(int $post_id): array
	{

		$post = get_post( $post_id );
		if ( !$post || $post->post_type !== 'crlms-lesson' ) {

			return [
				'status' => "error",
				'message' => __( 'Lesson not found.', 'creator-lms' ),
			];
		}

		/**
		 * Fires before lesson delete
		 *
		 * @param string $post_id lesson id to delete
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_lesson_delete', $post_id );

		$result = wp_delete_post( $post_id, true );

		if ( !$result ) {

			return [
				'status' => "error",
				'message' => __( 'Failed to delete lesson.', 'creator-lms' ),
			];
		}

		/**
		 * Fires after lesson delete
		 *
		 * @param string $post_id Deleted lesson id
		 * @since 1.0.0
		 */
		do_action( 'crlms_after_lesson_delete', $post_id );

		return [
			'status' => "success",
			'message' => __( 'Lesson has been deleted.', 'creator-lms' ),
		];

	}
}

