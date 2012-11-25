<?php


/*---------------------------------------------------------------------------------*/
/* KR Popular Posts - Popular Posts widget */
/*---------------------------------------------------------------------------------*/


class knowledgeringppgt_PopularPostsWidget extends WP_Widget {

   function knowledgeringppgt_PopularPostsWidget() {
  	   $widget_ops = array('description' => 'KR Popular Posts' );
       parent::WP_Widget(false, $name = __('KR Popular Posts Widget', 'knowledgeringppgt'), $widget_ops);
   }


   function widget($args, $instance) {
       extract( $args );

      if(isset($instance['kppg_numberposts']) && !empty($instance['kppg_numberposts'])){ $kppg_numberposts = $instance['kppg_numberposts'];}else {$kppg_numberposts = 5;}
		if(isset($instance['kppg_rpwidgettitle']) && !empty($instance['kppg_rpwidgettitle'])){$kppg_rpwidgettitle = $instance['kppg_rpwidgettitle'];} else {$kppg_rpwidgettitle = __('Most Popular Posts','knowledgeringppgt');}
		if(isset($instance['kppg_widget_classname']) && !empty($instance['kppg_widget_classname'])){$kppg_widget_classname = $instance['kppg_widget_classname'];} else {$kppg_widget_classname = "widget";}
		if(isset($instance['kppg_widget_container_type']) && !empty($instance['kppg_widget_container_type'])){$kppg_widget_container_type = $instance['kppg_widget_container_type'];} else {$kppg_widget_container_type = "div";}
		if(isset($instance['kppg_widget_title_header']) && !empty($instance['kppg_widget_title_header'])){$kppg_widget_title_header = $instance['kppg_widget_title_header'];} else {$kppg_widget_title_header = "";}
		if(isset($instance['kppg_widget_title_header_classname']) && !empty($instance['kppg_widget_title_header_classname'])){$kppg_widget_title_header_classname = $instance['kppg_widget_title_header_classname'];} else {$kppg_widget_title_header_classname = "";}
		if(isset($instance['show_popularity_graph']) && !empty($instance['show_popularity_graph'])){$show_popularity_graph = $instance['show_popularity_graph'];}else {$show_popularity_graph = "yes";}
		if(isset($instance['include_post_thumbnail']) && !empty($instance['include_post_thumbnail'])){$include_post_thumbnail = $instance['include_post_thumbnail'];}else {$include_post_thumbnail = "yes";}
		if(isset($instance['kppg_post_thumbnail_width']) && !empty($instance['kppg_post_thumbnail_width'])){$kppg_post_thumbnail_width = $instance['kppg_post_thumbnail_width'];}else {$kppg_post_thumbnail_width = "";}
		if(isset($instance['kppg_post_thumbnail_height']) && !empty($instance['kppg_post_thumbnail_height'])){$kppg_post_thumbnail_height = $instance['kppg_post_thumbnail_height'];}else {$kppg_post_thumbnail_height = "";}

		if(isset($instance['kppg_before_widget_code']) && !empty($instance['kppg_before_widget_code'])){$kppg_before_widget_code = $instance['kppg_before_widget_code'];}else {$kppg_before_widget_code = "";}
		if(isset($instance['kppg_after_widget_code']) && !empty($instance['kppg_after_widget_code'])){$kppg_after_widget_code = $instance['kppg_after_widget_code'];}else {$kppg_after_widget_code = "";}
		if(isset($instance['kppg_before_title_code']) && !empty($instance['kppg_before_title_code'])){$kppg_before_title_code = $instance['kppg_before_title_code'];}else {$kppg_before_title_code = "";}
		if(isset($instance['kppg_after_title_code']) && !empty($instance['kppg_after_title_code'])){$kppg_after_title_code = $instance['kppg_after_title_code'];}else {$kppg_after_title_code = "";}



	    if ( function_exists('knowledgeringppgt_most_popular_posts') ) { knowledgeringppgt_most_popular_posts($kppg_numberposts, $kppg_rpwidgettitle,$kppg_widget_title_header,$kppg_widget_classname,$kppg_widget_container_type,$kppg_widget_title_header_classname,$show_popularity_graph,$include_post_thumbnail,$kppg_post_thumbnail_width,$kppg_post_thumbnail_height,$kppg_before_widget_code,$kppg_after_widget_code,$kppg_before_title_code,$kppg_after_title_code);}

   }


   function update($new_instance, $old_instance) {
       return $new_instance;
   }

