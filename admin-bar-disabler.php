<?php
/*
Plugin Name: Admin Bar Disabler
Plugin URI: http://scottkclark.com/
Description: Disable the WP Admin Bar in 3.1+ entirely, or only for roles and capabilities which aren't in the 'whitelist' or 'blacklist'.
Version: 1.2
Author: Scott Kingsley Clark
Author URI: http://scottkclark.com/
Text Domain: admin-bar-disabler
*/

load_plugin_textdomain( 'admin-bar-disabler', false, basename( dirname( __FILE__ ) ) . 'languages/' );

/**
 * Class Admin_Bar_Disabler
 */
class Admin_Bar_Disabler {

	/**
	 * Setup init
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init' ), 8 );

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			if ( is_multisite() && $this->network_activated() ) {
				add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ) );
				add_action( 'network_admin_edit_admin_bar_disabler', array( $this, 'network_settings_save' ) );
			}
		}

	}

	/**
	 * Admin init
	 */
	public function admin_init() {

		register_setting( 'admin-bar-disabler-settings-group', 'admin_bar_disabler_disable_all' );
		register_setting( 'admin-bar-disabler-settings-group', 'admin_bar_disabler_whitelist_roles' );
		register_setting( 'admin-bar-disabler-settings-group', 'admin_bar_disabler_whitelist_caps' );
		register_setting( 'admin-bar-disabler-settings-group', 'admin_bar_disabler_blacklist_roles' );
		register_setting( 'admin-bar-disabler-settings-group', 'admin_bar_disabler_blacklist_caps' );

	}

	/**
	 * Check if plugin is network activated
	 *
	 * @return bool
	 */
	public function network_activated() {

		if ( !function_exists( 'is_plugin_active_for_network' ) ) {
			$plugins = get_site_option( 'active_sitewide_plugins', array() );

			if ( isset( $plugins[ plugin_basename( __FILE__ ) ] ) ) {
				return true;
			}

			return false;
		}

		return is_plugin_active_for_network( plugin_basename( __FILE__ ) );

	}

	/**
	 * Get plugin settings
	 *
	 * @return array
	 */
	public function get_settings( $inherit = false ) {

		$settings = array(
			'disable_all' => (boolean) get_option( 'admin_bar_disabler_disable_all', 0 ),
			'whitelist_roles' => (array) get_option( 'admin_bar_disabler_whitelist_roles', array() ),
			'whitelist_caps' => get_option( 'admin_bar_disabler_whitelist_caps', '' ),
			'blacklist_roles' => (array) get_option( 'admin_bar_disabler_blacklist_roles', array() ),
			'blacklist_caps' => get_option( 'admin_bar_disabler_blacklist_caps', '' )
		);

		$settings[ 'whitelist_roles' ] = array_map( 'trim', array_unique( array_filter( $settings[ 'whitelist_roles' ] ) ) );

		$settings[ 'whitelist_caps' ] = explode( ',', $settings[ 'whitelist_caps' ] );
		$settings[ 'whitelist_caps' ] = array_map( 'trim', array_unique( array_filter( $settings[ 'whitelist_caps' ] ) ) );

		$settings[ 'blacklist_roles' ] = array_map( 'trim', array_unique( array_filter( $settings[ 'blacklist_roles' ] ) ) );

		$settings[ 'blacklist_caps' ] = explode( ',', $settings[ 'blacklist_caps' ] );
		$settings[ 'blacklist_caps' ] = array_map( 'trim', array_unique( array_filter( $settings[ 'blacklist_caps' ] ) ) );

		// Inherit settings from network settings
		if ( $inherit && is_multisite() ) {
			$site_settings = $this->get_site_settings();

			foreach ( $site_settings as $setting => $value ) {
				if ( !isset( $settings[ $setting ] ) || empty( $settings[ $setting ] ) ) {
					$settings[ $setting ] = $value;
				}
			}
		}

		return $settings;

	}

	/**
	 * Get plugin settings
	 *
	 * @return array
	 */
	public function get_site_settings() {

		$settings = array(
			'disable_all' => (boolean) get_site_option( 'admin_bar_disabler_disable_all', 0 ),
			'whitelist_roles' => (array) get_site_option( 'admin_bar_disabler_whitelist_roles', array() ),
			'whitelist_caps' => get_site_option( 'admin_bar_disabler_whitelist_caps', '' ),
			'blacklist_roles' => (array) get_site_option( 'admin_bar_disabler_blacklist_roles', array() ),
			'blacklist_caps' => get_site_option( 'admin_bar_disabler_blacklist_caps', '' )
		);

		$settings[ 'whitelist_roles' ] = array_map( 'trim', array_unique( array_filter( $settings[ 'whitelist_roles' ] ) ) );

		$settings[ 'whitelist_caps' ] = explode( ',', $settings[ 'whitelist_caps' ] );
		$settings[ 'whitelist_caps' ] = array_map( 'trim', array_unique( array_filter( $settings[ 'whitelist_caps' ] ) ) );

		$settings[ 'blacklist_roles' ] = array_map( 'trim', array_unique( array_filter( $settings[ 'blacklist_roles' ] ) ) );

		$settings[ 'blacklist_caps' ] = explode( ',', $settings[ 'blacklist_caps' ] );
		$settings[ 'blacklist_caps' ] = array_map( 'trim', array_unique( array_filter( $settings[ 'blacklist_caps' ] ) ) );

		return $settings;

	}

