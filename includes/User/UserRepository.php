<?php

namespace CreatorLms\user;

/**
 * Responsible to handle all user and enrollment related database operations
 * @since 1.0.0
 */
class UserRepository
{
	/**
	 * Find is user enrolled
	 * @param $course_id
	 * @param $user_id
	 * @return string|null
	 * @since 1.0.0
	 */
	public static function query_is_user_enrolled($course_id, $user_id): ?string
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'crlms_users_enrolled_in_courses';

		$query = $wpdb->prepare(
			"SELECT id FROM $table_name WHERE course_id = %d AND user_id = %d",
			$course_id,
			$user_id
		);

		return $wpdb->get_var($query);
	}

	/**
	 * Insert new enrollment
	 * @param $data
	 * @return array
	 * @since 1.0.0
	 */
	public static function insert_new_enrollment($data): array
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'crlms_users_enrolled_in_courses';
		try {
			$wpdb->insert($table_name, $data);
			return [
				'status'  => 'success',
				'message' => __('Successfully enrolled.', 'creator-lms'),
			];
		}
		catch ( \Exception $e ) {
			return [
				'status'  => 'error',
				'message' => __('Failed to enroll. Please try again.', 'creator-lms'),
				'original_message' => $e->getMessage(),
			];
		}
	}
}

