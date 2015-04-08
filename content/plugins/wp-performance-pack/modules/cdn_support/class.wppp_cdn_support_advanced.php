<?php

class WPPP_CDN_Support_Advanced {
	public function enqueue_scripts_and_styles ( $renderer ) {
		wp_register_script( 'jquery-ui-slider-pips', $renderer->wppp->plugin_url . 'common/js/jquery-ui-slider-pips.min.js', array ( 'jquery-ui-slider' ), false, true );
		wp_register_script( 'wppp-admin-script', $renderer->wppp->plugin_url . 'common/js/wppp_advanced.js', array ( 'jquery-ui-slider-pips' ), false, true );
		wp_enqueue_script( 'wppp-admin-script' );

		wp_register_style( 'jquery-ui-slider-pips-styles', $renderer->wppp->plugin_url . 'common/css/jquery-ui-slider-pips.css' );
		wp_register_style( 'wppp-admin-styles-jqueryui', $renderer->wppp->plugin_url . 'common/css/styles.css' );
		wp_register_style( 'wppp-admin-styles', $renderer->wppp->plugin_url . 'common/css/wppp.css' );
		wp_enqueue_style( 'jquery-ui-slider-pips-styles' );
		wp_enqueue_style( 'wppp-admin-styles-jqueryui' );
		wp_enqueue_style( 'wppp-admin-styles' );
	}

