<?php


use Abstracts\PaymentGateway;

class OfflineGateway extends PaymentGateway {

    public function __construct() {
        $this->id = 'offline_payment';
        $this->title = __( 'Offline payment', 'creator-lms' );
        $this->has_fields = false;
        $this->order_button_text = __( 'Place payment', 'creator-lms' );

        $this->init_form_fields();
        $this->init_settings();


        // Actions
        add_action( 'creator_lms_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
//		add_action( 'creator_lms_update_options_payment_gateways_' . $this->id, array( $this, 'save_account_details' ) );
    }


    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable/Disable', 'creator-lms' ),
                'type' => 'checkbox',
                'label' => __( 'Enable offline payments', 'creator-lms' ),
                'default' => 'no',
            ),
            'test_mode' => array(
                'title' => __( 'Test mode', 'creator-lms' ),
                'type' => 'checkbox',
                'label' => __( 'Auto complete the order', 'creator-lms' ),
                'default' => 'no',
            ),
            'title' => array(
                'title' => __( 'Title', 'creator-lms' ),
                'type' => 'text',
                'description' => __( 'Payment method description that the customer will see on your checkout.', 'creator-lms' ),
                'default' => __( 'Offline payment', 'creator-lms' ),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __( 'Description', 'creator-lms' ),
                'type' => 'textarea',
                'description' => __( 'Payment method description that the customer will see on your website.', 'creator-lms' ),
                'default' => __( 'Pay with offline payment.', 'creator-lms' ),
                'desc_tip' => true,
            ),
            'instructions' => array(
                'title' => __( 'Instructions', 'creator-lms' ),
                'type' => 'textarea',
                'description' => __( 'Instructions that will be added to the thank you page.', 'creator-lms' ),
                'default' => __( 'Pay with offline payment.', 'creator-lms' ),
                'desc_tip' => true,
            ),
        );
    }

    /**
     * Process payment
     *
     * @param int $order_id
     * @return array
     * @since 1.0.0
     */
    public function process_payment( $order_id ) {}
}
