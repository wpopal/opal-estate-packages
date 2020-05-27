<?php

use Opalestate_Packages\Core\Template_Loader;
use Opalestate_Packages\Core\User;
use Opalestate_Packages\Core\Query;

/**
 * Register product type.
 */
function opalestate_packages_add_product_type() {
	class WC_Product_Opalestate_Package extends WC_Product {
		/**
		 * Get the product if ID is passed, otherwise the product is new and empty.
		 * This class should NOT be instantiated, but the wc_get_product() function
		 * should be used. It is possible, but the wc_get_product() is preferred.
		 *
		 * @param int|\WC_Product|object $product Product to init.
		 */
		public function __construct( $product = 0 ) {
			$this->product_type = 'opalestate_package';
			parent::__construct( $product );
		}

		public function is_purchasable() {
			return true;
		}

		public function is_sold_individually() {
			return true;
		}

		public function is_virtual() {
			return true;
		}

		// public function is_downloadable() {
		// 	return true;
		// }
		//
		// public function has_file( $download_id = '' ) {
		// 	return false;
		// }

		public function get_package_maximum_purchased() {
			$value = get_post_meta( $this->get_id(), 'opalestate_package_maximum_purchased', true );

			return ! empty( $value ) ? $value : -1;
		}

		public function is_enable_expired() {
			$value = get_post_meta( $this->get_id(), 'opalestate_package_enable_expired', true );

			if ( $value ) {
				return true;
			}

			return false;
		}

		public function get_package_duration() {
			$value = get_post_meta( $this->get_id(), 'opalestate_package_duration', true );

			return ! empty( $value ) ? absint( $value ) : 0;
		}

		public function get_package_duration_unit() {
			$value = get_post_meta( $this->get_id(), 'opalestate_package_duration_unit', true );

			if ( empty( $value ) || ! in_array( $value, [ 'day', 'week', 'month', 'year' ] ) ) {
				return 'day';
			}

			return $value;
		}

		public function is_highlighted() {
			$value = get_post_meta( $this->get_id(), 'opalestate_package_hightlighted', true );

			if ( $value ) {
				return true;
			}

			return false;
		}

		public function is_package_recurring() {
			$value = get_post_meta( $this->get_id(), 'opalestate_package_recurring', true );

			if ( $value ) {
				return true;
			}

			return false;
		}

		public function get_package_listings() {
			$value = get_post_meta( $this->get_id(), 'opalestate_package_package_listings', true );

			return ! empty( $value ) ? absint( $value ) : 0;
		}

		public function is_unlimited_listings() {
			$value = get_post_meta( $this->get_id(), 'opalestate_package_unlimited_listings', true );
			// Convert with opal membership.
			if ( $value ) {
				return false;
			}

			return true;
		}

		public function get_package_featured_listings() {
			$value = get_post_meta( $this->get_id(), 'opalestate_package_package_featured_listings', true );

			return ! empty( $value ) ? ( $value ) : 0;
		}

		public function get_expiration_unit_time() {

			if ( ! ( $duration = $this->get_package_duration() ) ) {
				$duration = 1;
			}

			$duration_unit = $this->get_package_duration_unit();

			switch ( $duration_unit ) {
				case 'day':
					$seconds = 60 * 60 * 24;
					break;
				case 'week':
					$seconds = 60 * 60 * 24 * 7;
					break;
				case 'month':
					$seconds = 60 * 60 * 24 * 30;
					break;
				case 'year':
					$seconds = 60 * 60 * 24 * 365;
					break;
			}

			return $seconds * $duration;
		}

		/*
		 * Method that returns the expiration date of the subscription plan
		 */
		public function get_expiration_date( $actived_time = false ) {
			$expired_date = ( $actived_time + $this->get_expiration_unit_time() );

			return $expired_date;
		}

		public function add_to_cart_url() {
			$url = $this->is_in_stock() ? esc_url( remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id, home_url() ) ) ) : get_permalink( $this->id );

			return apply_filters( 'opalestate_packages_product_add_to_cart_url', $url, $this );
		}

		public function add_to_cart_text() {
			$text = $this->is_purchasable() && $this->is_in_stock() ? esc_html__( 'Select', 'opal-estate-packages' ) : esc_html__( 'Read More', 'opal-estate-packages' );

			return apply_filters( 'opalestate_packages_product_add_to_cart_text', $text, $this );
		}
	}
}

add_action( 'init', 'opalestate_packages_add_product_type' );

function opalestate_packages_get_template_part( $name, $args = [], $plugin_dir = '' ) {
	return Template_Loader::get_template_part( $name, $args, $plugin_dir );
}

