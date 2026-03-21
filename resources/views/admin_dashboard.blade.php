@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-primary"><i class="bi bi-bar-chart-fill"></i> ภาพรวมระบบ (Dashboard)</h2>
        <div class="dropdown d-inline-block">
            <button class="btn btn-danger dropdown-toggle shadow-sm" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-file-earmark-pdf"></i> ส่งออกรายงาน PDF
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="{{ route('admin_dashboard_export', ['type' => 'all']) }}">ส่งออกทั้งหมด (All)</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('admin_dashboard_export', ['type' => 'room']) }}">เฉพาะรายงานการใช้ห้อง</a></li>
                <li><a class="dropdown-item" href="{{ route('admin_dashboard_export', ['type' => 'user']) }}">เฉพาะสถิติผู้ใช้งาน</a></li>
            </ul>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">จำนวนการจองทั้งหมด</h5>
                    <p class="card-text fs-2 fw-bold">{{ $totalItems }} <span class="fs-6 fw-normal">รายการ</span></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">อนุมัติแล้ว</h5>
                    <p class="card-text fs-2 fw-bold">{{ $approvedCount }} <span class="fs-6 fw-normal">รายการ</span></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-dark bg-warning shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">รออนุมัติ</h5>
                    <p class="card-text fs-2 fw-bold">{{ $pendingCount }} <span class="fs-6 fw-normal">รายการ</span></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">ปฏิเสธ / ยกเลิก</h5>
                    <p class="card-text fs-2 fw-bold">{{ $rejectedCount + $canceledCount }} <span class="fs-6 fw-normal">รายการ</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Room Usage Report -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">รายงานการใช้ห้อง (เฉพาะที่อนุมัติแล้ว)</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($roomUsages as $room)
                            <li class="list-group-item d-flex justify-content-between align-items-center {{ $loop->first ? 'bg-light border-start border-warning border-4' : '' }}">
                                <div>
                                    @if($loop->first)
                                        <i class="bi bi-trophy-fill text-warning me-1"></i>
                                        <strong>{{ $room->name }}</strong>
                                        <span class="badge bg-warning text-dark ms-2">ใช้งานมากที่สุด</span>
                                    @else
                                        <span class="ms-1">{{ $room->name }}</span>
                                    @endif
                                </div>
                                <span class="badge {{ $loop->first ? 'bg-warning text-dark' : 'bg-primary' }} rounded-pill">{{ $room->total }} ครั้ง</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">ยังไม่มีข้อมูลการอนุมัติ</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- User Usage Statistics -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">สถิติผู้ใช้งาน (เฉพาะที่อนุมัติแล้ว)</h5>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>ผู้ใช้งาน (รหัส)</th>
                                <th class="text-center">จองทั้งหมด</th>
                                <th class="text-center text-success">ใช้งานจริง</th>
                                <th class="text-center text-danger">ไม่เข้าใช้งาน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userUsages as $user)
                                <tr>
                                    <td>{{ $user->name }} <br><small class="text-muted">{{ $user->userid }}</small></td>
                                    <td class="text-center align-middle">{{ $user->total_approved }}</td>
                                    <td class="text-center align-middle text-success fw-bold">{{ $user->total_used }}</td>
                                    <td class="text-center align-middle text-danger fw-bold">{{ $user->total_missed }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">ยังไม่มีข้อมูลการอนุมัติ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
