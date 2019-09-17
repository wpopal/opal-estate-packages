<?php
/**
 * Register product type.
 */
function opalestate_packages_add_product_type() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

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
