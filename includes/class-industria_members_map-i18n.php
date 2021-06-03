<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       www.jrwebstudio.com
 * @since      1.0.0
 *
 * @package    Industria_members_map
 * @subpackage Industria_members_map/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Industria_members_map
 * @subpackage Industria_members_map/includes
 * @author     Jeton Ramadani <jetonr@gmail.com>
 */
class Industria_members_map_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'industria_members_map',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
