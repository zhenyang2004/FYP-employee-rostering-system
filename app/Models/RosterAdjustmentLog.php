<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterAdjustmentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'roster_id',
        'roster_shift_requirement_id',
        'leave_request_id',
        'user_id',
        'roster_date',
        'shift_type',
        'reason',
        'status',
    ];
    
    public function user() {
        return $this->belongsTo(User::class);

    }

    public function roster() {
        return $this->belongsTo(Roster::class);

    }

    public function leaveRequest() {
        return $this->belongsTo(LeaveRequest::class);

    }

    public function shiftRequirement() {
        return $this->belongsTo(RosterShiftRequirement::class, 'roster_shift_requirement_id');
        
    }
}
