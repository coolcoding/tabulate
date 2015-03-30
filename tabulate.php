<?php

/**
 * Plugin Name: Tabulate
 * Description: A simple user-friendly interface to tables in your database.
 * Version: 0.1.0
 * Author: Sam WIlson
 * Author URI: http://samwilson.id.au/
 * License: GPL-2.0+
 */
define( 'TABULATE_SLUG', 'tabulate' );
define( 'TABULATE_VERSION', '0.1.0' );

// Make sure Composer has been set up (for installation from Git, mostly).
if ( !file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Please run <tt>composer install</tt> prior to using Tabulate.</p></div>';
	} );
	return;
}
require __DIR__ . '/vendor/autoload.php';

// This is the only global usage of wpdb; it's injected from here to everywhere.
global $wpdb;

// Set up the menus; their callbacks do the actual dispatching to controllers.
$menus = new \WordPress\Tabulate\Menus($wpdb);
$menus->init();

// Add grants-checking callback.
add_filter( 'user_has_cap', '\\WordPress\\Tabulate\\DB\\Grants::check', 0, 3 );
