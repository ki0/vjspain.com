</div>

	<footer id="content-info" role="contentinfo">		
		<?php if ( is_page('login') || is_page('registro') ) { ?>
			<div id="main-register" class="row">
		<?php }
		else { ?>
			<div class="row">
				<?php dynamic_sidebar("Footer"); ?>
			</div>
		<?php } ?>
		<?php /* wp_nav_menu(array('theme_location' => 'utility_navigation', 'container' => false, 'menu_class' => 'eight columns footer-nav')); */ ?>
	</footer>
	
	<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
	     chromium.org/developers/how-tos/chrome-frame-getting-started -->
	<!--[if lt IE 7]>
		<script defer src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
		<script defer>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
	<![endif]-->

	<?php wp_footer(); ?>
</body>
</html>