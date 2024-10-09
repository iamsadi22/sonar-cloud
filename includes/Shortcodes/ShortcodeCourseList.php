<?php

namespace CreatorLms\Shortcodes;

use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Course List Shortcode
 *
 * Class ShortcodeCourseList
 * @package CreatorLms\Shortcodes
 * @since 1.0.0
 */
class ShortcodeCourseList {

	/**
	 * Render the course list
	 *
	 * @since 1.0.0
	 */
	public static function output($atts): void
	{
		self::course_list($atts);
	}


	/**
	 * Show the list.
	 *
	 * @since 1.0.0
	 */
	private static function course_list($atts)
	{
		$args = array(
			'post_type' => 'crlms-course',
			'post_status' => 'publish',
			'posts_per_page' => -1
		);
		$courses = new WP_Query($args);

		ob_start();

		// Apply filter before displaying course list
		echo apply_filters('crlms_before_display_course_list', '');

		// Change buy now button text
		$add_to_cart_button_text = apply_filters('crlms_add_to_cart_button_text', __('Add to cart', 'creator-lms'));

		if ($courses->have_posts()) {
			?>
			<ul class="course-list">
				<?php
				while ($courses->have_posts()) {
					$courses->the_post();

					$course_id = get_the_ID();
					$price = get_post_meta($course_id, 'crlms_course_price', true);
					$duration = get_post_meta($course_id, 'crlms_course_duration', true);

					if($price) {
						?>
						<li class="course-item">
							<h2 class="course-title"><?php echo get_the_title(); ?></h2>
							<p class="course-duration"><?php printf(__('Duration: %s', 'creator-lms'), $duration); ?></p>
							<div class="course-description"><?php echo get_the_excerpt(); ?></div>
							<p class="course-price"><?php printf(__('Price: %s', 'creator-lms'), '$'. number_format( $price, 2 ) ); ?></p>
							<button class="crlms-add-to-cart" data-id="<?php echo get_the_ID(); ?>"><?php echo $add_to_cart_button_text; ?></button>
						</li>
						<?php
					}
				}
				?>
			</ul>
			<style>
				.course-list {
					list-style: none;
					padding: 0;
					margin: 0;
					display: flex;
					flex-wrap: wrap;
					gap: 20px;
				}
				.course-item {
					width: calc(33.333% - 20px);
					box-sizing: border-box;
					border: 1px solid #ddd;
					padding: 10px;
					text-align: center;
				}
				.course-title {
					margin-top: 0;
				}
				.course-description {
					margin-bottom: 10px;
				}
				.course-price {
					font-weight: bold;
				}
				.crlms-add-to-cart {
					background-color: #0073aa;
					color: #fff;
					border: none;
					padding: 10px 20px;
					cursor: pointer;
				}
				.crlms-add-to-cart:hover {
					background-color: #005a87;
				}
			</style>
			<?php
			wp_reset_postdata();
		} else {
			?>
			<p><?php __('No courses found.', 'creator-lms'); ?></p>
			<?php
		}

		// Apply filter after displaying course list
		echo apply_filters('crlms_after_display_course_list', '');

		echo ob_get_clean();
	}

}
