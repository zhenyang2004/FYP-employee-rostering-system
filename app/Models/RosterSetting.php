<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'max_weekly_hours',
        'shift_duration_hours',
        'morning_start_time',
        'morning_end_time',
        'afternoon_start_time',
        'afternoon_end_time',
        'night_start_time',
        'night_end_time',
    ];

    public static function getSettings() {
        $settings = self::first();

        if (!$settings) {
            $settings = self::create([
                'max_weekly_hours' => 40,
                'shift_duration_hours' => 8,
                'morning_start_time' => '08:00:00',
                'morning_end_time' => '16:00:00',
                'afternoon_start_time' => '14:00:00',
                'afternoon_end_time' => '22:00:00',
                'night_start_time' => '22:00:00',
                'night_end_time' => '06:00:00',
            ]);
        }

        return $settings;
    }
    
}
