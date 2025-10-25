<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/jruns/wp-performance-utilities
 * @since      0.1.0
 *
 * @package    Performance_Utilities
 * @subpackage Performance_Utilities/admin
 */
class Performance_Utilities_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function add_options_page() {
		add_options_page(
			'Performance Utilities',
			'Performance Utilities',
			'manage_options',
			'performance-utilities',
			array( $this, 'render_options_page' )
		);
	}
	
    public function registersettings() {
        register_setting( 'performance-utilities', 'wppu_disable_jquery_migrate');
        register_setting( 'performance-utilities', 'wppu_remove_versions');
        register_setting( 'performance-utilities', 'wppu_enable_youtube_facade');
        register_setting( 'performance-utilities', 'wppu_move_scripts_and_styles_to_footer');
        register_setting( 'performance-utilities', 'wppu_remove_scripts_and_styles');
        register_setting( 'performance-utilities', 'wppu_delay_scripts_and_styles');
        register_setting( 'performance-utilities', 'wppu_delay_scripts_and_styles_autoload_delay');
        register_setting( 'performance-utilities', 'wppu_preload_images');
    }

	public function render_options_page() {
		require_once( plugin_dir_path( __FILE__ ) . 'partials/performance-utilities-admin-options-display.php' );
	}

	public function add_plugin_action_links( array $links ) {
		$settings_url = menu_page_url( 'performance-utilities', false );
		return array_merge( array(
			'settings' => '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'performance-utilities' ) . '</a>',
		), $links );
	}
}
