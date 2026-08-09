<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'preference_request_id',
        'user_id',
        'preference_date',
        'preference_type',
        'shift_type',
        'available_from',
        'reason',
    ];

    public function preferenceRequest() {
        return $this->belongsTo(PreferenceRequest::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
