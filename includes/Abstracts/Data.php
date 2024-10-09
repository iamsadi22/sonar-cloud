<?php

namespace CreatorLms\Abstracts;


use CreatorLms\CreatorLmsDateTime;
use CreatorLms\DataException;

abstract class Data {

	/**
	 * ID of this object
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $id = 0;


	/**
	 * Data for this object
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $data = array();


	/**
	 * Reference of the data store
	 *
	 * @var DataStore
	 * @since 1.0.0
	 */
	protected $data_store;


	/**
	 * Name of the data store
	 *
	 * @var $data_store_name
	 * @since 1.0.0
	 */
	protected string $data_store_name;


	/**
	 * Object type
	 *
	 * @var string $object_type
	 * @since 1.0.0
	 */
	protected string $object_type = 'data';

	/**
	 * Save the object data to the data store. If the object already exists, it updates the data,
	 * otherwise, it creates a new entry.
	 *
	 * @return int The ID of the saved object.
	 * @since 1.0.0
	 */
	public function save() {
		if ( ! $this->data_store ) {
			return $this->get_id();
		}

		/**
		 * Action hook before saving the object object.
		 *
		 * @param object $this The object object being saved.
		 * @param DataStores $data_store The data store handling object data.
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_before_' . $this->object_type . '_object_save', $this, $this->data_store );

		// If the object already has an ID, update it, otherwise, create a new entry.
		if ( $this->get_id() ) {
			$this->data_store->update( $this );
		} else {
			$this->data_store->create( $this );
		}

		/**
		 * Action hook after saving the object object.
		 *
		 * @param object $this The object object being saved.
		 * @param DataStores $data_store The data store handling object data.
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_after_' . $this->object_type . '_object_save', $this, $this->data_store );

		return $this->get_id();
	}

	/**
	 * Get the unique id of this object
	 *
	 * @return int
	 * @since  2.6.0
	 */
	public function get_id() {
		return $this->id;
	}


	/**
	 * Get the store object
	 *
	 * @return DataStore
	 * @since 1.0.0
	 */
	public function get_data_store() {
		return $this->data_store;
	}

	/**
	 * Set ID of object
	 *
	 * @param $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}


	/**
	 * Set collection of params to the object
	 *
	 * @param $props
	 * @return bool|\WP_Error
	 */
	public function set_props( $props ) {
		$errors = false;
		foreach ( $props as $prop => $value ) {
			try {
				if ( is_null( $value ) ) {
					continue;
				}
				$setter = "set_$prop";

				if ( is_callable( array( $this, $setter ) ) ) {
					$this->{$setter}( $value );
				}
			} catch ( \Exception $e ) {
				if ( ! $errors ) {
					$errors = new \WP_Error();
				}
				$errors->add( $e->getErrorCode(), $e->getMessage(), array( 'property_name' => $prop ) );
			}
		}

		return $errors && count( $errors->get_error_codes() ) ? $errors : true;
	}


	/**
	 * Set prop
	 *
	 * @param $prop
	 * @param $value
	 * @since 1.0.0
	 */
	protected function set_prop( $prop, $value ) {
		if ( array_key_exists( $prop, $this->data ) ) {
			$this->data[ $prop ] = $value;
		}
	}


	/**
	 * Get the value of data property
	 *
	 * @param string $prop Name of the property to get the value of.
	 * @param string $context What kind of context to get the value in. Valid values are 'view' and 'edit'.
	 * @return mixed|null
	 * @since 1.0.0
	 */
	protected function get_prop( string $prop, string $context = 'view' ): mixed {
		$value = null;

		if ( array_key_exists( $prop, $this->data ) ) {
			$value = $this->data[ $prop ];

			if ( 'view' === $context ) {
				$value = apply_filters( $this->get_hook_prefix() . $prop, $value, $this );
			}
		}
		return $value;
	}


	/**
	 * Get the hook prefix
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_hook_prefix() {
		return 'creator_lms_' . $this->object_type . '_get_';
	}


	/**
	 * Set a date property.
	 *
	 * @param string $prop The name of the property to set.
	 * @param mixed $value The value to set for the property. Can be a string, timestamp, or CreatorLmsDateTime object.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/5907114d6eabae41edf39c593a36345b92990b38/plugins/woocommerce/includes/abstracts/abstract-wc-data.php#L898
	 * @since 1.0.0
	 */
	protected function set_date_prop( $prop, $value ) {
		try {
			if ( empty( $value ) || '0000-00-00 00:00:00' === $value ) {
				$this->set_prop( $prop, null );
				return;
			}

			if ( is_a( $value, 'CreatorLmsDateTime' ) ) {
				$datetime = $value;
			} elseif ( is_numeric( $value ) ) {
				// Timestamps are handled as UTC timestamps in all cases.
				$datetime = new CreatorLmsDateTime( "@{$value}", new \DateTimeZone( 'UTC' ) );
			} else {
				// Strings are defined in local WP timezone. Convert to UTC.
				if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $value, $date_bits ) ) {
					$offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : crlms_timezone_offset();
					$timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
				} else {
					$timestamp = crlms_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', crlms_string_to_timestamp( $value ) ) ) );
				}
				$datetime = new CreatorLmsDateTime( "@{$timestamp}", new \DateTimeZone( 'UTC' ) );
			}

			// Set local timezone or offset.
			if ( get_option( 'timezone_string' ) ) {
				$datetime->setTimezone( new \DateTimeZone( crlms_timezone_string() ) );
			} else {
				$datetime->set_utc_offset( crlms_timezone_string() );
			}

			$this->set_prop( $prop, $datetime );
		} catch ( \Exception $e ) {} // @codingStandardsIgnoreLine.
	}


	/**
	 * Throw a DataException with the given parameters.
	 *
	 * @param string $code The error code.
	 * @param string $message The error message.
	 * @param int $http_status_code The HTTP status code (default is 400).
	 * @param array $data Additional data to pass with the exception.
	 * @throws \CreatorLms\DataException
	 *
	 * @since 1.0.0
	 */
	protected function error( $code, $message, $http_status_code = 400, $data = array() ) {
		throw new DataException( $code, $message, $http_status_code, $data );
	}
}
