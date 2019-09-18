<?php
/**
 * $Desc$
 *
 * @version    $Id$
 * @package    opalmembership
 * @author     Opal  Team <info@wpopal.com >
 * @copyright  Copyright (C) 2016 wpopal.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @website  http://www.wpopal.com
 * @support  http://www.wpopal.com/support/forum.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Shortcodes {

	/**
	 * Default templates
	 *
	 * @access public
	 * @param $template
	 * @return string
	 * @throws Exception
	 */
	public static function init(){

		$shortcodes = array(
			'checkout',
			'checkout_failed',
			'receipt',
			'history',
			'pricing_table',
			'register_form',
			'login_form',
			'dashboard',
			'packages',
			'user_current_package'
		);

		foreach( $shortcodes as $shortcode ){
			add_shortcode( 'opalmembership_' . trim($shortcode),  array( __CLASS__, trim( $shortcode ) ) );
		}
	}

	/**
	 * checkout shortcode to display checkout form
	 */
	public static function checkout(){  
		return OpalMembership()->checkout()->checkout_form();
	}

	/**
	 * thank you page
	 */
	public static function receipt(){
		return OpalMembership()->checkout()->thankyou();
	}

	/* failed checkout */
	public static function checkout_failed() {
		return OpalMembership()->checkout()->checkout_failed();
	}

	/* membership pricing table */
	public static function pricing_table( $atts = array(), $content = '' ) {

		$atts = shortcode_atts( array(
 				'featured_item'	=> 1,
 				'button_text'	=> esc_html__( 'Sign Up', 'opalmembership' )
			), $atts );

		$args = array(
				'post_type'		=> 'membership_packages'
			);

		if ( isset( $atts['package_id'] ) && $atts['package_id'] ) {
			$args['post__in'] = array_map( 'absint', explode( ',', $atts['package_id'] ) );
		}

		if ( isset( $atts['order'] ) && in_array( trim( $atts['order'] ), array( 'desc', 'asc' ) ) ) {
			$args['order']		= strtoupper( $atts['order'] );
			$args['orderby']	= 'meta_value_num';
			$args['meta_key']	= OPALMEMBERSHIP_PACKAGES_PREFIX . 'price';
		}

		return Opalmembership_Template_Loader::get_template_part( 'account/pricing-table', array( 'query' => new WP_Query( $args ), 'atts' => $atts ) );
		wp_reset_postdata();
	}

	/* register form show up */
	public static function register_form( $atts = array() ) {
		$atts = shortcode_atts( array(
 				'message' 	=> '',
 				'redirect'	=> '',
 				'hide_title'	=> false
			), $atts );
		return Opalmembership_Template_Loader::get_template_part( 'account/register-form', $atts );
	}

	/* sign in show up */
	public static function login_form( $atts = array() ) {
		$atts = shortcode_atts( array(
 				'message' 	=> '',
 				'redirect'	=> '',
 				'hide_title'	=> false
			), $atts );
		return Opalmembership_Template_Loader::get_template_part( 'account/login-form', $atts );
	}

	/**
	 *
	 */
	public static function packages( $atts ){
		$atts = $atts ? $atts : array();
		$paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');

		$args = array();
		$loop = Opalmembership_Query::get_packages();


		$atts['loop'] = $loop; 	
		$atts['column'] = isset($atts['column']) ? $atts['column'] : apply_filters( 'opalmembership_package_grid_column', 4 );

		return Opalmembership_Template_Loader::get_template_part( 'shortcodes/packages', $atts );
	}

	/* dashboard shortcode */
	public static function dashboard( $atts = array() ) {
		return Opalmembership_Template_Loader::get_template_part( 'account/dashboard', $atts );
	}

	public static function history( $atts = array() ){

		global $current_user;
		
		if( !is_array($atts) ){
			$atts = array();
		}

		if( isset($_GET['payment_id']) && $_GET['payment_id'] ){

		 	$payment = opalmembership_payment( sanitize_text_field( $_GET['payment_id'] ) );
			if( $payment->user_id == $current_user->ID ){
				$atts['payment'] = $payment;
			}else {
				$atts['payment'] = array();
			}

			return Opalmembership_Template_Loader::get_template_part( 'account/payment-detail', $atts );
		}else {

			$paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');

			$loop = Opalmembership_Query::get_payments_by_user( $current_user->ID, 10, $paged );
 
			$atts['loop'] = $loop;

			return Opalmembership_Template_Loader::get_template_part( 'account/history', $atts );
		}
	}

	public static function user_current_package( $atts ){
		return Opalmembership_Template_Loader::get_template_part( 'account/current-package', $atts );
	}
}
