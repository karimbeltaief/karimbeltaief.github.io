<?php
class Homey_Permalinks {


	/**
	 * Sets up init
	 *
	 */
	public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'homey_register_settings' ) );
    }


	public static function render() {
      
        // Flush the rewrite rules if the settings were updated.
        if ( isset( $_GET['settings-updated'] ) )
            flush_rewrite_rules(); ?>

        <div class="wrap">

            <?php settings_errors(); ?>

            <?php
            $template = HOMEY_TEMPLATES.'tabs.php';

            if ( file_exists( $template ) ) {
                //load_template( $template );
            }
            ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'homey_settings' ); ?>
                <?php do_settings_sections( 'homey_permalinks' ); ?>
                <?php submit_button( esc_attr__( 'Update Permalinks', 'homey-core' ), 'primary' ); ?>
            </form>

        </div><!-- wrap -->
    <?php
    }

    public static function homey_register_settings() {

        // Register the setting.
        register_setting( 'homey_settings', 'homey_settings', array( __CLASS__, 'homey_validate_settings' ) );

        /* === Settings Sections === */
        add_settings_section( 'permalinks', esc_html__( 'Permalinks', 'homey-core' ), array( __CLASS__, 'homey_section_permalinks' ), 'homey_permalinks' );

        /* === Settings Fields === */
        add_settings_field( 'listing_rewrite_base',   esc_html__( 'Listing',   'homey-core' ), array( __CLASS__, 'homey_listing_slug_field'   ), 'homey_permalinks', 'permalinks' );

        add_settings_field( 'listing_type_rewrite_base',   esc_html__( 'Listing Type',   'homey-core' ), array( __CLASS__, 'listing_type_callback'), 'homey_permalinks', 'permalinks' );

        add_settings_field( 'listing_room_type_rewrite_base',   esc_html__( 'Room Type',   'homey-core' ), array( __CLASS__, 'room_type_callback'   ), 'homey_permalinks', 'permalinks' );

        add_settings_field( 'listing_amenity_rewrite_base',   esc_html__( 'Amenity',   'homey-core' ), array( __CLASS__, 'amenity_callback'   ), 'homey_permalinks', 'permalinks' );

        add_settings_field( 'listing_facility_rewrite_base',   esc_html__( 'Facility',   'homey-core' ), array( __CLASS__, 'facility_callback'   ), 'homey_permalinks', 'permalinks' );

        add_settings_field( 'listing_country_rewrite_base',   esc_html__( 'Country',   'homey-core' ), array( __CLASS__, 'country_callback'   ), 'homey_permalinks', 'permalinks' );

        add_settings_field( 'listing_state_rewrite_base',   esc_html__( 'State',   'homey-core' ), array( __CLASS__, 'state_callback'   ), 'homey_permalinks', 'permalinks' );

        add_settings_field( 'listing_city_rewrite_base',   esc_html__( 'City',   'homey-core' ), array( __CLASS__, 'city_callback'   ), 'homey_permalinks', 'permalinks' );

        add_settings_field( 'listing_area_rewrite_base',   esc_html__( 'Area',   'homey-core' ), array( __CLASS__, 'area_callback'   ), 'homey_permalinks', 'permalinks' );
        
    }

    /**
     * Validates the plugin settings.
     *
     * @since  1.0.8
     * @access public
     * @param  array  $input
     * @return array
     */
    public static function homey_validate_settings( $settings ) {

        // Text boxes.
        $settings['listing_rewrite_base'] = $settings['listing_rewrite_base'] ? trim( strip_tags( $settings['listing_rewrite_base']   ), '/' ) : '';

        $settings['listing_type_rewrite_base'] = $settings['listing_type_rewrite_base'] ? trim( strip_tags( $settings['listing_type_rewrite_base']   ), '/' ) : '';

        $settings['listing_room_type_rewrite_base'] = $settings['listing_room_type_rewrite_base'] ? trim( strip_tags( $settings['listing_room_type_rewrite_base']   ), '/' ) : '';
        $settings['listing_facility_rewrite_base'] = $settings['listing_facility_rewrite_base'] ? trim( strip_tags( $settings['listing_facility_rewrite_base']   ), '/' ) : '';
        $settings['listing_amenity_rewrite_base'] = $settings['listing_amenity_rewrite_base'] ? trim( strip_tags( $settings['listing_amenity_rewrite_base']   ), '/' ) : '';
        $settings['listing_country_rewrite_base'] = $settings['listing_country_rewrite_base'] ? trim( strip_tags( $settings['listing_country_rewrite_base']   ), '/' ) : '';
        $settings['listing_state_rewrite_base'] = $settings['listing_state_rewrite_base'] ? trim( strip_tags( $settings['listing_state_rewrite_base']   ), '/' ) : '';
        $settings['listing_city_rewrite_base'] = $settings['listing_city_rewrite_base'] ? trim( strip_tags( $settings['listing_city_rewrite_base']   ), '/' ) : '';
        $settings['listing_area_rewrite_base'] = $settings['listing_area_rewrite_base'] ? trim( strip_tags( $settings['listing_area_rewrite_base']   ), '/' ) : '';


        // Return the validated/sanitized settings.
        return $settings;
    }

    /**
     * Permalinks section callback.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public static function homey_section_permalinks() { ?>

        <p class="description">
            <?php esc_html_e( 'Set up custom permalinks for the listing section on your site.', 'homey-core' ); ?>
        </p>
    <?php }

    /**
     * listing rewrite base field callback.
     *
     * @since  1.2.0
     * @access public
     * @return void
     */
    public static function homey_listing_slug_field() { ?>

        <label>
            <code><?php echo esc_url( home_url( '/' ) ); ?></code>
            <input type="text" class="regular-text code" name="homey_settings[listing_rewrite_base]" value="<?php echo esc_attr( homey_get_listing_rewrite_base() ); ?>" />
        </label>

    <?php }

    /**
     * listing rewrite base field callback.
     *
     * @since  1.2.0
     * @access public
     * @return void
     */
    public static function listing_type_callback() { ?>

        <label>
            <code><?php echo esc_url( home_url( '/' ) ); ?></code>
            <input type="text" class="regular-text code" name="homey_settings[listing_type_rewrite_base]" value="<?php echo esc_attr( homey_get_listing_type_rewrite_base() ); ?>" />
        </label>

    <?php }

    /**
     * listing rewrite base field callback.
     *
     * @since  1.2.0
     * @access public
     * @return void
     */
    public static function room_type_callback() { ?>

        <label>
            <code><?php echo esc_url( home_url( '/' ) ); ?></code>
            <input type="text" class="regular-text code" name="homey_settings[room_type_rewrite_base]" value="<?php echo esc_attr( homey_get_room_type_rewrite_base() ); ?>" />
        </label>

    <?php }

    /**
     * listing rewrite base field callback.
     *
     * @since  1.2.0
     * @access public
     * @return void
     */
    public static function amenity_callback() { ?>

        <label>
            <code><?php echo esc_url( home_url( '/' ) ); ?></code>
            <input type="text" class="regular-text code" name="homey_settings[amenity_rewrite_base]" value="<?php echo esc_attr( homey_get_amenity_rewrite_base() ); ?>" />
        </label>

    <?php }

    /**
     * listing rewrite base field callback.
     *
     * @since  1.2.0
     * @access public
     * @return void
     */
    public static function facility_callback() { ?>

        <label>
            <code><?php echo esc_url( home_url( '/' ) ); ?></code>
            <input type="text" class="regular-text code" name="homey_settings[facility_rewrite_base]" value="<?php echo esc_attr( homey_get_facility_rewrite_base() ); ?>" />
        </label>

    <?php }

    /**
     * listing rewrite base field callback.
     *
     * @since  1.2.0
     * @access public
     * @return void
     */
    public static function country_callback() { ?>

        <label>
            <code><?php echo esc_url( home_url( '/' ) ); ?></code>
            <input type="text" class="regular-text code" name="homey_settings[country_rewrite_base]" value="<?php echo esc_attr( homey_get_country_rewrite_base() ); ?>" />
        </label>

    <?php }

    /**
     * listing rewrite base field callback.
     *
     * @since  1.2.0
     * @access public
     * @return void
     */
    public static function state_callback() { ?>

        <label>
            <code><?php echo esc_url( home_url( '/' ) ); ?></code>
            <input type="text" class="regular-text code" name="homey_settings[state_rewrite_base]" value="<?php echo esc_attr( homey_get_state_rewrite_base() ); ?>" />
        </label>

    <?php }

    /**
     * listing rewrite base field callback.
     *
     * @since  1.2.0
     * @access public
     * @return void
     */
    public static function city_callback() { ?>

        <label>
            <code><?php echo esc_url( home_url( '/' ) ); ?></code>
            <input type="text" class="regular-text code" name="homey_settings[city_rewrite_base]" value="<?php echo esc_attr( homey_get_city_rewrite_base() ); ?>" />
        </label>

    <?php }

    /**
     * listing rewrite base field callback.
     *
     * @since  1.2.0
     * @access public
     * @return void
     */
    public static function area_callback() { ?>

        <label>
            <code><?php echo esc_url( home_url( '/' ) ); ?></code>
            <input type="text" class="regular-text code" name="homey_settings[area_rewrite_base]" value="<?php echo esc_attr( homey_get_area_rewrite_base() ); ?>" />
        </label>

    <?php }

	
}