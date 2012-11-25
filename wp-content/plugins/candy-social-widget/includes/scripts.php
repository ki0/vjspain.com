<?php
/******************************
* script control
******************************/
function candy_social_load_scripts() {
	wp_enqueue_style('candy_social_styles', plugin_dir_url( __FILE__ ) . 'css/candy_social_style.css');
}
add_action('wp_enqueue_scripts', 'candy_social_load_scripts');
?>