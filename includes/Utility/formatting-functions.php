<?php


/**
 * Converts a string to bool
 *
 * @param $string
 * @return bool
 * @since 1.0.0
 */
function crlms_string_to_bool( $string ) {
	$string = $string ?? '';
	return is_bool( $string ) ? $string : ( 'yes' === strtolower( $string ) || 1 === $string || 'true' === strtolower( $string ) || '1' === $string );
}


/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 *
 * @param $var
 * @return array|string
 * @since 1.0.0
 */
function crlms_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'crlms_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}


/**
 * Format decimal number
 *
 * @param $number
 * @param bool $dp
 * @param bool $trim_zeros
 * @return string|string[]|null
 * @since 1.0.0
 */
function crlms_format_decimal( $number, $dp = false, $trim_zeros = false ) {
	$number = $number ?? '';

	$locale   = localeconv();
	$decimals = array( crlms_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

	// Remove locale from string.
	if ( ! is_float( $number ) ) {
		$number = str_replace( $decimals, '.', $number );

		// Convert multiple dots to just one.
		$number = preg_replace( '/\.(?![^.]+$)|[^0-9.-]/', '', crlms_clean( $number ) );
	}

	if ( false !== $dp ) {
		$dp     = intval( '' === $dp ? crlms_get_price_decimals() : $dp );
		$number = number_format( floatval( $number ), $dp, '.', '' );
	} elseif ( is_float( $number ) ) {
		// DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
		$number = str_replace( $decimals, '.', sprintf( '%.' . crlms_get_rounding_precision() . 'f', $number ) );
		// We already had a float, so trailing zeros are not needed.
		$trim_zeros = true;
	}

	if ( $trim_zeros && strstr( $number, '.' ) ) {
		$number = rtrim( rtrim( $number, '0' ), '.' );
	}

	return $number;
}


/**
 * Get the price format depending on the currency position
 *
 * @return mixed|null
 * @since 1.0.0
 */
function crlms_get_price_format() {
	$currency_pos = get_option( 'creator_lms_currency_pos' );
	$format       = '%1$s%2$s';

	switch ( $currency_pos ) {
		case 'left':
			$format = '%1$s%2$s';
			break;
		case 'right':
			$format = '%2$s%1$s';
			break;
		case 'left_space':
			$format = '%1$s&nbsp;%2$s';
			break;
		case 'right_space':
			$format = '%2$s&nbsp;%1$s';
			break;
	}

	return apply_filters( 'creator_lms_price_format', $format, $currency_pos );
}


/**
 * Get price thousand separator
 *
 * @return string
 * @since 1.0.0
 */
function crlms_get_price_thousand_separator() {
	return stripslashes( apply_filters( 'creator_lms_get_price_thousand_separator', get_option( 'creator_lms_price_thousand_sep' ) ) );
}


/**
 * Get price decimal separator
 *
 * @return string
 * @since 1.0.0
 */
function crlms_get_price_decimal_separator() {
	$separator = apply_filters( 'creator_lms_get_price_decimal_separator', get_option( 'creator_lms_price_decimal_sep' ) );
	return $separator ? stripslashes( $separator ) : '.';
}


/**
 * Return the number of decimal
 *
 * @return int
 * @since 1.0.0
 */
function crlms_get_price_decimals() {
	return absint( apply_filters( 'creator_lms_get_price_decimals', get_option( 'creator_lms_price_num_decimals', 2 ) ) );
}


/**
 * Format a price with Creator LMS currency settings
 *
 * @param $value
 * @return mixed|void
 * @since 1.0.0
 */
function crlms_format_localized_price( $value ) {
	return apply_filters( 'creator_lms_format_localized_price', str_replace( '.', crlms_get_price_decimal_separator(), strval( $value ) ), $value );
}


/**
 * Get currency symbol
 *
 * @param $currency
 * @return mixed|null
 * @since 1.0.0
 */
function crlms_get_currency_symbol( $currency = '' ) {
	if ( ! $currency ) {
		$currency = get_crlms_currency();
	}

	$symbols = get_crlms_currency_symbols();

	$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

	return apply_filters( 'creator_lms_currency_symbol', $currency_symbol, $currency );
}


/**
 * Format a price with currency symbol
 *
 * @param $price
 * @param $args
 * @return string
 * @since 1.0.0
 */
function crlms_price( $price, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'currency'           => '',
			'decimal_separator'  => crlms_get_price_decimal_separator(),
			'thousand_separator' => crlms_get_price_thousand_separator(),
			'decimals'           => crlms_get_price_decimals(),
			'price_format'       => crlms_get_price_format(),
		)
	);

	$price 		= (float) $price;
	$negative	= $price < 0;
	$price 		= number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );

	$formatted_price = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], '<span class="crlms-price-currency-symbol">' . get_crlms_currency_symbol( $args['currency'] ) . '</span>', $price );
	return '<span class="crlms-price-amount amount"><bdi>' . $formatted_price . '</bdi></span>';
}


