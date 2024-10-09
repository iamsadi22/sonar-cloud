<?php

/**
 * Clears the cart session when called.
 */
function rex_empty_cart() {
	if ( ! isset( \CodeRex\Ecommerce\ecommerce()->cart ) || '' === \CodeRex\Ecommerce\ecommerce()->cart ) {
		\CodeRex\Ecommerce\ecommerce()->cart = new \CodeRex\Ecommerce\Cart();
	}
	\CodeRex\Ecommerce\ecommerce()->cart->empty_cart( false );
}
