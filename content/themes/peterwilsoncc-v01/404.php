<?php
	get_header();
?>
	<div id="post-f404" <?php post_class( "Page_Main " ); ?>>
			<?php get_template_part( 'partials/content', 'none' ); ?>
	</div>
	<!-- //.Page_Main -->
<?php
	get_sidebar();
	get_footer();
