<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://kmancuso.com/
 * @since      1.0.0
 *
 * @package    Ronik_Media_Cleaner
 * @subpackage Ronik_Media_Cleaner/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ronik_Media_Cleaner
 * @subpackage Ronik_Media_Cleaner/includes
 * @author     Kevin Mancuso <kevin@ronikdesign.com>
 */
class Ronik_Media_Cleaner {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ronik_Media_Cleaner_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'RONIK_MEDIA_CLEANER_VERSION' ) ) {
			$this->version = RONIK_MEDIA_CLEANER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ronik-media-cleaner';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ronik_Media_Cleaner_Loader. Orchestrates the hooks of the plugin.
	 * - Ronik_Media_Cleaner_i18n. Defines internationalization functionality.
	 * - Ronik_Media_Cleaner_Admin. Defines all hooks for the admin area.
	 * - Ronik_Media_Cleaner_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ronik-media-cleaner-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ronik-media-cleaner-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ronik-media-cleaner-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ronik-media-cleaner-public.php';

		$this->loader = new Ronik_Media_Cleaner_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ronik_Media_Cleaner_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ronik_Media_Cleaner_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ronik_Media_Cleaner_Admin( $this->get_plugin_name(), $this->get_version() );

		// Lets check to see if dependencies are met before continuing...
		$this->loader->add_action( 'admin_init', $plugin_admin, 'rmc_plugin_dependencies' );

		// Hooking up our function to theme setup
		$this->loader->add_action('acf/init', $plugin_admin, 'rmc_acf_op_init');
		$this->loader->add_action('acf/init', $plugin_admin, 'rmc_acf_op_init_fields', 10);
		$this->loader->add_action('acf/init', $plugin_admin, 'rmc_acf_op_init_functions', 20);

		// Enque Scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		// Add Ajax
		$this->loader->add_action('wp_ajax_nopriv_do_init_remove_unused_media', $plugin_admin, 'rmc_ajax_media_cleaner_remove');
		$this->loader->add_action('wp_ajax_do_init_remove_unused_media', $plugin_admin, 'rmc_ajax_media_cleaner_remove');

		$this->loader->add_action('wp_ajax_nopriv_do_init_unused_media_migration', $plugin_admin, 'rmc_ajax_media_cleaner');
		$this->loader->add_action('wp_ajax_do_init_unused_media_migration', $plugin_admin, 'rmc_ajax_media_cleaner');
	}


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ronik_Media_Cleaner_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ronik_Media_Cleaner_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
