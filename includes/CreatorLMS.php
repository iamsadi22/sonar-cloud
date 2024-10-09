<?php

use CreatorLms\Hooks\CourseHookHandler;
use CreatorLms\Hooks\ChapterHookHandler;
use CreatorLms\Hooks\LessonHookHandler;

final class CreatorLms {

	/**
	 * @var $rest_api CreatorLms\Rest\Api
	 */
	public $rest_api;


	/**
	 * @var $admin CreatorLms\Admin\Admin
	 */
	public \CreatorLms\Admin\Admin $admin;


	/**
	 * @var $admin_menu CreatorLms\Admin\Menu
	 */
	public $admin_menu;


	/**
	 * @var $order_loader \CreatorLms\Order\OrderLoader
	 */
	public $order_loader;


	/**
	 * @var $session \CreatorLms\Order\SessionHandler
	 */
	public $session;


	/**
	 * @var \CreatorLms\Factory\CourseFactory
	 */
	public $course_factory;

	/**
	 * @var \CreatorLms\Factory\ChapterFactory
	 */
	public $chapter_factory;



	/**
	 * @var \CreatorLms\Factory\LessonFactory
	 */
	public $lesson_factory;


	/**
	 * @var \CreatorLms\Shortcodes\Shortcodes
	 */
	public $shortcode;


	/**
	 * @var \CreatorLms\Admin\Ajax
	 */
	public $admin_ajax;


	/**
	 * @var \CreatorLms\Admin\Pages\AdminSettings
	 */
	public $settings_pages;


	/**
	 * Holds the singleton instance of this class.
	 *
	 * @var CreatorLms
	 */
	private static $instance;

	/**
	 * @var \CreatorLms\Hooks\CourseHookHandler
	 */
	private $course_hook_handler;
	
	
	/**
	 * @var \CreatorLms\Hooks\ChapterHookHandler
	 */
	private $chapter_hook_handler;
	
	/**
	 * @var \CreatorLms\Hooks\LessonHookHandler
	 */
	private $lesson_hook_handler;


	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	const SLUG = 'creator-lms';


	/**
	 * Singleton instance.
	 *
	 * @return CreatorLms
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}



	/**
	 * Constructor for the PluginName class.
	 *
	 * Sets up all the appropriate hooks and actions within our plugin.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		register_activation_hook( CRLMS_FILE, [ $this, 'activate' ] );
		register_deactivation_hook( CRLMS_FILE, [ $this, 'deactivate' ] );
		$this->includes();
		$this->init_plugin();
	}


	/**
	 * Auto-load in-accessible properties on demand.
	 *
	 * @param mixed $key Key name.
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( in_array( $key, array( 'payment_gateways' ), true ) ) {
			return \CodeRex\Ecommerce\ecommerce()->$key();
//			return $this->$key();
		}
	}


	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 */
	private function includes(): void {

		if ( $this->is_request( 'admin' ) ) {
			include_once plugin_dir_path( __FILE__ ) . 'Admin/crlms-admin-functions.php';
		}

		/**
		 * Utility functions
		 */
		include_once plugin_dir_path( __FILE__ ) . 'Hooks/template-hooks.php';
		include_once plugin_dir_path( __FILE__ ) . 'Utility/core-functions.php';
		include_once plugin_dir_path( __FILE__ ) . 'Utility/course-functions.php';
		include_once plugin_dir_path( __FILE__ ) . 'Utility/lesson-functions.php';
		include_once plugin_dir_path( __FILE__ ) . 'Utility/chapter-functions.php';
		include_once plugin_dir_path( __FILE__ ) . 'Utility/notice-functions.php';
		include_once plugin_dir_path( __FILE__ ) . 'Utility/template-functions.php';
		include_once plugin_dir_path( __FILE__ ) . 'Utility/page-functions.php';
		include_once plugin_dir_path( __FILE__ ) . 'Utility/formatting-functions.php';
		include_once plugin_dir_path( __FILE__ ) . 'Utility/conditional-functions.php';

		/**
		 * Core classes.
		 */
		include_once plugin_dir_path( __FILE__ ) . 'Ajax.php';
		include_once plugin_dir_path( __FILE__ ) . 'TemplateLoader.php';
		include_once plugin_dir_path( __FILE__ ) . 'Assets/FrontendAssets.php';
		include_once plugin_dir_path( __FILE__ ) . 'Assets/AdminAssets.php';


		/**
		 * CPT registration
		 */
		require_once plugin_dir_path( __FILE__ ) . 'PostTypes/CoursePostType.php';
		require_once plugin_dir_path( __FILE__ ) . 'PostTypes/MembershipPostType.php';
	}


