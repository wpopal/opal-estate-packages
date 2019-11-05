<?php
/**
 * Plugin Name:     Opal Estate Packages
 * Plugin URI:      http://www.wpopal.com/product/opal-estate-wordpress-plugin/
 * Description:     It's another solution for the Opal Membership plugin. Seamlessly connects with Woocommerce to benefit from all variety of Woocommerce extensions and payment gateways.
 * Author:          WPOPAL
 * Author URI:      wpopal.com
 * Text Domain:     opal-estate-packages
 * Domain Path:     /languages
 * Version:         1.0.1
 * WC requires at least: 3.0.0
 * WC tested up to: 3.7
 *
 * @package         Opalestate_Packages
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
	function opalestate_packages_wordpress_upgrade_notice() {
		$message = sprintf( esc_html__( 'Opal Packages requires at least WordPress version 4.6, you are running version %s. Please upgrade and try again!', 'opal-packages' ),
			$GLOBALS['wp_version'] );
		printf( '<div class="error"><p>%s</p></div>', $message ); // WPCS: XSS OK.

		deactivate_plugins( [ 'opalestate_packages/opalestate_packages.php' ] );
	}

	add_action( 'admin_notices', 'opalestate_packages_wordpress_upgrade_notice' );

	return;
}

/**
 * And only works with PHP 5.6 or later.
 */
if ( version_compare( phpversion(), '5.6', '<' ) ) {
	/**
	 * Adds a message for outdate PHP version.
	 */
	function opalestate_packages_php_upgrade_notice() {
		$message = sprintf( esc_html__( 'Opal Packages requires at least PHP version 5.6 to work, you are running version %s. Please contact to your administrator to upgrade PHP version!',
			'opal-packages'
		),
			phpversion() );
		printf( '<div class="error"><p>%s</p></div>', $message ); // WPCS: XSS OK.

		deactivate_plugins( [ 'opalestate_packages/opalestate_packages.php' ] );
	}

	add_action( 'admin_notices', 'opalestate_packages_php_upgrade_notice' );

	return;
}

if ( defined( 'OPALESTATE_PACKAGES_VERSION' ) ) {
	return;
}

define( 'OPALESTATE_PACKAGES_VERSION', '1.0.1' );
define( 'OPALESTATE_PACKAGES_USER_PREFIX', 'opalmb_' );
define( 'OPALESTATE_PACKAGES_PAYMENT_PREFIX', 'opalmembership_payment_' );
define( 'OPALESTATE_PACKAGES_PACKAGES_PREFIX', 'opalestate_package_' );
define( 'OPALESTATE_PACKAGES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'OPALESTATE_PACKAGES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Admin notice: Require OpalEstate && WooCommerce.
 */
function opalestate_packages_admin_notice() {
	if ( ! class_exists( 'OpalEstate' ) ) {
		echo '<div class="error">';
		echo '<p>' . __( 'Please note that the <strong>Opal Packages</strong> plugin is meant to be used only with the <strong>Opal Estate Pro</strong> plugin.</p>', 'opal-packages' );
		echo '</div>';
	}

	if ( ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="error">';
		echo '<p>' . __( 'Please note that the <strong>Opal Packages</strong> plugin is meant to be used only with the <strong>WooCommerce</strong> plugin.</p>', 'opal-packages' );
		echo '</div>';
	}

	if ( class_exists( 'OpalMembership' ) ) {
		echo '<div class="error">';
		echo '<p>' . __( 'Please note that the <strong>Opal Packages</strong> plugin is meant to be used only without the <strong>OpalMembership</strong> plugin. You should only choose 1 of 2.</p>',
				'opal-packages' );
		echo '</div>';
	}
}

/**
 * Is activatable?
 *
 * @return bool
 */
function is_opalestate_packages_activatable() {
	return class_exists( 'OpalEstate' ) && class_exists( 'WooCommerce' ) && ! class_exists( 'OpalMembership' );
}

add_action( 'plugins_loaded', function () {
	if ( is_opalestate_packages_activatable() ) {
		// Include the loader.
		require_once dirname( __FILE__ ) . '/loader.php';

		$GLOBALS['opalestate_packages'] = Opalestate_Packages::get_instance();
	}
	add_action( 'admin_notices', 'opalestate_packages_admin_notice', 4 );
} );
