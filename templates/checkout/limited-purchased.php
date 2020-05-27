<?php if ( opalestate_packages_is_unlimited_purchased() ): ?>
	<div class="alert alert-warning">
		<p><?php esc_html_e( 'You have too limited to purchase this package, please try to purchase other.', 'opal-estate-packages' ); ?></p>
		<p><a href="#" class="btn btn-primary"><?php esc_html_e( 'Click to this link to see plans.', 'opal-estate-packages' ); ?></a></p>
	</div>
<?php endif; ?>
