<?php

/**
 * BuddyPress - Groups Directory
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

get_header( 'buddypress' ); ?>

	<?php do_action( 'bp_before_directory_groups_page' ); ?>

	<div class="four columns veinte-botton">
		<h2>Grupos de la Comunidad</h2>
	</div>

	<div class="two columns veinte-top">	
		<?php if ( is_user_logged_in() && bp_user_can_create_groups() ) : ?> &nbsp;
			<a class="button" href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/create' ); ?>"><?php _e( 'Create a Group', 'buddypress' ); ?></a>
		<?php endif; ?>
	</div>
	
	<div class="twelve columns">
		<div class="item-list-tabs" role="navigation">
			<ul>
				<li class="selected" id="groups-all"><a href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() ); ?>"><?php printf( __( 'All Groups <span>%s</span>', 'buddypress' ), bp_get_total_group_count() ); ?></a></li>
				<?php if ( is_user_logged_in() && bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>
					<li id="groups-personal"><a href="<?php echo trailingslashit( bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-groups' ); ?>"><?php printf( __( 'My Groups <span>%s</span>', 'buddypress' ), bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>
				<?php endif; ?>
				<?php do_action( 'bp_groups_directory_group_filter' ); ?>
			</ul>
		</div><!-- .item-list-tabs -->
	</div>	

<div class="twelve columns" id="blanco">
	<div id="comunidad" class="content">
		<?php do_action( 'bp_before_directory_groups' ); ?>
		<form action="" method="post" id="groups-directory-form" class="dir-form eight columns veinte-top">
			<?php do_action( 'bp_before_directory_groups_content' ); ?>
			<div class="five columns">
				<?php if ( bp_has_groups( bp_ajax_querystring( 'groups' ) ) ) : ?>	
					<h3><?php bp_groups_pagination_count(); ?></h3>
				<?php endif; ?>
			</div>
						
			<?php do_action( 'template_notices' ); ?>

			<div class="three columns" id="subnav" role="navigation">
				<ul>
					<?php do_action( 'bp_groups_directory_group_types' ); ?>
					<li id="groups-order-select" class="last filter">
						<label for="groups-order-by"><?php _e( 'Order By:', 'buddypress' ); ?></label>
						<select id="groups-order-by">
							<option value="active"><?php _e( 'Last Active', 'buddypress' ); ?></option>
							<option value="popular"><?php _e( 'Most Members', 'buddypress' ); ?></option>
							<option value="newest"><?php _e( 'Newly Created', 'buddypress' ); ?></option>
							<option value="alphabetical"><?php _e( 'Alphabetical', 'buddypress' ); ?></option>

							<?php do_action( 'bp_groups_directory_order_options' ); ?>
						</select>
					</li>
				</ul>
			</div>

			<div id="groups-dir-list" class="groups dir-list">
				<?php locate_template( array( 'groups/groups-loop.php' ), true ); ?>
			</div><!-- #groups-dir-list -->

			<?php do_action( 'bp_directory_groups_content' ); ?>
			<?php wp_nonce_field( 'directory_groups', '_wpnonce-groups-filter' ); ?>
			<?php do_action( 'bp_after_directory_groups_content' ); ?>			
		</form><!-- #groups-directory-form -->
		<?php do_action( 'bp_after_directory_groups' ); ?>	
		
		<aside id="sidebar" class="four columns sidebar-comunity" role="complementary">
			<?php dynamic_sidebar("Comunidad"); ?>
		</aside><!-- /#sidebar -->
	</div>
</div>

	<?php do_action( 'bp_after_directory_groups_page' ); ?>

</div> <!-- row main -->	

<?php get_footer( 'buddypress' ); ?>