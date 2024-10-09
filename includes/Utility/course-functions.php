<?php

/**
 * Get course object
 *
 * @param $course_id
 * @return bool|\CreatorLms\Data\Course
 * @throws Exception
 * @since 1.0.0
 */
function crlms_get_course( $course_id ) {
	return CRLMS()->course_factory->get_course( $course_id );
}

