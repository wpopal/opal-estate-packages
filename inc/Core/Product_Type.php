<?php
namespace Opal_Packages\Core;

class Product_Type extends \WC_Product {
	/**
	 * Get the product if ID is passed, otherwise the product is new and empty.
	 * This class should NOT be instantiated, but the wc_get_product() function
	 * should be used. It is possible, but the wc_get_product() is preferred.
	 *
	 * @param int|\WC_Product|object $product Product to init.
	 */
	public function __construct( $product ) {
		$this->product_type = 'opal_package';
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

	public function is_downloadable() {
		return true;
	}

	public function has_file( $download_id = '' ) {
		return false;
	}

	public function is_unlimited_opal_posting() {
		return (bool) get_post_meta( $this->id, '_opal_posting_unlimit', true );
	}

	public function get_post_opal_limit() {

		if ( $this->is_unlimited_opal_posting() ) {
			return 99999999;
		}

		$value = get_post_meta( $this->id, '_opal_posting_limit', true );
		if ( ! empty( $value ) ) {
			return absint( $value );
		}

		return 0;
	}

	public function get_opal_feature_limit() {

		$value = get_post_meta( $this->id, '_opal_feature_limit', true );
		if ( ! empty( $value ) ) {
			if ( $value == -1 ) {
				return 99999999;
			}

			return $value;
		}

		return 0;
	}

	public function get_opal_refresh_limit() {

		$value = get_post_meta( $this->id, '_opal_refresh_limit', true );
		if ( ! empty( $value ) ) {
			if ( $value == -1 ) {
				return 99999999;
			}

			return $value;
		}

		return 0;
	}

	public function get_can_view_resume() {
		return get_post_meta( $this->id, '_can_view_resume', true );
	}

	public function get_resume_view_limit() {
		return get_post_meta( $this->id, '_resume_view_limit', true );
	}

	public function get_download_resume_limit() {
		$value = get_post_meta( $this->id, '_download_resume_limit', true );
		if ( ! empty( $value ) ) {
			if ( $value == -1 ) {
				return 99999999;
			}

			return $value;
		}

		return 0;
	}

	public function get_opal_display_duration() {

		$value = get_post_meta( $this->id, '_opal_display_duration', true );
		if ( ! empty( $value ) ) {
			return $value;
		}

		return 1;
	}

	public function get_package_interval() {

		$value = get_post_meta( $this->id, '_opal_package_interval', true );
		if ( ! empty( $value ) ) {
			return $value;
		}

		return '';
	}

	public function get_package_interval_unit() {
		$value = get_post_meta( $this->id, '_opal_package_interval_unit', true );
		if ( ! empty( $value ) ) {
			return $value;
		}

		return 'day';
	}

	public function get_company_featured() {

		$value = get_post_meta( $this->id, '_company_featured', true );
		if ( ! empty( $value ) ) {
			return $value;
		}

		return false;
	}

	public function get_can_view_candidate_contact() {

		$value = get_post_meta( $this->id, '_can_view_candidate_contact', true );
		if ( ! empty( $value ) ) {
			return $value;
		}

		return false;
	}

	public function get_view_candidate_contact_limit() {
		return get_post_meta( $this->id, '_view_candidate_contact_limit', true );
	}

	public function add_to_cart_url() {
		$url = $this->is_in_stock() ? esc_url( remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id, home_url() ) ) ) : get_permalink( $this->id );

		return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
	}

	public function add_to_cart_text() {
		$text = $this->is_purchasable() && $this->is_in_stock() ? __( 'Select', 'opal-packages' ) : __( 'Read More', 'opal-packages' );

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}
}
