<?php get_header(); ?>

	<div id="content" class="five columns" role="main">
		<?php query_posts(array("post__not_in" =>get_option("sticky_posts")));?> <!-- exclude sticky post -->
		<?php while (have_posts()) : the_post(); ?>
			<div class="post-box">
				<?php get_template_part( 'content', get_post_format() );?>
			</div>		
		<?php endwhile; // End the loop ?>
		<?php wp_reset_query()?> <!-- reset query -->
	</div><!-- End Content row -->
	
	<div id="sticky" class="seven columns">
	<?php while (have_posts()) : the_post(); ?>
		<?php if (is_sticky() ) : ?>
				<hgroup>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php echo '<p class="byline author vcard">'. __('Por', 'reverie') .' <a href="'. get_author_posts_url(get_the_author_meta('ID')) .'" rel="author" class="fn">'. get_the_author().'</a></p>'; ?>
					<p><i id="date" class="lsf symbol">time</i> <?php the_date(); ?>
					<i id="category" class="lsf symbol">tag</i> <?php the_category(', '); ?>
					<i id="category" class="lsf symbol">comment</i> <?php comments_number( '0', '1', '%' );?></p>
					<div class="excerpt"><?php the_excerpt();?></div>
					
					<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('homepage-destacado');?></a>
					
					<footer class="row">
						<div class="derecha columns">
							<a href="<?php the_permalink(); ?>">Leer m&aacute;s <i id="arrow-right" class="lsf symbol">arrowright</i></a>
						</div>
						<!-- <?php $tag = get_the_tags(); if (!$tag) { } else { ?><p><?php the_tags(); ?></p><?php } ?> -->
					</footer>
					<div class="divider"></div>
				</hgroup>
		<?php endif; ?>
	<?php endwhile; // End the loop ?>
		<div class="row">
		
			<aside id="sidebar" class="six columns" role="complementary">
				<?php dynamic_sidebar("Right"); ?>
			</aside><!-- /#sidebar right = most viewed & vimeo-->
			
			<aside id="sidebar" class="six columns gris1" role="complementary">
				<?php dynamic_sidebar("Sidebar"); ?>
			</aside><!-- /#sidebar = social & suscribe -->
			
		</div> <!-- end row for aside -->
	</div> <!-- end sticky -->

</div><!-- End Main row star in header.php-->

<div id="more-posts">
	<div class="row">
		<a href="http://localhost/vjspain"><h2 class="blanco">Cargar m&aacute;s noticias</h2></a>
	</div>
</div>

<!-- end blog post -->

<div id="community">
	<div id="community-text" class="row">
		<div class="six columns">
			<h1 id="actividad-comunidad">Actividad de la comunidad</h1>
			<ul class="link-list">
				<li><a href="./actividad">Actividad</a></li>
				<li><a href="./miembros">Miembros</a></li>
				<li><a href="./grupos">Grupos</a></li>
				<li><a href="./foros">Foros</a></li>
			</ul>
		</div>
	</div>
	
