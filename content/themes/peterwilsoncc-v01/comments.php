<?php
// need the post password to read the comments
if ( post_password_required() )
	return;

if ( pwcc_theme_have_comments() || comments_open() ):
?>
	<div class="Section Section-Comment" id="comments">
		<?php
		if ( pwcc_theme_have_comments() ):

			echo '<h2 class="util-SectionTitle">';
			printf ( _n( 'One comment', '%s comments', pwcc_theme_get_comments_number() ), pwcc_theme_get_comments_number() );
			echo '</h2>';


			?>
			<ol class="CommentList">
				<?php
					wp_list_comments( array(
						'style'       => 'ol',
						'short_ping'  => true,
						'avatar_size' => 96,
						'callback'    => 'pwcc_theme_comment_template'
					) );
				?>
			</ol><!-- //.CommentList -->


		<?php
			// Are there comments to navigate through?
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
		?>
		<nav class="Pagination" role="navigation">
			<h3 class="util-accessibility">Comment navigation</h3>
			<div class="Pagination_Direction Pagination_Direction-Previous"><?php previous_comments_link( 'Older Comments' ); ?></div>
			<div class="Pagination_Direction Pagination_Direction-Next"><?php next_comments_link( 'Newer Comments' ); ?></div>
		</nav><!-- .Pagination -->
		<?php 
			endif; // Check for comment navigation 
		?>


		<?php
		endif; // ( pwcc_theme_have_comments() ):
		?>

		<?php comment_form(); ?>

		

	</div>
	<!-- //.Section Section-Comment -->
<?php
endif; // ( pwcc_theme_have_comments() || comments_open() ):
?>