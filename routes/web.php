<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\EmployeeListController;
use App\Http\Controllers\GenerateRosterController;
use App\Http\Controllers\PreferenceRequestController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\EmployeeDetailController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ManageRequestController;
use App\Http\Controllers\ViewRosterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*Welcome page route*/
Route::get('/', function () {return view('welcome'); });

/*User login route*/
Route::get('/userlogin', function() {return view('userlogin'); })->name('login');
Route::post('/userlogin', [LoginController::class, 'login'])->name('user.login');

/*User Register route*/
Route::get('/usersignup', function() {return view('usersignup'); });
Route::post('/usersignup', [RegisterController::class, 'register'])->name('user.register');

/*Dashboard route*/
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

/*User logout route*/
Route::post('/logout', [LoginController::class, 'logout'])->name('user.logout');

/*Employee details route*/  
Route::get('/employeedetails', [EmployeeDetailController::class, 'index'])->middleware('auth')->name('employeedetails');
Route::get('/viewemployeedetails/{id}', [EmployeeDetailController::class, 'viewEmployeeDetail'])->middleware('auth')->name('viewemployeedetails');
Route::get('/viewpreferencesrequesthistory/{id}', [EmployeeDetailController::class, 'viewPreferencesHistory'])->middleware('auth')->name('viewpreferencesrequesthistory');

/*User profile route*/
Route::get('/userprofile', [ProfileController::class, 'index'])->middleware('auth')->name('userprofile');
Route::get('/editprofile', [ProfileController::class, 'editProfile'])->middleware('auth')->name('editprofile');
Route::post('/userprofile/update', [ProfileController::class, 'updateProfile'])->middleware('auth')->name('userprofile.update');

/*Forgot password route*/
Route::get('/forgotpassword', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('forgotpassword');
Route::post('/forgotpassword', [ForgotPasswordController::class, 'sendResetPasswordLink'])->name('password.email');

/*Reset password route*/
Route::get('/resetpassword/{token}', [ResetPasswordController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/resetpassword', [ResetPasswordController::class, 'resetPassword'])->name('password.update');

/*Protected after logout*/
Route::middleware(['auth', 'prevent-back-history'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/userprofile', [ProfileController::class, 'index'])->name('userprofile');
    Route::get('/editprofile', [ProfileController::class, 'editProfile'])->name('editprofile');
    Route::post('/userprofile/update', [ProfileController::class, 'updateProfile'])->name('userprofile.update');
    Route::post('/logout', [LoginController::class, 'logout'])->name('user.logout');
});

/*Employee list route*/
Route::get('/employeelist', [EmployeeListController::class, 'index'])->middleware('auth')->name('employeelist');
Route::get('/editemployee/{id}', [EmployeeListController::class, 'editEmployee'])->middleware('auth')->name('editemployee');
Route::post('/employee/{id}', [EmployeeListController::class, 'updateEmployeeRole'])->middleware('auth')->name('employee.updateRole');

/*Generate roster route*/
Route::get('/generateroster', [GenerateRosterController::class, 'index'])->middleware('auth')->name('generateroster');
Route::post('/generateroster/preview', [GenerateRosterController::class, 'preview'])->middleware('auth')->name('generateroster.preview');
Route::post('/generateroster/save', [GenerateRosterController::class, 'save'])->middleware('auth')->name('generateroster.save');
Route::post('/generateroster/reset', [GenerateRosterController::class, 'reset'])->middleware('auth')->name('generateroster.reset');

/*Preferences request route*/
Route::get('/preferencesrequest', [PreferenceRequestController::class, 'index'])->middleware('auth')->name('preferencesrequest');
Route::post('/preferencesrequest', [PreferenceRequestController::class, 'store'])->middleware('auth')->name('preferencesrequest.store');
Route::get('/viewpreferencesrequest/{id}', [PreferenceRequestController::class, 'viewPreferences'])->middleware('auth')->name('viewpreferencesrequest');
Route::get('/editpreferencesrequest/{id}', [PreferenceRequestController::class, 'editPreferences'])->middleware('auth')->name('editpreferencesrequest');
Route::post('/editpreferencesrequest/{id}', [PreferenceRequestController::class, 'updatePreferences'])->middleware('auth')->name('editpreferencesrequest.update');

/*Leave Request route*/
Route::get('/leaverequest', [LeaveRequestController::class, 'index'])->middleware('auth')->name('leaverequest');
Route::post('/leaverequest/store', [LeaveRequestController::class, 'storeLeaveRequest'])->middleware('auth')->name('leaverequest.store'); 


/*Setting route*/
Route::get('/setting', [SettingController::class, 'index'])->middleware('auth')->name('setting');
Route::post('/setting', [SettingController::class, 'saveLeaveType'])->middleware('auth')->name('setting.leavetype.store');
Route::post('/setting/{id}', [SettingController::class, 'destroyLeaveType'])->middleware('auth')->name('setting.leavetype.destroy');
Route::get('editleavetype/{id}', [SettingController::class, 'editLeaveType'])->middleware('auth')->name('editleavetype');
Route::post('/editleavetype/{id}', [SettingController::class, 'updateLeaveType'])->middleware('auth')->name('setting.leavetype.update');

/*Manage request route*/
Route::get('/managerequest', [ManageRequestController::class, 'index'])->middleware('auth')->name('managerequest');
Route::post('/managerequest/{id}', [ManageRequestController::class, 'updateLeaveRequest'])->middleware('auth')->name('managerequest.leave.status');

/*View roster route*/
Route::get('/viewroster', [ViewRosterController::class, 'index'])->middleware('auth')->name('viewroster');