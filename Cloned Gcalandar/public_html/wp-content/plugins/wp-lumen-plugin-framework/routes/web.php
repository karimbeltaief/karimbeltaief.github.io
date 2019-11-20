<?php
/** @var $router \Illuminate\Routing\Router */

$router->group([
	'prefix'=>'lumen/api', 'middleware' => []
], function($router){
	$router->post('auth/login', 'Auth\AuthController@login');
	$router->post('auth/register', 'Auth\AuthController@register');
	$router->get('auth/logout', 'Auth\AuthController@logout');
});

// For google route
$router->group([
	'prefix'=>'google', 'middleware' => []
], function($router){
  $router->get('/', [
    'as'=>'google.index', 
    'uses'=>'GoogleAccountController@index']
  );
  
  $router->get('oauth', [
    'as'=>'google.store', 
    'uses'=> 'GoogleAccountController@store']
  );
  
  $router->delete('{googleAccount}', [
    'as'=>'google.destroy', 
    'uses'=> 'GoogleAccountController@destroy']
  );
  $router->delete('delete/{googleAccount}',[
      'as' => 'google.ajax.destroy',
      'uses' => 'GoogleAccountController@ajaxDestroy'
  ]);
  
  $router->post('webhook', [
    'as'=>'google.webhook', 
    'uses'=> 'GoogleWebhookController']
  );
});

// For events route
$router->group([
	'prefix'=>'events', 'middleware' => []
], function($router){
  $router->get('timeline', [
    'as'=>'events.index', 
    'uses'=>'EventController@index']
  );
  
  $router->get('grid', [
    'as'=>'grid.events', 
    'uses'=> 'EventsController@grid']
  );
  
  $router->get('manage', [
    'as'=>'manage.events', 
    'uses'=> 'EventsController']
  );
  
  $router->get('check', [
    'as'=>'check.events', 
    'uses'=> 'EventController@check']
  );
});

$router->group([
    'prefix' => 'api', 'middleware' => []
], function($router){
    $router->get('user/{wpUserId}/google-account',[
        'as' => 'api.user.googleAccount',
        'uses' => 'GCalendarController@findUserGoogleAccount'
    ]);
    $router->get('google-account/{accountId}/calendar',[
        'as' => 'api.googleAccount.calendar',
        'uses' => 'GCalendarController@findGoogleAccountCalendars'
    ]);
    $router->get('calendar/{calendarId}/event',[
        'as' => 'api.calendar.event',
        'uses' => 'GCalendarController@findCalendarEvents'
    ]);
    $router->get('calendar/{calendarId}/sync',[
        'as' => 'api.calendar.sync',
        'uses' => 'GCalendarController@syncCalendar'
    ]);
    $router->get('calendar/indisponibilites',[
        'as' => 'api.calendar.indisponibilite',
        'uses' => 'GCalendarController@getIndisponibilite'
    ]);
    $router->post('calendar/{calendarId}/indisponibilites',[
        'as' => 'api.calendar.indisponibilite.create',
        'uses' => 'GCalendarController@addIndisponibilite'
    ]);
    $router->get('calendar/trash',[
        'as' => 'api.calendar.indisponibilite.trash',
        'uses' => 'GCalendarController@trashIndisponibilite'
    ]);
});