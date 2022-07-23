<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_bind', function (Blueprint $table) {
            $table->increments('id'); //
            $table->string('ato_address')->unique();
            $table->string('twitter_screen_name')->nullable()->unique();
            $table->string('twitter_profile_image_url')->nullable();
            $table->string('twitter_profile_image_url_https')->nullable();
            $table->string('oauth_token')->nullable();;
            $table->string('oauth_token_secret')->nullable();;
            $table->string('twitter_full_data')->nullable();;
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
        Schema::dropIfExists('twitter_bind');
    }
};
