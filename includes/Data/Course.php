<?php

namespace CreatorLms\Data;

use CreatorLms\Abstracts\Data;
use CreatorLms\DataStores\DataStores;

defined( 'ABSPATH' ) || exit;

/**
 * Class Course
 * @package CreatorLms\Data
 * @since 1.0.0
 */
class Course extends Data {

	/**
	 * Name of the store
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected string $data_store_name = 'course';


	/**
	 * Object type
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $object_type = 'course';


	/**
	 * Course data array
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected array $data = array(
		'name' 					=> '',
		'description' 			=> '',
		'thumbnail_id'			=> '',
		'slug'					=> '',
		'status' 				=> 'draft',
		'featured'          	=> false,
		'price'              	=> '',
		'regular_price'      	=> '',
		'sale_price'         	=> '',
		'sale_price_dates_from' => null,
		'sale_price_dates_to'   => null,
		'date_created'       	=> null,
		'date_modified'      	=> null,
		'level'				 	=> 'beginner',
		'availability'			=>  false,
		'available_date'		=>  '',
		'accessibility'			=> 'public',
		'capacity'				=>  false,
		'limit'					=>  0,
	);


	/**
	 * Course constructor.
	 *
	 * @param $course
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function __construct( $course = '' ) {
		if ( is_numeric( $course ) && $course > 0 ) {
			$this->set_id( $course );
		} elseif ( $course instanceof self ) {
			$this->set_id( absint( $course->get_id() ) );
		} elseif ( ! empty( $course->ID ) ) {
			$this->set_id( absint( $course->ID ) );
		}

		// load the data store
		$this->data_store = DataStores::load( $this->data_store_name );

		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}


	/**
	 * Get name of the object
	 *
	 * @return mixed
	 */
	public function get_name() {
		return $this->get_prop('name');
	}


	/**
	 * Get name
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_slug() {
		return $this->get_prop('slug');
	}

	/**
	 * Get description
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_description() {
		return $this->get_prop('description');
	}


	/**
	 * Get short description
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_short_description() {
		return $this->get_prop('short_description');
	}


	/**
	 * Get status of the course
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_status() {
		return $this->get_prop('status');
	}


	/**
	 * Get permalink of the course
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function get_permalink(): string {
		return get_permalink( $this->get_id() );
	}


	/**
	 * Get price of the course
	 *
	 * @param string $context
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_price( string $context = 'view' ) {
		return $this->get_prop( 'price', $context );
	}


	/**
	 * Get regular price of the course
	 *
	 * @param string $context
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_regular_price( string $context = 'view' ): mixed {
		return $this->get_prop( 'regular_price', $context );
	}


	/**
	 * Get sale price of the course
	 *
	 * @param string $context
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_sale_price( string $context = 'view' ): mixed {
		return $this->get_prop( 'sale_price', $context );
	}


	/**
	 * Get price html
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_price_html() {
		$price_html = '';
		if ( '' === $this->get_price() ) {
			$price_html .= apply_filters(
				'creator_lms_free_course_price_html',
				sprintf( '<span class="free">%s</span>', esc_html__( 'Free', 'creator-lms' ) )
			);
		} elseif ( $this->is_on_sale() ) {
			$price_html .= apply_filters(
				'creator_lms_course_sale_price_html',
				sprintf(
					'<del><bdi>%s</bdi></del> <ins><bdi>%s</bdi></ins>',
					crlms_price( $this->get_regular_price() ),
					crlms_price( $this->get_sale_price() )
				)
			);
		} else {
			$price_html .= apply_filters(
				'creator_lms_course_price_html',
				crlms_price( $this->get_price() )
			);
		}

		return apply_filters( 'creator_lms_get_price_html', $price_html, $this );
	}


	/**
	 * Get course created date.
	 *
	 * @param $context
	 * @return mixed|null
	 *
	 * @since 1.0.0
	 */
	public function get_date_created( $context = 'view' ) {
		return $this->get_prop( 'date_created', $context );
	}
	
	
	/**
	 * Get course created date.
	 *
	 * @param $context
	 * @return mixed|null
	 *
	 * @since 1.0.0
	 */
	public function get_date_modified( $context = 'view' ) {
		return $this->get_prop( 'date_modified', $context );
	}


