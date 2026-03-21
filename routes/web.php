<?php

use Illuminate\Support\Facades\Route;
use App\Models\Room;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RoomTypeController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/rooms', [RoomsController::class, 'rooms'])->name('rooms');
Route::get('/profile', [RoomsController::class, 'profile'])->name('profile');




Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/staff/index', [StaffManagementController::class, 'index'])->name('admin_staff_index');
    Route::get('admin/staff/create', [StaffManagementController::class, 'create'])->name('admin_staff_create');
    Route::post('admin/staff/store', [StaffManagementController::class, 'store'])->name('admin_staff_store');
    Route::get('/edit_staff/{id}', [StaffManagementController::class, 'edit_staff'])->name('admin_staff_edit');
    Route::post('/update_staff/{id}', [StaffManagementController::class, 'update_staff'])->name('admin_staff_update');
    Route::delete('/delete_staff/{id}', [StaffManagementController::class, 'delete_staff'])->name('admin_staff_delete');

    Route::get('/manage_staff', [RoomsController::class, 'manage_staff'])->name('manage_staff');
});
Route::middleware(['auth', 'role:staff,admin'])->group(function () {
    Route::get('/manage_room', [RoomsController::class, 'manage_room'])->name('manage_room');
    Route::get('/create', [RoomsController::class, 'create'])->name('create');
    Route::post('/insert', [RoomsController::class, 'insert']);
    Route::get('/change/{id}', [RoomsController::class, 'change'])->name('change');
    Route::get('/edit/{id}', [RoomsController::class, 'edit'])->name('edit');
    Route::get('/delete/{id}', [RoomsController::class, 'delete'])->name('delete');
    Route::post('/update/{id}', [RoomsController::class, 'update'])->name('update');
    Route::get('/admin/bookings', [BookingController::class, 'manage'])->name('admin_booking_manage');
    Route::post('/admin/bookings/update/{id}', [BookingController::class, 'updateStatus'])->name('admin_booking_update');
    Route::post('/admin/bookings/update_usage/{id}', [BookingController::class, 'updateUsage'])->name('admin_booking_update_usage');
    Route::get('/booking/settings', [BookingController::class, 'settings'])->name('booking_settings');
    Route::post('/booking/settings', [BookingController::class, 'updateSettings'])->name('booking_settings_update');

    Route::get('/admin/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('admin_dashboard');
    Route::get('/admin/dashboard/export', [App\Http\Controllers\DashboardController::class, 'exportPdf'])->name('admin_dashboard_export');

    // Room Types
    Route::get('/admin/room-types', [RoomTypeController::class, 'index'])->name('room_types.index');
    Route::post('/admin/room-types', [RoomTypeController::class, 'store'])->name('room_types.store');
    Route::delete('/admin/room-types/{id}', [RoomTypeController::class, 'destroy'])->name('room_types.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/booking', [BookingController::class, 'index'])->name('booking');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking_store');
    Route::get('/booking/availability', [BookingController::class, 'availability'])->name('booking_availability');
    Route::get('/booking/history', [BookingController::class, 'history'])->name('booking_history');
    Route::get('/room-details/{id}', function ($id) {
        $room = \App\Models\Room::find($id);
        return response()->json($room);
    });
    Route::patch('/booking/cancel/{id}', [App\Http\Controllers\BookingController::class, 'cancel'])->name('booking_cancel');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
