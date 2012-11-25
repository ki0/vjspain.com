<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!-- Consider adding an manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="<?php bloginfo('charset'); ?>">

	<title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
	
	<!-- Mobile viewport optimized: j.mp/bplateviewport -->
	<meta name="viewport" content="width=device-width" />
				
	<!-- Favicon and Feed -->
	<link rel="shortcut icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/favicon.png">
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> Feed" href="<?php echo home_url(); ?>/feed/">
	
	<!--  iPhone Web App Home Screen Icon -->
	<link rel="apple-touch-icon" sizes="72x72" href="<?php echo get_template_directory_uri(); ?>/images/devices/reverie-icon-ipad.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="<?php echo get_template_directory_uri(); ?>/images/devices/reverie-icon-retina.png" />
	<link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/images/devices/reverie-icon.png" />
	
	<!-- Enable Startup Image for iOS Home Screen Web App -->
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<link rel="apple-touch-startup-image" href="<?php echo get_template_directory_uri(); ?>/mobile-load.png" />

	<!-- Startup Image iPad Landscape (748x1024) -->
	<link rel="apple-touch-startup-image" href="<?php echo get_template_directory_uri(); ?>/images/devices/reverie-load-ipad-landscape.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)" />
	<!-- Startup Image iPad Portrait (768x1004) -->
	<link rel="apple-touch-startup-image" href="<?php echo get_template_directory_uri(); ?>/images/devices/reverie-load-ipad-portrait.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)" />
	<!-- Startup Image iPhone (320x460) -->
	<link rel="apple-touch-startup-image" href="<?php echo get_template_directory_uri(); ?>/images/devices/reverie-load.png" media="screen and (max-device-width: 320px)" />
	
<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>
	<!-- Start the main container -->
	<div id="container" class="container" role="document">
		<div class="gris2">
			<div class="row">
				<?php if ( is_page('login') || is_page('registro') ) { ?>
				<?php }
				else { ?>
				<div class="reverie-header">
					<h1 class="portada eight columns">vjspain <?php /* bloginfo('description'); */ ?></h1>
				</div>
				<ul class="four columns enter-login">
					<li class="right"><a href="./registro" class="enter">Registrarse</a></li>
					<li class="right"><a href="./login" class="register">Entrar</a></li>
				</div>
				<?php } ?>
			</div>
		</div>
		
		<?php if ( is_page('login') || is_page('registro') ) {  }
		else { ?>
		<div class="gris1">
			<div class="row">
				<header class="twelve columns" id="menu" role="banner">	
					<nav role="navigation">
					<?php wp_nav_menu(array('theme_location' => 'primary_navigation', 'container' => false, 'menu_class' => 'eight columns footer-nav')); ?>
					</nav>
				</header>
			</div>
		</div>
		<?php } ?>

		<!-- Row for main content area -->
		<?php if ( is_page('login') || is_page('registro') ) { ?>
		<div id="main-register" class="row"> <!-- Main row register, login -->
		<?php }
		else { ?>
		<div id="main" class="row"> <!-- Main row -->
		<?php } ?>