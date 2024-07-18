<?php
/**
 * Plugin Name:     HD Banner
 * Plugin URI:      https://www.dxw.com
 * Description:     Display a customisable banner on the front/back-end of the site.
 * Text Domain:     hd-banner
 * Domain Path:     /languages
 * Version:         0.3.1
 *
 * @package         Hd_Banner
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

$hd_banner_defaults = [
	'banner_message'       => '<strong>IMPORTANT NOTE: THIS IS THE TEST/STAGING VERSION, NOT THE LIVE WEBSITE</strong>',
	'when_to_display'      => 'always',
	'background_colour'    => '#ffff00',
	'text_colour'          => '#cc0000',
	'link_colour'          => '#cc0000',
	'element_to_attach_to' => 'body',
	'position'             => 'prepend',
	'fixed'                => 'no',
	'show_in_admin'        => 'show_in_admin',
];


/** Defines the default settings for the plugin */
defined( 'HD_BANNER_DEFAULTS' ) or define( 'HD_BANNER_DEFAULTS', maybe_serialize( $hd_banner_defaults ) );

/** Register the activation functionality to set the default settings */
register_activation_hook( __FILE__, 'hd_banner_plugin_activation' );

/** Init HD_Banner class when plugins are loaded */
add_action( 'plugins_loaded', [ 'HD_Banner', 'init' ] );

/** Init HD_Banner_Options class when plugins are loaded and in the admin */
if ( is_admin() ) {
	require_once plugin_dir_path( __FILE__ ) . 'hd-banner_options.php';
	add_action( 'plugins_loaded', [ 'HD_Banner_Options', 'init' ] );
}

/**
 * Set defaults on activation.
 */
function hd_banner_plugin_activation() {
	if ( false === get_option( 'hd_banner_options' ) ) {
		add_option( 'hd_banner_options', maybe_unserialize( HD_BANNER_DEFAULTS ) );
	}

	if ( empty( get_option( 'hd_banner_options' ) ) ) {
		update_option( 'hd_banner_options', maybe_unserialize( HD_BANNER_DEFAULTS ) );
	}
}

if ( ! class_exists( 'HD_Banner' ) ) :
	/**
	 * Class HD_Banner
	 */
	class HD_Banner {

		/**
		 * Init the object.
		 */
		public static function init(): HD_Banner {
			return new HD_Banner();
		}

		/**
		 * HD_Banner constructor.
		 */
		public function __construct() {
			add_action( 'plugin_action_links_' . basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ),
				[ $this, 'hd_banner_plugin_settings' ], 10, 1 );
			add_filter( 'plugin_row_meta', [ $this, 'hd_banner_plugin_links' ], 10, 2 );
			add_action( 'init', [ $this, 'hd_banner_maybe_load_js' ] );
		}

		/**
		 * Add settings links near Deactivate link into plugins admin.
		 *
		 * @param string $links
		 *
		 * @return mixed $links
		 */
		public function hd_banner_plugin_settings( $links ) {
			if ( current_user_can( 'manage_options' ) ) {
				$links[] = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=hd-banner' ),
					'Settings' );
			}

			return $links;
		}

		/**
		 * Add a link at the end of the Description into plugins admin.
		 *
		 * @param $links
		 * @param $file
		 *
		 * @return array
		 */
		public function hd_banner_plugin_links( $links, $file ) {
			if ( $file == plugin_basename( __FILE__ ) ) {
				$links[] = 'For more info visit ' . ' <a href="https://www.helpfuldigital.com/">Helpful Digital</a>';
			}

			return $links;
		}


		/**
		 * Loads the script for the frontend.
		 */
		public static function hd_banner_load_script() {
			wp_enqueue_script( 'hd-banner', plugins_url( 'hd-banner.js', __FILE__ ), [ 'jquery' ], null, true );
			wp_localize_script( 'hd-banner', 'hd_banner_vars', get_option( 'hd_banner_options' ) );
		}


		/**
		 * Loads the scripts for the backend.
		 */
		public static function hd_banner_load_script_admin() {
			wp_enqueue_script( 'hd-banner-admin', plugins_url( 'hd-banner-admin.js', __FILE__ ), [ 'jquery' ],
				null,
				true );
			wp_localize_script( 'hd-banner-admin', 'hd_banner_vars', get_option( 'hd_banner_options' ) );
		}


		/**
		 * Loads the banner on the front/back-end of the site.
		 */
		public function hd_banner_maybe_load_js() {
			$hd_banner_options = get_option( 'hd_banner_options' );

			if ( ( empty( $hd_banner_options ) ) || ( false === $hd_banner_options ) ) {
				return;
			}

			$current_user_roles = [];
			$current_user       = wp_get_current_user();
			if ( $current_user->exists() ) {
				$current_user_roles = ( array ) $current_user->roles;
			}

			// Front end.
			if ( 'always' === $hd_banner_options['when_to_display'] ) { // Always.
				add_action( 'wp_enqueue_scripts', [ __CLASS__, 'hd_banner_load_script' ] );
			}
			if ( 'loggedin' === $hd_banner_options['when_to_display']
			     || in_array( $hd_banner_options['when_to_display'], $current_user_roles ) ) { // Logged in variations.
				add_action( 'wp_enqueue_scripts', [ __CLASS__, 'hd_banner_load_script' ] );
			}
			if ( 'loggedout' === $hd_banner_options['when_to_display'] && ! $current_user->exists() ) { // Logged out.
				add_action( 'wp_enqueue_scripts', [ __CLASS__, 'hd_banner_load_script' ] );
			}

			// Back end.
			if ( ! empty( $hd_banner_options['show_in_admin'] ) ) {
				if ( 'loggedin' === $hd_banner_options['when_to_display'] || 'always' === $hd_banner_options['when_to_display']
				     || in_array( $hd_banner_options['when_to_display'], $current_user_roles ) ) {
					add_action( 'admin_enqueue_scripts', [ __CLASS__, 'hd_banner_load_script_admin' ] );
				}
			}

		}

	}
endif;
