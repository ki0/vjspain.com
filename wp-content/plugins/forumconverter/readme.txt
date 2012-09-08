=== ForumConverter ===
Contributors: Orson Teodoro
Donate link: http://orsonteodoro.wordpress.com/forumconverter/
Tags: phpbb, bbpress, forum, conversion
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable tag: 1.13

Migrates a phpBB forum into a bbPress forum.

== Description ==

*This plugin is under heavy development.  Please do not use it in production unless your willing to accept difficulty of 
upgrading or correctness of the conversion.*

ForumConverter migrates post, topics, and users from one forum to a WordPress compatible forum.  Currently, 
ForumConverter supports phpBB 3.0.9 to bbPress 2.0 conversion in addition to migrating users as subscribers 
to your WordPress blog. 

ForumConverter migrates users from the source forum database to WordPress compatible forum so that migraters can take 
advantage of BuddyPress social networking.  This is not a bridge.

For phpBB-to-bbPress conversions, ForumConverter is capable of...

* reproducing the same forum tree structure for bbPress
* reproducing forum post using the same same user, timestamps, sticky flag
* allowing converted users to sign on using their existing phpBB password.
* converting between BBCode or native bbPress HTML markup
* supporting inline attachment conversion and whole topic/reply attachment conversion.
* migrating of instant messenger info, global moderator membership, and emails.
* password protected forums migration using the same phpBB password

Some things that are impossible to do for ForumConverter for phpBB-to-bbPress conversions

* Complete conversion of custom BBCode.  Only YouTube BBCode is supported but BBCode lite does not support YouTube tags. 

Please read the installation instructions before preceeding.  Remote conversion is not supported.

== Installation ==

1. Before you start make sure you backup your databases and ftp site.  This software does a **destructive 
conversion** to your existing WordPress/bbPress installation.
1. It is recommended that you set up a development server or sandbox environment and do 
the conversion on that machine before damaging your production server.
1. Bring your phpBB installation up to date to 3.0.9
1. Bring your bbPress installation up to date to bbPress 2.0.
1. Upload `forum-converter folder` to the `/wp-content/plugins/` directory
1. Activate the bbPress plugin first through the 'Plugins' menu in WordPress
1. Activate the ForumConverter plugin through the 'Plugins' menu in WordPress
1. Configure the settings information for the source forum
1. Press convert to start conversion.
1. Delete the source forum username/password after conversion by saving it.
1. Deactivate ForumConverter plugin and activate ForumConverter-Auth plugin
1. Optional: Activate the ForumConverter-Signature plugin to render signatures and edit signatures
1. Optional: Activate the ForumConverter-Password plugin to edit/remove converted forum passwords

== Frequently Asked Questions ==

= Does your plugin support the bbPress found on BuddyPress =

No.  The data tables differ for bbPress 2.0.

= Will you support bbPress found on BuddyPress =

Maybe. BuddyPress does not support subforums or subgroups making the conversion process non-trivial.

= What does "Use BBCode instead of HTML markup" do =

If you check this option, the converter will utilize "BBcode Lite for bbPress" markup.  Unchecking this option will 
make the converter use HTML markup.

= What do I put for server prefix =

This might be phpbb_ depending on your phpBB setup.

= What do I put for server upload path =

This should point to the folder containing your attachments.  For phpBB this may be contained in
phpBB_InstallationFolder/files.  Use backslashes for windows servers or forward slashes for unix or 
unix like servers.

= How do I get BBCode Lite recognize my BBCode produced by ForumConverter =

We do not support BBPress Lite.  But it is trivial to bring BBCode Lite up-to-date to support bbPress 2.0.

= How do I get my plugin to support attachments produced by ForumConverter =

ForumConverter does not utilize extra tables found in bbPress Attachments plugin and phpBB but rather utilizes
wp_posts table itself.  Attachments reference their topic or posts ID in that same table.

= How do I get forum members to take on a different WordPress group permission =

You can modify the source code to place that user in a different user permission.

= What sort of destructive conversion does this plugin do? =

The plugin will delete existing bbPress 2.0 posts, replies, and post meta.  In addition, it may 
do destructive conversion of users if it encounters an already existing WordPress user.

