<?php do_action( 'opalestate_packages_membership_warning_content' ); ?>

<?php if ( ! opalestate_packages_is_membership_valid( $user_id ) ) : ?>
    <div class="alert alert-danger">
        <p><?php esc_html_e( 'Your membership package is expired please upgrade now', 'opal-estate-packages' ); ?></p>
        <p><a href="#"><?php esc_html_e( 'Click to this link to see plans.', 'opal-estate-packages' ); ?></a></p>
    </div>
<?php endif; ?>

<?php if ( ! opalesate_check_has_add_listing( $user_id ) ): ?>
    <div class="alert alert-warning">
        <p><?php esc_html_e( 'Your package has 0 left listing, you could not add any more. Please upgrade now', 'opal-estate-packages' ); ?></p>
        <p><a href="#" class="btn btn-primary"><?php esc_html_e( 'Click to this link to see plans.', 'opal-estate-packages' ); ?></a></p>
    </div>
<?php endif; ?>
