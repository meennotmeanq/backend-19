@extends('layouts.app')
@section('title', 'Manage Bookings')

@section('content')
    <div class="container">
        <h2>จัดการการจองห้องเรียน</h2>
        <table class="table table-bordered shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>รหัสการจอง</th>
                    <th>รหัสผู้จอง</th>
                    <th>ห้อง</th>
                    <th>วันที่/เวลา</th>
                    <th>สถานะปัจจุบัน</th>
                    <th>จัดการ (อนุมัติ/ปฏิเสธ)</th>
                    <th>การเข้าใช้งาน (สำหรับที่อนุมัติแล้ว)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $groupId => $group)
                    @php
                        $firstBooking = $group->first();
                        $bookingIdDisplay = $firstBooking->booking_id ? $firstBooking->booking_id : 'RID-'.$firstBooking->id;
                    @endphp
                    <tr>
                        <td><strong>{{ $bookingIdDisplay }}</strong></td>
                        <td>{{ $firstBooking->user_id }}</td>
                        <td>{{ $firstBooking->room->name }}</td>
                        <td>
                            @foreach($group as $item)
                                <div class="mb-1">
                                    {{ \Carbon\Carbon::parse($item->booking_date)->format('d/m/Y') }}
                                    <span class="text-muted">({{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }})</span>
                                </div>
                            @endforeach
                        </td>
                        <td>
                            <span
                                class="badge @if ($firstBooking->status == 'pending') bg-warning @elseif($firstBooking->status == 'approved') bg-success @else bg-danger @endif">
                                {{ $firstBooking->status }}
                            </span>
                        </td>
                        <td>
                            @if ($firstBooking->status == 'pending')
                                <form action="{{ route('admin_booking_update', $firstBooking->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-sm btn-success">อนุมัติ</button>
                                </form>
                                <form action="{{ route('admin_booking_update', $firstBooking->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-sm btn-danger">ปฏิเสธ</button>
                                </form>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($firstBooking->status == 'approved')
                                <div class="d-flex gap-1">
                                    {{-- ปุ่มเข้าใช้งาน (1) --}}
                                    <form action="{{ route('admin_booking_update_usage', $firstBooking->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="is_used" value="1">
                                        <button
                                            class="btn btn-sm {{ $firstBooking->is_used == 1 ? 'btn-success' : 'btn-outline-success' }}"
                                            title="เข้าใช้งาน">
                                            <i class="bi bi-check-circle"></i> มาใช้งาน
                                        </button>
                                    </form>

                                    {{-- ปุ่มไม่เข้าใช้งาน (0) --}}
                                    <form action="{{ route('admin_booking_update_usage', $firstBooking->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="is_used" value="0">
                                        <button
                                            class="btn btn-sm {{ $firstBooking->is_used !== null && $firstBooking->is_used == 0 ? 'btn-danger' : 'btn-outline-danger' }}"
                                            title="ไม่เข้าใช้งาน">
                                            <i class="bi bi-x-circle"></i> ไม่มาใช้งาน
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
