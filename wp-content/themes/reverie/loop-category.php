<?php /* Start loop */ ?>
<?php while (have_posts()) : the_post(); ?>
	<article <?php post_class() ?> id="post-<?php the_ID(); ?>">
		<header>
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php echo '<p class="byline author vcard">'. __('Por', 'reverie') .' <a href="'. get_author_posts_url(get_the_author_meta('ID')) .'" rel="author" class="fn">'. get_the_author().'</a></p>'; ?>
			<p><i id="date" class="lsf symbol">time</i> <?php the_date(); ?>
			<i id="category" class="lsf symbol">tag</i> <?php the_category(', '); ?>
			<i id="category" class="lsf symbol">comment</i> <?php comments_number( '0', '1', '%' );?></p>
			<div class="excerpt"><?php the_excerpt();?></div>
			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail();?></a>
		</header>
		
		<footer class="row">
			<div class="derecha columns">
			<a href="<?php the_permalink(); ?>">Leer m&aacute;s <i id="arrow-right" class="lsf symbol">arrowright</i></a>
			</div>
			<!-- <?php $tag = get_the_tags(); if (!$tag) { } else { ?><p><?php the_tags(); ?></p><?php } ?> -->
			</footer>
			<div class="divider"></div>
			<!-- <p><?php the_tags(); ?></p> -->
		</footer>
	</article>

<?php wp_link_pages(array('before' => '<nav id="page-nav"><p>' . __('Pages:', 'reverie'), 'after' => '</p></nav>' )); ?>

<?php endwhile; // End the loop ?>

<div class="navigation"><p><?php posts_nav_link(); ?></p></div>