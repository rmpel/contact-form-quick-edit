<?php
/**
 * Plugin Name: Contact Form Quick Edit
 * Plugin URI: https://www.remonpel.nl
 * Description: Adds edit links to forms on a page to the Admin Bar.
 * Version: 1.0
 * Author: Remon Pel
 * Author URI: https://www.remonpel.nl
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace RMPel\ContactFormQuickEdit;

// Autoloader
spl_autoload_register( function ( $class ) {
	$prefix   = __NAMESPACE__ . '\\';
	$base_dir = __DIR__ . '/src/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
	if ( file_exists( $file ) ) {
		require $file;
	} else {
		wp_die( 'Class not found: ' . $class . ', file: ' . $file );
	}
} );

new CFQE();