	/**
	 * Get course featured image id
	 *
	 * @param $context
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_thumbnail_id( $context = 'view' ) {
		return $this->get_prop('thumbnail_id', $context);
	}

	/**
	 * Get date on sale from.
	 *
	 * @param $context
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_sale_price_dates_from( $context = 'view' ) {
		return $this->get_prop( 'date_on_sale_from', $context );
	}

	/**
	 * Get date on sale to.
	 *
	 * @param $context
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_sale_price_dates_to( $context = 'view' ) {
		return $this->get_prop( 'date_on_sale_to', $context );
	}


	/**
	 * Get average rating
	 *
	 * @param $context
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_average_rating( $context = 'view' ) {
		return $this->get_prop( 'average_rating', $context );
	}


	/**
	 * Get review count
	 *
	 * @param $context
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_review_count( $context = 'view' ) {
		return $this->get_prop( 'review_count', $context );
	}

	/**
	 * Get rating counts
	 *
	 * @param $context
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_rating_counts( $context = 'view' ) {
		return $this->get_prop( 'rating_counts', $context );
	}


	/**
	 * Get the add to url used mainly in loops.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function add_to_cart_url(): string {
		return apply_filters( 'creator_lms_course_add_to_cart_url', $this->get_permalink(), $this );
	}


	/**
	 * Get course add to cart text
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function add_to_cart_text(): mixed {
		return apply_filters( 'creator_lms_course_add_to_cart_text', __( 'Enroll now', 'creator-lms' ) );
	}


	/**
	 * Get the level of the course.
	 *
	 * @param string $context The context for getting the level. Default is 'view'.
	 * @return mixed|null The level of the course.
	 *
	 * @since 1.0.0
	 */
	public function get_level( $context = 'view' ) {
		return $this->get_prop( 'level', $context );
	}


	/**
	 * Get the availability of the course.
	 *
	 * @param bool $context The context for getting the availability. Default is false.
	 * @return mixed|null The availability of the course.
	 *
	 * @since 1.0.0
	 */
	public function get_availability( $context = 'view' ) {
		return $this->get_prop( 'availability', $context );
	}

	/**
	 * Get the available date of the course.
	 *
	 * @param string $context The context for getting the available date. Default is ''.
	 * @return mixed|null The available date of the course.
	 *
	 * @since 1.0.0
	 */
	public function get_available_date( $context = 'view' ) {
		return $this->get_prop( 'available_date', $context );
	}

	/**
	 * Get the accessibility of the course.
	 *
	 * @param string $context The context for getting the accessibility. Default is ''.
	 * @return mixed|null The accessibility of the course.
	 *
	 * @since 1.0.0
	 */
	public function get_accessibility( $context = 'view' ) {
		return $this->get_prop( 'accessibility', $context );
	}

	/**
	 * Get the capacity of the course.
	 *
	 * @param bool $context The context for getting the capacity. Default is false.
	 * @return mixed|null The capacity of the course.
	 *
	 * @since 1.0.0
	 */
	public function get_capacity( $context = 'view' ) {
		return $this->get_prop( 'capacity', $context );
	}

	/**
	 * Get the limit of the course.
	 *
	 * @param int $context The context for getting the limit. Default is false.
	 * @return mixed|null The limit of the course.
	 *
	 * @since 1.0.0
	 */
	public function get_limit( $context = 'view' ) {
		return $this->get_prop( 'limit', $context );
	}

	/**
	 * Get the chapters of the course.
	 *
	 * @return array The chapters of the course.
	 * @since 1.0.0
	 */
	public function get_chapters() {
		return $this->data_store->get_chapters( $this );
	}

	/**
	 * Check if course is exists otr not
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function exists(): bool {
		return true;
	}


	/**
	 * Check if course is purchasable
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_purchasable(): bool {
		return true;
	}


	/**
	 * Check if course is available for enrolment
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_in_stock(): bool {
		return true;
	}

	/**
	 * Check if course is on sale
	 *
	 * @param string $context
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_on_sale( $context = 'view' ) {
		if ( '' !== (string) $this->get_sale_price( $context ) && $this->get_regular_price( $context ) > $this->get_sale_price( $context ) ) {
			$on_sale = true;
		} else {
			$on_sale = false;
		}
		return 'view' === $context ? apply_filters( 'creator_lms_product_is_on_sale', $on_sale, $this ) : $on_sale;
	}


	/*
	 * ************************************
	 * Setters
	 * ************************************
	 */


	/**
	 * Set status
	 *
	 * @param $status
	 * @since 1.0.0
	 */
	public function set_status( $status ) {
		$this->set_prop('status', $status );
	}


	/**
	 * Set name
	 *
	 * @param $name
	 * @since 1.0.0
	 */
	public function set_name( $name ) {
		$this->set_prop('name', $name );
	}


	/**
	 * Set short description
	 *
	 * @param $short_description
	 * @since 1.0.0
	 */
	public function set_short_description( $short_description ) {
		$this->set_prop('short_description', $short_description );
	}

	/**
	 * Set description
	 *
	 * @param $description
	 * @since 1.0.0
	 */
	public function set_description($description) {
		$this->set_prop('description', $description);
	}


	/**
	 * Set featured image id
	 *
	 * @param $image_id
	 * @return void
	 * @since 1.0.0
	 */
	public function set_thumbnail_id( $image_id ) {
		$this->set_prop('thumbnail_id', $image_id );
	}