= Can I deactivate this plugin? =

If you are using password protected forums or phpBB password features do not deactivate this plugin to 
ensure that phpBB password protected forums and phpBB login passwords work properly.

With version 1.03 you can disable the conversion module and then activate the 

= Will upgrades update previous conversions missing features of the current version? =

No

= What password should be used? =

Before 1.03, Any user with a phpBB account will use the phpBB username and password.

If your using 1.03, merged users with existing WordPress accounts must use the same WordPress username and password 
with the phpBB username lost in the conversion if a email match is found.  Any account that failed to match a 
WordPress username, or email will need to use the phpBB username and password.

= I get "conversion is not supported" message =

Make sure you activate bbPress before you do the conversion.  If this doesn't work you may try to disable the check
in the source code yourself.

= The conversion dies suddenly in the middle of the conversion =

You need to increase your PHP script timeout period longer.  PHP kills a script after a certain amount of time.  Modify the source code and set set_time_limit to a higher value so that the conversion completes for slower CPUs.  Also there is a similar limit for MySQL also that needs to be adjusted to prevent mysql server from timing out.

== Screenshots ==

1. Configuring settings for ForumConverter.

== Upgrade Notice ==
= 1.13 = 
None

= 1.12 =
XSS Security vulnerability.  Please upgrade immediately if your using the signature plugin.  Versions 1.08-1.11 affected.

= 1.11 =
None

= 1.10 =
Run conversion again to fix post attachments.

= 1.09 =
None

= 1.08 =
None

= 1.07 =
None

= 1.06 =
None

= 1.05 =
None

= 1.05 =
None

= 1.04 =
None

= 1.03 =
None

= 1.02 =
None

= 1.01 =
None

== Changelog ==
= 1.13 =
* Bug Fix: Make signatures editing on WordPress backend only make changes to the viewed profile.

= 1.12 =
* Security Fix: Sanitize signatures.
* Bug Fix: Redirect the user back to the signature page after save.

= 1.11 =
* Correct versoning for all plugins.
* Cosmetic fixes for support for bbPress 2.0.
* Few fixes to bbcode conversion markup contributed by Vato.

= 1.10 =
* Bugfix: Fix incorrect url generated for attachments that are not inline.

= 1.09 =
* Bugfix: Fix displaying of signatures under ie8

= 1.08 =
* Created the "Orphaned Topics" forum to store orphaned topics
* References to missing users now link to Anonymous by default
* Bugfix: Fixed (mismatch) problem
* Added FormConverter forum passwords editor/remover in the "Forum Attributes" panel
* Signature rendering added with BuddyPress and WordPress signature editors
* Bugfix: Fixed infinite loop for database retry
* Cosmetic changes for support for bbPress 2.0 Release Candidate 4

= 1.07 =
* Update avatar support for BuddyPress 1.5 beta 2
* Prevent empty folder creation for posts without attachments

= 1.06 =
* Add buddypress avatar support
* Reconnect on disconnect

= 1.05 =
* Bugfix: increase execution time to 1 hour for those who don't have it in their php.ini
* Bugfix: fix attachments for those not attached inline

= 1.04 =
* Remove end of file whitespace to get rid of plugin activation warnings.
* Cosmetic changes for bbPress 2.0 rc 2

= 1.03 =
* Sticky now really become stickies
* Support for global announcement to super sticky conversion
* Support for topic locking
* Support for forum post locking
* Support automated forum conversion resulting in forums being automatically marked hidden, read only, or read or writable
* Bugfix: keep messages contained in log viewer
* Prevent banned users from subscribing, or replying or creating topics
* Better user merging to prevent orphaned ownership and better preservation of user capabilities.  Existing users on 
WordPress installation no longer get deleted.
* Prevent bots from being listed as subscribed user
* Seperate post conversion auth module
* Bugfix: fix role to show it properly on combobox.

= 1.02 =
* Duplicate attachment resolution
* Suppress file removal warnings

= 1.01 =
* phpBB password protected forums support
* Recoursive cleanup of attachments for failed conversion

= 1.0 =
* Initial release.