	/**
	 * Disable admin bar based on settings
	 *
	 * @return bool
	 */
	public function init() {

		$settings = $this->get_settings( true );

		if ( $settings[ 'disable_all' ] ) {
			return $this->disable();
		}

		$whitelist_roles = $settings[ 'whitelist_roles' ];

		if ( !empty( $whitelist_roles ) ) {
			if ( !is_array( $whitelist_roles ) ) {
				$whitelist_roles = array( $whitelist_roles );
			}

			foreach ( $whitelist_roles as $role ) {
				if ( current_user_can( $role ) ) {
					return false;
				}
			}

			return $this->disable();
		}

		$whitelist_caps = $settings[ 'whitelist_caps' ];

		if ( !empty( $whitelist_caps ) ) {
			foreach ( $whitelist_caps as $cap ) {
				if ( current_user_can( $cap ) ) {
					return false;
				}
			}

			return $this->disable();
		}

		$blacklist_roles = $settings[ 'blacklist_roles' ];

		if ( !empty( $blacklist_roles ) ) {
			if ( !is_array( $blacklist_roles ) ) {
				$blacklist_roles = array( $blacklist_roles );
			}

			foreach ( $blacklist_roles as $role ) {
				if ( !current_user_can( $role ) ) {
					return $this->disable();
				}
			}
		}

		$blacklist_caps = $settings[ 'blacklist_caps' ];

		if ( !empty( $blacklist_caps ) ) {
			foreach ( $blacklist_caps as $cap ) {
				if ( !current_user_can( $cap ) ) {
					return $this->disable();
				}
			}
		}

		return false;

	}

