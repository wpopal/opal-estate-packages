<?php
use Opalestate_Packages\Core\Query;

$product_cat = [];
if ( isset( $product_cat ) && ! empty( $product_cat ) ) {
	$product_cat = explode( ',', $product_cat );
}

$loop    = Query::get_packages( [], $product_cat );
?>
<div class="membership-packages">
	<?php if ( $loop->have_posts() ) : ?>
        <div class="row">
			<?php
			$col = floor( 12 / $column );
			$i   = 0;
			while ( $loop->have_posts() ) : $loop->the_post(); ?>
                <div class="col-lg-<?php echo esc_attr( $col ); ?> col-md-<?php echo esc_attr( $col ); ?> col-sm-6 col-xs-12 <?php if ( $i++ % $column == 0 ): ?>first<?php endif; ?>">
					<?php echo opalestate_packages_get_template_part( 'content-package' ); ?>
                </div>
			<?php endwhile; ?>
        </div>
	<?php endif; ?>
</div>
<?php wp_reset_postdata(); ?>
