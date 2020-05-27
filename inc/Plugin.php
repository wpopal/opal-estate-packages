<?php
namespace Opalestate_Packages;

use Opalestate_Packages\Admin\Metabox;
use Opalestate_Packages\Admin\Settings;
use Opalestate_Packages\Core\Handler;
use Opalestate_Packages\Core\WooCommerce_Hook;
use Opalestate_Packages\Core\Shortcodes;

/**
 * Set up and initialize
 */
class Plugin {
	/**
	 *  The instance.
	 *
	 * @var void
	 */
	private static $instance;

	/**
	 * Returns the instance.
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Actions setup
	 */
	public function __construct() {
		$this->register_admin();
		$this->register_core();

		add_action( 'plugins_loaded', [ $this, 'i18n' ], 3 );
		add_filter( 'woocommerce_register_post_type_product', [ $this, 'rename_woocommerce' ], 3 );
	}

	/**
	 * Register admin.
	 */
	public function register_admin() {
		new Metabox();
		new Settings();
	}

	/**
	 * Register core.
	 */
	public function register_core() {
		new Shortcodes();
		new WooCommerce_Hook();
		new Handler();
	}

	/**
	 * Translations.
	 */
	public function i18n() {
		load_plugin_textdomain( 'opal-estate-packages', false, 'opal-estate-packages/languages' );
	}

	/**
	 * @param $args
	 * @return mixed
	 */
	public function rename_woocommerce( $args ) {
		if ( apply_filters( 'opalestate_packages_rename_woocommerce', true ) ) {
			$args['labels']['name']          = __( 'Packages', 'opal-estate-packages' );
			$args['labels']['singular_name'] = __( 'Package', 'opal-estate-packages' );
			$args['labels']['menu_name']     = _x( 'Packages', 'Admin menu name', 'opal-estate-packages' );
			$args['labels']['all_items']     = _x( 'All Packages', 'Admin menu name', 'opal-estate-packages' );
		}

		return $args;
	}
}
