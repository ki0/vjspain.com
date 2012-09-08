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

	Plugin Name: ForumConverter-Auth
	Plugin URI: http://orsonteodoro.wordpress.com/forumconverter/
	Description: Post conversion support for backwards compatiblity for forum passwords and user passwords
	Version: 1.11
	Author: Orson Teodoro
	Author URI: http://orsonteodoro.wordpress.com/
	License: GPL2
*/

	require_once('PhpbbAuth.php');
	require_once('Forum.php');

	add_action('wp_authenticate', 'bridgePassword', 0, 2);
	add_filter('the_password_form', 'bridgeForumPassword');

	function bridgeForumPassword($form)
	{
		$bn = plugin_basename(__FILE__);
		$chunks = explode('/',$bn);
		$aurl = plugins_url().'/'.$chunks[0].'/wp-pass-ex.php';
		$fid = preg_replace('|.*"pwbox-([0-9]+)".*|is','$1',$form);
		$form = preg_replace('|action="(.*)"|','action="'.$aurl.'"',$form);
		$form = str_replace('<input type="submit"','<input type="hidden" name="forum_id" value="'.$fid.'" /><input type="submit"',$form);
		return $form;
	}

	function bridgePassword($username, $password)
	{
		global $wpdb;

		$result = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'users WHERE user_nicename="'.$wpdb->escape(strtolower(sanitize_user($username))).'"');
		if ($result !== NULL && $wpdb->num_rows > 0)
		{
			//phpbb authentication
			$pos = strpos($result->user_pass,'phpbb');
			if ($pos !== FALSE && $pos == 0)
			{
				$phpbbpassword = substr($result->user_pass, 5, strlen($result->user_pass));
				if (PhpbbAuth::phpbb_check_hash($password, $phpbbpassword))
				{
					wp_set_auth_cookie($result->ID);
					wp_redirect(site_url());
				}
			}
		}
		else
		{
			//wp authentication - do nothing and pass to other hook
		}
	}
?>
