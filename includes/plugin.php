<?php
namespace BRU_Addons;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin class.
 *
 * The main class that initiates and runs the addon.
 *
 * @since 1.0.2
 */
final class Features
{

	/**
	 * Instance
	 *
	 * @since 1.0.2
	 * @access private
	 * @static
	 * @var \BRU_Addons\Features The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.2
	 * @access public
	 * @static
	 * @return \BRU_Addons\Features An instance of the class.
	 */
	public static function instance()
	{

		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * Perform some compatibility checks to make sure basic requirements are meet.
	 * If all compatibility checks pass, initialize the functionality.
	 *
	 * @since 1.0.2
	 * @access public
	 */
	public function __construct()
	{

		if ($this->is_compatible()) {
			add_action('elementor/init', [$this, 'init']);
		}

		// Register Widget Styles
		add_action('elementor/frontend/after_enqueue_styles', [$this, 'frontend_widget_styles'], 999);

		// // Register Widget Scripts
		add_action("elementor/frontend/after_enqueue_scripts", [$this, 'frontend_assets_scripts']);

		// Include Custom Post Type
		// register_activation_hook( __FILE__, [$this, 'cpt_activate'] );

		require_once BRU_DIR . '/includes/CPT/register-cpt-class.php';

		\BRU_Addons\CPT\register_Cpts::instance();

	}

	/**
	 * Compatibility Checks
	 *
	 * Checks whether the site meets the addon requirement.
	 *
	 * @since 1.0.2
	 * @access public
	 */
	public function is_compatible()
	{

		// Check if Elementor installed and activated
		if (!did_action('elementor/loaded')) {
			add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
			return false;
		}

		// Check for required Elementor version
		if (!version_compare(ELEMENTOR_VERSION, MINIMUM_ELEMENTOR_VERSION, '>=')) {
			add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
			return false;
		}

		// Check for required PHP version
		if (version_compare(PHP_VERSION, MINIMUM_PHP_VERSION, '<')) {
			add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
			return false;
		}

		return true;
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.2
	 * @access public
	 */
	public function admin_notice_missing_main_plugin()
	{

		if (isset($_GET['activate'])) unset($_GET['activate']);

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'bruneza'),
			'<strong>' . esc_html__('Bruneza Addons', 'bruneza') . '</strong>',
			'<strong>' . esc_html__('Elementor', 'bruneza') . '</strong>'
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.2
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version()
	{

		if (isset($_GET['activate'])) unset($_GET['activate']);

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'bruneza'),
			'<strong>' . esc_html__('Bruneza Addons', 'bruneza') . '</strong>',
			'<strong>' . esc_html__('Elementor', 'bruneza') . '</strong>',
			MINIMUM_ELEMENTOR_VERSION
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.2
	 * @access public
	 */
	public function admin_notice_minimum_php_version()
	{

		if (isset($_GET['activate'])) unset($_GET['activate']);

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'bruneza'),
			'<strong>' . esc_html__('Bruneza Addons', 'bruneza') . '</strong>',
			'<strong>' . esc_html__('PHP', 'bruneza') . '</strong>',
			MINIMUM_PHP_VERSION
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}

	/**
	 * Initialize
	 *
	 * Load the addons functionality only after Elementor is initialized.
	 *
	 * Fired by `elementor/init` action hook.
	 *
	 * @since 1.0.2
	 * @access public
	 */
	public function init()
	{

		load_plugin_textdomain('bruneza');

		add_action('elementor/widgets/register', [$this, 'register_widgets']);

		// foreach (glob(BRU_DIR . "/includes/queries/*.php") as $filename) {
		// 	require_once $filename;
		// }
		// foreach (glob(BRU_DIR . "/includes/queries/content-template/*.php") as $filename) {
		// 	require_once $filename;
		// }
		// foreach (glob(BRU_DIR . "/includes/queries/js-reusable/*.php") as $filename) {
		// 	require_once $filename;
		// }
	}

	public function cpt_activate()
	{
		// unregister_post_type('bruneza_teams');

		// $this->team_cpt->register_cpt();;
		flush_rewrite_rules();
	}

	// Extra functionality

	/*
	plugin css
	*/
	function frontend_widget_styles()
	{
		wp_enqueue_style("bootstrap-grid-css", BRU_ASSETS . 'css/bootstrap-grid.min.css');
		wp_enqueue_style("bootstrap-grid-css", BRU_ASSETS . 'css/bootstrap-extra.css');
		wp_enqueue_style("bruneza-main-css", BRU_ASSETS . 'css/style.css', array(), rand(1, 1000));
	}

	// /*
	// plugin elementor js
	// */
	function frontend_assets_scripts()
	{
		//posts carousel active
		wp_enqueue_script("bruneza-bootstrap-js", BRU_ASSETS . 'js/bootstrap.min.js', array('jquery'), VERSION, true);
		wp_enqueue_script("bruneza-main-js", BRU_ASSETS . 'js/script.js', array('jquery'), rand(1, 1000), true);
	}

	/**
	 * Register Currency Control.
	 *
	 * Include control file and register control class.
	 *
	 * @since 1.0.2
	 * @param \Elementor\Controls_Manager $controls_manager Elementor controls manager.
	 * @return void
	 */

	function register_control($controls_manager)
	{

		// foreach (glob(BRU_DIR . "/includes/controls/*.php") as $filename) {
		// 	require_once $filename;
		// }

		// $controls_manager->add_group_control(Group_BRU_Query::get_type(), new Group_BRU_Query());
	}
	/**
	 * Register Widgets
	 *
	 * Load widgets files and register new Elementor widgets.
	 *
	 * Fired by `elementor/widgets/register` action hook.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets($widgets_manager)
	{

		// foreach (glob(BRU_DIR . "/includes/widgets/*.php") as $filename) {
		// 	require_once $filename;
		// }

		// $widgets_manager->register(new \BRU_Addons\Widgets\BRU_Deals_Carousel());

	}
}
