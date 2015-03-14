		<div class="Page_Footer">
			<footer class="Footer" role="contentinfo">
				<p class="Footer_Copyright">&copy; 2014 Peter Wilson.</p>



				<?php
				$footer_nav = wp_nav_menu( array( 
					'theme_location' => 'footer',
					'container' => false,
					'menu_class' => 'util-nav',
					'fallback_cb' => false,
					'depth' => 1,
					'echo' => 0,
					'before' => ' ',
					'after' => ' '
				) );
				
				if ( $footer_nav ) :
					echo '<nav id="footer-nav" class="Footer_Nav" role="navigation">';
					echo $footer_nav;
					echo '</nav>';
					echo '<!--// .Footer_Nav-->';
				endif;
				
				?>
			</footer>
		</div>
		<!-- //.Page_Footer -->


	</div>
	<!-- //.Page -->

<?php
	wp_footer();
?>
</body>
</html>