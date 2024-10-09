<?php

/**
 * Creator LMS admin functions
 */

/**
 * Check if current page is Creator LMS admin page
 *
 * @return bool
 */
function is_crlm_admin_page(): bool {
	return is_admin() && isset($_GET['page']) && in_array($_GET['page'], ['crlms-settings', 'crlms-tools'], true);
}
