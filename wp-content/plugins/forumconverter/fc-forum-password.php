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

	Plugin Name: ForumConverter-Password
	Plugin URI: http://orsonteodoro.wordpress.com/forumconverter/
	Description: Adds post conversion forum password change support
	Version: 1.11
	Author: Orson Teodoro
	Author URI: http://orsonteodoro.wordpress.com/
	License: GPL2
*/

	add_action('admin_footer', 'fc_forum_password',20);

	function fc_forum_password()
	{
		if (!isset($_GET['post']))
			return;
		
		global $wpdb;
		$pass = $wpdb->get_var('SELECT post_password FROM '.$wpdb->prefix.'posts WHERE ID='.addslashes($_GET['post']));
		echo <<<ADDUI
			<script type="text/javascript">
				out = 
				'<hr/>'+
				'	<p>' +
				'   <strong class="label">Password</strong>' +
				'	<label class="screen-reader-text" for="forum_password">Password</label>' +
				'	<input type="text" name="forum_password" id="forum_password" value="{$pass}" maxlength="20" />' +
				'   <p><small>(Use blank to remove password)</small></p>' +
				'	</p>' +
				'';
				jQuery("div#bbp_forum_attributes div.inside").append(out);
			</script>
ADDUI;
	}
	
	add_action('save_post', 'fc_forum_save_password');
	
	function fc_forum_save_password()
	{
		if (!isset($_POST['forum_password']))
			return;

		global $wpdb;
		$id = addslashes($_POST['post_ID']);
		$pass = addslashes($_POST['forum_password']);
		
		$oldpass = $wpdb->get_var('SELECT post_password FROM '.$wpdb->prefix.'posts WHERE post_id='.$id);
		if ($pass === $oldpass)
			return; //don't delete the phpbb forum hash

		$wpdb->query('UPDATE '.$wpdb->prefix.'posts SET post_password="'.$pass.'" WHERE ID='.$id);
		@$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE post_id='.$id.' AND meta_key="_fc_password"'); //hash for passwords >20 characters long
	}
?>
