<?php /**
 * @package fw-vimeo-videowall
 * @author fairweb
 */
/*
Plugin Name: fw-vimeo-videowall
Plugin URI: http://www.fairweb.fr/en/my-wordpress-plugins/fw-vimeo-videowall/
Description: Displays a user, group, album or channel vimeo videowall with thumbnails or small videos in sidebar or content with pagination if needed.
Author: fairweb
Version: 1.3.4
Author URI: http://www.fairweb.fr/
*/

/*  Copyright 2010 Myriam Faulkner (email : web@fairweb.fr)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 */


$fwvvw_user_name = 'petole';
define ('FWVVW_DIR',plugin_dir_path(__FILE__));
define ('FWVVW_URL',plugin_dir_url(__FILE__));

require_once(FWVVW_DIR .'/fw-vimeo-videowall.class.php');
require_once(FWVVW_DIR .'/fw-vimeo-videowall-widget.class.php');

function fw_vimeowall_widget() {
	register_widget( 'FW_widget_vimeowall' );
}

function fw_vimeowall_display($args='', $wrapper = true) {
    $defaults = array('id' => 'petole',
		'number' => 4, 'width' => 100,
		'height' => 100, 'type' => 'image',
		'source' => 'user', 'paginate' => true, 'page' => 1, 'title' => false, 'echo' => true
	);

    $args = wp_parse_args( $args, $defaults );
    $paginate = '';
    $wall = new FW_vimeo_videowall();
    $thewall = $wall->video_wall($args, $wrapper);
    
    if ($args['echo'] == false ) {
        return $thewall;
    } 
}

function fw_vimeowall_styles() {

        if (file_exists(get_bloginfo('template_directory').'/fw-vimeo-videowall.css')) {
            $plugincss_url = get_bloginfo('template_directory').'/fw-vimeo-videowall.css';
            $plugincss_file = TEMPLATEPATH . '/fw-vimeo-videowall.css';
        } else {
            $plugincss_url = FWVVW_URL. '/fw-vimeo-videowall.css';
            $plugincss_file = FWVVW_DIR . '/fw-vimeo-videowall.css';
        }

        if ( file_exists($plugincss_file) ) {
            wp_register_style('fwvvw_styles', $plugincss_url);
            wp_enqueue_style( 'fwvvw_styles');
        }
    }

function fw_vimeowall_init() {
    wp_enqueue_script('fwvvw_js', FWVVW_URL.'/fw-vimeo-videowall.js', array('jquery'));

}

function fw_vimeowall_header() { ?>
    <script type="text/javascript">var fwvvw_ajax_handler = "<?php echo FWVVW_URL; ?>/fw-vimeo-videowall-ajax-handler.php";</script>
<?php }

function fw_vimeowall_shortcode($atts) {
    $atts['echo'] = false;
    $out = fw_vimeowall_display($atts);
return $out;
}
add_shortcode('fwvvw', 'fw_vimeowall_shortcode');
add_action( 'widgets_init', 'fw_vimeowall_widget' );
$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'fwvvw', 'wp-content/plugins/' . $plugin_dir.'/languages', $plugin_dir.'/languages' );


add_action( 'wp_head', 'fw_vimeowall_header' );
add_action( 'admin_head', 'fw_vimeowall_header' );
add_action('init', 'fw_vimeowall_init');
add_action('wp_print_styles', 'fw_vimeowall_styles');

?>