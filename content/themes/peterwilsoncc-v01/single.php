<?php
	get_header();
?>
	<div id="post-<?php the_ID(); ?>" <?php post_class( "Page_Main " ); ?>>
		<?php 
		$pwcc_format = get_post_format();
		if ( is_attachment() ) {
			$pwcc_format = 'pwcc-attachment';
		}
		while ( have_posts() ) : the_post();
			get_template_part( 'partials/content', $pwcc_format );
			pwcc_theme_post_nav();
			comments_template();
		endwhile; 
		?>
	</div>
	<!-- //.Page_Main -->
<?php
	get_sidebar();
	get_footer();
