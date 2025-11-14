<?php

/**
 * The file that defines the core plugin class
 *
 * This is used to define admin-specific hooks, public-facing site hooks, 
 * load active utilities, and activate the html buffer.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://github.com/jruns/speedy-utilities
 * @since      0.1.0
 *
 * @package    SpeedyUtilities
 * @subpackage SpeedyUtilities/includes
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class SpeedyUtilities {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      SpeedyUtilities_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The current plugin settings.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $settings    The current plugin settings.
	 */
	protected $settings;

	/**
	 * If we should only check wp-config.php constants for active plugins.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $wpconfig_mode    The wp-config mode setting.
	 */
	protected $wpconfig_mode;

	/**
	 * The status of the HTML buffer.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      bool    $buffer_is_active    The current status of the HTML buffer.
	 */
	protected $buffer_is_active;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {
		if ( defined( 'SPEEDY_VERSION' ) ) {
			$this->version = SPEEDY_VERSION;
		} else {
			$this->version = '1.1.0';
		}
		$this->plugin_name = 'speedy-utilities';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->load_utilities();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - SpeedyUtilities_Loader. Orchestrates the hooks of the plugin.
	 * - SpeedyUtilities_Conditional_Checks. Defines page conditional processing.
	 * - SpeedyUtilities_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-loader.php';
		
		/**
		 * The class responsible for defining functions for page conditional processing.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-conditional-checks.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin.php';

		$this->loader = new SpeedyUtilities_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new SpeedyUtilities_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'registersettings' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_options_page' );
		$this->loader->add_action( 'plugin_action_links_' . SPEEDY_BASE_NAME, $plugin_admin, 'add_plugin_action_links' );
	}

	private function load_settings() {
		$defaults = array(
			'active_utilities' => array()
		);
		$this->settings = wp_parse_args( get_option( 'speedyutils_settings' ), $defaults );
		
		$this->wpconfig_mode = false;
		if( defined( 'SPEEDY_ENABLE_WPCONFIG_MODE' ) ) {
			if ( constant( 'SPEEDY_ENABLE_WPCONFIG_MODE' ) ) {
				$this->wpconfig_mode = true;
			}
		}
	}

	private function utility_is_active( $className ) {
		$className = str_replace( 'SpeedyUtilities_', '', $className );

		$constant_name = strtoupper( 'speedy_' . $className );
		$utility_name = strtolower( $className );

		if( defined( $constant_name ) ) {
			if ( constant( $constant_name ) ) {
				return true;
			}
		} else if ( ! $this->wpconfig_mode ) {
			if ( array_key_exists( $utility_name, $this->settings['active_utilities'] ) && $this->settings['active_utilities'][$utility_name] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Load enabled utilities
	 */
	private function load_utilities() {
		$utilities_dir = dirname( __FILE__ ) . '/utilities/';
		$this->load_settings();

		if ( is_dir( $utilities_dir ) ) {
			if ( $dh = opendir( $utilities_dir ) ) {
				while ( ( $file = readdir( $dh ) ) !== false ) {
					if ( $file == '.' || $file == '..' ) {
						continue;
					}

					$className = 'SpeedyUtilities_' . str_replace( array( 'class-', '-', '.php'), array( '', ' ', ''), $file );
					$className = str_replace( ' ', '_', ucwords( $className ) );

					if ( $this->utility_is_active( $className ) ) {
						include_once( $utilities_dir . $file );

						// Only activate some utilites in the WP admin
						if ( is_admin() && ! ( property_exists( $className, 'runs_in_admin' ) && $className::$runs_in_admin ) ) {
							continue;
						}

						// Activate output buffer if utility requires it and it has not been activated already
						if( property_exists( $className, 'needs_html_buffer' ) && $className::$needs_html_buffer ) {
							$this->activate_html_buffer();
						}

						// Activate on after_setup_theme so we can access filters
						add_action( 'after_setup_theme', function() use ( $utilities_dir, $file, $className ) {
							$utility = new $className;
							$utility->run();
						}, 1 );
					}
				}
				closedir( $dh );
			}
		}
	}

	/**
	 * Activate HTML Buffer if required by an active utility
	 */
	private function activate_html_buffer() {
		if ( ! $this->buffer_is_active ) {
			require_once plugin_dir_path( __FILE__ ) . 'class-html-buffer.php';
			new SpeedyUtilities_Html_Buffer();

			$this->buffer_is_active = true;
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.1.0
	 * @return    SpeedyUtilities_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}