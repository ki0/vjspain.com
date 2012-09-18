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
	require_once('wp-db-ex.php');

	class PhpbbToBbpress extends ForumConverter
	{
		public $forumLoginSrc;
		private $processAttachments;
		private $siteurl;
		private $dstAvatarPath;
		
		public function __construct($forumSrc,$forumDst = NULL)
		{
			global $wpdb;
			
			$dbver = $wpdb->get_var('SELECT option_value FROM '.$wpdb->prefix.'options WHERE option_name="_bbp_db_version"');
			if (!in_array($dbver, array('110',   //2.0-rc-2, 2.0-beta-3, 2.0-beta-3b
			                            '155',   //2.0-rc-3
			                            '165',   //2.0-rc-4
			                            '200'    //2.0 final
			                            ))) 
#				$this->fc_die('conversion is not supported');
			
			$this->siteurl = site_url();
			$this->processAttachments = true;
			$this->forumLoginSrc = $forumSrc;

			//timeouts - this may require adjusting based on amount of posts that need to be converted
			set_time_limit(3600);

			$this->dstAvatarPath = '';
			if (file_exists(WP_PLUGIN_DIR.'/buddypress/bp-core/bp-core-avatars.php'))
			{
				$this->fc_echo('found buddypress<br/>');
				include_once(WP_PLUGIN_DIR.'/buddypress/bp-core/bp-core-avatars.php');
				bp_core_set_avatar_constants();

				if (defined('BP_AVATAR_UPLOAD_PATH'))
					$this->dstAvatarPath = BP_AVATAR_UPLOAD_PATH;        //BuddyPress 1.2.9
				if (function_exists('bp_core_avatar_upload_path'));
					$this->dstAvatarPath = bp_core_avatar_upload_path(); //BuddyPress 1.5-beta-2
			}
			$dblinks = $wpdb->get_var('SELECT option_value FROM '.$wpdb->prefix.'options WHERE option_name="permalink_structure"');
		}
		
		//phpBB 3.0.8 to bbPress 2.0 beta 3
		public function convertForums()
		{
			//global $wpdb;
			global $table_prefix;
			
			$wpdb = new wpdbex(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
			$wpdb->set_prefix($table_prefix);
			$fdb = new wpdbex($this->forumLoginSrc->username, $this->forumLoginSrc->password, $this->forumLoginSrc->databasename, $this->forumLoginSrc->hostname);
			$fdb->show_errors();
			$wpdb->show_errors();

			//trash existing forums
			
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'posts WHERE post_type="forum"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "%_bbp_forum_%"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "%_bbp_last_active_time%"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "%_fc_password%"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "%_bbp_status%"');
			
			//temporary maping table
			$wpdb->query('CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'fc_map_forums (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, phpbb_id MEDIUMINT(8), wp_id BIGINT(20), phpbb_parent_id MEDIUMINT(8), last_post_time int(11))');

			//get acl permission ids
			$perm_rwm = $fdb->get_var('SELECT role_id FROM '.$this->forumLoginSrc->prefix.'acl_roles WHERE role_name="ROLE_FORUM_FULL"');
			$perm_rw = $fdb->get_var('SELECT role_id FROM '.$this->forumLoginSrc->prefix.'acl_roles WHERE role_name="ROLE_FORUM_STANDARD"');
			$perm_r = $fdb->get_var('SELECT role_id FROM '.$this->forumLoginSrc->prefix.'acl_roles WHERE role_name="ROLE_FORUM_READONLY"');
			$perm_ = $fdb->get_var('SELECT role_id FROM '.$this->forumLoginSrc->prefix.'acl_roles WHERE role_name="ROLE_FORUM_NOACCESS"');

			//get groups
			$group_admin = $fdb->get_var('SELECT group_id FROM '.$this->forumLoginSrc->prefix.'groups WHERE group_name="ADMINISTRATORS"');
			$group_mod = $fdb->get_var('SELECT group_id FROM '.$this->forumLoginSrc->prefix.'groups WHERE group_name="GLOBAL_MODERATORS"');
			$group_reg = $fdb->get_var('SELECT group_id FROM '.$this->forumLoginSrc->prefix.'groups WHERE group_name="REGISTERED"');
			$group_reg_lt13 = $fdb->get_var('SELECT group_id FROM '.$this->forumLoginSrc->prefix.'groups WHERE group_name="REGISTERED_COPPA"');


			$forums_hidden = array();
			$forums_private = array();

			//transfer all forums first
			$this->fc_echo('converting forums<br/>');
			$srcforums = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'forums');
			if ($fdb->num_rows > 0)
			{
				//add special forum to collect orphaned topics
				$srcforum = (object) array(
					'forum_id' => '8388607', //medium int limit
					'parent_id' => '0', 
					'left_id' => '8388607', //medium int limit
					'right_id' => '8388607', //medium int limit
					'forum_parents' => '',
					'forum_name' => 'Orphaned Topics',
					'forum_desc' => 'ForumConverter collected orphaned topics',
					'forum_desc_bitfield' => '',
					'forum_desc_options' => '7',
					'forum_desc_uid' => '',
					'forum_link' => '',
					'forum_password' => '',
					'forum_style' => '0',
					'forum_image' => '',
					'forum_rules' => '',
					'forum_rules_link' => '',
					'forum_rules_bitfield' => '', 
					'forum_rules_options' => '7',
					'forum_rules_uid' => '',
					'forum_topics_per_page' => '0',
					'forum_type' => '1',
					'forum_status' => '0',
					'forum_posts' => '0',
					'forum_topics' => '0',
					'forum_topics_real' => '0',
					'forum_last_post_id' => '0',
					'forum_last_poster_id' => '0',
					'forum_last_post_subject' => '',
					'forum_last_post_time' => '0',
					'forum_last_poster_name' => '',
					'forum_last_poster_colour' => '',
					'forum_flags' => '32',
					'display_subforum_list' => '1',
					'display_on_index' => '0',
					'enable_indexing' => '1',
					'enable_icons' => '0',
					'enable_prune' => '0',
					'prune_next' => '0',
					'prune_days' => '7',
					'prune_viewed' => '7',
					'prune_freq' => '1',
					'forum_options' => '0');
				array_unshift($srcforums, $srcforum);
				
				foreach ($srcforums as $srcforum)
				{
					$status = $wpdb->insert($wpdb->prefix.'posts', 
						array(
							'post_author'           => 1,
							'post_date'             => $this->convertTimestamp(time()),
							'post_date_gmt'         => $this->convertTimestamp(time()),
							'post_content'          => $srcforum->forum_desc,
							'post_title'            => $this->convertTitle($srcforum->forum_name),
							'post_excerpt'          => '',
							'post_status'           => 'public', //publish=public,hidden,private assume all hidden
							'comment_status'        => 'closed',
							'ping_status'           => 'open',
							'post_password'         => '',
							'post_name'             => '',
							'to_ping'               => '',
							'pinged'                => '',
							'post_modified'         => $this->convertTimestamp(time()),
							'post_modified_gmt'     => $this->convertTimestamp(time()),
							'post_content_filtered' => '',
							'post_parent'           => 0,
							'guid'                  => '',
							'menu_order'            => $srcforum->left_id,
							'post_type'             => 'forum',
							'post_mime_type'        => '',
							'comment_count'         => 0
						), 
						array(
							'%d',//post_author
							'%s',//post_date
							'%s',//post_date_gmt
							'%s',//post_content
							'%s',//post_title
							'%s',//post_excerpt
							'%s',//post_status
							'%s',//comment_status
							'%s',//ping_status
							'%s',//post_password
							'%s',//post_name
							'%s',//to_ping
							'%s',//pinged
							'%s',//post_modified
							'%s',//post_modified_gmt
							'%s',//post_content_filtered
							'%d',//post_parent
							'%s',//guid
							'%d',//menu_order
							'%s',//post_type
							'%s',//post_mime_type
							'%d' //comment_count
						)
					);
					
					if ($status !== false)
						$this->fc_echo('converted forum '.$srcforum->forum_name.'<br/>');
					else
						$this->fc_die('failed to convert forum: '.$wpdb->last_error);
					$insertId = $wpdb->insert_id;

					$forums_hidden[$insertId] = true;
					
					//fix name for permalinks
					$r = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_name LIKE "%'.$this->convertName($srcforum->forum_name).'%"');
					$status = $wpdb->update($wpdb->prefix.'posts',
						array('post_name' => $this->convertName($srcforum->forum_name)),#.($wpdb->num_rows == 0 ? '' : '-'.($wpdb->num_rows + 1))),
						array('ID' => $insertId),
						array('%s'),
						array('%d')
					);
					if ($status === false)
						$this->fc_die('failed to update name: '.$wpdb->last_error);
					
					//generate guid
					$status = $wpdb->update($wpdb->prefix.'posts',
//						array('guid' => $this->siteurl.'/?post_type=forum&#038;p='.$insertId),
						array('guid' => $this->siteurl.'/?p='.$insertId),
						array('ID' => $insertId),
						array('%s'),
						array('%d')
					);
					if ($status === false)
						$this->fc_die('failed to update guid: '.$wpdb->last_error);
					
					$status = $wpdb->update($wpdb->prefix.'posts',
            array('post_name' => $this->convertName($srcforum->forum_name)),	
  #          array('post_name' => $insertId), //intentional no seo crap
						array('ID' => $insertId),
						array('%s'),
						array('%d')
					);
					if ($status === false)
						$this->fc_die('failed to update post name: '.$wpdb->last_error);

					//clean out the old meta
					$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE post_id='.$insertId);

					//password protect
					if (strlen($srcforum->forum_password) > 0)
					{
						//wordpress stores this in plaintext... obfuscate it
						$status = $wpdb->update($wpdb->prefix.'posts',
							array('post_password' => substr(hash('sha1',$srcforum->forum_password),0,20)),
							array('ID' => $insertId),
							array('%s'),
							array('%d')
						);
						if ($status === false)
							$this->fc_die('failed update password: '.$wpdb->last_error);
							

						$wpdb->insert($wpdb->prefix.'postmeta', 
							array( 'post_id'    => $insertId, 
								   'meta_key'   => '_fc_password', 
								   'meta_value' => $srcforum->forum_password
							), 
							array( '%d',//post_id
								   '%s',//meta_key
								   '%s' //meta_value
							)
						);
					}
					
					//fix acl we assumed all forums were hidden
					//14 //full
					//15 //standard  +r +w
					//17 //read only +r
					//16 //no access -r
					
					//start state of every form is hidden and writeable
					//everything is based on registered user
					//			  viewable?     starttopic?
					//noaccess       n               n
					//readonly       y               n
					//limited        y               y
					//std            y               y
					//full           y               y
					//modqueue       y               y
					//bot            y               n
					//new registered n               n

					//assume writeable forum
					$status = $wpdb->insert($wpdb->prefix.'postmeta', 
						array( 'post_id'    => $insertId, 
							   'meta_key'   => '_bbp_status', 
							   'meta_value' => 'open'
						), 
						array( '%d',//post_id
							   '%s',//meta_key
							   '%s' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed set meta for _bbp_status: '.$wpdb->last_error);

					if ($srcforum->forum_id != 8388607) //skip if missing forum type. Missing is already marked hidden
					{
						//unhide for members and make writable (or unlocked)
						//reduce to standard user
						$status = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'acl_groups WHERE forum_id='.$srcforum->forum_id.' AND ((group_id='.$group_reg.' AND auth_role_id='.$perm_rw.') OR (group_id='.$group_reg_lt13.' AND auth_role_id='.$perm_rw.') OR (group_id='.$group_reg.' AND auth_role_id='.$perm_rwm.') OR (group_id='.$group_reg_lt13.' AND auth_role_id='.$perm_rwm.'))');
						if ($status === false)
							$this->fc_die('failed to check acl for members: '.$fdb->last_error);
						if ($fdb->num_rows > 0)
						{
							$this->fc_echo('marking forum rw<br/>');
							//make it visible to public
							$status = $wpdb->update($wpdb->prefix.'posts',
								array('post_status' => 'publish'),
								array('ID' => $insertId),
								array('%s'),
								array('%d')
							);
							if ($status === false)
								$this->fc_die('failed to make forum public: '.$wpdb->last_error);

							//force open
							$status = $wpdb->update($wpdb->prefix.'postmeta',
								array('meta_value' => 'open'),
								array('meta_key' => '_bbp_status', 
									  'post_id' => $insertId),
								array('%s'), //meta_value
								array('%s',  //meta_key
									  '%s')  //post_id
							);
							if ($status === false)
								$this->fc_die('failed to update name: '.$wpdb->last_error);

							if (isset($forums_hidden[$insertId]))
								unset($forums_hidden[$insertId]);
						}

						//unhide for members and make readonly (or locked)
						$status = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'acl_groups WHERE forum_id='.$srcforum->forum_id.' AND ((group_id='.$group_reg.' AND auth_role_id='.$perm_r.') OR (group_id='.$group_reg_lt13.' AND auth_role_id='.$perm_r.'))');
						if ($status === false)
							$this->fc_die('failed to check acl for members: '.$fdb->last_error);
						if ($fdb->num_rows > 0)
						{
							$this->fc_echo('marking forum r<br/>');
							//make it visible to public
							$status = $wpdb->update($wpdb->prefix.'posts',
								array('post_status' => 'publish'),
								array('ID' => $insertId),
								array('%s'),
								array('%d')
							);
							if ($status === false)
								$this->fc_die('failed to make forum public: '.$wpdb->last_error);

							//force readonly
							$status = $wpdb->update($wpdb->prefix.'postmeta',
								array('meta_value' => 'closed'),
								array('meta_key' => '_bbp_status', 
									  'post_id' => $insertId),
								array('%s'), //meta_value
								array('%s',  //meta_key
									  '%s')  //post_id
							);
							if ($status === false)
								$this->fc_die('failed to update name: '.$wpdb->last_error);

							if (isset($forums_hidden[$insertId]))
								unset($forums_hidden[$insertId]);
						}

						//hidden for members and make readonly (or locked)
						//no access means no post in phpbb
						$status = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'acl_groups WHERE forum_id='.$srcforum->forum_id.' AND ((group_id='.$group_reg.' AND auth_role_id='.$perm_.') OR (group_id='.$group_reg_lt13.' AND auth_role_id='.$perm_.'))');
						if ($status === false)
							$this->fc_die('failed to check acl for members: '.$fdb->last_error);
						if ($fdb->num_rows > 0)
						{
							$this->fc_echo('marking forum -r<br/>');
							//make it visible to public
							$status = $wpdb->update($wpdb->prefix.'posts',
								array('post_status' => 'hidden'),
								array('ID' => $insertId),
								array('%s'),
								array('%d')
							);
							if ($status === false)
								$this->fc_die('failed to make forum public: '.$wpdb->last_error);

							//force readonly
							$status = $wpdb->update($wpdb->prefix.'postmeta',
								array('meta_value' => 'closed'),
								array('meta_key' => '_bbp_status', 
									  'post_id' => $insertId),
								array('%s'), //meta_value
								array('%s',  //meta_key
									  '%s')  //post_id
							);
							if ($status === false)
								$this->fc_die('failed to update name: '.$wpdb->last_error);

							$forums_hidden[$insertId] = true;
						}
					}
					
					//add meta
					$status = $wpdb->insert($wpdb->prefix.'postmeta', 
						array( 'post_id'    => $insertId, 
							   'meta_key'   => '_bbp_total_reply_count', 
							   'meta_value' => ($srcforum->forum_posts - $srcforum->forum_topics)
						), 
						array( '%d',//post_id
							   '%s',//meta_key
							   '%s' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed set meta for _bbp_total_reply_count: '.$wpdb->last_error);

					$status = $wpdb->insert($wpdb->prefix.'postmeta', 
						array( 'post_id'    => $insertId, 
							   'meta_key'   => '_bbp_total_topic_count', 
							   'meta_value' => $srcforum->forum_topics
						), 
						array( '%d',//post_id
							   '%s',//meta_key
							   '%s' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed set meta for _bbp_total_topic_count: '.$wpdb->last_error);
					
					$status = $wpdb->insert($wpdb->prefix.'postmeta', 
						array( 'post_id'    => $insertId, 
							   'meta_key'   => '_bbp_last_active_time', 
							   'meta_value' => $this->convertTimestamp($srcforum->forum_last_post_time)
						), 
						array( '%d',//post_id
							   '%s',//meta_key
							   '%s' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed set meta for _bbp_last_active_time: '.$wpdb->last_error);

					//create mapping
					$status = $wpdb->insert($wpdb->prefix.'fc_map_forums', 
						array( 'phpbb_id'             => $srcforum->forum_id, 
							   'wp_id'                => $insertId, 
							   'phpbb_parent_id' 	  => $srcforum->parent_id,
							   'last_post_time'       => $srcforum->forum_last_post_time
						), 
						array( '%d',//post_id
							   '%d',//meta_key
							   '%d' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed create mapping: '.$wpdb->last_error);
					
				}
			}
			
			//reconnect parent/child using wordpress ids
			$this->fc_echo('relinking forum hierarchy<br/>');
			$forums = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'fc_map_forums WHERE phpbb_parent_id<>0');
			if ($forums === false)
				$this->fc_die('failed to get children: '.$wpdb->last_error);
      $this->fc_echo('number of relinking forums:'. $wpdb->num_rows);
			if ($wpdb->num_rows > 0)
			{
				foreach ($forums as $forum)
				{
					//$this->fc_echo('linking forum #'.$forum->id.'<br/>');
					$parent = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_forums WHERE phpbb_id='.$forum->phpbb_parent_id);
					if ($parent === NULL)
						$this->fc_die('failed to obtain forum id:'.$wpdb->last_error);

					$status = $wpdb->update($wpdb->prefix.'posts',
						array('post_parent' => $parent->wp_id),
						array('ID' => $forum->wp_id),
						array('%d'),
						array('%d')
					);						
					if ($status === false)
						$this->fc_die('error linking parent to child: '.$wpdb->last_error);
				}
			}

			//generate subcounts for subforums
			$this->fc_echo('fixing subcounts<br/>');
			$forums = $wpdb->get_results('SELECT *, count(*) AS subforumcnt FROM '.$wpdb->prefix.'fc_map_forums WHERE phpbb_parent_id<>0 GROUP BY phpbb_parent_id');
			if ($forums === false)
				$this->fc_die('failed to get children: '.$wpdb->last_error);
			if ($wpdb->num_rows > 0)
			{
				foreach ($forums as $forum)
				{
					$parent = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_forums WHERE phpbb_id='.$forum->phpbb_parent_id);
					if ($parent === NULL)
						$this->fc_echo('failed to get parent info: '.$wpdb->last_error);
					$status = $wpdb->insert($wpdb->prefix.'postmeta', 
						array( 'post_id'    => $parent->wp_id, 
							   'meta_key'   => '_bbp_forum_subforum_count', 
							   'meta_value' => $forum->subforumcnt
						), 
						array( '%d',//post_id
							   '%s',//meta_key
							   '%s'//meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed to add subforum count: '.$wpdb->last_error);			
				}
			}
			
			//remove failed conversion data
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'options WHERE option_name="_bbp_private_forums"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'options WHERE option_name="_bbp_hidden_forums"');

			//private forums
			$out = '';
			$index = 0;
			foreach($forums_private as $k)
			{
				$out .= 'i:'.$index.';i:'.$k.';';
				$index++;
			}
			$status = $wpdb->insert($wpdb->prefix.'options', 
				array( 'option_id' => 0, 
					   'option_name'   => '_bbp_private_forums', 
					   'option_value' => 'a:'.(count($forums_private)+1).':{i:0;s:0:"";'.$out.'}',
					   'autoload' => 'yes'
				), 
				array( '%d',//blog_id
					   '%s',//option_name
					   '%s',//option_value
					   '%s' //autoload
				)
			);
			if ($status === false)
				$this->fc_die('failed set meta for _bbp_private_forums: '.$wpdb->last_error);

			//hidden forums
			$out = '';
			$index = 0;
			foreach($forums_hidden as $k)
			{
				$out .= 'i:'.$index.';i:'.$k.';';
				$index++;
			}
			$status = $wpdb->insert($wpdb->prefix.'options', 
				array( 'option_id' => 0, 
					   'option_name'   => '_bbp_hidden_forums', 
					   'option_value' => 'a:'.(count($forums_hidden)+1).':{i:0;s:0:"";'.$out.'}',
					   'autoload' => 'yes'
				), 
				array( '%d',//blog_id
					   '%s',//option_name
					   '%s',//option_value
					   '%s' //autoload
				)
			);
			if ($status === false)
				$this->fc_die('failed set meta for _bbp_hidden_forums: '.$wpdb->last_error);
			
			$this->fc_echo('done processing forums<br/>');
		}
				
		//phpBB 3.0.8 to bbPress 2.0 beta 3
		public function convertPosts()
		{
			//global $wpdb;
			global $table_prefix;
			
			$wpdb = new wpdbex(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
			$wpdb->set_prefix($table_prefix);
			$fdb = new wpdbex($this->forumLoginSrc->username, $this->forumLoginSrc->password, $this->forumLoginSrc->databasename, $this->forumLoginSrc->hostname);
			//$fdb->query("set session wait_timeout=1");
			$fdb->show_errors();
			$wpdb->show_errors();

			//trash existing topics
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'posts WHERE post_type="topic"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'posts WHERE post_type="reply"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'posts WHERE post_type="attachment" AND guid LIKE "%bbpress%"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "_bbp_"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "_bbp_sticky_topics"');

			//trash uploads
			$dir = wp_upload_dir();
			@$this->deldir($dir['basedir'].DIRECTORY_SEPARATOR.'bbpress');

			//temporary maping table
			$wpdb->query('CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'fc_map_posts (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, phpbb_id MEDIUMINT(8), wp_id BIGINT(20), phpbb_forum_id MEDIUMINT(8), phpbb_poster_id MEDIUMINT(8), phpbb_topic_id MEDIUMINT(8))');
			
			$this->fc_echo('converting posts<br/>');
			$fdb->disconnect();
			$srcposts = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'posts');
			$wpdb->disconnect();
			$dstposts = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'posts');
			if ($fdb->num_rows > 0)
			{
				foreach ($srcposts as $data)
				{
					//link
					$this->fc_echo('converting '.$data->post_subject.'<br/>');	
					$status = $wpdb->insert($wpdb->prefix.'posts', 
						array(
							'post_author'           => 1,
							'post_date'             => $this->convertTimestamp($data->post_time),
							'post_date_gmt'         => $this->convertTimestamp($data->post_time),
							'post_content'          => '',
							'post_title'            => $this->convertTitle($data->post_subject),
							'post_excerpt'          => '',
							'post_status'           => 'publish',
							'comment_status'        => 'closed',
							'ping_status'           => 'open',
							'post_password'         => '',
							'post_name'             => '',
							'to_ping'               => '',
							'pinged'                => '',
							'post_modified'         => $this->convertTimestamp($data->post_time),
							'post_modified_gmt'     => $this->convertTimestamp($data->post_time),
							'post_content_filtered' => '',
							'post_parent'           => 0,
							'guid'                  => '',
							'menu_order'            => $data->left_id,
							'post_type'             => 'topic',
							'post_mime_type'        => '',
							'comment_count'         => 0
						), 
						array(
							'%d',//post_author
							'%s',//post_date
							'%s',//post_date_gmt
							'%s',//post_content
							'%s',//post_title
							'%s',//post_excerpt
							'%s',//post_status
							'%s',//comment_status
							'%s',//ping_status
							'%s',//post_password
							'%s',//post_name
							'%s',//to_ping
							'%s',//pinged
							'%s',//post_modified
							'%s',//post_modified_gmt
							'%s',//post_content_filtered
							'%d',//post_parent
							'%s',//guid
							'%d',//menu_order
							'%s',//post_type
							'%s',//post_mime_type
							'%d' //comment_count
						)
					);
					if ($status === false)
					{
						$this->fc_echo('<br/>raw: '.$data->post_text.'<br/>');
						$this->fc_echo('<br/>convertPost output: '.$this->convertPost($data->post_text).'<br/>');
						$this->fc_die('failed to convert post: '.$wpdb->last_error);
					}
					
					$insertId = $wpdb->insert_id;
					
					//fix name for permalinks
					$r = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_name LIKE "%'.$this->convertName($data->post_subject).'%"');
					$status = $wpdb->update($wpdb->prefix.'posts',
						array('post_name' => $this->convertName($data->post_subject).($wpdb->num_rows == 0 ? '' : '-'.($wpdb->num_rows + 1))),
						array('ID' => $insertId),
						array('%s'),
						array('%d')
					);
					if ($status === false)
						$this->fc_die('failed to update name: '.$wpdb->last_error);
					
					$status = $wpdb->insert($wpdb->prefix.'fc_map_posts',
						array( 'phpbb_id'        => $data->post_id,
							   'wp_id'           => $insertId,
							   'phpbb_forum_id'  => $data->forum_id,
							   'phpbb_poster_id' => $data->poster_id,
							   'phpbb_topic_id'  => $data->topic_id
						),
						array( '%d',//phpbb_id
							   '%d',//wp_id
							   '%d',//phpbb_forum_id
							   '%d',//phpbb_poster_id
							   '%d' //phpbb_topic_id
						)
					);
					if ($status === false)
						$this->fc_die('failed to insert into post map table: '.$wpdb->last_error);

					//fix post text and attachment
					$postText = $data->post_text;
					if ($this->processAttachments)
					{
						if (strpos($postText, '[attachment=') !== FALSE)
						{
							//first split it
							$arr = preg_split('|(\[attachment=[0-9]+\:[a-z0-9]+\]<!-- ia[0-9]+ -->[^<>]+<!-- ia[0-9]+ -->\[\/attachment\:[a-z0-9]+\])|', $postText,-1,PREG_SPLIT_DELIM_CAPTURE);
							
							$postText = '';
							foreach($arr as $k => $v)
							{
								if (strpos($v,'[attachment=') !== FALSE)
								{
									//grab the filename
									//$a = array($v);
									$filename = preg_replace('|\[attachment=[0-9]+\:[a-z0-9]+\]<!-- ia[0-9]+ -->([^<>]+)<!-- ia[0-9]+ -->\[\/attachment\:[a-z0-9]+\]|im','\1',$v);
									$dcount = 0;
									if ($data->post_attachment)
									{
										$this->fc_echo('adding inline attachment<br/>');

										//create a folder to dump the attachments
										$dir = wp_upload_dir();
										if (is_dir ($dir['basedir'].DIRECTORY_SEPARATOR.'bbpress'.DIRECTORY_SEPARATOR.$insertId) == FALSE)
											mkdir($dir['basedir'].DIRECTORY_SEPARATOR.'bbpress'.DIRECTORY_SEPARATOR.$insertId,0644,true);

										//insert as attachment
										$attachment = $fdb->get_row('SELECT * FROM '.$this->forumLoginSrc->prefix.'attachments WHERE post_msg_id='.$data->post_id.' AND real_filename="'.$filename.'"');

										//try to maintain the original filename name without any extra fancy script
										//copy attachment to wordpress uploads folder
										$ofilename = $filename;
										if (file_exists($dir['basedir'].DIRECTORY_SEPARATOR.'bbpress'.DIRECTORY_SEPARATOR.$insertId.DIRECTORY_SEPARATOR.$filename))
										{
											$filename = $dcount . '_' . $filename;
											$dcount++;
										}
										$status = @copy($this->forumLoginSrc->uploadpath.DIRECTORY_SEPARATOR.$attachment->physical_filename, $dir['basedir'].DIRECTORY_SEPARATOR.'bbpress'.DIRECTORY_SEPARATOR.$insertId.DIRECTORY_SEPARATOR.$filename);
										if ($status == FALSE)
											$this->fc_echo('failed to copy file<br/>');

										$attachmenturl = content_url().'/uploads/bbpress/'.$insertId.'/'.$filename;
										
										//check filename
										$results = $wpdb->query('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_name LIKE "%'.$this->convertName($filename).'%"');
										$pname = $this->convertName($filename).($wpdb->num_rows == 0 ? '' : '-'.($wpdb->num_rows+1));
										
										$status = $wpdb->insert($wpdb->prefix.'posts', 
											array(
												'post_author'           => 1,
												'post_date'             => $this->convertTimestamp($data->post_time),
												'post_date_gmt'         => $this->convertTimestamp($data->post_time),
												'post_content'          => '',
												'post_title'            => $ofilename,
												'post_excerpt'          => '',
												'post_status'           => 'publish',
												'comment_status'        => 'closed',
												'ping_status'           => 'open',
												'post_password'         => '',
												'post_name'             => $pname,
												'to_ping'               => '',
												'pinged'                => '',
												'post_modified'         => $this->convertTimestamp($data->post_time),
												'post_modified_gmt'     => $this->convertTimestamp($data->post_time),
												'post_content_filtered' => '',
												'post_parent'           => $insertId,
												'guid'                  => $attachmenturl,
												'menu_order'            => $data->left_id,
												'post_type'             => 'attachment',
												'post_mime_type'        => $attachment->post_mime_type,
												'comment_count'         => 0
											), 
											array(
												'%d',//post_author
												'%s',//post_date
												'%s',//post_date_gmt
												'%s',//post_content
												'%s',//post_title
												'%s',//post_excerpt
												'%s',//post_status
												'%s',//comment_status
												'%s',//ping_status
												'%s',//post_password
												'%s',//post_name
												'%s',//to_ping
												'%s',//pinged
												'%s',//post_modified
												'%s',//post_modified_gmt
												'%s',//post_content_filtered
												'%d',//post_parent
												'%s',//guid
												'%d',//menu_order
												'%s',//post_type
												'%s',//post_mime_type
												'%d' //comment_count
											)
										);
					
										if (strpos($attachment->mimetype,'image/') !== FALSE)
											$postText .= '<img src="'.$attachmenturl.'" />';
										else
											$postText .= '<p>Download <a href="'.$attachmenturl.'">'.$attachment->real_filename.'</a>. (Caution: This file may not be virus scanned.)</p>';
									}
									else
										//just return the filename to mimic phpbb
										$postText .= '<p>'.$filename.'</p>';
								}
								else
									$postText .= $v;
							}
						}
						else
						{

							//lookup attachment
							$attachments = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'attachments WHERE post_msg_id='.$data->post_id);
							if ($attachments === false)
								$this->fc_die('failed to check attachment'.$fdb->last_error);
							if ($wpdb->num_rows > 0)
							{
								$dcount = 0;
								foreach ($attachments as $attachment)
								{
									//add download link
									if ($data->post_attachment)
									{
										$this->fc_echo('adding post/reply attachment<br/>');

										//create a folder to dump the attachments
										$dir = wp_upload_dir();
										if (is_dir ($dir['basedir'].DIRECTORY_SEPARATOR.'bbpress'.DIRECTORY_SEPARATOR.$insertId) == FALSE)
											mkdir($dir['basedir'].DIRECTORY_SEPARATOR.'bbpress'.DIRECTORY_SEPARATOR.$insertId,0644,true);
										
										//try to maintain the original filename name without any extra fancy script
										//copy attachment to wordpress uploads folder
										$filename = $attachment->real_filename;
										$ofilename = $filename;
										if (file_exists($dir['basedir'].DIRECTORY_SEPARATOR.'bbpress'.DIRECTORY_SEPARATOR.$insertId.DIRECTORY_SEPARATOR.$filename))
										{
											$filename = $dcount . '_' . $filename;
											$dcount++;
										}
										$status = @copy($this->forumLoginSrc->uploadpath.DIRECTORY_SEPARATOR.$attachment->physical_filename, $dir['basedir'].DIRECTORY_SEPARATOR.'bbpress'.DIRECTORY_SEPARATOR.$insertId.DIRECTORY_SEPARATOR.$filename);
										if ($status == FALSE)
											$this->fc_echo('failed to copy file<br/>');

										$attachmenturl = content_url().'/uploads/bbpress/'.$insertId.'/'.$filename;
										
										//check filename
										$results = $wpdb->query('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_name LIKE "%'.$this->convertName($filename).'%"');
										$pname = $this->convertName($filename).($wpdb->num_rows == 0 ? '' : '-'.($wpdb->num_rows+1));
										
										$status = $wpdb->insert($wpdb->prefix.'posts', 
											array(
												'post_author'           => 1,
												'post_date'             => $this->convertTimestamp($data->post_time),
												'post_date_gmt'         => $this->convertTimestamp($data->post_time),
												'post_content'          => '',
												'post_title'            => $ofilename,
												'post_excerpt'          => '',
												'post_status'           => 'publish',
												'comment_status'        => 'closed',
												'ping_status'           => 'open',
												'post_password'         => '',
												'post_name'             => $pname,
												'to_ping'               => '',
												'pinged'                => '',
												'post_modified'         => $this->convertTimestamp($data->post_time),
												'post_modified_gmt'     => $this->convertTimestamp($data->post_time),
												'post_content_filtered' => '',
												'post_parent'           => $insertId,
												'guid'                  => $attachmenturl,
												'menu_order'            => $data->left_id,
												'post_type'             => 'attachment',
												'post_mime_type'        => $attachment->post_mime_type,
												'comment_count'         => 0
											), 
											array(
												'%d',//post_author
												'%s',//post_date
												'%s',//post_date_gmt
												'%s',//post_content
												'%s',//post_title
												'%s',//post_excerpt
												'%s',//post_status
												'%s',//comment_status
												'%s',//ping_status
												'%s',//post_password
												'%s',//post_name
												'%s',//to_ping
												'%s',//pinged
												'%s',//post_modified
												'%s',//post_modified_gmt
												'%s',//post_content_filtered
												'%d',//post_parent
												'%s',//guid
												'%d',//menu_order
												'%s',//post_type
												'%s',//post_mime_type
												'%d' //comment_count
											)
										);

										$postText .= '<p>Download <a href="'.$attachmenturl.'">'.$attachment->real_filename.'</a>. (Caution: This file may not be virus scanned.)</p>';
									}
								}
							}
						}
					}
					$status = $wpdb->update($wpdb->prefix.'posts',
						array('post_content' => $this->convertPost($postText)),
						array('ID' => $insertId),
						array('%s'),
						array('%d')
					);
					if ($status === false)
						$this->fc_die('failed to update post content: '.$wpdb->last_error);

					//clean out the old meta
					$status = $wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE post_id='.$insertId);

					//fix metadata
					$status = $wpdb->insert($wpdb->prefix.'postmeta',
						array( 'post_id'    => $insertId, 
							   'meta_key'   => '_bbp_author_ip', 
							   'meta_value' => $data->poster_ip
						), 
						array( '%d',//post_id
							   '%s',//meta_key
							   '%s' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed to set metadata: '.$wpdb->last_error);
				}
			}

			//now link the post to the user and the forum/subforum
			$this->fc_echo('relink top level posts to forum and subforums<br/>');
			$posts = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'fc_map_posts');
			if ($posts === false)
				$this->fc_die('failed to obtain post map table: '.$wpdb->last_error);
			if ($wpdb->num_rows > 0)
			{
				foreach($posts as $post)
				{
					$this->fc_echo('linking post #'.$post->id.'<br/>');
					//topic_first_post_id
					$topic = $fdb->get_row('SELECT * FROM '.$this->forumLoginSrc->prefix.'topics WHERE topic_first_post_id='.$post->phpbb_id);
					if ($topic !== NULL)
					{
						$this->fc_echo('marking as topic<br/>');
						//locate forum/subforum
						$forum = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_forums WHERE phpbb_id='.$post->phpbb_forum_id);
						if ($forum === NULL)
						{
							$this->fc_echo('Problem locating forum attaching it to Missing<br/>');
							
							//link it to missing
							$forum = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_forums WHERE phpbb_id=8388607');
						}

						//force it as topic
						//link toplic to forum or subforum
						$status = $wpdb->update($wpdb->prefix.'posts',
							array('post_parent' => $forum->wp_id,
							      'post_type'   => 'topic'),
							array('ID' => $post->wp_id),
							array('%d','%s'),
							array('%d')
						);
						if ($status === false)
							$this->fc_die('failed to topic to forum or subforum'.$wpdb->last_error);
							
						//correct location
						$name = $wpdb->get_var('SELECT post_name FROM '.$wpdb->prefix.'posts WHERE ID='.$post->wp_id);
						$status = $wpdb->update($wpdb->prefix.'posts',
//							array('guid' => $this->siteurl.'?topic='.$name),
							array('guid' => $this->siteurl.'/?p='.$post->wp_id),
							array('ID'   => $post->wp_id),
							array('%s'),
							array('%d')
						);						
						if ($status === false)
							$this->fc_die('failed to update guid: '.$wpdb->last_error);

						//locate post
						$lastReply = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_posts WHERE phpbb_id='.$topic->topic_last_post_id);
						if ($lastReply === NULL)
							$this->fc_die('failed to get topic'.$wpdb->last_error);

						//more meta
						$status = $wpdb->insert($wpdb->prefix.'postmeta',
							array( 'post_id'    => $post->wp_id, 
								   'meta_key'   => '_bbp_forum_id', 
								   'meta_value' => $forum->wp_id
							), 
							array( '%d',//post_id
								   '%s',//meta_key
								   '%s' //meta_value
							)
						);
						if ($status === false)
							$this->fc_die('error inserting _bbp_forum_id'.$wpdb->last_error);
						$status = $wpdb->insert($wpdb->prefix.'postmeta',
							array( 'post_id'    => $post->wp_id, 
								   'meta_key'   => '_bbp_topic_id', 
								   'meta_value' => $post->wp_id
							), 
							array( '%d',//post_id
								   '%s',//meta_key
								   '%s' //meta_value
							)
						);
						if ($status === false)
							$this->fc_die('error inserting _bbp_topic_id'.$wpdb->last_error);
						$status = $wpdb->insert($wpdb->prefix.'postmeta',
							array( 'post_id'    => $post->wp_id, 
								   'meta_key'   => '_bbp_last_reply_id',
								   'meta_value' => $lastReply->wp_id
							), 
							array( '%d',//post_id
								   '%s',//meta_key
								   '%s' //meta_value
							)
						);
						if ($status === false)
							$this->fc_die('error inserting _bbp_last_reply_id'.$wpdb->last_error);
						$status = $wpdb->insert($wpdb->prefix.'postmeta',
							array( 'post_id'    => $post->wp_id, 
								   'meta_key'   => '_bbp_last_active_id', 
								   'meta_value' => $lastReply->wp_id
							), 
							array( '%d',//post_id
								   '%s',//meta_key
								   '%s' //meta_value
							)
						);
						if ($status === false)
							$this->fc_die('error inserting _bbp_last_active_id'.$wpdb->last_error);
						$status = $wpdb->insert($wpdb->prefix.'postmeta',
							array( 'post_id'    => $post->wp_id, 
								   'meta_key'   => '_bbp_last_active_time', 
								   'meta_value' => $this->convertTimestamp($topic->topic_last_post_time)
							), 
							array( '%d',//post_id
								   '%s',//meta_key
								   '%s' //meta_value
							)
						);
						if ($status === false)
							$this->fc_die('error inserting _bbp_last_active_time'.$wpdb->last_error);
						$status = $wpdb->insert($wpdb->prefix.'postmeta',
							array( 'post_id'    => $post->wp_id, 
								   'meta_key'   => '_bbp_reply_count', 
								   'meta_value' => $topic->topic_replies
							), 
							array( '%d',//post_id
								   '%s',//meta_key
								   '%s' //meta_value
							)
						);
						if ($status === false)
							$this->fc_die('error inserting _bbp_reply_count'.$wpdb->last_error);
							
						//initially we assumed open
						if ($topic->topic_status == 1)
						{
							//now marking mark topic closed
							$this->fc_echo('marking topic closed<br/>');
							$status = $wpdb->update($wpdb->prefix.'posts',
								array('post_status' => 'closed'),
								array('ID' => $insertId),
								array('%s'),
								array('%d')
							);						
							if ($status === false)
								$this->fc_die('failed to update lock topic: '.$wpdb->last_error);
						}
					}
					else
					{
						$this->fc_echo('marking as reply<br/>');
						//locate topic
						$srctopic = $fdb->get_row('SELECT * FROM '.$this->forumLoginSrc->prefix.'topics WHERE topic_id='.$post->phpbb_topic_id);
						if ($srctopic === NULL)
							$this->fc_echo('Error in topic search skipping linking for this reply<br/>');
						else
						{
							//locate post
							$dsttopic = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_posts WHERE phpbb_id='.$srctopic->topic_first_post_id);
							if ($dsttopic === NULL)
								$this->fc_die('failed to locate post'.$wpdb->last_error);

							//link replies to topic
							$status = $wpdb->update($wpdb->prefix.'posts',
								array('post_parent' => $dsttopic->wp_id),
								array('ID' => $post->wp_id),
								array('%d'),
								array('%d')
							);							
							if ($status === false)
								$this->fc_die('failed to link reply to topic'.$wpdb->last_error);

							$status = $wpdb->insert($wpdb->prefix.'postmeta',
								array( 'post_id'    => $post->wp_id, 
									   'meta_key'   => '_bbp_topic_id', 
									   'meta_value' => $dsttopic->wp_id
								), 
								array( '%d',//post_id
									   '%s',//meta_key
									   '%s' //meta_value
								)
							);												
							if ($status === false)
								$this->fc_die('error inserting _bbp_topic_id'.$wpdb->last_error);

							//grab the topics forum id
							$forumid = $wpdb->get_var('SELECT post_parent FROM '.$wpdb->prefix.'posts WHERE ID='.$dsttopic->wp_id);

							//more meta
							$status = $wpdb->insert($wpdb->prefix.'postmeta',
								array( 'post_id'    => $post->wp_id, 
									   'meta_key'   => '_bbp_forum_id', 
									   'meta_value' => $forumid
								), 
								array( '%d',//post_id
									   '%s',//meta_key
									   '%s' //meta_value
								)
							);
							if ($status === false)
								$this->fc_die('error inserting _bbp_forum_id'.$wpdb->last_error);
						}

						//force it as reply
						$status = $wpdb->update($wpdb->prefix.'posts',
							array('post_type' => 'reply'),
							array('ID' => $post->wp_id),
							array('%s'),
							array('%d')
						);							
						if ($status === false)
							$this->fc_die('failed to post as reply'.$wpdb->last_error);

						//correct location
						$name = $wpdb->get_var('SELECT post_name FROM '.$wpdb->prefix.'posts WHERE ID='.$post->wp_id);
						$status = $wpdb->update($wpdb->prefix.'posts',
//							array('guid' => $this->siteurl.'/?reply='.$name),
							array('guid' => $this->siteurl.'/?p='.$post->wp_id),
							array('ID' => $post->wp_id),
							array('%s'),
							array('%d')
						);						
						if ($status === false)
							$this->fc_die('failed to update guid: '.$wpdb->last_error);
					}

					//lookup user and set post author
					$user = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_users WHERE phpbbid='.$post->phpbb_poster_id);
					if ($user === NULL)
					{
						$this->fc_echo('Mapping missing user to Anonymous<br/>');
						$user = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'users WHERE user_nicename="anonymous"');
					}
					$status = $wpdb->update($wpdb->prefix.'posts',
						array('post_author' => $user->wpid),
						array('ID' => $post->wp_id),
						array('%d'),
						array('%d')
					);
					if ($status === false)
						$this->fc_die('failed to update post author: '.$wpdb->last_error);

					//link metadata
					/* dupe
					$status = $wpdb->insert($wpdb->prefix.'postmeta',
						array( 'post_id'    => $post->wp_id, 
							   'meta_key'   => '_bbp_forum_id', 
							   'meta_value' => $forum->wp_id
						), 
						array( '%d',//post_id
							   '%s',//meta_key
							   '%s' //meta_value
						)
					);												
					if ($status === false)
						$this->fc_die('failed to set metadata: '.$wpdb->last_error);
					*/
				}
				
				//generate sticky
				$this->fc_echo('scanning stickies<br/>');
				$topics = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'topics WHERE topic_type=1 OR topic_type=2'); //1 sticky, 2=announce, 3=global announce
				if ($topics === false)
					$this->fc_die('failed to match topic'.$fdb->last_error);
				if ($fdb->num_rows > 0)
				{
					$post = array();
					foreach ($topics as $topic)
					{
						//locate post
						$topicId = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_posts WHERE phpbb_id='.$topic->topic_first_post_id);
						if ($topicId === NULL)
							$this->fc_die('failed to get topic'.$wpdb->last_error);
						
						if (isset($post[$topicId->phpbb_forum_id]))
							array_push($post[$topicId->phpbb_forum_id], strval($topicId->wp_id));
						else
						{
							$post[$topicId->phpbb_forum_id] = array();
							array_push($post[$topicId->phpbb_forum_id], strval($topicId->wp_id));
						}
					}

					foreach($post as $k => $arr)
					{
						$this->fc_echo('found sticky<br/>');
						//convert to wp id
						$forum = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_forums WHERE phpbb_id='.$k);
						if ($forum === NULL)
							$this->fc_die('failed to find forum: '.$wpdb->last_error);
					
						//generate string
						//a:1:{i:0;s:3:"337";}
						$subdata = '';
						foreach($arr as $key => $value)
							$subdata .= ('i:'.$key.';s:'.strlen($value).':"'.$value.'";');
						$data = 'a:'.count($arr).':{'.$subdata.'}';
						
						//add metadata to forum
						$status = $wpdb->insert($wpdb->prefix.'postmeta',
							array( 'post_id'    => $forum->wp_id, 
								   'meta_key'   => '_bbp_sticky_topics', 
								   'meta_value' => $data
							), 
							array( '%d',//post_id
								   '%s',//meta_key
								   '%s' //meta_value
							)
						);
						if ($status === false)
							$this->fc_die('failed to add sticky: '.$wpdb->last_error);
					}
				}
				
				//generate super sticky
				$this->fc_echo('scanning super sticky<br/>');
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'options WHERE option_name LIKE "_bbp_super_sticky_topics"');
				$topics = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'topics WHERE topic_type=3'); //1 sticky, 2=announce, 3=global announce
				if ($topics === false)
					$this->fc_die('failed to match topic'.$fdb->last_error);
				if ($fdb->num_rows > 0)
				{
					$post = array();
					foreach ($topics as $topic)
					{
						//locate post
						$topicId = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_posts WHERE phpbb_id='.$topic->topic_first_post_id);
						if ($topicId === NULL)
							$this->fc_die('failed to get topic'.$wpdb->last_error);
						
						array_push($post, $topicId);
					}

					$subdata = '';
					foreach($post as $k => $t)
					{
						$this->fc_echo('found super sticky<br/>');
						$subdata .= ('i:'.$k.';s:'.strlen($value).':"'.$value.'";');
					}

					$data = 'a:'.count($post).':{'.$subdata.'}';
					
					//add option
					$status = $wpdb->insert($wpdb->prefix.'options',
						array( 'blog_id'      => 0, 
							   'option_name'  => '_bbp_super_sticky_topics', 
							   'option_value' => $data,
							   'autoload'     => 'yes'
						), 
						array( '%d',//blog_id
							   '%s',//option_name
							   '%s',//option_value
							   '%s' //autoload
						)
					);
					if ($status === false)
						$this->fc_die('failed to add super sticky: '.$wpdb->last_error);
				}
			}
			
			//trash old view counts
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "_bbp_voice_count"');
			
			//count views
			$this->fc_echo('fixing post count views<br/>');
			$topics = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_type="topic"');
			if ($topics === false)
				$this->fc_die('failed to get views: '.$wpdb->last_error);
			foreach ($topics as $topic)
			{
				$views = $wpdb->get_results('SELECT DISTINCT post_author FROM '.$wpdb->prefix.'posts WHERE (post_type="reply" AND post_parent='.$topic->ID.') OR (post_type="topic" AND ID='.$topic->ID.')');
				if ($views === false)
					$this->fc_die('failed to count views: '.$wpdb->last_error);
				
				$status = $wpdb->insert($wpdb->prefix.'postmeta',
					array( 'post_id'    => $topic->ID, 
						   'meta_key'   => '_bbp_voice_count', 
						   'meta_value' => $wpdb->num_rows
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed to add views: '.$wpdb->last_error);
			}
			
			//trash old entries for failed conversion
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "_bbp_last_topic_id"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "_bbp_last_reply_id"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "_bbp_last_active_id"');

			$wpdb->query('DROP TABLE IF EXISTS '.$wpdb->prefix.'fc_forum_latest');
			$wpdb->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$wpdb->prefix.'fc_forum_latest (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, wp_id BIGINT(20), phpbb_parent_id MEDIUMINT(8), latest_time int(11), post BIGINT(20))');

			//just fix non roots
			$this->fc_echo('fixing freshness for non roots<br/>');
			$forums = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'fc_map_forums WHERE phpbb_parent_id<>0');
			foreach($forums as $forum)
			{
				$time = 0;
				$targetPost = 0;
				$targetTopic = 0;

				//scan topics
				$topics = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'topics WHERE forum_id='.$forum->phpbb_id.' ORDER BY topic_last_post_time DESC');
				if ($fdb->num_rows > 0)
				{
					$topic = $topics[0];
					$post = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_posts WHERE phpbb_id='.$topic->topic_last_post_id);
					
					//select the post that that is the freshest
					$time = $topic->topic_last_post_time;
					$targetPost = $post->wp_id;
				}

				//skip for now
				/*
				//scan forums
				$subforums = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'fc_map_forums WHERE phpbb_parent_id='.$forum->phpbb_id.' ORDER BY last_post_time DESC');
				if ($wpdb->num_rows > 0)
				{
					$subforum = $subforums[0];
					if ($subforum->last_post_time > $time)
						$targetPost = $subforum->wp_id;
				}*/
				
				//grab the topic
				if ($targetPost != 0)
				{
					$targetTopic = $targetPost;
					$topic = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'posts WHERE ID='.$targetTopic);
					if ($topic !== NULL && $topic->post_type === 'reply')
					{
						$topic = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'posts WHERE ID='.$topic->ID);
						$targetTopic = $topic->post_parent;
					}
				}
				
				//t1
				$status = $wpdb->insert($wpdb->prefix.'postmeta', 
					array( 'post_id'    => $forum->wp_id, 
						   'meta_key'   => '_bbp_last_topic_id', 
						   'meta_value' => $targetTopic
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed set meta for _bbp_last_topic_id: '.$wpdb->last_error);

				//t2
				$status = $wpdb->insert($wpdb->prefix.'postmeta', 
					array( 'post_id'    => $forum->wp_id, 
						   'meta_key'   => '_bbp_last_reply_id', 
						   'meta_value' => $targetPost
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed set meta for _bbp_last_reply_id: '.$wpdb->last_error);

				//t3
				$status = $wpdb->insert($wpdb->prefix.'postmeta', 
					array( 'post_id'    => $forum->wp_id, 
						   'meta_key'   => '_bbp_last_active_id', 
						   'meta_value' => $targetPost
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed set meta for _bbp_last_active_id: '.$wpdb->last_error);
				
				//fix time
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key="_bbp_last_active_time" AND post_id="'.$forum->wp_id.'"');
				$status = $wpdb->insert($wpdb->prefix.'postmeta', 
					array( 'post_id'    => $forum->wp_id, 
						   'meta_key'   => '_bbp_last_active_time', 
						   'meta_value' => $this->convertTimestamp($time)
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed set meta for _bbp_last_active_time: '.$wpdb->last_error);
				
				//latest time table
				$status = $wpdb->insert($wpdb->prefix.'fc_forum_latest', 
					array( 'wp_id'           => $forum->wp_id,
					       'phpbb_parent_id' => $forum->phpbb_parent_id,
						   'latest_time'     => $time,
						   'post'            => $targetPost
					), 
					array( '%d',//wp_id
						   '%d',//phpbb_parent_id
						   '%d',//latest_time
						   '%d' //post
					)
				);
				if ($status === false)
					$this->fc_die('failed to insert into latest time table: '.$wpdb->last_error);
				
			}
			
			//just fix top level roots
			$this->fc_echo('fixing freshness for roots<br/>');
			$forums = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'fc_map_forums WHERE phpbb_parent_id=0');
			foreach($forums as $forum)
			{
				$time = 0;
				$targetPost = 0;
				$targetTopic = 0;

				//scan topics
				$topics = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'topics WHERE forum_id='.$forum->phpbb_id.' ORDER BY topic_last_post_time DESC');
				if ($fdb->num_rows > 0)
				{
					$topic = $topics[0];
					$post = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'fc_map_posts WHERE phpbb_id='.$topic->topic_last_post_id);
					
					//select the post that that is the freshest
					$time = $topic->topic_last_post_time;
					$targetPost = $post->wp_id;
				}
				
				//scan forums
				$subforums = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'fc_forum_latest WHERE phpbb_parent_id='.$forum->phpbb_id.' ORDER BY latest_time DESC');
				if ($wpdb->num_rows > 0)
				{
					$subforum = $subforums[0];
					if (intval($subforum->latest_time) > intval($time))
					{
						$time = $subforum->latest_time;
						$targetPost = $subforum->post;
					}
				}

				//grab the topic
				if ($targetPost != 0)
				{
					$targetTopic = $targetPost;
					$topic = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'posts WHERE ID='.$targetTopic);
					if ($topic !== NULL && $topic->post_type === 'reply')
					{
						$topic = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'posts WHERE ID='.$topic->ID);
						$targetTopic = $topic->post_parent;
					}
				}
				
				//t1
				$status = $wpdb->insert($wpdb->prefix.'postmeta', 
					array( 'post_id'    => $forum->wp_id, 
						   'meta_key'   => '_bbp_last_topic_id', 
						   'meta_value' => $targetTopic
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed set meta for _bbp_last_topic_id: '.$wpdb->last_error);

				//t2
				$status = $wpdb->insert($wpdb->prefix.'postmeta', 
					array( 'post_id'    => $forum->wp_id, 
						   'meta_key'   => '_bbp_last_reply_id', 
						   'meta_value' => $targetPost
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed set meta for _bbp_last_reply_id: '.$wpdb->last_error);

				//t3
				$status = $wpdb->insert($wpdb->prefix.'postmeta', 
					array( 'post_id'    => $forum->wp_id, 
						   'meta_key'   => '_bbp_last_active_id', 
						   'meta_value' => $targetPost
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed set meta for _bbp_last_active_id: '.$wpdb->last_error);
					
				//fix time
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key="_bbp_last_active_time" AND post_id="'.$forum->wp_id.'"');
				$status = $wpdb->insert($wpdb->prefix.'postmeta', 
					array( 'post_id'    => $forum->wp_id, 
						   'meta_key'   => '_bbp_last_active_time', 
						   'meta_value' => $this->convertTimestamp($time)
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed set meta for _bbp_last_active_time: '.$wpdb->last_error);
					
				//fix topic count
				$ntopics = 0;
				$nreplies = 0;
				
				//accumulate subforum totals
				$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_type="forum" AND post_parent='.$forum->wp_id);
				foreach($results as $result)
				{
					$ntopics += intval($wpdb->get_var('SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key="_bbp_total_topic_count" AND post_id='.$result->ID));
					$nreplies += intval($wpdb->get_var('SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key="_bbp_total_reply_count" AND post_id='.$result->ID));
				}

				//accmulate topic totals
				$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_type="topic" AND post_parent='.$forum->wp_id);
				$ntopics += $wpdb->num_rows;

				//accmulate reply totals
				foreach($results as $result)
				{
					$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_type="reply" AND post_parent='.$result->ID);
					$nreplies += $wpdb->num_rows;
				}
				
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key="_bbp_total_topic_count" AND post_id="'.$forum->wp_id.'"');
				$status = $wpdb->insert($wpdb->prefix.'postmeta', 
					array( 'post_id'    => $forum->wp_id, 
						   'meta_key'   => '_bbp_total_topic_count', 
						   'meta_value' => $ntopics
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed set meta for _bbp_total_topic_count: '.$wpdb->last_error);
				
				//fix reply count
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'postmeta WHERE meta_key="_bbp_total_reply_count" AND post_id="'.$forum->wp_id.'"');
				$status = $wpdb->insert($wpdb->prefix.'postmeta', 
					array( 'post_id'    => $forum->wp_id, 
						   'meta_key'   => '_bbp_total_reply_count', 
						   'meta_value' => $nreplies
					), 
					array( '%d',//post_id
						   '%s',//meta_key
						   '%s' //meta_value
					)
				);
				if ($status === false)
					$this->fc_die('failed set meta for _bbp_total_reply_count: '.$wpdb->last_error);
			}

			$this->fc_echo('done processing posts<br/>');
		}
		
		public function convertUsers()
		{
			//global $wpdb;
			global $table_prefix;

			$wpdb = new wpdbex(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
			$wpdb->set_prefix($table_prefix);
			$fdb = new wpdbex($this->forumLoginSrc->username, $this->forumLoginSrc->password, $this->forumLoginSrc->databasename, $this->forumLoginSrc->hostname);
			//$fdb->show_errors();
			//$wpdb->show_errors();
			
			//trash users from failed conversion
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'users WHERE user_pass LIKE "%phpbb%"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'usermeta WHERE meta_key="bbp_signature"');
			
			//create table to store password lookup
			$status = $wpdb->query('CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'fc_users (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, wpid INT, phpbbid MEDIUMINT(8))');
			if ($status === false)
				$this->fc_die('failed to create table: '.$wpdb->last_error);
				
			//lookup group ids for administrator and moderators
			$administratorGroupId = $fdb->get_var('SELECT group_id FROM '.$this->forumLoginSrc->prefix.'groups WHERE group_name="ADMINISTRATORS"');
			$moderatorGroupId = $fdb->get_var('SELECT group_id FROM '.$this->forumLoginSrc->prefix.'groups WHERE group_name="GLOBAL_MODERATORS"');
			$botGroupId = $fdb->get_var('SELECT group_id FROM '.$this->forumLoginSrc->prefix.'groups WHERE group_name="BOTS"');
			
			$this->fc_echo('converting users<br/>');
			$srcusers = $fdb->get_results('SELECT * FROM '.$this->forumLoginSrc->prefix.'users');
			if ($fdb->num_rows > 0)
			{
				foreach ($srcusers as $user)
				{
					if ($user->username_clean === 'admin')
					{
						//add admin to password lookup table
						$status = $wpdb->insert($wpdb->prefix.'fc_users', 
							array(
								'wpid'    => $wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'users WHERE user_nicename="admin"'),
								'phpbbid' => $user->user_id
							),
							array(
								'%d',//wpid
								'%d' //phpbbid
							)
						);
						if ($status === false)
							$this->fc_die('failed to insert (2): '.$wpdb->last_error);

						continue;
					}
						
					if ($user->group_id === $botGroupId)
						continue;

					$found = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'users WHERE user_email="'.$user->user_email.'" AND user_nicename="'.$user->username_clean.'"');
					$foundName = $wpdb->get_row('SELECT user_nicename FROM '.$wpdb->prefix.'users WHERE user_nicename="'.$user->username_clean.'"');
					$foundEmail = $wpdb->get_row('SELECT user_email FROM '.$wpdb->prefix.'users WHERE user_email="'.$user->username_clean.'"');

					//case: user didn't register wordpress but did register on forum
					if ($foundName === NULL && $foundEmail === NULL)
					{
						$this->fc_echo('merging user '.$user->username.'<br/>');
						//add new user to wordpress
						$status = $wpdb->insert($wpdb->prefix.'users',
							array(
								'user_login'          => $user->username,
								'user_pass'           => 'phpbb'.$user->user_password,
								'user_nicename'       => $user->username_clean,
								'user_email'          => $user->user_email,
								'user_url'            => '',
								'user_registered'     => $this->convertTimestamp($user->user_regdate),
								'user_activation_key' => '',
								'user_status'         => 0,
								'display_name'        => $user->username
							),
							array(
								'%s',//user_login
								'%s',//user_pass
								'%s',//user_nicename
								'%s',//user_email
								'%s',//user_url
								'%s',//user_registered
								'%s',//user_activation_key
								'%d',//user_status
								'%s' //display_name
							)
						);
						if ($status === NULL)
							$this->fc_die('failed to insert (1): '.$wpdb->last_error);
						$insertId = $wpdb->insert_id;
					}
					//case: user uses same email and user name on wordpress
					else if ($found !== NULL)
					{
						$this->fc_echo('merging user '.$user->username.'<br/>');
						//grab the id
						$insertId = $found->ID;
					}
					//case: user uses same email on both accounts
					else if ($foundEmail !== NULL)
					{
						$this->fc_echo('merging user '.$user->username.'<br/>');
						$insertId = $foundEmail->ID;
					}
					//case: user uses same username but different email
					else if ($foundName !== NULL)
					{
						$this->fc_echo('merging user '.$user->username.'<br/>');
						$insertId = $foundName->ID;
					}
					else
						$this->fc_die('user match fail'.$wpdb->last_error);
					
					//save elevated cap if any
					$oldcap = $wpdb->get_var('SELECT meta_value FROM '.$wpdb->prefix.'usermeta WHERE meta_key="wp_capabilities" AND user_id='.$insertId);
					
					//trash permission
					$wpdb->query('DELETE FROM '.$wpdb->prefix.'usermeta WHERE meta_key LIKE "%wp_capabilities%" AND user_id='.$insertId);
					
					//modify user capabilities
					if ($oldcap === NULL)
						$permission = 'a:1:{s:10:"subscriber";b:1;}'; //low role
					else
						$permission = $oldcap; //elevated role
					if (strstr($permission, 'administrator') !== FALSE)
						;//already moderator
					else
					{
						if ($user->group_id == $administratorGroupId)
							$permission = 'a:1:{s:13:"bbp_moderator";b:1;}';
						else if ($user->group_id == $moderatorGroupId)
							$permission = 'a:1:{s:13:"bbp_moderator";b:1;}';
					}
					
					//prevent anonymous from posting
					if ($user->username_clean === 'anonymous')
						$permission = '';

					//check if banned
					$bannedUser = $fdb->get_row('SELECT * FROM '.$this->forumLoginSrc->prefix.'banlist WHERE ban_userid='.$user->user_id);
					if ($bannedUser !== NULL)
					{
						$permission = '';
						$this->fc_echo('banned user will not be subscribed for '. $user->username.'<br/>');
					}

					if ($permission != '')
					{
						$status = $wpdb->insert($wpdb->prefix.'usermeta', 
							array(
								'user_id'    => $insertId,
								'meta_key'   => 'wp_capabilities',
								'meta_value' => $permission
							),
							array(
								'%d',//user_id
								'%s',//meta_key
								'%s' //meta_value
							)
						);
						if ($status === false)
							$this->fc_die('failed to modify capabilities: '.$wpdb->last_error);
					}
					//fix for buddypress
					$status = $wpdb->insert($wpdb->prefix.'usermeta', 
						array(
						    'user_id'    => $insertId,
							'meta_key'   => 'last_activity',
							'meta_value' => $this->convertTimestamp($user->user_lastvisit)
						),
						array(
							'%d',//user_id
							'%s',//meta_key
							'%s' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed to set last_activity: '.$wpdb->last_error);
					
					//instant messaging support aim
					$status = $wpdb->insert($wpdb->prefix.'usermeta', 
						array(
						    'user_id'    => $insertId,
							'meta_key'   => 'aim',
							'meta_value' => $user->user_aim
						),
						array(
							'%d',//user_id
							'%s',//meta_key
							'%s' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed to set last_activity: '.$wpdb->last_error);

					//instant messaging support yim
					$status = $wpdb->insert($wpdb->prefix.'usermeta', 
						array(
						    'user_id'    => $insertId,
							'meta_key'   => 'yim',
							'meta_value' => $user->user_yim
						),
						array(
							'%d',//user_id
							'%s',//meta_key
							'%s' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed to set last_activity: '.$wpdb->last_error);

					//instant messaging support yim
					$status = $wpdb->insert($wpdb->prefix.'usermeta', 
						array(
						    'user_id'    => $insertId,
							'meta_key'   => 'jabber',
							'meta_value' => $user->user_jabber
						),
						array(
							'%d',//user_id
							'%s',//meta_key
							'%s' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed to set last_activity: '.$wpdb->last_error);						

					//signature
					$status = $wpdb->insert($wpdb->prefix.'usermeta', 
						array(
						    'user_id'    => $insertId,
							'meta_key'   => 'bbp_signature',
							'meta_value' => $this->convertPost($user->user_sig)
						),
						array(
							'%d',//user_id
							'%s',//meta_key
							'%s' //meta_value
						)
					);
					if ($status === false)
						$this->fc_die('failed to set signature: '.$wpdb->last_error);
					
					//add users to password lookup table
					$status = $wpdb->insert($wpdb->prefix.'fc_users', 
						array(
							'wpid'    => $insertId,
							'phpbbid' => $user->user_id
						),
						array(
							'%d',//wpid
							'%d' //phpbbid
						)
					);
					if ($status === false)
						$this->fc_die('failed to insert (2): '.$wpdb->last_error);

					//buddypress avatar transfer
					if (strlen($this->dstAvatarPath) > 0)
					{
						//find the file
						$found = '';
						$files = scandir($this->forumLoginSrc->avatarpath.DIRECTORY_SEPARATOR);
						array_shift($files);
						array_shift($files);
						foreach($files as $file)
						{
							$t = $file;
							$id = preg_replace('|[a-z0-9]+_([0-9]+)\.[a-z]+|i','\1',$t);
							if (strval($id) === strval($user->user_id))
							{
								$found = $file;
								break;
							}
						}
						
						if (strlen($found) > 0)
						{
							$this->fc_echo('transfering avatar<br/>');

							if (is_dir ($this->dstAvatarPath.DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR.$insertId) == FALSE)
								mkdir($this->dstAvatarPath.DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR.$insertId,0777,true);

							//large
							$filename = $found;
							$filename = str_replace('.jpg', '-bpfull.jpg', $filename);
							$filename = str_replace('.gif', '-bpfull.gif', $filename);
							$filename = str_replace('.png', '-bpfull.png', $filename);
							$status = copy($this->forumLoginSrc->avatarpath.DIRECTORY_SEPARATOR.$found, $this->dstAvatarPath.DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR.$insertId.DIRECTORY_SEPARATOR.$filename);
							$this->fc_echo($filename . '<br/>');
							if ($status == FALSE)
								$this->fc_echo('failed to copy file<br/>');

							//thumb
							$filename = $found;
							$filename = str_replace('.jpg', '-bpthumb.jpg', $filename);
							$filename = str_replace('.gif', '-bpthumb.gif', $filename);
							$filename = str_replace('.png', '-bpthumb.png', $filename);
							$status = copy($this->forumLoginSrc->avatarpath.DIRECTORY_SEPARATOR.$found, $this->dstAvatarPath.DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR.$insertId.DIRECTORY_SEPARATOR.$filename);
							if ($status == FALSE)
								$this->fc_echo('failed to copy file<br/>');
						}
					}
					
				}
			}
			$this->fc_echo('done processing users<br/>');
		}
		
		public function cleanup()
		{
			global $wpdb;
			$wpdb->query('DROP TABLE IF EXISTS '.$wpdb->prefix.'fc_users');
			$wpdb->query('DROP TABLE IF EXISTS '.$wpdb->prefix.'fc_map_posts');
			$wpdb->query('DROP TABLE IF EXISTS '.$wpdb->prefix.'fc_map_forums');
		}
		
		public function convertTimestamp($date)
		{
			return strftime("%Y-%m-%d %H:%M:%S",intval($date));
		}
		
		public function convertTitle($subject)
		{
			return str_replace('','&quot;',$subject);
		}

		public function convertName($subject)
		{
			return sanitize_title($subject);
		}
		
		public function convertPost($subject)
		{
			//convert all phpbb bbcode into bbcode lite format
			
			$level = error_reporting();
			error_reporting(0);
			
			$ubbc = in_array('bbcode',$this->forumLoginSrc->options);

			//strip out the comments

			//space-ellipsis fix for bbpress
			$subject = preg_replace('|[ ]\.\.\.[ ]|i', '&nbsp;...&nbsp;', $subject);
			
			if ($ubbc)
			{
				//move attachments
				if (strpos($subject,'postlink') !== FALSE)
					$subject = preg_replace('|<a class="postlink" href="([^\"<>]*)">|i', '<a href="\1">', $subject);
				if (strpos($subject,'{SMILIES_PATH}') !== FALSE) 
					$subject = preg_replace('|<img src="[^\"]+" alt="(.+)" title=".+" .>|i', '\1', $subject);
					
				//strip out the random
				if (preg_match('|\[\/[a-zA-Z0-9]+\:[a-z0-9]+\]|im',$subject) > 0) 
					$subject = preg_replace('|\[\/([a-z0-9]+)\:[a-z0-9]+\]|i', '[/\1]', $subject);
				if (preg_match('|\[[a-zA-Z0-9]+\:[a-z0-9]+\]|im',$subject) > 0) 
					$subject = preg_replace('|\[([a-zA-Z0-9]+)\:[a-z0-9]+\]|i', '[\1]', $subject);
				if (preg_match('|\[[a-zA-Z0-9]+=.+\:[a-z0-9]+\]|im',$subject) > 0) 
					$subject = preg_replace('|\[([a-zA-Z0-9]+)=(.+)\:[a-z0-9]+\]|i', '[\1=\2]', $subject);
				
				//quotes
				if (strpos($subject,'[quote') !== FALSE)
				{
					$subject = preg_replace('|\[quote=&quot;(.+)&quot;\:[a-z0-9]+\]|i', '[quote="\1"]',$subject);
					$subject = preg_replace('|\[quote=&quot;(.+)&quot;\]|i', '[quote="\1"]',$subject);
				}

				//list
				if (             preg_match('|\[\*\:[a-z0-9]+\]|i',$subject) > 0) 
					$subject = preg_replace('|\[\*\:[a-z0-9]+\]|i', '[*]', $subject);
				if (             preg_match('|\[\/\*\:.\:[a-z0-9]+\]|i',$subject) > 0) 
					$subject = preg_replace('|\[\/\*\:.\:[a-z0-9]+\]|i', '[/*]', $subject);
				if (             preg_match('|\[\/\*\:[a-z]\]|i',$subject) > 0) 
					$subject = preg_replace('|\[\/\*\:[a-z]\]|i', '[/*]', $subject);
				if (             preg_match('|\[\/list\:.\:[a-z0-9]+\]|i',$subject) > 0) 
					$subject = preg_replace('|\[\/list\:.\:[a-z0-9]+\]|i', '[/list]', $subject);
				if (             preg_match('|\[list=([0-9]+)\:[a-z0-9]+\]|i',$subject) > 0) 
					$subject = preg_replace('|\[list=([0-9]+)\:[a-z0-9]+\]|i', '[list=\1]', $subject);

				//needs rework
				$subject = preg_replace('|\[color=(.*)\:[a-z0-9]+\]|i', '[color=\1]', $subject); //alpha as param
				//$subject = preg_replace('|\[align=([a-zA-Z]+)\:[a-z0-9]+\]|i', '[align=\1]', $subject); //alpha as param
				//$subject = preg_replace('|\[glow=([,a-zA-Z0-9]+)\:[a-z0-9]+\]|i', '[glow=\1]', $subject); //2 params in param
				$subject = preg_replace('|\[size=([0-9]+)\:[a-z0-9]+\]|i', '[size=\1]', $subject);
				$subject = preg_replace('|\[url=(.*)\:[a-z0-9]+\]|i', '[url=\1]', $subject); //print char as param

				$subject = preg_replace('|\<!-- s([^\[\]]*)\-->([^\[\]]*)\<!-- s([^\[\]]*)\-->|is', '\2',$subject);
				$subject = str_replace('<!-- s;) -->;)<!-- s;) -->', ';)',$subject); //custom code by neopeek
				$subject = str_replace('" title="Exclamation" />', '',$subject); 						 //remove exclamation cruft
				$subject = preg_replace('|<img src="{SMILIES_PATH}/.*gif" alt="|is', '', $subject); //remove smiley cruft
					
				//use complex form for bbcode light
				if (             preg_match('|\[img\]|i',$subject) > 0) 
					$subject = preg_replace('|\[img\]([^\[\]]+)\[\/img\]|i', '[img=\1]\1[/img]', $subject);
			}
			else
			{   //use native markup (html)
				//move attachments
				if (strpos($subject,'postlink') !== FALSE)
					$subject = preg_replace('|<a class="postlink" href="([^\"<>]*)">|i', '<a href="\1">', $subject);
				if (strpos($subject,'{SMILIES_PATH}') !== FALSE) 
					$subject = preg_replace('|<img src="[^\"]+" alt="(.+)" title=".+" .>|i', '\1', $subject);
					
				//strip out the random
				if (preg_match('|\[\/[a-zA-Z0-9]+\:[a-z0-9]+\]|im',$subject) > 0) 
					$subject = preg_replace('|\[\/([a-z0-9]+)\:[a-z0-9]+\]|i', '[/\1]', $subject);
				if (preg_match('|\[[a-zA-Z0-9]+\:[a-z0-9]+\]|im',$subject) > 0) 
					$subject = preg_replace('|\[([a-zA-Z0-9]+)\:[a-z0-9]+\]|i', '[\1]', $subject);
				if (preg_match('|\[[a-zA-Z0-9]+=.+\:[a-z0-9]+\]|im',$subject) > 0) 
					$subject = preg_replace('|\[([a-zA-Z0-9]+)=(.+)\:[a-z0-9]+\]|i', '[\1=\2]', $subject);
				
				//quotes
				if (strpos($subject,'[quote') !== FALSE)
				{
					$subject = preg_replace('|\[quote=&quot;(.+)&quot;\:[a-z0-9]+\]|i', '\1 wrote:  <blockquote>',$subject);
					$subject = preg_replace('|\[quote=&quot;(.+)&quot;\]|i', '  \1 wrote:  <blockquote>',$subject);
				}
				$subject = str_replace('[quote]', '  Someone wrote:  <blockquote>',$subject);
				$subject = str_replace('[/quote]', '</blockquote>',$subject);

				//list
				if (             preg_match('|\[\*\:[a-z0-9]+\]|i',$subject) > 0) 
					$subject = preg_replace('|\[\*\:[a-z0-9]+\]|i', '[*]', $subject);
				if (             preg_match('|\[\/\*\:.\:[a-z0-9]+\]|i',$subject) > 0) 
					$subject = preg_replace('|\[\/\*\:.\:[a-z0-9]+\]|i', '[/*]', $subject);
				if (             preg_match('|\[\/\*\:[a-z]\]|i',$subject) > 0) 
					$subject = preg_replace('|\[\/\*\:[a-z]\]|i', '[/*]', $subject);
				if (             preg_match('|\[\/list\:.\:[a-z0-9]+\]|i',$subject) > 0) 
					$subject = preg_replace('|\[\/list\:.\:[a-z0-9]+\]|i', '[/list]', $subject);
				if (             preg_match('|\[list=([0-9]+)\:[a-z0-9]+\]|i',$subject) > 0) 
					$subject = preg_replace('|\[list=([0-9]+)\:[a-z0-9]+\]|i', '[list=\1]', $subject);

				//needs reworking
				$subject = preg_replace('|\[color=(.*)\:[a-z0-9]+\]|i', '[color=\1]', $subject); //alpha as param
				//$subject = preg_replace('|\[align=([a-zA-Z]+)\:[a-z0-9]+\]|i', '[align=\1]', $subject); //alpha as param
				//$subject = preg_replace('|\[glow=([,a-zA-Z0-9]+)\:[a-z0-9]+\]|i', '[glow=\1]', $subject); //2 params in param
				$subject = preg_replace('|\[size=([0-9]+)\:[a-z0-9]+\]|i', '[size=\1]', $subject);
				$subject = preg_replace('|\[url=(.*)\:[a-z0-9]+\]|i', '[url=\1]', $subject); //

				//replace all with native markup bbPress uses html
				$subject = str_replace('[i]', '<i>',$subject); //italics
				$subject = str_replace('[/i]', '</i>',$subject);
				$subject = str_replace('[b]', '<b>',$subject); //bold
				$subject = str_replace('[/b]', '</b>',$subject);
				$subject = str_replace('[u]', '<u>',$subject); //underline
				$subject = str_replace('[/u]', '</u>',$subject);

				$subject = str_replace('[s]', '<s>',$subject); //strike
				$subject = str_replace('[/s]', '</s>',$subject);

				$subject = str_replace('[left]', '<left>',$subject);     //left
				$subject = str_replace('[/left]', '</left>',$subject);
				
				$subject = str_replace('[right]', '<right>',$subject);   //right
				$subject = str_replace('[/right]', '</right>',$subject);

				$subject = preg_replace('|\[size=([^\[\]]*)\]([^\[\]]*)\[\/size\]|is', '<span>\2</span>', $subject);
				$subject = str_replace('[/size]', '', $subject);

				$subject = preg_replace('|\[color=([^\[\]]*)\]([^\[\]]*)\[\/color\]|is', '<span style="color:\1;">\2</span>', $subject);
				
				$subject = str_replace('[code]', '<pre><code>',$subject);
				$subject = str_replace('[/code]', '</code></pre>',$subject);
				$subject = preg_replace('|\[url=([^\[\]]*)\]([^\[\]]*)\[\/url\]|is', '<a href="\1">\2</a>',$subject);
//				$subject = preg_replace('|\[url=([^\[\]]*)\]([^\[\]]*)\[\/url\]|is', '<a target="_blank" href="\1">\2</a>',$subject);
				$subject = preg_replace('|\[url\]([^\[\]]*)\[\/url\]|is', '\1',$subject);
				$subject = preg_replace('|\[img\]([^\[\]]*)\[\/img\]|is', '<img src="\1" />',$subject);
				$subject = preg_replace('|\[list\]([^\[\]]*)\[\/list\]|ims', '<ul>\1</ul>', $subject);
				$subject = preg_replace('|\[list=([^\[\]]*)\]([^\[\]]*)\[\/list\]|ims', '<ol>\2</ol>', $subject);
				$subject = str_replace('[*]', '<li>', $subject);
				$subject = str_replace('[/*]', '</li>', $subject);

				$subject = preg_replace('|\<!-- s([^\[\]]*)\-->([^\[\]]*)\<!-- s([^\[\]]*)\-->|is', '\2',$subject);
				$subject = str_replace('<!-- s;) -->;)<!-- s;) -->', ';)',$subject); //custom code by neopeek
				$subject = str_replace('" title="Exclamation" />', '',$subject); 						 //remove exclamation cruft
				$subject = preg_replace('|<img src="{SMILIES_PATH}/.*gif" alt="|is', '', $subject); //remove smiley cruft
				
				//ugly we do it again
				$subject = str_replace('[list]', '<ul>', $subject);
				$subject = str_replace('[/list]', '</ul>', $subject);
				$subject = preg_replace('|\[size=[0-9]+\]|is', '', $subject);
				$subject = preg_replace('|\[color=([^\[\]]*)\]([^\[\]]*)\[\/color\]|is', '<span style="color:\1;">\2</span>', $subject);

				//custom bbcode
				$subject = preg_replace('|\[youtube\]([^\[\]]*)\[\/youtube\]|is', '<a href="http://www.youtube.com/watch?v=\1">http://www.youtube.com/watch?v=\1</a>', $subject);
			}
			
			error_reporting($level);
			return $subject;
		}
		
		public function fc_die($message)
		{
			global $wpdb;
			echo $message;
			echo '<script type="text/javascript">jQuery("#fc_message-status").removeClass("updated"); jQuery("#fc_message-status").addClass("error"); jQuery("#fc_message-status").text("Conversion failed");</script>';
			$wpdb->print_error();
			die();
		}
		
		public function fc_echo($message)
		{
			echo '<script type="text/javascript">jQuery("#fc_message").append("'.str_replace('\\','\\\\',$message).'"); jQuery("#fc_message").scrollTop(jQuery("#fc_message")[0].scrollHeight);</script>';
		}

		private function deldir($target)
		{
			$this->_deldir($target);
			rmdir($target);	
		}
		
		private function _deldir($target)
		{
			$os = scandir($target);
			array_shift($os);
			array_shift($os);
	
			foreach($os as $o)
			{
				if (is_dir($target.DIRECTORY_SEPARATOR.$o))
				{
					$this->_deldir($target.DIRECTORY_SEPARATOR.$o);
					rmdir($target.DIRECTORY_SEPARATOR.$o);
				}
				else
					unlink($target.DIRECTORY_SEPARATOR.$o);
			}
		}
	}
?>
