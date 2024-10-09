<?php

/**
 * Get other templates passing attributes and including the file.
 *
 * @param $template_name
 * @param array $args
 * @param string $template_path
 * @param string $default_path
 * @since 1.0.0
 */
function crlms_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	if ( false === strpos( $template_name, '.php' ) ) {
		$template_name .= '.php';
	}

	$template = crlms_locate_template( $template_name, $template_path, $default_path );

	// Allow 3rd party plugin filter template file from their plugin.
	$filter_template = apply_filters( 'creator_lms_get_template', $template, $template_name, $args, $template_path, $default_path );

	if ( $filter_template !== $template ) {
		if ( ! file_exists( $filter_template ) ) {
			/* translators: %s template */
			_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'creator-lms' ), '<code>' . $filter_template . '</code>' ), '1.0' );
			return;
		}
		$template = $filter_template;
	}

	$action_args = array(
		'template_name' => $template_name,
		'template_path' => $template_path,
		'located'       => $template,
		'args'          => $args,
	);


	if ( ! empty( $args ) && is_array( $args ) ) {
		if ( isset( $args['action_args'] ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				__( 'action_args should not be overwritten when calling crlms_get_template.', 'creator-lms' ),
				'1.0.0'
			);
			unset( $args['action_args'] );
		}
		extract( $args ); // @codingStandardsIgnoreLine
	}

	do_action( 'creator_lms_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );

	include $action_args['located'];

	do_action( 'creator_lms_after_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
}


/**
 * Locate template
 *
 * @param $template_name
 * @param string $template_path
 * @param string $default_path
 * @return mixed|void
 * @since 1.0.0
 */
function crlms_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = apply_filters( 'creator_lms_template_path', 'creator-lms/' );
	}

	if ( ! $default_path ) {
		$default_path = apply_filters( 'creator_lms_template_path', CREATOR_LMS_PATH ) . '/templates/';
	}

	if ( empty( $template ) ) {
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);
	}

	if ( ! isset( $template ) || ! $template ) {
		$template = trailingslashit( $default_path ) . $template_name;
	}

	// Return what we found.
	return apply_filters( 'creator_lms_locate_template', $template, $template_name, $template_path );
}


/**
 * Get template part
 *
 * @param $slug
 * @param string $name
 * @since 1.0.0
 */