	/**
	 * Set slug
	 *
	 * @param $slug
	 * @since 1.0.0
	 */
	public function set_slug( $slug ) {
		$this->set_prop('slug', $slug );
	}


	/**
	 * Set price of the course
	 *
	 * @param $price
	 * @return void
	 * @since 1.0.0
	 */
	public function set_price( $price ) {
		$this->set_prop('price', $price );
	}


	/**
	 * Set regular price of the course
	 *
	 * @param $regular_price
	 * @return void
	 * @since 1.0.0
	 */
	public function set_regular_price( $regular_price ) {
		$this->set_prop('regular_price', $regular_price );
	}


	/**
	 * Set sale price of the course
	 *
	 * @param $sale_price
	 * @return void
	 * @since 1.0.0
	 */
	public function set_sale_price( $sale_price ) {
		$this->set_prop('sale_price', $sale_price );
	}

	/**
	 * Set the date when the sale price starts.
	 *
	 * @param string|null $date The date when the sale price starts.
	 * @since 1.0.0
	 */
	public function set_sale_price_dates_from( $date = null ) {
		$this->set_date_prop( 'sale_price_dates_to', $date );
	}

	/**
	 * Set the date when the sale price ends.
	 *
	 * @param string|null $date The date when the sale price ends.
	 * @since 1.0.0
	 */
	public function set_sale_price_dates_to( $date = null ) {
		$this->set_prop('sale_price', $date );
	}


	/**
	 * Set the date when the course was created.
	 *
	 * @param string|null $date The date when the course was created.
	 * @since 1.0.0
	 */
	public function set_date_created( $date = null ) {
		$this->set_date_prop( 'date_created', $date );
	}
	
	/**
	 * Set the date when the course was created.
	 *
	 * @param string|null $date The date when the course was created.
	 * @since 1.0.0
	 */
	public function set_date_modified( $date = null ) {
		$this->set_date_prop( 'date_modified', $date );
	}

	/**
	 * Set the level of the course.
	 *
	 * @param mixed $level The level of the course.
	 *
	 * @since 1.0.0
	 */
	public function set_level( $level ) {
		$this->set_prop( 'level', $level );
	}


	/**
	 * Set the availability of the course.
	 *
	 * @param mixed $availability The availability of the course.
	 *
	 * @since 1.0.0
	 */
	public function set_availability( $availability ) {
		$this->set_prop( 'availability', $availability );
	}

	/**
	 * Set the available date of the course.
	 *
	 * @param mixed $available_date The available date of the course.
	 *
	 * @since 1.0.0
	 */
	public function set_available_date( $available_date ) {
		$this->set_prop( 'available_date', $available_date );
	}

	/**
	 * Set the capacity date of the course.
	 *
	 * @param mixed $capacity The capacity date of the course.
	 *
	 * @since 1.0.0
	 */
	public function set_capacity( $capacity ) {
		$this->set_prop( 'capacity', $capacity );
	}

	/**
	 * Set the limit date of the course.
	 *
	 * @param mixed $limit The limit date of the course.
	 *
	 * @since 1.0.0
	 */
	public function set_limit( $limit ) {
		$this->set_prop( 'limit', $limit );
	}

	/**
	 * Set the accessibility date of the course.
	 *
	 * @param mixed $accessibility The limit date of the course.
	 *
	 * @since 1.0.0
	 */
	public function set_accessibility( $accessibility ) {
		$this->set_prop( 'accessibility', $accessibility );
	}


	/**
	 * Set the chapters of the course.
	 *
	 * @param array $chapters The chapters of the course.
	 *
	 * @since 1.0.0
	 */
	public function set_chapters( $chapters ) {
		return $this->data_store->set_chapters( $this, $chapters );
	}


	/**
	 * Save or update the course object in DB
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function save() {
		if ( ! $this->data_store ) {
			return $this->get_id();
		}

		/**
		 * Fires before saving the course object.
		 *
		 * This action allows developers to perform custom actions before the course object is saved.
		 *
		 * @param Course $this The course object being saved.
		 * @param DataStores $data_store The data store object handling the course data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_before_' . $this->object_type . '_object_save', $this, $this->data_store );


		if ( $this->get_id() ) {
			$this->data_store->update( $this );
		} else {
			$this->data_store->create( $this );
		}

		/**
		 * Fires after saving the course object.
		 *
		 * This action allows developers to perform custom actions after the course object is saved.
		 *
		 * @param Course $this The course object being saved.
		 * @param DataStores $data_store The data store object handling the course data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_after_' . $this->object_type . '_object_save', $this, $this->data_store );

		return $this->get_id();
	}


	/**
	 * Delete course data
	 *
	 * @param array $args
	 * @since 1.0.0
	 */
	public function delete( $args = array() ) {
		$this->data_store->delete($this, $args );
	}
}
