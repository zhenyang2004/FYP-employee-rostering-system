<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'entitlement_days',
        'status',
    ];

    public function leaveRequests() {
        return $this->hasMany(LeaveRequest::class);
    }
}
