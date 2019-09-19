<?php
namespace Opalestate_Packages\Admin;

class Settings {
	/**
	 * Metabox constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_filter( 'opalestate_settings_submission', [ $this, 'register_settings_submission' ] );
		}
	}

	public function register_settings_submission( $fields ) {
		$tmp = [
			[
				'name'       => esc_html__( 'Free Submission', 'opalestate-packages' ),
				'id'         => 'opalestate_title_free_submission_settings',
				'type'       => 'title',
				'before_row' => '<hr>',
				'after_row'  => '<hr>',
			],
			[
				'name'    => esc_html__( 'Enable Free Submission', 'opalestate-packages' ),
				'desc'    => esc_html__( 'Allow set automatic a free package.', 'opalestate-packages' ),
				'id'      => 'enabel_free_submission',
				'type'    => 'switch',
				'options' => [
					1 => esc_html__( 'Yes', 'opalestate-packages' ),
					0 => esc_html__( 'No', 'opalestate-packages' ),
				],
			],
			[
				'name'       => esc_html__( 'Number Free Listing', 'opalestate-packages' ),
				'desc'       => esc_html__( 'Maximum number of Free Listing that users can submit.', 'opalestate-packages' ),
				'id'         => 'free_number_listing',
				'type'       => 'text_small',
				'attributes' => [
					'type' => 'number',
				],
				'default'    => 3,
			],
			[
				'name'       => esc_html__( 'Number Free Featured', 'opalestate-packages' ),
				'desc'       => esc_html__( 'Maximum number of Free Featured that users can set.', 'opalestate-packages' ),
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
}
