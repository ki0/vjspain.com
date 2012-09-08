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

	require_once('Forum.php');

	//bb_db_version 2078
	class PhpbbToBpforums extends ForumConverter
	{
		public $forumLoginSrc;
		public $forumLoginDst;
		private $processAttachments;
		private $siteurl;
		
		public function __construct($forumSrc, $forumDst)
		{
			$wpdb = new wpdb($this->forumLoginDst->username, $this->forumLoginDst->password, $this->forumLoginDst->databasename, $this->forumLoginDst->hostname);

			$dbver = $wpdb->get_var('SELECT meta_value FROM '.$wpdb->prefix.'bb_meta WHERE meta_key="bb_db_version"');
			if ($dbver != '2078' || true)
				$this->fc_die('conversion is not supported');
			
			$this->siteurl = $wpdb->get_var('SELECT option_value FROM '.$wpdb->prefix.'options WHERE option_name="siteurl"');
			
			$this->processAttachments = false;
			$this->forumLoginSrc = $forumSrc;
		}
		
		//phpBB 3.0.8 to bbPress 2.0 beta 3
		public function convertForums()
		{
			$wpdb = new wpdb($this->forumLoginDst->username, $this->forumLoginDst->password, $this->forumLoginDst->databasename, $this->forumLoginDst->hostname);
			
			$fdb = new wpdb($this->forumLoginSrc->username, $this->forumLoginSrc->password, $this->forumLoginSrc->databasename, $this->forumLoginSrc->hostname);
			$fdb->show_errors();
			$wpdb->show_errors();

			$this->fc_echo('done processing forums<br/>');
		}
				
		//phpBB 3.0.8 to bbPress 2.0 beta 3
		public function convertPosts()
		{
			$wpdb = new wpdb($this->forumLoginDst->username, $this->forumLoginDst->password, $this->forumLoginDst->databasename, $this->forumLoginDst->hostname);
			
			$fdb = new wpdb($this->forumLoginSrc->username, $this->forumLoginSrc->password, $this->forumLoginSrc->databasename, $this->forumLoginSrc->hostname);
			$fdb->show_errors();
			$wpdb->show_errors();

			$this->fc_echo('done processing posts<br/>');
		}
		
		public function convertUsers()
		{
			$wpdb = new wpdb($this->forumLoginDst->username, $this->forumLoginDst->password, $this->forumLoginDst->databasename, $this->forumLoginDst->hostname);
			
			$fdb = new wpdb($this->forumLoginSrc->username, $this->forumLoginSrc->password, $this->forumLoginSrc->databasename, $this->forumLoginSrc->hostname);
			$fdb->show_errors();
			$wpdb->show_errors();
			
			error_reporting($level);
			return $subject;
		}

		public function cleanup()
		{
			global $wpdb;
		}
		
		public function fc_die($message)
		{
			global $wpdb;
			echo $message;
			echo '<script type="text/javascript">jQuery("#message-status").removeClass("updated"); jQuery("#message-status").addClass("error"); jQuery("#message-status").text("Conversion failed");</script>';
			$wpdb->print_error();
			die();
		}
		
		public function fc_echo($message)
		{
			echo '<script type="text/javascript">jQuery("#message").append("'.str_replace('\\','\\\\',$message).'"); jQuery("#message").scrollTop(jQuery("#message")[0].scrollHeight);</script>';
		}
	}
?>
