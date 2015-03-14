<?php
	$hN = is_singular() ? 'h1' : 'h2';
	
	$the_title = get_the_title();
	
	if ( $the_title ) :
?>
	<<?php echo $hN; ?> class="Headline entry-title p-name"><?php 
	if ( !is_singular() ) {
		echo '<a href="';
		the_permalink();
		echo '" class="u-url" rel="bookmark">';
	}
	the_title(); 
	if ( !is_singular() ) {
		echo '</a>';
	}
	?></<?php echo $hN; ?>>
	<?php
	endif; // ( $the_title ) :
	
	
		if ( !is_page() ) {
			pwcc_theme_entry_meta( $post );
		}
		echo '<div class="' . ( is_singular() ? ' Section ' : ' ' ) . ' Article_Body js-Article_Body entry-content e-content util-cf">';
		if ( has_post_thumbnail() && ! post_password_required() && ! is_attachment() ) {
			echo '<div class="Article_FullWidthBlock">';
			the_post_thumbnail();
			echo '</div>';
		}
		the_content( "Continue reading " . get_the_title() );
		
	 	$pages = array(
			'before'           => '<p class="Pagination Pagination--Post"><span>Pages: </span><span> ',
			'after'            => '</span></p>',
			'link_before'      => '<b>',
			'link_after'       => '</b>',
			'next_or_number'   => 'number',
			'separator'        => '',
			'nextpagelink'     => __( 'Next page' ),
			'previouspagelink' => __( 'Previous page' ),
			'pagelink'         => '%',
			'echo'             => 1
		);
		wp_link_pages( $pages );

		
		echo '</div>';
		
		if ( is_single() ) {
			pwcc_theme_entry_meta_footer( $post );
		}
		