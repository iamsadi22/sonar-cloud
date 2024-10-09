<?php

namespace CreatorLms\PostTypes;

use CreatorLms\Abstracts\PostType;
use CreatorLms\Membership\MembershipHelper;

defined( 'ABSPATH' ) || exit();

class MembershipPostType extends PostType {

	/**
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * Initialize post type
	 * @return self|null
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}



	public function __construct() {
		$this->post_type = 'crlms-membership';
		parent::__construct();

		add_action( 'add_meta_boxes', [$this, 'membership_add_plan_meta_box'] );

	}


	/**
	 * Get arguments of CPT - crlms-membership
	 *
	 * @return array|void
	 * @since 1.0.0
	 */
	public function get_args() {

		$labels = array(
			'name' => _x('Membership', 'Post Type General Name', 'creator-lms'),
			'singular_name' => _x('Membership', 'Post Type Singular Name', 'creator-lms'),
			'menu_name' => __('Memberships', 'creator-lms'),
			'name_admin_bar' => __('Membership', 'creator-lms'),
			'archives' => __('Membership Archives', 'creator-lms'),
			'attributes' => __('Membership Attributes', 'creator-lms'),
			'parent_item_colon' => __('Parent Membership:', 'creator-lms'),
			'all_items' => __('All Memberships', 'creator-lms'),
			'add_new_item' => __('Add New Membership', 'creator-lms'),
			'add_new' => __('Add New', 'creator-lms'),
			'new_item' => __('New Membership', 'creator-lms'),
			'edit_item' => __('Edit Membership', 'creator-lms'),
			'update_item' => __('Update Membership', 'creator-lms'),
			'view_item' => __('View Membership', 'creator-lms'),
			'view_items' => __('View Membership', 'creator-lms'),
			'search_items' => __('Search Membership', 'creator-lms'),
			'not_found' => __('Not found', 'creator-lms'),
			'not_found_in_trash' => __('Not found in Trash', 'creator-lms'),
			'featured_image' => __('Featured Image', 'creator-lms'),
			'set_featured_image' => __('Set featured image', 'creator-lms'),
			'remove_featured_image' => __('Remove featured image', 'creator-lms'),
			'use_featured_image' => __('Use as featured image', 'creator-lms'),
			'insert_into_item' => __('Insert in Membership', 'creator-lms'),
			'uploaded_to_this_item' => __('Uploaded to this Membership', 'creator-lms'),
			'items_list' => __('Memberships list', 'creator-lms'),
			'items_list_navigation' => __('Memberships list navigation', 'creator-lms'),
			'filter_items_list' => __('Filter Memberships list', 'creator-lms'),
		);


		// CPT supports
		$supports   = array(  'title', 'editor', 'excerpt'  );

		$this->args = array(
			'labels'             => $labels,
			'public'             => true,
			'query_var'          => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'has_archive'        => false,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'show_in_admin_bar'  => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'show_in_menu' 	     => false,
			'supports'           => $supports,
			'hierarchical'       => false,
		);

		return $this->args;

	}

	/**
	 * Membership plan meta box initialization
	 * @return void
	 * @since 1.0.0
	 */
	public function membership_add_plan_meta_box() {
		add_meta_box(
			'membership_meta_box',
			__( 'Membership Details', 'creator-lms' ),
			[$this, 'membership_meta_box_callback'],
			'crlms-membership',
			'normal',
			'high'
		);
	}

	/**
	 * Membership details metabox callback
	 * @param $post
	 * @return void
	 * @since 1.0.0
	 */
	public function membership_meta_box_callback( $post ) {

		wp_nonce_field( 'membership_save_meta_box_data', 'membership_meta_box_nonce' );

		// == implement plan details == //
		$membership_plans = get_post_meta( $post->ID, 'crlms_membership_plans', true );
		$membership_plans = is_array( $membership_plans ) ? $membership_plans : array();

		$courses = get_posts( array(
			'post_type' => 'crlms-course',
			'post_status'    => 'publish',
			'numberposts' => -1
		) );

		$subscription_options = MembershipHelper::subscription_options();

		?>
		<h2><?php _e( 'Membership Plans:', 'creator-lms' ); ?></h2>
		<div id="membership_plans_container">
			<?php foreach ( $membership_plans as $index => $plan ) : ?>
				<div class="membership_plan">
					<input type="hidden" id="membership_plan_id_<?php echo $index; ?>" name="membership_plan_id[]" value="<?php echo esc_attr( $plan['plan_id'] ); ?>" />
					<p>
						<label for="membership_plan_title_<?php echo $index; ?>"><?php _e( 'Plan Title', 'creator-lms' ); ?></label>
						<input type="text" id="membership_plan_title_<?php echo $index; ?>" name="membership_plan_title[]" value="<?php echo esc_attr( $plan['title'] ); ?>" />
					</p>
					<p>
						<label for="membership_plan_price_<?php echo $index; ?>"><?php _e( 'Plan Price', 'creator-lms' ); ?></label>
						<input type="number" id="membership_plan_price_<?php echo $index; ?>" name="membership_plan_price[]" value="<?php echo esc_attr( $plan['price'] ); ?>" step="0.01" />
					</p>
					<p>
						<label for="subscription_type_<?php echo esc_attr( $index ); ?>"><?php _e( 'Subscription Type', 'creator-lms' ); ?></label>
						<select id="subscription_type_<?php echo esc_attr( $index ); ?>" name="subscription_type[<?php echo esc_attr( $index ); ?>]" class="subscription_type">
							<?php
								foreach ( $subscription_options as $subscription_key => $subscription ) {
									?>
									<option value="<?php echo $subscription_key; ?>" <?php selected( $plan['subscription'], $subscription_key ); ?>><?php echo $subscription; ?></option>
									<?php
								}
							?>
						</select>
					</p>
					<p>
						<label for="connected_courses_<?php echo $index; ?>"><?php _e( 'Connected Courses', 'creator-lms' ); ?></label>
						<select id="connected_courses_<?php echo $index; ?>" name="connected_courses[<?php echo $index; ?>][]" multiple="multiple" class="connected_courses" style="width: 100%;">
							<?php
							$connected_courses = isset( $plan['courses'] ) ? $plan['courses'] : array();
							foreach ( $courses as $course ) {
								echo '<option value="' . esc_attr( $course->ID ) . '"' . ( in_array( $course->ID, $connected_courses ) ? ' selected="selected"' : '' ) . '>' . esc_html( $course->post_title ) . '</option>';
							}
							?>
						</select>
					</p>
					<button type="button" class="button button-secondary remove_membership_plan"><?php _e( 'Remove Plan', 'creator-lms' ); ?></button>
					<hr>
				</div>
			<?php endforeach; ?>
		</div>
		<p>
			<button type="button" id="add_membership_plan" class="button"><?php _e( 'Add Another Plan', 'creator-lms' ); ?></button>
		</p>
		<div class="crlms-loader" ></div>
		<p>
			<button type="button" id="membership_save_button" class="button button-primary"><?php _e( 'Save Membership Details', 'creator-lms' ); ?></button>
		</p>
		<div class="crlms-notice"></div>

		<style>
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


MembershipPostType::instance();
