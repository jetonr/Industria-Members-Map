<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       www.jrwebstudio.com
 * @since      1.0.0
 *
 * @package    Industria_members_map
 * @subpackage Industria_members_map/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Industria_members_map
 * @subpackage Industria_members_map/public
 * @author     Jeton Ramadani <jetonr@gmail.com>
 */
class Industria_members_map_Public {

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
	 * Undocumented variable
	 *
	 * @var [type]
	 */
    private $options;

	/**
	 * Geocoding API
	 * 
	* @since    1.0.0
	* @access   private
	* @var      string
	 */
	const GEOCODING_API  = "https://maps.googleapis.com/maps/api/geocode/json";

	/**
	 * Maps JavaScript API
	 * 
	* @since    1.0.0
	* @access   private
	* @var      string
	 */
	const MAPS_API  = "https://maps.google.com/maps/api/js";

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->set_options();

		// Add plugin shortcode
		add_shortcode( $this->plugin_name, array( $this, 'shortcode' ) );
	}

	/**
	 * Set API KEY
	 *
	 * @return void
	 */
	private function set_options() {
		$options = get_option( 'members_map_configuration_option_name' );
		if ( is_array($options) && !empty($options) ) {
			$this->options = $options;
		}
	}

	/**
	 * Get Options
	 *
	 * @return void
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	* WordPress Shortcode function for this plugin
	*
	* @since    1.0.0
	*
	* @return   mixed
	*/
	public function shortcode( $atts ) {

		$options = $this->get_options();

		// Attributes
		$atts = shortcode_atts(
			array(
				'width'       => isset( $options['map_width'] ) ? $options['map_width'] : "100%",
				'height'      => isset( $options['map_height'] ) ? $options['map_height'] : "500px",
				'type'        => isset( $options['map_type'] ) ? $options['map_type'] : 'roadmap',
				'zoom'        => isset( $options['zoom_size'] ) ? $options['zoom_size'] : '5',
				'scrollwheel' => ( isset( $options['mouse_scroll_wheel_for_map_zoom'] ) && '1' == $options['mouse_scroll_wheel_for_map_zoom'] ) ? 1 : 0,
				'street'      => ( isset( $options['street_view'] ) && '1' == $options['street_view'] ) ? 1 : 0,
			), 
			$atts,
			$this->plugin_name
		);

		return $this->industriaMembersMap( $atts );
	}

	/**
	 * Main function to print google maps
	 *
	 * @param [array] $atts
	 * @return void
	 */
	public function industriaMembersMap( $atts ) {
		$options   = $this->get_options();
		$force_update = false;
		if ( isset( $options['force_refresh'] ) ) {
			$force_update = true;
			unset($options['force_refresh']);
			update_option( 'members_map_configuration_option_name', $options );
		}
		$locations = $this->get_locations( $force_update );
		ob_start();
		include_once 'partials/map-template.php';
		return ob_get_clean();
	}


	/**
	*  Get Wordpress User Locations
	* 
	* @since    1.0.0
	*
	* @return   string 
	 */
	public function get_locations( $forceUpdate = false ) {

		$cachedUsers = get_transient( 'jr_industria_members_location' );

		if ( false === $cachedUsers || $forceUpdate ) {
			$users = get_users();
			$cachedUsers  = array();
			$search_value = array(",",".",'"',"'","\\","/","&","[","]","(",")","{","}",":","`","!","@","#","%","=","+");
			
			foreach ($users as $key => $user) {

				$str = str_replace( $search_value,"", get_cimyFieldValue($user->ID,'STRASSE') ); 
				$plz = str_replace( $search_value,"", get_cimyFieldValue($user->ID,'PLZ') );
				$ort = str_replace( $search_value,"", get_cimyFieldValue($user->ID,'ORT') );

				if ( '' != $str && '' != $plz && '' != $ort ) {
					
					$address_hash = md5( $str . $plz . $ort );
					$getLocation = get_user_meta( $user->ID, 'imm_location', true );
					$cachedUsers[$user->ID]['name'] = $user->first_name;
					$cachedUsers[$user->ID]['surname'] = $user->last_name;
					$cachedUsers[$user->ID]['nickname'] = $user->nickname;

					if ( "" == $getLocation || false == $getLocation || empty( $getLocation ) ) {

						$coordinates = $this->get_coordinates( $str .", ".$plz . " " . $ort);

						if ( is_array($coordinates) && !empty($coordinates) ) {
							$coordinates['hash'] = $address_hash;
							update_user_meta( $user->ID, 'imm_location', $coordinates );
							$cachedUsers[$user->ID]['location'] = $coordinates;
						}
					} else {
						if ( $getLocation['hash'] == $address_hash ) {
							$cachedUsers[$user->ID]['location'] = $getLocation;
						} else {
							$coordinates = $this->get_coordinates( $str .", ".$plz . " " . $ort);
							if ( is_array($coordinates) && !empty($coordinates) ) {
								$coordinates['hash'] = $address_hash;
								update_user_meta( $user->ID, 'imm_location', $coordinates );
								$cachedUsers[$user->ID]['location'] = $coordinates;
							}

						}

					}
				}
			}
			set_transient( 'jr_industria_members_location', $cachedUsers, 86400 );
		}

		return json_encode($cachedUsers);
	}

	/**
	 * Retrieve coordinates for an address
	 *
	 * Coordinates are cached using transients and a hash of the address
	 *
	 * @access      private
	 * 
	 * @since       1.0.0
	 * 
	 * @return      mixed
	*/
	public function get_coordinates( $address ) {
		
		$options    = $this->get_options();
		$args       = array( 'address' => urlencode( $address ), 'key' => $options['google_maps_api_key'] );
		$url        = add_query_arg( $args, self::GEOCODING_API );
		$response 	= wp_remote_get( $url );

		if( is_wp_error( $response ) ) {
			return;
		}

		$data = wp_remote_retrieve_body( $response );

		if( is_wp_error( $data ) ) {
			return;
		}

		if ( $response['response']['code'] == 200 ) {

			$data = json_decode( $data );
			
			if ( $data->status === 'OK' ) {

				$formatedData = array();
				$coordinates  = $data->results[0]->geometry->location;

				$formatedData['lat'] 	= $coordinates->lat;
				$formatedData['lng'] 	= $coordinates->lng;
				$formatedData['address'] = (string) $data->results[0]->formatted_address;

				return $formatedData;

			} elseif ( $data->status === 'ZERO_RESULTS' ) {
				return __( 'No location found for the entered address.', 'industria_members_map' );
			} elseif( $data->status === 'INVALID_REQUEST' ) {
				return __( 'Invalid request. Did you enter an address?', 'industria_members_map' );
			} else {
				return __( 'Something went wrong while retrieving your map, please ensure you have entered the short code correctly.', 'industria_members_map' );
			}

		} else {
			return __( 'Unable to contact Google API service.', 'industria_members_map' );
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/industria_members_map-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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
		$options    = $this->get_options();
		$args       = array( 'key' => $options['google_maps_api_key'] );
		$url        = add_query_arg( $args, self::MAPS_API );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/industria_members_map-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'googlemaps', $url, array(), NULL, false );
		wp_enqueue_script( 'marker-cluster', 'https://unpkg.com/@googlemaps/markerclustererplus/dist/index.min.js', array('googlemaps'), NULL, false );

	}

}
