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

	Plugin Name: ForumConverter
	Plugin URI: http://orsonteodoro.wordpress.com/forumconverter/
	Description: Converts a phpBB forum into a bbPress forum
	Version: 1.11
	Author: Orson Teodoro
	Author URI: http://orsonteodoro.wordpress.com/
	License: GPL2
*/

	//add plugin page support
	add_filter('plugin_action_links', 'add_settings_link', 10, 2 );

	function add_settings_link($links, $file) {
		static $this_plugin;
		if (!$this_plugin) 
			$this_plugin = plugin_basename(__FILE__);

		if ($file == $this_plugin){
			$settings_link = '<a href="options-general.php?page=fc-options">Settings</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}
	//add settings/options support
	include_once('fc-options.php');
?>
