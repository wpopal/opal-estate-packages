<?php
namespace Opalestate_Packages\Core;

class Shortcodes {
	/**
	 * Shortcodes constructor.
	 */
	public function __construct() {
		$shortcodes = [
			'collection',
		];

		foreach ( $shortcodes as $shortcode ) {
			add_shortcode( 'opalestate_packages_' . trim( $shortcode ), [ $this, trim( $shortcode ) ] );
		}
	}

	/**
	 * @param $atts
	 * @return void
	 */
	public function collection( $atts ) {
		$atts = shortcode_atts( [
			'product_cat' => '',
			'column'      => 3,
			'loop'        => '',
		], $atts );

		return opalestate_packages_get_template_part( 'packages', $atts );
	}
}
