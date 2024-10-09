<?php

namespace CreatorLms\Abstracts;

defined( 'ABSPATH' ) || exit();

abstract class Assets {

	public $scripts = [];

	public $styles = [];

	public $localized_scripts = [];

	public abstract function init();

	public abstract function load_scripts();

	public function get_styles(){
		$styles = array();
		return is_array( $styles ) ? array_filter( $styles ) : array();
	}

	public function get_scripts() {
		$scripts = array();
		return is_array( $scripts ) ? array_filter( $scripts ) : array();
	}

	public function get_script_data( $handle ) {}

	protected function get_asset_url( $path ) {
		return plugins_url( $path, CREATOR_LMS_FILE );
	}

	public function register_script( $handle, $path, $deps = array( 'jquery' ), $version = CREATOR_LMS_VERSION, $in_footer = array( 'strategy' => 'defer' ) ) {
		$this->scripts[] = $handle;
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	public function register_style( $handle, $path, $deps = array(), $version = CREATOR_LMS_VERSION, $media = 'all', $has_rtl = false ) {
		$this->styles[] = $handle;
		wp_register_style( $handle, $path, $deps, $version, $media );

		if ( $has_rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	public function register_scripts() {
		$register_scripts = $this->get_scripts();
		foreach ( $register_scripts as $name => $props ) {
			$this->register_script( $name, $props['src'], $props['deps'], $props['version'] );
		}
	}

	public function register_styles() {
		$register_styles = $this->get_styles();
		foreach ( $register_styles as $name => $props ) {
			$this->register_style( $name, $props['src'], $props['deps'], $props['version'], 'all', $props['has_rtl'] );
		}
	}

	public function localize_script( $handle ) {
		if ( ! in_array( $handle, $this->localized_scripts, true ) && wp_script_is( $handle ) ) {
			$data = $this->get_script_data( $handle );

			if ( ! $data ) {
				return;
			}

			$name 	= str_replace( '-', '_', $handle ) . '_params';
			wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
		}
	}

	/**
	 * Localize scripts only when enqueued.
	 */
	public function localize_printed_scripts() {
		foreach ( $this->scripts as $handle ) {
			$this->localize_script( $handle );
		}
	}


	public function should_enqueue( $script, $screen_id ): bool {
		$should_load = false;
		if ( ! empty( $script['screens'] ) ) {
			$should_load = in_array( $screen_id, $script['screens'] );
		} else {
			$should_load = true;
		}
		return $should_load;
	}

}
