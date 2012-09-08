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
*/

	//this is for the ajax update from buddypress

	require('../../../wp-load.php');

	if (!is_user_logged_in())
		die('No hacking');

	global $wpdb;

	$user = wp_get_current_user();
	$nicename = $user->user_nicename;
	$id = $user->ID;
	$sig = addslashes($_POST['sig']);
	$sig = wp_kses($sig, array('a' => array('href'=>array()), 'font' => array('color'=>array(), 'size'=>array(), 'face'=>array()), 'br' => array(), 'img'=>array('src'=>array(),'alt'=>array(),'class'=>array(),'style'=>array())));

	$wpdb->query('DELETE FROM '.$wpdb->prefix.'usermeta WHERE meta_key="bbp_signature" AND user_id='.$id);
	$wpdb->query('INSERT INTO '.$wpdb->prefix.'usermeta (meta_key, user_id, meta_value) VALUES ("bbp_signature", '.$id.', "'.$sig.'")');
	wp_redirect(get_site_url().'/members/'.$nicename.'/profile/?edit-forum-signature=true');
?>
