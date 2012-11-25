			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				
				<header>
					<h1>aside</h1>
					<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail();?></a>			
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php echo '<p class="byline author vcard">'. __('Por', 'reverie') .' <a href="'. get_author_posts_url(get_the_author_meta('ID')) .'" rel="author" class="fn">'. get_the_author().'</a></p>'; ?> 
					<div class="excerpt"><?php the_excerpt();?></div>
					<p><?php /* the_date(); the_category(', '); */ ?></p>
					<?php /* comments_number( '0', 'one response', '%' ); */ ?>
				</header>
				
				<div class="entry-content">
					<?php if (is_archive() || is_search()) : // Only display excerpts for archives and search ?>
						<?php the_excerpt(); ?>
							<?php else : ?>
								<?php /* the_content('Seguir leyendo...'); */?>
					<?php endif; ?>
				</div>
				
				<footer>
					<?php $tag = get_the_tags(); if (!$tag) { } else { ?><p><?php the_tags(); ?></p><?php } ?>
				</footer>
				
				<div class="divider"></div>
			</article>	