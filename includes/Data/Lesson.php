<?php

namespace CreatorLms\Data;

use CreatorLms\CPTData\PostTypeData;
use CreatorLms\DataStores\DataStores;

defined( 'ABSPATH' ) || exit;

/**
 * Class Lesson
 *
 * This class represents a lesson in the CreatorLMS system. It extends the base Data class and provides
 * methods for managing lesson data, including getting and setting properties, saving, and deleting lessons.
 *
 * @package CreatorLms\Data
 * @since 1.0.0
 */
class Lesson extends PostTypeData {

	/**
	 * Name of the store
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected string $data_store_name = 'lesson';


	/**
	 * Object type
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $object_type = 'lesson';


	/**
	 * Lesson data array
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected array $data = array(
		'name' 				=> '',
		'description' 		=> '',
		'thumbnail_id'		=> '',
		'slug'				=> '',
		'status' 			=> '',
		'type' 				=> 'text',
		'featured'          => false,
		'date_created'       => null,
		'date_modified'      => null,
	);


	/**
	 * Lesson constructor.
	 *
	 * @param $lesson
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function __construct( $lesson = '' ) {
		if ( is_numeric( $lesson ) && $lesson > 0 ) {
			$this->set_id( $lesson );
		} elseif ( $lesson instanceof self ) {
			$this->set_id( absint( $lesson->get_id() ) );
		} elseif ( ! empty( $lesson->ID ) ) {
			$this->set_id( absint( $lesson->ID ) );
		}

		// load the data store
		$this->data_store = DataStores::load( $this->data_store_name );

		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}


	/**
	 * Get the lesson type.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_type(): string {
		return $this->get_prop( 'type' );
	}
	
	
	/**
	 * Set the lesson type.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function set_type( $type ) {
		$this->set_prop('type', $type );
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
	 * Check if lesson exists or not
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function exists(): bool {
		return true;
	}


	/**
	 * Check if lesson is purchasable
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_purchasable(): bool {
		return apply_filters('creator_lms_lesson_is_purchasable', true, $this );
	}


	/**
	 * Check if lesson is available for enrolment
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_in_stock(): bool {
		return apply_filters('creator_lms_lesson_is_in_stock', true, $this );
	}

	/**
	 * Save or update the lesson object in DB
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function save() {
		if ( ! $this->data_store ) {
			return $this->get_id();
		}

		/**
		 * Fires before saving the lesson object.
		 *
		 * This action allows developers to perform custom actions before the lesson object is saved.
		 *
		 * @param Lesson $this The lesson object being saved.
		 * @param DataStores $data_store The data store object handling the lesson data.
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
		 * Fires after saving the lesson object.
		 *
		 * This action allows developers to perform custom actions after the lesson object is saved.
		 *
		 * @param Lesson $this The lesson object being saved.
		 * @param DataStores $data_store The data store object handling the lesson data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_after_' . $this->object_type . '_object_save', $this, $this->data_store );

		return $this->get_id();
	}


	/**
	 * Delete lesson data
	 *
	 * @param array $args
	 * @since 1.0.0
	 */
	public function delete( $args = array() ) {
		$this->data_store->delete($this, $args );
	}
}
