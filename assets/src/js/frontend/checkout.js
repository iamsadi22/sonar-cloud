(function ($) {

	$(function () {
		var Checkout = {
			init: function() {
				$( document.body )
					.on( 'click', 'a.showlogin', this.show_login_form )
					.on( 'submit', '#creator-lms-checkout-form', this.submit );
			},
			show_login_form: function() {
				$( '.crlms-checkout-login' ).slideToggle();
				return false;
			},
			submit: function(e) {
				e.preventDefault();
				var form = $( this ),
					submitButton = form.find('button[type="submit"]');

				if ( form.is( '.processing' ) ) {
					return false;
				}

				form.addClass( 'processing' );
				// submitButton.prop('disabled', true);

				$.ajax({
					type: 'POST',
					url: crlms_checkout_params.ajax_url,
					data: form.serialize(),
					dataType: 'json',
					success: function (response) {
						form.removeClass('processing');

						if(response.status === "pending") {
							if(response.paypal !== undefined) {

								if(response.paypal.subscriptionID !== undefined) {
									localStorage.setItem('crlms_payment_method', 'paypal_subscription');
									localStorage.setItem('crlms_paypal_subscription_id', response.paypal.subscriptionID);
									localStorage.setItem('crlms_order_id', response.order);
									window.location.href = response.paypal.approval_url;
								}
								else {
									localStorage.setItem('crlms_payment_method', 'paypal');
									localStorage.setItem('crlms_paypal_order_id', response.paypal.orderID);
									localStorage.setItem('crlms_order_id', response.order);
									window.location.href = response.paypal.approval_url;
								}
							}
						}
					}
				});
			},

		}

		Checkout.init();
	})

	/**
	 * Paypal payment - capture order
	 * @param paypal_order_id
	 * @since 1.0.0
	 */
	function request_paypal_capture(paypal_order_id) {
		let original_order = localStorage.getItem('crlms_order_id');
		$.ajax({
			type: 'POST',
			url: crlms_checkout_params.ajax_url,
			data: {
				action: 'crlms_after_paypal_payment_redirection',
				paypal_order_id: paypal_order_id,
				original_order: original_order,
			},
			success: function (response) {
				localStorage.removeItem('crlms_paypal_order_id');
				localStorage.removeItem('crlms_order_id');
				localStorage.removeItem('crlms_payment_method');
			},
			error: function (xhr, status, error) {
				console.log(xhr.responseText);
			}
		});
	}

	/**
	 * Paypal subscription capture
	 * @param subscription_id
	 * @since 1.0.0
	 */
	function request_paypal_subscription_capture(subscription_id) {

		let original_order = localStorage.getItem('crlms_order_id');

		$.ajax({
			type: 'POST',
			url: crlms_checkout_params.ajax_url,
			data: {
				action: 'crlms_after_paypal_subscription_redirection',
				subscription_id: subscription_id,
				original_order: original_order,
			},
			success: function (response) {
				// localStorage.removeItem('crlms_paypal_subscription_id');
				// localStorage.removeItem('crlms_order_id');
				// localStorage.removeItem('crlms_payment_method');
			},
			error: function (xhr, status, error) {
				console.log(xhr.responseText);
			}
		});
	}

	//=== capture mode for any payment redirection ===//
	jQuery(document).ready(function($) {
		let urlParams = new URLSearchParams(window.location.search);
		let PayerID = urlParams.get('PayerID');
		let token = urlParams.get('token');
		let subscription_id = urlParams.get('subscription_id');
		let ba_token = urlParams.get('ba_token');

		let paypal_order_id = localStorage.getItem('crlms_paypal_order_id');
		if(paypal_order_id && PayerID && token) {
			request_paypal_capture(paypal_order_id);
		}

		if(subscription_id && ba_token && token) {
			request_paypal_subscription_capture(subscription_id);
		}

		$('.crlm_purchase').on('click', function(e) {
			e.preventDefault();
			let plan  = $(this).data('plan');
			let membership_id = $(this).data('membership');

			$.ajax({
				type: 'POST',
				url: crlms_checkout_params.ajax_url,
				data: {
					action: 'creator_lms_purchase_membership',
					nonce: crlms_checkout_params.nonce,
					membership_id: membership_id,
					plan: plan,
				},
				success: function (response) {
					if (response.status === 'success') {
						window.location = response.redirect_url;
					}
				},
				error: function (xhr, status, error) {
					console.log(xhr.responseText);
				}
			});
		});

	});

})(jQuery);



