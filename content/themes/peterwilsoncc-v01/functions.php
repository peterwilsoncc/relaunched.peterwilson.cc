<?php
$content_width=717;
$pwcc_css_ver = "20150315-01";

class PWCC_theme {
	
	
	function __construct() {
		add_action( 'after_setup_theme', array( $this, 'action_theme_setup' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'action_enqueue_assets' ) );
		add_action( 'wp_head', array( $this, 'action_header_javascript' ) );
		add_action( 'wp_head', array( $this, 'action_favicons' ) );
		
		add_filter( 'body_class', array( $this, 'filter_body_class' ), 10, 2 );
		add_filter( 'post_class', array( $this, 'filter_post_class' ), 10, 2 );
		add_filter( 'comment_class', array( $this, 'filter_comment_class' ), 10, 2 );
		add_filter( 'nav_menu_css_class', '__return_empty_array' );
		
		//add_filter( 'single_post_title', array( $this, 'filter_single_post_title' ), 10, 2 );
		add_filter( 'the_title', array( $this, 'filter_the_title' ), 10, 2  );
		add_filter( 'wpseo_title', array( $this, 'filter_wpseo_title' ), 10 );
		add_filter( 'wpseo_twitter_domain', array( &$this, 'filter_wpseo_twitter_domain' ) );
		
		
		
		
		add_filter( 'get_comment_author_link', array( $this, 'filter_get_comment_author_link' ), 10, 4 );
		add_filter( 'comment_form_default_fields', array( $this, 'filter_comment_form_default_fields' ) );
		add_filter( 'comment_form_defaults', array( $this, 'filter_comment_form_defaults' ) );
		
		add_filter( 'next_posts_link_attributes', array( $this, 'rel_next_attr' ) );
		add_filter( 'previous_posts_link_attributes', array( $this, 'rel_prev_attr' ) );
		
		// oh Jetpack, I am sure you're very sweet.
		add_filter( 'jetpack_implode_frontend_css', '__return_false' );
		add_action( 'wp_print_styles', array( $this, 'i_only_use_jetpack_stats' ) );
	}
	
	function action_theme_setup() {
		
		// posts and comments feeds
		add_theme_support( 'automatic-feed-links' );
		
		// use the WP built in title
		add_theme_support( 'title-tag' );
		
		// post thumbnails? sure
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 1200, 675, true ); // 16:9
		
		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus( array(
			'header'  => __( 'Primary Menu',      'pwcc-theme' ),
			'footer'  => __( 'Footer Menu', 'pwcc-theme' ),
		) );
		
