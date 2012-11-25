<?php /* Start loop */ ?>
<?php while (have_posts()) : the_post(); ?>

		<?php if ( is_page('login') || is_page('registro') ) { ?>

			<h2><?php the_title(); ?></h2>
			<?php the_content(); ?>
			
		<?php } else { ?>
		
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
			<?php wp_link_pages(array('before' => '<nav id="page-nav"><p>' . __('Pages:', 'reverie'), 'after' => '</p></nav>' )); ?>
		
		<?php } ?>

<?php endwhile; // End the loop ?>