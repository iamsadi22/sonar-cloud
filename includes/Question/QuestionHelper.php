<?php

namespace CreatorLms\Question;

/**
 * Responsible to handle all quiz related calculations
 * @since 1.0.0
 */
class QuestionHelper
{
	/**
	 * Add new question
	 * @param string $title
	 * @param string $description
	 * @return array
	 * @since 1.0.0
	 */
	public static function add_question(string $title, string $content): array
	{
		/**
		 * Fires before question add
		 *
		 * @param string $title question title
		 * @param string $content question title
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_add_question', $title, $content );

		$quiz_id = wp_insert_post( array(
			'post_title' => $title,
			'post_type' => 'crlms-question',
			'post_status' => 'publish',
		));

		/**
		 * log the error when debug is enabled.
		 * $post_id->get_error_message()
		 */
		if ( is_wp_error( $quiz_id ) ) {
			return [
				'status' => "error",
				'message' => __( 'Failed to add question', 'creator-lms' ),
			];
		}

		/**
		 * Fires after question add
		 *
		 * @param string $title question title
		 * @param string $content question title
		 * @param string $quiz_id newly inserted question id
		 * @since 1.0.0
		 */
		do_action( 'crlms_after_add_question', $title, $content, $quiz_id );

		return [
			'status' => "success",
			'message' => __( 'Question has been added.', 'creator-lms' ),
		];
	}

	/**
	 * Saved question
	 * @param int $post_id
	 * @param array $params
	 * @return array
	 * @since 1.0.0
	 */
	public static function save_question(int $post_id, array $question_data): array
	{
		/**
		 * Fires before question save
		 *
		 * @param number $post_id new inserted question id
		 * @param array $question_data key value pair based items to update meta fields
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_question_update', $post_id, $question_data );

		if(empty($question_data['title'])){
			return  [
				'status' => "error",
				'message' => __( 'Title is required to save question.', 'creator-lms' ),
			];
		}

		$post = get_post($post_id);

		if ($post && $post->post_type === 'crlms-question') {
			wp_update_post([
				'ID'         => $post_id,
				'post_title' => sanitize_text_field($question_data['title']),
			]);
		}

		/**
		 * Fires after question update
		 *
		 * @param number $post_id new inserted question id
		 * @param array $question_data key value pair based items to update meta fields
		 * @since 1.0.0
		 */
		do_action( 'crlms_after_question_update', $post_id, $question_data );

		return [
			'status' => "success",
			'message' => __( 'Question has been saved.', 'creator-lms' ),
		];
	}

	/**
	 * Deleted question
	 * @param int $post_id
	 * @return array
	 * @since 1.0.0
	 */
	public static function delete_question(int $post_id): array
	{
		$post = get_post( $post_id );
		if ( !$post || $post->post_type !== 'crlms-question' ) {

			return [
				'status' => "error",
				'message' => __( 'Question not found.', 'creator-lms' ),
			];
		}

		/**
		 * Fires before question delete
		 *
		 * @param string $post_id question id to delete
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_question_delete', $post_id );

		$result = wp_delete_post( $post_id, true );

		if ( !$result ) {

			return [
				'status' => "error",
				'message' => __( 'Failed to delete question.', 'creator-lms' ),
			];
		}

		/**
		 * Fires after question delete
		 *
		 * @param string $post_id Deleted Question id
		 * @since 1.0.0
		 */
		do_action( 'crlms_after_question_delete', $post_id );

		return [
			'status' => "success",
			'message' => __( 'Question has been deleted.', 'creator-lms' ),
		];
	}
}
