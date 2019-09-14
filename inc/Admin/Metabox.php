<?php
namespace Opal_Packages\Admin;

class Metabox {
	/**
	 * Metabox constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_filter( 'product_type_selector', [ $this, 'product_type_selector' ] );
			add_action( 'woocommerce_product_options_general_product_data', [ $this, 'product_data', ] );
			add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_data' ] );
		}
	}

	/**
	 * @param $types
	 * @return mixed
	 */
	public function product_type_selector( $types ) {
		$types['opal_package'] = esc_html__( 'Opal Package', 'opal-packages' );

		return $types;
	}

	/**
	 * Add Opal Packages meta boxes.
	 */
	public function product_data() {
		global $post;
		?>
        <div class="options_group show_if_opal_package">
			<?php
			woocommerce_wp_text_input( [
				'id'                => '_opal_package_maximum_purchased',
				'label'             => esc_html__( 'Maximum Purchased', 'opal-packages' ),
				'description'       => esc_html__( 'Set Maximum purchased for each user, Default: -1.', 'opal-packages' ),
				'value'             => max( get_post_meta( $post->ID, '_opal_package_maximum_purchased', true ), -1 ),
				'type'              => 'number',
				'desc_tip'          => true,
				'custom_attributes' => [
					'min'  => '-1',
					'step' => '1',
				],
			] );

			woocommerce_wp_text_input( [
				'id'                => '_opal_package_duration',
				'label'             => esc_html__( 'Expired After', 'opal-packages' ),
				'description'       => esc_html__( 'The time that buyer can use this package. Use zero for unlimited time.', 'opal-packages' ),
				'value'             => max( get_post_meta( $post->ID, '_opal_package_duration', true ), 0 ),
				'placeholder'       => '',
				'type'              => 'number',
				'desc_tip'          => true,
				'custom_attributes' => [
					'min'  => '0',
					'step' => '1',
				],
			] );

			woocommerce_wp_select( [
				'id'          => '_opal_package_duration_unit',
				'label'       => esc_html__( 'Expired Date Type', 'opal-packages' ),
				'description' => esc_html__( 'Enter expired date type. Example Day(s), Week(s), Month(s), Year(s)', 'opal-packages' ),
				'placeholder' => '',
				'desc_tip'    => true,
				'options'     => [
					'day'   => esc_html__( 'Day', 'opal-packages' ),
					'week'  => esc_html__( 'Week', 'opal-packages' ),
					'month' => esc_html__( 'Month', 'opal-packages' ),
					'year'  => esc_html__( 'Year', 'opal-packages' ),
				],
			] );

			$custom_attributes = get_post_meta( $post->ID, '_opal_package_unlimited_listings', true ) ? 'disabled' : '';
			woocommerce_wp_text_input( [
				'id'                => '_opal_package_listings',
				'label'             => esc_html__( 'Number of listings', 'opal-packages' ),
				'description'       => esc_html__( 'The number of listings an user can post with this package. If not set it will be unlimited.', 'opal-packages' ),
				'value'             => max( get_post_meta( $post->ID, '_opal_package_listings', true ), 0 ),
				'placeholder'       => esc_html__( 'No job posting', 'opal-packages' ),
				'type'              => 'number',
				'desc_tip'          => true,
				'custom_attributes' => [
					'min'              => '0',
					'step'             => '1',
					$custom_attributes => $custom_attributes,
				],
			] );

			woocommerce_wp_checkbox( [
				'id'          => '_opal_package_unlimited_listings',
				'label'       => '',
				'value'       => get_post_meta( $post->ID, '_opal_package_unlimited_listings', true ),
				'description' => esc_html__( 'Unlimited listing?', 'opal-packages' ),
			] );

			woocommerce_wp_text_input( [
				'id'                => '_opal_package_featured_listings',
				'label'             => esc_html__( 'Number Of Featured listings', 'opal-packages' ),
				'description'       => esc_html__( 'Number of listings can make featured with this package.', 'opal-packages' ),
				'value'             => max( get_post_meta( $post->ID, '_opal_package_featured_listings', true ), -1 ),
				'placeholder'       => '',
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => [
					'min'  => '0',
					'step' => '1',
				],
			] );
			?>

            <script type="text/javascript">
                jQuery( '.pricing' ).addClass( 'show_if_opal_package' );
                jQuery( document ).ready( function ( $ ) {
                    $( '#_opal_package_unlimited_listings' ).change( function () {
                        if ( this.checked ) {
                            $( '#_opal_package_listings' ).prop( 'disabled', true );
                        } else {
                            $( '#_opal_package_listings' ).prop( 'disabled', false );
                        }
                    } );
                } );
            </script>
			<?php
			do_action( 'opal_job_package_data' )
			?>
        </div>
		<?php
	}
}
