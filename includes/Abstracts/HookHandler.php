<?php

namespace CreatorLms\Abstracts;

/**
 * Abstract class HookHandler
 *
 * This class provides a template for registering hooks in the CreatorLMS plugin.
 */
abstract class HookHandler {

	/**
	 * Register hooks.
	 *
	 * This method should be implemented by subclasses to register their specific hooks.
	 */
	abstract public function register_hooks();
}
