<?php
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

			if ( ! empty( $value ) || ! in_array( $value, [ 'day', 'week', 'month', 'year' ] ) ) {
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

			if ( $value ) {
				return true;
			}

			return false;
		}

		public function get_package_featured_listings() {
			$value = get_post_meta( $this->get_id(), 'opalestate_package_package_featured_listings', true );

			return ! empty( $value ) ? absint( $value ) : 0;
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
			$text = $this->is_purchasable() && $this->is_in_stock() ? esc_html__( 'Select', 'opal-packages' ) : esc_html__( 'Read More', 'opal-packages' );

			return apply_filters( 'opalestate_packages_product_add_to_cart_text', $text, $this );
		}
	}
}

add_action( 'init', 'opalestate_packages_add_product_type' );


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
		$unlimited_listings = ! empty( $unlimited_listings ) && $unlimited_listings == 'on' ? 0 : 1;

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
