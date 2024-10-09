<?php

namespace CreatorLms\DataStores;

use CreatorLms\Abstracts\DataStore;
use CreatorLms\Data\Quiz;

defined( 'ABSPATH' ) || exit;

/**
 * Class QuizStore
 * Handles CRUD operations for Quiz data.
 *
 * @package CreatorLms\DataStores
 * @since 1.0.0
 */
class QuizStore extends DataStore {

	/**
	 * Create a new quiz.
	 *
	 * @param Quiz $quiz
	 * @since 1.0.0
	 */
	public function create(&$quiz): void
	{

		/**
		 * Fires before quiz add
		 *
		 * @param string $quiz quiz object
		 * @since 1.0.0
		 */
		do_action( 'crlms_before_add_quiz', $quiz );

		$quiz_id = wp_insert_post(
			apply_filters(
				'creator_lms_new_quiz_data', // Custom filter hook name
				[
					'post_title'   => $quiz->get_title(),
					'post_content' => $quiz->get_description(),
					'post_type'    => CREATOR_LMS_QUIZ_CPT,
					'post_status'  => 'publish',
				]
			)
		);

		if ($quiz_id && !is_wp_error($quiz_id)) {
			$quiz->set_id($quiz_id);
			update_post_meta($quiz_id, 'crlms_quiz_questions', $quiz->get_questions());
			update_post_meta($quiz_id, 'crlms_quiz_settings', $quiz->get_settings());

			$questions = apply_filters('creator_lms_quiz_questions_meta', $quiz->get_questions(), $quiz_id);
			update_post_meta($quiz_id, 'crlms_quiz_questions', $questions);

			$settings = apply_filters('creator_lms_quiz_settings_meta', $quiz->get_settings(), $quiz_id);
			update_post_meta($quiz_id, 'crlms_quiz_settings', $settings);

			/**
			 * Fires after quiz add
			 *
			 * @param string $quiz quiz object
			 * @since 1.0.0
			 */
			do_action( 'crlms_after_add_quiz', $quiz );
		}
	}

	/**
	 * Read a quiz.
	 *
	 * @param Quiz $quiz
	 * @since 1.0.0
	 */
	public function read(&$quiz) {
		$post = get_post($quiz->get_id());

		if ($post && $post->post_type === 'quiz') {
			$quiz->set_title($post->post_title);
			$quiz->set_description($post->post_content);
			$quiz->set_questions(get_post_meta($quiz->get_id(), 'crlms_quiz_questions', true));
			$quiz->set_settings(get_post_meta($quiz->get_id(), 'crlms_quiz_settings', true));
		}
	}

	/**
	 * Update an existing quiz.
	 *
	 * @param Quiz $quiz
	 * @since 1.0.0
	 */
	public function update(&$quiz): array
	{
		/**
		 * Before quiz update
		 * @param Quiz $quiz
		 * @since 1.0.0
		 */
		do_action('crlms_before_quiz_update', $quiz);

		$post_data = apply_filters('crlms_update_quiz', [
			'ID'           => $quiz->get_id(),
			'post_title'   => $quiz->get_title(),
			'post_content' => $quiz->get_description(),
		], $quiz);

		wp_update_post($post_data);

		$questions = apply_filters('crlms_update_quiz_questions_meta', $quiz->get_questions(), $quiz->get_id());
		$settings = apply_filters('crlms_update_quiz_settings_meta', $quiz->get_settings(), $quiz->get_id());

		update_post_meta($quiz->get_id(), 'crlms_quiz_questions', $questions);
		update_post_meta($quiz->get_id(), 'crlms_quiz_settings', $settings);

		/**
		 * After quiz update
		 * @param Quiz $quiz
		 * @since 1.0.0
		 */
		do_action('crlms_after_quiz_update', $quiz);

		return [
			'status' => "success",
			'message' => __( 'Quiz has been saved.', 'creator-lms' ),
		];
	}

	/**
	 * Delete a quiz.
	 *
	 * @param Quiz $quiz
	 * @param array $args
	 * @since 1.0.0
	 */
	public function delete(&$quiz, $args = array()) {

		/**
		 * Before quiz delete
		 * @param Quiz $quiz
		 * @since 1.0.0
		 */
		do_action('crlms_before_quiz_delete', $quiz);

		wp_delete_post($quiz->get_id(), true);

		/**
		 * After quiz delete
		 * @param Quiz $quiz
		 * @since 1.0.0
		 */
		do_action('crlms_after_quiz_delete', $quiz);

		return [
			'status' => "success",
			'message' => __( 'Quiz has been deleted.', 'creator-lms' ),
		];
	}
}

