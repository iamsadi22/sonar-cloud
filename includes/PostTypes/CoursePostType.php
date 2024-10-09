<?php

namespace CreatorLms\PostTypes;

use CreatorLms\Abstracts\PostType;

defined( 'ABSPATH' ) || exit();

class CoursePostType extends PostType {

	/**
	 * @var null
	 */
	protected static $_instance = null;


	/**
	 * @return CoursePostType|null
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}



	public function __construct() {
		$this->post_type = 'crlms-course';
		parent::__construct();

		add_action( 'init', array( $this, 'register_taxonomy' ) );

		add_action( 'add_meta_boxes', [$this, 'course_details_meta_boxes'] );
	}

	/**
	 * Course details mata boxes
	 * @return void
	 * @since 1.0.0
	 */
	public function course_details_meta_boxes() {
		add_meta_box(
			'course_detail_meta_box',
			__( 'Course Details', 'creator-lms' ),
			[$this, 'course_details_meta_box_callback'],
			'crlms-course',
			'normal',
			'high'
		);
	}

	/**
	 * Get arguments of CPT - crlms-course
	 *
	 * @return array|void
	 * @since 1.0.0
	 */
	public function get_args() {

		$labels = array(
			'name' => _x('Courses', 'Post Type General Name', 'creator-lms'),
			'singular_name' => _x('Course', 'Post Type Singular Name', 'creator-lms'),
			'menu_name' => __('Courses', 'creator-lms'),
			'name_admin_bar' => __('Course', 'creator-lms'),
			'archives' => __('Course Archives', 'creator-lms'),
			'attributes' => __('Course Attributes', 'creator-lms'),
			'parent_item_colon' => __('Parent Course:', 'creator-lms'),
			'all_items' => __('All Courses', 'creator-lms'),
			'add_new_item' => __('Add New Course', 'creator-lms'),
			'add_new' => __('Add New', 'creator-lms'),
			'new_item' => __('New Course', 'creator-lms'),
			'edit_item' => __('Edit Course', 'creator-lms'),
			'update_item' => __('Update Course', 'creator-lms'),
			'view_item' => __('View Course', 'creator-lms'),
			'view_items' => __('View Courses', 'creator-lms'),
			'search_items' => __('Search Course', 'creator-lms'),
			'not_found' => __('Not found', 'creator-lms'),
			'not_found_in_trash' => __('Not found in Trash', 'creator-lms'),
			'featured_image' => __('Featured Image', 'creator-lms'),
			'set_featured_image' => __('Set featured image', 'creator-lms'),
			'remove_featured_image' => __('Remove featured image', 'creator-lms'),
			'use_featured_image' => __('Use as featured image', 'creator-lms'),
			'insert_into_item' => __('Insert in course', 'creator-lms'),
			'uploaded_to_this_item' => __('Uploaded to this Course', 'creator-lms'),
			'items_list' => __('Courses list', 'creator-lms'),
			'items_list_navigation' => __('Courses list navigation', 'creator-lms'),
			'filter_items_list' => __('Filter Courses list', 'creator-lms'),
		);

		// Get permalink structure of Creator LMS courses
		$permalinks = crlms_get_permalink_structure();

		// Get course archive page ID and set archive page
		$course_page_id	= crlms_get_page_id( 'course' );
		$has_archive 	= $course_page_id && get_post( $course_page_id ) ? urldecode( get_page_uri( $course_page_id ) ) : 'course';

		// CPT supports
		$supports   = array( 'title', 'editor', 'thumbnail', 'revisions', 'comments', 'excerpt' );

		$this->args = array(
			'labels'             => $labels,
			'public'             => true,
			'query_var'          => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'has_archive'        => $has_archive,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'show_in_admin_bar'  => true,
			'show_in_nav_menus'  => false,
			'show_in_rest'       => true,
			'show_in_menu' 	     => false,
			'taxonomies'         => array( 'course_category', 'course_tag' ),
			'supports'           => $supports,
			'hierarchical'       => false,
			'rewrite'        => $permalinks['course_rewrite_slug'] ? array(
				'slug'       => '/'.$permalinks['course_rewrite_slug'],
				'with_front' => false,
				'feeds'      => true,
			) : false,
		);

		return $this->args;

	}


	/**
	 * Taxonomy added for course
	 * @return void
	 * @since 1.0.0
	 */
	public function register_taxonomy() {
		$permalinks = crlms_get_permalink_structure();

		register_taxonomy(
			'course_category',
			array( $this->post_type ),
			array(
				'hierarchical'          => true,
				'label'                 => __( 'Categories', 'creator-lms' ),
				'labels'                => array(
					'name'          => __( 'Course Categories', 'creator-lms' ),
					'menu_name'     => __( 'Course Category', 'creator-lms' ),
					'singular_name' => __( 'Category', 'creator-lms' ),
					'add_new_item'  => __( 'Add A New Course Category', 'creator-lms' ),
					'all_items'     => __( 'All Categories', 'creator-lms' ),
				),
				'show_in_rest'          => true,
				'show_ui'               => true,
				'query_var'             => true,
				'rewrite'               => array(
					'slug'         => $permalinks['category_rewrite_slug'],
					'with_front'   => false,
					'hierarchical' => true,
				),
			)
		);

		register_taxonomy(
			'course_tag',
			array( $this->post_type ),
			array(
				'hierarchical'          => false,
				'label'                 => __( 'Tags', 'creator-lms' ),
				'labels'                => array(
					'name'          => __( 'Course Tags', 'creator-lms' ),
					'menu_name'     => __( 'Course Tag', 'creator-lms' ),
					'singular_name' => __( 'Tag', 'creator-lms' ),
					'add_new_item'  => __( 'Add A New Course Tag', 'creator-lms' ),
					'all_items'     => __( 'All Tags', 'creator-lms' ),
				),
				'show_in_rest'          => true,
				'show_ui'               => true,
				'query_var'             => true,
				'rewrite'               => array(
					'slug'         => $permalinks['category_rewrite_slug'],
					'with_front'   => false,
					'hierarchical' => true,
				),
			)
		);
	}

