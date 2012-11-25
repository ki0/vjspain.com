<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'vjspain');

/** MySQL database username */
define('DB_USER', 'vj007');

/** MySQL database password */
define('DB_PASSWORD', 'redam069');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'oo.!n,C|f|0/zdB_>7cFQYj%Rjjw^#`Smw;#>tr8?g=T!~gwf}X/Uu}MuKumx/ar');
define('SECURE_AUTH_KEY',  'X:R/z%,P^]!,v>fshW)/0v;R=&q?o@z__3II;;^JFjnwdl58.,37]U*< pgXHed|');
define('LOGGED_IN_KEY',    '0X|tPz3O|<^MBZJxS3TIH%R;W/EapHRUsUO2X2(]X6DkPv@CgHzV${,5!p}-(cg?');
define('NONCE_KEY',        'a#F;~8RgnZVZB]t2L#H]oXM/go~.)@-_Y}6^z|0B6)h=yPP,hUW%oR-:UC$//-MZ');
define('AUTH_SALT',        'pL[fE*C&V)A+H^iQ6z,k8kDJPs$WvBBAEYuQ9U:C+E-H<M?b{^1D@1/xRisH&fI<');
define('SECURE_AUTH_SALT', '8}fo+gS3I-sKl+[A6)=EKh3D.SoP]Y,|v!(=evS!u:?+riy@,V?E%[G}d(S?eLMO');
define('LOGGED_IN_SALT',   'nFo U`kjgKUBWZG8b!3-St5hbGO7XxTfLzY,%mQUL{IWCkB32fb-: B|8mNY%nDV');
define('NONCE_SALT',       'f2a]Cu0 Sq3l~OlO9v%gFp-19jWHI{}O@bX-oT-dFFC|rX!/&oz.pLu~>@u3;$P+');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', 'es_ES');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/* Multisite */
/* define('WP_ALLOW_MULTISITE', true); */
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', true);
$base = '/';
define('DOMAIN_CURRENT_SITE', 'test.vjspain.com');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
