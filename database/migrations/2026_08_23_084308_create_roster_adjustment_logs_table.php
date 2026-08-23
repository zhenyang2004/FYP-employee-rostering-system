<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRosterAdjustmentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roster_adjustment_logs', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('roster_id');
            $table->unsignedBigInteger('roster_shift_requirement_id');
            $table->unsignedBigInteger('leave_request_id');
            $table->unsignedBigInteger('user_id');
            $table->date('roster_date');
            $table->string('shift_type');
            $table->string('reason')->default('Removed due to approved leave');
            $table->string('status')->default('Unresolved');
            $table->timestamps();
            
            $table->foreign('roster_id')->references('id')->on('rosters')->onDelete('cascade');
            $table->foreign('roster_shift_requirement_id')->references('id')->on('roster_shift_requirements')->onDelete('cascade');
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roster_adjustment_logs');
    }
}
