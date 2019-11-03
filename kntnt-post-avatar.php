<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Kntnt Post Avatar
 * Plugin URI:        https://github.com/kntnt/kntnt-post-avatar
 * GitHub Plugin URI: https://github.com/kntnt/kntnt-post-avatar
 * Description:       Replaces avatars with featured images of posts
 * Version:           1.0.1
 * Author:            Thomas Barregren
 * Author URI:        https://www.kntnt.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       kntnt-post-avatar
 * Domain Path:       /languages
 */

namespace Kntnt\Post_Avatar;

defined( 'WPINC' ) || die;

// Define WP_DEBUG as TRUE and uncomment next line to debug this plugin.
// define( 'KNTNT_POST_AVATAR', true );

spl_autoload_register( function ( $class ) {
	$ns_len = strlen( __NAMESPACE__ );
	if ( 0 == substr_compare( $class, __NAMESPACE__, 0, $ns_len ) ) {
		require_once __DIR__ . '/classes/' . strtr( strtolower( substr( $class, $ns_len + 1 ) ), '_', '-' ) . '.php';
	}
} );

new Plugin();
