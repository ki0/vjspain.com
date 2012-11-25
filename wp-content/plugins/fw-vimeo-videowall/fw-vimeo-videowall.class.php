<?php
/**
 * @package fw-vimeo-videowall
 * @author fairweb
 */

class FW_vimeo_videowall {
    public $id;
    public $vsource;
    public $vtype;
    public $vcall;
    public $api_endpoint;
    public $info_endpoint;
    public $vwidth;
    public $vheight;
    public $vnumber;
    public $vperpage = 20;
    public $vecho;
    public $vpage = 1;
    public $vpagination = true;
    public $vtitle = false;

    function FW_vimeo_videowall() {
       __construct();
    }
    function __construct() {

    }
    public function choose_endpoint () {
        global $fwvvw_user_name;
        switch ($this->vsource) {
            case 'user' : $this->api_endpoint = 'http://vimeo.com/api/v2/'.$this->id.'/videos.json?page='.$this->vpage;
                            $this->info_endpoint = 'http://vimeo.com/api/v2/'.$this->id.'/info.json'; break;
            case 'group' : $this->api_endpoint = 'http://vimeo.com/api/v2/group/'.$this->id.'/videos.json?page='.$this->vpage;
                            $this->info_endpoint = 'http://vimeo.com/api/v2/group/'.$this->id.'/info.json'; break;
            case 'album' : $this->api_endpoint = 'http://vimeo.com/api/v2/album/'.$this->id.'/videos.json?page='.$this->vpage;
                            $this->info_endpoint = 'http://vimeo.com/api/v2/album/'.$this->id.'/info.json'; break;
            case 'channel' : $this->api_endpoint = 'http://vimeo.com/api/v2/channel/'.$this->id.'/videos.json?page='.$this->vpage;
                            $this->info_endpoint = 'http://vimeo.com/api/v2/channel/'.$this->id.'/info.json'; break;
            case 'video' : $this->api_endpoint = 'http://vimeo.com/api/v2/video/'.$this->id.'.json'; break;

            //case 'video' : $this->api_endpoint = 'http://www.vimeo.com/api/oembed.json'; break;
            default : $this->api_endpoint = 'http://www.vimeo.com/api/v2/'.$this->id.'/videos.json'; break;
        }
    }

    public function video_wall($args, $wrapper) {
        $this->id = $args['id'];
        $this->vsource = $args['source'];
        $this->vnumber = $args['number'];
        $this->vwidth = $args['width'];
        $this->vheight = $args['height'];
        $this->vtype = $args['type'];
        $this->vpage = $args['page'];
        $this->vtitle = $this->vtype == 'title' ? false : $args['title'];
       	// $this->vperpage = $args['perpage'];
       	// $this->vpaginate = $args['paginate'];

        $html = '';
        
        $this->choose_endpoint ();
        if ($this->vnumber == 0) {
            $this->vnumber = $this->get_total_videos();
            $this->vpagination == true;        
        }
        // echo $this->vnumber;
       	/* $this->vperpage = $this->vperpage > $this->vnumber ? $this->vnumber : $this->vperpage;
        $this->vperpage = $this->vperpage > 20 ? 20 : $this->vperpage;*/
        if ($this->vnumber > 20) {
            $this->vpagination == true;
        }
        
        $video_details = $this->get_datas();

        if ($wrapper == true) {
            $html .='<div id="wall-fwvvw-'.$this->vsource.'-'.$this->id.'" class="fwvvw-'.$this->vsource.'">';
        }
            if ($this->vtype == 'title' && $video_details) {
                $html .='<ul id="walllist-fwvvw-'.$this->vsource.'-'.$this->id.'">';
            }
        
       
        if (!$video_details) {
         if ($args['echo'] == true) {
                 _e('No video', 'fwvvw');
         } else {
                 return false;
         }
        }
         $html .= $this->get_html($video_details);
          if ($this->vtype == 'title' && $video_details) {

            $html .='</ul>';


            }

            $html .= $this->paginate();
         if ($wrapper == true) {

            $html .='<div class="fwclear"></div>';
            $html.= '</div>';
           
         }


    
         if ($args['echo'] == false) {
                 return $html;
         } else {
                 echo $html;
         }

    }

    public function display_single_video ($video_id) {
        $this->id = $video_id;
        
        $this->vsource = 'video';
        //$this->choose_endpoint ();
        $this->api_endpoint = 'http://vimeo.com/api/oembed.json';
        $oembed_url = $this->api_endpoint.'?url='.rawurlencode('http://vimeo.com/'.$this->id).'&maxwidth='.$this->vwidth.'&maxheight='.$this->vheight;       
        $oembed_req = wp_remote_retrieve_body( wp_remote_get($oembed_url));
        $oembed = json_decode($oembed_req);
        $embed_code = html_entity_decode($oembed->html);
        echo $embed_code;
    }

