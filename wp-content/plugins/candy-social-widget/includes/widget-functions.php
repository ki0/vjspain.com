<?php
/*--------------------------------------*/
/* Begin Widget Code
/*--------------------------------------*/

//create widget class
class candy_social_widget extends WP_Widget {
	
	//main vars
	var $services = array();
	var $before_list = '<ul class="candy-clearfix">';
	var $after_list = '</ul>';
	var $before_item = '<li class="candy-social-%s">';
	var $after_item = '</li>';
	
	//construct function
	function candy_social_widget() { return self::__construct(); }

	//construct the array of social icons
	function __construct() {
		$default_services = array( 
			'twitter' => __( 'Twitter' ), 
			'facebook' => __( 'Facebook' ),
			'googleplus' => __( 'GooglePlus' ),
			'dribbble' => __( 'Dribbble' ), 
			'myspace' => __( 'MySpace' ),
			'tumblr' => __( 'Tumblr' ),
			'vimeo' => __( 'Vimeo' ),
			'youtube' => __( 'YouTube' ),
			'skype' => __( 'Skype' ), 
			'github' => __( 'Github' ),
			'hackernews' => __( 'HackerNews' ),
			'pinterest' => __( 'Pinterest' ),
			'dropbox' => __( 'Dropbox' ),
			'zerply' => __( 'Zerply' ),
			'lastfm' => __( 'LastFM' ),
			'linkedin' => __( 'LinkedIn' ),
			'forrst' => __( 'Forrst' ), 
			'rss' => __( 'RSS' )
		);
		
		//apply filters
		$this->services = apply_filters( 'candy-social-icons-services', $default_services );
		$this->before_list = apply_filters( 'candy-social-icons-before-list', $this->before_list );
		$this->after_list = apply_filters( 'candy-social-icons-after-list', $this->after_list );
		$this->before_item = apply_filters( 'candy-social-icons-before-item', $this->before_item );
		$this->after_item = apply_filters( 'candy-social-icons-after-item', $this->after_item );
		
		// define widget class and description
		$candy_widget_ops = array(
			'classname' => 'candy-social-icons', 
			'description' =>  __('Widget used to show delicious social icons.', 'wpex')
		);
		
		// register the widget
		$this->WP_Widget('candy-social-icons-widget', __('Candy Social Media Icons', 'wpex'), $candy_widget_ops);
	}
	
	
	// display the widget in the theme
	function widget( $args, $instance ) {
		extract( $args );
		
		$title	= apply_filters( 'widget_title', $instance['title'] );
		$links 	= array();
		foreach( $this->services as $s=>$n ) {
			$links[$s] = esc_url( $instance[$s] );
		}
		$links = array_filter( $links );
		if( empty( $links ) )
			return false; //don't return anything if there aren't any social links added
		
		echo $before_widget;
		if( isset( $title ) && !empty( $title ) )
			echo $before_title . $title . $after_title;
		
		echo $this->before_list;
		foreach( $links as $s=>$link ) {
			printf( $this->before_item, esc_attr( $s ) );
			echo '<a href="' . $link . '" title="' . $this->services[$s] . '"><img src="'. plugins_url( '/images/'. strtolower ($this->services[$s]) .'.png', __FILE__ ) .'" alt="' . $this->services[$s] . '" /></a>';
			echo $this->after_item;
		}
		echo $this->after_list;
		
		echo $after_widget;
	}
	
	// update the widget when new options have been entered
	function update( $new, $old ) {
		$instance = $old;
		$instance['title'] = !empty( $new['title'] ) ? strip_tags( $new['title'] ) : null;
		foreach( $this->services as $s=>$n ) {
			$instance[$s] = !empty( $new[$s] ) ? esc_url( $new[$s] ) : null;
		}
		
		return $instance;
	}
	
	
	// print the widget option form on the widget management screen
	function form( $instance ) {
		$defaults = array_fill_keys( array_merge( array_keys( $this->services ), array( 'title' ) ), null );
		$instance = wp_parse_args( (array)$instance, $defaults );
?>
	<p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Widget Title' ) ?></label>
    	<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo $instance['title'] ?>"/></p>
<?php
		foreach( $this->services as $s=>$n ) {
?>
	<p><label for="<?php echo $this->get_field_id( $s ) ?>"><?php echo $n ?></label>
    	<input type="url" class="widefat" name="<?php echo $this->get_field_name( $s ) ?>" id="<?php echo $this->get_field_id( $s ) ?>" value="<?php echo esc_attr( $instance[$s] ) ?>"/></p>
<?php
		}
	}
}

// register widget
add_action( 'widgets_init', create_function( '', "return register_widget( 'candy_social_widget' );" ) );
?>