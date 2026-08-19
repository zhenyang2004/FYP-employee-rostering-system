<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roster;
use App\Models\RosterDetail;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ViewRosterController extends Controller
{
    public function index(Request $request) {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'View Roster',
            'url' => route('viewroster')
        ];

        $rosters = Roster::orderBy('start_date', 'desc')->get();

        $selectedRoster = null;
        $dates = collect();
        $groupedRoster = [];

        if ($rosters->count() > 0) {

            $selectedRosterId = $request->roster_id ?? $rosters->first()->id;
            $selectedRoster = Roster::find($selectedRosterId);


            /* Generate date range */
            if ($selectedRoster) {

                $period = CarbonPeriod::create($selectedRoster->start_date, $selectedRoster->end_date);
            
                foreach ($period as $date) {

                    $dates->push($date->copy());
                }

                $rosterDetails = RosterDetail::with('user')->where('roster_id', $selectedRoster->id)->orderBy('roster_date', 'asc')->orderBy('shift_type', 'asc')->get();

                foreach ($rosterDetails as $detail) {
                    $dataKey = Carbon::parse($detail->roster_date)->format('Y-m-d');
                    $groupedRoster[$dataKey][$detail->shift_type][] = $detail;
                }
            }
        }

        $shiftTypes = [
            'Morning Shift' => [
                'label' => 'Morning',
                'time' => '08:00 - 16:00'
            ],
            'Afternoon Shift' => [
                'label' => 'Afternoon',
                'time' => '14:00 - 22:00'
            ],
            'Night Shift' => [
                'label' => 'Night',
                'time' => '22:00 - 06:00'
            ],
        ];

        return view('viewroster', compact('breadcrumbs', 'rosters', 'selectedRoster', 'dates', 'groupedRoster', 'shiftTypes'));
    }
}
