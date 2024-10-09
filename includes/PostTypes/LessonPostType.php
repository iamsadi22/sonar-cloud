<?php

namespace CreatorLms\PostTypes;

/**
 * Lesson post type to connect with topics
 * @since 1.0.0
 */
class LessonPostType  {

	/**
	 * Post type initialization
	 */
	public function __construct()
	{
		add_action( 'init', array( $this, 'register_lesson_cpt' ) );
	}

	/**
	 * Register section cpt
	 *
	 * @since 1.0.0
	 */
	public function register_lesson_cpt(): void
	{
		$labels = array(
			'name' => _x('Lessons', 'Post Type General Name', 'creator-lms'),
			'singular_name' => _x('Lesson', 'Post Type Singular Name', 'creator-lms'),
			'menu_name' => __('Lessons', 'creator-lms'),
			'name_admin_bar' => __('Lesson', 'creator-lms'),
			'archives' => __('Lesson Archives', 'creator-lms'),
			'attributes' => __('Lesson Attributes', 'creator-lms'),
			'parent_item_colon' => __('Parent Lesson:', 'creator-lms'),
			'all_items' => __('All Lessons', 'creator-lms'),
			'add_new_item' => __('Add New Lesson', 'creator-lms'),
			'add_new' => __('Add New', 'creator-lms'),
			'new_item' => __('New Lesson', 'creator-lms'),
			'edit_item' => __('Edit Lesson', 'creator-lms'),
			'update_item' => __('Update Lesson', 'creator-lms'),
			'view_item' => __('View Lesson', 'creator-lms'),
			'view_items' => __('View Lesson', 'creator-lms'),
			'search_items' => __('Search Lesson', 'creator-lms'),
			'not_found' => __('Not found', 'creator-lms'),
			'not_found_in_trash' => __('Not found in Trash', 'creator-lms'),
			'featured_image' => __('Featured Image', 'creator-lms'),
			'set_featured_image' => __('Set featured image', 'creator-lms'),
			'remove_featured_image' => __('Remove featured image', 'creator-lms'),
			'use_featured_image' => __('Use as featured image', 'creator-lms'),
			'insert_into_item' => __('Insert in Lesson', 'creator-lms'),
			'uploaded_to_this_item' => __('Uploaded to this Lesson', 'creator-lms'),
			'items_list' => __('Lessons list', 'creator-lms'),
			'items_list_navigation' => __('Lessons list navigation', 'creator-lms'),
			'filter_items_list' => __('Filter Lessons list', 'creator-lms'),
		);

		$args = array(
			'label' => __('Lesson', 'creator-lms'),
			'description' => __('Lessons to maintain lessons', 'creator-lms'),
			'labels' => $labels,
			'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
			'taxonomies' => array('category', 'post_tag'),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'menu_position' => 5,
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => false,
			'can_export' => true,
			'has_archive' => true,
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'capability_type' => 'post',
			'show_in_rest' => true,
		);
		register_post_type('crlms-lesson', $args);
	}
}