function crlms_get_template_part( $slug, $name = '' ) {
	if ( $name ) {
		$template = locate_template(
			array(
				"{$slug}-{$name}.php",
				crlms_template_path() . "{$slug}-{$name}.php",
			)
		);
		if ( ! $template ) {
			$fallback = CREATOR_LMS_PATH . "/templates/{$slug}-{$name}.php";
			$template = file_exists( $fallback ) ? $fallback : '';
		}
	}
	$template = apply_filters( 'crlms_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}


/**
 * Set Cookie
 *
 * @param $name
 * @param $value
 * @param int $expire
 * @param bool $secure
 * @param bool $httponly
 * @since 1.0.0
 */
function crlms_setcookie( $name, $value, $expire = 0, $secure = false, $httponly = false  ) {
	if ( ! headers_sent() ) {
		$options = array(
			'expires'  => $expire,
			'secure'   => $secure,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN,
			'httponly' => $httponly
		);
		setcookie( $name, $value, $options );
	}
}


/**
 * Check if the home URL is https. If it is, we don't need to do things such as 'force ssl'.
 *
 * @since  1.0.0
 * @return bool
 */
function crlms_site_is_https() {
	return false !== strstr( get_option( 'home' ), 'https:' );
}



/**
 * Display Creator LMS tooltip
 *
 * @param $tip
 * @param bool $allow_html
 * @return string
 * @since 1.0.0
 */
function crlms_help_tip( $tip ) {
	$sanitized_tip = esc_attr( $tip );
	return apply_filters( 'creator_lms_help_tip', '<span class="crlms-help-tip" tabindex="0" aria-label="' . $sanitized_tip . '" data-tip="' . $sanitized_tip . '"></span>', $sanitized_tip, $tip );
}



/**
 * @return mixed|void
 * @since 1.0.0
 */
function crlms_get_rounding_precision() {
	$precision = crlms_get_price_decimals() + 2;
	if ( $precision < absint( 6 ) ) {
		$precision = absint( 6 );
	}

	return $precision;
}


/**
 * Get template path
 *
 * @return mixed|null
 * @since 1.0.0
 */
function crlms_template_path() {
	return apply_filters( 'creator_lms_template_path', 'creator-lms/' );
}


/**
 * Get permalink settings for Course
 *
 * @return array
 * @since 1.0.0
 */
function crlms_get_permalink_structure() {
	$saved_permalinks = (array) get_option( 'creator_lms_permalinks', array() );
	$permalinks       = wp_parse_args(
		array_filter( $saved_permalinks ),
		array(
			'course_base'		=> _x( 'cr-all-course', 'slug', 'creator-lms' ),
			'category_base'		=> _x( 'course-category', 'slug', 'creator-lms' ),
			'tag_base'			=> _x( 'course-tag', 'slug', 'creator-lms' ),
			'membership_base'			=> _x( 'cr-all-members', 'slug', 'creator-lms' ),
		)
	);

	if ( $saved_permalinks !== $permalinks ) {
		update_option( 'creator_lms_permalinks', $permalinks );
	}

	$permalinks['course_rewrite_slug']   = untrailingslashit( $permalinks['course_base'] );
	$permalinks['membership_rewrite_slug']   = untrailingslashit( $permalinks['membership_base'] );
	$permalinks['category_rewrite_slug']  = untrailingslashit( $permalinks['category_base'] );
	$permalinks['tag_rewrite_slug']       = untrailingslashit( $permalinks['tag_base'] );

	return $permalinks;
}


/**
 * Get Base Currency Code.
 *
 * @return string
 */
function get_crlms_currency() {
	return apply_filters( 'creator_lms_currency', get_option( 'creator_lms_currency' ) );
}

/**
 * Get full list of currency codes.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (https://cldr.unicode.org/translation/currency-names-and-symbols)
 *
 * @return array
 */
function get_crlms_currencies() {
	static $currencies;

	if ( ! isset( $currencies ) ) {
		$currencies = array_unique(
			apply_filters(
				'creator_lms_currencies',
				array(
					'AED' => __( 'United Arab Emirates dirham', 'creator-lms' ),
					'AFN' => __( 'Afghan afghani', 'creator-lms' ),
					'ALL' => __( 'Albanian lek', 'creator-lms' ),
					'AMD' => __( 'Armenian dram', 'creator-lms' ),
					'ANG' => __( 'Netherlands Antillean guilder', 'creator-lms' ),
					'AOA' => __( 'Angolan kwanza', 'creator-lms' ),
					'ARS' => __( 'Argentine peso', 'creator-lms' ),
					'AUD' => __( 'Australian dollar', 'creator-lms' ),
					'AWG' => __( 'Aruban florin', 'creator-lms' ),
					'AZN' => __( 'Azerbaijani manat', 'creator-lms' ),
					'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'creator-lms' ),
					'BBD' => __( 'Barbadian dollar', 'creator-lms' ),
					'BDT' => __( 'Bangladeshi taka', 'creator-lms' ),
					'BGN' => __( 'Bulgarian lev', 'creator-lms' ),
					'BHD' => __( 'Bahraini dinar', 'creator-lms' ),
					'BIF' => __( 'Burundian franc', 'creator-lms' ),
					'BMD' => __( 'Bermudian dollar', 'creator-lms' ),
					'BND' => __( 'Brunei dollar', 'creator-lms' ),
					'BOB' => __( 'Bolivian boliviano', 'creator-lms' ),
					'BRL' => __( 'Brazilian real', 'creator-lms' ),
					'BSD' => __( 'Bahamian dollar', 'creator-lms' ),
					'BTC' => __( 'Bitcoin', 'creator-lms' ),
					'BTN' => __( 'Bhutanese ngultrum', 'creator-lms' ),
					'BWP' => __( 'Botswana pula', 'creator-lms' ),
					'BYR' => __( 'Belarusian ruble (old)', 'creator-lms' ),
					'BYN' => __( 'Belarusian ruble', 'creator-lms' ),
					'BZD' => __( 'Belize dollar', 'creator-lms' ),
					'CAD' => __( 'Canadian dollar', 'creator-lms' ),
					'CDF' => __( 'Congolese franc', 'creator-lms' ),
					'CHF' => __( 'Swiss franc', 'creator-lms' ),
					'CLP' => __( 'Chilean peso', 'creator-lms' ),
					'CNY' => __( 'Chinese yuan', 'creator-lms' ),
					'COP' => __( 'Colombian peso', 'creator-lms' ),
					'CRC' => __( 'Costa Rican col&oacute;n', 'creator-lms' ),
					'CUC' => __( 'Cuban convertible peso', 'creator-lms' ),
					'CUP' => __( 'Cuban peso', 'creator-lms' ),
					'CVE' => __( 'Cape Verdean escudo', 'creator-lms' ),
					'CZK' => __( 'Czech koruna', 'creator-lms' ),
					'DJF' => __( 'Djiboutian franc', 'creator-lms' ),
					'DKK' => __( 'Danish krone', 'creator-lms' ),
					'DOP' => __( 'Dominican peso', 'creator-lms' ),
					'DZD' => __( 'Algerian dinar', 'creator-lms' ),
					'EGP' => __( 'Egyptian pound', 'creator-lms' ),
					'ERN' => __( 'Eritrean nakfa', 'creator-lms' ),
					'ETB' => __( 'Ethiopian birr', 'creator-lms' ),
					'EUR' => __( 'Euro', 'creator-lms' ),
					'FJD' => __( 'Fijian dollar', 'creator-lms' ),
					'FKP' => __( 'Falkland Islands pound', 'creator-lms' ),
					'GBP' => __( 'Pound sterling', 'creator-lms' ),
					'GEL' => __( 'Georgian lari', 'creator-lms' ),
					'GGP' => __( 'Guernsey pound', 'creator-lms' ),
					'GHS' => __( 'Ghana cedi', 'creator-lms' ),
					'GIP' => __( 'Gibraltar pound', 'creator-lms' ),
					'GMD' => __( 'Gambian dalasi', 'creator-lms' ),
					'GNF' => __( 'Guinean franc', 'creator-lms' ),
					'GTQ' => __( 'Guatemalan quetzal', 'creator-lms' ),
					'GYD' => __( 'Guyanese dollar', 'creator-lms' ),
					'HKD' => __( 'Hong Kong dollar', 'creator-lms' ),
					'HNL' => __( 'Honduran lempira', 'creator-lms' ),
					'HRK' => __( 'Croatian kuna', 'creator-lms' ),
					'HTG' => __( 'Haitian gourde', 'creator-lms' ),
					'HUF' => __( 'Hungarian forint', 'creator-lms' ),
					'IDR' => __( 'Indonesian rupiah', 'creator-lms' ),
					'ILS' => __( 'Israeli new shekel', 'creator-lms' ),
					'IMP' => __( 'Manx pound', 'creator-lms' ),
					'INR' => __( 'Indian rupee', 'creator-lms' ),
					'IQD' => __( 'Iraqi dinar', 'creator-lms' ),
					'IRR' => __( 'Iranian rial', 'creator-lms' ),
					'IRT' => __( 'Iranian toman', 'creator-lms' ),
					'ISK' => __( 'Icelandic kr&oacute;na', 'creator-lms' ),
					'JEP' => __( 'Jersey pound', 'creator-lms' ),
					'JMD' => __( 'Jamaican dollar', 'creator-lms' ),
					'JOD' => __( 'Jordanian dinar', 'creator-lms' ),
					'JPY' => __( 'Japanese yen', 'creator-lms' ),
					'KES' => __( 'Kenyan shilling', 'creator-lms' ),
					'KGS' => __( 'Kyrgyzstani som', 'creator-lms' ),
					'KHR' => __( 'Cambodian riel', 'creator-lms' ),
					'KMF' => __( 'Comorian franc', 'creator-lms' ),
					'KPW' => __( 'North Korean won', 'creator-lms' ),
					'KRW' => __( 'South Korean won', 'creator-lms' ),
					'KWD' => __( 'Kuwaiti dinar', 'creator-lms' ),
					'KYD' => __( 'Cayman Islands dollar', 'creator-lms' ),
					'KZT' => __( 'Kazakhstani tenge', 'creator-lms' ),
					'LAK' => __( 'Lao kip', 'creator-lms' ),
					'LBP' => __( 'Lebanese pound', 'creator-lms' ),
					'LKR' => __( 'Sri Lankan rupee', 'creator-lms' ),
					'LRD' => __( 'Liberian dollar', 'creator-lms' ),
					'LSL' => __( 'Lesotho loti', 'creator-lms' ),
					'LYD' => __( 'Libyan dinar', 'creator-lms' ),
					'MAD' => __( 'Moroccan dirham', 'creator-lms' ),
					'MDL' => __( 'Moldovan leu', 'creator-lms' ),
					'MGA' => __( 'Malagasy ariary', 'creator-lms' ),
					'MKD' => __( 'Macedonian denar', 'creator-lms' ),
					'MMK' => __( 'Burmese kyat', 'creator-lms' ),
					'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'creator-lms' ),
					'MOP' => __( 'Macanese pataca', 'creator-lms' ),
					'MRU' => __( 'Mauritanian ouguiya', 'creator-lms' ),
					'MUR' => __( 'Mauritian rupee', 'creator-lms' ),
					'MVR' => __( 'Maldivian rufiyaa', 'creator-lms' ),
					'MWK' => __( 'Malawian kwacha', 'creator-lms' ),
					'MXN' => __( 'Mexican peso', 'creator-lms' ),
					'MYR' => __( 'Malaysian ringgit', 'creator-lms' ),
					'MZN' => __( 'Mozambican metical', 'creator-lms' ),
					'NAD' => __( 'Namibian dollar', 'creator-lms' ),
					'NGN' => __( 'Nigerian naira', 'creator-lms' ),
					'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'creator-lms' ),
					'NOK' => __( 'Norwegian krone', 'creator-lms' ),
					'NPR' => __( 'Nepalese rupee', 'creator-lms' ),
					'NZD' => __( 'New Zealand dollar', 'creator-lms' ),
					'OMR' => __( 'Omani rial', 'creator-lms' ),
					'PAB' => __( 'Panamanian balboa', 'creator-lms' ),
					'PEN' => __( 'Sol', 'creator-lms' ),
					'PGK' => __( 'Papua New Guinean kina', 'creator-lms' ),
					'PHP' => __( 'Philippine peso', 'creator-lms' ),
					'PKR' => __( 'Pakistani rupee', 'creator-lms' ),
					'PLN' => __( 'Polish z&#x142;oty', 'creator-lms' ),
					'PRB' => __( 'Transnistrian ruble', 'creator-lms' ),
					'PYG' => __( 'Paraguayan guaran&iacute;', 'creator-lms' ),
					'QAR' => __( 'Qatari riyal', 'creator-lms' ),
					'RON' => __( 'Romanian leu', 'creator-lms' ),
					'RSD' => __( 'Serbian dinar', 'creator-lms' ),
					'RUB' => __( 'Russian ruble', 'creator-lms' ),
					'RWF' => __( 'Rwandan franc', 'creator-lms' ),
					'SAR' => __( 'Saudi riyal', 'creator-lms' ),
					'SBD' => __( 'Solomon Islands dollar', 'creator-lms' ),
					'SCR' => __( 'Seychellois rupee', 'creator-lms' ),
					'SDG' => __( 'Sudanese pound', 'creator-lms' ),
					'SEK' => __( 'Swedish krona', 'creator-lms' ),
					'SGD' => __( 'Singapore dollar', 'creator-lms' ),
					'SHP' => __( 'Saint Helena pound', 'creator-lms' ),
					'SLL' => __( 'Sierra Leonean leone', 'creator-lms' ),
					'SOS' => __( 'Somali shilling', 'creator-lms' ),
					'SRD' => __( 'Surinamese dollar', 'creator-lms' ),
					'SSP' => __( 'South Sudanese pound', 'creator-lms' ),
					'STN' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'creator-lms' ),
					'SYP' => __( 'Syrian pound', 'creator-lms' ),
					'SZL' => __( 'Swazi lilangeni', 'creator-lms' ),
					'THB' => __( 'Thai baht', 'creator-lms' ),
					'TJS' => __( 'Tajikistani somoni', 'creator-lms' ),
					'TMT' => __( 'Turkmenistan manat', 'creator-lms' ),
					'TND' => __( 'Tunisian dinar', 'creator-lms' ),
					'TOP' => __( 'Tongan pa&#x2bb;anga', 'creator-lms' ),
					'TRY' => __( 'Turkish lira', 'creator-lms' ),
					'TTD' => __( 'Trinidad and Tobago dollar', 'creator-lms' ),
					'TWD' => __( 'New Taiwan dollar', 'creator-lms' ),
					'TZS' => __( 'Tanzanian shilling', 'creator-lms' ),
					'UAH' => __( 'Ukrainian hryvnia', 'creator-lms' ),
					'UGX' => __( 'Ugandan shilling', 'creator-lms' ),
					'USD' => __( 'United States (US) dollar', 'creator-lms' ),
					'UYU' => __( 'Uruguayan peso', 'creator-lms' ),
					'UZS' => __( 'Uzbekistani som', 'creator-lms' ),
					'VEF' => __( 'Venezuelan bol&iacute;var (2008–2018)', 'creator-lms' ),
					'VES' => __( 'Venezuelan bol&iacute;var', 'creator-lms' ),
					'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'creator-lms' ),
					'VUV' => __( 'Vanuatu vatu', 'creator-lms' ),
					'WST' => __( 'Samoan t&#x101;l&#x101;', 'creator-lms' ),
					'XAF' => __( 'Central African CFA franc', 'creator-lms' ),
					'XCD' => __( 'East Caribbean dollar', 'creator-lms' ),
					'XOF' => __( 'West African CFA franc', 'creator-lms' ),
					'XPF' => __( 'CFP franc', 'creator-lms' ),
					'YER' => __( 'Yemeni rial', 'creator-lms' ),
					'ZAR' => __( 'South African rand', 'creator-lms' ),
					'ZMW' => __( 'Zambian kwacha', 'creator-lms' ),
				)
			)
		);
	}

	return $currencies;
}

/**
 * Get all available Currency symbols.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (https://cldr.unicode.org/translation/currency-names-and-symbols)
 *
 * @since 4.1.0
 * @return array
 */
function get_crlms_currency_symbols() {

	$symbols = apply_filters(
		'creator_lms_currency_symbols',
		array(
			'AED' => '&#x62f;.&#x625;',
			'AFN' => '&#x60b;',
			'ALL' => 'L',
			'AMD' => 'AMD',
			'ANG' => '&fnof;',
			'AOA' => 'Kz',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => 'Afl.',
			'AZN' => '&#8380;',
			'BAM' => 'KM',
			'BBD' => '&#36;',
			'BDT' => '&#2547;&nbsp;',
			'BGN' => '&#1083;&#1074;.',
			'BHD' => '.&#x62f;.&#x628;',
			'BIF' => 'Fr',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => 'Bs.',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTC' => '&#3647;',
			'BTN' => 'Nu.',
			'BWP' => 'P',
			'BYR' => 'Br',
			'BYN' => 'Br',
			'BZD' => '&#36;',
			'CAD' => '&#36;',
			'CDF' => 'Fr',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&yen;',
			'COP' => '&#36;',
			'CRC' => '&#x20a1;',
			'CUC' => '&#36;',
			'CUP' => '&#36;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => 'Fr',
			'DKK' => 'kr.',
			'DOP' => 'RD&#36;',
			'DZD' => '&#x62f;.&#x62c;',
			'EGP' => 'EGP',
			'ERN' => 'Nfk',
			'ETB' => 'Br',
			'EUR' => '&euro;',
			'FJD' => '&#36;',
			'FKP' => '&pound;',
			'GBP' => '&pound;',
			'GEL' => '&#x20be;',
			'GGP' => '&pound;',
			'GHS' => '&#x20b5;',
			'GIP' => '&pound;',
			'GMD' => 'D',
			'GNF' => 'Fr',
			'GTQ' => 'Q',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => 'L',
			'HRK' => 'kn',
			'HTG' => 'G',
			'HUF' => '&#70;&#116;',
			'IDR' => 'Rp',
			'ILS' => '&#8362;',
			'IMP' => '&pound;',
			'INR' => '&#8377;',
			'IQD' => '&#x62f;.&#x639;',
			'IRR' => '&#xfdfc;',
			'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
			'ISK' => 'kr.',
			'JEP' => '&pound;',
			'JMD' => '&#36;',
			'JOD' => '&#x62f;.&#x627;',
			'JPY' => '&yen;',
			'KES' => 'KSh',
			'KGS' => '&#x441;&#x43e;&#x43c;',
			'KHR' => '&#x17db;',
			'KMF' => 'Fr',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'KWD' => '&#x62f;.&#x643;',
			'KYD' => '&#36;',
			'KZT' => '&#8376;',
			'LAK' => '&#8365;',
			'LBP' => '&#x644;.&#x644;',
			'LKR' => '&#xdbb;&#xdd4;',
			'LRD' => '&#36;',
			'LSL' => 'L',
			'LYD' => '&#x62f;.&#x644;',
			'MAD' => '&#x62f;.&#x645;.',
			'MDL' => 'MDL',
			'MGA' => 'Ar',
			'MKD' => '&#x434;&#x435;&#x43d;',
			'MMK' => 'Ks',
			'MNT' => '&#x20ae;',
			'MOP' => 'P',
			'MRU' => 'UM',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'MWK' => 'MK',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => 'MT',
			'NAD' => 'N&#36;',
			'NGN' => '&#8358;',
			'NIO' => 'C&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#x631;.&#x639;.',
			'PAB' => 'B/.',
			'PEN' => 'S/',
			'PGK' => 'K',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PRB' => '&#x440;.',
			'PYG' => '&#8370;',
			'QAR' => '&#x631;.&#x642;',
			'RMB' => '&yen;',
			'RON' => 'lei',
			'RSD' => '&#1088;&#1089;&#1076;',
			'RUB' => '&#8381;',
			'RWF' => 'Fr',
			'SAR' => '&#x631;.&#x633;',
			'SBD' => '&#36;',
			'SCR' => '&#x20a8;',
			'SDG' => '&#x62c;.&#x633;.',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&pound;',
			'SLL' => 'Le',
			'SOS' => 'Sh',
			'SRD' => '&#36;',
			'SSP' => '&pound;',
			'STN' => 'Db',
			'SYP' => '&#x644;.&#x633;',
			'SZL' => 'E',
			'THB' => '&#3647;',
			'TJS' => '&#x405;&#x41c;',
			'TMT' => 'm',
			'TND' => '&#x62f;.&#x62a;',
			'TOP' => 'T&#36;',
			'TRY' => '&#8378;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => 'Sh',
			'UAH' => '&#8372;',
			'UGX' => 'UGX',
			'USD' => '&#36;',
			'UYU' => '&#36;',
			'UZS' => 'UZS',
			'VEF' => 'Bs F',
			'VES' => 'Bs.',
			'VND' => '&#8363;',
			'VUV' => 'Vt',
			'WST' => 'T',
			'XAF' => 'CFA',
			'XCD' => '&#36;',
			'XOF' => 'CFA',
			'XPF' => 'Fr',
			'YER' => '&#xfdfc;',
			'ZAR' => '&#82;',
			'ZMW' => 'ZK',
		)
	);

	return $symbols;
}


/**
 * Get Currency symbol.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (https://cldr.unicode.org/translation/currency-names-and-symbols)
 *
 * @param string $currency Currency. (default: '').
 * @return string
 */
function get_crlms_currency_symbol( $currency = '' ) {
	if ( ! $currency ) {
		$currency = get_crlms_currency();
	}

	$symbols = get_crlms_currency_symbols();

	$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

	return apply_filters( 'creator_lms_currency_symbol', $currency_symbol, $currency );
}

function crlms_decode_unicode_sequences($str) {
	return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($matches) {
		return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
	}, $str);
}
