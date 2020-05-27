<?php
namespace Opalestate_Packages\Admin;

class Settings {
	/**
	 * Metabox constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			// add_filter( 'opalestate_settings_submission', [ $this, 'register_settings_submission' ] );
			add_filter( 'opalestate_settings_tabs', [ $this, 'register_admin_setting_tab' ], 1 );
			add_filter( 'opalestate_registered_packages_settings', [ $this, 'register_admin_settings' ], 10, 1 );
		}
	}

	public function register_settings_submission( $fields ) {
		$tmp = [
			[
				'name'       => esc_html__( 'Free Submission', 'opal-estate-packages' ),
				'id'         => 'opalestate_title_free_submission_settings',
				'type'       => 'title',
				'before_row' => '<hr>',
				'after_row'  => '<hr>',
			],
			[
				'name'    => esc_html__( 'Enable Free Submission', 'opal-estate-packages' ),
				'desc'    => esc_html__( 'Allow set automatic a free package.', 'opal-estate-packages' ),
				'id'      => 'enabel_free_submission',
				'type'    => 'switch',
				'options' => [
					1 => esc_html__( 'Yes', 'opal-estate-packages' ),
					0 => esc_html__( 'No', 'opal-estate-packages' ),
				],
			],
			[
				'name'       => esc_html__( 'Number Free Listing', 'opal-estate-packages' ),
				'desc'       => esc_html__( 'Maximum number of Free Listing that users can submit.', 'opal-estate-packages' ),
				'id'         => 'free_number_listing',
				'type'       => 'text_small',
				'attributes' => [
					'type' => 'number',
				],
				'default'    => 3,
			],
			[
				'name'       => esc_html__( 'Number Free Featured', 'opal-estate-packages' ),
				'desc'       => esc_html__( 'Maximum number of Free Featured that users can set.', 'opal-estate-packages' ),
				'id'         => 'free_number_featured',
				'type'       => 'text_small',
				'attributes' => [
					'type' => 'number',
				],
				'default'    => 3,
			],
		];

		return array_merge( $fields, $tmp );
	}

	/**
	 * Register admin setting tab.
	 *
	 * @param $tabs
	 * @return mixed
	 */
	public function register_admin_setting_tab( $tabs ) {
		$tabs['packages'] = esc_html__( 'Packages', 'opal-estate-packages' );

		return $tabs;
	}

	/**
	 * Register admin settings.
	 *
	 * @param $fields
	 * @return array
	 */
	public function register_admin_settings( $fields ) {
		$fields = [
			'id'      => 'options_page_packages',
			'title'   => esc_html__( 'Packages Settings', 'opal-estate-packages' ),
			'show_on' => [ 'key' => 'options-page', 'value' => [ 'opalestate_settings' ], ],
			'fields'  => apply_filters( 'opalestate_settings_packages', [
					[
						'name' => esc_html__( 'Packages Settings', 'opal-estate-packages' ),
						'desc' => '<hr>',
						'id'   => 'opalestate_title_packages_settings',
						'type' => 'title',
					],
					[
						'name'    => esc_html__( 'My Membership page', 'opal-estate-packages' ),
						'desc'    => esc_html__( 'This is the submission page. The <code>[opalestate_packages_user_current_package]</code> shortcode should be on this page.', 'opal-estate-packages' ),
						'id'      => 'packages_my_membership_page',
						'type'    => 'select',
						'options' => opalestate_cmb2_get_post_options( [
							'post_type'   => 'page',
							'numberposts' => -1,
						] ),
					],
					[
						'name'    => esc_html__( 'My Invoices page', 'opal-estate-packages' ),
						'desc'    => esc_html__( 'This is the submission page. The <code>[woocommerce_my_account]</code> shortcode should be on this page.', 'opal-estate-packages' ),
						'id'      => 'packages_my_invoices_page',
						'type'    => 'select',
						'options' => opalestate_cmb2_get_post_options( [
							'post_type'   => 'page',
							'numberposts' => -1,
						] ),
					],
					[
						'name'    => esc_html__( 'Renew Membership page', 'opal-estate-packages' ),
						'desc'    => esc_html__( 'This is the submission page. The <code>[opalestate_packages_collection]</code> shortcode should be on this page.', 'opal-estate-packages' ),
						'id'      => 'packages_renew_membership_page',
						'type'    => 'select',
						'options' => opalestate_cmb2_get_post_options( [
							'post_type'   => 'page',
							'numberposts' => -1,
						] ),
					],
				]
			),
		];

		return $fields;
	}
}
