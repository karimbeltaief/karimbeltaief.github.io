<?php
class Favethemes_Currency_Converter {

    /**
     * Plugin instance.
     *
     * @var homey
     */
    protected static $instance;


    /**
     * Plugin version.
     *
     * @var string
     */
    protected static $version = '1.0.0';

    /**
     *  Data and rates.
     *
     * @access public
     */
    public $currencies = null;


    /**
     * Constructor.
     */
    protected function __construct()
    {   
        //$this->actions();
        $this->init();
        $this->FCC_inc_files();
        //$this->filters();
    }

    /**
     * Return plugin version.
     *
     * @return string
     */
    public static function getVersion() {
        return static::$version;
    }

    /**
     * Return plugin instance.
     *
     * @return homey
     */
    protected static function getInstance() {
        return is_null( static::$instance ) ? new Favethemes_Currency_Converter() : static::$instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.4.0
     */
    public function __clone() {
      _doing_it_wrong( __FUNCTION__, __( 'Cloning the main instance of favethemes-currency-converter is forbidden.', 'favethemes-currency-converter' ), FCC_PLUGIN_VERSION );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.4.0
     */
    public function __wakeup() {
      _doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of favethemes-currency-converter is forbidden.', 'favethemes-currency-converter' ), FCC_PLUGIN_VERSION );
    }


    /**
     * Initialize plugin.
     *
     * @return void
     */
    public static function run() {
        self::FCC_class_loader();
        static::$instance = static::getInstance();
    }


    /**
     * include files
     *
     * @since 1.0
     *
    */
    function FCC_inc_files() {
  
        require_once(FCC_FUNCTION . '/helper.php');
    }


    /**
     * Plugin actions.
     *
     * @return void
     */
    public function actions() {

        add_action( 'admin_menu', array( $this, 'FCC_register_admin_pages' ) );
        //add_action( 'activated_plugin', array( $this, 'redirect' ) );
    }

    /**
     * Add filters to the WordPress functionality.
     *
     * @return void
     */
    public function filters() {
        //add_filter( 'homey_theme_meta', array( $this, 'homey_field_builder_meta' ), 9, 1 );
    }

    /**
     * Initialize classes
     *
     * @return void
     */
    public function init() {

        FCC_Cron::init();
        if( is_admin() ) {
            
            FCC_Currencies::init();
            FCC_API_Settings::init();

            FCC_Rates::init();
            if(isset($_GET['fcc-update']) && $_GET['fcc-update'] == 1) {
              FCC_Rates::update();
            }
    
        }

        add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'enqueue_scripts' ) );

    }


    public static function enqueue_scripts() {
        $js_path = 'assets/admin/js/';
        $css_path = 'assets/admin/css/';

        //wp_enqueue_style('homey-admin-style', HOMEY_PLUGIN_URL . $css_path . 'style.css', array(), '1.0.0', 'all');
    }


    /**
     * Load plugin files.
     *
     * @return void
     */
    public static function FCC_class_loader()
    {
        $files = apply_filters( 'FCC_class_loader', array(
            FCC_CLASSES . '/class-rates.php',
            FCC_CLASSES . '/class-cron.php',
            FCC_CLASSES . '/class-currencies.php',
            FCC_CLASSES . '/class-api-settings.php',
        ) );

        foreach ( $files as $file ) {
            if ( file_exists( $file ) ) {
                include $file;
            }
        }
    }

    /**
    *
    * Register admin dashboard pages
    */

    public function FCC_register_admin_pages() {

    }
    

    public static function FCC_plugin_activation() {

        self::FCC_create_tables();
        FCC_Cron::FCC_schedule_updates();

    }

    public static function FCC_plugin_deactivate() {
        global $wpdb;
        wp_clear_scheduled_hook( 'favethemes_currencies_update' );

    }

    /**
     * Create tables.
     *
     * @uses dbDelta()
     * @access private
     */
    private static function FCC_create_tables() {
      global $wpdb;
      $wpdb->hide_errors();
      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      self::FCC_schema();
    }

    /**
   * tables schema.
   *
   * @access private
   */
  private static function FCC_schema() { //echo '2222'; wp_die();
    global $wpdb;

    $table_name         = $wpdb->prefix . 'favethemes_currency_converter';
    $charset_collate    = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
      currency_code varchar(3) NOT NULL,
      currency_rate FLOAT NOT NULL,
      currency_data VARCHAR(5000) NOT NULL,
      `timestamp` TIMESTAMP DEFAULT 0 ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY currency_code (currency_code)
    ) $charset_collate;";

    dbDelta($sql);

    $table_name         = $wpdb->prefix . 'fcc_currencies';
    $charset_collate    = $wpdb->get_charset_collate();
    $sql_2 = "CREATE TABLE $table_name (
      id int(10) NOT NULL AUTO_INCREMENT,
      currency_name varchar(255) NOT NULL,
      currency_code varchar(55) NOT NULL,
      currency_symbol varchar(25) NOT NULL,
      currency_position varchar(25) NOT NULL DEFAULT 'before',
      currency_decimal int(10) NOT NULL,
      currency_decimal_separator varchar(10) NOT NULL DEFAULT '.',
      currency_thousand_separator varchar(10) NOT NULL DEFAULT ',',
      PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta($sql_2);

  }

  public function redirect($plugin) {
        /*if ( $plugin == HOMEY_PLUGIN_BASENAME ) {
            wp_redirect( 'admin.php?page=homey_dashboard' );
            wp_die();
        }*/
    }

}
?>