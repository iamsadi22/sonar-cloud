<?php

namespace CreatorLms\Lesson;

/**
 * Responsible to handle all lesson request validation
 * @since 1.0.0
 */
class LessonValidator
{
	/**
	 * Validate add lesson params
	 * @return array[]
	 * @since 1.0.0
	 */
	public static function validate_add_lesson_params(): array
	{
		return array(
			'title' => array(
				'required' => true,
				'validate_callback' => function( $param, $request, $key ) {
					if ( !is_string( $param ) ) {
						return new WP_Error(
							'rest_invalid_param',
							__('Title must ba a string.', 'creator-lms'),
							array( 'status' => 400 ) );
					}
					if ( strlen( $param ) > 100 ) {
						return new WP_Error(
							'rest_invalid_param',
							__('Title must not be greater than 100 characters.', 'creator-lms'),
							array( 'status' => 400 )
						);
					}
					return true;
				}
			),
		);
	}
}

