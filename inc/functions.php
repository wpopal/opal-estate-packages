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
