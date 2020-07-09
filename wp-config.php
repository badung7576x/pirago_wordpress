<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'pirago' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define('WP_HOME', 'http://localhost/pirago');
define('WP_SITEURL', 'http://localhost/pirago');
define( 'FS_METHOD', 'direct' );
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ' 4mz,Y1T8i]vwv*^}09m.j)KJ&SCCUzglx$O<]6k[N.=N~=JW I8m-twx$./9hp#' );
define( 'SECURE_AUTH_KEY',  'S.?YHQ}r{mRc$s@Dx1nQM drE3AD<5-}[pS= KUPBivit5{DwQ4s&hqw{F%f,@2_' );
define( 'LOGGED_IN_KEY',    'W&h* X! mMSvfr3.;aD)Go6aEpt rL`NKdKv{y#e@lHCoQlAlD>yI3N(/#yHe2=<' );
define( 'NONCE_KEY',        '`@2E4QWfx;cP-PcVzPuhZP542U){&}f%x=L!h ZcuAV5$m+,]V=H9!amTkNZP<)A' );
define( 'AUTH_SALT',        'cL-WWLYf7[MfV1wT~{~:eb2>[|BxIb0CQ|IR~*q*?V~xR#@^2 H]37(EsI!q?VVz' );
define( 'SECURE_AUTH_SALT', 'dpyfjTU3?wbq_slPL0y4qlwAg;9x_Qc:|_LK}lDp LCd.g+!|~{+lb@DS3kZv1zG' );
define( 'LOGGED_IN_SALT',   '(L-ea2RpGcjvb])G=o8TRmB(iDvyhDbRQ7gaeNb)0~r[,RwF1oE(N=U0TvT+LRFN' );
define( 'NONCE_SALT',       ',51&Y(x-j-+VG:kP#G%px`+,jD$T[#u5B|/kD0,T)iwD#~@!oi_{ G6:4!$aQw_U' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
