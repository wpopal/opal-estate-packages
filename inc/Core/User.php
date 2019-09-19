<?php
namespace Opalestate_Packages\Core;

class User {

	/**
	 * User constructor.
	 */
	public function __construct() {
		define( 'OPALESTATE_PACKAGES_USER_PREFIX', 'opalmb_' );
	}

	/**
	 * @param null $user_id
	 * @return int
	 */
	public static function get_current_membership( $user_id = null ) {
		return (int) get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_id', true );
	}

	/**
	 * @param null $user_id
	 * @return bool
	 */
	public static function is_membership_valid( $user_id = null ) {
		if ( ! defined( 'OPALESTATE_PACKAGES_USER_PREFIX' ) ) {
			return false;
		}

		if ( ! $user_id ) {
			$user    = wp_get_current_user();
			$user_id = $user->ID;
		}

		$package_id = (int) get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_id', true );

		$product = wc_get_product( $package_id );

		/* package currently is not exists */
		if ( ! $package_id || ! get_post( $package_id ) || ! $product ) {
			return false;
		}

		$payment_id = (int) get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'payment_id', true );

		/* payment is not completed */
		if ( ! $payment_id || get_post_status( $payment_id ) !== 'wc-completed' ) {
			return false;
		}

		$package_expired = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_expired', true );

		if ( ! $package_expired || ( $package_expired <= time() ) ) {
			return false;
		}

		return true;
	}
}
