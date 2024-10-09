<?php

namespace CodeRex\Ecommerce;

class Scheduler {

	/**
	 * The single instance of the class.
	 *
	 * @var Scheduler|null
	 */
	protected static $instance = null;

	/**
	 * Gets the main Checkout instance
	 *
	 * @return Scheduler|null
	 * @since 1.0.0
	 */
	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Scheduler constructor.
	 */
	public function __construct() {
		// Add action to schedule subscription events
		// For example, when a subscription status is updated, or subscription date is updated or deleted schedule a single
		// no recurring event to be triggered at a specific time.
		// e.g when a subscription status is updated from pending to active, schedule an event to be triggered at a specific time.
		// add_action('creator_lms_subscription_status_updated', array( $this, 'update_status' ), 10, 3);
	}

	/**
	 * @param $subscription
	 * @param $status
	 * @param $old_status
	 * @return void
	 */
	public function update_status( $subscription, $status, $old_status ) {
		// Schedule an event to be triggered at a specific time
		// This event can be used to send an email notification to the user
		// or to update the subscription status in the database
		// or to perform any other action
		//
		// for example, for renewal payment action, you can schedule an event to be triggered at a specific time
		// lets say, a new subscription is created with monthly renewal payment, so after the first payment is made
		// schedule an event to be triggered after 30 days to renew the subscription
		// get the next payment date of the subscription and schedule an event to be triggered at the end date
	}

}
