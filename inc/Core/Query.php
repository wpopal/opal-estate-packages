<?php
namespace Opalestate_Packages\Core;

class Query {
	public function __construct() {
		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ], 100 );
		}
	}

	public function pre_get_posts( $q ) {
		if ( ! defined( 'WOOCOMMERCE_VERSION' ) ) {
			return;
		}

		if ( $this->is_woo_product_query( $q ) ) {
			$tax_query = [
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => [ 'opalestate_package' ],
				'operator' => 'NOT IN',
			];
			$q->tax_query->queries[]    = $tax_query;
			$q->query_vars['tax_query'] = $q->tax_query->queries;
		}
	}

	protected function is_woo_product_query( $query = null ) {
		if ( empty( $query ) ) {
			return false;
		}

		if ( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] === 'product' ) {
			return true;
		}

		if ( is_post_type_archive( 'product' ) || is_product_taxonomy() ) {
			return true;
		}

		return false;
	}

	public static function get_packages( $args = [], $categories = [] ) {
		$defaults = [
			'post_type'        => 'product',
			'posts_per_page'   => -1,
			'suppress_filters' => false,
			'tax_query'        => [
				[
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => [ 'opalestate_package' ],
				],
			],
			'orderby'          => 'menu_order title',
			'order'            => 'ASC',
		];

		if ( isset( $categories ) && ! empty( $categories ) ) {
			$defaults['tax_query'][] = [
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $categories,
			];
		}

		$args = wp_parse_args( $args, $defaults );

		return new \WP_Query( $args );
	}
}
