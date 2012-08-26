<div id="midbar">	
	<?php rewind_posts(); ?>
	<?php query_posts('&category_name=breaking&showposts=3'); ?>
				<?php while (have_posts()) : the_post(); ?>


					<div id="post-<?php the_ID(); ?>">

						<div class="post-content">
							
						
						
							<h2 class="posttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'buddypress' ) ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

							<p class="date"><?php the_time() ?> <em><?php printf( __( 'by %s', 'buddypress' ), bp_core_get_userlink( $post->post_author ) ) ?></em></p>

							<div class="entry">
							<div id="postImage">
							<?php the_post_thumbnail('midbarthumb'); ?>
							</div>
								<?php the_excerpt(); ?>
								
							</div>

						</div>

					</div>

					

				<?php endwhile; ?>


	<?php dynamic_sidebar( 'midbar' ) ?>
</div><!-- #midbar -->


