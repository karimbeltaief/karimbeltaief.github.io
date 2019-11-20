<?php

namespace App\Jobs;

use App\Jobs\SynchronizeGoogleResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\OfflineEvent;
use App\Repositories\OfflineEventRepository;
use App\Utilities\QueryParametersHelper;

class SynchronizeGoogleEvents extends SynchronizeGoogleResource implements ShouldQueue {

  use Dispatchable,
      InteractsWithQueue,
      Queueable,
      SerializesModels;

  const INDISPONIBLE_KEY = 'indisponible';
  const RESERVATION_KEY = 'reservation';

  public function getGoogleRequest($service, $options) {
    return $service->events->listEvents(
                    $this->synchronizable->google_id, $options
    );
  }

  public function buildOfflineRepository() {
    return new OfflineEventRepository(new OfflineEvent());
  }

  /**
   * @param \Google_Service_Calendar_Event $googleEvent
   * @return mixed
   */
  public function syncItem($googleEvent) {
    try {
      $event_info = $this->storableEvent($googleEvent);
      if(empty($event_info)) {
        return FALSE;
      }
      
      $listing_id = $this->synchronizable->listing_id ?? 0;
      if (!empty($event_info['token'])) {
        $token = $event_info['token'];
      }
      elseif ($event = OfflineEvent::where('google_id', $googleEvent->id)->first()) {
        $token = $event->token ?? NULL;
      }
      $token = $token ?? QueryParametersHelper::uniqidReal();
      if (!isset($event_info['uid']) || empty($event_info['uid'])) {
        $event_info['uid'] = QueryParametersHelper::buildUid($listing_id, $token);
      }

      $data_event = [
        'name' => !empty($googleEvent->summary) ? $googleEvent->summary : '(No title)',
        'description' => $googleEvent->description,
        'allday' => $this->isAllDayEvent($googleEvent),
        'started_at' => $event_info['start'],
        'ended_at' => $event_info['end'],
        'hobby_mood_event' => $event_info['hobby_mood_event'],
        'google_id' => $googleEvent->id,
        'token' => $token
      ];

      $this->synchronizable->events()->updateOrCreate(['uid' => $event_info['uid']], $data_event);

      // Event offlines
      $data_event['listing_id'] = $listing_id;
      $data_event['calendar_id'] = $this->synchronizable->id;
      $this->buildOfflineRepository()->updateOrCreateOfflineEvent($event_info['uid'], $data_event);

      Log::info('SYNC[EVENT]: ' . $googleEvent->id);
    } catch (\Exception $e) {
      Log::error('SYNC[EVENT][ID]: ' . $googleEvent->id);
      Log::error('SYNC[EVENT]:' . $e->getTraceAsString());
    }
  }

  public function dropAllSyncedItems() {

    $this->synchronizable->events()->delete();
  }

  protected function isAllDayEvent($googleEvent) {
    return !$googleEvent->start->dateTime && !$googleEvent->end->dateTime;
  }

  protected function parseDatetime($googleDatetime, $tz = "UTC") {
    $rawDatetime = $googleDatetime->dateTime ?: $googleDatetime->date;

    return Carbon::parse($rawDatetime)->setTimezone($tz);
  }

  private function clean_pattern($option, $default) {
    if (!empty($option) && $option != '') {
      $string = strtolower(htmlentities($option));
      $string = preg_replace("/&(.)(uml);/", "$1e", $string);
      $string = preg_replace("/&(.)(acute|cedil|circ|ring|tilde|uml);/", "$1", $string);
      $string = preg_replace("/([^a-z0-9]+)/", "", html_entity_decode($string));
      return $string;
    }

    return $default;
  }

