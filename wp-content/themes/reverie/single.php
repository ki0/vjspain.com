<?php get_header(); ?>

		<!-- Row for main content area -->
		<div id="content" class="eight columns" role="main">
	
			<div class="post-box">
				<?php get_template_part('loop', 'single'); ?>
			</div>

		</div><!-- End Content row -->
		<?php get_sidebar('sidebar');?>	

</div> <!-- div main row -->
			
<?php get_footer(); ?>