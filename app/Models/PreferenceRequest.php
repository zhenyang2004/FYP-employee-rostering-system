<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreferenceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
    ];

    public function preferences()
    {
        return $this->hasMany(EmployeePreference::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
