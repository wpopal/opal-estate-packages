<?php
namespace Opalestate_Packages\Core;

class WooCommerce_Hook {
	/**
	 * Handler constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_add_to_cart_handler_opalestate_package', [ $this, 'woocommerce_add_to_cart_handler', ], 100 );
		add_action( 'woocommerce_order_status_completed', [ $this, 'woocommerce_order_status_completed' ] );
		add_action( 'woocommerce_order_status_changed', [ $this, 'woocommerce_order_status_changed' ], 10, 3 );
	}

	public function woocommerce_add_to_cart_handler() {
		global $woocommerce;
		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );
		$product           = wc_get_product( absint( $product_id ) );
		$quantity          = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( $_REQUEST['quantity'] );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

		if ( $product->is_type( 'opalestate_package' ) && $passed_validation ) {
			$woocommerce->cart->empty_cart();
			if ( $woocommerce->cart->add_to_cart( $product_id, $quantity ) ) {
				wp_safe_redirect( wc_get_checkout_url() );
				die;
			}
		}
	}

	/**
	 * @param $order_id
	 */
	public function woocommerce_order_status_completed( $order_id ) {
		$order = new \WC_Order( $order_id );

		foreach ( $order->get_items() as $item ) {
			$product = wc_get_product( $item['product_id'] );

			if ( $product->is_type( 'opalestate_package' ) && $order->get_customer_id() ) {
				$user_id = $order->get_customer_id();
				$date    = time();

				$expired_time = $product->get_expiration_date( $date );

				update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'payment_id', $order_id );
				update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_activation', $date );
				update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_expired', $expired_time );
				update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_id', $product->get_id() );
				update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'send_expired_email', 0 );

				/**
				 * Get some information from selected package.
				 */
				$pack_listings          = $product->get_meta( 'opalestate_package_package_listings' );
				$pack_featured_listings = $product->get_meta( 'opalestate_package_package_featured_listings' );
				$is_unlimited_listings  = $product->get_meta( 'opalestate_package_unlimited_listings' );

				$pack_unlimited_listings = $is_unlimited_listings == 'on' ? 0 : 1;

				/**
				 * Get package information with user logined
				 */
				$current_listings         = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', true );
				$curent_featured_listings = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_featured_listings', true );
				$current_pack             = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_id', true );

				$user_current_listings          = opalesate_get_user_current_listings( $user_id ); // get user current listings ( no expired )
				$user_current_featured_listings = opalesate_get_user_current_featured_listings( $user_id ); // get user current featured listings ( no expired )

				if ( opalesate_check_package_downgrade_status( $user_id, $product->get_id() ) ) {
					$new_listings          = $pack_listings;
					$new_featured_listings = $pack_featured_listings;
				} else {
					$new_listings          = $pack_listings - $user_current_listings;
					$new_featured_listings = $pack_featured_listings - $user_current_featured_listings;
				}

				// in case of downgrade
				if ( $new_listings < 0 ) {
					$new_listings = 0;
				}

				if ( $new_featured_listings < 0 ) {
					$new_featured_listings = 0;
				}


				if ( $pack_unlimited_listings == 1 ) {
					$new_listings = -1;
				}

				/**
				 * Update new number of packages listings and featured listing.
				 */
				update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', $new_listings );
				update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_featured_listings', $new_featured_listings );

				do_action( 'opalestate_packages_after_update_user_membership', $order_id, $user_id, $product->get_id() );

				break;
			}
		}
	}

	public function woocommerce_order_status_changed( $order_id, $old_status, $new_status ) {

		// Check if order is changing from completed to not completed
		if ( $old_status != 'completed' && $new_status == 'completed' ) {
			$order = new \WC_Order( $order_id );

			foreach ( $order->get_items() as $item ) {
				$product = wc_get_product( $item['product_id'] );

				if ( $product->is_type( 'opalestate_package' ) && $order->get_customer_id() ) {
					$user_id = $order->get_customer_id();
					$date    = time();

					$expired_time = $product->get_expiration_date( $date );

					update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'payment_id', $order_id );
					update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_activation', $date );
					update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_expired', $expired_time );
					update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_id', $product->get_id() );
					update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'send_expired_email', 0 );

					/**
					 * Get some information from selected package.
					 */
					$pack_listings          = $product->get_meta( 'opalestate_package_package_listings' );
					$pack_featured_listings = $product->get_meta( 'opalestate_package_package_featured_listings' );
					$is_unlimited_listings  = $product->get_meta( 'opalestate_package_unlimited_listings' );

					$pack_unlimited_listings = $is_unlimited_listings == 'on' ? 0 : 1;

					/**
					 * Get package information with user logined
					 */
					$current_listings         = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', true );
					$curent_featured_listings = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_featured_listings', true );
					$current_pack             = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_id', true );

					$user_current_listings          = opalesate_get_user_current_listings( $user_id ); // get user current listings ( no expired )
					$user_current_featured_listings = opalesate_get_user_current_featured_listings( $user_id ); // get user current featured listings ( no expired )

					if ( opalesate_check_package_downgrade_status( $user_id, $product->get_id() ) ) {
						$new_listings          = $pack_listings;
						$new_featured_listings = $pack_featured_listings;
					} else {
						$new_listings          = $pack_listings - $user_current_listings;
						$new_featured_listings = $pack_featured_listings - $user_current_featured_listings;
					}

					// in case of downgrade
					if ( $new_listings < 0 ) {
						$new_listings = 0;
					}

					if ( $new_featured_listings < 0 ) {
						$new_featured_listings = 0;
					}


					if ( $pack_unlimited_listings == 1 ) {
						$new_listings = -1;
					}

					/**
					 * Update new number of packages listings and featured listing.
					 */
					update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', $new_listings );
					update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_featured_listings', $new_featured_listings );

					do_action( 'opalestate_packages_after_update_user_membership', $order_id, $user_id, $product->get_id() );

					break;
				}
			}
		}

	}
}
