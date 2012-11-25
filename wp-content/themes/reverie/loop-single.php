<?php /* Start loop */ ?>
<?php while (have_posts()) : the_post();?>
	<article <?php post_class() ?> id="post-<?php the_ID(); ?>">
		<header>
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<?php echo '<p class="byline author vcard">'. __('Por', 'reverie') .' <a href="'. get_author_posts_url(get_the_author_meta('ID')) .'" rel="author" class="fn">'. get_the_author().'</a></p>'; ?>
			<p><i id="date" class="lsf symbol">time</i> <?php the_date(); ?>
			<i id="category" class="lsf symbol">tag</i> <?php the_category(', '); ?>
			<i id="category" class="lsf symbol">comment</i> <?php comments_number( '0', '1', '%' );?></p>
			
			<div class="excerpt"><?php the_excerpt();?></div>
			
			<div class="hide-for-small">
				<?php echo get_post_meta($post->ID, 'vimeo', true); ?>
				<?php the_post_thumbnail('large');?>
			</div>
			
			<div class="divider"></div>
		</header>
		
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
		<?php if (function_exists('nrelate_related')) nrelate_related(); ?>
		<footer>
			<?php wp_link_pages(array('before' => '<nav id="page-nav"><p>' . __('Pages:', 'reverie'), 'after' => '</p></nav>' )); ?>
			<p><?php the_tags(); ?></p>
			<div class="divider"></div>
		</footer>
		<?php comments_template(); ?>
	</article>
<?php endwhile; // End the loop ?>