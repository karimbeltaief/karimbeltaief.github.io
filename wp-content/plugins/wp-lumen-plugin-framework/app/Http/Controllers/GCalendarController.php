<?php

/**
 * Created by PhpStorm.
 * User: valisoa
 * Date: 8/27/19
 * Time: 9:28 AM
 */

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\OfflineEvent;
use App\Models\Event;
use App\Repositories\OfflineEventRepository;
use App\Helpers\LumenHelper;
use App\Jobs\SynchronizeGoogleEvents;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Utilities\QueryParametersHelper;

class GCalendarController extends Controller {

  private $googleAccountFields = [
    // Ne pas inclure le token par sécurité
    "id", "wp_user_ID", "google_id", "name", "created_at", "updated_at"
  ];

  /**
   *
   * @var App\Repositories\OfflineEventRepository
   */
  private $offlineEventRepo;

  public function __construct(LumenHelper $helper, OfflineEvent $offlineEvent) {
    $this->helper = $helper;
    $this->auth = $this->helper->auth();
    $this->request = $this->helper->request();

    // set the model
    $this->offlineEventRepo = new OfflineEventRepository($offlineEvent);
  }

  public function findUserGoogleAccount($wpUserId) {
    /**
     * @var \App\Models\WpUser $user
     */
    $user = auth()->user();
    $googleAccount = $user->googleAccounts()->firstOrFail($this->googleAccountFields);

    return $this->helper->response(json_encode($googleAccount), 200, [
              "Content-type" => "application/json"
    ]);
  }

  public function findGoogleAccountCalendars($accountId) {
    /**
     * @var \App\Models\WpUser $user
     */
    $user = auth()->user();
    $postId = $this->helper->request()->get("postId");
    $meta_key = "homey_calendar_meta";
    $calendar_meta = get_post_meta($postId, $meta_key, TRUE);
    /**
     * @var \App\Models\GoogleAccount $account
     */
    $account = $user->googleAccounts()->where("id", $accountId)->firstOrFail($this->googleAccountFields);
    if ($calendar_meta != FALSE && is_array($calendar_meta)) {
      $calendars = $account->calendars()->where("id", $calendar_meta["id"])->get();
    }
    else {
      $calendars = $account->calendars()->where("listing_id", "=", NULL)->get();
    }

    return $this->helper->response(json_encode($calendars), 200, [
              "Content-type" => "application/json"
    ]);
  }

  public function findCalendarEvents($calendarId) {
    /**
     * @var \App\Models\WpUser $user
     */
    $user = auth()->user();
    $accountId = $this->request->get("googleAccount");
    /**
     * @var \App\Models\GoogleAccount $account
     */
    $account = $user->googleAccounts()->where("id", $accountId)->firstOrFail($this->googleAccountFields);
    $events = $account->calendars()
            ->where("ID", $calendarId)
            ->firstOrFail()
            ->events()
            ->get();

    return $this->helper->response(json_encode($events), 200, [
              "Content-type" => "application/json"
    ]);
  }

