<?php do_action('opalmembership_membership_warning_content'); ?>
<?php if(  !opalmembership_is_membership_valid() ) : ?>
<div class="alert alert-danger">
	<p><?php esc_html_e( 'Your membership package is expired please upgrade now','opalmembership' );?></p>
	<p><a href="<?php echo opalmembership_get_membership_page_uri();?>"><?php esc_html_e( 'Click to this link to see plans','opalmembership' );?></a></p>
</div>
<?php  endif; ?>