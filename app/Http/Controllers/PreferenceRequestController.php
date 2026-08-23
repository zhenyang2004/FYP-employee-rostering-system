<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeePreference;
use App\Models\PreferenceRequest;
use App\Models\Roster;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PreferenceRequestController extends Controller
{

    public function isRosterGenerated($startDate, $endDate) {
        
        return Roster::whereDate('start_date', $startDate)->whereDate('end_date', $endDate)->exists();
    }
    public function index() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Preferences Request',
            'url' => route('preferencesrequest')
        ];

        $preferenceRequests = PreferenceRequest::where('user_id', auth()->id())->orderBy('start_date', 'asc')->get();

        foreach ($preferenceRequests as $preferenceRequest) {
            $preferenceRequest->roster_generated = $this->isRosterGenerated($preferenceRequest->start_date, $preferenceRequest->end_date);
        }

        return view('preferencesrequest', compact('breadcrumbs', 'preferenceRequests')); 

    }

    public function viewPreferences($id) {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Preferences Request',
            'url' => route('preferencesrequest')
        ];

        $breadcrumbs[] = [
            'text' => 'View Preferences',
            'url' => route('viewpreferencesrequest', $id)
        ];

        $preferenceRequest = PreferenceRequest::with(['preferences' => function ($query) {
            $query->orderBy('preference_date', 'asc');
        }])->where('user_id', auth()->id())->findOrFail($id); 

        return view('viewpreferencesrequest', compact('breadcrumbs', 'preferenceRequest')); 
    }

    public function store(Request $request) {

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',

            'preferences' => 'required|array',
            'preferences.*.preference_date' => 'required|date',
            'preferences.*.preference_type' => 'required|string|in:Preferred Shift,Any Shift,Unavailable',
            'preferences.*.shift_type' => 'nullable|string|in:Morning Shift,Afternoon Shift,Night Shift',
            'preferences.*.available_from' => 'nullable|date_format:H:i',
            'preferences.*.reason' => 'nullable|string|max:500',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $expectedEndDate = $startDate->copy()->addDays(6)->format('Y-m-d');

        if ($validated['end_date'] != $expectedEndDate) {
            return back()->withErrors(['end_date' => 'End date must be exactly 7 days from the start date.'])->withInput();
        }

        if ($this->isRosterGenerated($validated['start_date'], $validated['end_date'])) {
            return back()->withErrors(['preferences' => 'Preference request cannot be submitted. The roster for this week has already been generated.'])->withInput();
        }

        // Check for same date that already submitted
        $preferenceDates = collect($validated['preferences'])->pluck('preference_date')->toArray();
        $existingPreferences = EmployeePreference::where('user_id', auth()->id())->whereIn('preference_date', $preferenceDates)->pluck('preference_date')->toArray();

        if (!empty($existingPreferences)) {
            return back()->withErrors(['preferences' => 'You have already submitted preferences for the selected date.'])->withInput();
        }

        DB::beginTransaction();

        try {
            $preferenceRequest = PreferenceRequest::Create([
                'user_id' => auth()->id(),
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);
        

            foreach ($validated['preferences'] as $preference) {

                $preferenceDate = Carbon::parse($preference['preference_date']);

                if ($preferenceDate->lt($startDate) || $preferenceDate->gt($endDate)) {
                    DB::rollBack();
                    return back()->withErrors(['preferences' => 'Preference date is outside the selected date range.'])->withInput();
                }

                $preferenceType = $preference['preference_type'];

                $shiftType = null;
                $availableFrom = null;

                if ($preferenceType == 'Preferred Shift') {
                    if (empty($preference['shift_type'])) {
                        DB::rollBack();
                        return back()->withErrors(['preferences' => 'Please select a shift type for Preferred Shift.'])->withInput();
                    }

                    $shiftType = $preference['shift_type'];
                }

                if ($preferenceType == 'Any Shift') {

                    $availableFrom = $preference['available_from'] ?? null;
                }

                EmployeePreference::Create([
                    'preference_request_id' => $preferenceRequest->id,
                    'user_id' => auth()->id(),
                    'preference_date' => $preference['preference_date'],
                    'preference_type' => $preferenceType,
                    'shift_type' => $shiftType,
                    'available_from' => $availableFrom,
                    'reason' => $preference['reason'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('preferencesrequest')->with('success', 'Preferences submitted successfully.');

        } catch (\Exception $e) {

            DB::rollBack();
            return redirect()->route('preferencesrequest')->with('error', 'Failed to submit preferences.');
        }
    }

    public function editPreferences($id) {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Preferences Request',
            'url' => route('preferencesrequest')
        ];

        $breadcrumbs[] = [
            'text' => 'Edit Preferences',
            'url' => route('editpreferencesrequest', $id)
        ];

        $preferenceRequest = PreferenceRequest::with(['preferences' => function ($query) {
            $query->orderBy('preference_date', 'asc');
        }])->where('user_id', auth()->id())->findOrFail($id);

        if ($this->isRosterGenerated($preferenceRequest->start_date, $preferenceRequest->end_date)) {
            return redirect()->route('preferencesrequest')->withErrors(['preferences' => 'This preference request cannot be edited. The roster for this week has already been generated.']);
        }

        return view('editpreferencesrequest', compact('breadcrumbs', 'preferenceRequest')); 

    }

    public function updatePreferences(Request $request, $id) {

        $preferenceRequest = PreferenceRequest::where('user_id', auth()->id())->findOrFail($id);

        if ($this->isRosterGenerated($preferenceRequest->start_date, $preferenceRequest->end_date)) {
            return redirect()->route('preferencesrequest')->withErrors(['preferences' => 'This preference request cannot be edited. The roster for this week has already been generated.']);
        }

        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*.id' => 'required|exists:employee_preferences,id',
            'preferences.*.preference_date' => 'required|date',
            'preferences.*.preference_type' => 'required|string|in:Preferred Shift,Any Shift,Unavailable',
            'preferences.*.shift_type' => 'nullable|string|in:Morning Shift,Afternoon Shift,Night Shift',
            'preferences.*.available_from' => 'nullable|date_format:H:i',
            'preferences.*.reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['preferences'] as $preference) {

                $preferenceType = $preference['preference_type'];

                $shiftType = null;
                $availableFrom = null;

                if ($preferenceType == 'Preferred Shift') {
                    if (empty($preference['shift_type'])) {
                        DB::rollBack();

                        return back()->withErrors(['preferences' => 'Please select a shift type for Preferred Shift.'])->withInput();
                    }

                    $shiftType = $preference['shift_type'];
                }

                if ($preferenceType == 'Any Shift') {
                    $availableFrom = $preference['available_from'] ?? null;
                }

                if ($preferenceType == 'Unavailable') {
                    $shiftType = null;
                    $availableFrom = null;
                }

                EmployeePreference::where('id', $preference['id'])->where('user_id', auth()->id())->where('preference_request_id', $preferenceRequest->id)->update([
                    'preference_type' => $preferenceType,
                    'shift_type' => $shiftType,
                    'available_from' => $availableFrom,
                    'reason' => $preference['reason'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('preferencesrequest')->with('success', 'Preferences updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['preferences' => 'Something went wrong. Please try again.'])->withInput();
        }
    }
}
