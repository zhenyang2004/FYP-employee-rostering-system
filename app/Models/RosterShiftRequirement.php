<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterShiftRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'roster_id',
        'roster_date',
        'shift_type',
        'shift_start_time',
        'shift_end_time',
        'required_staff',
        'assigned_staff',
        'status',
    ];

    public function roster() {
        return $this->belongsTo(Roster::class);
    }

    public function details() {
        return $this->hasMany(RosterDetail::class);
    }
}