	/**
	 * Course details contents
	 * @param $post
	 * @return void
	 * @since 1.0.0
	 */
	public function course_details_meta_box_callback($post) {

		$course_duration = get_post_meta($post->ID, 'crlms_course_duration', true);
		$course_price = get_post_meta($post->ID, 'crlms_course_price', true);
		$sale_price = get_post_meta($post->ID, 'crlms_course_sale_price', true);
		$max_students_allowed = get_post_meta($post->ID, 'crlms_course_max_student_allowed', true);
		$max_retake_allowed = get_post_meta($post->ID, 'crlms_course_max_retake_allowed', true);
		$passing_grade = get_post_meta($post->ID, 'crlms_course_passing_grade', true);

		// Nonce field for security
		wp_nonce_field('crlms_course_nonce', 'nonce');
		?>
		<div class="crlms-course-meta-box">
			<h3><?php _e('General Settings', 'creator-lms') ?></h3>
			<ul class="course-meta-list">
				<li>
					<label for="crlms_course_duration"><?php _e('Duration:', 'creator-lms'); ?></label>
					<input type="text" id="crlms_course_duration" name="crlms_course_duration" value="<?php echo esc_attr($course_duration); ?>" />
				</li>
				<li>
					<label for="crlms_course_price"><?php _e('Course Price:', 'creator-lms'); ?></label>
					<input type="text" id="crlms_course_price" name="crlms_course_price" value="<?php echo esc_attr($course_price); ?>" />
				</li>
				<li>
					<label for="crlms_course_sale_price"><?php _e('Sale Price:', 'creator-lms'); ?></label>
					<input type="text" id="crlms_course_sale_price" name="crlms_course_sale_price" value="<?php echo esc_attr($sale_price); ?>" />
				</li>
				<li>
					<label for="crlms_course_max_student_allowed"><?php _e('Max Students Allowed:', 'creator-lms'); ?></label>
					<input type="text" id="crlms_course_max_student_allowed" name="crlms_course_max_student_allowed" value="<?php echo esc_attr($max_students_allowed); ?>" />
				</li>
				<li>
					<label for="crlms_course_max_retake_allowed"><?php _e('Max Retake Allowed:', 'creator-lms'); ?></label>
					<input type="text" id="crlms_course_max_retake_allowed" name="crlms_course_max_retake_allowed" value="<?php echo esc_attr($max_retake_allowed); ?>" />
				</li>
				<li>
					<label for="crlms_course_passing_grade"><?php _e('Passing Grade (%):', 'creator-lms'); ?></label>
					<input type="text" id="crlms_course_passing_grade" name="crlms_course_passing_grade" value="<?php echo esc_attr($passing_grade); ?>" />
				</li>
			</ul>
		</div>

		<div class="crlms-loader" ></div>
		<button id="save-course-data" class="button button-primary"><?php _e('Save Course Data', 'creator-lms') ?></button>
		<div class="crlms-notice"></div>

		<style>
			.crlms-course-meta-box {
				margin-bottom: 20px;
			}

			.course-meta-list {
				list-style: none;
				padding: 0;
			}

			.course-meta-list li {
				margin-bottom: 10px;
			}

			.course-meta-list label {
				display: inline-block;
				width: 150px;
				font-weight: bold;
			}

			.course-meta-list input[type="text"] {
				width: 300px;
				padding: 5px;
				border: 1px solid #ccc;
			}

			.crlms-loader {
				display: none;
				text-align: center;
				margin-top: 10px;
			}

			.crlms-loader .spinner {
				display: inline-block;
				float: left;
				width: 20px;
				height: 20px;
				vertical-align: middle;
				border: 3px solid rgba(0, 0, 0, 0.1);
				border-left-color: #0073aa;
				border-radius: 50%;
				animation: spin 0.8s linear infinite;
			}

			@keyframes spin {
				to {
					transform: rotate(360deg);
				}
			}

			.crlms-notice {
				display: none;
				margin-top: 10px;
			}

			.crlms-notice .notice {
				padding: 10px;
				border-radius: 3px;
			}

			.notice-success {
				background-color: #d4edda;
				border-color: #c3e6cb;
				color: #155724;
			}

			.notice-error {
				background-color: #f8d7da;
				border-color: #f5c6cb;
				color: #721c24;
			}
		</style>
		<?php
	}

}


CoursePostType::instance();
