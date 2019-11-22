<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories;

use App\Models\OfflineEvent;

/**
 * Description of OfflineEventRepository
 *
 * @author Oolna
 */
class OfflineEventRepository extends Repository
{

  /**
   *
   * @var App\Models\OfflineEvent
   */
  protected $model;
  
  /**
   * Restrict columns for select.
   * @var type 
   */
  private $visible_columns = [
    "name",
    "description",
    "hobby_mood_event",
    "token",
    "started_at",
    "ended_at"
  ];

  // Constructor to bind model to repo
  public function __construct(OfflineEvent $model) 
  {
    $this->model = $model;
  }

  public function findAllByListingId($listing_id) 
  {
    return $this->model->where(['listing_id' => $listing_id]);
  }
  
  public function findAllByListingIdOrCalendarId($listing_id, $calendar_id) 
  {
    return $this->model
            ->where(['listing_id' => $listing_id])
            ->orWhere(['calendar_id' => $calendar_id]);
  }

  public function updateOrCreateOfflineEvent($uid, $data) 
  {
    return $this->model->updateOrCreate(
        ['uid' => $uid],
        $this->model->mappingToFillableKeys($data)
    );
  }

  public function getIndisponible($id) 
  {
    return $this->model->show($id);
  }
  
  public function getIndisponibles($conditions) {
    return $this->model->where($conditions)->get($this->visible_columns)->all();
  }

  public function findAllByParams($params) 
  {
    return $this->model->where($params);
  }

  public function deleteByParams($params)
  {
    // Keep reservation from hobby. I.e, reservation_id =/= 0
    // Can delete reservation from google calendar
    if (!isset($params['reservation_id'])) {
      $params['reservation_id'] = 0;
    }
    return $this->model->where($params)->delete();
  }
}
