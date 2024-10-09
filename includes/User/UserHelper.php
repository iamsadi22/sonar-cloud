<?php

namespace CreatorLms\user;

/**
 * Responsible to handle all user and enrollment related operations
 * @since 1.0.0
 */
class UserHelper
{
	/**
	 * Is user enrolled im given course
	 * @param $course_id
	 * @param $user_id
	 * @return bool
	 * @since 1.0.0
	 */
	public static function is_user_enrolled($course_id, $user_id): bool
	{
		$enrollment_id = UserRepository::query_is_user_enrolled($course_id, $user_id);
		return !empty($enrollment_id); // Return true if user is enrolled, false otherwise
	}
}

