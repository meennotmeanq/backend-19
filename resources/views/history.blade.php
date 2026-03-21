@extends('layouts.app')
@section('title', 'Booking History')

@section('content')
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">ประวัติการจองห้องเรียนของคุณ</h4>
            </div>
            <div class="card-body">
                @if ($bookings->isEmpty())
                    <p class="text-center">คุณยังไม่มีประวัติการจองในขณะนี้</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>รหัสการจอง</th>
                                    <th>ชื่อห้อง</th>
                                    <th>วันที่และเวลา</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $groupId => $group)
                                    @php
                                        $firstBooking = $group->first();
                                        $bookingIdDisplay = $firstBooking->booking_id ? $firstBooking->booking_id : 'RID-'.$firstBooking->id;
                                    @endphp
                                    <tr>
                                        <td>{{ $bookingIdDisplay }}</td>
                                        <td>{{ $firstBooking->room->name ?? 'ไม่พบข้อมูลห้อง' }}</td>
                                        <td>
                                            @foreach($group as $item)
                                                <div class="mb-1">
                                                    {{ date('d/m/Y', strtotime($item->booking_date)) }} 
                                                    <span class="text-muted">({{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }})</span>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if ($firstBooking->status == 'pending')
                                                <span class="badge bg-warning text-dark">รออนุมัติ</span>
                                            @elseif($firstBooking->status == 'approved')
                                                <span class="badge bg-success">อนุมัติแล้ว</span>
                                            @elseif($firstBooking->status == 'rejected')
                                                <span class="badge bg-danger">ปฏิเสธ</span>
                                            @elseif($firstBooking->status == 'canceled')
                                                <span class="badge bg-secondary">ยกเลิกแล้ว</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($firstBooking->status != 'canceled')
                                                <form action="{{ route('booking_cancel', $firstBooking->id) }}" method="POST"
                                                    onsubmit="return confirm('ยืนยันการยกเลิกการจอง?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger">ยกเลิก</button>
                                                </form>
                                            @else
                                                <span class="text-muted">ยกเลิกแล้ว</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
