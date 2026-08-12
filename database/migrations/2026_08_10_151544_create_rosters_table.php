<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRostersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rosters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('Generated');
            $table->timestamps();
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rosters');
    }
}
