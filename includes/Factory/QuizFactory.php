<?php

namespace CreatorLms\Factory;

use CreatorLms\Data\Quiz;

class QuizFactory {

	/**
	 * Get quiz object
	 *
	 * @param bool $quiz_id
	 * @return bool|Quiz
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function get_quiz( $quiz_id = false ) {
		$quiz_id = $this->get_quiz_id( $quiz_id );

		if ( ! $quiz_id ) {
			return false;
		}

		return new Quiz($quiz_id);
	}


	/**
	 * Get quiz id
	 *
	 * @param $quiz
	 * @return bool|int
	 * @since 1.0.0
	 */
	private function get_quiz_id( $quiz ) {
		global $post;

		if ( false === $quiz && isset( $post, $post->ID ) && CREATOR_LMS_QUIZ_CPT === get_post_type( $post->ID ) ) {
			return absint( $post->ID );
		} elseif ( is_numeric( $quiz ) ) {
			return $quiz;
		} elseif ( $quiz instanceof Quiz ) {
			return $quiz->get_id();
		} elseif ( ! empty( $quiz->ID ) ) {
			return $quiz->ID;
		} else {
			return false;
		}
	}
}
