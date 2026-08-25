<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmployeePreference;
use App\Models\Roster;
use App\Models\RosterShiftRequirement;
use App\Models\RosterDetail;
use App\Models\LeaveRequest;
use App\Models\RosterSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateRosterController extends Controller
{
    public function index() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' =>route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Generate Roster',
            'url' => route('generateroster')
        ];

        return view('generateroster', compact('breadcrumbs'));
    }

    public function preview(Request $request) {

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',

            'requirements' => 'required|array',
            'requirements.*.roster_date' => 'required|date',
            'requirements.*.morning_required' => 'required|integer|min:1',
            'requirements.*.afternoon_required' => 'required|integer|min:1',
            'requirements.*.night_required' => 'required|integer|min:1',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $expectedEndDate = $startDate->copy()->addDays(6)->format('Y-m-d');

        if ($validated['end_date'] != $expectedEndDate) {
            return back()->withErrors(['roster' => 'End date must be exactly 7 days from the start date.'])->withInput();
        }

        $existingRoster = Roster::where('start_date', $validated['start_date'])->where('end_date', $validated['end_date'])->exists();

        if ($existingRoster) {
            return back()->withErrors(['roster' => 'Roster for the selected period already exists.'])->withInput();
        }

        $employees = User::with('employee')->where('status', 'Active')->whereHas('employee', function ($q) {
            $q->whereIn('role', ['Staff', 'Manager']);
        })->orderBy('id', 'asc')->get();
        
        if ($employees->count() == 0) {
            return back()->withErrors(['roster' => 'No employees found.'])->withInput();
        }

        $settings = RosterSetting::getSettings();

        $maxWeeklyHours = $settings->max_weekly_hours;
        $shiftDurationHours = $settings->shift_duration_hours;

        $shifts = [
            [
                'key' => 'morning',
                'type' => 'Morning Shift',
                'start' => $settings->morning_start_time,
                'end' => $settings->morning_end_time,
                'duration_hours' => $shiftDurationHours
            ],
            [
                'key' => 'afternoon',
                'type' => 'Afternoon Shift',
                'start' => $settings->afternoon_start_time,
                'end' => $settings->afternoon_end_time,
                'duration_hours' => $shiftDurationHours
            ],
            [
                'key' => 'night',
                'type' => 'Night Shift',
                'start' => $settings->night_start_time,
                'end' => $settings->night_end_time,
                'duration_hours' => $shiftDurationHours
            ],
        ];

        $assignedCount = [];
        $assignedHours = [];

        foreach ($employees as $employee) {
            $assignedCount[$employee->id] = 0;
            $assignedHours[$employee->id] = 0;
        }

        $rosterPreview = [];

        foreach ($validated['requirements'] as $requirement) {

            $rosterDate = $requirement['roster_date'];

            $preferences = EmployeePreference::whereDate('preference_date', $rosterDate)->get()->keyBy('user_id');

            $leaveUserIds = LeaveRequest::where('status', 'Approved')->whereDate('start_date', '<=', $rosterDate)->whereDate('end_date', '>=', $rosterDate)->pluck('user_id')->toArray();
            $availableEmployees = $employees->reject(function ($employee) use ($leaveUserIds) {
                return in_array($employee->id, $leaveUserIds);
            });

            $assignedToday = [];

            $dailyShiftPreview = [];

            foreach ($shifts as $shift) {
                $requiredField = $shift['key'] . '_required';

                $dailyShiftPreview[$shift['key']] = [
                    'roster_date' => $rosterDate,
                    'day_name' => Carbon::parse($rosterDate)->format('l'),
                    'shift_type' => $shift['type'],
                    'shift_start_time' => $shift['start'],
                    'shift_end_time' => $shift['end'],
                    'required_staff' => (int) $requirement[$requiredField],
                    'assigned_staff' => 0,
                    'status' => 'Understaffed',
                    'assigned_employees' => [],
                ];
            }

            // Step 1: Assign employees to their preferred shifts
            foreach ($shifts as $shift) {
                $candidates = [];

                foreach ($availableEmployees as $employee) {
                    if (in_array($employee->id, $assignedToday)) {
                        continue;
                    }

                    if ($assignedHours[$employee->id] + $shift['duration_hours'] > $maxWeeklyHours) {
                        continue;
                    }

                    $preference = $preferences->get($employee->id);

                    if (!$preference) {
                        continue;
                    }

                    if ($preference->preference_type == 'Unavailable') {
                        continue;
                    }

                    if ($preference->preference_type == 'Preferred Shift' && $preference->shift_type == $shift['type']) {
                        $candidates[] = [
                            'user' => $employee,
                            'assigned_count' => $assignedCount[$employee->id],
                            'assigned_hours' => $assignedHours[$employee->id],
                            'preference_type' => 'Preferred Shift',
                            'preference_result' => 'Matched',
                        ];
                    }
                }

                $this->sortCandidatesByFairness($candidates);

                $this->assignCandidatesToShift($dailyShiftPreview[$shift['key']], $candidates, $assignedToday, $assignedCount, $assignedHours, $shift['duration_hours']);
            }

            // Step 2: Fill remaining slots with Any Shift employees
            foreach ($shifts as $shift) {
                $candidates = [];

                foreach ($availableEmployees as $employee) {
                    if (in_array($employee->id, $assignedToday)) {
                        continue;
                    }

                    if ($assignedHours[$employee->id] + $shift['duration_hours'] > $maxWeeklyHours) {
                        continue;
                    }

                    $preference = $preferences->get($employee->id);

                    if (!$preference) {
                        continue;
                    }

                    if ($preference->preference_type == 'Unavailable') {
                        continue;
                    }

                    if ($preference->preference_type == 'Any Shift') {
                        if ($preference->available_from) {
                            if (strtotime($shift['start']) < strtotime($preference->available_from)) {
                                continue;
                            }
                        }

                        $candidates[] = [
                            'user' => $employee,
                            'assigned_count' => $assignedCount[$employee->id],
                            'assigned_hours' => $assignedHours[$employee->id],
                            'preference_type' => 'Any Shift',
                            'preference_result' => 'Assigned',
                        ];
                    }
                }

                $this->sortCandidatesByFairness($candidates);

                $this->assignCandidatesToShift($dailyShiftPreview[$shift['key']], $candidates, $assignedToday, $assignedCount, $assignedHours, $shift['duration_hours']);
            }

            // Step 3: Fill remaining slots with employees who have no preference
            foreach ($shifts as $shift) {
                $candidates = [];

                foreach ($availableEmployees as $employee) {
                    if (in_array($employee->id, $assignedToday)) {
                        continue;
                    }

                    if ($assignedHours[$employee->id] + $shift['duration_hours'] > $maxWeeklyHours) {
                        continue;
                    }

                    $preference = $preferences->get($employee->id);

                    if ($preference) {
                        continue;
                    }

                    $candidates[] = [
                        'user' => $employee,
                        'assigned_count' => $assignedCount[$employee->id],
                        'assigned_hours' => $assignedHours[$employee->id],
                        'preference_type' => 'No Preference',
                        'preference_result' => 'No Preference',
                    ];
                }

                $this->sortCandidatesByFairness($candidates);

                $this->assignCandidatesToShift($dailyShiftPreview[$shift['key']], $candidates, $assignedToday, $assignedCount, $assignedHours, $shift['duration_hours']);
            }

            // Step 4: If still not enough, use employees whose preferred shift cannot be matched
            foreach ($shifts as $shift) {
                $candidates = [];

                foreach ($availableEmployees as $employee) {
                    if (in_array($employee->id, $assignedToday)) {
                        continue;
                    }

                    if ($assignedHours[$employee->id] + $shift['duration_hours'] > $maxWeeklyHours) {
                        continue;
                    }

                    $preference = $preferences->get($employee->id);

                    if (!$preference) {
                        continue;
                    }

                    if ($preference->preference_type == 'Unavailable') {
                        continue;
                    }

                    if ($preference->preference_type == 'Preferred Shift' && $preference->shift_type != $shift['type']) {
                        $candidates[] = [
                            'user' => $employee,
                            'assigned_count' => $assignedCount[$employee->id],
                            'assigned_hours' => $assignedHours[$employee->id],
                            'preference_type' => 'Preferred Shift',
                            'preference_result' => 'Not Matched',
                        ];
                    }
                }

                $this->sortCandidatesByFairness($candidates);

                $this->assignCandidatesToShift($dailyShiftPreview[$shift['key']], $candidates, $assignedToday, $assignedCount, $assignedHours, $shift['duration_hours']);
            }

            foreach ($dailyShiftPreview as $shiftPreview) {
                $shiftPreview['assigned_staff'] = count($shiftPreview['assigned_employees']);
                $shiftPreview['status'] = $shiftPreview['assigned_staff'] >= $shiftPreview['required_staff']
                    ? 'Filled'
                    : 'Understaffed';

                $rosterPreview[] = $shiftPreview;
            }
        }

        session([
            'roster_preview' => [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'requirements' => $validated['requirements'],
                'roster_preview' => $rosterPreview,
            ]
        ]);

        return redirect()->route('generateroster')->with('success', 'Roster preview generated successfully!');
    }

    private function assignCandidatesToShift(&$shiftPreview, $candidates, &$assignedToday, &$assignedCount, &$assignedHours, $shiftHours) {
        
        $remainingSlots = $shiftPreview['required_staff'] - count($shiftPreview['assigned_employees']);

        if ($remainingSlots <= 0) {
            return;
        }

        $selectedCandidates = array_slice($candidates, 0, $remainingSlots);

        foreach ($selectedCandidates as $selected) {
            $employee = $selected['user'];

            $shiftPreview['assigned_employees'][] = [
                'user_id' => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'preference_type' => $selected['preference_type'],
                'preference_result' => $selected['preference_result'],
            ];  

            $assignedToday[] = $employee->id;
            $assignedCount[$employee->id]++;
            $assignedHours[$employee->id] += $shiftHours;
        }
    }

    private function sortCandidatesByFairness(&$candidates) {

        foreach ($candidates as $index => $candidate) {
            $candidates[$index]['random_order'] = rand(1, 999999);
        }

        usort($candidates, function ($a, $b) {
            if ($a['assigned_hours'] == $b['assigned_hours']) {
                
                if ($a['assigned_count'] == $b['assigned_count']) {
                    return $a['random_order'] <=> $b['random_order'];
                }
                return $a['assigned_count'] <=> $b['assigned_count'];
            }

            return $a['assigned_hours'] <=> $b['assigned_hours'];
        });
    }

    public function save() {

        $preview = session('roster_preview');

        if (!$preview) {
            return redirect()
                ->route('generateroster')
                ->withErrors(['roster' => 'Please generate roster preview before saving.']);
        }

        $existingRoster = Roster::where('start_date', $preview['start_date'])
            ->where('end_date', $preview['end_date'])
            ->exists();

        if ($existingRoster) {
            return redirect()
                ->route('generateroster')
                ->withErrors(['roster' => 'Roster for this date range has already been saved.']);
        }

        DB::beginTransaction();

        try {
            $roster = Roster::create([
                'generated_by' => auth()->id(),
                'start_date' => $preview['start_date'],
                'end_date' => $preview['end_date'],
                'status' => 'Generated',
            ]);

            foreach ($preview['roster_preview'] as $shiftPreview) {
                $shiftRequirement = RosterShiftRequirement::create([
                    'roster_id' => $roster->id,
                    'roster_date' => $shiftPreview['roster_date'],
                    'shift_type' => $shiftPreview['shift_type'],
                    'shift_start_time' => $shiftPreview['shift_start_time'],
                    'shift_end_time' => $shiftPreview['shift_end_time'],
                    'required_staff' => $shiftPreview['required_staff'],
                    'assigned_staff' => $shiftPreview['assigned_staff'],
                    'status' => $shiftPreview['status'],
                ]);

                foreach ($shiftPreview['assigned_employees'] as $assignedEmployee) {
                    RosterDetail::create([
                        'roster_id' => $roster->id,
                        'roster_shift_requirement_id' => $shiftRequirement->id,
                        'user_id' => $assignedEmployee['user_id'],
                        'roster_date' => $shiftPreview['roster_date'],
                        'shift_type' => $shiftPreview['shift_type'],
                        'shift_start_time' => $shiftPreview['shift_start_time'],
                        'shift_end_time' => $shiftPreview['shift_end_time'],
                        'preference_type' => $assignedEmployee['preference_type'],
                        'preference_result' => $assignedEmployee['preference_result'],
                    ]);
                }
            }

            DB::commit();

            session()->forget('roster_preview');

            return redirect()
                ->route('generateroster')
                ->with('success', 'Roster saved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('generateroster')
                ->withErrors(['roster' => $e->getMessage()]);
        }
    }

    public function reset() {

        session()->forget('roster_preview');
        return redirect()->route('generateroster');
    }
}