<?php
namespace Opal_Estate_Packages\Admin;

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
	 * Add product type selector.
	 *
	 * @param $types
	 * @return mixed
	 */
	public function product_type_selector( $types ) {
		$types['opal_estate_package'] = esc_html__( 'Estate Package', 'opal-estate-packages' );

		return $types;
	}

	/**
	 * Add Opal Packages meta boxes.
	 */
	public function product_data() {
		global $post;
		?>
        <div class="options_group show_if_opal_estate_package">
			<?php
			woocommerce_wp_text_input( [
				'id'                => 'opalestate_package_maximum_purchased',
				'label'             => esc_html__( 'Maximum Purchased', 'opal-estate-packages' ),
				'description'       => esc_html__( 'Set Maximum purchased for each user, Default: -1.', 'opal-estate-packages' ),
				'value'             => max( get_post_meta( $post->ID, 'opalestate_package_maximum_purchased', true ), -1 ),
				'type'              => 'number',
				'desc_tip'          => true,
				'custom_attributes' => [
					'min'  => '-1',
					'step' => '1',
				],
			] );

			woocommerce_wp_checkbox( [
				'id'          => 'opalestate_package_enable_expired',
				'label'       => esc_html__( 'Enable Expired Date', 'opal-estate-packages' ),
				'value'       => get_post_meta( $post->ID, 'opalestate_package_recurring', true ),
				'description' => esc_html__( 'Do you want enable expired date?', 'opal-estate-packages' ),
			] );

			woocommerce_wp_text_input( [
				'id'                => 'opalestate_package_duration',
				'label'             => esc_html__( 'Expired After', 'opal-estate-packages' ),
				'description'       => esc_html__( 'The time that buyer can use this package. Use zero for unlimited time.', 'opal-estate-packages' ),
				'value'             => max( get_post_meta( $post->ID, 'opalestate_package_duration', true ), 0 ),
				'placeholder'       => '',
				'type'              => 'number',
				'desc_tip'          => true,
				'custom_attributes' => [
					'min'  => '0',
					'step' => '1',
				],
			] );

			woocommerce_wp_select( [
				'id'          => 'opalestate_package_duration_unit',
				'label'       => esc_html__( 'Expired Date Type', 'opal-estate-packages' ),
				'description' => esc_html__( 'Enter expired date type. Example Day(s), Week(s), Month(s), Year(s)', 'opal-estate-packages' ),
				'placeholder' => '',
				'desc_tip'    => true,
				'options'     => [
					'day'   => esc_html__( 'Day', 'opal-estate-packages' ),
					'week'  => esc_html__( 'Week', 'opal-estate-packages' ),
					'month' => esc_html__( 'Month', 'opal-estate-packages' ),
					'year'  => esc_html__( 'Year', 'opal-estate-packages' ),
				],
			] );

			woocommerce_wp_checkbox( [
				'id'          => 'opalestate_package_hightlighted',
				'label'       => esc_html__( 'Highlighted', 'opal-estate-packages' ),
				'value'       => get_post_meta( $post->ID, 'opalestate_package_hightlighted', true ),
				'description' => esc_html__( 'Highlighted?', 'opal-estate-packages' ),
			] );

			woocommerce_wp_checkbox( [
				'id'          => 'opalestate_package_recurring',
				'label'       => esc_html__( 'Recurring', 'opal-estate-packages' ),
				'value'       => get_post_meta( $post->ID, 'opalestate_package_recurring', true ),
				'description' => esc_html__( 'Do you want enable recurring?', 'opal-estate-packages' ),
			] );

			$custom_attributes = get_post_meta( $post->ID, 'opalestate_package_package_listings', true ) ? 'disabled' : '';
			woocommerce_wp_text_input( [
				'id'                => 'opalestate_package_package_listings',
				'label'             => esc_html__( 'Number of listings', 'opal-estate-packages' ),
				'description'       => esc_html__( 'The number of listings an user can post with this package. If not set it will be unlimited.', 'opal-estate-packages' ),
				'value'             => max( get_post_meta( $post->ID, 'opalestate_package_package_listings', true ), 0 ),
				'type'              => 'number',
				'desc_tip'          => true,
				'custom_attributes' => [
					'min'              => '0',
					'step'             => '1',
					$custom_attributes => $custom_attributes,
				],
			] );

			woocommerce_wp_checkbox( [
				'id'          => 'opalestate_package_unlimited_listings',
				'label'       => esc_html__( 'Unlimited listing', 'opal-estate-packages' ),
				'value'       => get_post_meta( $post->ID, 'opalestate_package_unlimited_listings', true ),
				'description' => esc_html__( 'Unlimited listing?', 'opal-estate-packages' ),
			] );

			woocommerce_wp_text_input( [
				'id'                => 'opalestate_package_package_featured_listings',
				'label'             => esc_html__( 'Number of Featured listings', 'opal-estate-packages' ),
				'description'       => esc_html__( 'Number of listings can make featured with this package.', 'opal-estate-packages' ),
				'value'             => max( get_post_meta( $post->ID, 'opalestate_package_package_featured_listings', true ), -1 ),
				'placeholder'       => '',
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => [
					'min'  => '-1',
					'step' => '1',
				],
			] );
			?>

            <script type="text/javascript">
                jQuery( '.pricing' ).addClass( 'show_if_opal_estate_package' );
                jQuery( document ).ready( function ( $ ) {
                    $( '#opalestate_package_unlimited_listings' ).change( function () {
                        if ( this.checked ) {
                            $( '#opalestate_package_package_listings' ).prop( 'disabled', true );
                        } else {
                            $( '#opalestate_package_package_listings' ).prop( 'disabled', false );
                        }
                    } );
                } );
            </script>
			<?php do_action( 'opal_estate_package_data' ); ?>
        </div>
		<?php
	}

	public function save_product_data() {

	}
}
