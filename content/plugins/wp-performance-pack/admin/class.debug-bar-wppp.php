<?php
/**
 * DebugBar panel for WP Performance Pack
 *
 * @author Björn Ahrens <bjoern@ahrens.net>
 * @package WP Performance Pack
 * @since 0.5
 * @license GNU General Public License version 3 or later
 */
 
class Debug_Bar_WPPP extends Debug_Bar_Panel {
	public $textdomains = array ();
	public $plugin_base = '';

	private function get_caller ( $stacktrace ) {
		static $excludes = array (
			'call_user_func_array',
			'wppp_load_textdomain_override',
			'apply_filters',
			'load_textdomain',
			'load_theme_textdomain',
			'load_plugin_textdomain',
		);
		$str = '?';
		for ( $i = 0, $max = count( $stacktrace ); $i < $max; $i++) {
			if ( !in_array ( $stacktrace[$i]['function'], $excludes ) ) {
				if ( isset( $stacktrace[$i]['class'] ) ) {
					$str = $stacktrace[$i]['class'] . $stacktrace[$i]['type'] . $stacktrace[$i]['function'];
				} else {
					$str = $stacktrace[$i]['function'];
				}

				if ( isset( $stacktrace[$i]['file'] ) ) {
					$str = substr ( $stacktrace[$i]['file'], strlen ( ABSPATH ) ) . ', line ' . $stacktrace[$i]['line'] . ': ' . $str;
				}
				break;
			}
		}
		return $str;
	}

	function init() {
		$this->title( __('WP Performance Pack', 'wppp') );
	}

	private function isAvailable($func) {
		if (ini_get('safe_mode')) return false;
		$disabled = ini_get('disable_functions');
		if ($disabled) {
			$disabled = explode(',', $disabled);
			$disabled = array_map('trim', $disabled);
			return !in_array($func, $disabled);
		}
		return true;
	}

	private function WPPP_loaded_first () {
		if ( $plugins = get_option( 'active_plugins' ) ) {
			$key = array_search( $this->plugin_base, $plugins );
			return ( $key === 0 );
		}
	}

