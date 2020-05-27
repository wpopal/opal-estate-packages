<?php
namespace Opalestate_Packages\Core;

class Handler {
	/**
	 * Handler constructor.
	 */
	public function __construct() {
		// if ( opalestate_options( 'enabel_free_submission' ) ) {
		// 	/// free account
		// 	add_action( 'user_register', [ $this, 'opalestate_on_create_user' ], 10, 1 );
		// 	add_action( 'profile_update', [ $this, 'opalestate_on_update_user' ] );
		// }

		if ( get_current_user_id() ) {
			add_action( 'show_admin_bar', [ $this, 'hide_admin_toolbar' ] );

			/**
			 * save user meta when save user agent post type
			 */
			add_action( 'cmb2_save_post_fields', [ $this, 'trigger_update_user_meta' ], 10, 4 );
			add_action( 'profile_update', [ $this, 'trigger_update_agent_meta' ], 10, 2 );

			/**
			 * HOOK TO My Properties Page Set action to check when user set property as featured.
			 */
			add_filter( 'opalestate_set_feature_property_checked', [ $this, 'feature_property_checked' ] );
			add_action( 'opalestate_toggle_featured_property_before', [ $this, 'update_featured_remaining_listing' ], 10, 2 );

			/**
			 * HOOK to Submssion Page: Check permission before submitting
			 */
			// check validation before
			add_action( 'opalestate_process_submission_before', [ $this, 'check_membership_validation' ], 1 );

			add_action( 'opalestate_submission_form_before', [ $this, 'show_membership_warning' ], 9 );
			add_action( 'opalestate_submission_form_before', [ $this, 'check_membership_validation_message' ] );

			add_action( 'opalestate_process_edit_submission_before', [ $this, 'check_edit_post' ] );
			add_action( 'opalestate_process_add_submission_before', [ $this, 'check_add_post' ] );

			/// check before uploading image
			add_action( 'opalestate_before_process_ajax_upload_file', [ $this, 'check_ajax_upload' ] );
			add_action( 'opalestate_process_submission_after', [ $this, 'update_remaining_listing' ], 10, 3 );

			/**
			 * HOOK to user management Menu
			 */
			add_filter( 'opalestate_management_user_menu', [ $this, 'membership_menu' ] );
			add_action( 'profile_update', [ $this, 'on_update_user' ], 10, 1 );
		}
	}

	/**
	 * @param $user_id
	 */
	public function opalestate_on_create_user( $user_id ) {
		if ( $user_id ) {
			opalestate_reset_user_free_package( $user_id );
		}
	}

	/**
	 * @param $user_id
	 */
	public function opalestate_on_update_user( $user_id ) {
		$package_id = get_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_id', true );
		if ( empty( $package_id ) ) {
			opalestate_reset_user_free_package( $user_id );
		}
	}

