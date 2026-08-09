<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreferenceRequestIdToEmployeePreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_preferences', function (Blueprint $table) {
            $table->unsignedBigInteger('preference_request_id')->nullable()->after('user_id');

            $table->foreign('preference_request_id')->references('id')->on('preference_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_preferences', function (Blueprint $table) {
            $table->dropForeign(['preference_request_id']);
            $table->dropColumn('preference_request_id');
        });
    }
}
