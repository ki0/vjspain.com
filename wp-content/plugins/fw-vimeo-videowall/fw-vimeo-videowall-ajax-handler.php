<?php define('DOING_AJAX', true);
require_once( preg_replace('%(.*)[/\\\\]wp-content[/\\\\].*%', '\1', $_SERVER['SCRIPT_FILENAME'] ) . '/wp-load.php' );

switch ($_POST['action']) {

    case 'show_video' :
        $video = new FW_vimeo_videowall();
        $video->vwidth = 600;
        $video->vheight = 450;
        $video->display_single_video ($_POST['id']);
        echo '<img src="'.FWVVW_URL.'/images/cross.png" class="closewindow" alt="'.__("Close window","fwvvw").'" title="'.__("Close window","fwvvw").'" />';
        break;
    case 'show_page' :
        fw_vimeowall_display('paginate=0&id='.$_POST['id'].'&source='.$_POST['source'].'&type='.$_POST['type'].'&width='.$_POST['width'].'&height='.$_POST['height'].'&number='.$_POST['number'].'&page='.$_POST['page'], false);
        break;
    default: break;
}
?>