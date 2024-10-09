<?php

namespace CreatorLms\PostTypes;

/**
 * Question post type to connect with topics
 * @since 1.0.0
 */
class QuestionPostType  {

	/**
	 * Post type initialization
	 */
	public function __construct()
	{
		add_action( 'init', array( $this, 'register_question_cpt' ) );
	}

	/**
	 * Register section cpt
	 *
	 * @since 1.0.0
	 */
	public function register_question_cpt(): void
	{
		$labels = array(
			'name' => _x('Questions', 'Post Type General Name', 'creator-lms'),
			'singular_name' => _x('Question', 'Post Type Singular Name', 'creator-lms'),
			'menu_name' => __('Questions', 'creator-lms'),
			'name_admin_bar' => __('Question', 'creator-lms'),
			'archives' => __('Question Archives', 'creator-lms'),
			'attributes' => __('Question Attributes', 'creator-lms'),
			'parent_item_colon' => __('Parent Question:', 'creator-lms'),
			'all_items' => __('All Questions', 'creator-lms'),
			'add_new_item' => __('Add New Question', 'creator-lms'),
			'add_new' => __('Add New', 'creator-lms'),
			'new_item' => __('New Question', 'creator-lms'),
			'edit_item' => __('Edit Question', 'creator-lms'),
			'update_item' => __('Update Question', 'creator-lms'),
			'view_item' => __('View Question', 'creator-lms'),
			'view_items' => __('View Question', 'creator-lms'),
			'search_items' => __('Search Question', 'creator-lms'),
			'not_found' => __('Not found', 'creator-lms'),
			'not_found_in_trash' => __('Not found in Trash', 'creator-lms'),
			'featured_image' => __('Featured Image', 'creator-lms'),
			'set_featured_image' => __('Set featured image', 'creator-lms'),
			'remove_featured_image' => __('Remove featured image', 'creator-lms'),
			'use_featured_image' => __('Use as featured image', 'creator-lms'),
			'insert_into_item' => __('Insert in Question', 'creator-lms'),
			'uploaded_to_this_item' => __('Uploaded to this Question', 'creator-lms'),
			'items_list' => __('Questions list', 'creator-lms'),
			'items_list_navigation' => __('Questions list navigation', 'creator-lms'),
			'filter_items_list' => __('Filter Questions list', 'creator-lms'),
		);

		$args = array(
			'label' => __('Question', 'creator-lms'),
			'description' => __('Questions to maintain Questions', 'creator-lms'),
			'labels' => $labels,
			'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
			'taxonomies' => array('category', 'post_tag'),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'menu_position' => 5,
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'can_export' => true,
			'has_archive' => true,
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'capability_type' => 'post',
			'show_in_rest' => true,
		);
		register_post_type('crlms-question', $args);
	}
}
