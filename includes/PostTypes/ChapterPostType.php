<?php

namespace CreatorLms\PostTypes;

/**
 * Chapter post type to connect with topics
 * @since 1.0.0
 */
class ChapterPostType  {

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
			'name' => _x('Chapters', 'Post Type General Name', 'creator-lms'),
			'singular_name' => _x('Chapter', 'Post Type Singular Name', 'creator-lms'),
			'menu_name' => __('Chapters', 'creator-lms'),
			'name_admin_bar' => __('Chapter', 'creator-lms'),
			'archives' => __('Chapter Archives', 'creator-lms'),
			'attributes' => __('Chapter Attributes', 'creator-lms'),
			'parent_item_colon' => __('Parent Chapter:', 'creator-lms'),
			'all_items' => __('All Chapters', 'creator-lms'),
			'add_new_item' => __('Add New Chapter', 'creator-lms'),
			'add_new' => __('Add New', 'creator-lms'),
			'new_item' => __('New Chapter', 'creator-lms'),
			'edit_item' => __('Edit Chapter', 'creator-lms'),
			'update_item' => __('Update Chapter', 'creator-lms'),
			'view_item' => __('View Chapter', 'creator-lms'),
			'view_items' => __('View Chapter', 'creator-lms'),
			'search_items' => __('Search Chapter', 'creator-lms'),
			'not_found' => __('Not found', 'creator-lms'),
			'not_found_in_trash' => __('Not found in Trash', 'creator-lms'),
			'featured_image' => __('Featured Image', 'creator-lms'),
			'set_featured_image' => __('Set featured image', 'creator-lms'),
			'remove_featured_image' => __('Remove featured image', 'creator-lms'),
			'use_featured_image' => __('Use as featured image', 'creator-lms'),
			'insert_into_item' => __('Insert in Chapter', 'creator-lms'),
			'uploaded_to_this_item' => __('Uploaded to this Chapter', 'creator-lms'),
			'items_list' => __('Chapters list', 'creator-lms'),
			'items_list_navigation' => __('Chapters list navigation', 'creator-lms'),
			'filter_items_list' => __('Filter Chapters list', 'creator-lms'),
		);

		$args = array(
			'label' => __('Chapter', 'creator-lms'),
			'description' => __('Chapters to maintain lessons', 'creator-lms'),
			'labels' => $labels,
			'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
			'taxonomies' => array('category', 'post_tag'),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
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
		register_post_type('crlms-chapter', $args);
	}
}
