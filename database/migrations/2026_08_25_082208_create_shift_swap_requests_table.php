<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftSwapRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_swap_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('target_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('roster_id')->constrained('rosters')->onDelete('cascade');
            $table->foreignId('requester_roster_detail_id')->constrained('roster_details')->onDelete('cascade');
            $table->foreignId('target_roster_detail_id')->constrained('roster_details')->onDelete('cascade');
            $table->text('reason')->nullable();
            $table->string('status')->default('Pending Staff Approval');
            $table->text('target_staff_remark')->nullable();
            $table->timestamp('target_responded_at')->nullable();
            $table->text('manager_remark')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
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
        Schema::dropIfExists('shift_swap_requests');
    }
}
