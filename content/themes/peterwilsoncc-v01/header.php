<!doctype html>
<html <?php language_attributes(); echo ' '; body_class( 'no-js' ); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php wp_head(); ?>
</head>
<!--[if lte IE 8 ]><body class="lte8"><![endif]-->
<!--[if IE 9 ]><body class="ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><body><!--<![endif]-->
	<div class="Page <?php if ( is_singular() ) { echo ' hentry h-entry '; } ?> ">
		<div class="Page_Header">
			<header class="Header vcard h-card" role="banner">
				<div class="Header_Logo">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="url u-url"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 160 160" enable-background="new 0 0 160 160" xml:space="preserve" class="Logo">
<polygon class="Logo_fill" points="114.6,57.2 91.3,83.2 91.3,95.1 114.6,69.8"/>
<path class="Logo_fill" d="M50.9 47c10.2 0 19 6.1 23.1 14.8L86.4 48h4.9v26.8L115.3 48h4.9v15.7l26.1-28.4C132 14 107.6 0 80 0 47.6 0 19.7 19.3 7.1 47H50.9zM149.2 39.9l-29 30.9v24.4l36-39.5C154.4 50.1 152.1 44.8 149.2 39.9zM30.3 142.7l55.3-61.6V57.2l-9.8 10.9c0.2 1.4 0.4 2.9 0.4 4.4 0 14-11.4 25.4-25.4 25.4h-1.8l-24.4 27.4L18 125V92.1h28.6l23.1-25.7c-2.5-8-10-13.8-18.8-13.8H4.8C1.7 61.2 0 70.4 0 80 0 105.4 11.9 128 30.3 142.7zM70.5 74L54.5 91.8C63.1 90.2 69.8 82.9 70.5 74zM119.5 102.4l-4.9 0V76.8l-24.1 25.7 -4.9 0v-13l-50.7 56.6C47.8 154.9 63.3 160 80 160c44.2 0 80-35.8 80-80 0-6.5-0.8-12.8-2.2-18.8L119.5 102.4z"/>
<polygon class="Logo_fill" points="41.5,97.8 23.6,97.8 23.6,117.7"/>
</svg></a>
				</div>
				<div class="Header_NonLogo">
					<div class="Header_Nav">
						<?php
						$header_nav = wp_nav_menu( array( 
							'theme_location' => 'header',
							'container' => false,
							'menu_class' => 'SiteNav_List',
							'fallback_cb' => false,
							'depth' => 1,
							'echo' => 0
						) );
						
						if ( $header_nav ) :
							echo '<nav id="nav" class="SiteNav" role="navigation">';
							echo $header_nav;
							echo '</nav>';
							echo '<!--// .SiteNav-->';
						endif;
						
						?>
					</div>
					<div class="Header_Bio">
						<!-- faux widget -->
						<p><span class="fn p-name">Peter Wilson</span> has been making websites since around 1995. A front-end developer with a love for CSS, Peter has worked on sites for some of the country's largest and smallest companies.</p>
					</div>
				</div>
			</header>
		</div>
		<!-- //.Page_Header -->