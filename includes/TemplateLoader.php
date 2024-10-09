<?php

namespace CreatorLms;

/**
 * Class TemplateLoader
 *
 * @package CreatorLms
 * @since 1.0.0
 */
class TemplateLoader {

	/**
	 * Course page id
	 *
	 * @var int $course_page_id
	 * @since 1.0.0
	 */
	private static $course_page_id = 0;


	/**
	 * TemplateLoader constructor.
	 * @since 1.0.0
	 */
	public static function init(): void {
		add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );
		add_filter('template_include', array( __CLASS__ , 'load_template'));
	}

	/**
	 * Load the template
	 *
	 * @param $template
	 * @return string
	 * @since 1.0.0
	 */
	public static function load_template( $template ): string {
		if ( is_embed() ) {
			return $template;
		}
		$default_file = self::get_template_loader_default_file();

		if ( $default_file ) {
			$search_files = self::get_template_loader_files( $default_file );
			$template     = locate_template( $search_files );

			if ( ! $template ) {
				$template = CREATOR_LMS_PATH . '/templates/' . $default_file;
			}
		}
		return $template;
	}


	/**
	 * Get the default file for the template loader.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private static function get_template_loader_default_file(): string {

		if ( is_singular( CREATOR_LMS_COURSE_CPT ) ) {
			$default_file = 'single-course.php';
		} elseif ( crlms_is_courses_page() || crlms_is_course_category() || crlms_is_course_taxonomy() ) {
			$default_file = 'archive-course.php';
		} else {
			$default_file = '';
		}
		return $default_file;
	}


	/**
	 * Get the files to search for the template loader.
	 *
	 * @param $default_file
	 * @return mixed|null
	 * @since 1.0.0
	 */
	private static function get_template_loader_files( $default_file ) {
		$templates   = apply_filters( 'creator_lms_template_loader_files', array(), $default_file );

		if ( is_page_template() ) {
			$page_template = get_page_template_slug();

			if ( $page_template ) {
				$validated_file = validate_file( $page_template );
				if ( 0 === $validated_file ) {
					$templates[] = $page_template;
				} else {
					error_log( "Creator LMS: Unable to validate template path: \"$page_template\". Error Code: $validated_file." ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
			}
		}

		if ( is_singular( CREATOR_LMS_COURSE_CPT ) ) {
			$object       = get_queried_object();
			$name_decoded = urldecode( $object->post_name );
			if ( $name_decoded !== $object->post_name ) {
				$templates[] = "single-product-{$name_decoded}.php";
			}
			$templates[] = "single-product-{$object->post_name}.php";
		}

		$templates[] = $default_file;
		if ( isset( $cs_default ) ) {
			$templates[] = crlms_template_path() . $cs_default;
		}
		$templates[] = crlms_template_path() . $default_file;

		return array_unique( $templates );
	}


	/**
	 * @param \WP_Query $q
	 * @return \WP_Query
	 */
	public static function pre_get_posts( \WP_Query $q ): \WP_Query {
		if ( ! $q->is_main_query() && ! is_admin() ) {
			return $q;
		}

		if ( is_page( crlms_get_page_id( 'course' ) ) ) {
			$q->set( 'post_type', CREATOR_LMS_COURSE_CPT );
			$q->set( 'posts_per_page', 6 );
			$q->set( 'page_id', '' );
			return $q;
		}

		return $q;
	}
}

add_action( 'init', array( 'CreatorLms\TemplateLoader', 'init' ) );