    public function get_html($video_details) {
        $full_html = '';
        if ($video_details) {
        switch ($this->vtype) {
            case 'video' :
                foreach ($video_details as $video) {
                    $full_html .= $this->get_video_html ($video);
                }
                break;
            case 'title' :
                foreach ($video_details as $video) {
                    $full_html .= $this->get_title_html ($video);
                }
                break;
            default :
                 foreach ($video_details as $video) {
                    $full_html .= $this->get_image_html ($video);
                }
                break; // default is image
        }
        }
        
        return $full_html;
    }

    public function get_video_html ($video) {
        $oembed_endpoint = 'http://vimeo.com/api/oembed.json';
        $oembed_url = $oembed_endpoint.'?url='.rawurlencode($video->url).'&maxwidth='.$this->vwidth.'&maxheight='.$this->vheight;
        $oembed_req = wp_remote_retrieve_body( wp_remote_get($oembed_url));
        $oembed = json_decode($oembed_req);
        $html_code = '<div id="video_'.$video->id.'" class="fwvvw_vthumb">';
        $html_code .= html_entity_decode($oembed->html);
        $html_code .= $this->vtitle == true ? '<div class="fwvvw-videotitle" style="width: '.$this->vwidth.'px;">'.$video->title.'</div>' : '';
        $html_code .= '</div>';
        return $html_code;
    }

    public function get_image_html ($video) {
         $html_code = '<div id="video_'.$video->id.'" class="fwvvw_vthumb">';
         $html_code .= '<img src="'.$video->thumbnail_small.'" alt="'.$video->title.'" title="'.$video->title.'" style="width: '.$this->vwidth.'px;" />';
         $html_code .= $this->vtitle == true ? '<div class="fwvvw-videotitle" style="width: '.$this->vwidth.'px;">'.$video->title.'</div>' : '';

         $html_code .= '</div>';
         return $html_code;
    }

    public function get_title_html ($video) {
        $html_code = '<li id="video_'.$video->id.'" class="fwvvw_vthumb">';
         $html_code .= '<a href="#" title="'.$video->title.'">'.$video->title.'</a>';
         $html_code .= '</li>';
         return $html_code;
    }

    public function get_total_videos () {
        $info_req = wp_remote_retrieve_body( wp_remote_get($this->info_endpoint));
        $infos = json_decode($info_req );
       
        if (!$infos) { return false; }
        if ($this->vsource == 'user') {
            $total_videos = $infos->total_videos_uploaded;
        } else {
            $total_videos = $infos->total_videos;
        }
        return $total_videos;
    }

    public function paginate() {
         $html = '';
        if ($this->vpagination == false ) { return; }
    
            if ($this->vnumber > 20) {
                $nb_pages = ceil($this->vnumber / 20);
                //echo $nb_pages;
                if ($nb_pages > 1) {
                    $html .= '<div id="fwvvw-paginate-'.$this->vsource.'-'.$this->id.'" class="fwvvw-pagination">';
                    $pagelinkid = 'fwvvw_'.$this->vsource.'_'.$this->id.'_'.$this->vtype.'_'.$this->vwidth.'_'.$this->vheight.'_'.$this->vnumber;
                    for ($i = 1; $i <= $nb_pages; $i++) {
                        $selected = '';
                       if ($this->vpage == $i) {
                           $selected = 'fwvvw_current_page';
                       }
                       $html .= '<a id="'.$pagelinkid.'_'.$i.'" class="fwvvw_pagelink '.$selected.'" href="#wall-fwvvw-'.$this->vsource.'-'.$this->id.'">'.$i.'</a> ';
                    }
                    $html .= '</div>';
                   
                }
            }
            return $html;
    }

    public function get_datas() {
    //echo $this->api_endpoint;
        $endpoint_req = wp_remote_retrieve_body( wp_remote_get($this->api_endpoint));
        $videos = json_decode($endpoint_req );
        
        //print_r($videos);
        $video_details = array();

        //$nb = $this->vsource == 'video' ? 1 : $this->vperpage;
        //$this->vnumber = $this->vnumber == 0 ? $nb : $this->vnumber;
        if ($this->vnumber != false ) {
       /* for ($i=0; $i < $this->vperpage; $i++) {
            if ($i == $nb) { break; }
            $videos_details [] = $videos[$i];
        }*/
            if ($this->vnumber <= 20) {
            $videos = array_slice($videos,0, $this->vnumber);
        }
            $i=0;
             do {
                $videos_details [] = $videos[$i];
                $i++;
            }
            while (isset($videos[$i]));
        }
        return $videos_details;
    }
}

?>