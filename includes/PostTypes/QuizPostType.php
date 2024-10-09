<?php

namespace CreatorLms\PostTypes;

/**
 * Quiz post type to connect with topics
 * @since 1.0.0
 */
class QuizPostType  {

	/**
	 * Post type initialization
	 */
	public function __construct()
	{
		add_action( 'init', array( $this, 'register_quiz_cpt' ) );
	}

	/**
	 * Register section cpt
	 *
	 * @since 1.0.0
	 */
	public function register_quiz_cpt(): void
	{
		$labels = array(
			'name' => _x('Quiz', 'Post Type General Name', 'creator-lms'),
			'singular_name' => _x('Quiz', 'Post Type Singular Name', 'creator-lms'),
			'menu_name' => __('Quiz', 'creator-lms'),
			'name_admin_bar' => __('Quiz', 'creator-lms'),
			'archives' => __('Quiz Archives', 'creator-lms'),
			'attributes' => __('Quiz Attributes', 'creator-lms'),
			'parent_item_colon' => __('Parent Quiz:', 'creator-lms'),
			'all_items' => __('All Quiz', 'creator-lms'),
			'add_new_item' => __('Add New Quiz', 'creator-lms'),
			'add_new' => __('Add New', 'creator-lms'),
			'new_item' => __('New Quiz', 'creator-lms'),
			'edit_item' => __('Edit Quiz', 'creator-lms'),
			'update_item' => __('Update Quiz', 'creator-lms'),
			'view_item' => __('View Quiz', 'creator-lms'),
			'view_items' => __('View Quiz', 'creator-lms'),
			'search_items' => __('Search Quiz', 'creator-lms'),
			'not_found' => __('Not found', 'creator-lms'),
			'not_found_in_trash' => __('Not found in Trash', 'creator-lms'),
			'featured_image' => __('Featured Image', 'creator-lms'),
			'set_featured_image' => __('Set featured image', 'creator-lms'),
			'remove_featured_image' => __('Remove featured image', 'creator-lms'),
			'use_featured_image' => __('Use as featured image', 'creator-lms'),
			'insert_into_item' => __('Insert in Quiz', 'creator-lms'),
			'uploaded_to_this_item' => __('Uploaded to this Quiz', 'creator-lms'),
			'items_list' => __('Quiz list', 'creator-lms'),
			'items_list_navigation' => __('Quiz list navigation', 'creator-lms'),
			'filter_items_list' => __('Filter Quiz list', 'creator-lms'),
		);

		$args = array(
			'label' => __('Quiz', 'creator-lms'),
			'description' => __('Quiz to maintain quiz and questions', 'creator-lms'),
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
		register_post_type('crlms-quiz', $args);
	}
}
