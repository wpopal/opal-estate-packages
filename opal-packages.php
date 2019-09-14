<?php
/**
 * Plugin Name:     Opal Packages
 * Plugin URI:      wpopal.com
 * Description:     //.
 * Author:          WPOPAL
 * Author URI:      wpopal.com
 * Text Domain:     opal-packages
 * Domain Path:     /languages
 * Version:         1.0.0
 * WC requires at least: 3.0.0
 * WC tested up to: 3.7
 *
 * @package         Opal_Packages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Opal Packages only works with WordPress 4.6 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.6', '<' ) ) {
	/**
	 * Prints an update nag after an unsuccessful attempt to active
	 * Opal Packages on WordPress versions prior to 4.6.
	 *
	 * @global string $wp_version WordPress version.
	 */
	function opal_packages_wordpress_upgrade_notice() {
		$message = sprintf( esc_html__( 'Opal Packages requires at least WordPress version 4.6, you are running version %s. Please upgrade and try again!', 'opal-packages' ),
			$GLOBALS['wp_version'] );
		printf( '<div class="error"><p>%s</p></div>', $message ); // WPCS: XSS OK.

		deactivate_plugins( [ 'opal_packages/opal_packages.php' ] );
	}

	add_action( 'admin_notices', 'opal_packages_wordpress_upgrade_notice' );

	return;
}

/**
 * And only works with PHP 5.6 or later.
 */
if ( version_compare( phpversion(), '5.6', '<' ) ) {
	/**
	 * Adds a message for outdate PHP version.
	 */
	function opal_packages_php_upgrade_notice() {
		$message = sprintf( esc_html__( 'Opal Packages requires at least PHP version 5.6 to work, you are running version %s. Please contact to your administrator to upgrade PHP version!', 'opal-packages'
		),
			phpversion() );
		printf( '<div class="error"><p>%s</p></div>', $message ); // WPCS: XSS OK.

		deactivate_plugins( [ 'opal_packages/opal_packages.php' ] );
	}

	add_action( 'admin_notices', 'opal_packages_php_upgrade_notice' );

	return;
}

if ( defined( 'OPAL_PACKAGES_VERSION' ) ) {
	return;
}

define( 'OPAL_PACKAGES_VERSION', '1.0.0' );
define( 'OPAL_PACKAGES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'OPAL_PACKAGES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Admin notice: Require WooCommerce.
 */
function opal_packages_admin_notice() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="error">';
		echo '<p>' . __( 'Please note that the <strong>Opal Packages</strong> plugin is meant to be used only with the <strong>WooCommerce</strong> plugin.</p>', 'opal-packages' );
		echo '</div>';
	}
}

// Include the loader.
require_once dirname( __FILE__ ) . '/loader.php';

add_action( 'plugins_loaded', function () {
	if ( class_exists( 'WooCommerce' ) ) {
		$GLOBALS['opal_packages'] = Opal_Packages::get_instance();
	}
	add_action( 'admin_notices', 'opal_packages_admin_notice', 4 );
} );
