
	<header class="entry-header">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php echo '<p class="byline author vcard">'. __('Por', 'reverie') .' <a href="'. get_author_posts_url(get_the_author_meta('ID')) .'" rel="author" class="fn">'. get_the_author().'</a></p>'; ?> 
				<p><i id="date" class="lsf symbol">time</i> <?php the_date(); ?>
				<i id="category" class="lsf symbol">tag</i> <?php the_category(', '); ?>
				<i id="category" class="lsf symbol">comment</i> <?php comments_number( '0', 'one response', '%' );?></p>
	
				<div class="excerpt"><?php the_excerpt();?></div>
				<div class="hide-for-small">
					<?php echo get_post_meta($post->ID, 'vimeo', true); ?>
					<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium');?></a>
				</div>
			</header>
			<div class="entry-content">
				<?php if (is_archive() || is_search()) : // Only display excerpts for archives and search ?>
					<?php the_excerpt(); ?>
						<?php else : ?>
							<?php /* the_content('Seguir leyendo...'); */?>
				<?php endif; ?>
			</div>
			<footer class="row">
				<div class="derecha columns">
					<a href="<?php the_permalink(); ?>">Leer m&aacute;s <i id="arrow-right" class="lsf symbol">arrowright</i></a>
				</div>
				<!-- <?php $tag = get_the_tags(); if (!$tag) { } else { ?><p><?php the_tags(); ?></p><?php } ?> -->
			</footer>
			<div class="divider"></div>
		</article>	
	
		<?php if ( comments_open() && ! post_password_required() ) : ?>
			<div class="comments-link"></div>
		<?php endif; ?>
		
	</header><!-- .entry-header -->

	<?php if ( is_search() ) : // Only display Excerpts for Search ?>
		<div class="entry-summary"> </div><!-- .entry-summary -->
			
		<?php else : ?>
		
		<div class="entry-content"> </div><!-- .entry-content -->
		
	<?php endif; ?>

	<footer class="entry-meta">
		<?php $show_sep = false; ?>
		<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
		<span class="cat-links"></span>
		<?php endif; // End if categories ?>
	</footer><!-- #entry-meta -->
