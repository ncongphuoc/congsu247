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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'congsu247');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         '&rxfS@%2&x0Qe{@lt -NxS.50h)n*ke)aE7a>F!>tToZmqidXWK:>{5P(ykAAz7W');
define('SECURE_AUTH_KEY',  '8l[mV2c}24&s=kRo3KO4m,Wqf!Cs4UbFxK2y$fzn?@&#]*%jirZ8*2N[3Oc^$V<P');
define('LOGGED_IN_KEY',    'Dw[Iecxfao6&Q?[@ I^g3J.QJhi*|nRh(EiHbMru:-#267x,zdcUQ5L>|Q0H4XZP');
define('NONCE_KEY',        'Y>|0|j{<Xw1Ly/@<ub:*5YVk<|6q4ed&~DVAsj,@)?`3x{<*[i&#i4&k+JA;*2^1');
define('AUTH_SALT',        '-pu3~P&^Mabt>p^vQbbdIl}Zi6up%yMLe8p&%HSqB%??y4NT qf(Efcqz6t=.kFn');
define('SECURE_AUTH_SALT', '>i::uxGnygBs5w2@rhmKpP/DNSJgt{7*@mB3qRIf)%3>TpDuciP|y=}(5(~]A0Wv');
define('LOGGED_IN_SALT',   'sVf?E3x&V*QCE=+?.0;eK,~2F7s@r;XT7lP1z[abYFw_`SZW+iZhGZQ%)wIM&#En');
define('NONCE_SALT',       '7w(@ffz1s9q#ao+8IJbN^8 7X,|nVb$zvOvb4u^0W]v xp(k MV>^D2.KqTw04I)');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
