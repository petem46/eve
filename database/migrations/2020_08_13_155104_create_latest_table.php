<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLatestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('latest', function (Blueprint $table) {
            $table->id();
            $table->string('alliance_id');
            $table->string('solar_system_id');
            $table->string('structure_id');
            $table->string('structure_type_id');
            $table->string('vulnerability_occupancy_level')->nullable();
            $table->string('vulnerable_end_time')->nullable();
            $table->string('vulnerable_start_time')->nullable();
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
        Schema::dropIfExists('latest');
    }
}
