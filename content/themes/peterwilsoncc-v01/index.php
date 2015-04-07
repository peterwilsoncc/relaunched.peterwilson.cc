<?php
// global $wp_query;
// echo '<pre>';
// print_r( $wp_query );
// echo '</pre>';


	get_header();
	$section_hN = is_front_page() ? 'h2' : 'h1';
?>
	<div class="Page_Main ">
		<section class="Section Section-LatestPosts">
			<?php echo pwcc_theme_section_title( $section_hN ); ?>
		<?php if ( have_posts() ) : ?>
			<ul class="PostList">
			<?php while ( have_posts() ) : the_post(); ?>
				<li id="post-<?php the_ID(); ?>" <?php post_class( 'PostList_Post' ); ?>>
				<?php get_template_part( 'partials/content', get_post_format() ); ?>
				</li>
			<?php endwhile; ?>
			</ul>
			<!-- //.PostList -->
			
			
		<?php else : ?>
			<?php get_template_part( 'partials/content', 'none' ); ?>
		<?php endif; ?>
		</section>
		<?php pwcc_theme_paging_nav(); ?>
	</div>
	<!-- //.Page_Main -->
<?php
	get_sidebar();
	get_footer();
