<?php


function crlms_is_courses_page() {
	return ( is_post_type_archive( CREATOR_LMS_COURSE_CPT ) || is_page( crlms_get_page_id( 'course' ) ) );
}


function crlms_is_single_course_page() {
	return is_singular( CREATOR_LMS_COURSE_CPT );
}


function crlms_is_course_taxonomy() {
	return is_tax( get_object_taxonomies( CREATOR_LMS_COURSE_CPT ) );
}


function crlms_is_course_category( $term = '' ) {
	return is_tax( 'course_category', $term );
}
