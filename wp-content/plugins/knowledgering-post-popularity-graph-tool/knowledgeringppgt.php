<?php if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*

Plugin Name: KR Popular Posts
Plugin URI: http://www.knowledgering.com
Description: KR Popular Posts displays a horizontal graph that represents the popularity of a post based on comments, views, ratings, facebook likes and retweets. Ratings are measured only if GD Star Rating plugin is installed and active. Views look for a "views" custom field and assumes it is tracking the views of the page so any plugin that stores view count using "views" as the post meta will work ( future update will allow you to specify the custom field to use). Facebook likes and retweets require you to setup an hourly cronjob to fetch likes and retweet values for your posts. Post popularity is shown with a horizontal bar graph below the post if you check "Use content filtering to append popularity graph". Otherwise you can manually insert the function code where you prefer within your post template page. You can also show a list under the main post popularity graph which displays a graph for the individual item percentages (ie views, ratings, comments, retweets, facebook likes) by checking "Show itemized popularity graphs under main graph". A post's overall popularity and itemized popularity value will rise or fall in relation to the other posts on your site. That means a post with a 50% popularity rating today can have a 30% popularity rating next week if another post increases in views, comments, ratings, retweets, facebook likes by a large enough number to push down the value of the example post's popularity rating. Includes a popular posts widget, with thumbnails or without thumbnails, for your sidebar.
Version: 1.6
Author: knowledgering.com
Author URI: http://www.knowledgering.com

