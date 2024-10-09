<?php

namespace CreatorLms\CPTData;
use CreatorLms\Abstracts\Data;

/**
 * Class PostTypeData
 *
 * This class represents a post type in the CreatorLMS system. It extends the base Data class and provides
 * methods for managing post type data, including getting and setting properties, saving, and deleting post types.
 *
 * @package CreatorLms\CPTData
 * @since 1.0.0
 */
Class PostTypeData  extends Data {

	/**
	 * Get the post type name.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_name(): string {
		return $this->get_prop( 'name' );
	}

	/**
	 * Get the post type description.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_description(): string {
		return $this->get_prop( 'description' );
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
	 * Get short description
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_short_description() {
		return $this->get_prop('short_description');
	}

	/**
	 * Get status of the post type
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_status() {
		return $this->get_prop('status');
	}

	/**
	 * Get permalink of the post type
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function get_permalink(): string {
		return get_permalink( $this->get_id() );
	}


	/**
	 * Get post type created date.
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
	 * Get post type featured image id
	 *
	 * @param $context
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function get_thumbnail_id( $context = 'view' ) {
		return $this->get_prop('thumbnail_id', $context);
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
	 * Set the date when the post type was created.
	 *
	 * @param string|null $date The date when the post type was created.
	 * @since 1.0.0
	 */
	public function set_date_created( $date = null ) {
		$this->set_date_prop( 'date_created', $date );
	}

}
