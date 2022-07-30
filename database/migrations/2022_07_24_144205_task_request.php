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
        Schema::create('task_request', function (Blueprint $table) {
            $table->id();
            $table->string('request_owner', 100);
            $table->integer('request_status');
            $table->text('request_detail');
            $table->text('request_expand');
            $table->timestamps();
            $table->unsignedBigInteger('task_id')->index()->comment('Foreign key with task_reward');
            $table->foreign('task_id')->references('id')->on('task_reward');
            $table->unique(['task_id', 'request_owner']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_request');
    }
};
