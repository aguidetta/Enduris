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
define('DB_NAME', 'enduris0921');

/** MySQL database username */
define('DB_USER', 'anthony0921');

/** MySQL database password */
define('DB_PASSWORD', 'hentai112');

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
define('AUTH_KEY',         'h~Cdy[Ph1hER0x%.#~X:L)B`0,=kl~(xU8V#K(pnd2A69)q 66^`P.5J+.+v6Wq}');
define('SECURE_AUTH_KEY',  'Gx/5Y0Ik{0Y?o1G^QX_R*}7qY4+)Ra+/Vx+uYxaQ&tk&)Y*@i[K77FXDddj>ulvH');
define('LOGGED_IN_KEY',    'TMSvuP8OY{M5$w~1c9Sm5gF;Fd*XKh0kXT-#YcL7:Lf u7tWZ*l(Z{EPCw.H:x=Z');
define('NONCE_KEY',        'H,N|Amyo(]UlGs6AsF=2lNX](EMalh+[G^LI6:gdZph(ZYsJ!bdFg~@=r|kmA5>|');
define('AUTH_SALT',        '+*O.;l#M]pi7^bRdq,A>nSMfAH(NreS`Trn-X-o3%R{s x;jq~.2%%DM&ppCQxN9');
define('SECURE_AUTH_SALT', '$~`6cw*z?E68SqXJ8wMU ,o[MiYwp7ij2/(9Gbd5N0>-A1e]DM&~h+n#*=taSs(a');
define('LOGGED_IN_SALT',   'h:AVDo^Jh7~nXcVG/fC+ b]sm#Sh{*a,O,9.6*ku5coA.t$8+}*fyfO62S|C`0|k');
define('NONCE_SALT',       'y-7kj`/n7~0aNd*STQ~Yj$M.J)w+j :eR[NMhGumql=qD6;T-=PD/`:bx`A+N8Ky');

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
