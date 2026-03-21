<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report - {{ $type }}</title>
    <style>
        @font-face {
            font-family: 'Sarabun';
            font-style: normal;
            font-weight: normal;
            src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(public_path('fonts/Sarabun-Regular.ttf'))) }}") format('truetype');
        }
        @font-face {
            font-family: 'Sarabun';
            font-style: normal;
            font-weight: bold;
            src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(public_path('fonts/Sarabun-Bold.ttf'))) }}") format('truetype');
        }
        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 16px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        h2, h3 { color: #333; margin-bottom: 5px; }
        .summary-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 30px;">
        <h2>รายงานสรุปผลการใช้งานห้องเรียน (Dashboard)</h2>
        <p style="margin: 0; color: #555;">วันที่พิมพ์: {{ date('d/m/Y H:i') }}</p>
    </div>

    @if($type == 'all' || $type == 'summary')
    <div class="summary-box">
        <h3>ภาพรวมระบบ</h3>
        <p style="margin: 3px 0;">• จำนวนการจองทั้งหมด: {{ $totalItems }} รายการ</p>
        <p style="margin: 3px 0; color: green;">• อนุมัติแล้ว: {{ $approvedCount }} รายการ</p>
        <p style="margin: 3px 0; color: #d39e00;">• รออนุมัติ: {{ $pendingCount }} รายการ</p>
        <p style="margin: 3px 0; color: red;">• ปฏิเสธ / ยกเลิก: {{ $rejectedCount + $canceledCount }} รายการ</p>
    </div>
    @endif

    @if($type == 'all' || $type == 'room')
    <h3>รายงานการใช้ห้อง <span style="font-size: 14px; font-weight: normal;">(เฉพาะที่อนุมัติแล้ว)</span></h3>
    <table>
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">อันดับ</th>
                <th>ชื่อห้อง</th>
                <th style="width: 25%; text-align: center;">จำนวนที่มีการเข้าใช้งาน (ครั้ง)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roomUsages as $index => $room)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $room->name }} {!! $loop->first ? '<span style="color: #c98800; font-size: 13px;">(ใช้มากที่สุด)</span>' : '' !!}</td>
                <td class="text-center">{{ $room->total }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center">ยังไม่มีข้อมูลการอนุมัติห้อง</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    @if($type == 'all' || $type == 'user')
    <h3>สถิติผู้ใช้งาน <span style="font-size: 14px; font-weight: normal;">(เฉพาะที่อนุมัติแล้ว)</span></h3>
    <table>
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">ลำดับ</th>
                <th>ผู้ใช้งาน / รหัส</th>
                <th style="width: 15%; text-align: center;">จองทั้งหมด</th>
                <th style="width: 15%; text-align: center; color: green;">เข้าใช้งานจริง</th>
                <th style="width: 15%; text-align: center; color: red;">ไม่มาใช้งาน</th>
            </tr>
        </thead>
        <tbody>
            @forelse($userUsages as $index => $user)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $user->name }} ({{ $user->userid }})</td>
                <td class="text-center">{{ $user->total_approved }}</td>
                <td class="text-center" style="color: green;">{{ $user->total_used }}</td>
                <td class="text-center" style="color: red;">{{ $user->total_missed }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">ยังไม่มีข้อมูลการจองของผู้ใช้</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif

</body>
</html>
