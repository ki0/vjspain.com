<?php
/**
 * @package fw-vimeo-videowall
 * @author fairweb
 */

class FW_widget_vimeowall extends WP_Widget {
    
    function FW_widget_vimeowall () {
    
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'fwvvw-widget', 'description' => __("Adds a Vimeo Videowall in your sidebar. The videowall may contain a user's, a group's, an album's or channel's Vimeo videos", "fwvvw") );

		/* Widget control settings. */
		$control_ops = array( 'width' => 250, 'id_base' => 'fwvvw-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'fwvvw-widget', __('Vimeo Videowall', 'fwvvw'), $widget_ops, $control_ops );
    }


    function widget( $args, $instance ) {
		extract( $args );

		/* Widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$video_args = array(
                'source' => $instance['source'],
                'type' => $instance['type'],
		'id' => $instance['id'],
		'number' => $instance['number'],
		'width' => $instance['width'],
                'height' => $instance['height'],
                'title' => $instance['v_title']);
		

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;


		fw_vimeowall_display($video_args);

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	* Update widget settings.
	*/
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['source'] = strip_tags( $new_instance['source'] );
		$instance['id'] = strip_tags( $new_instance['id'] );
                $instance['type'] = strip_tags( $new_instance['type'] );
		$instance['number'] = strip_tags( $new_instance['number'] );
		$instance['width'] = strip_tags( $new_instance['width'] );
                $instance['height'] = strip_tags( $new_instance['height'] );
		$instance['v_title'] = strip_tags( $new_instance['v_title'] );

		return $instance;
	}

        function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Videos', 'fwvvw'), 'source' => 'user', 'id' => 'petole', 'type' => 'image', 'number' => 4, 'width' => 100, 'height' => 100);
		$instance = wp_parse_args( (array) $instance, $defaults );
               ?>
        <p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title','fwvvw');?>:</label>
		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

        <p>
		<label for="<?php echo $this->get_field_id( 'source' ); ?>"><?php _e('Vimeo source type','fwvvw');?>:</label>
		<select id="<?php echo $this->get_field_id( 'source' ); ?>" name="<?php echo $this->get_field_name( 'source' ); ?>" class="widefat" style="width:100%;">
		<option <?php if ( 'user' == $instance['source'] ) echo 'selected="selected"'; ?> value="user"><?php _e('Vimeo User','fwvvw');?></option>
		<option <?php if ( 'group' == $instance['source'] ) echo 'selected="selected"'; ?> value="group"><?php _e('Vimeo Group','fwvvw');?></option>
                <option <?php if ( 'album' == $instance['source'] ) echo 'selected="selected"'; ?> value="album"><?php _e('Vimeo Album','fwvvw');?></option>
                <option <?php if ( 'channel' == $instance['source'] ) echo 'selected="selected"'; ?> value="channel"><?php _e('Vimeo Channel','fwvvw');?></option>
		</select>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php _e('Vimeo ID','fwvvw');?><br /><small>(<?php _e('user, group, album or channel ID','fwvvw');?>)</small></label>
		<input id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" value="<?php echo $instance['id']; ?>" style="width:100%;" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e('Display','fwvvw');?>:</label>
		<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" class="widefat" style="width:100%;">
		<option <?php if ( 'image' == $instance['type'] ) echo 'selected="selected"'; ?> value="image"><?php _e('clickable video thumbnails','fwvvw');?></option>
		<option <?php if ( 'video' == $instance['type'] ) echo 'selected="selected"';?> value="video"><?php _e('playable videos','fwvvw');?></option>
                <option <?php if ( 'title' == $instance['type'] ) echo 'selected="selected"';?> value="title"><?php _e('list of video titles','fwvvw');?></option>
		</select>
		</p>

		<p><?php _e('Display video title under thumbnail (irrelevant if you display a list of video titles)','fwvvw');?><br />
		<label><input type="radio" name="<?php echo $this->get_field_name( 'v_title' ); ?>" value="1" <?php if ( 1 == $instance['v_title'] ) echo 'checked="checked"'; ?> /><?php _e('Yes','fwvvw');?></label>
		<br />
		<label><input type="radio" name="<?php echo $this->get_field_name( 'v_title' ); ?>" value="0" <?php if ( 0 == $instance['v_title'] ) echo 'checked="checked"'; ?> /><?php _e('No','fwvvw');?></label>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e('Number of videos','fwvvw');?><br /></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $instance['number']; ?>" style="width:100%;" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e('Max video width','fwvvw');?><br /></label>
		<input id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" value="<?php echo $instance['width']; ?>" style="width:100%;" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e('Max video height','fwvvw');?><br /></label>
		<input id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" value="<?php echo $instance['height']; ?>" style="width:100%;" />
		</p>
                <?php
        }
}
?>