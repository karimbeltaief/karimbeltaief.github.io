<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
/**
 * Handler event on calendar without google account.
 *
 * @author Oolna
 */
class OfflineEvent extends Model
{
  protected $fillable = [
    'uid', 
    'calendar_id',
    'listing_id', 
    'google_id', 
    'name',
    'description', 
    'allday', 
    'started_at',
    'ended_at',
    'hobby_mood_event',
    'token',
    'reservation_id',
    'timezone'
  ];
  
  private $mappingKeys = [
    'uid' => 'uid',    
    'calendar_id' => 'calendar_id',
    'postId' => 'listing_id',
    'google_id' => 'google_id',
    'summary' => 'name',
    'description' => 'description',
    'check_in_hour' => 'started_at',
    'check_out_hour' => 'ended_at',
    'hobbyMoodEvent' => 'hobby_mood_event',
    'key' => 'token',
    'timezone' => 'timezone',
    'reservationId' => 'reservation_id'
  ];
  
  /**
   * Convert keys
   * @param type $data
   */
  public function mappingToFillableKeys($data) {
    if(empty($data)) {
      throw new Exception('Invalid data');
    }
    
    if (isset($data['name'])) {
      $data['summary'] = $data['name'];
    }
    elseif (!isset($data['summary'])) {
      $data['summary'] = get_option($data['pattern_key'], FALSE) 
       ?? $data['postId'] . " " . $data['hobbyMoodEvent']
       ?? 'No title';
    }
    
    if (!isset($data['reservationId'])) {
      $data['reservationId'] = 0;
    }
    
    $keys = array_keys($data);
    
    foreach ($this->mappingKeys as $oldKey => $newKey) {
       if(!array_key_exists($oldKey,$data )) {
         continue;
       }
      
      $keys[array_search($oldKey,$keys)] = $newKey;
    }
    $data = array_combine($keys, $data);
    $data = array_intersect_key($data, array_combine($this->mappingKeys, $this->mappingKeys));

    // Check datetime
    $data['started_at'] = is_string($data['started_at']) ? $data['started_at'] : $data['started_at']->format('Y-m-d H:i:s 000');
    $data['ended_at']   = is_string($data['ended_at']) ? $data['ended_at'] : $data['ended_at']->format('Y-m-d H:i:s 000');
    return $data;     
  }
}
