<?php

namespace App\Utilities;

use App\Jobs\SynchronizeGoogleEvents;

/**
 * This encapsulate all mamnipuation of query parameters. 
 */
class QueryParametersHelper {

  public static function buildAddOrUpdateIndisponibleEventInfo($calendarId, $request) {
    $descriptions = [];
    $postId = $request->get("postId");
    $start = $request->get("start");
    $end = $request->get("end");
    $tz = $request->get("tz", NULL) ? ((int) $request->get("tz")) * 60 : NULL;
    if (empty($tz)) {
      $gmt_offset = get_option('gmt_offset', NULL);
      $tz = $gmt_offset ? ((int) $gmt_offset - 1) * 3600 : 3600; // UTC+2 <=> 'Europe/Paris'
    }
    $formToken = $request->get("token");
    $descriptions[] = "Post id: $postId" . PHP_EOL;
    $hobbyMoodEvent = SynchronizeGoogleEvents::INDISPONIBLE_KEY;
    $timezone_string = $tz ? self::getTimeZoneName($tz) : 'Europe/Paris';
    $timezone = new \DateTimeZone($timezone_string);
    $sourceFormat = "Y-m-d H:i";
    $check_in_hour = \DateTime::createFromFormat($sourceFormat, $start, $timezone);
    $cin = \DateTime::createFromFormat($sourceFormat, $start, $timezone);
    $cin_unix = $cin->getTimestamp();
    $check_out_hour = \DateTime::createFromFormat($sourceFormat, $end, $timezone);
    $cout = \DateTime::createFromFormat($sourceFormat, $end, $timezone);
//    $cout->modify('-30 minutes');//Bug
    $cout_unix = $cout->getTimestamp();
    $token = ($formToken && $formToken != "") ? $formToken : self::uniqidReal();
    $uid = self::buildUid($postId, $token);

    $event_info = [
      'pattern_key' => 'oo_gsa_pattern_indisponible',
      'postId' => $postId,
      'check_in_hour' => $check_in_hour,
      'check_out_hour' => $check_out_hour,
      'hobbyMoodEvent' => $hobbyMoodEvent,
      'uid' => $uid,
      'key' => $token,
      'cin_unix' => $cin_unix,
      'cin' => $cin,
      'cout_unix' => $cout_unix,
      'tz' => $tz,
      'timezone' => $timezone_string,
      'description' => implode(PHP_EOL, $descriptions)
    ];

    return $event_info;
  }

  public static function buildcreateReservationEventInfo($reservationId, $request) {
    $descriptions = [];
    $listingId = (int) get_post_meta($reservationId, 'reservation_listing_id', true);
    $reservationMeta = get_post_meta($reservationId, 'reservation_meta', true);

    // Main reservation on Paris ???
    $timezone = new \DateTimeZone('Europe/Paris');
    $sourceFormat = "Y-m-d H:i";
    $check_in_hour = \DateTime::createFromFormat($sourceFormat, $reservationMeta['check_in_hour'], $timezone);
    $check_out_hour = \DateTime::createFromFormat($sourceFormat, $reservationMeta['check_out_hour'], $timezone);

    $descriptions[] = 'guests: ' . $reservationMeta['guests'];
    $descriptions[] = 'additional_guests: ' . $reservationMeta['additional_guests'];

    $listing_renter = get_post_meta($reservationId, 'listing_renter', true);
    $renter = homey_usermeta($listing_renter);
    $descriptions[] = 'renter email: ' . $renter['email'];
    $descriptions[] = 'renter username: ' . $renter['username'];

    $hobbyMoodEvent = SynchronizeGoogleEvents::RESERVATION_KEY;
    $token = self::uniqidReal();
    $uid = self::buildUid($listingId, $token);

    $event_info = [
      'pattern_key' => 'oo_gsa_pattern_reservation',
      'postId' => $listingId,
      'timezone' => $timezone->getName(),
      'check_in_hour' => $check_in_hour,
      'check_out_hour' => $check_out_hour,
      'hobbyMoodEvent' => $hobbyMoodEvent,
      'uid' => $uid,
      'key' => $token,
      'reservationId' => $reservationId,
      'description' => implode(PHP_EOL, $descriptions)
    ];

    return $event_info;
  }

  public static function buildTrashIndisponibleEventInfo($request) {
    $listingId = (int) $request->get("edit_listing");
    $token = $request->get("token");
    $timezone = new \DateTimeZone('Europe/Paris');
    $uid = self::buildUid($listingId, $token);
    return [
      'uid' => $uid,
      'pattern_key' => 'oo_gsa_pattern_indisponible',
      'listing_id' => $listingId,
      'postId' => $listingId,
      'tz' => $timezone->getName()
    ];
  }

  public static function buildUid($listing_id, $token) {
    // before: md5 => hash based on date
    return base64_encode(serialize([
      'token' => $token,
      'listing_id' => (int) $listing_id
    ]));
  }

  public static function buildParamsEventInfo($offlineModel) {
    return [
      'pattern_key' => 'oo_gsa_pattern_' . $offlineModel->hobby_mood_event,
      'postId' => $offlineModel->listing_id,
      'timezone' => $offlineModel->timezone ?? get_option('timezone_string', 'Europe/Paris'),
      'check_in_hour' => $offlineModel->started_at,
      'check_out_hour' => $offlineModel->ended_at,
      'hobbyMoodEvent' => $offlineModel->hobby_mood_event,
      'uid' => $offlineModel->uid,
      'key' => $offlineModel->token,
      'reservationId' => $offlineModel->reservation_id,
      'description' => $offlineModel->descriptions
    ];
  }

  /**
   * @param string $tz
   *   Unit of variable: second
   * @return false|string
   */
  public static function getTimeZoneName($tz) {
    $timezone_name = timezone_name_from_abbr(NULL, $tz -3600, date('I')); // @todo correct TZ
    return $timezone_name;
  }

  /**
   * To skip duplicate uniq id
   * 
   * @param int $lenght
   * @return string
   * @throws Exception
   * @see https://www.php.net/manual/fr/function.uniqid.php.
   */
  public static function uniqidReal($lenght = 13) {
    if (function_exists("random_bytes")) {
      $bytes = random_bytes(ceil($lenght / 2));
    }
    elseif (function_exists("openssl_random_pseudo_bytes")) {
      $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    }
    else {
      throw new Exception("no cryptographically secure random function available");
    }
    return urlencode(substr(bin2hex($bytes), 0, $lenght));
  }

  /**
   *  Takes a GMT offset (in hours) and returns a timezone name 
   */
  public static function tz_offset_to_name($offset) {
    $offset *= 3600; // convert hour offset to seconds
    $abbrarray = timezone_abbreviations_list();
    foreach ($abbrarray as $abbr) {
      foreach ($abbr as $city) {
        if ($city['offset'] == $offset) {
          return $city['timezone_id'];
        }
      }
    }

    return FALSE;
  }

}
