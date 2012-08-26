<?php
/*
Template Name: Widget Home Page
*/
?>

<?php get_header() ?>

	<div id="contentFront" class="grid_10">
	
		<div class="page" id="blog-latest">

				<?php query_posts('&category_name=featured&showposts=1'); ?>
				<?php while (have_posts()) : the_post(); ?>


					<div id="post-<?php the_ID(); ?>">

						<div class="post-content">
							<div id="featuredImage">
							<a href="<?php the_permalink() ?>"><?php the_post_thumbnail('featuredthumb'); ?></a>
							</div>
						
						
							<h2 class="posttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'buddypress' ) ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

							<p class="date"><?php the_time() ?> <em><?php printf( __( 'by %s', 'buddypress' ), bp_core_get_userlink( $post->post_author ) ) ?></em></p>

							<div class="entry">
								<?php the_excerpt( __( 'Read the rest of this entry &rarr;', 'buddypress' ) ); ?>
							</div>

							<p class="postmetadata"><span class="tags"><?php the_tags( __( 'Tags: ', 'buddypress' ), ', ', '<br />'); ?></span> <span class="comments"><?php comments_popup_link( __( 'No Comments &#187;', 'buddypress' ), __( '1 Comment &#187;', 'buddypress' ), __( '% Comments &#187;', 'buddypress' ) ); ?></span></p>
						</div>

					</div>

					

				<?php endwhile; ?>
			<?php rewind_posts(); ?>
		</div>

		<div id="subcontentLeft" class="grid_10">
		
		<?php $id = get_cat_ID('featured');
			query_posts("cat=-$id"); ?>
		<?php while (have_posts()) : the_post(); ?>


					<div id="post-<?php the_ID(); ?>">

						<div class="subcontentpost">
							
						<div id="subcontentImage">
						<a href="<?php the_permalink() ?>"><?php the_post_thumbnail('subcontentthumb'); ?></a>
						</div>
						
							<h2 class="posttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'buddypress' ) ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
							
						</div>

					</div>

					

				<?php endwhile; ?>
				<?php rewind_posts(); ?>
				
				<div id="bottomLeft">
				<?php dynamic_sidebar( 'bottomLeft' ) ?>
				</div>
		</div>
				

	</div><!-- #content -->
	
	<?php locate_template( array( 'midbar.php' ), true ) ?>

	<?php locate_template( array( 'sidebar.php' ), true ) ?>
	

<?php get_footer() ?>
