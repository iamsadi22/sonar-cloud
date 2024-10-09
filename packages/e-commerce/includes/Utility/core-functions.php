<?php

/**
 * Cleanup sessions data via cron jobs
 */
function crlms_cleanup_sessions(  ) {
	$session = new CodeRex\Ecommerce\SessionHandler();
	if ( is_callable( array( $session, 'cleanup_sessions' ) ) ) {
		$session->cleanup_sessions();
	}
}
add_action( 'creator_lms_cleanup_sessions', 'crlms_cleanup_sessions' );
