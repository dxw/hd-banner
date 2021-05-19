<?php
/**
 * Options page.
 *
 * @package Hd_Banner
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

if ( ! class_exists( 'HD_Banner_Options' ) ) :
	/**
	 * Class HD_Banner_Options
	 */
	class HD_Banner_Options {
		/**
		 * @var $hd_banner_options
		 */
		private $hd_banner_options;

		/**
		 * Init the object.
		 */
		public static function init(): HD_Banner_Options {
			return new HD_Banner_Options();
		}

		/**
		 * HD_Banner_Options constructor.
		 */
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'hd_banner_add_plugin_page' ] );
			add_action( 'admin_init', [ $this, 'hd_banner_page_init' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'hd_banner_color_picker' ] );
		}

		/**
		 * Add the options plugin page under Settings.
		 */
		public function hd_banner_add_plugin_page() {
			add_options_page(
				'HD Banner', // page_title
				'HD Banner', // menu_title
				'manage_options', // capability
				'hd-banner', // menu_slug
				[ $this, 'hd_banner_create_admin_page' ] // function
			);
		}

		/**
		 * Create the options plugin page.
		 */
		public function hd_banner_create_admin_page() {
			$this->hd_banner_options = get_option( 'hd_banner_options' ); ?>

			<style type="text/css">
				#wp-banner_message-editor-container{max-width:70%}

				.wp-core-ui .button{margin-right:20px}
			</style>
			<div class="wrap">
				<h2>HD Banner</h2>
				<p>Display a customisable banner on the front/back-end of the site.</p>

				<form method="post" action="options.php">
					<?php
					settings_fields( 'hd_banner_option_group' );
					do_settings_sections( 'hd-banner-admin' );
					submit_button( 'Save settings', 'primary', 'submit', false );
					submit_button( 'Reset to defaults settings', 'secondary', 'reset-default', false );
					submit_button( 'Delete all settings', 'secondary', 'reset-all', false );
					?>
				</form>
			</div>
		<?php }

		/**
		 * Register and add settings for the options plugin page.
		 */
		public function hd_banner_page_init() {
			register_setting(
				'hd_banner_option_group', // option_group
				'hd_banner_options', // option_name
				[ $this, 'hd_banner_sanitize' ] // sanitize_callback
			);

			add_settings_section(
				'hd_banner_setting_section', // id
				'Settings', // title
				'', // callback
				'hd-banner-admin' // page
			);

			add_settings_field(
				'banner_message', // id
				'Banner text', // title
				[ $this, 'banner_message_callback' ], // callback
				'hd-banner-admin', // page
				'hd_banner_setting_section', // section
				[ 'label_for' => 'banner_message' ] // output proper label element
			);

			add_settings_field(
				'background_colour', // id
				'Background colour', // title
				[ $this, 'background_colour_callback' ], // callback
				'hd-banner-admin', // page
				'hd_banner_setting_section', // section
				[ 'label_for' => 'background_colour' ] // output proper label element
			);

			add_settings_field(
				'text_colour', // id
				'Text colour', // title
				[ $this, 'text_colour_callback' ], // callback
				'hd-banner-admin', // page
				'hd_banner_setting_section', // section
				[ 'label_for' => 'text_colour' ] // output proper label element
			);

			add_settings_field(
				'link_colour', // id
				'Link colour', // title
				[ $this, 'link_colour_callback' ], // callback
				'hd-banner-admin', // page
				'hd_banner_setting_section', // section
				[ 'label_for' => 'link_colour' ] // output proper label element
			);

			add_settings_field(
				'when_to_display', // id
				'When to display (login status or user-role)', // title
				[ $this, 'when_to_display_callback' ], // callback
				'hd-banner-admin', // page
				'hd_banner_setting_section', // section
				[ 'label_for' => 'when_to_display' ] // output proper label element
			);

			add_settings_field(
				'element_to_attach_to', // id
				'Element to attach to (jQuery selector, front-end only)', // title
				[ $this, 'element_to_attach_to_callback' ], // callback
				'hd-banner-admin', // page
				'hd_banner_setting_section', // section
				[ 'label_for' => 'element_to_attach_to' ] // output proper label element
			);

			add_settings_field(
				'position', // id
				'Position (front-end only)', // title
				[ $this, 'position_callback' ], // callback
				'hd-banner-admin', // page
				'hd_banner_setting_section', // section
				[ 'label_for' => 'position' ] // output proper label element
			);

			add_settings_field(
				'fixed', // id
				'Fixed position (always visible - front-end only)', // title
				[ $this, 'fixed_callback' ], // callback
				'hd-banner-admin', // page
				'hd_banner_setting_section', // section
				[ 'label_for' => 'fixed' ] // output proper label element
			);

			add_settings_field(
				'show_in_admin', // id
				'Show in admin', // title
				[ $this, 'show_in_admin_callback' ], // callback
				'hd-banner-admin', // page
				'hd_banner_setting_section', // section
				[ 'label_for' => 'show_in_admin' ] // output proper label element
			);
		}

		/**
		 * Sanitize the settings for the options plugin page
		 *
		 * @param $input
		 *
		 * @return array
		 */
		public function hd_banner_sanitize( $input ) {

			$setting = 'hd_banner_option_group';
			$code    = esc_attr( 'settings_updated' );
			$type    = 'updated';

			if ( isset( $_POST['reset-default'] ) ) {

				$message = 'Settings have been reset to default values.';
				add_settings_error( $setting, $code, $message, $type );

				return maybe_unserialize( HD_BANNER_DEFAULTS ); // Default settings.

			}

			if ( isset( $_POST['reset-all'] ) && ! empty( get_option( 'hd_banner_options' ) ) ) {

				$message = 'All settings deleted.';
				add_settings_error( $setting, $code, $message, $type );

				delete_option( 'hd_banner_options' );

				return false;
			}

			if ( isset( $_POST['submit'] ) ) {

				$message = 'Settings saved.';
				add_settings_error( $setting, $code, $message, $type );
			}

			$sanitary_values = [];
			if ( isset( $input['banner_message'] ) ) {
				$sanitary_values['banner_message'] = wp_kses_post( $input['banner_message'] );
			}

			if ( isset( $input['when_to_display'] ) ) {
				$sanitary_values['when_to_display'] = $input['when_to_display'];
			}

			if ( isset( $input['background_colour'] ) ) {
				$sanitary_values['background_colour'] = sanitize_text_field( $input['background_colour'] );
			}

			if ( isset( $input['text_colour'] ) ) {
				$sanitary_values['text_colour'] = sanitize_text_field( $input['text_colour'] );
			}

			if ( isset( $input['link_colour'] ) ) {
				$sanitary_values['link_colour'] = sanitize_text_field( $input['link_colour'] );
			}

			if ( isset( $input['element_to_attach_to'] ) ) {
				$sanitary_values['element_to_attach_to'] = sanitize_text_field( $input['element_to_attach_to'] );
			}

			if ( isset( $input['position'] ) ) {
				$sanitary_values['position'] = $input['position'];
			}

			if ( isset( $input['fixed'] ) ) {
				$sanitary_values['fixed'] = $input['fixed'];
			}

			if ( isset( $input['show_in_admin'] ) ) {
				$sanitary_values['show_in_admin'] = $input['show_in_admin'];
			}

			return $sanitary_values;
		}

		/**
		 * Banner message setup.
		 */
		public function banner_message_callback() {
			$content   = isset( $this->hd_banner_options['banner_message'] ) ? $this->hd_banner_options['banner_message'] : '';
			$editor_id = 'banner_message';
			$args      = [
				'tinymce'       => [
					'toolbar1' => 'fontsizeselect,bold,italic,underline,alignleft,aligncenter,alignright,link,unlink,removeformat,undo,redo',
					'toolbar2' => '',
				],
				'media_buttons' => false,
				'textarea_name' => 'hd_banner_options[banner_message]',
				'quicktags'     => false,
				'textarea_rows' => 10,
			];
			wp_editor( $content, $editor_id, $args );

		}

		/**
		 * When to display setup.
		 */
		public function when_to_display_callback() {
			$when_to_display_options = [
				'always'    => 'Always',
				'loggedin'  => 'All logged-in users',
				'loggedout' => 'All logged-out users',
			];
			$roles                   = wp_roles()->get_names();
			$when_to_display_options = array_merge( $when_to_display_options, $roles );
			?>
			<select name="hd_banner_options[when_to_display]" id="when_to_display">
				<?php foreach( $when_to_display_options as $value => $label ) :
					$selected = ( isset( $this->hd_banner_options['when_to_display'] ) && $this->hd_banner_options['when_to_display'] === $value ) ? 'selected' : '';
					?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php echo $selected; ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php
		}

		/**
		 * Background color setup.
		 */
		public function background_colour_callback() {
			printf(
				'<input data-default-color="#ffff00" class="color-picker" type="text" name="hd_banner_options[background_colour]" id="background_colour" value="%s">',
				isset( $this->hd_banner_options['background_colour'] ) ? esc_attr( $this->hd_banner_options['background_colour'] ) : ''
			);
		}

		/**
		 * Text colour setup.
		 */
		public function text_colour_callback() {
			printf(
				'<input data-default-color="#cc0000" class="color-picker" type="text" name="hd_banner_options[text_colour]" id="text_colour" value="%s">',
				isset( $this->hd_banner_options['text_colour'] ) ? esc_attr( $this->hd_banner_options['text_colour'] ) : ''
			);
		}

		/**
		 * Link colour setup.
		 */
		public function link_colour_callback() {
			printf(
				'<input data-default-color="#cc0000" class="color-picker" type="text" name="hd_banner_options[link_colour]" id="link_colour" value="%s">',
				isset( $this->hd_banner_options['link_colour'] ) ? esc_attr( $this->hd_banner_options['link_colour'] ) : ''
			);
		}

		/**
		 * Element to attach setup.
		 */
		public function element_to_attach_to_callback() {
			printf(
				'<input class="regular-text" type="text" name="hd_banner_options[element_to_attach_to]" id="element_to_attach_to" value="%s">',
				isset( $this->hd_banner_options['element_to_attach_to'] ) ? esc_attr( $this->hd_banner_options['element_to_attach_to'] ) : ''
			);
		}

		/**
		 * Position setup.
		 */
		public function position_callback() {
			?> <select name="hd_banner_options[position]" id="position">
				<?php $selected = ( isset( $this->hd_banner_options['position'] ) && $this->hd_banner_options['position'] === 'prepend' ) ? 'selected' : ''; ?>
				<option value="prepend" <?php echo $selected; ?>>Prepend</option>
				<?php $selected = ( isset( $this->hd_banner_options['position'] ) && $this->hd_banner_options['position'] === 'append' ) ? 'selected' : ''; ?>
				<option value="append" <?php echo $selected; ?>>Append</option>
				</select> <?php
		}

		/**
		 * Fixed setup.
		 */
		public function fixed_callback() {
			?> <select name="hd_banner_options[fixed]" id="fixed">
				<?php $selected = ( isset( $this->hd_banner_options['fixed'] ) && $this->hd_banner_options['fixed'] === 'no' ) ? 'selected' : ''; ?>
				<option value="no" <?php echo $selected; ?>>No</option>
				<?php $selected = ( isset( $this->hd_banner_options['fixed'] ) && $this->hd_banner_options['fixed'] === 'yes' ) ? 'selected' : ''; ?>
				<option value="yes" <?php echo $selected; ?>>Yes</option>
				</select> <?php
		}

		/**
		 * Show in admin setup.
		 */
		public function show_in_admin_callback() {
			printf(
				'<input type="checkbox" name="hd_banner_options[show_in_admin]" id="show_in_admin" value="show_in_admin" %s> <label for="show_in_admin">Should the banner be shown in the wp-admin area (depends on other options)</label>',
				( isset( $this->hd_banner_options['show_in_admin'] ) && $this->hd_banner_options['show_in_admin'] === 'show_in_admin' ) ? 'checked' : ''
			);
		}

		/**
		 * Initialize the color picker for Background colour, Text colour and Link colour.
		 */
		public function hd_banner_color_picker() {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script(
				'hd-banner-color-picker',
				plugins_url( 'hd-color-picker.js', __FILE__ ),
				[ 'wp-color-picker' ],
				false,
				true
			);
		}

	}
endif;
