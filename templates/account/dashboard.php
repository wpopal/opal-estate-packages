<?php
/**
 * $Desc$
 *
 * @version    $Id$
 * @package    $package$
 * @author     Opal  Team <info@wpopal.com >
 * @copyright  Copyright (C) 2014 wpopal.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @website  http://www.wpopal.com
 * @support  http://www.wpopal.com/support/forum.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! is_user_logged_in() ) {
	esc_html_e( 'You must be logged in before.', 'opalmembership' ); return;
}

$tabs = apply_filters( 'opalmembership_dashboard_tabs', array() );

if ( empty( $tabs ) ) return;

?>

<!-- Dashborad wrapper -->
<div class="opalmembership-dashboard">

	<!-- dashboard sidebar -->
	<div class="opalmembership-dashboard-sidebar">
		<?php
			/**
			 * include template: account/sidebar.php
			 */
			do_action( 'opalmembership_dashboard_sidebar', $tabs );
		?>
	</div>
	<!-- end dashboard sidebar -->
	<?php
			/**
			 * include template: account/sidebar.php
			 */
			do_action( 'opalmembership_dashboard_container_before' );
	?>
	
	<?php foreach ( $tabs as $key => $tab ) :    ?>
		<div class="opalmembership-dashboard-container <?php echo esc_attr( $key ); ?>">
			<?php call_user_func_array( $tab['callback'], array( $key, $tab ) ); ?>
		</div>
	<?php endforeach; ?>
	<?php
		/**
		 * include template: account/sidebar.php
		 */
		do_action( 'opalmembership_dashboard_container_after' );
	?>
</div>