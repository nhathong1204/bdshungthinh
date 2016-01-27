<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'bdshun67_hungthinh');

/** MySQL database username */
define('DB_USER', 'bdshun67_ht');

/** MySQL database password */
define('DB_PASSWORD', 'hungthinhland1A');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'j,^/x}-#V5{6E+-D;X[/V67@J6K}nUFbe0OLB?I1T)-=ZD32$Mk2=uHzdOJ--IJ2');
define('SECURE_AUTH_KEY',  'J%GYde(3OO7VUw|$6@[^~z0h]?aX! ;,EYS-3hp3g.wkSPHL|cNC8IBx{td7s |E');
define('LOGGED_IN_KEY',    '-yv7QqA812I.gmTXI$QWp9[Js>.tDcK{Aq({v=bBQSlWe~Omd:t_+[U>t ;RfV^>');
define('NONCE_KEY',        'Cyfq= 8>2x1]K%?)n1g=yg4LMg.,+a8@y|lFoqp 5@.-l33*O:E3XA,|xSQv:1W|');
define('AUTH_SALT',        '1C=~TmstpD4=Ym}S{aU:^!03hdW3[b/VN@i#M9=~Zz3F6h@+?brToxUrB%|%KjvF');
define('SECURE_AUTH_SALT', 'pgd;$p^kXS?T0.MMF6JKCPo3D+aY!rD/9vVnkmooh--I4Mr*^xUR?`Xpqy64-W<b');
define('LOGGED_IN_SALT',   '{F&H/_/VT!?-/w=.xN^.{]80%nQ9D~M .wSx&&S|xFF#oR>];tvz-s;zEd<{BK~x');
define('NONCE_SALT',       'Uw,&3-,rEoAIE5h~B9CE6IfE,-27#e@oY:s,*z#-5{P|S&o;%@[6+/7?/K-T`@Y-');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
