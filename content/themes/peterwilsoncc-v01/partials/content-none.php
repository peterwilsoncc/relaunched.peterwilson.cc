<?php
	$hN = is_404() ? 'h1' : 'h2';
	
	$the_title = "Nothing found";
	
	if ( $the_title ) :
?>
	<<?php echo $hN; ?> class="Headline entry-title p-name"><?php 
	echo $the_title;
	?></<?php echo $hN; ?>>
	<?php
	endif; // ( $the_title ) :
	
	
		// pwcc_theme_entry_meta();
		echo '<div class="' . ( is_404() ? ' Section ' : ' ' ) . ' Article_Body js-Article_Body entry-content e-content util-cf">';
		
		switch ( 1 ) {
			case is_home() && current_user_can( 'publish_posts' ):
				$output = sprintf( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', admin_url( 'post-new.php' ) );
				break;
			case is_search() :
				$output = 'Sorry, but nothing matched your search terms. Please try again with different keywords.';
				break;
			
			default : 
				$output = 'It seems we can&rsquo;t find what you&rsquo;re looking for.';
				break;
		}
		
		echo $output;
		
		echo '</div>';
	?>