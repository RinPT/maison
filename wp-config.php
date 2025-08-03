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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'fqxdfix_maison' );

/** Database username */
define( 'DB_USER', 'fqxdfix_maison' );

/** Database password */
define( 'DB_PASSWORD', '1Epdgi)5ZS(f' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '#`!PKlWjv[acZSDj?j:<Y_#1.cnjIKg-38F1+U<YX?)J){%qK0/dHDchQK}],Zh!' );
define( 'SECURE_AUTH_KEY',   '@+IW^SP]xd+!n5Fdf,L2JZuy7X%!Xa9) PTQbDO%ty}&6]LBTN6B!#o9=<RLr`:b' );
define( 'LOGGED_IN_KEY',     'JFvk7zeQ?!U1!Jfo<HZFF/Xj45vOpCqw_A.+R*&ue}}{.C?.L!6)fQw[T4CX,WpS' );
define( 'NONCE_KEY',         '5!16zRdRZaEUS8>J.pBNE41Ho V2MB&QTNIJrU!ybL4hj@*T[`(Y%JA2Z4{N/rp^' );
define( 'AUTH_SALT',         'bSq6Dlrl;:%}hC_uSs+EeBVgOyxw+BoE5B[6&uobB9:EwFn5?;t]lit!.D`8wOoz' );
define( 'SECURE_AUTH_SALT',  'M4NTB03A_izB[g[FI&IAXl*ul,8pSE.mm]1OZ^C,!g[;Y1I1qsI^.6(qVPn6d>c-' );
define( 'LOGGED_IN_SALT',    ')Dcf xDj^!9RXv(#;v+Oc~!pzPkA9|{.Gyc#lqYYdT;4>>$ f^f}rx61f]O__iUm' );
define( 'NONCE_SALT',        'Q!He84S|}v;!0HE!LH#ru@3j{ KX=I^l6UI]hihK&TiaY_d`83i,.nn]r?4]|%$o' );
define( 'WP_CACHE_KEY_SALT', 'lS5[PT|Vmgalu%$&D.@8&VrT:gMHb~l-S{=t9BvBAj9:S%pPhbjmen}9o>uwHUs-' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