	function render() {
		$locale=get_locale();
		$Path = WP_LANG_DIR . '/' . $locale . '/LC_MESSAGES';
		$direxists = false;
		$show_hitcount_hint = false;
		?>
		<div id="debug-bar-wppp">
			<h3>General</h3>
			<table class="widefat">
				<tr>
					<th scope="row">WPPP loaded first?</th>
					<td><?php echo $this->WPPP_loaded_first()===false ? 'No' : 'Yes'; ?></td>
				</tr>
			</table>
			
			<h3>Textdomains</h3>
			<table class="widefat">
				<thead>
					<tr>
						<th>textdomain</th>
						<th>mofile(s)</th>
						<th>file exists?</th>
						<th>caller(s)</th>
						<th>implementation</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$odd = false;
					foreach ($this->textdomains as $domain => $td) {
						$odd = !$odd;
						$mo_class = NULL;
						for ($i = 0, $max = count($td['mofiles']); $i < $max; $i++ ) {
						?>
						<tr <?php echo $odd ? 'class="alternate" ' : ' '; ?> >
							<td><?php if ( $i == 0 ) { echo $domain; } ?></td>
							<td><?php echo substr ( $td['mofiles'][$i], strlen ( ABSPATH . 'wp-content' ) ); ?></td>
							<td><?php echo $td['mofileexists'][$i]; ?></td>
							<td><code><?php echo $this->get_caller( $td['callers'][$i] ); ?></code></td>
							<td><?php
								if ( $i == 0 ) {
									echo '<code>';
									global $l10n;
									if ( isset( $l10n[$domain] ) ) {
										$mo_class = $l10n[$domain];
										if ( $mo_class instanceof WPPP_MO_dynamic_Debug )
											// Hide use of ...Debug class from user, as it doesn't matter and possibly confuses
											echo get_parent_class ( $mo_class );
										else
											echo get_class( $mo_class ); 
									} else {
										echo 'none (NOOPTranslations)';
									}
									echo '</code>';
								}
							?></td>
						</tr>
						<?php
						}
						
						if ($mo_class instanceof MO_dynamic_Debug) {
							$show_hitcount_hint = true;
							?>
							<tr <?php echo $odd ? 'class="alternate" ' : ' '; ?> >
								<td></td>
								<td colspan="4">
									<span class="description">
										translate calls: <strong><?php echo $mo_class->translate_hits; ?></strong> - 
										translate_plural calls: <strong><?php echo $mo_class->translate_plural_hits; ?></strong> - 
										translation searches: <strong><?php echo $mo_class->search_translation_hits; ?></strong>
										<sup><a href="#wppp-hit-count-hint">*</a></sup>
									</span>
									<?php
										global $wp_performance_pack;
										if ( $wp_performance_pack->options['mo_caching'] ) : ?>
										<br/>
										<span class="description">
											caching active - 
											<?php if ( isset( $td['cache'] ) || isset( $td['basecache'] ) ) : ?>
												translations loaded from cache: 
												<?php if ( isset( $td['cache'] ) ) : ?>
													<strong><?php echo $td['cache']; ?></strong>
													<?php if ( isset( $td['basecache'] ) ) : ?>
														+ <strong><?php echo $td['basecache']; ?></strong> base translations
													<?php endif; ?>
												<?php else : ?>
													<strong><?php echo $td['basecache']; ?></strong> base translations
												<?php endif; ?>
											<?php else : ?>
												no translations loaded from cache
											<?php endif; ?>
										</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php
						}
					}
					?>
				</tbody>
			</table>
			<?php if ( $show_hitcount_hint ) : ?>
				<small><sup><a id="wppp-hit-count-hint" style="text-decoration:none !important;">*</a></sup>Hit counts are actually higher because some translations occur after debug panel is rendered.</small>
			<?php endif; ?>

			<h3>Native gettext support</h3>
			<table class="widefat">
				<tr class="alternate">
					<th scope="row">OS</th>
					<td><?php echo php_uname(); ?></td>
				</tr>
				<tr>
					<th scope="row">PHP gettext extension is</th>
					<td><?php echo extension_loaded('gettext') ? 'Available' : 'Not available'; ?></td>
				</tr>
				<tr class="alternate">
					<th scope="row">WordPress locale</th>
					<td><?php echo $locale; ?></td>
				</tr>
				<tr>
					<th scope="row">LC_MESSAGES defined?</th>
					<td><?php echo defined( 'LC_MESSAGES' ) ? 'Yes' : 'No'; ?></td>
				</tr>
				<tr class="alternate">
					<th scope="row">System locales (LC_MESSAGES)</th>
					<td><?php
						if( !defined( 'LC_MESSAGES' ) )
							define( 'LC_MESSAGES', LC_CTYPE );
						$l = setlocale (LC_MESSAGES, "0");
						echo join( '<br/>', explode( ';', $l ) );
						?>
					</td>
				</tr>
				<tr>
					<th scope="row">Putenv available?</th>
					<td><?php echo $this->isAvailable( 'putenv' ) ? 'Yes' : 'No'; ?></td>
				</tr>
				<tr class="alternate">
					<th scope="row">Locale writeable? (<?php  echo $locale; ?>)</th>
					<td><?php echo ( setlocale (LC_MESSAGES, $locale ) == $locale ) ? 'Yes' : 'No' ; ?></td>
				</tr>
				<tr>
					<th scope="row">Directory <code><?php echo $Path; ?></code></th>
					<td><?php
						if ( !is_dir ( $Path ) ) {
							if ( !wp_mkdir_p ( $Path ) ) {
								echo 'Does not exist and could not be created';
							} else {
								echo 'Created';
								$direxists = true;
							}
						} else {
							echo 'Exists';
							$direxists = true;
						}
						?>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}
}
