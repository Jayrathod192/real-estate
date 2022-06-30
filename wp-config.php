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
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'realestate' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
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
define( 'AUTH_KEY',         '`(Wp2KElQ5o~`FVvpaa$t1w8iaG3TS-y=.s59YJ#lENbk!OOHsN#]GN9{5t3zTne' );
define( 'SECURE_AUTH_KEY',  ']&<=S=vZgK|aKO]<@uo&x~|jvH@iWp1qOnq=+/L6O2MV4V6Gj^^zLP(EA@>Rk=R/' );
define( 'LOGGED_IN_KEY',    '^[_?O(Cxb0|bAaw=w2(Hl*aX%Mcg#DRFN/r_Ef]x87N[Q%Xg(d$T6VjhtsDN|JO_' );
define( 'NONCE_KEY',        'vdCAia:`xpX1P]Cxb$A#mU33p/Mq;]Etk6{puQCtsEotk<wTjY;3EX&Ew^8&04kN' );
define( 'AUTH_SALT',        'tWflr;oaS|XI&D>4q#1=B6Cg/DUEcxLx/(|V|KGS$s6Uh4?C?tk>R#f,4G5;#81S' );
define( 'SECURE_AUTH_SALT', 'ySYvm)]2r17&I`!J|9:`gMyQI?]AEEo]P}4(?.</7lIVMCsdT&a(@P{l+0Vcf$mP' );
define( 'LOGGED_IN_SALT',   'aI{2$}BU3naWW|Ex:hdH&w:?C}gFm~`}a;#6+m(r}MsS|VGcm9]3+BM+2HSTN1k.' );
define( 'NONCE_SALT',       '2RIY4~hM;L&[AP+YzQ%U`ChiC*K;DY-BbsGoP;9XWA==#e,uVZvvn-/6,G.f$aQd' );

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
