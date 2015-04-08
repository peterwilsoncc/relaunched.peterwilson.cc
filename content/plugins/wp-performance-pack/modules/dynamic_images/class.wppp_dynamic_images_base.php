<?php
/**
 * Don't generate intermediate images on upload, but on first access.
 * Image creation is done via serve-dynamic-images.php.
 * Based on Dynamic Image Resizer (http://ottopress.com) by Samuel Wood (http://ottodestruct.com).
 *
 * @author Björn Ahrens
 * @package WP Performance Pack
 * @since 1.1
 */

class WPPP_Dynamic_Images_Base extends WPPP_Dynamic_Images {
	private $dynimg_image_sizes = NULL;

	function early_init () {
		add_action( 'setup_theme',  array( $this, 'replace_wp_rewrite' ) );
	}

	function init () {
		self::set_rewrite_rules();

		add_filter( 'wp_image_editors', array ( $this, 'filter_wp_image_editor' ), 1000, 1 ); // set to very low priority, so it is hopefully called last as this overrides previously registered editors
		add_action( 'shutdown', array( $this, 'save_preset_image_sizes' ) );

		if ( $this->wppp->options['dynamic_images_rthook'] ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				add_filter( 'wp_update_attachment_metadata', array ( $this, 'rebuild_thumbnails_delete_hook' ), 100, 2 );
			}
			add_action( 'admin_notices', array( $this, 'rthook_notice') );
		}
	}

	function replace_wp_rewrite () {
		if ( $this->wppp->is_network ) {
			$GLOBALS['wp_rewrite'] = new WPPP_Rewrite();
		}
	}

	public static function set_rewrite_rules () {
		$path = substr( plugins_url( 'serve-dynamic-images.php', __FILE__ ), strlen( site_url() ) + 1 ); // cut wp-content including trailing slash
		add_rewrite_rule( '(.*)-([0-9]+)x([0-9]+)?\.((?i)jpeg|jpg|png|gif)' , $path, 'top' );
		add_filter ( 'mod_rewrite_rules', array ( 'WPPP_Dynamic_Images_Base', 'mod_rewrite_rules' ) );
	}

	public static function flush_rewrite_rules ( $enabled ) {
		// init is called prior to options update
		// so add or remove rules before flushing
		if ( $enabled ) {
			self::set_rewrite_rules();
		} else {
			global $wp_rewrite;
			if ( $wp_rewrite && isset( $wp_rewrite->non_wp_rules['(.*)-([0-9]+)x([0-9]+)?\.((?i)jpeg|jpg|png|gif)'] ) ) {
				unset( $wp_rewrite->non_wp_rules['(.*)-([0-9]+)x([0-9]+)?\.((?i)jpeg|jpg|png|gif)'] );
			}
		}
		flush_rewrite_rules();
	}

	public static function mod_rewrite_rules ( $rules ) {
		$lines = explode( "\n", $rules );
		$rules = '';
		for ($i = 0, $max = count($lines); $i<$max; $i++ ) {
			if ( strpos( $lines[$i], 'serve-dynamic-images.php' ) !== false ){
				// extend rewrite rule by conditionals, so if the requested file exist it gets served directly
				$rules .= "RewriteCond %{REQUEST_FILENAME} !-f \n";
			}
			$rules .= $lines[$i] . "\n";
		}
		return $rules;
	}

	function filter_wp_image_editor ( $editors ) {
		$new_editors = array();
		// extend each registered editor and override its multi_resize function - found no better (i.e. flexible) way than to use eval
		foreach ( $editors as $editor ) {
			if ( ! class_exists( "WPPP_$editor" ) ) {
			eval (" 
			class WPPP_$editor extends $editor {

				public function multi_resize( \$sizes ) {
					\$metadata = array();
					\$orig_size = \$this->size;

					foreach ( \$sizes as \$size => \$size_data ) {
						if ( ! isset( \$size_data['width'] ) && ! isset( \$size_data['height'] ) ) {
							continue;
						}

						if ( ! isset( \$size_data['width'] ) ) {
							\$size_data['width'] = null;
						}
						if ( ! isset( \$size_data['height'] ) ) {
							\$size_data['height'] = null;
						}

						if ( ! isset( \$size_data['crop'] ) ) {
							\$size_data['crop'] = false;
						}

						\$dims = image_resize_dimensions( \$this->size['width'], \$this->size['height'], \$size_data['width'], \$size_data['height'], \$size_data['crop'] );
						if ( \$dims ) {
							list( \$dst_x, \$dst_y, \$src_x, \$src_y, \$dst_w, \$dst_h, \$src_w, \$src_h ) = \$dims;
							\$this->update_size( \$dst_w, \$dst_h );

							list( \$filename, \$extension, \$mime_type ) = \$this->get_output_format( null, null );

							if ( ! \$filename )
								\$filename = \$this->generate_filename( null, null, \$extension );

							\$metadata[\$size] = array(
								'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', \$filename ) ),
								'width'     => \$this->size['width'],
								'height'    => \$this->size['height'],
								'mime-type' => \$mime_type,
							);
							\$this->size = \$orig_size;
						}
					}
					return \$metadata;
				}
			} 
			");
			}
			$new_editors[] = 'WPPP_' . $editor;
		}
		return $new_editors;
	}

	function save_preset_image_sizes() {
		global $_wp_additional_image_sizes;
 
		$sizes = array();
		foreach ( get_intermediate_image_sizes() as $s ) {
			$sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => false );
			if ( isset( $_wp_additional_image_sizes[$s]['width'] ) ) {
				$sizes[$s]['width'] = intval( $_wp_additional_image_sizes[$s]['width'] ); // For theme-added sizes
			} else {
				$sizes[$s]['width'] = intval ( get_option( "{$s}_size_w" ) ); // For default sizes set in options
				if ( $sizes[$s]['width'] == 0 ) {
					unset( $sizes[$s] );
					continue;
				}
			}
			if ( isset( $_wp_additional_image_sizes[$s]['height'] ) ) {
				$sizes[$s]['height'] = intval( $_wp_additional_image_sizes[$s]['height'] ); // For theme-added sizes
			} else {
				$sizes[$s]['height'] = intval ( get_option( "{$s}_size_h" ) ); // For default sizes set in options
				if ( $sizes[$s]['height'] == 0 ) {
					unset( $sizes[$s] );
					continue;
				}
			}
			if ( isset( $_wp_additional_image_sizes[$s]['crop'] ) ) {
				$sizes[$s]['crop'] = $_wp_additional_image_sizes[$s]['crop'] ? true : false; // For theme-added sizes
			} else {
				$sizes[$s]['crop'] = get_option( "{$s}_crop" ) ? true : false; // For default sizes set in options
			}
		}
		
		add_option ( 'wppp_dynimg_sizes', $sizes );
	}

	function rebuild_thumbnails_delete_hook ($data, $postID) {
		global $wp_current_filter;
		if ( is_array( $wp_current_filter ) && 
			( in_array( 'wp_ajax_regeneratethumbnail', $wp_current_filter ) 
			 || in_array( 'wp_ajax_ajax_thumbnail_rebuild', $wp_current_filter )
			 || in_array( 'wp_ajax_sis_rebuild_images', $wp_current_filter ) ) ) {
			if ( $attach_meta = wp_get_attachment_metadata( $postID ) ) {
				global $wp_performance_pack;
				if ( $wp_performance_pack->options['dynamic_images_rthook_force'] ) {
					// delete all potential thumbnail files (filname.ext ~ filename-*x*.ext)
					$upload_dir = wp_upload_dir();
					$filename = $upload_dir['basedir'] . '/' . $attach_meta['file'];
					$info = pathinfo($filename);
					$ext = $info['extension'];
					$pattern = str_replace(".$ext", "-*x*.$ext", $filename);
					foreach (glob($pattern) as $thumbname) {
						@unlink($thumbname);
					}
				} else {
					if ( isset( $attach_meta['sizes'] ) ) {
						$upload_dir = wp_upload_dir();
						$filepath = $upload_dir['basedir'] . '/' . dirname( $attach_meta['file'] ) . '/';
						$filename = wp_basename( $attach_meta['file'] );
						foreach ( $attach_meta['sizes'] as $size => $size_data ) {
							$file = $filepath . $size_data['file'];
							if ( file_exists( $file ) && ( $size_data['file'] != $filename ) ) {
								@unlink( $file );
							}
						}
					}
				}
			}
		}
		return $data;
	}

	function rthook_notice () { 
		// display message on Rebuild Thumbnails page
		$screen = get_current_screen(); 
		if ( $screen->id == 'tools_page_regenerate-thumbnails' 
			|| $screen->id == 'tools_page_ajax-thumbnail-rebuild' 
			|| ( $screen->id == 'options-media' && is_plugin_active( 'simple-image-sizes/simple_image_sizes.php' ) ) ) : ?>
			<div class="update-nag"> 
				<p>
					WPPP Regenerate Thumbnails integration active.<br/>
					Existing intermediate images will be deleted while regenerating thumbnails.
					<?php
						global $wp_performance_pack;
						if ( $wp_performance_pack->options['dynamic_images_rthook_force'] ) : 
							?>
							<br/><strong>Force delete option is active!</strong>
							<?php 
						endif;
					?>
					<br/>
					<a href="options-general.php?page=wppp_options_page">Change WPPP settings</a>
				</p> 
			</div>
		<?php endif; 
	}
}

?>