<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Overview
        $totalItems = Booking::count();
        $approvedCount = Booking::where('status', 'approved')->count();
        $pendingCount = Booking::where('status', 'pending')->count();
        $rejectedCount = Booking::where('status', 'rejected')->count();
        $canceledCount = Booking::where('status', 'canceled')->count();

        // 2. Room Usage Report (Bookings per room)
        $roomUsages = DB::table('bookings')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->select('rooms.name', DB::raw('count(*) as total'))
            ->where('bookings.status', 'approved')
            ->groupBy('rooms.id', 'rooms.name')
            ->orderBy('total', 'desc')
            ->get();

        // 3. User usage report (Who booked, who used/didn't use the room based on is_used)
        $userUsages = DB::table('bookings')
            ->join('users', 'bookings.user_id', '=', 'users.userid')
            ->select(
                'users.name',
                'users.userid',
                DB::raw('count(*) as total_approved'),
                DB::raw('sum(case when is_used = 1 then 1 else 0 end) as total_used'),
                DB::raw('sum(case when is_used = 0 then 1 else 0 end) as total_missed')
            )
            ->where('bookings.status', 'approved')
            ->groupBy('users.userid', 'users.name')
            ->orderBy('total_approved', 'desc')
            ->get();

        return view('admin_dashboard', compact(
            'totalItems', 'approvedCount', 'pendingCount', 'rejectedCount', 'canceledCount',
            'roomUsages', 'userUsages'
        ));
    }

    public function exportPdf(Request $request)
    {
        $type = $request->query('type', 'all'); // 'all', 'room', 'user'

        $totalItems = Booking::count();
        $approvedCount = Booking::where('status', 'approved')->count();
        $pendingCount = Booking::where('status', 'pending')->count();
        $rejectedCount = Booking::where('status', 'rejected')->count();
        $canceledCount = Booking::where('status', 'canceled')->count();

        $roomUsages = DB::table('bookings')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->select('rooms.name', DB::raw('count(*) as total'))
            ->where('bookings.status', 'approved')
            ->groupBy('rooms.id', 'rooms.name')
            ->orderBy('total', 'desc')
            ->get();

        $userUsages = DB::table('bookings')
            ->join('users', 'bookings.user_id', '=', 'users.userid')
            ->select(
                'users.name',
                'users.userid',
                DB::raw('count(*) as total_approved'),
                DB::raw('sum(case when is_used = 1 then 1 else 0 end) as total_used'),
                DB::raw('sum(case when is_used = 0 then 1 else 0 end) as total_missed')
            )
            ->where('bookings.status', 'approved')
            ->groupBy('users.userid', 'users.name')
            ->orderBy('total_approved', 'desc')
            ->get();

        $data = compact('type', 'totalItems', 'approvedCount', 'pendingCount', 'rejectedCount', 'canceledCount', 'roomUsages', 'userUsages');

        $pdf = Pdf::loadView('pdf.dashboard', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'defaultFont' => 'Sarabun',
                'chroot' => public_path() // ให้ DOMPDF เข้าถึงไฟล์ใน public ได้ด้วย
            ]);

        $filename = 'Dashboard_Report_' . date('Ymd_Hi') . '.pdf';
        if ($type == 'room') $filename = 'Room_Usage_Report_' . date('Ymd_Hi') . '.pdf';
        if ($type == 'user') $filename = 'User_Usage_Report_' . date('Ymd_Hi') . '.pdf';

        return $pdf->download($filename);
    }
}
