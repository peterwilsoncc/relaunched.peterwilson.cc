<?php

class WPPP_CDN_Support_Simple {
	public function enqueue_scripts_and_styles ( $renderer ) {
		wp_register_script( 'jquery-ui-slider-pips', $renderer->wppp->plugin_url . 'common/js/jquery-ui-slider-pips.min.js', array ( 'jquery-ui-slider' ), false, true );
		wp_register_script( 'wppp-admin-script', $renderer->wppp->plugin_url . 'common/js/wppp_simple.js', array ( 'jquery-ui-slider-pips' ), false, true );
		wp_enqueue_script( 'wppp-admin-script' );

		wp_register_style( 'jquery-ui-slider-pips-styles', $renderer->wppp->plugin_url . 'common/css/jquery-ui-slider-pips.css' );
		wp_register_style( 'wppp-admin-styles-jqueryui', $renderer->wppp->plugin_url . 'common/css/styles.css' );
		wp_register_style( 'wppp-admin-styles', $renderer->wppp->plugin_url . 'common/css/wppp.css' );
		wp_enqueue_style( 'jquery-ui-slider-pips-styles' );
		wp_enqueue_style( 'wppp-admin-styles-jqueryui' );
		wp_enqueue_style( 'wppp-admin-styles' );
	}

	public function add_help_tab () {
	}

	public function render_options ( $renderer ) {
		wp_localize_script( 'wppp-admin-script', 'wpppData', '{}' );
		?>
		<input type="hidden" id="dynamic-links" <?php $renderer->e_opt_name( 'dyn_links' ); ?> value="<?php echo $renderer->wppp->options['dyn_links'] ? 'true' : 'false'; ?>" />
		<input type="hidden" id="cdn-url" <?php $renderer->e_opt_name( 'cdnurl' ); ?> value="<?php echo $renderer->wppp->options['cdnurl']; ?>"/>
		<input type="hidden" <?php $renderer->e_opt_name('cdn_images'); ?> value="<?php echo $renderer->wppp->options['cdn_images']; ?>"/>
		<input type="hidden" <?php $renderer->e_opt_name('dyn_links_subst'); ?> value="<?php echo $renderer->wppp->options['dyn_links_subst'] ? 'true' : 'false'; ?>" />

		<h3 class="title"><?php _e( 'Use CDN for images', 'wppp' );?></h3>
		
		<p class="description"><?php _e( 'Using a CDN for images improves loading times and eliminates the need to save intermediate images locally (select Webspace). The default settings when activating CDN support are activate dynamic image linking and serving images through CDN on both front and back end. These settings can be adjusted via advanced view.', 'wppp' );?></p>

		<?php
			if ( $renderer->wppp->options['cdn'] ) {
				$cdn_test = get_transient( 'wppp_cdntest' );
				if ( false !== $cdn_test ) {
					if ( 'ok' === $cdn_test ) { ?>
						<div class="ui-state-highlight ui-corner-all" style="padding:.5em; background: #fff; border: thin solid #7ad03a;"><span class="ui-icon ui-icon-check" style="float:left; margin-top:.2ex; margin-right:.5ex;"></span><?php _e( 'CDN active and working.', 'wppp' );?></div>
						<?php
					} else {
						?>
						<div class="ui-state-error ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-alert" style="float:left; margin-right:.3em;"></span><strong><?php _e( 'CDN error!', 'wppp' );?></strong> <?php prinftf( __( "Either the CDN is down or CDN configuration isn't working. CDN will be retested every 15 minutes until the configuration is changed or the CDN is back up. CDN test error message: <em>%s</em>", 'wppp' ), $cdn_test );?></div>
						<?php
					}
					?> <br/> <?php
				}
			}
		?>

		<table>
			<tr valign="top">
				<th scope="row" style="text-align:left"><?php _e( 'Select CDN provider', 'wppp' ); ?></th>
				<td style="padding-left:2em;">
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
					<br/>
				</td>
			</tr>
		</table>

		<hr/>
	<?php
	}
}

?>