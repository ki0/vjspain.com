<?php
define( 'BP_DTHEME_DISABLE_CUSTOM_HEADER', true );
define( 'BP_SILENCE_THEME_NOTICE', true );
add_theme_support( 'post-thumbnails' );
add_image_size( 'featuredthumb', 405, 175, true );
add_image_size( 'midbarthumb', 150, 150, true );
add_image_size( 'subcontentthumb', 190, 100, true );

/* Register the widget columns */
register_sidebars( 1,
	array(
		'name' => 'midbar',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>'
	)
);

register_sidebars( 1,
	array(
		'name' => 'bottomLeft',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>'
	)
);

?>