  /**
   * Check event storable within listing id.
   * 
   */
  private function storableEvent($googleEvent) {
    $event = [];
    $is_event_hobby_mood = FALSE;
    
    // post id , calendar id
    if (!((int) $this->synchronizable->listing_id > 0 && (int) $this->synchronizable->id > 0)) {
      return NULL;
    }
    
    $extendProperties = $googleEvent->getExtendedProperties() ?? NULL;
    
    if ($is_event_hobby_mood = isset($extendProperties->private['hobby_mood_event'])) {
      $event['hobby_mood_event'] = $extendProperties->private['hobby_mood_event'];
      $event['uid'] = $extendProperties->private["uid"];
      $event['token'] = $extendProperties->private["token"];
    }
    // Filtre events by text summary
    elseif (!empty($googleEvent->summary)) {
      $sanitize = preg_replace('/[^a-z]/i', '', $googleEvent->summary);
      $sanitize = strtolower($sanitize);

      $pattern_indisponible = self::clean_pattern(
          get_option('oo_gsa_pattern_indisponible', ''),
          self::INDISPONIBLE_KEY
      );

      $pattern_reservation = self::clean_pattern(
          get_option('oo_gsa_pattern_reservation', ''),
          self::RESERVATION_KEY
      );

      if ($is_event_hobby_mood = ($pattern_indisponible == $sanitize)) {
        $event['hobby_mood_event'] = self::INDISPONIBLE_KEY;
      }
      elseif ($is_event_hobby_mood = ($pattern_reservation == $sanitize)) {
        $event['hobby_mood_event'] = self::RESERVATION_KEY;
      }
    }

    if ($googleEvent->status === 'cancelled' || !$is_event_hobby_mood) {
      $this->buildOfflineRepository()->deleteByParams(['google_id' => $googleEvent->id]);
      $this->synchronizable->events()
           ->where('google_id', $googleEvent->id)
           ->delete();
      
      return NULL;
    }
    
    // date_default_timezone_set($googleEvent->getStart()->getTimeZone() ?? get_option('timezone_string'));
    
    $event['google_id'] = $googleEvent->id;
    $event['start'] = $this->parseDatetime($googleEvent->start, $googleEvent->getStart()->getTimeZone());
    $event['end'] = $this->parseDatetime($googleEvent->end, $googleEvent->getStart()->getTimeZone());
    $start_datetime = new \Datetime($event['start']);
    $end_datetime = new \Datetime($event['end']);
    
    // switch 
    // @todo homey_mon_fri_closed
    $booking_start_hour = get_post_meta($this->synchronizable->listing_id, 'homey_mon_fri_open',true );
    if($booking_start_hour == '') $booking_start_hour = '00:00';
    $booking_end_hour = get_post_meta($this->synchronizable->listing_id, 'homey_mon_fri_close',true );
    if($booking_end_hour == '') $booking_end_hour = '23:59';
    // @todo homey_sat_closed
    // @todo homey_sun_closed
    
    $description = [];
    $description[] = "- Attention à l'horaire qui devrait être entre $booking_start_hour à $booking_end_hour" ;
    if ($start_datetime->format('Y-m-d') == $end_datetime->format('Y-m-d')) {
      $booking_start = (int) preg_replace('/([^0-9]+)/', '', $booking_start_hour);
      $booking_end = (int) preg_replace('/([^0-9]+)/', '', $booking_end_hour);
      $gc_start_time = intval($start_datetime->format('Hi'));
      $gc_end_time = intval($end_datetime->format('Hi'));
      if ($booking_start <= $gc_start_time && $gc_end_time <= $booking_end) {
        return $event;
      }
    }
    else {
      $description[] = "- Vérifier la date ({$event['hobby_mood_event']}) qui devrait être le même jour";
    }
    
    
    // Event rejected: update and send to gcal.
    // 1- Change to incorrect date means, remove event in database
    $condition = ['google_id' => $event['google_id']];
    $this->synchronizable->events()->where($condition)->delete();  
    if(!empty($event['uid'])) {
      $this->buildOfflineRepository()->deleteByParams(["uid" => $event["uid"]]);
    }
    
    if (!empty($event['uid'])) {
      $extendProperties->setPrivate([]);
      $googleEvent->setExtendedProperties($extendProperties);
    }
    
    // 2- Inform gcalendar
    $srvCalendar = $this->synchronizable->getGoogleService("Calendar");
    $googleEvent->summary = $googleEvent->summary .": incorrecte"; 
    $googleEvent->description = implode(PHP_EOL, $description);
    $srvCalendar->events->update(
      $this->synchronizable->google_id,
      $googleEvent->getId(),
      $googleEvent
    );
    
    return NULL;
  }

}