*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*  Copyright 2011  Knowledgering.com  (email : knowledgeringcontact@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down

if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content'); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down

$wpcontenturl=WP_CONTENT_URL;
$wpcontentdir=WP_CONTENT_DIR;
$wpinc=WPINC;

$knowledgeringppgt_plugin_path = WP_CONTENT_DIR.'/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
$knowledgeringppgt_plugin_url = WP_CONTENT_URL.'/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));


	if( file_exists("$knowledgeringppgt_plugin_path/knowledgeringppgt-widgets.php") )
	{
		require("$knowledgeringppgt_plugin_path/knowledgeringppgt-widgets.php");
	}
	if( file_exists("$knowledgeringppgt_plugin_path/knowledgeringppgt-functions.php") )
	{
		require("$knowledgeringppgt_plugin_path/knowledgeringppgt-functions.php");
	}


add_action('wp_print_styles', 'knowledgeringppgt_addcss');
function knowledgeringppgt_addcss()
{
    global $knowledgeringppgt_plugin_path,$knowledgeringppgt_plugin_url;

    $knowledgeringppgtstylesheet="knowledgeringppgt.css";
    if(file_exists(get_template_directory() .'/css/'.$knowledgeringppgtstylesheet))
    {
		$myknowledgeringppgtStyleUrl = get_template_directory_uri() . '/css/' .$knowledgeringppgtstylesheet;
    }
    elseif(file_exists(get_stylesheet_directory() .'/css/'.$knowledgeringppgtstylesheet))
    {
		$myknowledgeringppgtStyleUrl = get_stylesheet_directory_uri() . '/css/' .$knowledgeringppgtstylesheet;
    }
    elseif(file_exists($knowledgeringppgt_plugin_path .'css/'.$knowledgeringppgtstylesheet))
    {
		$myknowledgeringppgtStyleUrl = $knowledgeringppgt_plugin_url . 'css/' .$knowledgeringppgtstylesheet;
    }
    if (0 < strlen('myknowledgeringppgtStyleUrl'))
    {
		wp_register_style('myknowledgeringppgtStyleSheets', $myknowledgeringppgtStyleUrl);
		wp_enqueue_style( 'myknowledgeringppgtStyleSheets');
    }
}

$kppg_options = get_option( 'knowledgeringppgt_plugin_options' );

function knowledgeringppgt_show_post_popularity()
{

	if(is_single()){

		global $post,$wpdb,$table_prefix;
		$mymaxviews=get_max_views();
		$mymaxcoms=get_max_comments();
		$ppg_pr_object='';
		$ppg_pviews='';
		$ppg_comments='';
		$ppg_retweets='';
		$kppg_post_graph_width=0;
		$kppg_post_popularity=0;
		$kppg_post_popularity_vars=array();
		$kppg_wp_gdsr_user_votes='';
		$kppg_wp_gdsr_visitor_votes='';
		$kppg_wp_gdsr_uvs_vvs='';
		$kppgwpgdsruvsvvshigh='';
		$kppg_view_percentage=0;
		$kppg_ratings_percentage=0;
		$kppg_facebooklikes_percentage=0;
		$kppg_retweets_percentage=0;
		$kppg_post_graph_width=0;
		$kppg_ratings_graph_width=0;
		$kppg_comments_graph_width=0;
		$kppg_retweets_graph_width=0;
		$kppg_fblikes_graph_width=0;
		$kppg_views_graph_width=0;

		global $kppg_options;

		if ( isset( $kppg_options['graph_color'] ) && !empty( $kppg_options['graph_color'] ) ) { $kppg_graph_color=$kppg_options['graph_color']; } else { $kppg_graph_color="FF0000";}
		if ( isset( $kppg_options['graph_height'] ) && !empty( $kppg_options['graph_height'] ) ) { $kppg_graph_height=$kppg_options['graph_height'];} else { $kppg_graph_height="10";}

		$kppg_graph_color=str_replace("#","",$kppg_graph_color);

		$kppgPID=$post->ID;
		$kppgPcomcount=$post->comment_count;

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Get the post rating data
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if(function_exists('wp_gdsr_rating_article'))
		{

			$kppg_ratings_percentage=knowledgeringppgt_get_post_ratings_percentage($kppgPID);

			$kppg_ratings_percentage_arr=round($kppg_ratings_percentage,2);
			$kppg_ratings_graph_width=round($kppg_ratings_percentage,0);
			$kppg_post_popularity_vars[]=$kppg_ratings_percentage_arr;
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Get total views
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if( get_post_meta($kppgPID,'views', true) )
		{

			$kppg_view_percentage=knowledgeringppgt_get_post_views_percentage($kppgPID);

			$kppg_view_percentage_arr=round($kppg_view_percentage,2);
			$kppg_views_graph_width=round($kppg_view_percentage,0);
			$kppg_post_popularity_vars[]=$kppg_view_percentage_arr;

		}

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Get retweet percentage
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if ( isset( $kppg_options['yes_no_options_rtw'] ) && !empty( $kppg_options['yes_no_options_rtw'] ) && ($kppg_options['yes_no_options_rtw'] == 'yes') )
		{

			$kppg_retweets_percentage=knowledgeringppgt_get_retweets_percentage($kppgPID);

			$kppg_retweets_percentage_arr=round($kppg_retweets_percentage,2);
			$kppg_retweets_graph_width=round($kppg_retweets_percentage,0);
			$kppg_post_popularity_vars[]=$kppg_retweets_percentage_arr;

		}

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Get facebook likes percentage
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if ( isset( $kppg_options['yes_no_options_fbl'] ) && !empty( $kppg_options['yes_no_options_fbl'] ) && ($kppg_options['yes_no_options_fbl'] == 'yes') )
		{

			$kppg_facebooklikes_percentage=knowledgeringppgt_get_fblikes_percentage($kppgPID);

			$kppg_facebooklikes_percentage_arr=round($kppg_facebooklikes_percentage,2);
			$kppg_fblikes_graph_width=round($kppg_facebooklikes_percentage,0);
			$kppg_post_popularity_vars[]=$kppg_facebooklikes_percentage_arr;

		}


		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Get the total post comments and calculate comments popularity if comments open
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if ('open' == $post->comment_status)
		{

			$kppg_comments_percentage=knowledgeringppgt_get_comments_percentage($kppgPID,$kppgPcomcount);

			$kppg_comments_percentage_arr=round($kppg_comments_percentage,2);
			$kppg_comments_graph_width=round($kppg_comments_percentage,0);
			$kppg_post_popularity_vars[]=$kppg_comments_percentage_arr;
		}


		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Calculate the post popularity percentage based on values in array $kppg_post_popularity_vars
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		$kppg_post_popularity_arritems=count($kppg_post_popularity_vars);
		$kppg_post_popularity_arrsum=array_sum($kppg_post_popularity_vars);

		$kppg_post_popularity=($kppg_post_popularity_arrsum/$kppg_post_popularity_arritems);
		$kppg_post_popularity=round($kppg_post_popularity,2);


		if($kppg_post_popularity <= 0){$kppg_graph_bg="background:none;";}
		else{$kppg_graph_bg="background:#$kppg_graph_color;";$kppg_post_graph_width=round($kppg_post_popularity,0);}

		if($kppg_post_graph_width < 1){ $kppg_post_graph_height=0;}else{$kppg_post_graph_height = $kppg_graph_height;}
		if($kppg_ratings_graph_width < 1){ $kppg_ratings_graph_height=0;}else{$kppg_ratings_graph_height = $kppg_graph_height;}
		if($kppg_views_graph_width < 1){ $kppg_views_graph_height=0;}else{$kppg_views_graph_height = $kppg_graph_height;}
		if($kppg_retweets_graph_width < 1){ $kppg_retweets_graph_height=0;}else{$kppg_retweets_graph_height = $kppg_graph_height;}
		if($kppg_fblikes_graph_width < 1){ $kppg_fblikes_graph_height=0;}else{$kppg_fblikes_graph_height = $kppg_graph_height;}
		if($kppg_comments_graph_width < 1){ $kppg_comments_graph_height=0;}else{$kppg_comments_graph_height = $kppg_graph_height;}


		$kppg_post_graph='<div style="clear:both;">';
		$kppg_post_graph.=__("Post Popularity ","knowledgeringppgt");
		$kppg_post_graph.=$kppg_post_popularity .'% &nbsp;';

		$kppg_post_graph.='<div style="width:'.$kppg_post_graph_width.'px;'.$kppg_graph_bg.';height:'.$kppg_post_graph_height.'px;">';
		$kppg_post_graph.='</div>';

		if ( isset( $kppg_options['display_itemized_popularity_chart'] ) && '0' != $kppg_options['display_itemized_popularity_chart'] && ( $kppg_options['display_itemized_popularity_chart'] == "yes") ) {

		$kppg_post_graph.='<div style="margin:10px 0;">';
		$kppg_post_graph.=__("Popularity Breakdown","knowledgeringppgt");
		$kppg_post_graph.='<div style="margin-top:5px;">';



			if( get_post_meta($kppgPID,'views', true) ) {
			$kppg_post_graph.=__("Views ","knowledgeringppgt");
			$kppg_post_graph.=$kppg_view_percentage_arr .'% &nbsp;';
			$kppg_post_graph.='<div style="width:'.$kppg_views_graph_width.'px;'.$kppg_graph_bg.';height:'.$kppg_views_graph_height.'px;">';
			$kppg_post_graph.='</div>';
			}

			if ('open' == $post->comment_status){
			$kppg_post_graph.=__("Comments ","knowledgeringppgt");
			$kppg_post_graph.=$kppg_comments_percentage_arr .'% &nbsp;';
			$kppg_post_graph.='<div style="width:'.$kppg_comments_graph_width.'px;'.$kppg_graph_bg.';height:'.$kppg_comments_graph_height.'px;">';
			$kppg_post_graph.='</div>';
			}

			if(function_exists('wp_gdsr_rating_article')){
			$kppg_post_graph.=__("Ratings ","knowledgeringppgt");
			$kppg_post_graph.=$kppg_ratings_percentage_arr .'% &nbsp;';
			$kppg_post_graph.='<div style="width:'.$kppg_ratings_graph_width.'px;'.$kppg_graph_bg.';height:'.$kppg_ratings_graph_height.'px;">';
			$kppg_post_graph.='</div>';
			}


			if ( isset( $kppg_options['yes_no_options_rtw'] ) && !empty( $kppg_options['yes_no_options_rtw'] ) && ($kppg_options['yes_no_options_rtw'] == 'yes') ) {
			$kppg_post_graph.=__("Retweets ","knowledgeringppgt");
			$kppg_post_graph.=$kppg_retweets_percentage_arr .'% &nbsp;';
			$kppg_post_graph.='<div style="width:'.$kppg_retweets_graph_width.'px;'.$kppg_graph_bg.';height:'.$kppg_retweets_graph_height.'px;">';
			$kppg_post_graph.='</div>';
			}

			if ( isset( $kppg_options['yes_no_options_fbl'] ) && !empty( $kppg_options['yes_no_options_fbl'] ) && ($kppg_options['yes_no_options_fbl'] == 'yes') ) {
			$kppg_post_graph.=__("Facebook Likes ","knowledgeringppgt");
			$kppg_post_graph.=$kppg_facebooklikes_percentage_arr .'% &nbsp;';
			$kppg_post_graph.='<div style="width:'.$kppg_fblikes_graph_width.'px;'.$kppg_graph_bg.';height:'.$kppg_fblikes_graph_height.'px;">';
			$kppg_post_graph.='</div>';
			}

			$kppg_post_graph.='</div></div>';


		}

		$kppg_post_graph.='</div>';



			if ( isset( $kppg_options['show_graph_filter_manual_insert'] ) && '0' != $kppg_options['show_graph_filter_manual_insert'] && ( $kppg_options['show_graph_filter_manual_insert'] == "filter") ) {
			return $kppg_post_graph;
			}

			else {echo $kppg_post_graph;}



	}
}

if ( isset( $kppg_options['show_graph_filter_manual_insert'] ) && '0' != $kppg_options['show_graph_filter_manual_insert'] && ( $kppg_options['show_graph_filter_manual_insert'] == "filter") ) {
add_filter('the_content','knowledgeringppgt_pg');
}

if ( isset( $kppg_options['yes_no_options_cl'] ) && '0' != $kppg_options['yes_no_options_cl'] && ( $kppg_options['yes_no_options_cl'] == "no") ) {
add_action('wp_footer','knowledgeringppgt_dcl');
}

// Functions start

// Get post retweets

function knowledgeringppgt_tweetCount($url) {

	$twitterEndpoint = "http://urls.api.twitter.com/1/urls/count.json?url=$url";
	$rtwcarr='';

	if(function_exists('wp_remote_retrieve_body')){
			$fileData = wp_remote_retrieve_body(wp_remote_get($twitterEndpoint));
		}
	elseif(function_exists('file_get_contents')){
		$fileData = @file_get_contents(sprintf($twitterEndpoint, $url));
		}

		if($fileData !== false)
		{
			if($fileData)
			{
				$jsonrtw = json_decode($fileData, true);
				unset($fileData); // free memory

				foreach($jsonrtw as $jsonrtwitem)
				{
					$rtwc=$jsonrtwitem['count'];
				}
					if(isset($rtwc) && !empty($rtwc))
					{
						return $rtwc;
					}
					else { return 0;}
			}
			else { return 0;}
		}

}


//  Get post facebook likes value

function knowledgeringppgt_fbCount($url) {
$fileData='';
$fbltc='';

$facebookEndpoint="http://api.ak.facebook.com/restserver.php?v=1.0&method=fql.query&query=select%20url,%20total_count%20from%20link_stat%20where%20url%20in%20('".$url."')&format=json";

	if(function_exists('wp_remote_retrieve_body'))
	{
		$fileData = wp_remote_retrieve_body(wp_remote_get($facebookEndpoint));
		$ujs=1;
	}
	elseif(function_exists('file_get_contents')){
		$fileData = @file_get_contents($facebookEndpoint);
		$ujs=0;
	}

		if($fileData !== false)
		{

			if($fileData && ($ujs == 0) )
			{

				$ppgfbCount = simplexml_load_string($fileData);
				$ppgfbCount->link_stat->total_count;

				if(is_bool($ppgfbCount))
				{
					return '0';
				}else{
					return $ppgfbCount;
				 }
			}
			elseif($fileData && ($ujs == 1))
			{
				$jsonfc = json_decode($fileData, true);
				unset($fileData); // free memory

				//if(is_array($jsonfc)){$fbltcarr=$jsonfc[0];
				foreach($jsonfc as $jsonfcitem)
				{
					$fbltc=$jsonfcitem['total_count'];
				}
					if(isset($fbltc) && !empty($fbltc))
					{
						return $fbltc;
					}
					else { return 0; }
			}
			else
			{
				return 0;
			}
		}
}



function knowledgeringppgt_dcl(){
?>

<div id="fixme">Post Popularity Graphing by <a href="http://knowledgering.com/">Knowledge Ring</a></div>

<?php

}

function knowledgeringppgt_pg($content){

$kppg_post_graph=knowledgeringppgt_show_post_popularity();
if(is_single()){
if(isset($kppg_post_graph) && !empty($kppg_post_graph)){
$content .= $kppg_post_graph;
}
}

return $content;

}


// Setup the admin options

add_action( 'admin_init', 'knowledgeringppgt_plugin_options_init' );
add_action( 'admin_menu', 'knowledgeringppgt_add_menu_page' );

/**
 * Add theme options page styles
 */
wp_register_style( 'knowledgeringppgt', get_template_directory_uri() . '/inc/theme-options.css', '', '0.1' );
if ( isset( $_GET['page'] ) && $_GET['page'] == 'theme_options' ) {
	wp_enqueue_style( 'knowledgeringppgt' );
}

/**
 * Init plugin options to white list our options
 */
function knowledgeringppgt_plugin_options_init(){
	register_setting( 'knowledgeringppgt_options', 'knowledgeringppgt_plugin_options', 'knowledgeringppgt_plugin_options_validate' );
}

/**
 * Load up the menu page
 */
function knowledgeringppgt_add_menu_page() {

		add_menu_page('KR Popular Posts', 'KR Popular Posts', 'activate_plugins', 'knowledgringppgt.php', 'knowledgeringppgt_plugin_options_do_page', '');

}

/**
 * Return array for graph color
 */
function knowledgeringppgt_graph_colors() {
	$graph_colors = array(
		'default' => array(
			'value' =>	'FF0000',
			'label' => __( 'FF0000' )
		),

	);

	return $graph_colors;
}

/**
 * Return array for graph height
 */
function knowledgeringppgt_graph_height() {
	$graph_height = array(
		'default' => array(
			'value' =>	'10',
			'label' => __( '10' )
		),

	);

	return $graph_height;
}

/**
 * Return array for credit link options
 */
function knowledgeringppgt_yes_no_options_credit_links() {
	$yes_no_options_cl = array(
		'yes' => array(
			'value' => 'yes',
			'label' => __( 'Yes, I prefer not to credit plugin author' )
		),
		'no' => array(
			'value' => 'no',
			'label' => __( 'No, I would like to credit plugin author' ),
		),
	);

	return $yes_no_options_cl;
}

/**
 * Return array for facebook like options
 */
function knowledgeringppgt_yes_no_options_fbl() {
	$yes_no_options_fbl = array(
		'yes' => array(
			'value' => 'yes',
			'label' => __( 'Yes, include facebook likes in calculation' )
		),
		'no' => array(
			'value' => 'no',
			'label' => __( 'No, do not include facebook likes in calculation' ),
		),
	);

	return $yes_no_options_fbl;
}


/**
 * Return array for retweet options
 */
function knowledgeringppgt_yes_no_options_rtw() {
	$yes_no_options_rtw = array(
		'yes' => array(
			'value' => 'yes',
			'label' => __( 'Yes, include retweets in caluculation' )
		),
		'no' => array(
			'value' => 'no',
			'label' => __( 'No, do not include retweets in calculation' ),
		),
	);

	return $yes_no_options_rtw;
}


/**
 * Retweets/Likes high value
 */
function knowledgeringppgt_retweets_likes_high_value() {
	$graph_height = array(
		'default' => array(
			'value' =>	'',
			'label' => __( '' )
		),

	);

	return $graph_height;
}

/**
 * Return array for retweet options
 */
function knowledgeringppgt_show_graph_filter_manual_insert() {
	$show_graph_filter_manual_insert = array(
		'filter' => array(
			'value' => 'filter',
			'label' => __( 'Use content filtering to append popularity graph' )
		),
		'manual' => array(
			'value' => 'manual',
			'label' => __( 'I will manually insert function code where I want post popularity graph to display <br/> <b>Code to insert:</b> &lt;?php knowledgeringppgt_show_post_popularity();?&gt;' ),
		),
	);

	return $show_graph_filter_manual_insert;
}


/**
 * Return array for display itemized popularity chart
 */
function knowledgeringppgt_display_itemized_popularity_chart() {
	$display_itemized_popularity_chart = array(
		'yes' => array(
			'value' => 'yes',
			'label' => __( 'Show itemized popularity graphs under main graph' )
		),
		'no' => array(
			'value' => 'no',
			'label' => __( 'Only show overall popularity graph' ),
		),
	);

	return $display_itemized_popularity_chart;
}

/**
 * Show percentage value? For widget
 */
function knowledgeringppgt_yes_no_options_showpp() {
	$yes_no_options_showpp = array(
		'yes' => array(
			'value' => 'yes',
			'label' => __( 'Yes' )
		),
		'no' => array(
			'value' => 'no',
			'label' => __( 'No' ),
		),
	);

	return $yes_no_options_showpp;
}

/**
 * Set default options
 */
function knowledgeringppgt_default_options() {
	$options = get_option( 'knowledgeringppgt_plugin_options' );

	if ( ! isset( $options['graph_color'] ) ) {
		$options['graph_color'] = 'FF0000';
		update_option( 'knowledgeringppgt_plugin_options', $options );
	}

	if ( ! isset( $options['graph_height'] ) ) {
		$options['graph_height'] = '10';
		update_option( 'knowledgeringppgt_plugin_options', $options );
	}

	if ( ! isset( $options['yes_no_options_cl'] ) ) {
		$options['yes_no_options_cl'] = 'no';
		update_option( 'knowledgeringppgt_plugin_options', $options );
	}

	if ( ! isset( $options['yes_no_options_fbl'] ) ) {
		$options['yes_no_options_fbl'] = 'no';
		update_option( 'knowledgeringppgt_plugin_options', $options );
	}

	if ( ! isset( $options['yes_no_options_rtw'] ) ) {
		$options['yes_no_options_rtw'] = 'no';
		update_option( 'knowledgeringppgt_plugin_options', $options );
	}

	if ( ! isset( $options['retweets_likes_high_value'] ) ) {
		$options['retweets_likes_high_value'] = '';
		update_option( 'knowledgeringppgt_plugin_options', $options );
	}

	if ( ! isset( $options['show_graph_filter_manual_insert'] ) ) {
		$options['show_graph_filter_manual_insert'] = 'filter';
		update_option( 'knowledgeringppgt_plugin_options', $options );
	}

	if ( ! isset( $options['display_itemized_popularity_chart'] ) ) {
		$options['display_itemized_popularity_chart'] = 'yes';
		update_option( 'knowledgeringppgt_plugin_options', $options );
	}

}
add_action( 'init', 'knowledgeringppgt_default_options' );

/**
 * Create the options page
 */
function knowledgeringppgt_plugin_options_do_page() {

	if ( ! isset( $_REQUEST['updated'] ) )
		$_REQUEST['updated'] = false;

	?>
	<div class="wrap">
		<?php screen_icon(); echo "<h2>" . sprintf( __( 'Knowledgering Post Popularity Graph Tool Admin Options', 'knowledgeringppgt' ), '' )
		 . "</h2>"; ?>

		<?php if ( false !== $_REQUEST['updated'] ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'Options saved', 'knowledgeringppgt' ); ?></strong></p></div>
		<?php endif; ?>

<?php $cronurl=home_url().'?krppcron=1';?>
<div class="updated fade"><?php _e('For the KR popular posts sidebar widget to work efficiently and also for the sidebar widget to count retweets and facebook likes you must set up a cron job and enter: <b>wget -O /dev/null ' . $cronurl .'</b> into the command field. You should set it to run hourly for more up-to-date results.', '');?></div>

		<form method="post" action="options.php">
			<?php settings_fields( 'knowledgeringppgt_options' ); ?>
			<?php $options = get_option( 'knowledgeringppgt_plugin_options' ); ?>

			<table class="form-table">

				<?php
				/**
				 * Knowledgering Post Popularity Graph Tool Graph Color
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Graph Color', 'knowledgeringppgt' ); ?></th>
					<td>
						<input id="knowledgeringppgt_plugin_options[graph_color]" class="regular-text" type="text" name="knowledgeringppgt_plugin_options[graph_color]" value="<?php if(isset($options['graph_color']) && !empty($options['graph_color'])){ echo esc_attr( $options['graph_color'] );}?>" />

						<label class="description" for="knowledgeringppgt_plugin_options[graph_color]"><?php _e( 'Enter the hex value for popularity graph color. Do not include #', 'knowledgeringppgt' ); ?></label>
					</td>
				</tr>


				<?php
				/**
				 * Knowledgering Post Popularity Graph Tool Graph Height
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Graph Height', 'knowledgeringppgt' ); ?></th>
					<td>
						<input id="knowledgeringppgt_plugin_options[graph_height]" class="regular-text" type="text" name="knowledgeringppgt_plugin_options[graph_height]" value="<?php if(isset($options['graph_height']) && !empty($options['graph_height'])){echo esc_attr( $options['graph_height'] );}?>" />

						<label class="description" for="knowledgeringppgt_plugin_options[graph_height]"><?php _e( 'Enter value for graph height.', 'knowledgeringppgt' ); ?></label>
					</td>
				</tr>

				<?php
				/**
				 * Knowledgering Post Popularity Graph Tool Include Facebook likes
				 */
				?>
				<tr valign="top" id="knowledgeringppgt-facebook-likes"><th scope="row"><?php _e( 'Include Facebook Likes', 'knowledgeringppgt' ); ?></th>
					<td>
						<fieldset><legend class="screen-reader-text"><span><?php _e( 'Include Facebook Likes', 'knowledgeringppgt' ); ?></span></legend>
						<?php
							if ( ! isset( $checked ) )
								$checked = '';
							foreach ( knowledgeringppgt_yes_no_options_fbl() as $option ) {
								$radio_setting = $options['yes_no_options_fbl'];

								if ( '' != $radio_setting ) {
									if ( $options['yes_no_options_fbl'] == $option['value'] ) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								}
								?>
								<div class="layout">
								<label class="description">
									<input type="radio" name="knowledgeringppgt_plugin_options[yes_no_options_fbl]" value="<?php esc_attr_e( $option['value'] ); ?>" <?php echo $checked; ?> />
									<span>
										<?php echo $option['label']; ?>
									</span>
								</label>
								</div>
								<?php
							}
						?>
						</fieldset>
					</td>
				</tr>


				<?php
				/**
				 * Knowledgering Post Popularity Graph Tool Include Retweet count
				 */
				?>
				<tr valign="top" id="knowledgeringppgt-retweets"><th scope="row"><?php _e( 'Include Retweets', 'knowledgeringppgt' ); ?></th>
					<td>
						<fieldset><legend class="screen-reader-text"><span><?php _e( 'Include Retweets', 'knowledgeringppgt' ); ?></span></legend>
						<?php
							if ( ! isset( $checked ) )
								$checked = '';
							foreach ( knowledgeringppgt_yes_no_options_rtw() as $option ) {
								$radio_setting = $options['yes_no_options_rtw'];

								if ( '' != $radio_setting ) {
									if ( $options['yes_no_options_rtw'] == $option['value'] ) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								}
								?>
								<div class="layout">
								<label class="description">
									<input type="radio" name="knowledgeringppgt_plugin_options[yes_no_options_rtw]" value="<?php esc_attr_e( $option['value'] ); ?>" <?php echo $checked; ?> />
									<span>
										<?php echo $option['label']; ?>
									</span>
								</label>
								</div>
								<?php
							}
						?>
						</fieldset>
					</td>
				</tr>


				<?php
				/**
				 * Knowledgering Post Popularity Graph Tool retweets/likes high value
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Retweet/Likes Arbitrary high value', 'knowledgeringppgt' ); ?></th>
					<td>
						<input id="knowledgeringppgt_plugin_options[retweets_likes_high_value]" class="regular-text" type="text" name="knowledgeringppgt_plugin_options[retweets_likes_high_value]" value="<?php if(isset($options['retweets_likes_high_value']) && !empty($options['retweets_likes_high_value'])){echo esc_attr( $options['retweets_likes_high_value'] );}?>" />

						<label class="description" for="knowledgeringppgt_plugin_options[retweets_likes_high_value]"><?php _e( 'Enter value for retweet/likes high value. If you want to calculate the high value based on retweet count for the post with the most retweets leave this blank but keep in mind that if the post with the most retweets or most likes has 1 retweet or 1 like for example, retweet percentages and facebook likes percentages will be very high', 'knowledgeringppgt' ); ?></label>
					</td>
				</tr>


				<?php
				/**
				 * Knowledgering Post Popularity Graph Tool graph display option
				 */
				?>
				<tr valign="top" id="knowledgeringppgt-filter-manual"><th scope="row"><?php _e( 'Automatic/Manual graph display', 'knowledgeringppgt' ); ?></th>
					<td>
						<fieldset><legend class="screen-reader-text"><span><?php _e( 'Automatic/Manual graph display', 'knowledgeringppgt' ); ?></span></legend>
						<?php
							if ( ! isset( $checked ) )
								$checked = '';
							foreach ( knowledgeringppgt_show_graph_filter_manual_insert() as $option ) {
								$radio_setting = $options['show_graph_filter_manual_insert'];

								if ( '' != $radio_setting ) {
									if ( $options['show_graph_filter_manual_insert'] == $option['value'] ) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								}
								?>
								<div class="layout">
								<label class="description">
									<input type="radio" name="knowledgeringppgt_plugin_options[show_graph_filter_manual_insert]" value="<?php esc_attr_e( $option['value'] ); ?>" <?php echo $checked; ?> />
									<span>
										<?php echo $option['label']; ?>
									</span>
								</label>
								</div>
								<?php
							}
						?>
						</fieldset>
					</td>
				</tr>


				<?php
				/**
				 * Knowledgering Post Popularity Graph Tool display itemized popularity graphs
				 */
				?>
				<tr valign="top" id="knowledgeringppgt-show-itemized-graphs"><th scope="row"><?php _e( 'Include itemized popularity graphs under main popularity graph', 'knowledgeringppgt' ); ?></th>
					<td>
						<fieldset><legend class="screen-reader-text"><span><?php _e( 'Include itemized popularity graphs under main popularity graph', 'knowledgeringppgt' ); ?></span></legend>
						<?php
							if ( ! isset( $checked ) )
								$checked = '';
							foreach ( knowledgeringppgt_display_itemized_popularity_chart() as $option ) {
								$radio_setting = $options['display_itemized_popularity_chart'];

								if ( '' != $radio_setting ) {
									if ( $options['display_itemized_popularity_chart'] == $option['value'] ) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								}
								?>
								<div class="layout">
								<label class="description">
									<input type="radio" name="knowledgeringppgt_plugin_options[display_itemized_popularity_chart]" value="<?php esc_attr_e( $option['value'] ); ?>" <?php echo $checked; ?> />
									<span>
										<?php echo $option['label']; ?>
									</span>
								</label>
								</div>
								<?php
							}
						?>
						</fieldset>
					</td>
				</tr>

				<?php
				/**
				 * Knowledgering Post Popularity Graph Tool Credit Link
				 */
				?>
				<tr valign="top" id="knowledgeringppgt-credit-links"><th scope="row"><?php _e( 'Hide Credit Link', 'knowledgeringppgt' ); ?></th>
					<td>
						<fieldset><legend class="screen-reader-text"><span><?php _e( 'Hide Credit Link', 'knowledgeringppgt' ); ?></span></legend>
						<?php
							if ( ! isset( $checked ) )
								$checked = '';
							foreach ( knowledgeringppgt_yes_no_options_credit_links() as $option ) {
								$radio_setting = $options['yes_no_options_cl'];

								if ( '' != $radio_setting ) {
									if ( $options['yes_no_options_cl'] == $option['value'] ) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								}
								?>
								<div class="layout">
								<label class="description">
									<input type="radio" name="knowledgeringppgt_plugin_options[yes_no_options_cl]" value="<?php esc_attr_e( $option['value'] ); ?>" <?php echo $checked; ?> />
									<span>
										<?php echo $option['label']; ?>
									</span>
								</label>
								</div>
								<?php
							}
						?>
						</fieldset>
					</td>
				</tr>


			</table>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'knowledgeringppgt' ); ?>" />
			</p>
		</form>
	</div>
	<?php
}

/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
function knowledgeringppgt_plugin_options_validate( $input ) {


	// Say our text option must be safe text with no HTML tags
	// Graph height
	$input['graph_height'] = wp_filter_nohtml_kses( $input['graph_height'] );

	// Graph color
	$input['graph_color'] = wp_filter_nohtml_kses( $input['graph_color'] );

	// Our radio option must actually be in our array of radio options
	if ( ! isset( $input['yes_no_options_cl'] ) )
		$input['yes_no_options_cl'] = null;
	if ( ! array_key_exists( $input['yes_no_options_cl'], knowledgeringppgt_yes_no_options_credit_links() ) )
		$input['yes_no_options_cl'] = null;

	if ( ! isset( $input['yes_no_options_fbl'] ) )
		$input['yes_no_options_fbl'] = null;
	if ( ! array_key_exists( $input['yes_no_options_fbl'], knowledgeringppgt_yes_no_options_fbl() ) )
		$input['yes_no_options_fbl'] = null;

	if ( ! isset( $input['yes_no_options_rtw'] ) )
		$input['yes_no_options_rtw'] = null;
	if ( ! array_key_exists( $input['yes_no_options_rtw'], knowledgeringppgt_yes_no_options_rtw() ) )
		$input['yes_no_options_rtw'] = null;

	// retweets/likes high value
	$input['retweets_likes_high_value'] = wp_filter_nohtml_kses( $input['retweets_likes_high_value'] );


	// 	Show popularity graph via automatic filter or manual insert

	if ( ! isset( $input['show_graph_filter_manual_insert'] ) )
		$input['show_graph_filter_manual_insert'] = null;
	if ( ! array_key_exists( $input['show_graph_filter_manual_insert'], knowledgeringppgt_show_graph_filter_manual_insert() ) )
		$input['show_graph_filter_manual_insert'] = null;


	// Itemized charts
	if ( ! isset( $input['display_itemized_popularity_chart'] ) )
		$input['display_itemized_popularity_chart'] = null;
	if ( ! array_key_exists( $input['display_itemized_popularity_chart'], knowledgeringppgt_display_itemized_popularity_chart() ) )
		$input['display_itemized_popularity_chart'] = null;




	return $input;
}


// Widget functionality

function knowledgeringppgt_most_popular_posts($kppg_numberposts,$kppg_rpwidgettitle,$kppg_widget_title_header,$kppg_widget_classname,$kppg_widget_container_type,$kppg_widget_title_header_classname,$show_popularity_graph,$include_post_thumbnail,$kppg_post_thumbnail_width,$kppg_post_thumbnail_height,$kppg_before_widget_code,$kppg_after_widget_code,$kppg_before_title_code,$kppg_after_title_code)
{


	set_time_limit(500);
	global $kppg_options;

	$knowledgeringppgt_ppwidget=get_option('knowledgeringppgt_popular_posts_widget_content');

	if(isset($knowledgeringppgt_ppwidget) && !empty($knowledgeringppgt_ppwidget))
	{
		echo $knowledgeringppgt_ppwidget;
	}
	else
	{

			if ( isset( $kppg_options['graph_color'] ) && !empty( $kppg_options['graph_color'] ) ) { $kppg_graph_color=$kppg_options['graph_color']; } else { $kppg_graph_color="FF0000";}
			if ( isset( $kppg_options['graph_height'] ) && !empty( $kppg_options['graph_height'] ) ) { $kppg_graph_height=$kppg_options['graph_height'];} else { $kppg_graph_height="10";}

			$kppg_graph_color=str_replace("#","",$kppg_graph_color);


			$knowledgeringppgt_postsIDsarr=array();
			//$args=array('numberposts' => -1,'orderby' => 'comment_count');
			//$knowledgeringppgt_posts=get_posts($args);

			$where="WHERE post_type='post' AND post_status='publish'";
			$orderbycc="ORDER BY 'comment_count' DESC";
			$mypostids=retrievepostids($where,$orderbycc);

			if(!isset($kppg_numberposts) || empty($kppg_numberposts)){$kppg_numberposts=10;}
			if(!isset($show_popularity_graph) || empty($show_popularity_graph)){$show_popularity_graph="yes";}
			if(!isset($kppg_widget_title_header) || empty($kppg_widget_title_header)){$kppg_widget_title_header="h3";}
			if(!isset($kppg_post_thumbnail_width) || empty($kppg_post_thumbnail_width)){$kppg_post_thumbnail_width="50";}
			if(!isset($kppg_post_thumbnail_height) || empty($kppg_post_thumbnail_height)){$kppg_post_thumbnail_height="50";}
			if(!isset($kppg_widget_container_type) || empty($kppg_widget_container_type)){$kppg_widget_container_type="div";}

			foreach($mypostids as $mypostid)
			{
				$thepid=$mypostid['ID'];
				$mypostdata=get_post($thepid);
				$thepostid=$mypostdata->ID;
				$thepostcomments=$mypostdata->comment_count;

				$knowledgeringppgt_postsIDsarr[]=array('thepostid' => $thepostid,'thecommentcount' => $thepostcomments);
			}

			foreach ($knowledgeringppgt_postsIDsarr as $kpriditem)
			{

				$thepostid=$kpriditem['thepostid'];
				$commentcount=$kpriditem['thecommentcount'];

				$thispostpopularitypercentage=knowledgeringppgt_show_post_popularity_for_widget($thepostid,$commentcount);

				$thispostguid=get_permalink($thepostid);
				$thisposttitle=get_the_title($thepostid);

				$postidpercentagepairarr[]=array('title'=> $thisposttitle,'guid'=> $thispostguid,'ID' => $thepostid, 'percentage' => $thispostpopularitypercentage);
				$percentsarr[]=$thispostpopularitypercentage;
				$highpercent=max($percentsarr);

			}


			$postidpercentagepairarrsorted=msort($postidpercentagepairarr, 'percentage');

			$kppg_popular_ret='';


				if($highpercent > 0)
				{
					echo $kppg_before_widget_code;
					echo '<'.$kppg_widget_container_type .' class="'.$kppg_widget_classname .'">';

						if(isset($kppg_rpwidgettitle) && !empty($kppg_rpwidgettitle))
						{
							echo $kppg_before_title_code;
							echo '<';

							echo $kppg_widget_title_header;

							if(isset($kppg_widget_title_header_classname) && !empty($kppg_widget_title_header_classname)){ echo ' class="'. $kppg_widget_title_header_classname .'"'; }
							echo '>'.$kppg_rpwidgettitle.'</'. $kppg_widget_title_header .'>';
							echo $kppg_after_title_code;
						}


						$postidpercentagepairarrcapped = array_slice($postidpercentagepairarrsorted, 0, $kppg_numberposts, true);

						foreach($postidpercentagepairarrcapped as $postidpercentagepairarrvals)
						{
							echo '<div id="popularmain"><div class="popularmainin">';
							$kppg_post_title=$postidpercentagepairarrvals['title'];
							$kppg_post_guid=$postidpercentagepairarrvals['guid'];
							$kppg_post_percentage=$postidpercentagepairarrvals['percentage'];
							$kppg_post_ID=$postidpercentagepairarrvals['ID'];

							if(isset($include_post_thumbnail) && !empty($include_post_thumbnail) && ($include_post_thumbnail == "yes"))
							{
								if ( (function_exists('has_post_thumbnail')) && (has_post_thumbnail($kppg_post_ID)) )
								{ echo '<div class="thumb">'. get_the_post_thumbnail($kppg_post_ID,array($kppg_post_thumbnail_width,$kppg_post_thumbnail_height) ) .'</div>'; }
							}

							echo '<div class="excerpt"><a href="'.$kppg_post_guid.'" title="Permalink to'. $kppg_post_title.'" rel="bookmark">'. $kppg_post_title .'</a>';


							if(isset($show_popularity_graph) && !empty($show_popularity_graph) && ($show_popularity_graph == "yes"))
							{

								$kppg_post_graph='';

								echo '<div style="clear:both;display:block;">' . __("Popularity", "knowledgeringppgt") .': ' .$kppg_post_percentage.'% </div>';


								if($kppg_post_percentage <= 0){$kppg_graph_bg="background:none;";}
								else{$kppg_graph_bg="background:#$kppg_graph_color;";$kppg_post_graph_width=round($kppg_post_percentage,0);}

								if($kppg_post_graph_width < 1){ $kppg_post_graph_height=0;}else{$kppg_post_graph_height = $kppg_graph_height;}

								$kppg_post_graph.='<div style="width:'.$kppg_post_graph_width.'px;'.$kppg_graph_bg.';height:'.$kppg_post_graph_height.'px;">';
								$kppg_post_graph.='</div>';

								echo '<span style="display:block";>'.$kppg_post_graph.'</span>';

							}


							echo '<div class="clear"></div></div><!--close div excerpt--></div><!-- close popularmainin--></div><!-- close popularmain--><div class="clear"></div> <div class="divider-polular-post"></div>';

						}

						echo '</'.$kppg_widget_container_type .'>';
						echo $kppg_after_widget_code;

				}
		}
}


add_filter('query_vars', 'knowledgeringppgt_query_vars');
function knowledgeringppgt_query_vars($public_query_vars) {
	$public_query_vars[] = 'krppcron';
	return $public_query_vars;
}

add_action('template_redirect', 'knowledgeringppgt_krppcron');
function knowledgeringppgt_krppcron(){
	$krppcronval = get_query_var('krppcron');
	if ($krppcronval == 1){

		@knowledgeringppgt_process_cron($runcron=1);
	}
}


function knowledgeringppgt_process_cron($runcron)
{

	global $knowledgeringppgt_plugin_path;
	if( file_exists("$knowledgeringppgt_plugin_path/knowledgeringppgt-cron.php") )
	{
		require("$knowledgeringppgt_plugin_path/knowledgeringppgt-cron.php");
	}

}
?>