		// post types
		add_theme_support( 'post-formats', array( 'link', 'status' ) );
		
		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
		) );
		
	}
	
	function action_enqueue_assets() {
		global $content_width, $pwcc_css_ver;
		if ( is_attachment() )
			$content_width = 1200;
		elseif ( has_post_format( 'audio' ) )
			$content_width = 484;



		$assets = get_template_directory_uri() . '/assets';
		
		if ( isset( $_COOKIE["pwccsscache"] ) && ( $pwcc_css_ver == $_COOKIE["pwccsscache"] ) ) {
			wp_enqueue_style(
				'pwcc-styles',
				$assets . '/css/style.min.css',
				null,
				$pwcc_css_ver
			);
		}
		
		
		wp_register_script(
			'pwcc-scripts',
			$assets . '/js/min/functions-min.js',
			null,
			'1.0',
			true
		);
		
		$js_config = array(
			'siteHome' => home_url( '/' ),
			'assetsHome' => untrailingslashit( $assets ) . '/'
		);
		
		wp_localize_script(
			'pwcc-scripts',
			'PWCC_data',
			$js_config
		);
		
		wp_enqueue_script( 'pwcc-scripts' );
		
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) && ( 0 < pwcc_theme_get_comments_number() ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
		
	}
	
	function action_header_javascript() {
		global $pwcc_css_ver;
		
		if ( !isset( $_COOKIE["pwccsscache"] ) || ( $pwcc_css_ver != $_COOKIE["pwccsscache"] ) ) {
			echo '<style>';
			readfile ( get_stylesheet_directory() . '/assets/css/style.min.css' );
			echo '</style>';
		}
		
		
		?>
		<script id="pwcc-inline-js">
		<?php
		readfile ( get_stylesheet_directory() . '/assets/js/min/inline-header-min.js' );
		echo ';';
		if ( !isset( $_COOKIE["pwccsscache"] ) || ( $pwcc_css_ver != $_COOKIE["pwccsscache"] ) ) {
			$css = get_template_directory_uri() . '/assets/css/style.min.css?ver=' . $pwcc_css_ver ;
			echo 'PWCC.loadCSS( "' . $css . '", document.getElementById("pwcc-inline-js") );';
			echo 'document.cookie = "pwccsscache=' . $pwcc_css_ver . '; path=/";';
		}
		?>
		</script>
		<?php
	}

	function action_favicons() {
		$icons = get_template_directory_uri() . '/assets/images/favicons';
		
		echo "<link rel='shortcut icon' href='$icons/favicon.ico'>\n";
		echo "<link rel='apple-touch-icon' sizes='57x57' href='$icons/apple-touch-icon-57x57.png'>\n";
		echo "<link rel='apple-touch-icon' sizes='114x114' href='$icons/apple-touch-icon-114x114.png'>\n";
		echo "<link rel='apple-touch-icon' sizes='72x72' href='$icons/apple-touch-icon-72x72.png'>\n";
		echo "<link rel='apple-touch-icon' sizes='144x144' href='$icons/apple-touch-icon-144x144.png'>\n";
		echo "<link rel='apple-touch-icon' sizes='60x60' href='$icons/apple-touch-icon-60x60.png'>\n";
		echo "<link rel='apple-touch-icon' sizes='120x120' href='$icons/apple-touch-icon-120x120.png'>\n";
		echo "<link rel='apple-touch-icon' sizes='76x76' href='$icons/apple-touch-icon-76x76.png'>\n";
		echo "<link rel='apple-touch-icon' sizes='152x152' href='$icons/apple-touch-icon-152x152.png'>\n";
		echo "<meta name='apple-mobile-web-app-title' content='pwcc.cc'>\n";
		echo "<link rel='icon' type='image/png' href='$icons/favicon-196x196.png' sizes='196x196'>\n";
		echo "<link rel='icon' type='image/png' href='$icons/favicon-160x160.png' sizes='160x160'>\n";
		echo "<link rel='icon' type='image/png' href='$icons/favicon-96x96.png' sizes='96x96'>\n";
		echo "<link rel='icon' type='image/png' href='$icons/favicon-16x16.png' sizes='16x16'>\n";
		echo "<link rel='icon' type='image/png' href='$icons/favicon-32x32.png' sizes='32x32'>\n";
		echo "<meta name='msapplication-TileColor' content='#006ef6'>\n";
		echo "<meta name='msapplication-TileImage' content='$icons/mstile-144x144.png'>\n";
		echo "<meta name='msapplication-config' content='$icons/browserconfig.xml'>\n";
		echo "<meta name='application-name' content='PWCC'>\n";
	}


	function i_only_use_jetpack_stats() {
		wp_deregister_style( 'AtD_style' ); // After the Deadline
		wp_deregister_style( 'jetpack_likes' ); // Likes
		wp_deregister_style( 'jetpack_related-posts' ); //Related Posts
		wp_deregister_style( 'jetpack-carousel' ); // Carousel
		wp_deregister_style( 'grunion.css' ); // Grunion contact form
		wp_deregister_style( 'the-neverending-homepage' ); // Infinite Scroll
		wp_deregister_style( 'infinity-twentyten' ); // Infinite Scroll - Twentyten Theme
		wp_deregister_style( 'infinity-twentyeleven' ); // Infinite Scroll - Twentyeleven Theme
		wp_deregister_style( 'infinity-twentytwelve' ); // Infinite Scroll - Twentytwelve Theme
		wp_deregister_style( 'noticons' ); // Notes
		wp_deregister_style( 'post-by-email' ); // Post by Email
		wp_deregister_style( 'publicize' ); // Publicize
		wp_deregister_style( 'sharedaddy' ); // Sharedaddy
		wp_deregister_style( 'sharing' ); // Sharedaddy Sharing
		wp_deregister_style( 'stats_reports_css' ); // Stats
		wp_deregister_style( 'jetpack-widgets' ); // Widgets
		wp_deregister_style( 'jetpack-slideshow' ); // Slideshows
		wp_deregister_style( 'presentations' ); // Presentation shortcode
		wp_deregister_style( 'jetpack-subscriptions' ); // Subscriptions
		wp_deregister_style( 'tiled-gallery' ); // Tiled Galleries
		wp_deregister_style( 'widget-conditions' ); // Widget Visibility
		wp_deregister_style( 'jetpack_display_posts_widget' ); // Display Posts Widget
		wp_deregister_style( 'gravatar-profile-widget' ); // Gravatar Widget
		wp_deregister_style( 'widget-grid-and-list' ); // Top Posts widget
		wp_deregister_style( 'jetpack-widgets' ); // Widgets
	}
	
	function filter_body_class( $classes, $custom_classes ) {
		
		// I like to kill ALL the body classes!
		$classes = [];

		if ( is_home() || is_archive() ) {
			$classes[] = 't-List';
			// if ( 'post' == get_post_type() ) {
			// 	$classes[] = 't-AllBlog';
			// }
		}
		if ( is_search() ) {
			$classes[] = 't-List';
		}
		
		if ( is_singular() || is_404() ) {
			$classes[] = 't-Singular';
			// if ( ( 'post' == get_post_type() ) || ( 'attachment' == get_post_type() ) ) {
			// 	$classes[] = 't-AllBlog';
			// }
		}

		// clearing classes removed the custom classes, redo.
		// all the tidying & forcing the array has been done
		$classes = array_merge( $classes, $custom_classes );
		
		// escape the classes for attributes
		$classes = array_map( 'esc_attr', $classes );
		
		
		return $classes;
	}

	function filter_post_class( $classes, $custom_classes ) {
		
		// I like to kill all the default classes
		$classes = [];
		
		// microformats 1 and 2
		// $classes[] = 'hentry';
		// $classes[] = 'h-entry';
		
		// it's an article 
		$classes[] = 'Article';
		
		// clearing classes removed the custom classes, redo.
		// all the tidying & forcing the array has been done
		$classes = array_merge( $classes, $custom_classes );
		
		// escape the classes for attributes
		$classes = array_map( 'esc_attr', $classes );
		
		
		return $classes;
	}

	function filter_single_post_title( $title, $_post ) {
		// this filters the HTML title of the post
		
		$title = trim( $title );
		
		if ( '' == $title ) {
			$cats = get_the_category( $_post->ID );
			$num_terms = count( $cats );
			if ( $cats ) {
				$term = $cats[0]; // only care about the first cat
				if ( 'notes' == $term->slug ) {
					$title .= 'Note';
				}
				else {
					$title .= $term->name;
				}
				$title .= '—';
				$title .= get_the_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) );
				
			}
			
		}
		
		return $title;
	}
	
	function filter_the_title( $title, $post_id ) {
		$post = get_post( $post_id );
		
		if ( '' == $title ) {

			$cats = get_the_category( $post->ID );
			$num_terms = count( $cats );
			if ( $cats ) {
				$term = $cats[0]; // only care about the first cat
				if ( 'notes' == $term->slug ) {
					$title .= 'Noted ';
				}
				else {
					$title .= $term->name;
				}
				$title .= ' ';
			}
			else if ( 'pwcc_notes' == $post->post_type ) {
				$title .= 'Noted ';
			}
			// Date it
			$title .= get_the_date( get_option( 'date_format' ), $post->id );
		}
		
		return $title;
	}
	
	function filter_wpseo_title( $title ) {
		if ( is_singular() || is_single() ) {
			global $post;
			if ( wpseo_replace_vars( '%%page%% %%sep%% %%sitename%%', $post ) == $title ) {
				
				$pwcc_title = '';
				$cats = get_the_category( $post->ID );
				if ( $cats ) {
					$term = $cats[0]; // only care about the first cat
					if ( 'notes' == $term->slug ) {
						$pwcc_title .= 'Noted';
					}
					else {
						$pwcc_title .= $term->name;
					}
					$pwcc_title .= ' ';
					$pwcc_title .= get_the_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) );
				
				}
				else if ( 'pwcc_notes' == $post->post_type ) {
					$pwcc_title .= 'Noted ';
					$pwcc_title .= get_the_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) );
				}
				
				
				$title = wpseo_replace_vars( $pwcc_title . ' %%page%% %%sep%% %%sitename%%', $post );
			}
		}
		
		
		return $title;
	}
	

	function filter_comment_class( $classes, $custom_classes ) {

		// I am fussy
		$classes = [];
		
		$classes[] = 'Comment';
		$classes[] = 'p-comment';
		$classes[] = 'h-entry';
		

		// clearing classes removed the custom classes, redo.
		// all the tidying & forcing the array has been done
		$classes = array_merge( $classes, $custom_classes );

		
		// escape the classes for attributes
		$classes = array_map( 'esc_attr', $classes );

		return $classes;
	}

	function filter_get_comment_author_link( $return, $author, $comment_ID ) {
		$url    = get_comment_author_url( $comment_ID );

		if ( empty( $url ) || 'http://' == $url )
			$return = $author;
		else
			$return = "<a href='$url' rel='external nofollow' class='url u-url'>$author</a>";
		
		return $return;
	}

	function filter_comment_form_default_fields( $fields ) {
	
		$commenter = wp_get_current_commenter();
		$user = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';
	
		
		$html5 = true;
		$req      = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );
		
		
		$fields   =  array(
			'author' => '<div class="InputSet InputSet-Text comment-form-author">' . '<label for="author">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
			            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></div>',
			'email'  => '<div class="InputSet InputSet-Text comment-form-email"><label for="email">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
			            '<input id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" aria-describedby="email-notes"' . $aria_req . ' /></div>',
			'url'    => '<div class="InputSet InputSet-Text comment-form-url"><label for="url">' . __( 'Website' ) . '</label> ' .
			            '<input id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></div>',
		);
		
		
		return $fields;
	}

	function filter_comment_form_defaults( $defaults ) {


		$commenter = wp_get_current_commenter();
		$user = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';
	
		
		$html5 = true;
		$req      = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );
		
		// the comment form
		$defaults['comment_field'] = '<div class="InputSet InputSet-Text comment-form_comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label> <textarea id="comment" name="comment" cols="45" rows="8" aria-describedby="form-allowed-tags" aria-required="true"></textarea></div>';
		
		$defaults['comment_notes_after']  = '<p class="form-allowed-tags" id="form-allowed-tags"><small>' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ), ' <code>' . allowed_tags() . '</code>' ) . '</small></p>';
		
		
		return $defaults;
	}

	function rel_next_attr( $attr ) {
		
		$attr .= ' ' . 'rel="next" ';
		
		return $attr;
	}

	function rel_prev_attr( $attr ) {
		
		$attr .= ' ' . 'rel="prev" ';
		
		return $attr;
	}

	function section_title( $hN = 'h2' ){
		$title = '';
		switch( 1 ) {
			case is_home() && !is_paged():
				$title = "Latest Posts";
				break;
			case is_archive():
			case is_tax():
			case is_category():
			case is_tag():
				$title = get_the_archive_title();
				break;
		}
		
		if ( $title ) {
			$title = "<$hN class='util-SectionTitle'>$title</$hN>";
		}
		
		return $title;
	}

	function entry_meta( $post ) {
		$title = isset( $post->post_title ) ? $post->post_title : '';

		echo '<div class="EntryMeta">';
		
		// when and where
		echo '<span class="EntryMeta_Item"> Posted ';
		
		// the when
		echo '<time class="EntryMeta_Detail entry-date dt-published" datetime="' . esc_attr( get_the_date( 'c' ) ) . '">';
		echo '<a href="' . esc_attr( get_permalink() ) . '" class="u-url" rel="bookmark">';
		
		if ( '' == $title ) {
			// date is in title
			the_time();
		}
		else {
			the_time( get_option( 'date_format' ) );
		}

		echo '</a>';
		echo '</time>';
		
		// the where 
		$geo_latitude  = get_post_meta( get_the_ID(), 'geo_latitude',  true );
		$geo_longitude = get_post_meta( get_the_ID(), 'geo_longitude', true );
		$geo_public    = get_post_meta( get_the_ID(), 'geo_public',    true );
		$geo_address   = get_post_meta( get_the_ID(), 'geo_address',   true );
		
		if ( ( $geo_public !== 'false' ) && $geo_latitude && $geo_longitude ) {
			echo ' from ';
			echo '<span class="EntryMeta_Detail p-location h-geo">';
			echo '<data class="p-latitude"  value="' . esc_attr( $geo_latitude ) . '">';
			$int_lat = intval( $geo_latitude );
			$abs_lat = abs( $int_lat );
			echo $abs_lat;
			echo '<abbr class="util-silentAbbr" title="degrees">°</abbr>';
			if ( $abs_lat == $int_lat ) {
				echo '<abbr class="util-silentAbbr" title="North">N</abbr>';
			}
			else {
				echo '<abbr class="util-silentAbbr" title="South">S</abbr>';
			}
			echo ', ';
			echo '<data class="p-longitude"  value="' . esc_attr( $geo_longitude ) . '">';
			$int_long = intval( $geo_longitude );
			$abs_long = abs( $int_long );
			echo $abs_long;
			echo '<abbr class="util-silentAbbr" title="degrees">°</abbr>';
			if ( $abs_long == $int_long ) {
				echo '<abbr class="util-silentAbbr" title="West">W</abbr>';
			}
			else {
				echo '<abbr class="util-silentAbbr" title="East">E</abbr>';
			}
			echo '</data>';
			echo '</span>';
		}
		
		// finish the when anad where
		echo '</span> ';
		
		// Categories
		$cats = get_the_category();
		$num_terms = count( $cats );
		if ( $cats ) {
			echo '<span class="EntryMeta_Item';
			if ( ( 1 == $num_terms ) && ( 'notes' == $cats[0]->slug ) ) {
				echo ' util-Display-None ';
			}
			echo '">';
			$seperator = ', ';
			echo ( 1 == $num_terms ) ? 'Category ' : 'Categories ';
			$out = '';
			foreach ( $cats as $term ) {
				$out .= '<a href="';
				$out .= esc_url( get_term_link( $term ) );
				$out .= '" class="EntryMeta_Detail EntryMeta_Detail-Taxonomy p-category" rel="category tag">';
				$out .= esc_html( $term->name );
				$out .= '</a>' . $seperator;
			}
			
			echo trim( $out, $seperator );
			echo '</span>';


		}
		
		

		// Tags
		$tags = get_the_tags();
		$num_terms = count( $tags );
		if ( $tags ) {
			echo '<span class="EntryMeta_Item">';
			$seperator = ', ';
			echo 'Tagged ';
			$out = '';
			foreach ( $tags as $term ) {
				$out .= '<a href="';
				$out .= esc_url( get_term_link( $term ) );
				$out .= '" class="EntryMeta_Detail EntryMeta_Detail-Taxonomy p-category" rel="category tag">';
				$out .= esc_html( $term->name );
				$out .= '</a>' . $seperator;
			}
			
			echo trim( $out, $seperator );
			echo '</span>';
		}
		

		// hidden syndication links
		
		$twitter_permalink = get_post_meta( $post->ID, 'twitter_permalink', true );
		$twitter_id = get_post_meta( $post->ID, 'twitter_id', true );
		$instagram_url = get_post_meta( $post->ID, 'instagram_url', true );
		
		if ( $twitter_id || $twitter_permalink || $instagram_url ) {
			echo ' <span class="EntryMeta_Item util-Display-None">Also on ';
			$seperator = ', ';
			$links = '';
			if ( $twitter_permalink ) {
				$links .= $this->syn_link( $twitter_permalink, 'Twitter', 'EntryMeta_Detail');
				$links .= $seperator;
			}
			else if ( ( '' != $twitter_id ) && ( 0 != $twitter_id ) ) {
				// assume user is pwcc
				$twitter_permalink = "https://twitter.com/pwcc/status/" . $twitter_id;
				$links .= $this->syn_link( $twitter_permalink, 'Twitter', 'EntryMeta_Detail');
				$links .= $seperator;
			}
			if ( $instagram_url ) {
				$links .= $this->syn_link( $instagram_url, 'Instagram', 'EntryMeta_Detail');
				$links .= $seperator;
			}
			
			echo trim( $links, $seperator );
			echo '</span>';
		}


		
		
		echo '</div><!-- //.EntryMeta -->';
	}

	function entry_meta_footer( $post ) {
		$twitter_permalink = get_post_meta( $post->ID, 'twitter_permalink', true );
		$twitter_id = get_post_meta( $post->ID, 'twitter_id', true );
		$instagram_url = get_post_meta( $post->ID, 'instagram_url', true );
		
		if ( $twitter_id || $twitter_permalink || $instagram_url ) {
			echo '<div class="EntryMeta EntryMeta-Footer">';
			echo ' <span class="EntryMeta_Item">Also on ';
			$seperator = ', ';
			$links = '';
			if ( $twitter_permalink ) {
				$links .= $this->syn_link( $twitter_permalink, 'Twitter', 'EntryMeta_Detail');
				$links .= $seperator;
			}
			else if ( ( '' != $twitter_id ) && ( 0 != $twitter_id ) ) {
				// assume user is pwcc
				$twitter_permalink = "https://twitter.com/pwcc/status/" . $twitter_id;
				$links .= $this->syn_link( $twitter_permalink, 'Twitter', 'EntryMeta_Detail');
				$links .= $seperator;
			}
			if ( $instagram_url ) {
				$links .= $this->syn_link( $instagram_url, 'Instagram', 'EntryMeta_Detail');
				$links .= $seperator;
			}
			
			echo trim( $links, $seperator );
			echo '</span>';
			echo '</div>';
		}
	}

	function syn_link( $url, $text, $classes = '' ) {
		if ( ( '' == $url ) || ( '' == $text ) ) {
			return '';
		}
		
		
		$link = '';
		$link .= '<a href="';
		$link .= esc_url( $url );
		$link .= '" rel="syndication" class="';
		$link .= esc_attr( 'u-syndication ' . $classes );
		$link .= '">' . esc_html( $text ) . '</a>';
		
		return $link;
	}

	function paging_nav() {
		global $wp_query;

		// Don't print empty markup if there's only one page.
		if ( $wp_query->max_num_pages < 2 )
			return;
		$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		?>
		<nav class="Section" role="navigation">
			<h2 class="util-accessibility">Post navigation</h2>
			<div class="Pagination">

				<div class="Pagination_Direction Pagination_Direction-Previous">
					<?php next_posts_link( 'Older posts' ); ?>
				</div>
				
				<div class="Pagination_Current">
					Page <?php echo esc_html( $page ); ?> 
					<span class="Pagination_Total"><abbr class="util-silentAbbr" title="of">/</abbr> 
					<?php echo esc_html( $wp_query->max_num_pages ); ?></span>
				</div>

				<div class="Pagination_Direction Pagination_Direction-Next">
					<?php previous_posts_link( 'Newer posts' ); ?>
				</div>

			</div><!-- //.Pagination -->
		</nav><!-- nav.Section -->
		<?php
	}

	function post_nav() {
		global $post;

		// Don't print empty markup if there's nowhere to navigate.
		$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
		$next     = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous )
			return;
		?>
		<nav class="Pagination Pagination-PrevNextPost" role="navigation">
			<h2 class="util-accessibility">Post navigation</h2>
				<div class="Pagination_Direction Pagination_Direction-Previous">
				<?php previous_post_link( '%link', 'Previous post' ); ?>
				</div>
				<div class="Pagination_Direction Pagination_Direction-Next">
				<?php next_post_link( '%link', 'Next post' ); ?>
				</div>

		</nav><!-- .navigation -->
		<?php
		
	}

	function comment_template( $comment, $args, $depth ) {
		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}
		
		// echo '<pre>';
		// print_r( $comment );
		// echo '</pre>';
		
		if ( ! get_option('show_avatars') ) {
			$args['avatar_size'] = 0;
		}
		$default_avatar = get_template_directory_uri() . '/assets/images/fpo_avatar.png';
		
		
		$custom_classes = [];
		$custom_classes[] = 'util-cf';
		$custom_classes[] = 'util-Clear';
		$custom_classes[] = empty( $args['has_children'] ) ? '' : 'parent';
		$custom_classes[] = ( 0 != $args['avatar_size'] ) ? 'Comment-withAvatar' : '';
		
		?>
		<<?php echo $tag; ?> <?php comment_class( $custom_classes ) ?> id="comment-<?php comment_ID(); ?>">
			<div class="Comment_Meta">
				<div class="vcard h-card p-author">
					<?php 
					if ( 0 != $args['avatar_size'] ):
						echo '<div class="Comment_Avatar">';
						echo get_avatar( $comment, $args['avatar_size'], $default_avatar, '' );
						echo '</div>';
					endif; // ( 0 != $args['avatar_size'] ): 
					?>
					<h4 class="Comment_Name">
						<cite class="fn p-name">
							<?php
								echo get_comment_author_link();
							?>
						</cite> says
					</h4>
					<time datetime="<?php echo esc_attr( get_comment_date('c') ); ?>" class="Comment_Time dt-published">
						<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID, $args ) ); ?>" rel="bookmark">
						<?php printf( __( '%1$s at %2$s' ), get_comment_date(),  get_comment_time() ); ?>
						</a>
					</time>
				</div>
			</div>
			
			<div class="Comment_Body e-content" id="div-comment-<?php comment_ID(); ?>">
				<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="Comment_Moderation">
					Your comment is awaiting moderation.
				</p>
				<?php endif; ?>


				
				
				<?php comment_text( get_comment_id(), array_merge( $args, array( 'add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div>

			<?php
			comment_reply_link( array_merge( $args, array(
				'add_below' => $add_below,
				'depth'     => $depth,
				'max_depth' => $args['max_depth'],
				'before'    => '<div class="reply Comment_Reply util-Clear">',
				'after'     => '</div>'
			) ) );
			?>


			
		<?php
	}

	function filter_wpseo_twitter_domain( $value ) {
		
		$value = 'peterwilson.cc';
		
		return $value;
	}

}

$pwcc_theme = new PWCC_theme();

function pwcc_theme_section_title( $hN = 'h2' ) {
	global $pwcc_theme;
	return $pwcc_theme->section_title( $hN );
}

function pwcc_theme_entry_meta( $post ) {
	global $pwcc_theme;
	return $pwcc_theme->entry_meta( $post );
}

function pwcc_theme_entry_meta_footer( $post ) {
	global $pwcc_theme;
	return $pwcc_theme->entry_meta_footer( $post );
}

function pwcc_theme_paging_nav() {
	global $pwcc_theme;
	return $pwcc_theme->paging_nav();
}

function pwcc_theme_post_nav() {
	global $pwcc_theme;
	return $pwcc_theme->post_nav();
}

function pwcc_theme_have_comments() {
	return have_comments();
}

function pwcc_theme_get_comments_number() {
	return get_comments_number();
}

function pwcc_theme_comment_template( $comment, $args, $depth ) {
	global $pwcc_theme;
	return $pwcc_theme->comment_template( $comment, $args, $depth );
}

function pwcc_theme_get_link_url() {
	$content = get_the_content();
	$has_url = get_url_in_content( $content );

	return ( $has_url ) ? $has_url : apply_filters( 'the_permalink', get_permalink() );
}
