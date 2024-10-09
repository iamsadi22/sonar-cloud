<?php

use Abstracts\PaymentGateway;

class PaypalGateway extends PaymentGateway {


	public function __construct() {
		$this->id = 'paypal';
		$this->title = __('Paypal payment', 'creator-lms');
		$this->has_fields = false;
		$this->order_button_text = __('Pay', 'creator-lms');

		$this->init_form_fields();
		$this->init_settings();


		// Actions
		add_action( 'creator_lms_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}


	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'         => array(
				'title'   => __( 'Enable/Disable', 'creator-lms' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable paypal payments', 'creator-lms' ),
				'default' => 'no',
			),
			'test_mode'         => array(
				'title'   => __( 'Test mode', 'creator-lms' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable test mode', 'creator-lms' ),
				'default' => 'no',
			),
			'client_id'              => array(
				'title'       => __( 'Client ID', 'creator-lms' ),
				'type'        => 'text',
				'description' => __( 'Add paypal client id', 'creator-lms' ),
				'default'     => __( '', 'creator-lms' ),
				'desc_tip'    => true,
			),
			'client_secret'              => array(
				'title'       => __( 'Client Secret', 'creator-lms' ),
				'type'        => 'password',
				'description' => __( 'Add paypal client secret', 'creator-lms' ),
				'default'     => __( '', 'creator-lms' ),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Get settings
	 * @return bool|array
	 * @since 1.0.0
	 */
	public function get_saved_settings(): bool|array
	{
		$settings = $this->settings;

		if(!isset($settings['enabled']) && $settings['enabled'] == 'yes') {
			return false;
		}

		if(empty($settings['client_id']) || empty($settings['client_secret'])) {
			return false;
		}

		return $settings;
	}

}
