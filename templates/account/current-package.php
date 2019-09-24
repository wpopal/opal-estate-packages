<?php
/**
 * Current package template.
 *
 * @author     Opal  Team <info@wpopal.com >
 * @copyright  Copyright (C) 2014 wpopal.com. All Rights Reserved.
 *
 * @website  http://www.wpopal.com
 * @support  http://www.wpopal.com/support/forum.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user;

wp_get_current_user();
$user_id = $current_user->ID;

$package_activation = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_activation', true );
$package_id         = (int) get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_id', true );
$payment_id         = (int) get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'payment_id', true );
$package_expired    = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_expired', true );

if ( $package_id ) :
	$package = wc_get_product( $package_id );
	$payment        = wc_get_order( $payment_id );

	if ( $package && $payment ) : ?>
		<?php
		$package_activation = ! is_numeric( $package_activation ) ? strtotime( $package_activation ) : $package_activation;

		$package_activation = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $package_activation );
		$package_expired    = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $package_expired );

		$current_listings         = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_listings', true );
		$curent_featured_listings = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_featured_listings', true );

		$pack_listings           = get_post_meta( $package_id, 'opalestate_package_package_listings', true );
		$pack_featured_listings  = get_post_meta( $package_id, 'opalestate_package_package_featured_listings', true );
		$pack_unlimited_listings = get_post_meta( $package_id, 'opalestate_package_unlimited_listings', true );
		$unlimited_listings      = $pack_unlimited_listings == 'yes' ? 0 : 1;
		$actions                 = wc_get_account_orders_actions( $payment );
		?>
        <div class="opalmembership-box">
            <div class="panel-body">
                <h3><?php esc_html_e( 'Latest Payment', 'opalestate-packages' ); ?></h3>
                <ul class="list-group">
                    <li>
                        <span><?php esc_html_e( 'Payment ID', 'opalestate-packages' ); ?></span>: <a href="<?php echo esc_url( $actions['url'] ); ?>">
							<?php echo absint( $payment->get_id() ); ?></a>
                    </li>
                    <li>
						<?php printf(
							__( 'Paid on %1$s @ %2$s', 'opalestate-packages' ),
							wc_format_datetime( $payment->get_date_paid() ),
							wc_format_datetime( $payment->get_date_paid(), get_option( 'time_format' ) )
						); ?>
                    </li>
                    <li><span><?php esc_html_e( 'Total', 'opalestate-packages' ); ?></span>: <strong><?php echo wp_kses_post( $payment->get_formatted_order_total() ); ?></strong></li>
                    <li><span><?php esc_html_e( 'Status', 'opalestate-packages' ); ?></span>: <?php echo esc_html( wc_get_order_status_name( $payment->get_status() ) ); ?></li>
                </ul>
                <p><a href="<?php echo esc_url( opalestate_packages_get_history_page_uri() ); ?>" class="btn btn-link"><?php esc_html_e( 'View more payments', 'opalestate-packages' ); ?></a></p>
            </div>
        </div>

        <div class="opalmembership-box">
            <div class="panel-body">
                <h3><?php esc_html_e( 'Current Package', 'opalestate-packages' ); ?></h3>
                <div class="membership-content">
                    <ul class="list-group">
                        <li><span><?php esc_html_e( 'Membership', 'opalestate-packages' ); ?></span>: <?php echo esc_html( $package->get_title() ); ?> </li>
                        <li><span><?php esc_html_e( 'Activion Date', 'opalestate-packages' ); ?></span>: <?php echo esc_html( $package_activation ); ?></li>
                        <li><span><?php esc_html_e( 'Expired On', 'opalestate-packages' ); ?></span>: <?php echo esc_html( $package_expired ); ?></li>
						<?php if ( $unlimited_listings == 1 && $package_id > 0 ) : ?>
                            <li><span><?php esc_html_e( '(Package) Listings Included:', 'opalestate-pro' ); ?></span><?php esc_html_e( 'Unlimited', 'opalestate-pro' ) ?></span></li>
                            <li><span><?php esc_html_e( '(Package) Featured Included:', 'opalestate-pro' ); ?></span><?php esc_html_e( 'Unlimited', 'opalestate-pro' ) ?></li>
						<?php else : ?>
							<?php if ( $package_id > 0 ) : ?>
                                <li><span><?php esc_html_e( '(Package) Listings Included:', 'opalestate-pro' ); ?></span><?php echo absint( $pack_listings ); ?></span></li>
                                <li><span><?php esc_html_e( '(Package) Featured Included:', 'opalestate-pro' ); ?></span><?php echo absint( $pack_featured_listings ); ?></li>
							<?php endif; ?>
                            <li><span><?php esc_html_e( 'Listings Remaining:', 'opalestate-pro' ); ?></span> <span class="text-primary"><?php echo absint( $current_listings ); ?></span></li>
                            <li><span><?php esc_html_e( 'Featured Remaining:', 'opalestate-pro' ); ?></span> <span class="text-primary"><?php echo absint( $curent_featured_listings ); ?></span></li>
						<?php endif; ?>

						<?php do_action( 'opalestate_packages_current_package_summary_after', $package_id, $payment_id ); ?>
                    </ul>
                </div>
                <p>
					<?php esc_html_e( 'Would you like to upgrade your membership?', 'opalestate-packages' ); ?>
                    <a class="btn btn-primary" href="<?php echo esc_url( opalestate_packages_get_packages_page_uri() ); ?>">
						<?php esc_html_e( 'Update Now', 'opalestate-packages' ); ?>
                    </a>
                </p>
            </div>
        </div>
    <?php else : ?>
        <div class="alert alert-warning">
            <p><?php esc_html_e( 'You have not purchased any package now.', 'opalestate-packages' ); ?></p>
            <p>
                <a href="<?php echo opalestate_packages_get_packages_page_uri(); ?>" class="btn btn-primary">
					<?php esc_html_e( 'Click to this link to see plans', 'opalestate-packages' ); ?></a>
            </p>
        </div>
	<?php endif; ?>
<?php else : ?>
    <div class="alert alert-warning">
        <p><?php esc_html_e( 'You have not purchased any package now.', 'opalestate-packages' ); ?></p>
        <p>
            <a href="<?php echo opalestate_packages_get_packages_page_uri(); ?>" class="btn btn-primary">
				<?php esc_html_e( 'Click to this link to see plans', 'opalestate-packages' ); ?></a>
        </p>
    </div>
<?php endif; ?>
