<?php

/**
 * Get lesson object
 *
 * @param $lesson_id
 * @return bool|\CreatorLms\Data\Lesson
 * @throws Exception
 * @since 1.0.0
 */
function crlms_get_lesson( $lesson_id ) {
	return CRLMS()->lesson_factory->get_lesson( $lesson_id );
}

