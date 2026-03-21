<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Booking;
use App\Models\BookingSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingStatusMail;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $userRole = auth()->user()->role ?? 'user';
        $setting = BookingSetting::first();
        $maxDays = $setting && $setting->max_advance_days !== null ? (int) $setting->max_advance_days : 14;
        if ($maxDays < 0) {
            $maxDays = 0;
        }
        $maxDate = Carbon::today()->addDays($maxDays)->toDateString();
        $limitRule = ($userRole === 'admin' || $userRole === 'staff') ? null : 'before_or_equal:' . $maxDate;

        $bookingType = $request->input('booking_type', 'single');

        // กรณีจองแบบ Group (หลายวัน, หลาย Slot ได้)
        // User Request: Group = <=3 days, >1 slots
        if ($bookingType === 'group') {
            $validated = $request->validate([
                'room_id' => 'required',
                'booking_dates' => 'required|array|min:1|max:3', // Max 3 days
                'booking_dates.*' => array_filter(['nullable', 'date', 'after_or_equal:today', $limitRule]),
                'time_slots' => 'required|array|min:1',
                'time_slots.*' => ['required', 'regex:/^(slot[1-3]|slot_w_([1-9]|1[0-2]))$/'],
            ]);

            // Logic below is same as single: loop through dates and slots
        } else {
            // กรณีจองแบบ Single (วันเดียว, หลาย Slot ได้)
            $validated = $request->validate([
                'room_id' => 'required',
                // booking_dates array required, but max 1
                'booking_dates' => 'required|array|min:1|max:1',
                'booking_dates.*' => array_filter(['nullable', 'date', 'after_or_equal:today', $limitRule]),
                // time_slots ต้องเป็น array หรือ string (ถ้าส่งมาค่าเดียว)
                'time_slots' => 'required|array|min:1',
                'time_slots.*' => ['required', 'regex:/^(slot[1-3]|slot_w_([1-9]|1[0-2]))$/'],
            ]);
        }

        $room = Room::find($validated['room_id']);
        if (!$room || !$room->status) {
            return back()->withErrors(['room_id' => 'ห้องนี้ถูกปิดไม่ให้จอง'])->withInput();
        }

        $dates = array_unique(array_filter($validated['booking_dates']));
        $slots = $validated['time_slots'];
        $userId = auth()->user()->userid;

        // Validate slot-type matches date-type (weekend vs weekday)
        foreach ($dates as $date) {
            $dayOfWeek = Carbon::parse($date)->dayOfWeek; // 0=Sun, 6=Sat
            $isWeekend = in_array($dayOfWeek, [0, 6]);
            foreach ($slots as $slot) {
                $isWeekendSlot = str_starts_with($slot, 'slot_w_');
                if ($isWeekend && !$isWeekendSlot) {
                    return back()->withErrors(['time_slots' => 'วันเสาร์-อาทิตย์ต้องใช้ช่วงเวลาวันหยุดครับ'])->withInput();
                }
                if (!$isWeekend && $isWeekendSlot) {
                    return back()->withErrors(['time_slots' => 'วันธรรมดาต้องใช้ช่วงเวลาปกติครับ'])->withInput();
                }
            }
        }

        $conflicts = [];

        foreach ($dates as $date) {
            foreach ($slots as $slot) {
                [$startTime, $endTime] = $this->mapTimeSlotToRange($slot);

                $exists = Booking::where('room_id', $validated['room_id'])
                    ->where('booking_date', $date)
                    ->where('start_time', $startTime)
                    ->where('end_time', $endTime)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($exists) {
                    $conflicts[] = "$date ($slot)";
                }
            }
        }

        if (!empty($conflicts)) {
            return back()->withErrors(['time_slots' => 'รายการต่อไปนี้ไม่ว่าง: ' . implode(', ', $conflicts)])->withInput();
        }

        // Create bookings
        $bookingIdGroup = 'Book-ID: ' . strtoupper(\Illuminate\Support\Str::random(6));

        foreach ($dates as $date) {
            foreach ($slots as $slot) {
                [$startTime, $endTime] = $this->mapTimeSlotToRange($slot);
                Booking::create([
                    'booking_id' => $bookingIdGroup,
                    'user_id' => $userId,
                    'room_id' => $validated['room_id'],
                    'booking_date' => $date, // Use loop variable
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => 'pending',
                ]);
            }
        }

        return redirect()->route('booking_history')->with('success', 'จองห้องเรียบร้อยแล้ว');
    }

    public function availability(Request $request)
    {
        $validated = $request->validate([
            'dates' => 'required',
            'slots' => 'required',
        ]);

        $slots = is_array($validated['slots']) ? $validated['slots'] : [$validated['slots']];
        $dates = is_array($validated['dates']) ? $validated['dates'] : [$validated['dates']];

        // Validate slot format
        $validPattern = '/^(slot[1-3]|slot_w_[1-9]|slot_w_1[0-2])$/';
        $slots = array_filter($slots, fn($s) => preg_match($validPattern, $s));

        // หาห้องที่ไม่ว่าง (booked) ใน *อย่างน้อย 1 ช่วงเวลา* ที่เลือก
        $unavailableRoomIds = [];

        foreach ($dates as $date) {
            foreach ($slots as $slot) {
                [$startTime, $endTime] = $this->mapTimeSlotToRange($slot);

                $ids = Booking::where('booking_date', $date)
                    ->where('start_time', $startTime)
                    ->where('end_time', $endTime)
                    ->whereIn('status', ['pending', 'approved'])
                    ->pluck('room_id')
                    ->toArray();

                $unavailableRoomIds = array_merge($unavailableRoomIds, $ids);
            }
        }

        $closedRoomIds = Room::where('status', false)->pluck('id')->toArray();
        $unavailable = array_values(array_unique(array_merge($unavailableRoomIds, $closedRoomIds)));

        return response()->json([
            'bookedRoomIds' => $unavailable,
        ]);
    }

    public function index()
    {
        $rooms = Room::all();
        $today = Carbon::today()->toDateString();

        $setting = BookingSetting::first();
        $maxDays = $setting && $setting->max_advance_days !== null ? (int) $setting->max_advance_days : 14;
        $maxDays = max(0, $maxDays);
        $maxBookingDate = Carbon::today()->addDays($maxDays)->toDateString();

        // For initial load (today), check slots?
        // Usually index shows blank or default. We'll leave $bookedRoomIdsToday simple for now
        // or just empty, as the view relies on JS or specific inputs.

        return view('booking', [
            'rooms' => $rooms,
            'today' => $today,
            'maxAdvanceDays' => $maxDays,
            'maxBookingDate' => $maxBookingDate,
            // Pass array of slots from request
            'preSelectedRoomId' => request()->query('room_id'),
            'preSelectedDates' => request()->query('dates'),  // Changed from date to dates
            'preSelectedSlots' => request()->query('slots'), // Expecting array or multiple params
            'preSelectedType' => request()->query('booking_type', 'single'),
        ]);
    }

    public function settings()
    {
        $setting = BookingSetting::first();
        if (!$setting) {
            $setting = new BookingSetting(['max_advance_days' => 14]);
        }

        return view('booking_settings', compact('setting'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'max_advance_days' => 'required|integer|min:0|max:365',
        ]);

        $setting = BookingSetting::first();
        if (!$setting) {
            $setting = new BookingSetting();
        }
        $setting->max_advance_days = $validated['max_advance_days'];
        $setting->save();

        return back()->with('success', 'บันทึกจำนวนวันจองล่วงหน้าเรียบร้อยแล้ว');
    }

    public function history()
    {

        $bookings = Booking::where('user_id', auth()->user()->userid)
            ->with('room')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($booking) {
                return $booking->booking_id ?: $booking->id;
            });

        return view('history', compact('bookings'));
    }

    public function cancel($id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', auth()->user()->userid)
            ->firstOrFail();

        if ($booking->booking_id) {
            Booking::where('booking_id', $booking->booking_id)->update(['status' => 'canceled']);
        } else {
            $booking->update(['status' => 'canceled']);
        }

        return back()->with('success', 'ยกเลิกการจองเรียบร้อยแล้ว');
    }

    public function manage()
    {
        $bookings = Booking::with(['room', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($booking) {
                return $booking->booking_id ?: $booking->id;
            });
        return view('admin_manage_bookings', compact('bookings'));
    }

    public function updateStatus(Request $request, $id)
    {
        // 1. ค้นหาข้อมูลการจองพร้อมดึงข้อมูล User และ Room มาด้วย
        $booking = Booking::with(['user', 'room'])->findOrFail($id);

        // 2. อัปเดตสถานะในฐานข้อมูล (approved, rejected, canceled) สำหรับทุกรายการในกลุ่ม
        if ($booking->booking_id) {
            Booking::where('booking_id', $booking->booking_id)->update(['status' => $request->status]);
        } else {
            $booking->update(['status' => $request->status]);
        }

        // รีเฟรชสถานะใหม่ให้ชัวร์
        $booking->refresh();

        // 3. ส่งอีเมลแจ้งเตือนไปยังผู้จองทันที
        // ตรวจสอบว่าผู้จองมีอีเมลจริงก่อนส่ง เพื่อป้องกัน Error
        if ($booking->user && $booking->user->email) {
            $bookingsGroup = $booking->booking_id ? Booking::with('room')->where('booking_id', $booking->booking_id)->get() : collect([$booking]);
            Mail::to($booking->user->email)->send(new BookingStatusMail($bookingsGroup));
        }

        return back()->with('success', 'อัปเดตสถานะและส่งอีเมลแจ้งเตือนเรียบร้อยแล้ว');
    }

    /**
     * แปลง time_slot จากปุ่มให้เป็นช่วงเวลาเริ่ม-จบ
     */
    private function mapTimeSlotToRange(string $timeSlot): array
    {
        switch ($timeSlot) {
            case 'slot1':
                // 08:30-12:30
                return ['08:30:00', '12:30:00'];
            case 'slot2':
                // 13:30-17:30
                return ['13:30:00', '17:30:00'];
            case 'slot3':
                // 18:30-20:00
                return ['18:30:00', '20:00:00'];
            case 'slot_w_1': return ['08:20:00', '09:10:00'];
            case 'slot_w_2': return ['09:10:00', '10:00:00'];
            case 'slot_w_3': return ['10:00:00', '10:50:00'];
            case 'slot_w_4': return ['10:50:00', '11:40:00'];
            case 'slot_w_5': return ['11:40:00', '12:30:00'];
            case 'slot_w_6': return ['12:30:00', '13:20:00'];
            case 'slot_w_7': return ['13:20:00', '14:10:00'];
            case 'slot_w_8': return ['14:10:00', '15:00:00'];
            case 'slot_w_9': return ['15:00:00', '15:50:00'];
            case 'slot_w_10': return ['15:50:00', '16:40:00'];
            case 'slot_w_11': return ['16:40:00', '17:30:00'];
            case 'slot_w_12': return ['17:30:00', '18:20:00'];
            default:
                // กันกรณีผิดพลาด
                return ['00:00:00', '00:00:00'];
        }
    }
    public function updateUsage(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        if ($booking->booking_id) {
            Booking::where('booking_id', $booking->booking_id)->update(['is_used' => $request->is_used]);
        } else {
            $booking->update(['is_used' => $request->is_used]);
        }
        return back()->with('success', 'อัปเดตสถานะการเข้าใช้งานเรียบร้อยแล้ว');
    }
}
