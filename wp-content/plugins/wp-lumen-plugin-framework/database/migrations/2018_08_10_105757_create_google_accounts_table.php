<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoogleAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_accounts', function (Blueprint $table) {
            $table->increments('id');

            // Relationships.
            $table->bigInteger('wp_user_ID')->unsigned();
            $table->foreign('wp_user_ID')
                  ->references('ID')->on('users')
                  ->onDelete('cascade');
            
            // Data.
            $table->string('google_id');
            $table->string('name');
            $table->text('token');

            // Timestamps.
            $table->timestamps();
        });
        
        /* Schema::table('google_accounts', function($table) {
            
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_accounts');
    }
}
