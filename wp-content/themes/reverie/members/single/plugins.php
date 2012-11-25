<?php

/**
 * BuddyPress - Users Plugins
 *
 * This is a fallback file that external plugins can use if the template they
 * need is not installed in the current theme. Use the actions in this template
 * to output everything your plugin needs.
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

?>

<?php get_header( 'buddypress' ); ?>

	<div id="content">
		<div class="padder">

			<?php do_action( 'bp_before_member_plugin_template' ); ?>

			<div id="item-header">
				<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>
			</div><!-- #item-header -->
				
			<div class="twelve columns">
				<div id="object-nav" class="item-list-tabs no-ajax" role="navigation">
					<ul>
						<?php bp_get_displayed_user_nav(); ?>
						<?php do_action( 'bp_member_options_nav' ); ?>
					</ul>
				</div>
			</div>
			
			<div id="blanco" class="twelve columns">
			
					<?php do_action( 'bp_before_member_body' ); ?>
	
					<div class="item-list-tabs no-ajax" id="subnav">
						<ul>
							<?php bp_get_options_nav(); ?>
							<?php do_action( 'bp_member_plugin_options_nav' ); ?>
						</ul>
					</div><!-- .item-list-tabs -->
					
					<div class="twelve columns">
						<?php do_action( 'bp_template_content' ); ?>
						<?php do_action( 'bp_after_member_body' ); ?>
						<?php do_action( 'bp_after_member_plugin_template' ); ?>
					</div>
					
			</div>
		</div><!-- .padder -->
	</div><!-- #content -->

</div> <!-- main row -->

<?php get_footer( 'buddypress' ); ?>