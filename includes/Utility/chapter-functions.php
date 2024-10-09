<?php

/**
 * Get chapter object
 *
 * @param $chapter_id
 * @return bool|\CreatorLms\Data\Chapter
 * @throws Exception
 * @since 1.0.0
 */
function crlms_get_chapter( $chapter_id ) {
	return CRLMS()->chapter_factory->get_chapter( $chapter_id );
}

