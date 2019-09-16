<?php
namespace Opalestate_Packages\Admin;

class Metabox {
	/**
	 * Metabox constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_filter( 'product_type_selector', [ $this, 'product_type_selector' ] );
			add_action( 'woocommerce_product_options_general_product_data', [ $this, 'product_data', ] );
			add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_data' ] );
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'product_data_tabs' ) );
		}

		add_action( 'after_switch_theme', array( $this, 'switch_theme_hook' ) );
	}

	/**
	 * Add product type selector.
	 *
	 * @param $types
	 * @return mixed
	 */
	public function product_type_selector( $types ) {
		$types['opalestate_package'] = esc_html__( 'Estate Package', 'opalestate-packages' );

		return $types;
	}

	/**
	 * Add Opal Packages meta boxes.
	 */
	public function product_data() {
		global $post;
		?>
        <div class="options_group show_if_opalestate_package">
			<?php
			woocommerce_wp_text_input( [
				'id'                => 'opalestate_package_maximum_purchased',
				'label'             => esc_html__( 'Maximum Purchased', 'opalestate-packages' ),
				'description'       => esc_html__( 'Set Maximum purchased for each user, Default: -1.', 'opalestate-packages' ),
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
				'label'       => esc_html__( 'Enable Expired Date', 'opalestate-packages' ),
				'value'       => get_post_meta( $post->ID, 'opalestate_package_recurring', true ),
				'description' => esc_html__( 'Do you want enable expired date?', 'opalestate-packages' ),
			] );

			woocommerce_wp_text_input( [
				'id'                => 'opalestate_package_duration',
				'label'             => esc_html__( 'Expired After', 'opalestate-packages' ),
				'description'       => esc_html__( 'The time that buyer can use this package. Use zero for unlimited time.', 'opalestate-packages' ),
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
				'label'       => esc_html__( 'Expired Date Type', 'opalestate-packages' ),
				'description' => esc_html__( 'Enter expired date type. Example Day(s), Week(s), Month(s), Year(s)', 'opalestate-packages' ),
				'placeholder' => '',
				'desc_tip'    => true,
				'options'     => [
					'day'   => esc_html__( 'Day', 'opalestate-packages' ),
					'week'  => esc_html__( 'Week', 'opalestate-packages' ),
					'month' => esc_html__( 'Month', 'opalestate-packages' ),
					'year'  => esc_html__( 'Year', 'opalestate-packages' ),
				],
			] );

			woocommerce_wp_checkbox( [
				'id'          => 'opalestate_package_hightlighted',
				'label'       => esc_html__( 'Highlighted', 'opalestate-packages' ),
				'value'       => get_post_meta( $post->ID, 'opalestate_package_hightlighted', true ),
				'description' => esc_html__( 'Highlighted?', 'opalestate-packages' ),
			] );

			woocommerce_wp_checkbox( [
				'id'          => 'opalestate_package_recurring',
				'label'       => esc_html__( 'Recurring', 'opalestate-packages' ),
				'value'       => get_post_meta( $post->ID, 'opalestate_package_recurring', true ),
				'description' => esc_html__( 'Do you want enable recurring?', 'opalestate-packages' ),
			] );

			$custom_attributes = get_post_meta( $post->ID, 'opalestate_package_package_listings', true ) ? 'disabled' : '';
			woocommerce_wp_text_input( [
				'id'                => 'opalestate_package_package_listings',
				'label'             => esc_html__( 'Number of listings', 'opalestate-packages' ),
				'description'       => esc_html__( 'The number of listings an user can post with this package. If not set it will be unlimited.', 'opalestate-packages' ),
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
				'label'       => esc_html__( 'Unlimited listing', 'opalestate-packages' ),
				'value'       => get_post_meta( $post->ID, 'opalestate_package_unlimited_listings', true ),
				'description' => esc_html__( 'Unlimited listing?', 'opalestate-packages' ),
			] );

			woocommerce_wp_text_input( [
				'id'                => 'opalestate_package_package_featured_listings',
				'label'             => esc_html__( 'Number of Featured listings', 'opalestate-packages' ),
				'description'       => esc_html__( 'Number of listings can make featured with this package.', 'opalestate-packages' ),
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
                jQuery( '.pricing' ).addClass( 'show_if_opalestate_package' );
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
			<?php do_action( 'opalestate_package_data' ); ?>
        </div>
		<?php
	}

	public function save_product_data( $post_id ) {
		$fields = [
			'opalestate_package_maximum_purchased'         => '',
			'opalestate_package_enable_expired'            => '',
			'opalestate_package_duration'                  => 'int',
			'opalestate_package_duration_unit'             => '',
			'opalestate_package_hightlighted'              => '',
			'opalestate_package_recurring'                 => '',
			'opalestate_package_package_listings'          => '',
			'opalestate_package_unlimited_listings'        => '',
			'opalestate_package_package_featured_listings' => '',
		];

		$fields = apply_filters( 'opalestate_package_fields_data', $fields );
		foreach ( $fields as $key => $type ) {
			$value = isset( $_POST[ $key ] ) ? $_POST[ $key ] : '';
			switch ( $type ) {
				case 'int' :
					$value = absint( $value );
					break;
				case 'float' :
					$value = floatval( $value );
					break;
				default :
					$value = sanitize_text_field( $value );
			}
			update_post_meta( $post_id, $key, $value );
		}

		do_action( 'opalestate_package_save_data', $post_id );
	}

	public function product_data_tabs( $product_data_tabs = [] ) {
		if ( empty( $product_data_tabs ) ) {
			return;
		}

		if ( isset( $product_data_tabs['shipping'] ) && isset( $product_data_tabs['shipping']['class'] ) ) {
			$product_data_tabs['shipping']['class'][] = 'hide_if_opalestate_package';
		}
		if ( isset( $product_data_tabs['linked_product'] ) && isset( $product_data_tabs['linked_product']['class'] ) ) {
			$product_data_tabs['linked_product']['class'][] = 'hide_if_opalestate_package';
		}
		if ( isset( $product_data_tabs['attribute'] ) && isset( $product_data_tabs['attribute']['class'] ) ) {
			$product_data_tabs['attribute']['class'][] = 'hide_if_opalestate_package';
		}

		return $product_data_tabs;
	}

	public function switch_theme_hook( $newname = '', $newtheme = '' ) {
		if ( defined( 'WOOCOMMERCE_VERSION' ) ) {
			if ( ! get_term_by( 'slug', sanitize_title( 'opalestate_package' ), 'product_type' ) ) {
				wp_insert_term( 'opalestate_package', 'product_type' );
			}
		}
	}
}
