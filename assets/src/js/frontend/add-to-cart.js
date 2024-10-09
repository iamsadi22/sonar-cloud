(function ($) {

	$(function () {

		var AddToCartFrontend = {
			init: function () {
				$(document.body)
					.on('click', '.add_to_cart_button', this.onAddToCart)
					.on('click', '.remove_from_cart_button', this.onRemoveFromCart )
					.on('course_added_to_cart', this.updateAddToCartButton)
			},

			/**
			 * On course added on cart
			 *
			 * @param e
			 */
			onAddToCart: function (e) {
				e.preventDefault();

				let thisButton = $(this),
					data = thisButton.data();

				data['action']	= 'creator_lms_add_to_cart';
				data['nonce']	= crlms_add_to_cart_params.nonce;

				thisButton.removeClass( 'added' );
				thisButton.addClass( 'loading' );

				// Trigger event.
				$( document.body ).trigger( 'course_adding_to_cart', [ thisButton, data ] );

				$.ajax( {
					url: crlms_add_to_cart_params.ajax_url,
					type: 'POST',
					data: data,
					dataType: 'json',
					success( response ) {
						if ( ! response ) {
							return;
						}

						// Trigger event after course is successfully added to cart
						$( document.body ).trigger( 'course_added_to_cart', [ response, thisButton ] );
					}
				} );

			},

			onRemoveFromCart: function (e) {
				e.preventDefault();
			},

			updateAddToCartButton: function ( e, response, button ) {
				button.removeClass('loading');
				button.addClass('added');

				if (response.status === 'success') {
					window.location = response.redirect_url;
				}
			}

		}


		AddToCartFrontend.init();


	})

})(jQuery);