<!-- tabs -->
	<div class="row">
		<div class="item-list-tabs activity-type-tabs" role="navigation">
			<ul>
				<?php do_action( 'bp_before_activity_type_tab_all' ); ?>
					<li class="selected" id="activity-all"><a href="<?php bp_activity_directory_permalink(); ?>" title="<?php _e( 'The public activity for everyone on this site.', 'buddypress' ); ?>"><?php printf( __( 'All Members <span>%s</span>', 'buddypress' ), bp_get_total_member_count() ); ?></a></li>
				<?php if ( is_user_logged_in() ) : ?>
					<?php do_action( 'bp_before_activity_type_tab_friends' ); ?>
						<?php if ( bp_is_active( 'friends' ) ) : ?>
							<?php if ( bp_get_total_friend_count( bp_loggedin_user_id() ) ) : ?>
								<li id="activity-friends"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . bp_get_friends_slug() . '/'; ?>" title="<?php _e( 'The activity of my friends only.', 'buddypress' ); ?>"><?php printf( __( 'My Friends <span>%s</span>', 'buddypress' ), bp_get_total_friend_count( bp_loggedin_user_id() ) ); ?></a></li>
							<?php endif; ?>	
						<?php endif; ?>
						<?php do_action( 'bp_before_activity_type_tab_groups' ); ?>
						<?php if ( bp_is_active( 'groups' ) ) : ?>
							<?php if ( bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>
								<li id="activity-groups"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . bp_get_groups_slug() . '/'; ?>" title="<?php _e( 'The activity of groups I am a member of.', 'buddypress' ); ?>"><?php printf( __( 'My Groups <span>%s</span>', 'buddypress' ), bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>
							<?php endif; ?>
						<?php endif; ?>
						<?php do_action( 'bp_before_activity_type_tab_favorites' ); ?>
						<?php if ( bp_get_total_favorite_count_for_user( bp_loggedin_user_id() ) ) : ?>
							<li id="activity-favorites"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/favorites/'; ?>" title="<?php _e( "The activity I've marked as a favorite.", 'buddypress' ); ?>"><?php printf( __( 'My Favorites <span>%s</span>', 'buddypress' ), bp_get_total_favorite_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>
						<?php endif; ?>
						<?php do_action( 'bp_before_activity_type_tab_mentions' ); ?>
				<?php endif; ?>
				<?php do_action( 'bp_activity_type_tabs' ); ?>
			</ul>
		</div>
	</div>
<!-- end tabs -->

	<div id="blanco" class="row">
		<div id="comunidad" class="eight columns">
			<?php do_action( 'bp_before_directory_activity_page' ); ?>
				<div class="padder">
					<?php do_action( 'bp_before_directory_activity' ); ?>
						<?php do_action( 'bp_before_directory_activity_content' ); ?>
						
						<?php if ( is_user_logged_in() ) : ?>
							<?php locate_template( array( 'activity/post-form.php'), true ); ?>
						<?php endif; ?>
						<?php do_action( 'template_notices' ); ?>

		
<!-- Mentions commented
						<?php do_action( 'bp_before_activity_type_tab_mentions' ); ?>

<li id="activity-mentions"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/mentions/'; ?>" title="<?php _e( 'Activity that I have been mentioned in.', 'buddypress' ); ?>"><?php _e( 'Mentions', 'buddypress' ); ?><?php if ( bp_get_total_mention_count_for_user( bp_loggedin_user_id() ) ) : ?> <strong><?php printf( __( '<span>%s new</span>', 'buddypress' ), bp_get_total_mention_count_for_user( bp_loggedin_user_id() ) ); ?></strong><?php endif; ?></a></li>
-->







			<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
				<ul>

<!-- RSS commented
					<li class="feed"><a href="<?php bp_sitewide_activity_feed_link(); ?>" title="<?php _e( 'RSS Feed', 'buddypress' ); ?>"><?php _e( 'RSS', 'buddypress' ); ?></a>
					
					</li>
-->
					<?php do_action( 'bp_activity_syndication_options' ); ?>
					<li id="activity-filter-select" class="last">
						<label for="activity-filter-by"><?php _e( 'Show:', 'buddypress' ); ?></label>
						<select id="activity-filter-by">
							<option value="-1"><?php _e( 'Everything', 'buddypress' ); ?></option>
							<option value="activity_update"><?php _e( 'Updates', 'buddypress' ); ?></option>
							<?php if ( bp_is_active( 'forums' ) ) : ?>
								<option value="new_forum_topic"><?php _e( 'Forum Topics', 'buddypress' ); ?></option>
								<option value="new_forum_post"><?php _e( 'Forum Replies', 'buddypress' ); ?></option>
							<?php endif; ?>
							<?php do_action( 'bp_activity_filter_options' ); ?>
						</select>
					</li>
				</ul>
			</div><!-- .item-list-tabs -->

			<?php do_action( 'bp_before_directory_activity_list' ); ?>

			<div class="activity" role="main">

			<?php locate_template( array( 'activity/activity-loop.php' ), true ); ?>

			</div><!-- .activity -->

			<?php do_action( 'bp_after_directory_activity_list' ); ?>
			<?php do_action( 'bp_directory_activity_content' ); ?>
			<?php do_action( 'bp_after_directory_activity_content' ); ?>
			<?php do_action( 'bp_after_directory_activity' ); ?>

		</div><!-- .padder -->
			<?php do_action( 'bp_after_directory_activity_page' ); ?>
		</div>
		
		<aside id="sidebar" class="four columns" role="complementary">
			<?php dynamic_sidebar("Comunidad"); ?>
		</aside><!-- /#sidebar -->
		
	</div>
</div>

<?php get_footer(); ?>