<?php

namespace App\Jobs;

use App\Jobs\SynchronizeGoogleResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SynchronizeGoogleCalendars extends SynchronizeGoogleResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function getGoogleRequest($service, $options)
    {
        return $service->calendarList->listCalendarList($options);
    }

    /**
     * @param \Google_Service_Calendar_Calendar $googleCalendar
     * @return mixed
     */
    public function syncItem($googleCalendar)
    {
        /**
         * @var \App\Helpers\LumenHelper $lumenHelper
         */
        $lumenHelper = app()->make("lumenHelper");
        
        $listing_id = $lumenHelper->request()->cookies->get("postId");
        $meta_key = "homey_calendar_meta";
        $calendar_meta = get_post_meta($listing_id, $meta_key, TRUE);
        $meta_id = NULL;      
        if($calendar_meta != "" && array($calendar_meta)){
            if($calendar_meta["google_id"] == $googleCalendar->id){
                $meta_id = $calendar_meta["id"];
            }
        }
        
        if ($googleCalendar->deleted) {
            return $this->synchronizable->calendars()
                ->where('google_id', $googleCalendar->id)
                ->get()->each->delete();
        }
        
        
        // Verify attached listing
        if(empty($meta_id)) {
          $gcalendar = $this->synchronizable->calendars()
            ->where('google_id', $googleCalendar->id)
            ->first();
           $meta_id = $gcalendar ? $gcalendar->listing_id : NULL;
        }
        
        $this->synchronizable->calendars()->updateOrCreate(
            [
                'google_id' => $googleCalendar->id,
            ],
            [
                'name' => $googleCalendar->summary,
                'color' => $googleCalendar->backgroundColor,
                'timezone' => $googleCalendar->timeZone,
                'listing_id' => $meta_id
            ]
        );
    }

    public function dropAllSyncedItems()    
    {
        // Here we use `each->delete()` to make sure model listeners are called.
        $this->synchronizable->calendars->each->delete();   
    }
}