/**
 * Count Number of listing following user
 */
if ( ! function_exists( 'opalesate_get_user_current_listings' ) ) {
	function opalesate_get_user_current_listings( $user_id ) {
		$args  = [
			'post_type'   => 'opalestate_property',
			'post_status' => [ 'pending', 'publish' ],
			'author'      => $user_id,

		];
		$posts = new WP_Query( $args );

		return $posts->found_posts;
	}
}

if ( ! function_exists( 'opalesate_get_user_current_featured_listings' ) ) {
	/**
	 * Count Number of featured listing following user
	 */
	function opalesate_get_user_current_featured_listings( $user_id ) {

		$args = [
			'post_type'   => 'opalestate_property',
			'post_status' => [ 'pending', 'publish' ],
			'author'      => $user_id,
			'meta_query'  => [
				[
					'key'           => OPALESTATE_PROPERTY_PREFIX . 'featured',
					'value'         => 1,
					'meta_compare ' => '=',
				],
			],
		];

		$posts = new WP_Query( $args );

		return $posts->found_posts;
	}
}

if ( ! function_exists( 'opalesate_check_package_downgrade_status' ) ) {
	/**
	 * Check current package is downgrade package or not via current number of featured, listing lesser
	 */
	function opalesate_check_package_downgrade_status( $user_id, $package_id ) {
		$product = wc_get_product( $package_id );

		if ( ! $product ) {
			return false;
		}

		$pack_listings           = $product->get_meta( 'opalestate_package_package_listings' );
		$pack_featured_listings  = $product->get_meta( 'opalestate_package_package_featured_listings' );
		$is_unlimited            = $product->get_meta( 'opalestate_package_unlimited_listings' );
		$pack_unlimited_listings = $is_unlimited == 'on' ? 0 : 1;

		$user_current_listings          = opalesate_get_user_current_listings( $user_id );
		$user_current_featured_listings = opalesate_get_user_current_featured_listings( $user_id );

		$current_listings = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', true );

		if ( $pack_unlimited_listings == 1 ) {
			return false;
		}

		// if is unlimited and go to non unlimited pack
		if ( $current_listings == -1 && $pack_unlimited_listings != 1 ) {
			return true;
		}

		return ( $user_current_listings > $pack_listings ) || ( $user_current_featured_listings > $pack_featured_listings );
	}
}

