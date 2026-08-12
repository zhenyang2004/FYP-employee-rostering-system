<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRosterShiftRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roster_shift_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roster_id');
            $table->date('roster_date');

            $table->string('shift_type', 50);

            $table->time('shift_start_time');
            $table->time('shift_end_time');
            $table->integer('required_staff');
            $table->integer('assigned_staff')->default(0);
            $table->string('status', 50)->default('Filled');
            $table->timestamps();

            $table->foreign('roster_id')
                ->references('id')
                ->on('rosters')
                ->onDelete('cascade');

            $table->unique(['roster_id', 'roster_date', 'shift_type'], 'rsr_date_shift_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roster_shift_requirements');
    }
}
