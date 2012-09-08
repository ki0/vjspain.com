<?php
/*  Copyright 2011 Orson Teodoro (orsonteodoro@yahoo.com)

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

	Plugin Name: ForumConverter-Signatures
	Plugin URI: http://orsonteodoro.wordpress.com
	Description: Post conversion forum signatures support for bbPress though either BuddyPress or WordPress
	Author: Orson Teodoro
	Author URI: http://orsonteodoro.wordpress.com
	Version: 1.11
	License: GPL2
	Donate: http://orsonteodoro.wordpress.com
*/

add_action('bp_after_profile_content', 'fcsig_mod_profile');
add_action('template_redirect', 'fcsig_show');
add_action('wp_footer', 'fcsig_filter_content',50);
add_action('admin_footer', 'fcsig_mod_profile2');
add_action('edit_user_profile_update', 'fcsig_save');
add_action('personal_options_update', 'fcsig_save');

//buddypress user interface
function fcsig_mod_profile () {
	$user = wp_get_current_user();
	$siteurl = get_site_url();
	$nicename = $user->user_nicename;
	echo <<<OUT
		<script type="text/javascript">
		jQuery('#change-avatar-personal-li').parent().append('<li><a href="{$siteurl}/members/{$nicename}/profile/edit-signature/">Change Forum Signature</a></li>');
		</script>
OUT;

	//ui
	$curl = ($_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') .  $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	if (strstr($curl, 'edit-forum-signature'))
	{
		global $wpdb;
		
		$id = $user->ID;
		$sig = $wpdb->get_var('SELECT meta_value FROM '.$wpdb->prefix.'usermeta WHERE user_id='.$id.' AND meta_key="bbp_signature"');
		$sig = str_replace(array("\r\n", "\n"), '\n', $sig);
		$sig = wp_kses($sig, array('a' => array('href'=>array()), 'font' => array('color'=>array(), 'size'=>array(), 'face'=>array()), 'br' => array(), 'img'=>array('src'=>array(),'alt'=>array(),'class'=>array(),'style'=>array())));
		
		$c = explode('/',plugin_basename(__FILE__));
		$plugin_folder = $c[0];
		$update_url = plugins_url().'/'.$plugin_folder.'/fc-sig-update.php';
		echo <<<SIGUI
		<script type="text/javascript">
			out = 
			'<form action="{$update_url}" method="post">'+
			'	<h4>Changing forum signature</h4>'+
			'	<p><textarea rows="10" cols="60" name="sig">{$sig}</textarea></p>'+
			'	<p><input type="submit" name="submit" value="Save"></input></p>'+
			'</form>';
			jQuery("div.profile").html(out);
		</script>
SIGUI;
	}
}

//admin panel
function fcsig_mod_profile2()
{
	$curl = ($_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') .  $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	if (strstr($curl, 'profile.php') || //user access
	    strstr($curl, 'user-edit.php')) //admin access
	{
		global $wpdb;
		
		if (strstr($curl, 'profile.php'))
		{
			$user = wp_get_current_user();
			$id = addslashes($user->ID);
		}
		else if (strstr($curl, 'user-edit.php'))
		{
			$id = addslashes($_REQUEST['user_id']);
		}
		$sig = $wpdb->get_var('SELECT meta_value FROM '.$wpdb->prefix.'usermeta WHERE user_id='.$id.' AND meta_key="bbp_signature"');
		$sig = str_replace(array("\r\n", "\n"), '\n', $sig);
		$sig = wp_kses($sig, array('a' => array('href'=>array()), 'font' => array('color'=>array(), 'size'=>array(), 'face'=>array()), 'br' => array(), 'img'=>array('src'=>array(),'alt'=>array(),'class'=>array(),'style'=>array())));
		
		echo <<<SIGUI2
		<script type="text/javascript">
			out = '<h3>Forum signature</h3>' +
			'<table class="form-table">' +
			'	<tbody>' +
			'	<tr id="sig_row">' +
			'		<th><label for="sig_text">Signature</label></th>'+
			'		<td><textarea rows="10" cols="60" name="sig" id="sig_text">{$sig}</textarea></td>' +
			'	</tr>' +
			'	</tbody>' +
			'</table>' +
			'<input type="hidden" name="bbp_sig_target" value="{$id}" />' +
			'';
			jQuery(".submit").before(out);
		</script>
SIGUI2;
	}	
}

//redirect user to buddypress edit sig section
function fcsig_show()
{
	$user = wp_get_current_user();
	$curl = ($_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') .  $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	if (strstr($curl, '/profile/edit-signature/'))
		wp_redirect(get_site_url().'/members/'.$user->user_nicename.'/profile/?edit-forum-signature=true');
}

//display sig
function fcsig_filter_content()
{
	$c = explode('/',plugin_basename(__FILE__));
	$plugin_folder = $c[0];
	$purl = plugins_url().'/'.$plugin_folder.'/fc-sig-req.php';
	echo <<<SIGSCRIPT
		<script type="text/javascript">
			jQuery("td.bbp-reply-content").each(function(idx){
				id = jQuery(this).parent().attr("id");
				id = id.replace('post-','');
				jQuery.ajax({
					url: '{$purl}?pid='+id,
					success: function( data ) {
						data = jQuery.parseJSON(data);
						if (jQuery.trim(data.signature).length > 0)
						{
							jQuery("tr#post-"+data.pid+" > td.bbp-reply-content").append('<hr/><p class="signature">'+data.signature+'</p>');
							jQuery('.bbimg_forum').css('max-width', '710px'); //400 right col
						}
					}
				});
			});
		</script>
SIGSCRIPT;
}

//save to database
function fcsig_save($user_id)
{
	if (!is_user_logged_in())
		die('No hacking');

	global $wpdb;

	$user = wp_get_current_user();
	if (isset($_REQUEST['bbp_sig_target']))
		$id = addslashes($_REQUEST['bbp_sig_target']);
	else
		$id = $user->ID;
	$sig = addslashes($_POST['sig']);

	$wpdb->query('DELETE FROM '.$wpdb->prefix.'usermeta WHERE meta_key="bbp_signature" AND user_id='.$id);
	$wpdb->query('INSERT INTO '.$wpdb->prefix.'usermeta (meta_key, user_id, meta_value) VALUES ("bbp_signature", '.$id.', "'.$sig.'")');
}

?>
