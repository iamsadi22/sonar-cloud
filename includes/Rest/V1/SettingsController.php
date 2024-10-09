<?php
namespace CreatorLms\Rest\V1;

use CreatorLms\Abstracts\RestController;
use CreatorLms\Admin\Settings\AdminSettings;

/**
 * SettingsController class.
 *
 * Handles REST API endpoints for settings.
 *
 * @since 1.0.0
 */
class SettingsController extends RestController {

	/**
	 * The base route for settings endpoints.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $base = 'settings/(?P<group_id>[\w-]+)';


	/**
	 * Register the routes for the settings endpoints.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->base , array(
				'args'   => array(
					'group_id' => array(
						'description' => __( 'Settings group ID.', 'creator-lms' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);


		register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\w-]+)',
			array(
				'args'   => array(
					'group_id' => array(
						'description' => __( 'Settings group ID.', 'creator-lms' ),
						'type'        => 'string',
					),
					'id'    => array(
						'description' => __( 'Unique identifier for the resource.', 'creator-lms' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_items_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}


	/**
	 * Get items (settings) for a specific group.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response|\WP_Error The response or error object.
	 *
	 * @since 1.0.0
	 */
	public function get_items( $request ) {
		$settings = $this->get_group_settings( $request['group_id'] );

		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		$data = array();

		foreach ( $settings as $setting_obj ) {
			$setting = $this->prepare_item_for_response( $setting_obj, $request );
			$setting = $this->prepare_response_for_collection( $setting );
			$data[] = $setting;
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Get a single item (setting) for a specific group.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response|\WP_Error The response or error object.
	 *
	 * @since 1.0.0
	 */
	public function get_item( $request ) {
		$setting = $this->get_setting( $request['group_id'], $request['id'] );

		if ( is_wp_error( $setting ) ) {
			return $setting;
		}

		$response = $this->prepare_item_for_response( $setting, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Update a single item (setting) for a specific group.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response|\WP_Error The response or error object.
	 *
	 * @since 1.0.0
	 */
	public function update_item( $request ) {
		$setting = $this->get_setting( $request['group_id'], $request['id'] );

		if ( is_wp_error( $setting ) ) {
			return $setting;
		}

		$value = is_null( $request['value'] ) ? '' : $request['value'];
		$value = wp_kses_post( trim( stripslashes( $value ) ) );

		update_option( $request['id'] , $value );

		$response = $this->prepare_item_for_response( $setting, $request );
		return rest_ensure_response( $response );
	}


	/**
	 * Get a single setting for a specific group.
	 *
	 * @param string $group_id The group ID.
	 * @param string $setting_id The setting ID.
	 * @return array|\WP_Error The setting array or error object.
	 *
	 * @since 1.0.0
	 */
	public function get_setting( $group_id, $setting_id ) {
		if ( empty( $setting_id ) ) {
			return new \WP_Error( 'rest_setting_setting_invalid', __( 'Invalid setting.', 'creator-lms' ), array( 'status' => 404 ) );
		}

		$settings = $this->get_group_settings( $group_id );

		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		$setting = null;
		foreach ( $settings as $s ) {
			if ( $s['id'] === $setting_id ) {
				$setting = $s;
				break;
			}
		}

		if ( is_null( $setting ) ) {
			return new \WP_Error( 'rest_setting_setting_invalid', __( 'Invalid setting.', 'creator-lms' ), array( 'status' => 404 ) );
		}

		return $setting;
	}


	/**
	 * Get settings for a specific group.
	 *
	 * @param string $group_id The group ID.
	 * @return array|\WP_Error The settings array or error object.
	 *
	 * @since 1.0.0
	 */
	public function get_group_settings( $group_id ) {
		if ( empty( $group_id ) ) {
			return new \WP_Error( 'rest_setting_setting_group_invalid', __( 'Invalid setting group.', 'woocommerce' ), array( 'status' => 404 ) );
		}
		$settings 			= apply_filters( 'creator_lms_settings-' . $group_id, array() );
		$filtered_settings 	= array();
		foreach ( $settings as $setting ) {
			$option_key 			= $setting['id'];
			$setting['value'] 		= AdminSettings::get_option( $option_key, $setting['default'] );
			$filtered_settings[] 	= $setting;
		}

		return $filtered_settings;
	}


	/**
	 * Prepare a single item for response.
	 *
	 * @param array $item The item array.
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response The response object.
	 *
	 * @since 1.0.0
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data     = $this->add_additional_fields_to_object( $item, $request );
		$response = rest_ensure_response( $data );
		return $response;
	}


	/**
	 * Check permissions for getting items.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return bool True if the current user has permission, false otherwise.
	 *
	 * @since 1.0.0
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Check permissions for updating items.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return bool True if the current user has permission, false otherwise.
	 *
	 * @since 1.0.0
	 */
	public function update_items_permissions_check( $request ) {
		return true;
	}
}
