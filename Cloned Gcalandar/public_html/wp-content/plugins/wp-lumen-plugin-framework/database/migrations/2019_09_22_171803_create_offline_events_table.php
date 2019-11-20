<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfflineEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offline_events', function (Blueprint $table) {
           $table->increments('id');
            $table->string("uid");
            // Relationships.
            $table->unsignedInteger('calendar_id');
            $table->bigInteger('listing_id')->nullable();
            
            // Data.
            $table->string('google_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('allday')->default(false);
            $table->string('hobby_mood_event')->nullable();
            $table->text('token')->nullable();
            
            // Reservation id to skip duplicate reservation
            // And differencies reservation from google calendar
            $table->bigInteger('reservation_id')->nullable();
            
            // Timestamps.
            $table->datetime('started_at');
            $table->datetime('ended_at');
            $table->string('timezone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offline_events');
    }
}
