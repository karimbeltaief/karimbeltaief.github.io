<?php namespace App\Providers;
use Illuminate\Support\ServiceProvider;


class WordpressServiceProvider extends ServiceProvider
{

	/** @var \App\Helpers\LumenHelper $lumenHelper **/
	/** @var \App\Helpers\WpHelper $wpHelper **/
	private $wpHelper, $lumenHelper, $absolutePath;

	/**
	 * WordpressServiceProvider constructor.
	 * @param $app \Illuminate\Contracts\Foundation\Application
	 */
	public function __construct( $app ) {
		parent::__construct( $app );
		$this->lumenHelper = $this->app->make('lumenHelper');
		$this->wpHelper = $this->lumenHelper->wpHelper();
	}

	/**
     * Register any application services.
     * @return void
     */
    public function register(){
	    /** Add Plugin Links to Admin > Plugins Page Entry **/
	    $this->wpHelper
		    ->addPluginLinks(array(
			    '<a target="_blank" href="http://bayareawebpro.com">Developer</a>',
		    ));


	    /** Add FrontEnd Widget **/
	    $this->wpHelper
		    ->addWidget(
			    \App\Widgets\ExampleFrontEndWidget::class
		    );

	    /** Add Admin Bar Nodes **/
	    $this->wpHelper
		    ->addAdminBarNode(
			    false,
			    'lumen_bar_node2',
			    'Lumen Bar Node',
			    '#'
		    )->addAdminBarNode(
			    'lumen_bar_node2',
			    'lumen_bar_node2_child1',
			    'Node Child 1',
			    '#'
		    )->addAdminBarNode(
			    'lumen_bar_node2',
			    'lumen_bar_node2_child2',
			    'Node Child 2',
			    '#'
		    )->addAdminBarNode(
			    'lumen_bar_node2',
			    'lumen_bar_node2_child3',
			    'Node Child 2',
			    '#'
		    );

	    /** Add Shortcodes **/
	    $this->wpHelper
		    ->addShortcode(
			    'auth_register',
			    function ($parameters, $content){
				    return $this->app->call( '\App\Http\Controllers\Auth\RegisterShortcodeController@template', compact('parameters', 'content'));
			    }
		    )
		    ->addShortcode(
			    'auth_login',
			    function ($parameters, $content){
				    return $this->app->call( '\App\Http\Controllers\Auth\LoginShortcodeController@template', compact('parameters', 'content'));
			    }
		    );


	    /** Trigger to create Reservation on google agenda **/
	    $this->wpHelper
        ->addAction(
			    'save_post',
			    function ($post_id, $post, $update){
            if($post->post_type == 'listing') {
					    $this->app->call('\App\Http\Controllers\GCalendarController@postSave', compact( 'post_id', 'post', 'update' ));
            }
			    },
			    10,
			    3
		    )
		    ->addAction(
          'homey_hourly_booking_skip_payment_card',
            function ($reservationId){
              return  $this->app->call('\App\Http\Controllers\GCalendarController@createReservation', compact( 'reservationId' ));
            },
            10,
            1
		    )
        ->addAction(
          'homey_booking_skip_payment_card',
            function ($reservationId){
              return  $this->app->call('\App\Http\Controllers\GCalendarController@createReservation', compact( 'reservationId' ));
            },
            10,
            1
		    );


	    /** Add Nav Menu MetaBoxes **/
      // @todo remove do_action('homey_booking_skip_payment_card', $reservation_id);
	    $this->wpHelper->addMetaBox(
		    'example_menu_meta_box',
		    'Test create reservation GC',
		    function($object, $arguments){
			    $this->lumenHelper
				    ->response($this->app->call( '\App\Http\Controllers\ExampleMetaBoxController@menuMetaBox', compact('object', 'arguments')))
				    ->sendContent();
		    },
		    'nav-menus',
		    'side',
		    'default',
		    2
	    );

	    /** Add Dashboard Widget **/
	    $this->wpHelper
		    ->addDashboardWidget(
			    'example_admin_widget',
			    'Example Admin Widget',
			    function(){
				    $this->lumenHelper
					    ->response($this->app->call( '\App\Widgets\ExampleAdminWidget@template'))
					    ->sendContent();
			    }
		    );

        /** Add WP Rest API Route **/
//        $this->wpHelper
//            ->addRestRoute('wp-lumen/api/v1', '/test', array(
//                'methods'  => ['get'],
//                'callback' => function(){
//                    return $this->app->call('\App\Http\Controllers\ExampleWpRestRouteController@get');
//                },
//            ));
/*-----------------------------------------------------------------------------------*/
// Menu and settings form for google synchronize agenda API
/*-----------------------------------------------------------------------------------*/

	    $this->wpHelper
		    ->addAdminPanel(
			    'admin_menu',
			    'Google synchronize agenda',
			    'GC config options',
			    function(){
				    $this->lumenHelper
					    ->response($this->app->call( '\App\Http\Controllers\SettingsController@template'))
					    ->sendContent();
			    },
			    'manage_options'
		    );

	    /** Add CSS & Scripts **/
	    $this->wpHelper
		    ->enqueueStyle(
			    'lumen',
			    $this->lumenHelper->asset('resources/assets/build/example.css'),
			    array(),
			    '1.0.0',
			    'all'
		    )
		    ->enqueueScript(
			    'lumen',
			    $this->lumenHelper->asset('resources/assets/build/example.js'),
			    array('jquery'),
			    '1.0.0',
			    true
		    );

    }
}