/**
 * Implode and escape HTML attributes for an HTML element.
 *
 * @param $attributes
 * @return string
 * @since 1.0.0
 */
function crlms_implode_html_attributes( $attributes ) {
	$_attributes = array();
	foreach ( $attributes as $name => $value ) {
		$_attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
	}
	return implode( ' ', $_attributes );
}


/**
 * Convert a string representation of a date/time into a Unix timestamp.
 *
 * @param string $time_string The date/time string to convert.
 * @param int|null $from_timestamp Optional. The timestamp to use as a base for relative date/time strings. Default is null.
 * @return int|false The Unix timestamp on success, or false on failure.
 *
 * @link https://github.com/woocommerce/woocommerce/blob/5907114d6eabae41edf39c593a36345b92990b38/plugins/woocommerce/includes/wc-formatting-functions.php#L703
 * @see wc_string_to_timestamp()
 *
 * @since 1.0.0
 */
function crlms_string_to_timestamp( $time_string, $from_timestamp = null ) {
	$time_string = $time_string ?? '';

	$original_timezone = date_default_timezone_get();

	date_default_timezone_set( 'UTC' );

	if ( null === $from_timestamp ) {
		$next_timestamp = strtotime( $time_string );
	} else {
		$next_timestamp = strtotime( $time_string, $from_timestamp );
	}

	date_default_timezone_set( $original_timezone );

	return $next_timestamp;
}


/**
 * Get the timezone offset in seconds.
 *
 * @return int The timezone offset in seconds.
 * @link https://github.com/woocommerce/woocommerce/blob/5907114d6eabae41edf39c593a36345b92990b38/plugins/woocommerce/includes/wc-formatting-functions.php#L808
 * @see wc_timezone_offset()
 * @since 1.0.0
 */
function crlms_timezone_offset() {
	$timezone = get_option( 'timezone_string' );

	if ( $timezone ) {
		$timezone_object = new \DateTimeZone( $timezone );
		return $timezone_object->getOffset( new \DateTime( 'now' ) );
	} else {
		return floatval( get_option( 'gmt_offset', 0 ) ) * HOUR_IN_SECONDS;
	}
}


/**
 * Get the timezone string.
 *
 * @return string The timezone string.
 * @since 1.0.0
 */
function crlms_timezone_string() {
	// Added in WordPress 5.3 Ref https://developer.wordpress.org/reference/functions/wp_timezone_string/.
	if ( function_exists( 'wp_timezone_string' ) ) {
		return wp_timezone_string();
	}

	// If site timezone string exists, return it.
	$timezone = get_option( 'timezone_string' );
	if ( $timezone ) {
		return $timezone;
	}

	// Get UTC offset, if it isn't set then return UTC.
	$utc_offset = floatval( get_option( 'gmt_offset', 0 ) );
	if ( ! is_numeric( $utc_offset ) || 0.0 === $utc_offset ) {
		return 'UTC';
	}

	// Adjust UTC offset from hours to seconds.
	$utc_offset = (int) ( $utc_offset * 3600 );

	// Attempt to guess the timezone string from the UTC offset.
	$timezone = timezone_name_from_abbr( '', $utc_offset );
	if ( $timezone ) {
		return $timezone;
	}

	// Last try, guess timezone string manually.
	foreach ( timezone_abbreviations_list() as $abbr ) {
		foreach ( $abbr as $city ) {
			// WordPress restrict the use of date(), since it's affected by timezone settings, but in this case is just what we need to guess the correct timezone.
			if ( (bool) date( 'I' ) === (bool) $city['dst'] && $city['timezone_id'] && intval( $city['offset'] ) === $utc_offset ) { // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				return $city['timezone_id'];
			}
		}
	}

	// Fallback to UTC.
	return 'UTC';
}
