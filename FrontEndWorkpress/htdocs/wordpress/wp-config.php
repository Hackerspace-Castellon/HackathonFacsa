<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
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
define( 'DB_NAME', 'hackcs' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'hackcs097' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'pUB_AJIUbYAbuO:p(UI8qh](2L-9K!(scBBGE!cOOd@mv+! &992w@2<Fu6r*J`O' );
define( 'SECURE_AUTH_KEY',  'DmO`JRo/>[bz{WpmIi99M7q:?4=pWppc{@5r?]/5,Ono.iG4v!%O#*0C2SQ`^,=n' );
define( 'LOGGED_IN_KEY',    '+fqBkHPaLrFr<Zk`VnY8,vfe~&G?]j^R$-f>h!Ub4i$(s^IFJ0h]}+e2zU?!Q6UZ' );
define( 'NONCE_KEY',        'P0$r8OR^ X+|/ha.A*=I<=QTY-*vpx4;RY)4qe!p%bxxoNqwaRVVhAw40oKh5qj:' );
define( 'AUTH_SALT',        'O_]Wp3Suz6Q!N(IgLHu$d`>&DrvlILczI{e`sMu:e!=T(jqOXE22.4d^BKmU4,Z:' );
define( 'SECURE_AUTH_SALT', 'pi-{lPE!_DzBBo}c~2wAguf@4xBBH)i*f_=GGd{;@wYC2!z3Xs+_Z>/&kIkx~.l%' );
define( 'LOGGED_IN_SALT',   'dQ`8ZpWOPE{=`7l|61QTCE<enr[C*7[|EUdsnONNA>{]#D(S$%iAz_x6wosG.kL`' );
define( 'NONCE_SALT',       'vL|xO2tQN}Ds,p)Z>=[Ls3H8em|(VmW0f+s}i;IqVy1@O?e!x&~W~+{~l~RWm4,l' );

/**#@-*/

/**
 * WordPress database table prefix.
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

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
define('FS_METHOD','direct');
