<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSwapRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_user_id',
        'target_user_id',
        'roster_id',
        'requester_roster_detail_id',
        'target_roster_detail_id',
        'reason',
        'status',
        'target_staff_remark',
        'target_responded_at',
        'manager_remark',
        'reviewed_by',
        'reviewed_at',
    ];

    public function requester() {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function targetUser() {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function roster() {
        return $this->belongsTo(Roster::class);
    }

    public function requesterRosterDetail() {
        return $this->belongsTo(RosterDetail::class, 'requester_roster_detail_id');
    }

    public function targetRosterDetail() {
        return $this->belongsTo(RosterDetail::class, 'target_roster_detail_id');
    }

    public function reviewer() {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    
}
