<?php do_action( 'opalestate_packages_membership_warning_content' ); ?>

<?php if ( ! opalestate_packages_is_membership_valid() ) : ?>
    <div class="alert alert-danger">
        <p><?php esc_html_e( 'Your membership package is expired please upgrade now', 'opalestate-packages' ); ?></p>
        <p><a href="#"><?php esc_html_e( 'Click to this link to see plans', 'opalestate-packages' ); ?></a></p>
    </div>
<?php endif; ?>
