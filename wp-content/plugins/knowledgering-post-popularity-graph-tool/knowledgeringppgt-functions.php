<?php if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }


function get_max_comments()
{
	$mymaxcoms='';
	$comargs=array('numberposts' => 1,'orderby' => 'comment_count');
	if($comargs){$kppg_com_posts=get_posts($comargs);}
	if($kppg_com_posts){$mypostformaxcoms=$kppg_com_posts[0];}
	if($mypostformaxcoms){$mymaxcoms=$mypostformaxcoms->comment_count;}

	return $mymaxcoms;
}

function get_max_views()
{

	global $wpdb;
	$mymaxviews ='';

	$kppgmostviewed = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='views' AND meta_value > 0");

	foreach($kppgmostviewed as $kppgmostviewedval){$kppgmostviewedarr[]=$kppgmostviewedval->meta_value;}
	$newkppgmostviewed=array_unique($kppgmostviewedarr);
	$mymaxviews=max($newkppgmostviewed);

	return $mymaxviews;

}

function get_fblikes_high_value()
{
	global $wpdb;
	$mymaxfbls='';
	$kppgmostfblsarr=array();
	global $kppg_options;
	if ( isset( $kppg_options['retweets_likes_high_value'] ) && !empty( $kppg_options['retweets_likes_high_value'] ) )
	{
		$retweets_likes_high_value=$kppg_options['retweets_likes_high_value'];
		$mymaxfbls=$retweets_likes_high_value;
	}
	else
	{

		$kppgmostfbls = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='krppfacebooklikes' AND meta_value > 0");

		if($kppgmostfbls)
		{
			foreach($kppgmostfbls as $kppgmostfblsval)
			{
				$kppgmostfblsarr[]=$kppgmostfblsval->meta_value;
			}

			if($kppgmostfblsarr)
			{
				$newkppgmostfbls=array_unique($kppgmostfblsarr);
				$mymaxfbls=max($newkppgmostfbls);
			}
		}
	}

	return $mymaxfbls;
}


