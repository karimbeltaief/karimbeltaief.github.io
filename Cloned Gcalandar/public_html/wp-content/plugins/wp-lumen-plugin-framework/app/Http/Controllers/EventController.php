<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Calendar;
use App\Http\Services\Google;
use Illuminate\Http\Request;
use App\Helpers\LumenHelper;

class EventController extends Controller {

  public function __construct(LumenHelper $helper) 
  {
    $this->middleware('auth');
    $this->helper = $helper;
    $this->request = $this->helper->request();
    $this->auth = $this->helper->auth();
  }

  public function index() 
  {
    $events = $this->auth->user()->events()
            ->orderBy('started_at', 'desc')
            ->get();

    return view('events', compact('events'));
  }

  public function check() 
  {
    $google_id = 'mood.hooby.client.a@gmail.com';

    $calendar = auth()->user()->calendars($google_id);
    
    // If submitted post
     if ($calendar) {

      $service = $calendar->getGoogleService('Calendar');
      
       $event = $calendar->getGoogleService('Calendar_Event', array(
         'summary' => 'Hobby mood 01:24',
         'location' => '800 Howard St., San Francisco, CA 94103',
         'description' => 'A chance to hear more about products.',
         'start' => array(
           'dateTime' => '2019-09-13T09:00:00-07:00',
           'timeZone' => 'America/Los_Angeles',
         ),
         'end' => array(
           'dateTime' => '2019-09-14T17:00:00-07:00',
           'timeZone' => 'America/Los_Angeles',
         ),
         'recurrence' => array(
           'RRULE:FREQ=DAILY;COUNT=1'
         ),
         'reminders' => array(
           'useDefault' => FALSE,
           'overrides' => array(
             array('method' => 'email', 'minutes' => 24 * 60),
             array('method' => 'popup', 'minutes' => 10),
           ),
         ),
         'extendedProperties' => array(
           'private' => array(
             array('hobby_mood_event' => 'reservation')
           ),
         ),
       ));
 
       
       $service->events->insert($google_id, $event);
    }

    return 'TEST';
  }

}
