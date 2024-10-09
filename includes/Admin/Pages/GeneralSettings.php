<?php

namespace CreatorLms\Admin\Pages;

use CreatorLms\Abstracts\SettingsPage;

defined( 'ABSPATH' ) || exit;


class GeneralSettings extends SettingsPage {


	public function __construct() {
		$this->id 		= 'general';
		$this->label 	= __('General', 'creator-lms');

		parent::__construct();
	}


	public function get_settings_for_default_section() {

		$currency_code_options = get_crlms_currencies();

		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_crlms_currency_symbol( $code ) . ')';
		}

		$settings = array(
			//page settings
			array(
				'title' => __( 'Page setup', 'creator-lms' ),
				'type'  => 'title',
				'id'    => 'general_page_settings',
			),
			array(
				'title'    => __( 'All course page', 'creator-lms' ),
				'id'       => 'creator_lms_course_page_id',
				'type'     => 'single_select_page_with_search',
				'default'  => '',
				'class'    => 'crlms-page-search',
				'css'      => 'min-width:300px;',
				'args'     => array(
					'exclude' =>
						array(
							crlms_get_page_id( 'checkout' ),
							crlms_get_page_id( 'myaccount' ),
						),
				),
				'desc_tip' => true,
				'autoload' => false,
			),
			array(
				'title'    => __( 'Profile page', 'creator-lms' ),
				'id'       => 'creator_lms_profile_page_id',
				'type'     => 'single_select_page_with_search',
				'default'  => '',
				'class'    => 'crlms-page-search',
				'css'      => 'min-width:300px;',
				'args'     => array(
					'exclude' =>
						array(
							crlms_get_page_id( 'checkout' ),
						),
				),
				'desc_tip' => true,
				'autoload' => false,
			),
			array(
				'title'    => __( 'Checkout page', 'creator-lms' ),
				'id'       => 'creator_lms_checkout_page_id',
				'type'     => 'single_select_page_with_search',
				'default'  => '',
				'class'    => 'crlms-page-search',
				'css'      => 'min-width:300px;',
				'args'     => array(
					'exclude' =>
						array(
							crlms_get_page_id( 'course' ),
							crlms_get_page_id( 'myaccount' ),
						),
				),
				'desc_tip' => true,
				'autoload' => false,
			),
			array(
				'title'    => __( 'Thank you/Order confirm page', 'creator-lms' ),
				'id'       => 'creator_lms_thank_you_page_id',
				'type'     => 'single_select_page_with_search',
				'default'  => '',
				'class'    => 'crlms-page-search',
				'css'      => 'min-width:300px;',
				'args'     => array(
					'exclude' =>
						array(
							crlms_get_page_id( 'course' ),
							crlms_get_page_id( 'myaccount' ),
							crlms_get_page_id( 'checkout' ),
						),
				),
				'desc_tip' => true,
				'autoload' => false,
			),
			array(
				'title'    => __( 'Terms and condition page', 'creator-lms' ),
				'id'       => 'creator_lms_terms_page_id',
				'type'     => 'single_select_page_with_search',
				'default'  => '',
				'class'    => 'crlms-page-search',
				'css'      => 'min-width:300px;',
				'args'     => array(
					'exclude' =>
						array(
							crlms_get_page_id( 'checkout' ),
						),
				),
				'desc_tip' => true,
				'autoload' => false,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'general_page_settings',
			),

			// currency settings
			array(
				'title' => __( 'Currency options', 'creator-lms' ),
				'type'  => 'title',
				'desc'  => __( 'The following options affect how prices are displayed on the frontend.', 'creator-lms' ),
				'id'    => 'pricing_options',
			),

			array(
				'title'    => __( 'Currency', 'creator-lms' ),
				'desc'     => __( 'This controls what currency prices are listed at in the catalog and which currency gateways will take payments in.', 'creator-lms' ),
				'id'       => 'creator_lms_currency',
				'default'  => 'USD',
				'type'     => 'select',
				'class'    => 'crlms-select2',
				'desc_tip' => true,
				'options'  => $currency_code_options,
			),

			array(
				'title'    => __( 'Currency position', 'creator-lms' ),
				'desc'     => __( 'This controls the position of the currency symbol.', 'creator-lms' ),
				'id'       => 'creator_lms_currency_pos',
				'class'    => 'crlms-select2',
				'default'  => 'left',
				'type'     => 'select',
				'options'  => array(
					'left'        => __( 'Left', 'creator-lms' ),
					'right'       => __( 'Right', 'creator-lms' ),
					'left_space'  => __( 'Left with space', 'creator-lms' ),
					'right_space' => __( 'Right with space', 'creator-lms' ),
				),
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Thousand separator', 'creator-lms' ),
				'desc'     => __( 'This sets the thousand separator of displayed prices.', 'creator-lms' ),
				'id'       => 'creator_lms_price_thousand_sep',
				'css'      => 'width:50px;',
				'default'  => ',',
				'type'     => 'text',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Decimal separator', 'creator-lms' ),
				'desc'     => __( 'This sets the decimal separator of displayed prices.', 'creator-lms' ),
				'id'       => 'creator_lms_price_decimal_sep',
				'css'      => 'width:50px;',
				'default'  => '.',
				'type'     => 'text',
				'desc_tip' => true,
			),

			array(
				'title'             => __( 'Number of decimals', 'creator-lms' ),
				'desc'              => __( 'This sets the number of decimal points shown in displayed prices.', 'creator-lms' ),
				'id'                => 'creator_lms_price_num_decimals',
				'css'               => 'width:50px;',
				'default'           => '2',
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => 0,
					'step' => 1,
				),
			),

			array(
				'type' => 'sectionend',
				'id'   => 'pricing_options',
			),
		);
		return apply_filters( 'creator_lms_settings_pages', $settings );
	}

}
