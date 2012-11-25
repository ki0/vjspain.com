<?php get_header(); ?>

				
		<?php if ( is_page('login') || is_page('registro') ) { ?>
		
			<div id="content" class="five columns centered" role="main">
		
				<div id="basic-details-section">
					<?php get_template_part('loop', 'page'); ?>
				</div>
	
			</div><!-- End Content row -->

		
		<?php } else { ?>
			
			<!-- Row for main content area -->
			<div id="content" class="eight columns" role="main">
		
				<div class="post-box">
					<?php get_template_part('loop', 'page'); ?>
				</div>
	
			</div><!-- End Content row -->
		
			<?php get_sidebar(); ?>
		<?php } ?>

<?php get_footer(); ?>