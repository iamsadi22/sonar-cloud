<?php
namespace CreatorLms\Admin;


class Ajax {

	public function __construct() {
		add_action( 'wp_ajax_install_sample_data', array( $this, 'install_sample_data' ) );
		add_action( 'wp_ajax_delete_sample_data', array( $this, 'delete_sample_data' ) );
	}


	public function install_sample_data() {
		check_ajax_referer('admin_tools', 'nonce');

		// create sample course here
	}

	public function delete_sample_data() {
		check_ajax_referer('admin_tools', 'nonce');

		// create sample course here
	}
}
