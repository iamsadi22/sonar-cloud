<?php

namespace CreatorLms\PostTypes;

/**
 * Sections post type to connect with course
 * @since 1.0.0
 */
class SectionPostType  {

	public function __construct()
	{
		add_action( 'init', array( $this, 'register_section_cpt' ) );
	}

	/**
	 * Register section cpt
	 *
	 * @since 1.0.0
	 */
	public function register_section_cpt() {
		$labels = array(
			'name' => _x('Sections', 'Post Type General Name', 'creator-lms'),
			'singular_name' => _x('Section', 'Post Type Singular Name', 'creator-lms'),
			'menu_name' => __('Sections', 'creator-lms'),
			'name_admin_bar' => __('Section', 'creator-lms'),
			'archives' => __('Section Archives', 'creator-lms'),
			'attributes' => __('Section Attributes', 'creator-lms'),
			'parent_item_colon' => __('Parent Section:', 'creator-lms'),
			'all_items' => __('All Sections', 'creator-lms'),
			'add_new_item' => __('Add New Section', 'creator-lms'),
			'add_new' => __('Add New', 'creator-lms'),
			'new_item' => __('New Section', 'creator-lms'),
			'edit_item' => __('Edit Section', 'creator-lms'),
			'update_item' => __('Update Section', 'creator-lms'),
			'view_item' => __('View Section', 'creator-lms'),
			'view_items' => __('View Section', 'creator-lms'),
			'search_items' => __('Search Section', 'creator-lms'),
			'not_found' => __('Not found', 'creator-lms'),
			'not_found_in_trash' => __('Not found in Trash', 'creator-lms'),
			'featured_image' => __('Featured Image', 'creator-lms'),
			'set_featured_image' => __('Set featured image', 'creator-lms'),
			'remove_featured_image' => __('Remove featured image', 'creator-lms'),
			'use_featured_image' => __('Use as featured image', 'creator-lms'),
			'insert_into_item' => __('Insert in Section', 'creator-lms'),
			'uploaded_to_this_item' => __('Uploaded to this Section', 'creator-lms'),
			'items_list' => __('Sections list', 'creator-lms'),
			'items_list_navigation' => __('Sections list navigation', 'creator-lms'),
			'filter_items_list' => __('Filter sections list', 'creator-lms'),
		);
		$args = array(
			'label' => __('Section', 'creator-lms'),
			'description' => __('Sections to maintain lessons', 'creator-lms'),
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
//		register_post_type('crlms-section', $args);
	}
}
