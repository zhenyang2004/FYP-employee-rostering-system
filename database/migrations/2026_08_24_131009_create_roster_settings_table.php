<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRosterSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roster_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('max_weekly_hours')->default(40);
            $table->integer('shift_duration_hours')->default(8);

            $table->time('morning_start_time')->default('08:00:00');
            $table->time('morning_end_time')->default('16:00:00');

            $table->time('afternoon_start_time')->default('14:00:00');
            $table->time('afternoon_end_time')->default('22:00:00');

            $table->time('night_start_time')->default('22:00:00');
            $table->time('night_end_time')->default('06:00:00');
            $table->timestamps();
        });

        DB::table('roster_settings')->insert([
            'max_weekly_hours' => 40,
            'shift_duration_hours' => 8,
            'morning_start_time' => '08:00:00',
            'morning_end_time' => '16:00:00',
            'afternoon_start_time' => '14:00:00',
            'afternoon_end_time' => '22:00:00',
            'night_start_time' => '22:00:00',
            'night_end_time' => '06:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roster_settings');
    }
}