function get_retweets_high_value()
{
	global $wpdb;
	$mymaxretweets='';
	$kppgmostretweetsarr=array();

	global $kppg_options;
	if ( isset( $kppg_options['retweets_likes_high_value'] ) && !empty( $kppg_options['retweets_likes_high_value'] ) )
	{
		$retweets_likes_high_value=$kppg_options['retweets_likes_high_value'];
		$mymaxretweets=$retweets_likes_high_value;
	}
	else
	{

		$kppgmostretweets = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='krppretweets' AND meta_value > 0");

		if($kppgmostretweets)
		{
			foreach($kppgmostretweets as $kppgmostretweetsval)
			{
				$kppgmostretweetsarr[]=$kppgmostretweetsval->meta_value;
			}

			if($kppgmostretweetsarr)
			{
				$newkppgmostretweets=array_unique($kppgmostretweetsarr);
				$mymaxretweets=max($newkppgmostretweets);
			}
		}
	}

	return $mymaxretweets;
}


		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Calculate and return the ratings percentage value
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		function knowledgeringppgt_get_post_ratings_percentage($kppgPID)
		{

			global $wpdb,$table_prefix;
			$kppg_ratings_percentage=0;

			if(function_exists('wp_gdsr_rating_article'))
			{
				$ppg_pr_object=wp_gdsr_rating_article($kppgPID);

				if($ppg_pr_object)
				{
					$kppg_wp_gdsr_user_votes=$ppg_pr_object->user_votes;
					$kppg_wp_gdsr_visitor_votes=$ppg_pr_object->visitor_votes;
					$kppg_wp_gdsr_uvs_vvs=($kppg_wp_gdsr_user_votes + $kppg_wp_gdsr_visitor_votes);


					$kppgwpgdsruvsvvshigh = $wpdb->get_var("SELECT sum(user_voters + visitor_voters) AS totalvotes FROM ${table_prefix}gdsr_data_article  ORDER BY totalvotes DESC LIMIT 1");
					$kppg_ratings_percentage=( ($kppg_wp_gdsr_uvs_vvs/$kppgwpgdsruvsvvshigh) * 100 );

					return $kppg_ratings_percentage;
				}
			}
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Calculate and return the views percentage value
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		function knowledgeringppgt_get_post_views_percentage($kppgPID)
		{

			global $wpdb,$table_prefix;
			$mymaxviews=get_max_views();
			$kppg_view_percentage=0;
			$ppg_pviews=0;

				if( get_post_meta($kppgPID,'views', true) )
				{
					$ppg_pviews=get_post_meta($kppgPID,'views', true);

					$ppg_pviews=intval($ppg_pviews);

					if($mymaxviews > 0 )
					{
						$kppg_view_percentage=( ($ppg_pviews/$mymaxviews) * 100 );
						return $kppg_view_percentage;
					}
			}
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Calculate and return the comments percentage value
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		function knowledgeringppgt_get_comments_percentage($kppgPID,$commentcount)
		{
			global $wpdb,$table_prefix;
			$mymaxcoms=get_max_comments();

				$post=get_post($kppgPID);

				if ( ('open' == $post->comment_status) && ($mymaxcoms > 0))
				{
					$kppg_comments_percentage='';

					$ppg_comments=$commentcount;

					$kppg_comments_percentage=( ($ppg_comments/$mymaxcoms) * 100 );

					return $kppg_comments_percentage;
				}
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Calculate and return the retweets percentage value
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function knowledgeringppgt_get_retweets_percentage($kppgPID)
		{

			global $kppg_options;
			$kppg_retweets_percentage=0;

			if ( isset( $kppg_options['yes_no_options_rtw'] ) && !empty( $kppg_options['yes_no_options_rtw'] ) && ($kppg_options['yes_no_options_rtw'] == 'yes') )
			{

				$ppg_retweets=0;

				// if ( isset( $kppg_options['retweets_likes_high_value'] ) && !empty( $kppg_options['retweets_likes_high_value'] ) ) { $retweets_likes_high_value=$kppg_options['retweets_likes_high_value']; } else { $retweets_likes_high_value="500";}

				$retweets_high_value=get_retweets_high_value();


				//$ppg_retweets=knowledgeringppgt_tweetCount( get_permalink($kppgPID) );

				if( get_post_meta($kppgPID,'krppretweets', true) )
				{
					$ppg_retweets=get_post_meta($kppgPID,'krppretweets', true);
				}
					$ppg_retweets=intval($ppg_retweets);

					if($retweets_high_value > 0)
					{
						$kppg_retweets_percentage=( ($ppg_retweets/$retweets_high_value) * 100 );
					}

				return $kppg_retweets_percentage;
			}

		}

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Calculate and return the retweets percentage value
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		function knowledgeringppgt_get_fblikes_percentage($kppgPID)
		{
			global $kppg_options;
			$kppg_facebooklikes_percentage=0;

			if ( isset( $kppg_options['yes_no_options_fbl'] ) && !empty( $kppg_options['yes_no_options_fbl'] ) && ($kppg_options['yes_no_options_fbl'] == 'yes') )
			{

				$ppg_facebooklikes=0;
				//if ( isset( $kppg_options['retweets_likes_high_value'] ) && !empty( $kppg_options['retweets_likes_high_value'] ) ) { $retweets_likes_high_value=$kppg_options['retweets_likes_high_value'];} else { $retweets_likes_high_value="500";}

				$fblikes_high_value=get_fblikes_high_value();


				//$ppg_facebooklikes='';
				//$ppg_facebooklikes=knowledgeringppgt_fbCount( get_permalink($kppgPID) );

				if( get_post_meta($kppgPID,'krppfacebooklikes', true) )
				{
					$ppg_facebooklikes=get_post_meta($kppgPID,'krppfacebooklikes', true);
				}
					$ppg_facebooklikes=intval($ppg_facebooklikes);


				if($fblikes_high_value > 0)
				{
					$kppg_facebooklikes_percentage=( ($ppg_facebooklikes/$fblikes_high_value) * 100 );
				}

				return $kppg_facebooklikes_percentage;

			}
		}

function knowledgeringppgt_show_post_popularity_for_widget($kppgPID,$kppgccount){

global $wpdb,$table_prefix,$kppg_options;


	if(isset($kppgPID)){

			$post=get_post($kppgPID);
			$kppg_post_popularity_widget=0;
			$kppg_post_popularity_widget_vars=array();
			$kppg_wp_gdsr_user_votes='';
			$kppg_wp_gdsr_visitor_votes='';
			$kppg_wp_gdsr_uvs_vvs='';
			$kppgwpgdsruvsvvshigh='';
			$kppg_view_percentage=0;
			$kppg_ratings_percentage=0;
			$kppg_facebooklikes_percentage=0;
			$kppg_retweets_percentage=0;
			$kppg_comments_percentage=0;
			$kppgccount=$post->comment_count;

			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// Get the post rating data
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			if(function_exists('wp_gdsr_rating_article'))
			{
				$kppg_ratings_percentage=knowledgeringppgt_get_post_ratings_percentage($kppgPID);
				$kppg_ratings_percentage_arr=round($kppg_ratings_percentage,2);
				$kppg_post_popularity_widget_vars[]=$kppg_ratings_percentage_arr;
			}

			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// Get total views
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			if( get_post_meta($kppgPID,'views', true) )
			{
				$kppg_view_percentage=knowledgeringppgt_get_post_views_percentage($kppgPID);
				$kppg_view_percentage_arr=round($kppg_view_percentage,2);
				$kppg_post_popularity_widget_vars[]=$kppg_view_percentage_arr;

			}

			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// Get retweet percentage
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			if ( isset( $kppg_options['yes_no_options_rtw'] ) && !empty( $kppg_options['yes_no_options_rtw'] ) && ($kppg_options['yes_no_options_rtw'] == 'yes') )
			{
				$kppg_retweets_percentage=knowledgeringppgt_get_retweets_percentage($kppgPID);
				$kppg_retweets_percentage_arr=round($kppg_retweets_percentage,2);
				$kppg_post_popularity_widget_vars[]=$kppg_retweets_percentage_arr;
			}

			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// Get facebook likes percentage
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			if ( isset( $kppg_options['yes_no_options_fbl'] ) && !empty( $kppg_options['yes_no_options_fbl'] ) && ($kppg_options['yes_no_options_fbl'] == 'yes') )
			{

				$kppg_facebooklikes_percentage=knowledgeringppgt_get_fblikes_percentage($kppgPID);
				$kppg_facebooklikes_percentage_arr=round($kppg_facebooklikes_percentage,2);
				$kppg_post_popularity_widget_vars[]=$kppg_facebooklikes_percentage_arr;
			}


			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// Get the total post comments and calculate comments popularity if comments open
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			if ('open' == $post->comment_status)
			{
				$kppg_comments_percentage=knowledgeringppgt_get_comments_percentage($kppgPID,$kppgccount);
				$kppg_comments_percentage_arr=round($kppg_comments_percentage,2);
				$kppg_post_popularity_widget_vars[]=$kppg_comments_percentage_arr;
			}

			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// Calculate the post popularity percentage based on values in array $kppg_post_popularity_widget_vars
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


			$kppg_post_popularity_widget_arritems=count($kppg_post_popularity_widget_vars);
			$kppg_post_popularity_widget_arrsum=array_sum($kppg_post_popularity_widget_vars);
			if($kppg_post_popularity_widget_arritems > 0){$kppg_post_popularity_widget=($kppg_post_popularity_widget_arrsum/$kppg_post_popularity_widget_arritems);
			$kppg_post_popularity_widget=round($kppg_post_popularity_widget,2);}

			return $kppg_post_popularity_widget;

		}

}


function msort($array, $id="id", $sort_ascending=false) {
        $temp_array = array();
        while(count($array)>0) {
            $lowest_id = 0;
            $index=0;
            foreach ($array as $item) {
                if (isset($item[$id])) {
                                    if ($array[$lowest_id][$id]) {
                    if ($item[$id]<$array[$lowest_id][$id]) {
                        $lowest_id = $index;
                    }
                    }
                                }
                $index++;
            }
            $temp_array[] = $array[$lowest_id];
            $array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id+1));
        }
                if ($sort_ascending) {
            return $temp_array;
                } else {
                    return array_reverse($temp_array);
                }
    }

function array_remove_empty($arr){
    $narr = array();
    while(list($key, $val) = each($arr)){
        if (is_array($val)){
            $val = array_remove_empty($val);
            // does the result array contain anything?
            if (count($val)!=0){
                // yes :-)
                $narr[$key] = $val;
            }
        }
        else {
            if (trim($val) != ""){
                $narr[$key] = $val;
            }
        }
    }
    unset($arr);
    return $narr;
}


function retrievepostids($where,$orderby)
{

	global $wpdb,$table_prefix;
	$knowledgeringppgt_postsIDsarr='';

	if(!isset($where) || empty($where)){$where="WHERE post_type='post' AND post_status='publish'";}
	if(!isset($orderby) || empty($orderby)){$orderby='';}

		$table_name =  "{$table_prefix}posts";

				$sql = "SELECT `ID` FROM `{$table_name}` $where $orderby";
				$knowledgeringppgt_postsIDsarr = $wpdb->get_results( $sql , ARRAY_A );

		return $knowledgeringppgt_postsIDsarr;

}
    ?>