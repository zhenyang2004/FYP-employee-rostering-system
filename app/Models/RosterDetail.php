<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'roster_id',
        'roster_shift_requirement_id',
        'user_id',
        'roster_date',
        'shift_type',
        'shift_start_time',
        'shift_end_time',
        'preference_type',
        'preference_result',
    ];

    public function roster() {
        return $this->belongsTo(Roster::class);
    }

    public function requirement() {
        return $this->belongsTo(RosterShiftRequirement::class, 'roster_shift_requirement_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
