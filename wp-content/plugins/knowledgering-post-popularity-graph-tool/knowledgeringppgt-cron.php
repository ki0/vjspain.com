<?php if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(isset($runcron) && !empty($runcron) && ($runcron == 1))
{
	//define('DOING_CRON', true);

	global $kppg_options;
	//$knowledgeringppgt_postsIDsarr=array();
	//$args=array('numberposts' => -1);
	//$knowledgeringppgt_posts=get_posts($args);

	global $wpdb,$table_prefix;

		$table_name =  "{$table_prefix}posts";

				$sql = "SELECT ID FROM `{$table_name}` WHERE post_type='post' AND post_status='publish'";
				$knowledgeringppgt_postsIDsarr = $wpdb->get_results( $sql , ARRAY_A );


	@knowledgeringppgt_do_widget();

	if ( isset( $kppg_options['yes_no_options_fbl'] ) && !empty( $kppg_options['yes_no_options_fbl'] ) && ($kppg_options['yes_no_options_fbl'] == 'yes') )
	{
		@knowledgeringppgt_do_twitter($knowledgeringppgt_postsIDsarr);
	}
	if ( isset( $kppg_options['yes_no_options_rtw'] ) && !empty( $kppg_options['yes_no_options_rtw'] ) && ($kppg_options['yes_no_options_rtw'] == 'yes') )
	{
		@knowledgeringppgt_do_facebook($knowledgeringppgt_postsIDsarr);
	}

}

function knowledgeringppgt_do_twitter($knowledgeringppgt_postsIDsarr)
{

		set_time_limit(500);
		$jsonrtw=array();
		$rtwc=0;

		if($knowledgeringppgt_postsIDsarr)
		{
			foreach ($knowledgeringppgt_postsIDsarr as $knowledgeringppgt_postsID)
			{
				$idsurls[]=array('url' => get_permalink($knowledgeringppgt_postsID), 'ID' => $knowledgeringppgt_postsID);
			}
		}

		if($idsurls)
		{
			foreach($idsurls as $idurl)
			{

				$url=$idurl['url'];
				$thpid=$idurl['ID'];

				$twitterEndpoint = "http://urls.api.twitter.com/1/urls/count.json?url=$url";

				if(function_exists('wp_remote_retrieve_body'))
				{
					$fileDataTwitter = wp_remote_retrieve_body(wp_remote_get($twitterEndpoint));
				}
				elseif(function_exists('file_get_contents'))
				{
					$fileDataTwitter = @file_get_contents(sprintf($twitterEndpoint, $url));
				}

				if($fileDataTwitter)
				{
					$jsonrtw = json_decode($fileDataTwitter, true);
					unset($fileDataTwitter); // free memory

					$rtwc=$jsonrtw['count'];
				}

				add_post_meta($thpid, 'krppretweets', $rtwc, true) or update_post_meta($thpid, 'krppretweets', $rtwc);
			}
		}
}

function knowledgeringppgt_do_facebook($knowledgeringppgt_postsIDsarr)
{

	set_time_limit(500);
	$jsonfc=array();
	$fbltc=0;

	if($knowledgeringppgt_postsIDsarr)
	{
		foreach ($knowledgeringppgt_postsIDsarr as $knowledgeringppgt_postsID)
		{
			$idsurls[]=array('url' => get_permalink($knowledgeringppgt_postsID), 'ID' => $knowledgeringppgt_postsID);
		}
	}

	if($idsurls)
	{
		foreach($idsurls as $idurl)
		{
			$url=$idurl['url'];
			$thpid=$idurl['ID'];

			$facebookEndpoint="http://api.ak.facebook.com/restserver.php?v=1.0&method=fql.query&query=select%20url,%20total_count%20from%20link_stat%20where%20url%20in%20('".$url."')&format=json";

			if(function_exists('wp_remote_retrieve_body'))
			{
				$fileDataFacebook = @wp_remote_retrieve_body(wp_remote_get($facebookEndpoint));
			}
			elseif(function_exists('file_get_contents'))
			{
				$fileDataFacebook = @file_get_contents($facebookEndpoint);
			}

				if($fileDataFacebook)
				{
					$jsonfc = json_decode($fileDataFacebook, true);
					unset($fileDataFacebook); // free memory

					foreach($jsonfc as $fbltcarr)
					{
						//$fbltcarr=$jsonfc[0];
						$fbltc=$fbltcarr['total_count'];

						add_post_meta($thpid, 'krppfacebooklikes', $fbltc, true) or update_post_meta($thpid, 'krppfacebooklikes', $fbltc);

					}
				}
		}

	}

}