	public function add_help_tab () {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_cdn',
			'title'	=>	__( 'CDN support', 'wppp' ),
			'content'	=> '<p>' . __( "CDN support allows to serve images through a CDN, both on front and back end. This eliminates the need to save intermediate images locally, thus reducing web space usage. Use of dynamic image linking is highly recommended when using WPPP CDN support for front end.", 'wppp' ) . '</p>',
		) );
	}

	public function render_options ( $renderer ) {
	?>
		<input id="cdn-url" type="hidden" <?php $renderer->e_opt_name( 'cdnurl' ); ?> value="<?php echo $renderer->wppp->options['cdnurl']; ?>"/>

		<h3 class="title"><?php _e( 'CDN Support', 'wppp' );?></h3>

		<?php
			if ( $renderer->wppp->options['cdn'] ) {
				$cdn_test = get_transient( 'wppp_cdntest' );
				if ( false !== $cdn_test ) {
					if ( 'ok' === $cdn_test ) { ?>
						<div class="ui-state-highlight ui-corner-all" style="padding:.5em; background: #fff; border: thin solid #7ad03a;"><span class="ui-icon ui-icon-check" style="float:left; margin-top:.2ex; margin-right:.5ex;"></span><?php _e( 'CDN active and working.', 'wppp' );?></div>
						<?php
					} else {
						?>
						<div class="ui-state-error ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-alert" style="float:left; margin-right:.3em;"></span><strong><?php _e( 'CDN error!', 'wppp' );?></strong> <?php printf( __( "Either the CDN is down or CDN configuration isn't working. CDN will be retested every 15 minutes until the configuration is changed or the CDN is back up. CDN test error message: <em>%s</em>", 'wppp' ), $cdn_test ); ?></div>
						<?php
					}
				}
			}
		?>

		<table class="form-table" style="clear:none">
			<tr valign="top">
				<th scope="row"><?php _e( 'Select CDN provider', 'wppp' ); ?></th>
				<td>
					<select id="wppp-cdn-select" <?php $renderer->e_opt_name( 'cdn' ) ?> >
						<option value="false" <?php echo $renderer->wppp->options['cdn'] === false ? 'selected="selected"' : ''; ?>><?php _e( 'None', 'wppp' );?></option>
						<option value="coralcdn" <?php echo $renderer->wppp->options['cdn'] === 'coralcdn' ? 'selected="selected"' : ''; ?>>CoralCDN</option>
						<option value="maxcdn" <?php echo $renderer->wppp->options['cdn'] === 'maxcdn' ? 'selected="selected"' : ''; ?>>MaxCDN</option>
						<option value="customcdn" <?php echo $renderer->wppp->options['cdn'] === 'customcdn' ? 'selected="selected"' : ''; ?>><?php _e( 'Custom', 'wppp' );?></option>
					</select>
					<span id="wppp-maxcdn-signup" <?php echo $renderer->wppp->options['cdn'] === 'maxcdn' ? '' : 'style="display:none;"'; ?> ><a class="button" href="http://tracking.maxcdn.com/c/92472/3982/378" target="_blank"><?php _e( 'Sign up with MaxCDN', 'wppp' );?></a> <?php _e( '<strong>Use <em>WPPP</em> as coupon code to save 25%!</strong>', 'wppp' );?></span>
					<div id="wppp-nocdn" class="wppp-cdn-div" <?php echo $renderer->wppp->options['cdn'] !== false ? 'style="display:none"' : ''; ?>>
						<p class="description"><?php _e( 'CDN support is disabled. Choose a CDN provider to activate serving images through the selected CDN.', 'wppp' );?></p>
					</div>
					<div id="wppp-coralcdn" class="wppp-cdn-div" <?php echo $renderer->wppp->options['cdn'] !== 'coralcdn' ? 'style="display:none"' : ''; ?>>
						<p class="description"><?php _e( '<a href="http://www.coralcdn.org" target="_blank">CoralCDN</a> does not require any additional settings.', 'wppp' );?></p>
					</div>
					<div id="wppp-maxcdn"  class="wppp-cdn-div" <?php echo $renderer->wppp->options['cdn'] !== 'maxcdn' ? 'style="display:none"' : ''; ?>>
						<p><label for="cdn-url"><?php _e( 'MaxCDN Pull Zone URL:', 'wppp' );?><br/><input id="maxcdn-url" type="text" value="<?php echo $renderer->wppp->options['cdnurl']; ?>" style="width:80%"/></label></p>
						<p class="description"><?php _e( '<a href="https://cp.maxcdn.com" target="_blank">Log in</a> to your <a href="http://www.maxcdn.com" target="_blank">MaxCDN</a> account, create a pull zone for your WordPress site and enter the CDN URL for that zone.', 'wppp' );?></p>
					</div>
					<div id="wppp-customcdn" class="wppp-cdn-div" <?php echo $renderer->wppp->options['cdn'] !== 'customcdn' ? 'style="display:none"' : ''; ?>>
						<p><label for="cdn-url"><?php _e( 'CDN URL:', 'wppp' );?><br/><input id="customcdn-url" type="text" value="<?php echo $renderer->wppp->options['cdnurl']; ?>" style="width:80%"/></label></p>
						<p class="description"><?php _e( 'Enter your CDN URL. This will be used to substitute the host name in image links.', 'wppp' );?></p>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Use CDN for images', 'wppp' ); ?></th>
				<td>
					<?php _e( 'Use on', 'wppp' );?> <input type="radio" <?php $renderer->e_opt_name( 'cdn_images' ); ?> <?php echo $renderer->wppp->options['cdn_images'] === 'front' ? 'checked="checked"' : ''; ?> value="front"><?php _e( 'front end', 'wppp' );?>&nbsp;
					<input type="radio" <?php $renderer->e_opt_name( 'cdn_images' ); ?> <?php echo $renderer->wppp->options['cdn_images'] === 'back' ? 'checked="checked"' : ''; ?> value="back"><?php _e( 'back end', 'wppp' );?>&nbsp;
					<input type="radio" <?php $renderer->e_opt_name( 'cdn_images' ); ?> <?php echo $renderer->wppp->options['cdn_images'] === 'both' ? 'checked="checked"' : ''; ?> value="both"><?php _e( 'both', 'wppp' );?><br/>
					<p class="description"><?php _e( 'Select if CDN should be used for front end and/or back end images. You can deactivate front end CDN to avoid conflicts with other CDN plugins.', 'wppp' );?></p>
				</td>
			<tr valign="top">
				<th scope="row"><?php _e( 'Dynamic image linking', 'wppp' ); ?></th>
				<td>
					<?php $renderer->e_radio_enable( 'dynlinks', 'dyn_links' ); ?>
					<p class="description"><?php _e( 'Instead of inserting fixed image urls into posts, urls get build dynamically when displaying the content. <strong>Highly recommended when using a CDN for front end images.</strong>', 'wppp' );?></p>
					<br>
					<?php $renderer->e_checkbox( 'dynlinksubst', 'dyn_links_subst', __( 'Use substitution for faster dynamic links', 'wppp' ) ); ?>
					<p class="description"><?php _e( 'Image links will be substituted by a placeholder in your posts to improve performance of dynamic links. <strong>This will alter your post content and might break your image links!</strong> To revert the changes see "<em>Restore static links</em>" below.', 'wppp' ); ?></p>
					<br>
					<p><a class="thickbox button" href="admin-ajax.php?action=wppp_restore_all_links&width=600&height=550" title="Restore static links"><?php _e( 'Restore static links', 'wppp' );?></a></p>
					<p class="description"><?php _e('Use this to restore all dynamic links to static links if you deactivate dynamic linking. Links will be automatically restored when WPPP gets deactivated.', 'wppp' );?></p>
				</td>
			</tr>
		</table>

		<hr/>
	<?php
	}
}

?>