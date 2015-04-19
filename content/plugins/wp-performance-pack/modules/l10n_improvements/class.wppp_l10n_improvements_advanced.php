<?php

class WPPP_L10n_Improvements_Advanced {
	public function enqueue_scripts_and_styles ( $renderer ) {
		wp_register_style( 'wppp-admin-styles-jqueryui', $renderer->wppp->plugin_url . 'common/css/styles.css' );
		wp_enqueue_style( 'wppp-admin-styles-jqueryui' );
	}

	public function add_help_tab () {
		$screen = get_current_screen();

		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_l10n',
			'title'	=> __( 'Overview', 'wppp' ),
			'content'	=> '<p>' . __( 'WPPP offers different options to significantly improve localization performance. These only affect localization of WordPress core, themes and plugins, not translation of content (e.g. by using plugins like WPML).', 'wppp' ) . '</p>',
		) );
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_gettext',
			'title'	=> __( 'GNU gettext', 'wppp' ),
			'content'	=> '<p>' . __( 'Using native GNU gettext is the fastest way for localization. It requires the PHP gettext extension to be installed and your <em>wp-content/languages</em> folder has to be writable. WPPP will store copies of translation files in the subfolder <em>wppp</em>.', 'wppp' ) . '</p>',
		) );
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_moreader',
			'title'	=> __( 'MO reader', 'wppp' ),
			'content'	=> '<p>' . __( 'The alternative MO reader is a complete rewrite of the default MO reader. It loads translation files only when needed and only the needed translations. This improves memory usage and localization performance significantly. For best performance activate caching. This requires a persistent Object Cache to be effective.', 'wppp' ) . '</p>',
		) );
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_jit',
			'title'	=> __( 'JIT', 'wppp' ),
			'content'	=> '<p>' . __( 'WordPress translates many texts by default, regardless if they are used or not. JIT script localization, as the name suggests, delays localizing default scripts to when (and if) they are used, thus reducing translation calls and improving performance a bit.', 'wppp' ) . '</p>',
		) );
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_backend',
			'title'	=> __( 'Back end localization', 'wppp' ),
			'content'	=> '<p>' . __( "The fastest option is to not localize WordPress. This might not be an option for the front end, but if you don't mind an english back end, you can disable backend localization. By activating <em>Allow user override</em> you can allow your users to reenable localization.", 'wppp' ) . '</p>',
		) );
	}

	public function render_options ( $renderer ) {
	?>
		<h3 class="title"><?php _e( 'Improve localization performance', 'wppp' ); ?></h3>
		<table class="form-table" style="clear:none">
			<tr valign="top">
				<th scope="row"><?php _e( 'Use gettext', 'wppp' ); ?></th>
				<td>
					<?php $renderer->e_radio_enable( 'native-gettext', 'use_native_gettext', $renderer->is_native_gettext_available() != 0 ); ?>
					<p class="description"><?php _e( 'Use php gettext extension for localization. This is in most cases the fastest way to localize your blog.', 'wppp' ); ?></p>
					<?php $renderer->do_hint_gettext( true ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="width:15em"><?php _e( 'Use alternative MO reader', 'wppp' ); ?></th>
				<td>
					<?php $renderer->e_radio_enable( 'mo-dynamic', 'use_mo_dynamic' ); ?>
					<p class="description"><?php _e( 'Alternative MO reader using on demand translation and loading of localization files (.mo). Faster and less memory intense than the default WordPress implementation.' ,'wppp' ); ?></p>
					<br/>
					<?php $renderer->e_checkbox( 'mo-caching', 'mo_caching', __( 'Use caching', 'wppp' ) ); ?>
					<p class="description"><?php _e( "Cache translations using WordPress' Object Cache API", 'wppp' ); ?></p>
					<?php $renderer->do_hint_caching(); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<?php _e( 'Use JIT localize', 'wppp' ); ?>
				</th>
				<td>
					<?php $renderer->e_radio_enable( 'jit', 'use_jit_localize', !$renderer->is_jit_available() ); ?>
					<p class="description"><?php _e( 'Just in time localization of scripts.', 'wppp' ); ?></p>
					<?php $renderer->do_hint_jit( true ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<?php _e( 'Disable back end localization', 'wppp' ); ?>
				</th>
				<td>
					<?php $renderer->e_radio_enable( 'backend-trans', 'disable_backend_translation' ); ?>
					<p class="description"><?php _e('Disables localization of back end texts.', 'wppp' ); ?></p>
					<br/>
					<?php $renderer->e_checkbox( 'allow-user-override', 'dbt_allow_user_override', __( 'Allow user override', 'wppp' ) ); ?>
					<p class="description"><?php  _e( 'Allow users to reactivate back end localization in their profile settings.', 'wppp' ); ?></p>
					<br/>
					<p>
						<?php _e( 'Default user language:', 'wppp' ); ?>&nbsp;
						<label for="user-default-english"><input id="user-default-english" type="radio" <?php $renderer->e_opt_name( 'dbt_user_default_translated' ); ?> value="false" <?php $renderer->e_checked( 'dbt_user_default_translated', false ); ?>><?php _e( 'English', 'wppp' ); ?></label>&nbsp;
						<label for="user-default-translated"><input id="user-default-translated" type="radio" <?php $renderer->e_opt_name( 'dbt_user_default_translated' ); ?> value="true" <?php $renderer->e_checked( 'dbt_user_default_translated' ); ?>><?php _e( 'Blog language', 'wppp' ); ?></label>
					</p>
					<p class="description"><?php _e( "Default back end language for new and existing users, who haven't updated their profile yet.", 'wppp' ); ?></p>
				</td>
			</tr>
		</table>
		<hr/>
	<?php
	}
}

?>