<?php

namespace CreatorLms\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Class DataStore
 * @package CreatorLms\Abstracts
 * @since 1.0.0
 */
abstract class DataStore {

	protected $must_exist_meta_keys = array();

	/**
	 * Create new record
	 *
	 * @param $data Data
	 * @return mixed
	 * @since 1.0.0
	 */
	public abstract function create( &$data );

	/**
	 * Read new record
	 *
	 * @param $data Data
	 * @return mixed
	 * @since 1.0.0
	 */
	public abstract function read( &$data );


	/**
	 * Update new record
	 *
	 * @param $data Data
	 * @return mixed
	 * @since 1.0.0
	 */
	public abstract function update( &$data );


	/**
	 * Delete new record
	 *
	 * @param $data
	 * @param array $args
	 * @return mixed
	 * @since 1.0.0
	 */
	public abstract function delete( &$data, $args = array() );


	/**
	 * @param $object
	 * @param $meta_key
	 * @param $meta_value
	 * @return bool
	 */
	protected function update_or_delete_post_meta( $object, $meta_key, $meta_value ) {
		if ( in_array( $meta_value, array( array(), '' ), true ) && ! in_array( $meta_key, $this->must_exist_meta_keys, true ) ) {
			$updated = delete_post_meta( $object->get_id(), $meta_key );
		} else {
			$updated = update_post_meta( $object->get_id(), $meta_key, $meta_value );
		}

		return (bool) $updated;
	}

}
