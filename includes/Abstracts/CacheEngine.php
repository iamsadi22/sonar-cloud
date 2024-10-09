<?php

namespace CreatorLms\Abstracts;

defined( 'ABSPATH' ) || exit();

abstract class CacheEngine {

	public $cache_group = 'creator_lms';

	public $key;

	public function set() {}

	public function get() {}

	public function clear() {}
}