  public function syncCalendar($calendarId) {
    $result = [];
    /**
     * @var \App\Models\WpUser $user
     */
    $user = auth()->user();
    /**
     * @var \App\Models\GoogleAccount $account
     */
    $account = $user->googleAccounts()->firstOrFail($this->googleAccountFields);
    /**
     * @var \App\Models\Calendar $calendar
     * @var \Google_Service_Calendar $srvCalendar
     */
    $calendar = $account->calendars()
            ->where("ID", $calendarId)
            ->firstOrFail();
    $postId = $this->request->get("postId");
    $start = $this->request->get("start");
    $end = $this->request->get("end");
    $tz = $this->request->get("tz", NULL) ? ((int) $this->request->get("tz")) * 60 : NULL;
    if (empty($tz)) {
      $gmt_offset = get_option('gmt_offset', NULL);
      $tz = $gmt_offset ? ((int) $gmt_offset - 1) * 3600 : 3600; // UTC+2 <=> 'Europe/Paris'
    }
    $dateFormat = "Y-m-d H:i";
    $timezone = new \DateTimeZone(QueryParametersHelper::getTimeZoneName($tz));
    $startDt = \DateTime::createFromFormat($dateFormat, $start, $timezone);
    $endDt = \DateTime::createFromFormat($dateFormat, $end, $timezone);
    $studio = get_post($postId);

    $args = array(
      'post_type' => 'homey_reservation',
      'post_status' => 'any',
      'posts_per_page' => -1,
      'meta_query' => array(
        'relation' => 'AND',
        array(
          'key' => 'reservation_listing_id',
          'value' => $postId,
          'type' => 'NUMERIC',
          'compare' => '='
        ),
        array(
          'key' => 'reservation_status',
          'value' => 'booked',
          'type' => 'CHAR',
          'compare' => '='
        )
      )
    );

    $wpQry = new \WP_Query($args);

    $srvCalendar = $calendar->getGoogleService("Calendar");
    if ($wpQry->have_posts()) {
      $posts = $this->filterPosts($wpQry->get_posts(), $start, $end, $tz);
      foreach ($posts as $post) {
        $resID = $post->ID;
        $reservation_meta = get_post_meta($resID, "reservation_meta", true);

        $listing_renter = get_post_meta($resID, 'listing_renter', true);
        $renter = homey_usermeta($listing_renter);
        if (!array_key_exists("check_in_hour", $reservation_meta))
          continue;
        $description = <<<description
Réservé par le client {$renter["username"]}
- email: {$renter["email"]}
- montant: {$reservation_meta["total"]} €
description;
        $sourceFormat = "Y-m-d H:i";
        $check_in_hour = \DateTime::createFromFormat($sourceFormat, $reservation_meta["check_in_hour"], $timezone);
        $check_out_hour = \DateTime::createFromFormat($sourceFormat, $reservation_meta["check_out_hour"], $timezone);
        $eventBody = [
          "summary" => $studio->post_title . " " . $check_in_hour->format($sourceFormat) . " - " . $check_out_hour->format("H:i"),
          "location" => get_post_meta($postId, 'homey_listing_address', true),
          "description" => $description,
          "start" => array(
            "dateTime" => $check_in_hour->format("c"),
            "timeZone" => QueryParametersHelper::getTimeZoneName($tz)
          ),
          "end" => array(
            "dateTime" => $check_out_hour->format("c"),
            "timeZone" => QueryParametersHelper::getTimeZoneName($tz)
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
              array(
                'hobby_mood_event' => SynchronizeGoogleEvents::RESERVATION_KEY,
              )
            ),
          )
        ];
        try {
          $event = new \Google_Service_Calendar_Event($eventBody);
          /**
           * @var \Google_Service_Calendar $srvCalendar
           * Find the reccord from google agenda first.
           */
          $listEvents = $srvCalendar->events->listEvents($calendar->google_id, [
            "privateExtendedProperty" => [
              "hobby_mood_event=reservation"
            ],
            "timeMin" => $startDt->format("c"),
            "timeMax" => $endDt->format("c"),
            "timeZone" => QueryParametersHelper::getTimeZoneName($tz),
            "q" => $event->summary
          ]);
          if (empty($listEvents->getItems())) {
            $srvCalendar->events->insert($calendar->google_id, $event);
          }
          else {
            $ev = $listEvents->getItems()[0];
            $event->setId($ev->id);
            $srvCalendar->events->update($calendar->google_id, $ev->id, $event);
          }
          $result = [
            "status" => "success",
            "message" => "Tout les événements sont synchronisés."
          ];
        } catch (\Exception $e) {
          $result = [
            "status" => "error",
            "message" => $e->getMessage()
          ];
        }
      }
      if (count($posts) == 0) {
        $result = [
          "status" => "success",
          "message" => "Aucun événements à synchroniser."
        ];
      }
    }
    else {
      $result = [
        "status" => "success",
        "message" => "Aucun événements à synchroniser."
      ];
    }
    return $this->helper->response(json_encode($result), 200, [
              "Content-Type" => "application/json"
    ]);
  }

  public function getIndisponibilite() {
    /**
     * /api/calendar/indisponibilites?postId=342&tz=180&start=2019-08-25%2000:00&end=2019-08-27%2000:00
     */
    $timezone = 'UTC';
    $postId = $this->helper->request()->get("postId");
    /**
     * @var \App\Models\Calendar $calendar
     */
    $calendar = NULL;

    if ((int) $postId > 0) {
      $calendar_meta = get_post_meta($postId, "homey_calendar_meta", TRUE);
      if ($calendar_meta && $calendar_meta != "" && is_array($calendar_meta)) {
        $conditions = [
          ["id", "=", $calendar_meta['id']],
          ["listing_id", "=", $postId], // Required listing_id
        ];
        try {
          $calendar = Calendar::query()->where($conditions)->first();
        } catch (ModelNotFoundException $exception) {
          $conditions = [
            ["google_id", "=", $calendar_meta['google_id']],
            ["listing_id", "=", $postId],
          ];
          $calendar = Calendar::query()->where($conditions)->first();
        }
      }
    }

    $conditions = [
      ["started_at", ">=", $this->request->get("start") ?? NULL],
      ["ended_at", "<=", $this->request->get("end")],
      ["hobby_mood_event", "=", SynchronizeGoogleEvents::INDISPONIBLE_KEY]
    ];
    if (!empty($calendar)) {
      $timezone = $calendar->timezone ?? $timezone;
      $events = $calendar->events()->where($conditions)->get()->all();
    }
    else {
      if ((int) $postId) {
        $conditions[] = ["listing_id", "=", $postId];
      }
      $events = $this->offlineEventRepo->getIndisponibles($conditions);
    }
    /**
     * @var \App\Models\OfflineEvent $event
     */
    $events = array_map( function($event) use ($timezone) {
      $array = $event->toArray();
      $date = new \DateTime($event->started_at, new \DateTimeZone($timezone));
      $isSummer = (int) $date->format('I');
      $array['started_at'] = $date->format('U') + 3600 *(1 - $isSummer);
      $date = new \DateTime($event->ended_at, new \DateTimeZone($timezone));
      $array['ended_at'] = $date->format('U') + 3600 *(1 - $isSummer);
      if (isset($array['calendar'])) unset($array['calendar']);
      return $array;
    }, $events);
    
    return $this->helper->response(json_encode($events), 200, [
              "Content-Type" => "application/json"
    ]);
  }

  private function filterPosts(array $get_posts, $start, $end, $tz) {
    $posts = [];
    $dateFormat = "Y-m-d H:i";
    $timezone = new \DateTimeZone(QueryParametersHelper::getTimeZoneName($tz));
    $startDt = \DateTime::createFromFormat($dateFormat, $start, $timezone);
    $endDt = \DateTime::createFromFormat($dateFormat, $end, $timezone);
    foreach ($get_posts as $post) {
      $resID = $post->ID;
      $reservation_meta = get_post_meta($resID, "reservation_meta", true);
      if (!array_key_exists("check_in_hour", $reservation_meta))
        continue;
      $sourceFormat = "Y-m-d H:i";
      $check_in_hour = \DateTime::createFromFormat($sourceFormat, $reservation_meta["check_in_hour"], $timezone);
      $check_out_hour = \DateTime::createFromFormat($sourceFormat, $reservation_meta["check_out_hour"], $timezone);
      if ((($startDt->getTimestamp() - $check_in_hour->getTimestamp()) <= 0) && (($check_out_hour->getTimestamp() - $endDt->getTimestamp()) <= 0)) {
        $posts[] = $post;
      }
    }
    return $posts;
  }

  public function addIndisponibilite($calendarId) {
    /**
     * https://hobby-mood.ngrok.io/api/calendar/31/indisponibilites?postId=342&tz=180&start=2019-08-25%2000:00&end=2019-08-27%2000:00
     * @var \App\Models\WpUser $user
     * @var \App\Models\GoogleAccount $account
     */
    /// Non déconnecté <=> non rattaché à un calendrier => listing_id =/= null 
    // Même si l'utilisateur est connécté à gcalendar
    $calendar = NULL;
    try {
      $user = auth()->user();
      $local = homey_get_localization();
      $account = $user->googleAccounts()->first($this->googleAccountFields);
      $event_info = QueryParametersHelper::buildAddOrUpdateIndisponibleEventInfo($calendarId, $this->request);
      /**
       * @var \App\Models\Calendar $calendar
       */
      if (!empty($account)) {
        $conditions = [
          ['ID', '=', $calendarId],
          ['listing_id', '=', $event_info['postId']]
        ];
        if ($calendarId != "null") {
          $calendar = $account->calendars()
                  ->where($conditions)
                  ->first();
        }
      }

      $reservation_booked_array = get_post_meta($event_info['postId'], 'reservation_booked_hours', true);
      if (empty($reservation_booked_array)) {
        $reservation_booked_array = homey_get_booked_hours($event_info['postId']);
      }
      //@TODO: Recherche chevauchement indisponibilités
      // Exclure celui que doit être à jour
      $cin_unix = $event_info['cin_unix'];
      $cin = $event_info['cin'];
      while ($cin_unix <= $event_info['cout_unix']) {
        if (array_key_exists($cin_unix, $reservation_booked_array) /* || array_key_exists($check_in_hour_unix, $reservation_pending_array) */) {
          $result = array(
            'status' => "error",
            'success' => false,
            'message' => $local['hours_already_booked']
          );

          return $this->helper->response(json_encode($result), 200, [
                    "Content-Type" => "application/json"
          ]);
        }
        $cin->modify('+30 minutes');
        $cin_unix = $event_info['cin']->getTimestamp();
      }
      $chevauchement = $this->chevauchement($event_info["postId"], $event_info["cin_unix"], $event_info["cout_unix"], $event_info["tz"], $event_info["uid"]);
      if ($chevauchement) {
        $result = [
          'status' => "error",
          "success" => false,
          "message" => $local['reject_add_unavailable_date']
        ];
        return new JsonResponse($result);
      }

      if (empty($calendar)) {
        $result = [
          "status" => "success",
          "message" => "Tout les événements sont synchronisés."
        ];
      }
      else {
        $event_info['calendar'] = $calendar;
        $srvCalendar = $calendar->getGoogleService("Calendar");

        $calendars = [
          'tz' => $event_info['tz'],
          'srvCalendar' => $srvCalendar,
          'local' => $local
        ];
        $event_info['calendar'] = $calendar;

        $result = $this->sendEvent($calendars, $event_info);

        $event_info['google_id'] = $result['id'] ?? NULL;
        $result = $this->updateOrCreateEvent($event_info);
      }

      $this->offlineEventRepo->updateOrCreateOfflineEvent($event_info['uid'], $event_info);

    } catch (\Exception $e) {
      Log::error('ERROR *** add indispo ***' . $e->getTraceAsString());
      $result = [
        "status" => "error",
        "message" => "Une erreur est survenue lors de la syncronisation"
      ];
    }
    return $this->helper->response(json_encode($result), 200, [
      "Content-Type" => "application/json"
    ]);
  }

  /**
   * @param $postId
   * @param $check_in_hour
   * @param $tz
   * @param $check_out_hour
   * @param string $hobbyMoodEvent
   * @param \Google_Service_Calendar $srvCalendar
   * @param $calendar
   * @param $uid
   * @param array $local
   * @return array
   */
  private function sendEvent($calendars, $event_info, $isRemoved = FALSE): array {
    $formatDate = 'Y-m-d\TH:i:s';
    try {
      $timezone = $event_info['timezone'] 
        ?? QueryParametersHelper::getTimeZoneName($calendars['tz']) 
        ?? get_option('timezone_string') 
        ?? 'Europe/Paris';
      $tz = new \DateTimeZone($timezone);
      // DELETE 
      if ($isRemoved) {
        $result_gcal = $calendars['srvCalendar']->events->delete($event_info['calendar']->google_id, $event_info['event_google_id']);
      }
      else {
        $gmtOffset = (int) get_option('gmt_offset');
        // DAtE RFC3339
        // UTC for start
        if (is_string($event_info['check_in_hour'])) {
          $startDateTime = (new \DateTime($event_info['check_in_hour']))
             ->format($formatDate);
        }
        else {
          $startDateTime = $event_info['check_in_hour']
             ->format($formatDate);
        }

        // UTC for end
        if (is_string($event_info['check_out_hour'])) {
          $endDateTime = (new \DateTime($event_info['check_out_hour']))
            ->format($formatDate);
        }
        else {
          $endDateTime = $event_info['check_out_hour']
            ->format($formatDate);
        }

        $event = Event::where('uid', $event_info['uid'])->first();
        $event_info['event_google_id'] = $event->google_id ?? NULL;
        // UPDATE
        if ($event_info['event_google_id']) {
          $event = $calendars['srvCalendar']->events->get(
                  $event_info['calendar']->google_id,
                  $event_info['event_google_id']
          );

          $start = $event->getStart();
          $end = $event->getEnd();
          $start->setDateTime($startDateTime);
          $end->setDateTime($endDateTime);
          $event->setStart($start);
          $event->setEnd($end);

          $result_gcal = $calendars['srvCalendar']->events->update(
                  $event_info['calendar']->google_id,
                  $event->getId(),
                  $event
          );
        }
        // CREATE WHEN MISSING
        else {
          /**
           * Send to google calendar
           */
          $eventBody = [
            "summary" => get_option($event_info['pattern_key'], FALSE) ?? $event_info['postId'] . " " . $event_info['hobbyMoodEvent'],
            "location" => get_post_meta($event_info['postId'], 'homey_listing_address', true),
            "description" => $event_info['description'],
            "start" => array(
              "dateTime" => $startDateTime,
              "timeZone" => $tz->getName(),
            ),
            "end" => array(
              "dateTime" => $endDateTime,
              "timeZone" => $tz->getName(),
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
                array(
                  'hobby_mood_event' => $event_info['hobbyMoodEvent'],
                  'uid' => $event_info['uid'],
                  'token' => $event_info['key'] ?? NULL
                )
              ),
            )
          ];
          /**
           * @var \Google_Service_Calendar $srvCalendar
           * Find the reccord from google agenda first.
           */
          $event = new \Google_Service_Calendar_Event($eventBody);
          $result_gcal = $calendars['srvCalendar']->events->insert($event_info['calendar']->google_id, $event);
        }
      }

      Log::info(" *** sending succes to gcal****: " . $result_gcal->id);
      $result = [
        "status" => "success",
        "message" => "Tout les événements sont synchronisés.",
        "id" => $result_gcal->id
      ];

      return $result;
    } catch (\Exception $e) {
      $result = [
        "status" => "error",
        "message" => $calendars['local']["unexpected_error"]
      ];
      Log::error("[error] --------->" . $e->getMessage());
    }
    return $result;
  }

  /**
   * @param $calendar
   * @param $uid
   * @param $check_in_hour
   * @param $check_out_hour
   * @return mixed
   */
  private function updateOrCreateEvent($event_info) {
    try {
      $event_info['calendar']->events()->updateOrCreate(
              [
                'uid' => $event_info['uid']
              ],
              [
                'name' => ucfirst($event_info['hobbyMoodEvent']),
                'started_at' => is_string($event_info['check_in_hour']) ? $event_info['check_in_hour'] : $event_info['check_in_hour']->format('Y-m-d H:i:s 000'),
                'ended_at' => is_string($event_info['check_out_hour']) ? $event_info['check_out_hour'] : $event_info['check_out_hour']->format('Y-m-d H:i:s 000'),
                'hobby_mood_event' => $event_info['hobbyMoodEvent'],
                'description' => $event_info['description'],
                'google_id' => $event_info['google_id'] ?? null
              ]
      );

      $result = [
        "status" => "success",
        "message" => "Tout les événements sont synchronisés."
      ];

      Log::info('Succes query *** add indisponible ***');
      return $result;
    } catch (\Exception $e) {
      Log::info('query create reservation error ------------------>' . $e->getTraceAsString());
    }
  }

  public function createReservation($reservationId) {
    /**
     * https://hobby-mood.ngrok.io/api/calendar/31/indisponibilites?postId=342&tz=180&start=2019-08-25%2000:00&end=2019-08-27%2000:00
     * @var \App\Models\WpUser $user
     * @var \App\Models\GoogleAccount $account
     */
    $tz = 0;
    $result = [
      "status" => "success",
      "message" => "La reservation est effectué.",
    ];

    try {
      $local = homey_get_localization();
      $event_info = QueryParametersHelper::buildcreateReservationEventInfo($reservationId, $this->request);

      /**
       * @var \App\Models\Calendar $calendar
       */
      $calendar = Calendar::where("listing_id", $event_info['postId'])->first();
      // No calendar implies no google account.
      if (!empty($calendar)) {
        $srvCalendar = $calendar->getGoogleService("Calendar");

        $calendars = [
          'tz' => $tz,
          'srvCalendar' => $srvCalendar,
          'local' => $local
        ];
        $event_info['calendar'] = $calendar;

        $result = $this->sendEvent($calendars, $event_info);
        $event_info['google_id'] = $result['id'] ?? NULL;
        $result = $this->updateOrCreateEvent($event_info);
      }

      $this->offlineEventRepo->updateOrCreateOfflineEvent($event_info['uid'], $event_info);

      return $this->helper->response(json_encode($result), 200, [
                "Content-Type" => "application/json"
      ]);
    } catch (\Exception $e) {
      Log::info('create reservation error ------------------>' . $e->getTraceAsString());
    }
  }

  public function trashIndisponibilite() {
    /**
     * @var \App\Models\Calendar $calendar
     */
    $calendar = NULL;
    $user = auth()->user();
    $local = homey_get_localization();
    try {
      $account = $user->googleAccounts()->first($this->googleAccountFields);
      $event_info = QueryParametersHelper::buildTrashIndisponibleEventInfo($this->request);
      if (!empty($account)) {
        $calendar_meta = get_post_meta($event_info["listing_id"], "homey_calendar_meta", TRUE);
        if ($calendar_meta && $calendar_meta != "" && is_array($calendar_meta)) {
          try {
            $calendar = Calendar::query()->where('id', $calendar_meta['id'])->first();
          } catch (ModelNotFoundException $exception) {
            $calendar = Calendar::query()->where('google_id', $calendar_meta["google_id"])->first();
          }
        }
      }

      if (empty($calendar)) {
        $result = [
          "status" => "success",
          "message" => "Tout les événements sont synchronisés."
        ];
      }
      else {
        $srvCalendar = $calendar->getGoogleService("Calendar");
        $event = $this->offlineEventRepo->findAllByParams(['uid' => $event_info['uid']])->firstOrFail(); //@TODO find the event
        $calendars = [
          'tz' => $event_info["tz"],
          'srvCalendar' => $srvCalendar,
          'local' => $local
        ];
        $event_info['calendar'] = $calendar;
        $event_info['event_google_id'] = $event->google_id;

        $result = $this->sendEvent($calendars, $event_info, TRUE);
        // obtenir le google_id depuis l'offline event au cas où il est déjà supprimé en ligne
        $id = isset($result['id']) ? $result["id"] : $event->google_id;
        $condition = ['google_id' => $id];
        $calendar->events()->where($condition)->delete();

        $result = [
          "status" => "success",
          "message" => "Tout les événements sont synchronisés."
        ];
      }

      $this->offlineEventRepo->deleteByParams(["uid" => $event_info["uid"]]);
    } catch (\Exception $e) {
      Log:info('DELETE [ERROR]' . print_r([$e->getMessage(), $event_info], TRUE));
      $result = [
        "status" => "error",
        "message" => "Erreur de suppression."
      ];
    }
    return $this->helper->response(json_encode($result), 200, [
              "Content-Type" => "application/json"
    ]);
  }

  /**
   * Cas 1 : $check_in_unix € [$debut:$fin[ OR $check_out_unix € [$debut:fin[
   * Cas 2 : $debut € [$check_in_unix:$check_out_unix[ OR $fin € [$check_in_unix:$check_out_unix[
   * @param $postId
   * @param int $debut 03:00
   * @param int $fin 04:00
   * @param int $tz
   *   $tz est utilisé pour parser la date des $offlineEvent
   *   au timezone utilisé pour fournir $debut et $fin
   * @param string $updateUid
   *   OfflineEvent Uid to update
   * @return bool
   *   Retourne vrai si Cas 1 = VRAI OR Cas 2 = VRAI
   * @throws \Exception
   */
  private function chevauchement($postId, $debut, $fin, $tz, $updateUid = "") {
    $chevauchement = FALSE;
    $list = OfflineEvent::query()
            ->where("listing_id", $postId)
            ->where('uid', '!=', $updateUid)
            ->get();
    /**
     * @var \App\Models\OfflineEvent $offlineEvent
     */
    $dateTimeZone = new \DateTimeZone(QueryParametersHelper::getTimeZoneName($tz));
    foreach ($list as $offlineEvent) {
      $checkInDate = $offlineEvent->started_at; // 02:00
      $checkOutDate = $offlineEvent->ended_at; //07:00
      $check_in = new \DateTime($checkInDate, $dateTimeZone);
      $check_in_unix = $check_in->getTimestamp();
      $check_out = new \DateTime($checkOutDate, $dateTimeZone);
      $check_out_unix = $check_out->getTimestamp();
      $cas1 = (( $check_in_unix >= $debut && $check_in_unix < $fin ) // $check_in_unix € [debut:fin[
              OR ( $check_out_unix >= $debut && $check_out_unix < $fin )); // $check_out_unix € [$debut:fin[
      $cas2 = (($debut >= $check_in_unix && $debut < $check_out_unix) // $debut € [$check_in_unix:$check_out_unix[
              OR ( $fin >= $check_out_unix && $fin < $check_out_unix)); // $fin € [$check_in_unix:$check_out_unix[
      if ($cas1 OR $cas2) {
        $chevauchement = TRUE;
        break;
      }
    }
    return $chevauchement;
  }

  public function postSave($post_id, $post, $update) {
//    $listing_timezone = $this->request->get("listing_timezone") ?? "UTC"; // set default as UTC
    $listing_timezone = "Europe/Paris";
    update_post_meta($post_id, "listing_timezone", $listing_timezone);
    if ($listing_calendar_id = $this->request->get('listing_calendar_id')) {
      if ($calendar = Calendar::query()->where("id", $listing_calendar_id)->first()) {
        $calendar_id = $calendar->id;
        $calendar_meta = [
          "id" => $calendar_id,
          "google_id" => $calendar->google_id
        ];
        $calendar->listing_id = $post_id;
        $calendar->save();
        $meta_key = "homey_calendar_meta";
        $meta_data = get_post_meta($post_id, $meta_key, TRUE);
        if (is_array($meta_data)) {
          update_post_meta($post_id, $meta_key, $calendar_meta);
        }
        else {
          add_post_meta($post_id, $meta_key, $calendar_meta);
        }

        $events_gc_missing_google_id = [];
        if ($all_events = $calendar->events()->get()) {
          foreach ($all_events as $event) {
            $events_gc_missing_google_id[] = $event->google_id;
            $event->uid = QueryParametersHelper::buildUid($event->listing_id, $event->token);
            $event->save();
          }
        }

        $srvCalendar = $calendar->getGoogleService("Calendar");

        $calendars = [
          'tz' => get_option('gmt_offset', 0),
          'srvCalendar' => $srvCalendar,
          'local' => homey_get_localization()
        ];

        if ($all_offline_events = $this->offlineEventRepo->findAllByListingIdOrCalendarId($post_id, $calendar_id)->get()) {
          foreach ($all_offline_events as $event) {
            $event->listing_id = $post_id;
            $event->calendar_id = $calendar_id;

            if (empty($event->google_id) || $event->google_id == '' || !in_array($event->google_id, $events_gc_missing_google_id)) {
              $event_info = QueryParametersHelper::buildParamsEventInfo($event);
              $event_info['calendar'] = $calendar;
              $result = $this->sendEvent($calendars, $event_info);
              $event_info['google_id'] = $result['id'] ?? NULL;
              $this->updateOrCreateEvent($event_info);
              $events_gc_missing_google_id[] = $event_info['google_id'];
            }
            else {
              // Rebuild token, beacuse listing_id change.
              $event->uid = QueryParametersHelper::buildUid($event->listing_id, $event->token);
              $event->save();
            }
          }
        }
      }
    }
  }

}
