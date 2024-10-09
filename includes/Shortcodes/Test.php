<?php

namespace CreatorLms\Shortcodes;

defined( 'ABSPATH' ) || exit;

class Test {

	public static function output( $atts ) {
		$atts = shortcode_atts( array(), $atts, 'test' );
		echo 'HELLO';
	}

}