	/**
	 * Hide admin toolbar with user role agent
	 */
	public function hide_admin_toolbar( $show ) {
		if ( ! is_user_logged_in() ) {
			return $show;
		}

		$user = wp_get_current_user();
		if ( opalestate_get_option( 'hide_toolbar' ) && $user->roles && in_array( 'opalestate_agent', $user->roles ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * save user meta data
	 */
	public function trigger_update_user_meta( $agent_id, $cmb_id, $updated, $cmb2 ) {
		if ( $cmb_id !== 'opalestate_agt_info' || empty( $cmb2->data_to_save ) ) {
			return;
		}
		$user_id = get_post_meta( $agent_id, OPALESTATE_AGENT_PREFIX . 'user_id', true );

		if ( ! $user_id ) {
			return;
		}
		foreach ( $cmb2->data_to_save as $name => $value ) {
			if ( strpos( $name, OPALESTATE_AGENT_PREFIX ) === 0 ) {
				update_user_meta( $user_id, OPALESTATE_USER_PROFILE_PREFIX . substr( $name, strlen( OPALESTATE_AGENT_PREFIX ) ), $value );
			}
		}
	}

	/**
	 * trigger save agent post meta
	 */
	public function trigger_update_agent_meta( $user_id, $old_user_meta ) {
		if ( empty( $_POST ) ) {
			return;
		}

		global $wpdb;
		$sql      = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_value = %d AND meta_key = %s", $user_id, OPALESTATE_AGENT_PREFIX . 'user_id' );
		$agent_id = $wpdb->get_var( $sql );

		if ( ! $agent_id ) {
			return;
		}

		foreach ( $_POST as $name => $value ) {
			if ( strpos( $name, OPALESTATE_USER_PROFILE_PREFIX ) === 0 ) {
				update_post_meta( $agent_id, OPALESTATE_AGENT_PREFIX . substr( $name, strlen( OPALESTATE_USER_PROFILE_PREFIX ) ), $value );
			}
		}
	}

	/**
	 * This function is called when user set property as featured.
	 *
	 * @return boolean. true is user having permission.
	 */
	public function feature_property_checked() {
		global $current_user;
		wp_get_current_user();
		$user_id = $current_user->ID;

		if ( isset( $_POST['property_id'] ) ) {
			return opalesate_get_user_featured_remaining_listing( $user_id );
		}

		return false;
	}

	/**
	 * Reduce -1 when set featured status is done.
	 */
	public function update_featured_remaining_listing( $user_id, $property_id ) {
		opalesate_update_package_number_featured_listings( $user_id );
	}

	/**
	 * Check user having any actived package and the package is not expired.
	 * Auto redirect to membership packages package.
	 */
	public function check_membership_validation() {
		$check = opalestate_packages_is_membership_valid();
		if ( ! $check ) {

			return opalestate_output_msg_json( true,
				__( 'Your membership package is expired or Your package has 0 left listing, please upgrade now.', 'opal-estate-packages' ),
				[
					'heading'  => esc_html__( 'Submission Information', 'opal-estate-packages' ),
					'redirect' => opalestate_packages_get_membership_page_uri(),
				] );
		}

		return;
	}

	/**
	 * @return bool|void
	 */
	public function show_membership_warning() {
		if ( isset( $_GET['id'] ) && $_GET['id'] > 0 ) {
			return true;
		}

		if ( class_exists( 'User' ) ) {
			return opalestate_packages_show_membership_warning();
		}
	}

	/**
	 * Display membership warning at top of submission form.
	 */
	public function check_membership_validation_message() {
		global $current_user;
		wp_get_current_user();
		$user_id = $current_user->ID;
		if ( isset( $_GET['id'] ) && $_GET['id'] > 0 ) {
			return;
		}

		echo opalestate_packages_get_template_part( 'account/membership-warning', [ 'user_id' => $user_id ] );
	}

	/**
	 * Check any action while editing page
	 */
	public function check_edit_post() {
		return true;
	}

	/**
	 * Check permission to allow creating any property. The package is not valid, it is automatic redirect to membership page.
	 */
	public function check_add_post() {
		global $current_user;
		wp_get_current_user();
		$user_id = $current_user->ID;

		$has = opalesate_check_has_add_listing( $user_id );
		if ( $has == false ) {
			wp_redirect( opalestate_packages_get_membership_page_uri() );
			exit;
		}
	}

	/**
	 * Before upload any file. this is called to check user having package which allows to upload or is not expired.
	 *
	 * @return void if everything is ok, or json data if it is not valid.
	 */
	public function check_ajax_upload() {
		global $current_user;
		wp_get_current_user();
		$user_id = $current_user->ID;

		$has = opalesate_check_has_add_listing( $user_id );

		$check = opalestate_packages_is_membership_valid( $user_id );

		if ( ! $check || ! $has ) {
			$std          = new stdClass();
			$std->status  = false;
			$std->message = esc_html__( 'Could not allow uploading image', 'opal-estate-packages' );
			echo json_encode( $std );
			exit();
		}
	}

	/**
	 * Remain listings.
	 *
	 * @param      $user_id
	 * @param      $property_id
	 * @param bool $isedit
	 */
	public function update_remaining_listing( $user_id, $property_id, $isedit = true ) {
		if ( $isedit != true ) {
			opalesate_update_package_number_listings( $user_id );
		}
	}

	/**
	 * On update user.
	 *
	 * @param $user_id
	 */
	public function on_update_user( $user_id ) {
		if ( $user_id ) {
			$prefix = OPALESTATE_PACKAGES_USER_PREFIX;
			$field  = $prefix . 'package_expired_date';
			if ( isset( $_POST[ $field ] ) && ! empty( $_POST[ $field ] ) ) {
				$expired_time                         = strtotime( $_POST[ $field ] );
				$_POST[ $prefix . 'package_expired' ] = $expired_time;
				update_user_meta( $user_id, OPALESTATE_PACKAGES_USER_PREFIX . 'package_expired', $expired_time );
			}
		}
	}

	/**
	 * Hook Method to add more link for user management
	 */
	public function membership_menu( $menu ) {
		$menu['membership'] = [
			'icon'  => 'fa fa-user',
			'link'  => opalestate_packages_get_current_package_page_uri(),
			'title' => esc_html__( 'My Membership', 'opal-estate-packages' ),
			'id'    => 0,
		];

		$menu['membership_history'] = [
			'icon'  => 'fa fa-user',
			'link'  => opalestate_packages_get_history_page_uri(),
			'title' => esc_html__( 'My Invoices', 'opal-estate-packages' ),
			'id'    => 0,
		];

		$menu['packages'] = [
			'icon'  => 'fa fa-certificate',
			'link'  => opalestate_packages_get_packages_page_uri(),
			'title' => esc_html__( 'Renew membership', 'opal-estate-packages' ),
			'id'    => 0,
		];

		return $menu;
	}
}
