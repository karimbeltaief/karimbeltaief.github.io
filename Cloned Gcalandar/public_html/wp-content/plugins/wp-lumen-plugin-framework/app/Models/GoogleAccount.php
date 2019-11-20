<?php

namespace App\Models;

use App\Models\Calendar;
use App\Concerns\Synchronizable;
use App\Jobs\SynchronizeGoogleCalendars;
use App\Jobs\WatchGoogleCalendars;
use App\Services\Google;
use App\Models\WpUser;
use Illuminate\Database\Eloquent\Model;


class GoogleAccount extends Model
{
    use Synchronizable;

	/** Model Settings **/
	protected $table = 'google_accounts';

    protected $fillable = [
        'google_id', 'name', 'token',
    ];

    /*protected $casts = [
        'token' => 'json',
    ];*/

    public function user()
    {
        return $this->belongsTo(WpUser::class, 'wp_user_ID');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }

    public function synchronize()
    {
        SynchronizeGoogleCalendars::dispatch($this);
    }

    public function watch()
    {
        WatchGoogleCalendars::dispatch($this);
    }
}
