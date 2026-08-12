<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    use HasFactory;

    protected $fillable = [
        'generated_by',
        'start_date',
        'end_date',
        'status',
    ];

    public function requirements() {
        return $this->hasMany(RosterShiftRequirement::class);
    }

    public function details() {
        return $this->hasMany(RosterDetail::class);
    }

    public function generatedBy() {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
