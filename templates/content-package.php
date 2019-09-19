<?php
global $post;

$product = wc_get_product( $post->ID );
$style   = '';
if ( has_post_thumbnail() ) {
	$style .= 'style="background-image:url(' . get_the_post_thumbnail_url() . ');"';
}

$pack_listings           = $product->get_package_listings();
$pack_featured_listings  = $product->get_package_featured_listings();
$pack_unlimited_listings = $product->is_unlimited_listings();
$unlimited_listings      = $pack_unlimited_listings == 'on' ? 0 : 1;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="package-inner<?php if ( $product->is_highlighted() ): ?> package-hightlighted<?php endif; ?>">
        <div class="pricing pricing-v3">
            <div class="pricing-header" <?php echo wp_kses_post( $style ); ?>>
                <span class="plan-subtitle hide"><?php esc_html_e( 'Recommend', 'opalestate-packages' ); ?></span>
				<?php the_title( '<h4 class="plan-title">', '</h4>' ); ?>
                <div class="plan-price">
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
                    <p>
						<?php
						$duration_unit = $product->get_package_duration_unit();
						$duration      = absint( $product->get_package_duration() );
						echo esc_html( $duration . ' ' . $duration_unit );
						?>
                    </p>
                </div>
            </div>
            <div class="pricing-body">
                <div class="plain-info">
                    <div class="pricing-more-info">
                        <div class="item-info">
                            <span>
                                <?php if ( ! empty( $pack_listings ) && $unlimited_listings == 0 ): ?>
	                                <?php echo trim( $pack_listings ); ?><?php esc_html_e( ' Listings', 'opalestate-pro' ); ?>
                                <?php else: ?>
	                                <?php esc_html_e( 'Unlimited', 'opalestate-pro' ); ?><?php esc_html_e( ' Listings', 'opalestate-pro' ); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="item-info">
                            <span>
                                <?php if ( ! empty( $pack_featured_listings ) && $unlimited_listings == 0 ): ?>
	                                <?php echo trim( $pack_featured_listings ); ?><?php esc_html_e( ' Featured', 'opalestate-pro' ); ?>
                                <?php else: ?>
	                                <?php esc_html_e( 'Unlimited', 'opalestate-pro' ); ?><?php esc_html_e( ' Featured', 'opalestate-pro' ); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

					<?php
					/* translators: %s: Name of current post */
					the_content( sprintf(
						esc_html__( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'opalestate-packages' ),
						the_title( '<span class="screen-reader-text">', '</span>', false )
					) );
					?>
                </div>
            </div>
            <div class="pricing-footer">
                <?php echo apply_filters( 'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
	                sprintf( '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
		                esc_url( $product->add_to_cart_url() ),
		                esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
		                esc_attr( isset( $args['class'] ) ? $args['class'] : 'membership-add-to-purchase btn btn-md btn-block' ),
		                isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
		                esc_html( $product->add_to_cart_text() )
	                ),
	                $product, $args ); ?>
            </div>
        </div>
    </div>
</article>