	/**
	 * Activating the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function activate() {
		\CreatorLms\Install::install();
	}

	/**
	 * Placeholder for deactivation function.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function deactivate() {}


	/**
	 * Load the plugin after all plugins are loaded.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init_plugin() {
		$this->define_constants();

		add_action( 'wp_loaded', [ $this, 'flush_rewrite_rules' ] );

		// Localize our plugin
		add_action( 'init', [ $this, 'localization_setup' ] );

		// Add the plugin page links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );


		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		add_action( 'init', array( $this, 'init' ) );

		add_action( 'init', array( '\CreatorLms\Shortcodes\Shortcodes', 'init' ) );

		//=== register post types ===//
		$sectionCPT = new CreatorLms\PostTypes\SectionPostType();
		$lessonCPT = new CreatorLms\PostTypes\LessonPostType();
		$lessonCPT = new CreatorLms\PostTypes\ChapterPostType();
		$quizCPT = new CreatorLms\PostTypes\QuizPostType();
		$questionCPT = new CreatorLms\PostTypes\QuestionPostType();

		//=== declare ajax requests ===//
		$ajax = new CreatorLms\Requests();

		// load the helper packages
		\CreatorLms\Packages::init();

		/**
		 * Fires after the plugin is loaded.
		 *
		 * @since 1.0.0
		 */
		do_action( 'creator_lms_loaded' );
	}


	/**
	 * Define the constants.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'CREATOR_LMS_VERSION', self::VERSION );
		define( 'CREATOR_LMS_SLUG', self::SLUG );
		define( 'CREATOR_LMS_FILE', CRLMS_FILE );
		define( 'CREATOR_LMS_PATH', dirname( CREATOR_LMS_FILE ) );
		define( 'CREATOR_LMS_INCLUDES', CREATOR_LMS_PATH . '/includes' );
		define( 'CREATOR_LMS_TEMPLATE_PATH', CREATOR_LMS_PATH . '/views' );
		define( 'CREATOR_LMS_URL', plugins_url( '', CREATOR_LMS_FILE ) );
		define( 'CREATOR_LMS_ASSETS_URL', CREATOR_LMS_URL . '/assets' );
		define( 'CREATOR_LMS_ASSETS_DIR', CREATOR_LMS_DIR . '/assets' );
		define( 'CREATOR_LMS_PRODUCTION', 'yes' );
		define( 'CREATOR_LMS_SESSION_CACHE_GROUP', 'creator_lms_session_id' );
		define( 'CREATOR_LMS_COURSE_CPT', 'crlms-course' );
		define( 'CREATOR_LMS_CHAPTER_CPT', 'crlms-chapter' );
		define( 'CREATOR_LMS_LESSON_CPT', 'crlms-lesson' );
		define( 'CREATOR_LMS_QUIZ_CPT', 'crlms-quiz' );
		define( 'CREATOR_LMS_QUESTION_CPT', 'crlms-question' );
		define( 'CREATOR_LMS_MEMBERSHIP_CPT', 'crlms-membership' );
		define( 'CREATOR_LMS_CHAPTER_RELATIONSHIP', 'crlms_chapter_relationship' );
		define( 'CREATOR_LMS_CONTENT_RELATIONSHIP', 'crlms_content_relationship' );
	}


	/**
	 * Flush rewrite rules after plugin is activated.
	 *
	 * Nothing being added here yet.
	 *
	 * @since 1.0.0
	 */
	public function flush_rewrite_rules() {}


	/**
	 * Init Creator LMS when WordPress is loaded
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {

		/**
		 * Action triggered before Creator LMS initialization begins.
		 *
		 * @since 1.0.0
		 */
		do_action('creator_lms_before_init');

		if ( $this->is_request( 'admin' ) ) {
			$this->admin 		= new \CreatorLms\Admin\Admin();
			$this->settings_pages = new \CreatorLms\Admin\Pages\AdminSettings();
			$this->settings_pages->init_settings_pages();
			$this->admin_menu 	= new CreatorLms\Admin\Menu();
			$this->admin_ajax 	= new \CreatorLms\Admin\Ajax();
		}

		$this->rest_api 		= new CreatorLms\Rest\Api();
		$this->course_factory 	= new \CreatorLms\Factory\CourseFactory();
		$this->lesson_factory 	= new \CreatorLms\Factory\LessonFactory();
		$this->chapter_factory 	= new \CreatorLms\Factory\ChapterFactory();
		$this->shortcode		= new CreatorLms\Shortcodes\Shortcodes();
		
		// set custom image size for creator LMS
		$this->set_custom_image_size();

		/**
		 * Action triggered after Creator LMS initialize.
		 *
		 * @since 1.0.0
		 */
		do_action('creator_lms_after_init');
	}


	public function on_plugins_loaded() {

		$this->course_hook_handler 	= new CourseHookHandler();
		$this->course_hook_handler->register_hooks();

		$this->chapter_hook_handler = new ChapterHookHandler();
		$this->chapter_hook_handler->register_hooks();
		
		$this->lesson_hook_handler = new LessonHookHandler();
		$this->lesson_hook_handler->register_hooks();

		/**
		 * Signal that Creator LMS is loaded
		 *
		 * @since 1.0.0
		 */
		do_action( 'creator_lma_loaded' );
	}


	/**
	 * Initialize plugin for localization.
	 *
	 * @uses load_plugin_textdomain()
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'creator-lms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Load the React-pages translations.
		if ( is_admin() ) {
			wp_set_script_translations( 'creator-lms-app', 'creator-lms', CRLMS_FILE . 'languages/' );
		}
	}

	/**
	 * What type of request is this.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type admin, ajax, cron or frontend
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();

			case 'ajax':
				return defined( 'DOING_AJAX' );

			case 'rest':
				return defined( 'REST_REQUEST' );

			case 'cron':
				return defined( 'DOING_CRON' );

			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Plugin action links
	 *
	 * @param array $links
	 *
	 * @since 0.2.0
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=plugin_name#/settings' ) . '">' . __( 'Settings', 'creator-lms' ) . '</a>';
		$links[] = '<a href="#" target="_blank">' . __( 'Documentation', 'creator-lms' ) . '</a>';

		return $links;
	}


	/**
	 * Init order module
	 *
	 * @since 1.0.0
	 */
	public function initialize_order_module() {
		if ( is_null( $this->order_loader ) || ! $this->order_loader instanceof CreatorLms\Order\OrderLoader ) {
			$this->order_loader 	= new CreatorLms\Order\OrderLoader();
		}
	}

	/**
	 * Set custom image for creator LMS.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function set_custom_image_size(){
		if ( function_exists( 'add_image_size' ) ) { 
			add_image_size( 'course-listing-thumb', 130, 80 );
		}
	}
}