   function form($instance) {

   if(isset($instance['kppg_numberposts']) && !empty($instance['kppg_numberposts'])){
       $kppg_numberposts = esc_attr($instance['kppg_numberposts']);}else {$kppg_numberposts=5;}
        if(isset($instance['kppg_rpwidgettitle']) && !empty($instance['kppg_rpwidgettitle'])){
       $kppg_rpwidgettitle = esc_attr($instance['kppg_rpwidgettitle']);}else { $kppg_rpwidgettitle=__('Most Popular Posts','knowledgeringppgt');}
        if(isset($instance['kppg_widget_classname']) && !empty($instance['kppg_widget_classname'])){
       $kppg_widget_classname = esc_attr($instance['kppg_widget_classname']);}else { $kppg_widget_classname="widget";}
       if(isset($instance['kppg_widget_container_type']) && !empty($instance['kppg_widget_container_type'])){
	   $kppg_widget_container_type = esc_attr($instance['kppg_widget_container_type']);}else { $kppg_widget_container_type="div";}
        if(isset($instance['kppg_widget_title_header']) && !empty($instance['kppg_widget_title_header'])){
       $kppg_widget_title_header = esc_attr($instance['kppg_widget_title_header']);}else { $kppg_widget_title_header="";}
        if(isset($instance['kppg_widget_title_header_classname']) && !empty($instance['kppg_widget_title_header_classname'])){
       $kppg_widget_title_header_classname = esc_attr($instance['kppg_widget_title_header_classname']);}else { $kppg_widget_title_header_classname="";}
        if(isset($instance['show_popularity_graph']) && !empty($instance['show_popularity_graph'])){
       $show_popularity_graph = esc_attr($instance['show_popularity_graph']);}else { $show_popularity_graph="yes";}
        if(isset($instance['include_post_thumbnail']) && !empty($instance['include_post_thumbnail'])){
       $include_post_thumbnail = esc_attr($instance['include_post_thumbnail']);}else { $include_post_thumbnail="yes";}
        if(isset($instance['kppg_post_thumbnail_width']) && !empty($instance['kppg_post_thumbnail_width'])){
       $kppg_post_thumbnail_width = esc_attr($instance['kppg_post_thumbnail_width']);}else { $kppg_post_thumbnail_width="";}

       if(isset($instance['kppg_post_thumbnail_height']) && !empty($instance['kppg_post_thumbnail_height'])){
       $kppg_post_thumbnail_height = esc_attr($instance['kppg_post_thumbnail_height']);}else { $kppg_post_thumbnail_height="";}

        if(isset($instance['kppg_before_widget_code']) && !empty($instance['kppg_before_widget_code'])){
       $kppg_before_widget_code = esc_attr($instance['kppg_before_widget_code']);}else { $kppg_before_widget_code="";}

        if(isset($instance['kppg_after_widget_code']) && !empty($instance['kppg_after_widget_code'])){
       $kppg_after_widget_code = esc_attr($instance['kppg_after_widget_code']);}else { $kppg_after_widget_code="";}

       if(isset($instance['kppg_before_title_code']) && !empty($instance['kppg_before_title_code'])){
       $kppg_before_title_code = esc_attr($instance['kppg_before_title_code']);}else { $kppg_before_title_code="";}

        if(isset($instance['kppg_after_title_code']) && !empty($instance['kppg_after_title_code'])){
       $kppg_after_title_code = esc_attr($instance['kppg_after_title_code']);}else { $kppg_after_title_code="";}


       ?>
       <p>
       <label for="<?php echo $this->get_field_id('kppg_rpwidgettitle'); ?>"><?php _e('Widget Title','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_rpwidgettitle'); ?>" name="<?php echo $this->get_field_name('kppg_rpwidgettitle'); ?>" type="text" value="<?php echo $kppg_rpwidgettitle; ?>" />
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_widget_container_type'); ?>"><?php _e('Widget container type(i.e. div,li)','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_widget_container_type'); ?>" name="<?php echo $this->get_field_name('kppg_widget_container_type'); ?>" type="text" value="<?php echo $kppg_widget_container_type; ?>" />
       </label>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_widget_classname'); ?>"><?php _e('Widget container class name','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_widget_classname'); ?>" name="<?php echo $this->get_field_name('kppg_widget_classname'); ?>" type="text" value="<?php echo $kppg_widget_classname; ?>" />
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_widget_title_header'); ?>"><?php _e('Widget header type (ie H2,H3,H4...)','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_widget_title_header'); ?>" name="<?php echo $this->get_field_name('kppg_widget_title_header'); ?>" type="text" value="<?php echo $kppg_widget_title_header; ?>" />
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_widget_title_header_classname'); ?>"><?php _e('Widget header type class name','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_widget_title_header_classname'); ?>" name="<?php echo $this->get_field_name('kppg_widget_title_header_classname'); ?>" type="text" value="<?php echo $kppg_widget_title_header_classname; ?>" />
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_before_widget_code'); ?>"><?php _e('Code Before Widget','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_before_widget_code'); ?>" name="<?php echo $this->get_field_name('kppg_before_widget_code'); ?>" type="text" value="<?php echo $kppg_before_widget_code; ?>" />
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_after_widget_code'); ?>"><?php _e('Code After Widget','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_after_widget_code'); ?>" name="<?php echo $this->get_field_name('kppg_after_widget_code'); ?>" type="text" value="<?php echo $kppg_after_widget_code; ?>" />
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_before_title_code'); ?>"><?php _e('Code Before Widget Title','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_before_title_code'); ?>" name="<?php echo $this->get_field_name('kppg_before_title_code'); ?>" type="text" value="<?php echo $kppg_before_title_code; ?>" />
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_after_title_code'); ?>"><?php _e('Code After Widget Title','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_after_title_code'); ?>" name="<?php echo $this->get_field_name('kppg_after_title_code'); ?>" type="text" value="<?php echo $kppg_after_title_code; ?>" />
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_numberposts'); ?>"><?php _e('Number of posts:','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_numberposts'); ?>" name="<?php echo $this->get_field_name('kppg_numberposts'); ?>" type="text" value="<?php echo $kppg_numberposts; ?>" />
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('show_popularity_graph'); ?>"><?php _e('Display popularity graph?','knowledgeringppgt'); ?>
						<?php
							if ( ! isset( $checked ) )
								$checked = '';
							foreach ( knowledgeringppgt_yes_no_options_showpp() as $option ) {
								$radio_setting = $show_popularity_graph;

								if ( '' != $radio_setting ) {
									if ( $show_popularity_graph == $option['value'] ) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								}
								?>
								<div class="layout">
								<label class="description">
									<input type="radio" id="<?php echo $this->get_field_id('show_popularity_graph'); ?>" name="<?php echo $this->get_field_name('show_popularity_graph'); ?>" type="text" value="<?php esc_attr_e( $option['value'] ); ?>" <?php echo $checked; ?> />
									<span>
										<?php echo $option['label']; ?>
									</span>
								</label>
								</div>
								<?php
							}
						?>
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('include_post_thumbnail'); ?>"><?php _e('Show post thumbnail?','knowledgeringppgt'); ?>
						<?php
							if ( ! isset( $checked ) )
								$checked = '';
							foreach ( knowledgeringppgt_yes_no_options_showpp() as $option ) {
								$radio_setting = $include_post_thumbnail;

								if ( '' != $radio_setting ) {
									if ( $include_post_thumbnail == $option['value'] ) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								}
								?>
								<div class="layout">
								<label class="description">
									<input type="radio" id="<?php echo $this->get_field_id('include_post_thumbnail'); ?>" name="<?php echo $this->get_field_name('include_post_thumbnail'); ?>" type="text" value="<?php esc_attr_e( $option['value'] ); ?>" <?php echo $checked; ?> />
									<span>
										<?php echo $option['label']; ?>
									</span>
								</label>
								</div>
								<?php
							}
						?>
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_post_thumbnail_width'); ?>"><?php _e('Post thumbnail width','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_post_thumbnail_width'); ?>" name="<?php echo $this->get_field_name('kppg_post_thumbnail_width'); ?>" type="text" value="<?php echo $kppg_post_thumbnail_width; ?>" />
       </label>
       </p>
       <p style="margin-top:10px;">
       <label for="<?php echo $this->get_field_id('kppg_post_thumbnail_height'); ?>"><?php _e('Post thumbnail height','knowledgeringppgt'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('kppg_post_thumbnail_height'); ?>" name="<?php echo $this->get_field_name('kppg_post_thumbnail_height'); ?>" type="text" value="<?php echo $kppg_post_thumbnail_height; ?>" />
       </label>
       </p>
       <?php
   }

}

function register_knowledgeringppgt_PopularPostsWidget(){

	if ( function_exists( 'current_theme_supports' ) )
	{
		if( !(current_theme_supports('post-thumbnails') ) )
		{

			if ( function_exists( 'add_theme_support' ) )

			{
				add_theme_support( 'post-thumbnails' );
			}
		}
	}
	add_image_size( 'kppgpopularpoststhumb', 50, 50, true );

	register_widget('knowledgeringppgt_PopularPostsWidget');
}

add_action('init', 'register_knowledgeringppgt_PopularPostsWidget', 1);



?>