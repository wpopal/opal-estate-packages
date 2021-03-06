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
			add_filter( 'woocommerce_product_data_tabs', [ $this, 'product_data_tabs' ] );
		}

		add_action( 'after_switch_theme', [ $this, 'switch_theme_hook' ] );

		add_action( 'cmb2_admin_init', [ $this, 'register_user_package_metabox' ] );
	}

	/**
	 * Add product type selector.
	 *
	 * @param $types
	 * @return mixed
	 */
	public function product_type_selector( $types ) {
		$types['opalestate_package'] = esc_html__( 'Estate Package', 'opal-estate-packages' );

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
				'value'       => get_post_meta( $post->ID, 'opalestate_package_enable_expired', true ),
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
				'options'     => opalestate_packages_get_expired_time_units(),
			] );

			woocommerce_wp_checkbox( [
				'id'          => 'opalestate_package_hightlighted',
				'label'       => esc_html__( 'Highlighted', 'opal-estate-packages' ),
				'value'       => get_post_meta( $post->ID, 'opalestate_package_hightlighted', true ),
				'description' => esc_html__( 'Highlighted?', 'opal-estate-packages' ),
			] );

			// woocommerce_wp_checkbox( [
			// 	'id'          => 'opalestate_package_recurring',
			// 	'label'       => esc_html__( 'Recurring', 'opal-estate-packages' ),
			// 	'value'       => get_post_meta( $post->ID, 'opalestate_package_recurring', true ),
			// 	'description' => esc_html__( 'Do you want enable recurring?', 'opal-estate-packages' ),
			// ] );

			woocommerce_wp_checkbox( [
				'id'          => 'opalestate_package_unlimited_listings',
				'label'       => esc_html__( 'Limited listing?', 'opal-estate-packages' ),
				'value'       => get_post_meta( $post->ID, 'opalestate_package_unlimited_listings', true ),
				'description' => esc_html__( 'Check if set limited listings. Default: Unlimited listings. Notice: Enter Number Of Properties when set limited listings.', 'opal-estate-packages' ),
			] );

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
				],
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
                jQuery( '.pricing' ).addClass( 'show_if_opalestate_package' );
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

	/**
	 * Hook in and add a metabox to add fields to the user profile pages
	 */
	public function register_user_package_metabox() {
		if ( ! defined( 'OPALESTATE_PACKAGES_USER_PREFIX' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$prefix = OPALESTATE_PACKAGES_USER_PREFIX;
		$fields = [];

		foreach ( $fields as $field ) {
			$cmb_user->add_field( $field );
		}
		$fields = [];
		$date   = null;

		$current_user = wp_get_current_user();

		if ( ( isset( $_GET['user_id'] ) && $_GET['user_id'] ) ) {
			$user_id = (int) $_GET['user_id'];
		} else {
			$user_id = get_current_user_id();
		}

		$date = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_expired', true );

		/**
		 * Metabox for the user profile screen
		 */
		$cmb_user = new_cmb2_box( [
			'id'               => $prefix . 'package',
			'title'            => esc_html__( 'Membership Package', 'opal-estate-packages' ), // Doesn't output for user boxes
			'object_types'     => [ 'user' ], // Tells CMB2 to use user_meta vs post_meta
			'show_names'       => true,
			'new_user_section' => 'add-new-user', // where form will show on new user page. 'add-existing-user' is only other valid option.
		] );

		$fields[] = [
			'name'        => esc_html__( 'Package', 'opal-estate-packages' ),
			'id'          => $prefix . 'package_id',
			'type'        => 'text',
			'attributes'  => [
				'type'    => 'number',
				'pattern' => '\d*',
				'min'     => 0,
			],
			'std'         => '1',
			'description' => esc_html__( 'Set package ID with -1 as free package.', 'opal-estate-packages' ),
			'before_row'  => '<hr><h3> ' . __( 'Membership Information', 'opal-estate-packages' ) . ' </h3>',
		];


		$fields[] = [
			'name'        => esc_html__( 'Number Of Properties', 'opal-estate-packages' ),
			'id'          => $prefix . 'package_listings',
			'type'        => 'text',
			'attributes'  => [
				'type'    => 'number',
				'pattern' => '\d*',
				'min'     => 0,
			],
			'std'         => '1',
			'description' => esc_html__( 'Number of properties with this package. If not set it will be unlimited.', 'opal-estate-packages' ),
		];

		$fields[] = [
			'name'        => esc_html__( 'Number Of Featured Properties', 'opal-estate-packages' ),
			'id'          => $prefix . 'package_featured_listings',
			'type'        => 'text',
			'attributes'  => [
				'type'    => 'number',
				'pattern' => '\d*',
				'min'     => 0,
			],
			'std'         => '1',
			'description' => esc_html__( 'Number of properties can make featured with this package.', 'opal-estate-packages' ),
		];

		$fields[] = [
			'name'        => esc_html__( 'Expired', 'opal-estate-packages' ),
			'id'          => $prefix . 'package_expired_date',
			'type'        => 'text_date',
			'default'     => $date,
			'std'         => '1',
			'description' => esc_html__( 'Show expired time in double format.', 'opal-estate-packages' ),
		];

		$fields[] = [
			'name'        => esc_html__( 'Expired', 'opal-estate-packages' ),
			'id'          => $prefix . 'package_expired',
			'type'        => 'text',
			'std'         => '1',
			'description' => esc_html__( 'Show expired time in double format.', 'opal-estate-packages' ),
		];

		foreach ( $fields as $field ) {
			$cmb_user->add_field( $field );
		}
	}
}
