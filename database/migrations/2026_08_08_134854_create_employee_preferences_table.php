<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeePreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_preferences', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('user_id');

            $table->date('preference_date');
            $table->string('preference_type');
            $table->string('shift_type')->nullable();
            $table->time('available_from')->nullable();
            $table->text('reason')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['user_id', 'preference_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_preferences');
    }
}
