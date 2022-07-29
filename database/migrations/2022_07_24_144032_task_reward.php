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
        Schema::create('task_reward', function (Blueprint $table) {
            $table->id();
            $table->string('task_kind', 50);
            $table->unsignedInteger('task_status', false);
            $table->string('task_title', 255);
            $table->text('task_detail');
            $table->bigInteger('task_prize');
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
        Schema::dropIfExists('task_reward');
    }
};
