<?php
/**
 * Admin settings advanced renderer class. Functions for advanced settings.
 *
 * @author Björn Ahrens <bjoern@ahrens.net>
 * @package WP Performance Pack
 * @since 0.9
 */
 
include ( sprintf( '%s/class.admin-renderer.php', dirname( __FILE__ ) ) );
 
class WPPP_Admin_Renderer_Advanced extends WPPP_Admin_Renderer {

	/*
	 * Settings page functions
	 */

	function enqueue_scripts_and_styles () {
	}

	function add_help_tab () {
		$screen = get_current_screen();

		// Add my_help_tab if current screen is My Admin Page
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_general',
			'title'	=> __('Overview'),
			'content'	=> '<p>' . __( "Welcome to WP Performance Pack, your first choice for speeding up WordPress core the easy way. The simple view helps you to easily apply  the optimal settings for your blog. Advanced view offers more in depth control of WPPP settings.", 'wppp' ) . '</p>',
		) );

		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_debugging',
			'title'	=> __( 'Debugging', 'wppp' ),
			'content'	=> '<p>' . sprintf( __( 'WPPP supports debugging using the %s plugin. When installed and activated, debugging adds a new panel to the Debug Bar showing information about loaded textdomains, used translation implementations and translation calls, as well as information about gettext support and other details.', 'wppp' ), '<a href="http://wordpress.org/plugins/debug-bar/">Debug Bar</a>' ) . '</p>',
		) );

		$screen->set_help_sidebar(
			'<p><a href="http://wordpress.org/support/plugin/wp-performance-pack" target="_blank">' . __( 'Support Forums' ) . '</a></p>'
			. '<p><a href="http://www.bjoernahrens.de/software/wp-performance-pack/" target="_blank">' . __( 'Development Blog (german)', 'wppp' ) . '</a></p>'
		);
	}

	/*
	 * Setting page rendering functions
	 */

	public function on_do_options_page() {
		wp_localize_script( 'wppp-admin-script', 'wpppData', array (
			'dynimg-quality' => $this->wppp->options['dynimg_quality'],
		));
	}

	function render_options () {
		foreach ( $this->wppp->modules as $module ) {
			$module->render_options( $this );
		}
	
		?>
<!--		<h3>Selective plugin loading</h3>
			<table class="widefat">
				<thead>
					<tr>
						<th>Plugin name</th>
						<th>Front end</th>
						<th>Back end</th>
						<th>AJAX</th>
					</tr>
				</thead>
				<tbody>
				<?php
					/* $plugins = get_option( 'active_plugins' );
					$odd = false;
					foreach ( $plugins as $plugin ) {
						$odd = !$odd;
						$data = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin );
						if ($odd) {
							echo '<tr class="alternate">';
						} else {
							echo '<tr>';
						}
						echo '<td>', $data['Name'],'</td>';
						?>
							<td><input type="checkbox" name="splFronend[]" value="test" /></td>
							<td><input type="checkbox" name="splBackend[]" value="test" /></td>
							<td><input type="checkbox" name="splAjax[]" value="test" /></td>
						</tr>
						<?php
					} */
				?>
				</tbody>
			</table>
-->

		<h3 class="title"><?php _e( 'Debugging', 'wppp' ); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Debug Panel', 'wppp' ); ?></th>
				<td>
					<?php $this->e_radio_enable( 'debug-panel', 'debug', !class_exists( 'Debug_Bar' ) ); ?>
					<p class="description"><?php _e( 'Enables debugging, requires <a href="http://wordpress.org/plugins/debug-bar/">Debug Bar</a> Plugin.', 'wppp' ); ?></p>
				</td>
			</tr>
		</table>

		<hr/>
		<?php
	}
}
?>