<?php
/**
 * Plugin options functions.
 *
 * @package    Homey
 * @subpackage Homey Core
 * @author     Waqas Riaz <waqas@favethemes.com>
 * @copyright  Copyright (c) 2019, Favethemes
 * @link       http://favethemes.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Returns the listing rewrite base. Used for single properties.
 *
 * @since  1.2.0
 * @access public
 * @return string
 */
function homey_get_listing_rewrite_base() {
	return apply_filters( 'homey_get_listing_rewrite_base', homey_get_setting( 'listing_rewrite_base' ) );
}

function homey_get_listing_type_rewrite_base() {
	return apply_filters( 'homey_get_listing_type_rewrite_base', homey_get_setting( 'listing_type_rewrite_base' ) );
}

function homey_get_room_type_rewrite_base() {
	return apply_filters( 'homey_get_room_type_rewrite_base', homey_get_setting( 'room_type_rewrite_base' ) );
}

function homey_get_amenity_rewrite_base() {
	return apply_filters( 'homey_get_amenity_rewrite_base', homey_get_setting( 'amenity_rewrite_base' ) );
}

function homey_get_facility_rewrite_base() {
	return apply_filters( 'homey_get_facility_rewrite_base', homey_get_setting( 'facility_rewrite_base' ) );
}

function homey_get_country_rewrite_base() {
	return apply_filters( 'homey_get_country_rewrite_base', homey_get_setting( 'country_rewrite_base' ) );
}

function homey_get_state_rewrite_base() {
	return apply_filters( 'homey_get_state_rewrite_base', homey_get_setting( 'state_rewrite_base' ) );
}

function homey_get_city_rewrite_base() {
	return apply_filters( 'homey_get_city_rewrite_base', homey_get_setting( 'city_rewrite_base' ) );
}

function homey_get_area_rewrite_base() {
	return apply_filters( 'homey_get_area_rewrite_base', homey_get_setting( 'area_rewrite_base' ) );
}


/**
 * Returns a plugin setting.
 *
 * @since  1.2.0
 * @access public
 * @param  string  $setting
 * @return mixed
 */
function homey_get_setting( $setting ) {

	$defaults = homey_get_default_settings();
	$settings = wp_parse_args( get_option('homey_settings', $defaults ), $defaults );

	return isset( $settings[ $setting ] ) ? $settings[ $setting ] : false;
}

/**
 * Returns the default settings for the plugin.
 *
 * @since  1.2.0
 * @access public
 * @return array
 */
function homey_get_default_settings() {

	$settings = array(
		'listing_rewrite_base'   => 'listing',
		'listing_type_rewrite_base'   => 'type',
		'room_type_rewrite_base'   => 'room_type',
		'amenity_rewrite_base'   => 'amenity',
		'facility_rewrite_base'   => 'facility',
		'country_rewrite_base'   => 'country',
		'state_rewrite_base'   => 'state',
		'city_rewrite_base'   => 'city',
		'area_rewrite_base'   => 'area'
	);

	return $settings;
}
