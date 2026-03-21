@extends('layouts.app')
@section('title', 'Booking Status')

@section('content')
    @php
       $firstBooking = $bookingsGroup->first();
       $bookingIdDisplay = $firstBooking->booking_id ? $firstBooking->booking_id : 'RID-'.$firstBooking->id;
    @endphp
    <h1>แจ้งผลการจองห้องเรียน</h1>
    <p>เรียน คุณ {{ $firstBooking->user->name }},</p>
    <p>การจองห้อง <strong>{{ $firstBooking->room->name }}</strong> (รหัสการจอง: <strong>{{ $bookingIdDisplay }}</strong>) ของคุณได้รับการพิจารณาแล้ว โดยมีรายละเอียดดังนี้:</p>
    <ul>
        @foreach($bookingsGroup as $item)
            <li>วันที่ {{ \Carbon\Carbon::parse($item->booking_date)->format('d/m/Y') }} เวลา {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}</li>
        @endforeach
    </ul>
    <p>สถานะปัจจุบัน:
        <strong>
            @if ($firstBooking->status == 'approved')
                <span style="color: green;">อนุมัติแล้ว</span>
            @elseif($firstBooking->status == 'rejected')
                <span style="color: red;">ปฏิเสธ</span>
            @endif
        </strong>
    </p>
    <p>ขอบคุณที่ใช้บริการครับ</p>
@endsection