if ( ! function_exists( 'opalesate_check_has_add_listing' ) ) {
	/**
	 * Check Current User having permission to add new property or not?
	 */
	function opalesate_check_has_add_listing( $user_id, $package_id = null ) {
		if ( ! $package_id ) {
			$package_id = (int) get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_id', true );
		}

		$package_listings = (int) get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', true );

		$product            = wc_get_product( $package_id );
		$unlimited_listings = $product ? $product->get_meta( 'opalestate_package_unlimited_listings' ) : '';
		$unlimited_listings = ! empty( $unlimited_listings ) && $unlimited_listings == 'yes' ? 0 : 1;

		if ( $package_id > 0 && $unlimited_listings ) {
			return true;
		}

		if ( $package_listings > 0 ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'opalesate_get_user_featured_remaining_listing' ) ) {
	/**
	 * Check current package is downgrade package or not via current number of featured, listing lesser
	 */
	function opalesate_get_user_featured_remaining_listing( $user_id ) {

		$count = get_the_author_meta( OPALESTATE_PACKAGES_USER_PREFIX . 'package_featured_listings', $user_id );

		return $count;
	}
}

if ( ! function_exists( 'opalestate_reset_user_free_package' ) ) {
	/**
	 *
	 */
	function opalestate_reset_user_free_package( $user_id ) {

		$duration = opalestate_options( 'free_expired_month', 12 );
		update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_id', -1 );
		update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', opalestate_options( 'free_number_listing', 3 ) );
		update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_featured_listings', opalestate_options( 'free_number_featured', 3 ) );

		update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_activation', time() );
		update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_expired', time() + ( $duration * 60 * 60 * 24 * 30 ) );

		return true;
	}
}

if ( ! function_exists( 'opalesate_update_package_number_featured_listings' ) ) {
	/**
	 * Update remaining featured listings
	 */
	function opalesate_update_package_number_featured_listings( $user_id ) {

		$current = get_the_author_meta( OPALESTATE_PACKAGES_USER_PREFIX . 'package_featured_listings', $user_id );

		if ( $current - 1 >= 0 ) {
			update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_featured_listings', $current - 1 );
		} else {
			update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_featured_listings', 0 );
		}
	}
}

if ( ! function_exists( 'opalesate_update_package_number_listings' ) ) {
	/**
	 * Update remaining featured listings
	 */
	function opalesate_update_package_number_listings( $user_id ) {

		$current = get_the_author_meta( OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', $user_id );

		if ( $current - 1 >= 0 ) {
			update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', $current - 1 );
		} else {
			update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', 0 );
		}
	}
}

if ( ! function_exists( 'opalesate_listing_set_to_expire' ) ) {
	/**
	 *
	 */
	function opalesate_listing_set_to_expire( $post_id ) {
		$prop = [
			'ID'          => $post_id,
			'post_type'   => 'opalestate_property',
			'post_status' => 'expired',
		];

		wp_update_post( $prop );
	}
}

if ( ! function_exists( 'opalestate_packages_get_endpoint_url' ) ) {
	function opalestate_packages_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
		if ( ! $permalink ) {
			$permalink = get_permalink();
		}

		if ( get_option( 'permalink_structure' ) ) {
			if ( strstr( $permalink, '?' ) ) {
				$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
				$permalink    = current( explode( '?', $permalink ) );
			} else {
				$query_string = '';
			}
			$url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
		} else {
			$url = esc_url_raw( add_query_arg( $endpoint, $value, $permalink ) );
		}

		return apply_filters( 'opalestate_packages_get_endpoint_url', $url, $endpoint );
	}
}

if ( ! function_exists( 'opalestate_packages_get_checkout_url' ) ) {
	function opalestate_packages_get_checkout_url( $product_id ) {
		$checkout_url = opalestate_packages_get_endpoint_url( 'package-checkout' );

		return esc_url_raw( add_query_arg( 'product_id', $product_id, $checkout_url ) );
	}
}

if ( ! function_exists( 'opalestate_packages_show_membership_warning' ) ) {
	function opalestate_packages_show_membership_warning() {
		echo opalestate_packages_get_template_part( 'account/membership-warning' );
	}
}

if ( ! function_exists( 'opalestate_packages_is_membership_valid' ) ) {
	function opalestate_packages_is_membership_valid( $user_id = null ) {
		return User::is_membership_valid( $user_id );
	}
}

if ( ! function_exists( 'opalestate_packages_get_membership_page_uri' ) ) {
	function opalestate_packages_get_membership_page_uri() {

		global $opalmembership_options;

		$membership_page = isset( $opalmembership_options['membership_page'] ) ? get_permalink( absint( $opalmembership_options['membership_page'] ) ) : get_bloginfo( 'url' );

		return apply_filters( 'opalestate_packages_get_membership_page_uri', $membership_page );

	}
}

if ( ! function_exists( 'opalestate_packages_is_unlimited_purchased' ) ) {
	function opalestate_packages_is_unlimited_purchased() {
		$cart = WC()->cart->get_cart();
		if ( ! $cart ) {
			return;
		}

		$current_user = wp_get_current_user();

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];
			$product    = wc_get_product( $product_id );

			if ( $product->is_type( 'opalestate_package' ) ) {
				$limit = (int) get_post_meta( $product_id, 'opalestate_package_maximum_purchased', true );
				if ( $limit > 0 ) {
					$purchased = Query::get_user_purchased_package( $current_user->ID, $product_id );
					if ( $purchased >= $limit ) {
						return true;
					}
				}

				break;
			}
		}
	}
}

function opalestate_packages_get_current_package_page_uri() {
	$option = opalestate_get_option( 'packages_my_membership_page' );
	$page   = $option ? get_permalink( absint( $option ) ) : get_bloginfo( 'url' );

	return apply_filters( 'opalestate_packages_get_current_package_page_uri', $page );
}

function opalestate_packages_get_history_page_uri() {
	$option = opalestate_get_option( 'packages_my_invoices_page' );
	$page   = $option ? get_permalink( absint( $option ) ) : get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );

	return apply_filters( 'opalestate_packages_get_history_page_uri', $page ? $page : get_bloginfo( 'url' ) );
}

function opalestate_packages_get_packages_page_uri() {
	$option = opalestate_get_option( 'packages_renew_membership_page' );
	$page   = $option ? get_permalink( absint( $option ) ) : get_bloginfo( 'url' );

	return apply_filters( 'opalestate_packages_get_packages_page_uri', $page );
}

function opalestate_packages_get_expired_time_units() {
	return [
		'day'   => esc_html__( 'Day', 'opal-estate-packages' ),
		'week'  => esc_html__( 'Week', 'opal-estate-packages' ),
		'month' => esc_html__( 'Month', 'opal-estate-packages' ),
		'year'  => esc_html__( 'Year', 'opal-estate-packages' ),
	];
}
