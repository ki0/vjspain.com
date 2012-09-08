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

// register settings used by this plugin
add_action('admin_init', 'register_fc_settings');

// create settings menu items in admin area
add_action('admin_menu', 'fc_create_menu');
add_action('admin_head', 'fc_css_done');

function fc_css_done() {
	echo <<<FC_CSS
	<style>
		div.done{background-color:#6afb92;border-color:#00ff00;}
	</style>
FC_CSS;
}

function fc_create_menu() {
	//create new top-level menu
	add_menu_page('ForumConverter Plugin Settings', 'ForumConverter Settings', 'manage_options', 'fc-options', 'fc_settings_page');
	add_options_page('ForumConverter Plugin Settings', 'ForumConverter Settings', 'manage_options', 'fc-options', 'fc_settings_page');
}

function register_fc_settings() {
	//register our settings
	register_setting('fc-settings-group', 'fc_src_hostname');
	register_setting('fc-settings-group', 'fc_src_databasename');
	register_setting('fc-settings-group', 'fc_src_username');
	register_setting('fc-settings-group', 'fc_src_password');
	register_setting('fc-settings-group', 'fc_src_type');
	register_setting('fc-settings-group', 'fc_src_version');
	register_setting('fc-settings-group', 'fc_src_prefix');
	register_setting('fc-settings-group', 'fc_src_upload_path');
	register_setting('fc-settings-group', 'fc_src_avatar_path');

	register_setting('fc-settings-group', 'fc_dst_type');
	register_setting('fc-settings-group', 'fc_dst_version');
	register_setting('fc-settings-group', 'fc_dst_use_bbcode_lite');

	register_setting('fc-settings-group', 'fc_dst_hostname');
	register_setting('fc-settings-group', 'fc_dst_databasename');
	register_setting('fc-settings-group', 'fc_dst_username');
	register_setting('fc-settings-group', 'fc_dst_password');
	register_setting('fc-settings-group', 'fc_dst_prefix');

	register_setting('fc-settings-group', 'fc_op_method', 'fc_op_method_validate' );

	add_settings_section('plugin_src', 'Source Forum', 'plugin_section_src', 'fc-options');

	add_settings_field('fc_src_hostname', 'Source server hostname', 'src_hostname_callback', 'fc-options', 'plugin_src');
	add_settings_field('fc_src_databasename', 'Source server database name', 'src_databasename_callback', 'fc-options', 'plugin_src');
	add_settings_field('fc_src_username', 'Source server username', 'src_username_callback', 'fc-options', 'plugin_src');
	add_settings_field('fc_src_password', 'Source server password', 'src_password_callback', 'fc-options', 'plugin_src');
	add_settings_field('fc_src_type', 'Source server type', 'src_type_callback', 'fc-options', 'plugin_src');
	add_settings_field('fc_src_version', 'Source server version', 'src_version_callback', 'fc-options', 'plugin_src');
	add_settings_field('fc_src_prefix', 'Source server prefix', 'src_prefix_callback', 'fc-options', 'plugin_src');
	add_settings_field('fc_src_upload_path', 'Source server upload path', 'src_upload_path_callback', 'fc-options', 'plugin_src');
	add_settings_field('fc_src_avatar_path', 'Source server avatar path', 'src_avatar_path_callback', 'fc-options', 'plugin_src');

	add_settings_section('plugin_dst', 'Destination Forum', 'plugin_section_dst', 'fc-options');

	add_settings_field('fc_dst_type', 'Destination server type', 'dst_type_callback', 'fc-options', 'plugin_dst');
	add_settings_field('fc_dst_version', 'Destination server version', 'dst_version_callback', 'fc-options', 'plugin_dst');
	add_settings_field('fc_dst_use_bbcode_lite', 'Use BBCode instead of HTML markup', 'dst_use_bbcode_lite_callback', 'fc-options', 'plugin_dst');
	
	/*
	add_settings_field('fc_dst_hostname', 'Destination server hostname', 'dst_hostname_callback', 'fc-options', 'plugin_dst');
	add_settings_field('fc_dst_databasename', 'Destination server database name', 'dst_databasename_callback', 'fc-options', 'plugin_dst');
	add_settings_field('fc_dst_username', 'Destination server username', 'dst_username_callback', 'fc-options', 'plugin_dst');
	add_settings_field('fc_dst_password', 'Destination server password', 'dst_password_callback', 'fc-options', 'plugin_dst');
	add_settings_field('fc_dst_prefix', 'Destination server prefix', 'dst_prefix_callback', 'fc-options', 'plugin_dst');	
	*/
}

function plugin_section_src() {
	echo '<p>Set up the database server info for the source forum.</p>';
}

function plugin_section_dst() {
	echo '<p>Set up the database server info for the destination forum.</p>';
}

function src_hostname_callback() {
	$options = get_option('fc_src_hostname');
	echo "<input name='fc_src_hostname' name='fc_src_hostname' size='40' type='text' value='{$options}' />";
}

function src_databasename_callback() {
	$options = get_option('fc_src_databasename');
	echo "<input name='fc_src_databasename' name='fc_src_databasename' size='40' type='text' value='{$options}' />";
}

function src_username_callback() {
	$options = get_option('fc_src_username');
	echo "<input id='fc_src_username' name='fc_src_username' size='40' type='text' value='{$options}' />";
}

function src_password_callback() {
	$options = get_option('fc_src_password');
	echo "<input id='fc_src_password' name='fc_src_password' size='40' type='password' value='{$options}' />";
}

function src_type_callback() {
	$options = get_option('fc_src_type');
	echo "<select id='fc_src_type' name='fc_src_type'>";
	echo "<option".(strpos($optons,'phpBB') !== FALSE ? " selected" : "").">phpBB</option>";
	echo "</select>";
}

function src_version_callback() {
	$options = get_option('fc_src_version');
	echo "<select id='fc_src_version' name='fc_src_version'>";
	echo "<option".(strpos($optons,'3.0.9') !== FALSE ? " selected" : "").">3.0.9</option>";
	echo "</select>";
}

function src_prefix_callback() {
	$options = get_option('fc_src_prefix');
	echo "<input id='fc_src_prefix' name='fc_src_prefix' size='40' type='text' value='{$options}' />";
}

function src_upload_path_callback() {
	$options = get_option('fc_src_upload_path');
	echo "<input id='fc_src_upload_path' name='fc_src_upload_path' size='40' type='text' value='{$options}' />";
}

function src_avatar_path_callback() {
	$options = get_option('fc_src_avatar_path');
	echo "<input id='fc_src_avatar_path' name='fc_src_avatar_path' size='40' type='text' value='{$options}' />";
}

function dst_type_callback() {
	$options = get_option('fc_dst_type');
	echo "<select id='fc_dst_type' name='fc_dst_type'>";
	echo "<option".(strpos($options,'bbPress') !== FALSE ? " selected" : "").">bbPress</option>";
	//echo "<option".(strpos($options,'BuddyPress Forums') !== FALSE ? " selected" : "").">BuddyPress Forums</option>";
	echo "</select>";
}

function dst_version_callback() {
	$options = get_option('fc_dst_version');
	echo "<select id='fc_dst_version' name='fc_dst_version'>";
	$type = get_option('fc_dst_type');	
	if ($type === 'bbPress')
		echo "<option".($options === '2.0' ? " selected" : "").">2.0</option>";
		echo "<option".($options === '2.0 release candidate 4' ? " selected" : "").">2.0 release candidate 4</option>";
		echo "<option".($options === '2.0 release candidate 3' ? " selected" : "").">2.0 release candidate 3</option>";
		echo "<option".($options === '2.0 release candidate 2' ? " selected" : "").">2.0 release candidate 2</option>";
		echo "<option".($options === '2.0 beta 3' ? " selected" : "").">2.0 beta 3</option>";
		echo "<option".($options === '2.0 beta 3b' ? " selected" : "").">2.0 beta 3b</option>";
	if ($type === 'BuddyPress Forums')
		echo "<option".($options ==='1.2.9' ? " selected" : "").">1.2.9</option>";
	echo "</select>";
}

function dst_use_bbcode_lite_callback() {
	$options = get_option('fc_dst_use_bbcode_lite');
	echo "<input id='fc_dst_use_bbcode_lite' name='fc_dst_use_bbcode_lite' type='checkbox' value='bbcode' ".(strpos($options,'bbcode') !== FALSE ? "checked" : "")." />";
}


function dst_hostname_callback() {
	$options = get_option('fc_dst_hostname');
	echo "<input class='dstlogin' id='fc_dst_hostname' name='fc_dst_hostname' size='40' type='text' value='{$options}' />";
}

function dst_databasename_callback() {
	$options = get_option('fc_dst_databasename');
	echo "<input class='dstlogin' name='fc_dst_databasename' name='fc_dst_databasename' size='40' type='text' value='{$options}' />";
}

function dst_username_callback() {
	$options = get_option('fc_dst_username');
	echo "<input class='dstlogin' id='fc_dst_username' name='fc_dst_username' size='40' type='text' value='{$options}' />";
}

function dst_password_callback() {
	$options = get_option('fc_dst_password');
	echo "<input class='dstlogin' id='fc_dst_password' name='fc_dst_password' size='40' type='password' value='{$options}' />";
}

function dst_prefix_callback() {
	$options = get_option('fc_dst_prefix');
	global $wpdb;
	echo "<input class='dstlogin' id='fc_dst_prefix' name='fc_dst_prefix' size='40' type='text' value='{$options}' /> This may be set to ".$wpdb->prefix."";
}

function fc_op_method_validate($input) {
	if (strpos($input, 'Convert Forum') !== FALSE)
		$o = 'convert';
	else if (strpos($input, 'Save Changes') !== FALSE)
		$o = 'save';
	return $o;
}
?>
<?php
function fc_settings_page() {
?>
<h2>ForumConverter</h2>

<form method="post" action="options.php">
    <div id="holder"></div>
	
    <?php settings_fields('fc-settings-group'); ?>
    <?php do_settings_sections('fc-options'); ?>

	<script type="text/javascript">
		jQuery('document').ready(function(){
			syncDestinationOptionsVisual();
		});
	
		jQuery(document).ready(function(){
			jQuery('#fc_dst_type').change( function(){
				syncDestinationOptionsVisual();
			});
		);
		
		function syncDestinationOptionsVisual()
		{
			if (jQuery('#fc_dst_type').val() == 'bbPress')
			{
				jQuery('#fc_dst_version').empty();
				jQuery('#fc_dst_version').append('<option>2.0</option>');
				jQuery('#fc_dst_version').append('<option>2.0 release candidate 4</option>');
				jQuery('#fc_dst_version').append('<option>2.0 release candidate 3</option>');
				jQuery('#fc_dst_version').append('<option>2.0 release candidate 2</option>');
				jQuery('#fc_dst_version').append('<option>2.0 beta 3</option>');
				jQuery('#fc_dst_version').append('<option>2.0 beta 3b</option>');

				jQuery('.dstlogin').attr('disabled','');
			}
			else if (jQuery('#fc_dst_type').val() == 'BuddyPress Forums')
			{
				jQuery('#fc_dst_version').empty();
				jQuery('#fc_dst_version').append('<option>1.2.9</option>');

				jQuery('.dstlogin').removeAttr('disabled');
			}
		}
	</script>

    <p class="submit">
    <input name="fc_op_method" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
    <input name="fc_op_method" type="submit" value="Convert Forum" />
    </p>
</form>

<?php
	//disable this message
	echo '<script type="text/javascript">jQuery("#setting-error-settings_updated").css("visibility","hidden"); jQuery("#setting-error-settings_updated").css("height","0");</script>';
	if (strpos(get_option('fc_op_method'), 'convert') !== FALSE && strpos(get_option('fc_op_method'), 'convert') == 0)
	{
		require_once('Forum.php');
		require_once('ForumConverter.php');
		require_once('PhpbbToBbpress.php');
		require_once('PhpbbToBpforums.php');
		update_option('fc_op_method','start'); //reset

		function convertForum()
		{
			echo '<script type="text/javascript">jQuery("#holder").append("<div id=\'fc_message-status\' class=\'updated below-h2\'>Please wait while conversion in progress...<br/></div>");</script>';
			echo '<script type="text/javascript">jQuery("#holder").append("<div id=\'fc_message\' class=\'updated below-h2\' style=\'max-height:150px;overflow:scroll;\'>starting conversion...<br/></div>");</script>';
			$forumSrc = new Forum(get_option('fc_src_hostname'), get_option('fc_src_databasename'), get_option('fc_src_username'), get_option('fc_src_password'), get_option('fc_src_prefix'), get_option('fc_src_type'), get_option('fc_src_version'), get_option('fc_src_upload_path'), get_option('fc_src_avatar_path'), array(get_option('fc_dst_use_bbcode_lite')));
			if (get_option('fc_src_type') === 'phpBB' && get_option('fc_dst_type') === 'bbPress')
				$converter = new PhpbbToBbpress($forumSrc);
			else if (get_option('fc_src_type') === 'phpBB' && get_option('fc_dst_type') === 'BuddyPress Forums')
			{
				require_once('../bb-config.php');
				$forumDst = new Forum(BBDB_HOST, BBDB_NAME, BBDB_USER, BBDB_PASSWORD, $bb_table_prefix, get_option('fc_dst_type'), get_option('fc_dst_version'), get_option('fc_dst_upload_path'), get_option('fc_src_avatar_path'), array());
				$converter = new PhpbbToBpforums($forumSrc, $forumDst);
			}
			echo '<script type="text/javascript">jQuery("#fc_message").append("cleaning up...<br/>"); jQuery("#fc_message").scrollTop(jQuery("#fc_message")[0].scrollHeight);</script>';
			$converter->cleanup();
			echo '<script type="text/javascript">jQuery("#fc_message").append("converting users...<br/>"); jQuery("#fc_message").scrollTop(jQuery("#fc_message")[0].scrollHeight);</script>';
			$converter->convertUsers();
			echo '<script type="text/javascript">jQuery("#fc_message").append("converting forums...<br/>"); jQuery("#fc_message").scrollTop(jQuery("#fc_message")[0].scrollHeight);</script>';
			$converter->convertForums();
			echo '<script type="text/javascript">jQuery("#fc_message").append("converting posts...<br/>"); jQuery("#fc_message").scrollTop(jQuery("#fc_message")[0].scrollHeight);</script>';
			$converter->convertPosts();
			echo '<script type="text/javascript">jQuery("#fc_message").append("cleaning up...<br/>"); jQuery("#fc_message").scrollTop(jQuery("#fc_message")[0].scrollHeight);</script>';
			$converter->cleanup();
			echo '<script type="text/javascript">jQuery("#fc_message").append("done converting...<br/>"); jQuery("#fc_message").scrollTop(jQuery("#fc_message")[0].scrollHeight);</script>';
			echo '<script type="text/javascript">jQuery("#fc_message-status").addClass("done"); jQuery("#fc_message-status").text("Conversion complete");</script>';
		}
		convertForum();
	}
	else if (strpos(get_option('fc_op_method'), 'save') !== FALSE && strpos(get_option('fc_op_method'), 'save') == 0)
	{
		update_option('fc_op_method','start'); //reset
		echo '<script type="text/javascript">jQuery("#holder").append("<div id=\'fc_message-status\' class=\'updated below-h2\'>Settings saved<br/></div>");</script>';
	}
	?>
<?php } ?>
