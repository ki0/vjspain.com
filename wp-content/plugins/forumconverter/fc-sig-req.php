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

	require('../../../wp-load.php');
	global $wpdb;

	$id = $wpdb->get_var('SELECT post_author FROM '.$wpdb->prefix.'posts WHERE ID='.addslashes($_GET['pid']));
	$sig = $wpdb->get_var('SELECT meta_value FROM '.$wpdb->prefix.'usermeta WHERE user_id='.$id.' AND meta_key="bbp_signature"');
	$sig = apply_filters('bbp_get_topic_content',$sig);
	$sig = wp_kses($sig, array('a' => array('href'=>array()), 'font' => array('color'=>array(), 'size'=>array(), 'face'=>array()), 'br' => array(), 'img'=>array('src'=>array(),'alt'=>array(),'class'=>array(),'style'=>array())));

	$out = (object) array('pid' => $_GET['pid'], 'uid' => $id,'signature' => $sig);

	echo json_encode($out);
?>
