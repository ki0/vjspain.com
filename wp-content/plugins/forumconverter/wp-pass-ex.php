<?php
/**
 * Creates the password cookie and redirects back to where the
 * visitor was before.
 *
 * @package WordPress
 */

/** Make sure that the WordPress bootstrap has run before continuing. */
require( dirname(__FILE__) . '../../../../wp-load.php');

if ( get_magic_quotes_gpc() )
	$_REQUEST['post_password'] = stripslashes($_REQUEST['post_password']);
	global $wpdb;
	$password = $wpdb->get_var('SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key="_fc_password" AND post_id="'.addslashes($_REQUEST['forum_id']).'"');
	if ($password !== NULL)
	{
		$postpass = $wpdb->get_var('SELECT post_password FROM '.$wpdb->prefix.'posts WHERE ID='.addslashes($_REQUEST['forum_id']));
		if (PhpbbAuth::phpbb_check_hash($_REQUEST['post_password'], $password))
		{
			setcookie('wp-postpass_' . COOKIEHASH, $postpass, time() + 864000, COOKIEPATH);
		}
	}
	else //use wordpress passwords
		// 10 days
		setcookie('wp-postpass_' . COOKIEHASH, $_REQUEST['post_password'], time() + 864000, COOKIEPATH);

wp_safe_redirect(wp_get_referer());
exit;
?>