	/**
	 * Disable admin bar
	 *
	 * @return bool
	 */
	public function disable() {

		if ( ! is_admin() ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
		else {
			// WP 3.x support
			remove_action( 'personal_options', '_admin_bar_preferences' );

			// Disable option on user edit screen
			add_action( 'admin_print_styles-user-edit.php', array( $this, 'disable_personal_option' ) );

			// Disable option on profile screen
			add_action( 'admin_print_styles-profile.php', array( $this, 'disable_personal_option' ) );
		}

		return true;

	}

	/**
	 * Disable personal option row for Admin Bar preferences via inline CSS
	 */
	public function disable_personal_option() {

		echo '<style type="text/css">
				.show-admin-bar {
					display: none;
				}
			</style>';

	}

	/**
	 * Add menu item
	 */
	public function admin_menu() {

		add_options_page( __( 'Admin Bar Disabler', 'admin-bar-disabler' ), __( 'Admin Bar Disabler', 'admin-bar-disabler' ), 'manage_options', 'admin_bar_disabler', array( $this, 'settings_page' ) );

	}

	/**
	 * Add network menu item
	 */
	public function network_admin_menu() {

		add_submenu_page( 'settings.php', __( 'Admin Bar Disabler', 'admin-bar-disabler' ), __( 'Admin Bar Disabler', 'admin-bar-disabler' ), 'manage_network_options', 'admin_bar_disabler', array( $this, 'settings_page' ) );

	}

	/**
	 * Save network settings
	 */
	public function network_settings_save() {

		check_admin_referer( 'admin_bar_disabler' );

		$settings = $this->get_site_settings();

		foreach ( $settings as $field => $value ) {
			if ( isset( $_POST[ 'admin_bar_disabler_' . $field ] ) && !empty( $_POST[ 'admin_bar_disabler_' . $field ] ) ) {
				update_site_option( 'admin_bar_disabler_' . $field, $_POST[ 'admin_bar_disabler_' . $field ] );
			}
			else {
				delete_site_option( 'admin_bar_disabler_' . $field );
			}
		}

		wp_redirect( 'settings.php?page=admin_bar_disabler&settings-updated=1' );
		die();

	}

	/**
	 * Admin settings page
	 */
	public function settings_page() {

		$settings = $this->get_settings();

		$is_network_admin = is_multisite() && is_network_admin();

		$action = 'options.php';

		if ( $is_network_admin ) {
			$settings = $this->get_site_settings();

			$action = 'edit.php?action=admin_bar_disabler';
		}

		global $wp_roles;

		if ( !isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$roles = $wp_roles->get_names();

		if ( $is_network_admin && isset( $_GET[ 'settings-updated' ] ) ) {
	?>
		<div id="message" class="updated"><p><strong><?php _e( 'Settings saved.' ); ?></strong></p></div>
	<?php
		}
	?>
		<div class="wrap">
			<h2><?php _e( 'Admin Bar Disabler', 'admin-bar-disabler' ); ?></h2>

			<form method="post" action="<?php echo esc_attr( $action ); ?>">
				<?php
					if ( $is_network_admin ) {
						wp_nonce_field( 'admin_bar_disabler' );
					}
					else {
						settings_fields( 'admin-bar-disabler-settings-group' );
						do_settings_sections( 'admin-bar-disabler-settings-group' );
					}
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="admin_bar_disabler_disable_all">
								<?php _e( 'Disable for Everyone?', 'admin-bar-disabler' ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" name="admin_bar_disabler_disable_all" id="admin_bar_disabler_disable_all" value="1"<?php checked( $settings[ 'disable_all' ] ); ?> />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="admin_bar_disabler_whitelist_roles">
								<?php _e( 'Roles Whitelist', 'admin-bar-disabler' ); ?>
							</label>
						</th>
						<td>
							<select name="admin_bar_disabler_whitelist_roles[]" id="admin_bar_disabler_whitelist_roles" size="10" style="height:auto;" multiple="multiple">
								<?php
									$whitelist_roles = $settings[ 'whitelist_roles' ];

									foreach ( $roles as $role => $name ) {
								?>
									<option value="<?php echo esc_attr( $role ); ?>"<?php selected( in_array( $role, $whitelist_roles ) ); ?>><?php echo esc_html( $name ); ?></option>
								<?php
									}
								?>
							</select>
							<br />
							<em><?php _e( 'ONLY show the Admin Bar for Users with these Role(s) - CTRL + Click for multiple selections', 'admin-bar-disabler' ); ?></em>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="admin_bar_disabler_whitelist_caps">
								<?php _e( 'Capabilities Whitelist<br />(comma-separated)', 'admin-bar-disabler' ); ?>
							</label>
						</th>
						<td>
							<?php
								$whitelist_caps = implode( ',', $settings[ 'whitelist_caps' ] );
							?>
							<input type="text" name="admin_bar_disabler_whitelist_caps" id="admin_bar_disabler_whitelist_caps" value="<?php echo esc_attr( $whitelist_caps ); ?>" />
							<br />
							<em><?php _e( 'ONLY show the Admin Bar for Users with these Capabilies', 'admin-bar-disabler' ); ?></em>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="admin_bar_disabler_blacklist_roles">
								<?php _e( 'Roles Blacklist', 'admin-bar-disabler' ); ?>
							</label>
						</th>
						<td>
							<select name="admin_bar_disabler_blacklist_roles[]" id="admin_bar_disabler_blacklist_roles" size="10" style="height:auto;" multiple="multiple">
								<?php
									$blacklist_roles = $settings[ 'blacklist_roles' ];

									foreach ( $roles as $role => $name ) {
								?>
									<option value="<?php echo esc_attr( $role ); ?>"<?php selected( in_array( $role, $blacklist_roles ) ); ?>><?php echo esc_html( $name ); ?></option>
								<?php
									}
								?>
							</select>
							<br />
							<em><?php _e( 'DO NOT show the Admin Bar for Users with these Role(s) - CTRL + Click for multiple selections', 'admin-bar-disabler' ); ?></em>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="admin_bar_disabler_blacklist_caps">
								<?php _e( 'Capabilities Blacklist<br />(comma-separated)', 'admin-bar-disabler' ); ?>
							</label>
						</th>
						<td>
							<?php
								$blacklist_caps = implode( ',', $settings[ 'blacklist_caps' ] );
							?>
							<input type="text" name="admin_bar_disabler_blacklist_caps" id="admin_bar_disabler_blacklist_caps" value="<?php echo esc_attr( $blacklist_caps ); ?>" />
							<br /><em><?php _e( 'DO NOT show the Admin Bar for Users with these Capabilies', 'admin-bar-disabler' ); ?></em>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'admin-bar-disabler' ); ?>" />&nbsp;&nbsp;
					<small>
						<strong><?php _e( 'Do not use Blacklist in combination with Whitelist, in all cases Whitelist overrides Blacklist', 'admin-bar-disabler' ); ?></strong>
					</small>
				</p>
			</form>
		</div>
	<?php

	}

}

global $admin_bar_disabler;
$admin_bar_disabler = new Admin_Bar_Disabler();