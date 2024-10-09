<?php

/**
 * Get page ID by page name.
 *
 * @param string $page Page name.
 * @return int
 * @since 1.0.0
 */
function crlms_get_page_id( $page ) {
	$page = apply_filters( 'creator_lms_get_' . $page . '_page_id', get_option( 'creator_lms_' . $page . '_page_id' ) );
	return $page ? absint( $page ) : -1;
}

/**
 * Get page url by name
 * @param $page
 * @return false|string|null
 * @since 1.0.0
 */
function crlms_get_page_url( $page ): false|string|null
{
	$id = crlms_get_page_id($page);

	if($id) {
		return get_permalink( $id );
	}

	return site_url();
}
