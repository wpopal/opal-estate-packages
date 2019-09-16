<?php
namespace Opalestate_Packages\Core;

class Handler {
	/**
	 * Handler constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_add_to_cart_handler_opalestate_package', [ $this, 'woocommerce_add_to_cart_handler', ], 100 );
		add_action( 'woocommerce_order_status_completed', [ $this, 'woocommerce_order_status_completed' ] );
		// add_action( 'woocommerce_order_status_changed', [ $this, 'order_changed' ], 10, 3 );
		// add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'checkout_fields_job_meta' ] );
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

	public function woocommerce_order_status_completed( $order_id ) {
		$order = new \WC_Order( $order_id );
		if ( get_post_meta( $order_id, 'opalestate_package_processed', true ) ) {
			return;
		}

		foreach ( $order->get_items() as $item ) {
			$product = wc_get_product( $item[ 'product_id' ] );

			if ( $product->is_type( 'opalestate_package' ) && $order->get_customer_id() ) {
				$user_id = $order->get_customer_id();
				$package_interval      = absint( $product->get_package_interval() );
				$package_interval_unit = $product->get_package_interval_unit();
				$package_data          = array(
					'product_id'            => $product->get_id(),
					'order_id'              => $order_id,
					'created'               => current_time( 'mysql' ),
					'package_interval'      => $package_interval,
					'package_interval_unit' => $package_interval_unit,
					'job_limit'             => absint( $product->get_post_job_limit() ),
					'job_featured'          => absint( $product->get_job_feature_limit() ),
				);

				$package_data = apply_filters( 'jm_opalestate_package_user_data', $package_data, $product );

				if ( ! self::is_purchased_free_package( $user_id ) || $product->get_price() > 0 ) {
					if ( $product->get_company_featured() ) {
						$company_id = jm_get_employer_company( $user_id );
						if ( $company_id ) {
							update_post_meta( $company_id, '_company_featured', 'yes' );
						}
					}
					if ( ! empty( $package_interval ) ) {
						$expired                   = strtotime( "+{$package_interval} {$package_interval_unit}" );
						$package_data[ 'expired' ] = $expired;
						Noo_Job_Package::set_expired_package_schedule( $user_id, $package_data );
					}

					update_user_meta( $user_id, '_opalestate_package', $package_data );
					update_user_meta( $user_id, '_job_added', '0' );
					update_user_meta( $user_id, '_job_featured', '0' );
					update_user_meta( $user_id, '_job_refresh', '0' );
					update_user_meta( $user_id, '_download_cv_count','0');


					$job_id = noo_get_post_meta( $order_id, '_job_id', '' );
					if ( ! empty( $job_id ) && is_numeric( $job_id ) ) {
						$job = get_post( $job_id );
						if ( $job->post_type == 'noo_job' ) {
							jm_increase_job_posting_count( $user_id );
							$job_need_approve = jm_get_job_setting( 'job_approve', 'yes' ) == 'yes';
							if ( ! $job_need_approve ) {
								wp_update_post( array(
									'ID'            => $job_id,
									'post_status'   => 'publish',
									'post_date'     => current_time( 'mysql' ),
									'post_date_gmt' => current_time( 'mysql', 1 ),
								) );
								jm_set_job_expired( $job_id );
							} else {
								wp_update_post( array(
									'ID'          => $job_id,
									'post_status' => 'pending',
								) );
								update_post_meta( $job_id, '_in_review', 1 );
							}

							Noo_Job::send_notification( $job_id, $user_id );
						}
					}

					if ( $product->is_unlimited_job_posting() ) {
						// TODO: add something for the unlimited package.
					}

					do_action( 'jm_job_package_order_completed', $product, $user_id );
				}

				break;
			}
		}

		update_post_meta( $order_id, 'opalestate_package_processed', true );
	}

	public function order_changed( $order_id, $old_status, $new_status ) {
		if ( get_post_meta( $order_id, 'job_package_processed', true ) ) {

			// Check if order is changing from completed to not completed
			if ( $old_status == 'completed' && $new_status != 'completed' ) {
				$order = new WC_Order( $order_id );
				foreach ( $order->get_items() as $item ) {
					$product = wc_get_product( $item[ 'product_id' ] );

					// Check if there's job package in this order
					if ( $product->is_type( 'job_package' ) && $order->get_customer_id() ) {
						$user_id = $order->get_customer_id();

						$user_package = jm_get_job_posting_info( $user_id );

						// Check if user is currently active with this order
						if ( ! empty( $user_package ) && isset( $user_package[ 'order_id' ] ) && absint( $order_id ) == absint( $user_package[ 'order_id' ] ) ) {

							self::reset_job_package( $user_id );

							// Reset the processed status so that it can update if the order is reseted.
							update_post_meta( $order_id, 'job_package_processed', false );
						}

						break;
					}
				}
			}
		}
	}

	public function checkout_fields_job_meta( $order_id ) {
		global $woocommerce;

		/* -------------------------------------------------------
		 * Create order create fields _job_id for storing job that need to activate
		 * ------------------------------------------------------- */
		foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item[ '_job_id' ] ) && is_numeric( $cart_item[ '_job_id' ] ) ) :

				update_post_meta( $order_id, '_job_id', sanitize_text_field( $cart_item[ '_job_id' ] ) );

			endif;
		}
	}
}
