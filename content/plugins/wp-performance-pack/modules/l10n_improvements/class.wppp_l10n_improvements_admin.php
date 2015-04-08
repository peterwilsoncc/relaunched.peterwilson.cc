<?php
/**
 * Admin base class. Contains functions for all users (i.e. user without manage_options rights).
 *
 * @author Björn Ahrens <bjoern@ahrens.net>
 * @package WP Performance Pack
 * @since 0.8
 */
 
class WPPP_L10n_Improvements_Admin extends WPPP_L10n_Improvements_Base  {
	public function load_renderer ( $view ) {
		if ( $this->renderer == NULL ) {
			if ( $view = 'advanced' ) {
				$this->renderer = new WPPP_L10n_Improvements_Advanced ();
			} else {
				$this->renderer = new WPPP_L10n_Improvements_Simple ();
			}
		}
	}

	public function admin_init () {
		if ( $this->wppp->options['disable_backend_translation'] && $this->wppp->options['dbt_allow_user_override'] ) {
			add_action( 'profile_personal_options', array( $this, 'wppp_extra_profile_fields' ) );
			add_action( 'personal_options_update', array ( $this, 'save_wppp_user_settings' ) );
			add_action( 'edit_user_profile_update', array ( $this, 'save_wppp_user_settings' ) );
		}
	}

	/*
	 * User override of disable  backend translation
	 */

	function save_wppp_user_settings ( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

		if ( isset( $_POST['wppp_translate_backend'] ) && $_POST['wppp_translate_backend'] === 'true' ) {
			update_user_option( $user_id, 'wppp_translate_backend', 'true' );
		} else {
			update_user_option( $user_id, 'wppp_translate_backend', 'false' );
		}
	}

	function wppp_extra_profile_fields( $user ) {
		$user_setting = get_user_option( 'wppp_translate_backend', $user->ID );
		$user_override = $user_setting === 'true' || ( $this->wppp->options['dbt_user_default_translated'] && $user_setting === false );
		?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Translate back end', 'wppp' ); ?></th>
					<td>
						<label for="wppp-translate-backend-enabled"><input type="checkbox" name="wppp_translate_backend" id="wppp-translate-backend-enabled" value="true" <?php echo  $user_override ? 'checked="true"' : ''; ?> /><?php _e( 'Enable back end translation', 'wppp' ); ?></label><br/>
						<span class="description"><?php _e( 'Enable or disable back end translation. When disabled, back end will be displayed in english, else it will be translated to the blog language.', 'wppp' ); ?></span>
					</td>
				</tr>
			</table>
		<?php
	}
}
?>