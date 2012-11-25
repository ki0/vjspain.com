<?php

/**
 * BuddyPress - Members Directory
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

get_header( 'buddypress' ); ?>

	<?php do_action( 'bp_before_directory_members_page' ); ?>

	<div class="twelve columns veinte-botton">
		<h2>Miembros de la Comunidad</h2>
	</div>
			
	<div class="twelve columns">
		<div class="item-list-tabs" role="navigation">
			<ul>
				<li class="selected" id="members-all"><a href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_members_root_slug() ); ?>"><?php printf( __( 'All Members <span>%s</span>', 'buddypress' ), bp_get_total_member_count() ); ?></a></li>
				<?php if ( is_user_logged_in() && bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) : ?>
					<li id="members-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends/' ?>"><?php printf( __( 'My Friends <span>%s</span>', 'buddypress' ), bp_get_total_friend_count( bp_loggedin_user_id() ) ); ?></a></li>
				<?php endif; ?>
				<?php do_action( 'bp_members_directory_member_types' ); ?>
			</ul>
		</div><!-- .item-list-tabs -->
	</div>

<div class="twelve columns" id="blanco">
	<div id="comunidad" class="content">
	
		<?php do_action( 'bp_before_directory_members' ); ?>
		<form action="" method="post" id="members-directory-form" class="dir-form eight columns veinte-top">
			<?php do_action( 'bp_before_directory_members_content' ); ?>

			<div class="right" id="subnav" role="navigation">
				<ul>
					<?php do_action( 'bp_members_directory_member_sub_types' ); ?>
					<li id="members-order-select" class="last filter">
						<label for="members-order-by"><?php _e( 'Order By:', 'buddypress' ); ?></label>
						<select id="members-order-by">
							<option value="active"><?php _e( 'Last Active', 'buddypress' ); ?></option>
							<option value="newest"><?php _e( 'Newest Registered', 'buddypress' ); ?></option>
							<?php if ( bp_is_active( 'xprofile' ) ) : ?>
								<option value="alphabetical"><?php _e( 'Alphabetical', 'buddypress' ); ?></option>
							<?php endif; ?>
							<?php do_action( 'bp_members_directory_order_options' ); ?>
						</select>
					</li>
				</ul>
			</div>
			
			<div class="twelve columns">
				<?php if ( bp_has_members( bp_ajax_querystring( 'members' ) ) ) : ?>
					<p><?php bp_members_pagination_count(); ?></p>
				<?php endif; ?>
			</div>

			<div class="members dir-list twelve columns">
				<?php locate_template( array( 'members/members-loop.php' ), true ); ?>
			</div><!-- #members-dir-list -->
			
			<?php do_action( 'bp_directory_members_content' ); ?>
			<?php wp_nonce_field( 'directory_members', '_wpnonce-member-filter' ); ?>
			<?php do_action( 'bp_after_directory_members_content' ); ?>
		</form><!-- #members-directory-form -->
		<?php do_action( 'bp_after_directory_members' ); ?>
		<aside id="sidebar" class="four columns sidebar-comunity" role="complementary">
			<?php dynamic_sidebar("Comunidad"); ?>
		</aside><!-- /#sidebar -->
		</div><!-- .padder -->
	</div><!-- #content -->
	<?php do_action( 'bp_after_directory_members_page' ); ?>
</div> <!-- row main -->

<?php get_footer( 'buddypress' ); ?>