function knowledgeringppgt_do_widget(){

	set_time_limit(500);
	global $kppg_options;
	$mykrppoptions='';

	$krppsbwidget=new knowledgeringppgt_PopularPostsWidget();
	$krppsbwidgetopsname=$krppsbwidget->option_name;
	$krppoptionsmain=get_option($krppsbwidgetopsname);
	$krppoptionsrememp=array_remove_empty($krppoptionsmain);


	foreach ($krppoptionsrememp as $krppoptionsarr)
	{
		if (array_key_exists('kppg_numberposts', $krppoptionsarr))
		{
			$mykrppoptions=$krppoptionsarr;
		}
	}

	if(isset($mykrppoptions['kppg_numberposts']) && !empty($mykrppoptions['kppg_numberposts'])){ $kppg_numberposts = $mykrppoptions['kppg_numberposts'];}else {$kppg_numberposts = 5;}
	if(isset($mykrppoptions['kppg_rpwidgettitle']) && !empty($mykrppoptions['kppg_rpwidgettitle'])){$kppg_rpwidgettitle = $mykrppoptions['kppg_rpwidgettitle'];} else {$kppg_rpwidgettitle = __('Most Popular Posts','knowledgeringppgt');}
	if(isset($mykrppoptions['kppg_widget_classname']) && !empty($mykrppoptions['kppg_widget_classname'])){$kppg_widget_classname = $mykrppoptions['kppg_widget_classname'];} else {$kppg_widget_classname = "widget";}
	if(isset($mykrppoptions['kppg_widget_container_type']) && !empty($mykrppoptions['kppg_widget_container_type'])){$kppg_widget_container_type = $mykrppoptions['kppg_widget_container_type'];} else {$kppg_widget_container_type = "div";}
	if(isset($mykrppoptions['kppg_widget_title_header']) && !empty($mykrppoptions['kppg_widget_title_header'])){$kppg_widget_title_header = $mykrppoptions['kppg_widget_title_header'];} else {$kppg_widget_title_header = "h3";}
	if(isset($mykrppoptions['kppg_widget_title_header_classname']) && !empty($mykrppoptions['kppg_widget_title_header_classname'])){$kppg_widget_title_header_classname = $mykrppoptions['kppg_widget_title_header_classname'];} else {$kppg_widget_title_header_classname = "";}
	if(isset($mykrppoptions['show_popularity_graph']) && !empty($mykrppoptions['show_popularity_graph'])){$show_popularity_graph = $mykrppoptions['show_popularity_graph'];}else {$show_popularity_graph = "yes";}
	if(isset($mykrppoptions['include_post_thumbnail']) && !empty($mykrppoptions['include_post_thumbnail'])){$include_post_thumbnail = $mykrppoptions['include_post_thumbnail'];}else {$include_post_thumbnail = "yes";}
	if(isset($mykrppoptions['kppg_post_thumbnail_width']) && !empty($mykrppoptions['kppg_post_thumbnail_width'])){$kppg_post_thumbnail_width = $mykrppoptions['kppg_post_thumbnail_width'];}else {$kppg_post_thumbnail_width = 50;}
	if(isset($mykrppoptions['kppg_post_thumbnail_height']) && !empty($mykrppoptions['kppg_post_thumbnail_height'])){$kppg_post_thumbnail_height = $mykrppoptions['kppg_post_thumbnail_height'];}else {$kppg_post_thumbnail_height = 50;}


	if ( isset( $kppg_options['graph_color'] ) && !empty( $kppg_options['graph_color'] ) ) { $kppg_graph_color=$kppg_options['graph_color']; } else { $kppg_graph_color="FF0000";}
	if ( isset( $kppg_options['graph_height'] ) && !empty( $kppg_options['graph_height'] ) ) { $kppg_graph_height=$kppg_options['graph_height'];} else { $kppg_graph_height="10";}

	$kppg_graph_color=str_replace("#","",$kppg_graph_color);

	//$knowledgeringppgt_postsIDsarr=array();
	//$args=array('numberposts' => -1,'orderby' => 'comment_count');

	$where="WHERE post_type='post' AND post_status='publish'";
	$orderbycc="ORDER BY 'comment_count' DESC";
	$mypostids=retrievepostids($where,$orderbycc);

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
	$knowledgeringppgt_the_widget='';


		if($highpercent > 0)
		{
			$knowledgeringppgt_the_widget.=$kppg_before_widget_code;
			$knowledgeringppgt_the_widget.='<'.$kppg_widget_container_type .' class="'.$kppg_widget_classname .'">';

				if(isset($kppg_rpwidgettitle) && !empty($kppg_rpwidgettitle))
				{
					$knowledgeringppgt_the_widget.= $kppg_before_title_code;
					$knowledgeringppgt_the_widget.='<';

					$knowledgeringppgt_the_widget.= $kppg_widget_title_header;

					if(isset($kppg_widget_title_header_classname) && !empty($kppg_widget_title_header_classname)){ $knowledgeringppgt_the_widget.= ' class="'. $kppg_widget_title_header_classname .'"'; }
					$knowledgeringppgt_the_widget.= '>'.$kppg_rpwidgettitle.'</'. $kppg_widget_title_header .'>';
					$knowledgeringppgt_the_widget.=$kppg_after_title_code;
				}


				$postidpercentagepairarrcapped = array_slice($postidpercentagepairarrsorted, 0, $kppg_numberposts, true);

				foreach($postidpercentagepairarrcapped as $postidpercentagepairarrvals)
				{
					$knowledgeringppgt_the_widget.= '<div id="popularmain"><div class="popularmainin">';
					$kppg_post_title=$postidpercentagepairarrvals['title'];
					$kppg_post_guid=$postidpercentagepairarrvals['guid'];
					$kppg_post_percentage=$postidpercentagepairarrvals['percentage'];
					$kppg_post_ID=$postidpercentagepairarrvals['ID'];
					$kppg_post_thumbnail=get_the_post_thumbnail($kppg_post_ID,array($kppg_post_thumbnail_width,$kppg_post_thumbnail_height) );

					if(isset($include_post_thumbnail) && !empty($include_post_thumbnail) && ($include_post_thumbnail == "yes"))
					{
						if ( (function_exists('has_post_thumbnail')) && (has_post_thumbnail($kppg_post_ID)) )
						{ $knowledgeringppgt_the_widget.= '<div class="thumb">'. $kppg_post_thumbnail .'</div>'; }
					}

					$knowledgeringppgt_the_widget.= '<div class="excerpt"><a href="'.$kppg_post_guid.'" title="Permalink to'. $kppg_post_title.'" rel="bookmark">'. $kppg_post_title .'</a>';


					if(isset($show_popularity_graph) && !empty($show_popularity_graph) && ($show_popularity_graph == "yes"))
					{

						$kppg_post_graph='';

						$knowledgeringppgt_the_widget.= '<div style="clear:both;display:block;">' . __("Popularity", "knowledgeringppgt") .': ' .$kppg_post_percentage.'% </div>';


						if($kppg_post_percentage <= 0){$kppg_graph_bg="background:none;";}
						else{$kppg_graph_bg="background:#$kppg_graph_color;";$kppg_post_graph_width=round($kppg_post_percentage,0);}

						if($kppg_post_graph_width < 1){ $kppg_post_graph_height=0;}else{$kppg_post_graph_height = $kppg_graph_height;}

						$kppg_post_graph.='<div style="width:'.$kppg_post_graph_width.'px;'.$kppg_graph_bg.';height:'.$kppg_post_graph_height.'px;">';
						$kppg_post_graph.='</div>';

						$knowledgeringppgt_the_widget.= '<span style="display:block";>'.$kppg_post_graph.'</span>';

					}


					$knowledgeringppgt_the_widget.= '<div class="clear"></div></div><!--close div excerpt--></div><!-- close popularmainin--></div><!-- close popularmain--><div class="clear"></div>';

				}

				$knowledgeringppgt_the_widget.= '</'.$kppg_widget_container_type .'>';
				$knowledgeringppgt_the_widget.= $kppg_after_widget_code;

		}

		//$optionexists=get_option('knowledgeringppgt_popular_posts_widget_content');
		//if(isset($optionexists) && !empty($optionexists)){update_option('knowledgeringppgt_popular_posts_widget_content',$knowledgeringppgt_the_widget);}
		//else {add_option('knowledgeringppgt_popular_posts_widget_content',$knowledgeringppgt_the_widget);}

if ( get_option( 'knowledgeringppgt_popular_posts_widget_content' ) != $knowledgeringppgt_the_widget ) {
    update_option( 'knowledgeringppgt_popular_posts_widget_content', $knowledgeringppgt_the_widget );
} else {
    $deprecated = ' ';
    $autoload = 'no';
    add_option( 'knowledgeringppgt_popular_posts_widget_content', $knowledgeringppgt_the_widget, $deprecated, $autoload );
}

}



?>
