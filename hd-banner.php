<?php
/**
 * Plugin Name:     HD Banner
 * Plugin URI:      https://www.helpfuldigital.com
 * Description:     Display a customisable banner on the front/back-end of the site.
 * Author:          Phil Banks | Helpful Digital
 * Author URI:      https://www.helpfuldigital.com
 * Text Domain:     hd-banner
 * Domain Path:     /languages
 * Version:         0.1
 *
 * @package         Hd_Banner
 */




/**
 * Load up.
 */
require plugin_dir_path( __FILE__ ) . 'hd-banner_options.php';




/*
 * Retrieve this value with:
 * $hd_banner_options = get_option( 'hd_banner_options' ); // Array of All Options
 * $banner_message = $hd_banner_options['banner_message']; // Message to show
 * $when_to_display = $hd_banner_options['when_to_display']; // When to display (status or user-role)
 * $background_colour = $hd_banner_options['background_colour']; // Background colour
 * $text_colour = $hd_banner_options['text_colour']; // Text colour
 * $link_colour = $hd_banner_options['link_colour']; // Link colour
 * $element_to_attach_to = $hd_banner_options['element_to_attach_to']; // Element to attach to
 * $position = $hd_banner_options['position']; // Position
 * $show_in_admin = $hd_banner_options['show_in_admin']; // Show in admin
 */
$hd_banner_options = get_option( 'hd_banner_options' );
defined( 'HD_BANNER_OPTIONS' ) or define( 'HD_BANNER_OPTIONS', maybe_serialize( $hd_banner_options ) );




function hd_banner_load_script() {
	wp_enqueue_script( 'hd-banner', plugins_url( 'hd-banner.js', __FILE__ ), array( 'jquery' ), null, true );
	wp_localize_script( 'hd-banner', 'hd_banner_vars', maybe_unserialize( HD_BANNER_OPTIONS ) );
}




function hd_banner_load_script_admin() {
	wp_enqueue_script( 'hd-banner-admin', plugins_url( 'hd-banner-admin.js', __FILE__ ), array( 'jquery' ), null, true );
	wp_localize_script( 'hd-banner-admin', 'hd_banner_vars', maybe_unserialize( HD_BANNER_OPTIONS ) );
}



function hd_banner_maybe_load_js(){

	$hd_banner_options = maybe_unserialize( HD_BANNER_OPTIONS );
	if ( empty( array_filter( $hd_banner_options ) ) ) {
		return;
	}
	$current_user_roles = array();
	$current_user = wp_get_current_user();
	if ( $current_user->exists() ) {
		$current_user_roles = ( array ) $current_user->roles;
	}

	// Front end.
	if ( 'always' === $hd_banner_options['when_to_display'] ) { // Always.
		add_action( 'wp_enqueue_scripts', 'hd_banner_load_script' );
	}
	if ( 'loggedout' !== $hd_banner_options['when_to_display'] && ! empty( $current_user_roles ) ) { // Logged in variations.
		add_action( 'wp_enqueue_scripts', 'hd_banner_load_script' );
	}
	if ( 'loggedout' === $hd_banner_options['when_to_display'] && empty( $current_user_roles ) ) { // Logged out.
		add_action( 'wp_enqueue_scripts', 'hd_banner_load_script' );
	}

	// Back end.
	if ( ! empty( $hd_banner_options['show_in_admin'] ) ) {
		if ( 'loggedin' === $hd_banner_options['when_to_display'] || 'always' === $hd_banner_options['when_to_display'] || in_array( $hd_banner_options['when_to_display'], $current_user_roles ) ) {
			add_action( 'admin_enqueue_scripts', 'hd_banner_load_script_admin' );
		}
	}
}
add_action( 'init', 'hd_banner_maybe_load_js' );
