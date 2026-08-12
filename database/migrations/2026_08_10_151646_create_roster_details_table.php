<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRosterDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roster_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roster_id');
            $table->unsignedBigInteger('roster_shift_requirement_id');
            $table->unsignedBigInteger('user_id');
            $table->date('roster_date');
            $table->string('shift_type');
            $table->time('shift_start_time');
            $table->time('shift_end_time');
            $table->string('preference_type')->nullable();
            $table->string('preference_result')->default('No Preference');
            $table->timestamps();

            $table->foreign('roster_id')->references('id')->on('rosters')->onDelete('cascade');
            $table->foreign('roster_shift_requirement_id')->references('id')->on('roster_shift_requirements')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['roster_id', 'user_id', 'roster_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roster_details');
    }
}
