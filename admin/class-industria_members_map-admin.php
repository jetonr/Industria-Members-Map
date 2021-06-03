<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.jrwebstudio.com
 * @since      1.0.0
 *
 * @package    Industria_members_map
 * @subpackage Industria_members_map/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Industria_members_map
 * @subpackage Industria_members_map/admin
 * @author     Jeton Ramadani <jetonr@gmail.com>
 */
class Industria_members_map_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Configuration Settings
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array
	 */
	private $members_map_configuration_options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Industria_members_map_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Industria_members_map_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/industria_members_map-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Industria_members_map_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Industria_members_map_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/industria_members_map-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function sample_admin_notice__success() {
		$screen = get_current_screen();
		// Only render this notice in the post editor.
		if ( ! $screen || 'settings_page_members-map-configuration' !== $screen->base ) {
			return;
		}
		?>
		<div class="notice notice-info is-dismissible">
			<p><span class="dashicons dashicons-info-outline"></span><span style="margin-left:5px"><?php _e( 'All settings other than the API Key can be overriden if defined in shortcode. This is usefull if this plugin is used to display maps in different pages! Eg: ( [industria_members_map width="200px"] this shortocde overrides the width option defined bellow! )', 'industria_members_map' ); ?></span></p>
		</div>
		<?php
	}

	public function members_map_configuration_add_plugin_page() {
		add_options_page(
			'Members Map Configuration', // page_title
			'Members Map Configuration', // menu_title
			'manage_options', // capability
			'members-map-configuration', // menu_slug
			array( $this, 'members_map_configuration_create_admin_page' ) // function
		);
	}

	public function members_map_configuration_create_admin_page() {
		$this->members_map_configuration_options = get_option( 'members_map_configuration_option_name' ); ?>

		<div class="wrap">
			<h2>Members Map Configuration</h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'members_map_configuration_option_group' );
					do_settings_sections( 'members-map-configuration-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function members_map_configuration_page_init() {
		register_setting(
			'members_map_configuration_option_group', // option_group
			'members_map_configuration_option_name', // option_name
			array( $this, 'members_map_configuration_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'members_map_configuration_setting_section', // id
			'Settings', // title
			array( $this, 'members_map_configuration_section_info' ), // callback
			'members-map-configuration-admin' // page
		);

		add_settings_field(
			'google_maps_api_key', // id
			'Google Maps API Key', // title
			array( $this, 'google_maps_api_key_callback' ), // callback
			'members-map-configuration-admin', // page
			'members_map_configuration_setting_section' // section
		);

		add_settings_field(
			'map_width', // id
			'Map Width', // title
			array( $this, 'map_width_callback' ), // callback
			'members-map-configuration-admin', // page
			'members_map_configuration_setting_section' // section
		);

		add_settings_field(
			'map_height', // id
			'Map Height', // title
			array( $this, 'map_height_callback' ), // callback
			'members-map-configuration-admin', // page
			'members_map_configuration_setting_section' // section
		);

		add_settings_field(
			'zoom_size', // id
			'Zoom Size', // title
			array( $this, 'zoom_size_callback' ), // callback
			'members-map-configuration-admin', // page
			'members_map_configuration_setting_section' // section
		);

		add_settings_field(
			'street_view', // id
			'Street View', // title
			array( $this, 'street_view_callback' ), // callback
			'members-map-configuration-admin', // page
			'members_map_configuration_setting_section' // section
		);

		add_settings_field(
			'map_type', // id
			'Map Type', // title
			array( $this, 'map_type_callback' ), // callback
			'members-map-configuration-admin', // page
			'members_map_configuration_setting_section' // section
		);

		add_settings_field(
			'mouse_scroll_wheel_for_map_zoom', // id
			'Mouse Scroll Wheel for Map Zoom', // title
			array( $this, 'mouse_scroll_wheel_for_map_zoom_callback' ), // callback
			'members-map-configuration-admin', // page
			'members_map_configuration_setting_section' // section
		);
	}

	public function members_map_configuration_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['google_maps_api_key'] ) ) {
			$sanitary_values['google_maps_api_key'] = sanitize_text_field( $input['google_maps_api_key'] );
		}

		if ( isset( $input['map_width'] ) ) {
			$sanitary_values['map_width'] = sanitize_text_field( $input['map_width'] );
		}

		if ( isset( $input['map_height'] ) ) {
			$sanitary_values['map_height'] = sanitize_text_field( $input['map_height'] );
		}

		if ( isset( $input['zoom_size'] ) ) {
			$sanitary_values['zoom_size'] = sanitize_text_field( $input['zoom_size'] );
		}

		if ( isset( $input['street_view'] ) ) {
			$sanitary_values['street_view'] = $input['street_view'];
		}

		if ( isset( $input['map_type'] ) ) {
			$sanitary_values['map_type'] = $input['map_type'];
		}

		if ( isset( $input['mouse_scroll_wheel_for_map_zoom'] ) ) {
			$sanitary_values['mouse_scroll_wheel_for_map_zoom'] = $input['mouse_scroll_wheel_for_map_zoom'];
		}

		return $sanitary_values;
	}

	public function members_map_configuration_section_info() {
		
	}

	public function google_maps_api_key_callback() {
		printf(
			'<input class="regular-text" type="text" name="members_map_configuration_option_name[google_maps_api_key]" id="google_maps_api_key" value="%s"><br>
			<p><small><span>Info on how to get the Google API Key for your account <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key">here</a>.</span></small></p>',
			isset( $this->members_map_configuration_options['google_maps_api_key'] ) ? esc_attr( $this->members_map_configuration_options['google_maps_api_key']) : ''
		);
	}

	public function map_width_callback() {
		printf(
			'<input class="regular-text" type="text" name="members_map_configuration_option_name[map_width]" id="map_width" value="%s"><br>
			<p><small>Width must be defined with values such as <i>px</i>, <i>%%</i>, <i>em</i>, (eg: 500px, 100%% ect)</small></p>',
			isset( $this->members_map_configuration_options['map_width'] ) ? esc_attr( $this->members_map_configuration_options['map_width']) : ''
		);
	}

	public function map_height_callback() {
		printf(
			'<input class="regular-text" type="text" name="members_map_configuration_option_name[map_height]" id="map_height" value="%s"><br>
			<p><small>Height must be defined with values such as <i>px</i>, <i>%%</i>, <i>em</i>, (eg: 500px ect)</small></p>',
			isset( $this->members_map_configuration_options['map_height'] ) ? esc_attr( $this->members_map_configuration_options['map_height']) : ''
		);
	}

	public function zoom_size_callback() {
		printf(
			'<input class="regular-text" type="text" name="members_map_configuration_option_name[zoom_size]" id="zoom_size" value="%s"><br>
			<p><small>Define the map zoom size on first map load. Best view for this usecase is 5!</small></p>',
			isset( $this->members_map_configuration_options['zoom_size'] ) ? esc_attr( $this->members_map_configuration_options['zoom_size']) : ''
		);
	}

	public function street_view_callback() {
		printf(
			'<input type="checkbox" name="members_map_configuration_option_name[street_view]" id="street_view" value="1" %s> <label for="street_view">Enable</label><br>
			<p><small>Google Street View provides panoramic 360 degree views from designated roads throughout its coverage area.</small></p>',
			( isset( $this->members_map_configuration_options['street_view'] ) && $this->members_map_configuration_options['street_view'] === '1' ) ? 'checked' : ''
		);
	}

	public function map_type_callback() {
		?> <select name="members_map_configuration_option_name[map_type]" id="map_type">
			<?php $selected = (isset( $this->members_map_configuration_options['map_type'] ) && $this->members_map_configuration_options['map_type'] === 'roadmap') ? 'selected' : '' ; ?>
			<option value="roadmap" <?php echo $selected; ?>>Roadmap</option>
			<?php $selected = (isset( $this->members_map_configuration_options['map_type'] ) && $this->members_map_configuration_options['map_type'] === 'hybrid') ? 'selected' : '' ; ?>
			<option value="hybrid" <?php echo $selected; ?>>Hybrid</option>
			<?php $selected = (isset( $this->members_map_configuration_options['map_type'] ) && $this->members_map_configuration_options['map_type'] === 'satellite') ? 'selected' : '' ; ?>
			<option value="satellite" <?php echo $selected; ?>>Satellite</option>
			<?php $selected = (isset( $this->members_map_configuration_options['map_type'] ) && $this->members_map_configuration_options['map_type'] === 'terrain') ? 'selected' : '' ; ?>
			<option value="terrain" <?php echo $selected; ?>>Terrain</option>
		</select><br>
			<p><small>The following map types are supported in Google Maps API: <br>ROADMAP (normal, default 2D map)<br>SATELLITE (photographic map)<br>HYBRID (photographic map + roads and city names)<br>TERRAIN (map with mountains, rivers, etc.)</small></p><?php
	}

	public function mouse_scroll_wheel_for_map_zoom_callback() {
		printf(
			'<input type="checkbox" name="members_map_configuration_option_name[mouse_scroll_wheel_for_map_zoom]" id="mouse_scroll_wheel_for_map_zoom" value="1" %s> <label for="mouse_scroll_wheel_for_map_zoom">Enable</label><br>
			<p><small>If NOT checked, zooming on the map using a mouse scroll wheel is disabled.</small></p>',
			( isset( $this->members_map_configuration_options['mouse_scroll_wheel_for_map_zoom'] ) && $this->members_map_configuration_options['mouse_scroll_wheel_for_map_zoom'] === '1' ) ? 'checked' : ''
		);
	}
}
