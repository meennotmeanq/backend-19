@extends('layouts.app')
@section('title', 'Rooms')

@section('content')
    @php
        $floorRooms = [
            '2' => ['19201', '19202', '19203', '19204', '19205', '19206'],
            '3' => ['19301', '19302', '19303', '19304', '19305', '19306'],
            '4' => ['19401', '19402', '19403', '19404', '19405', '19406'],
            '5' => ['19501', '19502', '19503', '19504', '19505', '19506'],
        ];
        $allTargetRooms = collect($floorRooms)->flatten()->toArray();

        $roomsByName = collect($rooms)->keyBy('name');
    @endphp

    <style>
        .slot-active {
            background-color: #2d940d !important;
            color: white !important;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="mb-0">ห้องเรียน</h2>
            <small class="text-muted d-block">แสดงตามชั้น และสถานะว่าง/ไม่ว่างตามวันที่ + ช่วงเวลา</small>
        </div>
        <div class="d-flex align-items-end gap-2 flex-wrap">
            <div style="min-width: 150px;">
                <label class="form-label mb-1">ประเภท</label>
                <select id="overview_booking_type" class="form-select form-select-sm">
                    <option value="single">จองรายวัน (เลือกหลายช่วงเวลาได้)</option>
                    <option value="group">จองแบบกลุ่ม (หลายวัน)</option>
                </select>
            </div>
            <div style="min-width: 190px;">
                <label class="form-label mb-1">วันที่</label>
                <input type="text" id="rooms_overview_calendar" class="form-control form-control-sm"
                    placeholder="เลือกวันที่">
            </div>
            <div>
                <label class="form-label mb-1 d-block">เวลา</label>
                <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Time slots" id="slots-container">
                    {{-- Slots will be injected by JS --}}
                </div>
            </div>
        </div>
    </div>
    <hr>

    @foreach ($floorRooms as $floor => $names)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h5 class="mb-0">ชั้น {{ $floor }}</h5>
                    <small class="text-muted" id="overview-floor-{{ $floor }}">ทั้งหมด {{ count($names) }}
                        ห้อง</small>
                </div>

                <div class="row g-2">
                    @foreach ($names as $roomName)
                        @php
                            $room = $roomsByName->get($roomName);
                        @endphp
                        <div class="col-6 col-md-4 col-lg-2">
                            @if ($room)
                                <button type="button" class="btn w-100 rooms-overview-card"
                                    data-room-id="{{ $room->id }}" data-floor="{{ $floor }}">
                                    <div class="fw-bold">{{ $roomName }}</div>
                                    <div class="small text-muted">ความจุ: {{ $room->capacity }}</div>
                                    <div class="small text-muted text-truncate" title="{{ $room->description }}">
                                        {{ $room->description }}
                                    </div>
                                    <div class="small mt-1 overview-status-text">
                                        {{-- จะอัปเดตด้วย JS ตามวันที่/เวลา --}}
                                    </div>
                                </button>
                            @else
                                <div class="card h-100">
                                    <div class="card-body p-2">
                                        <div class="fw-bold">{{ $roomName }}</div>
                                        <div class="small text-danger">ไม่พบข้อมูลห้องในระบบ</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        @php
            $setting = \App\Models\BookingSetting::first();
            $maxDays = $setting && $setting->max_advance_days !== null ? (int) $setting->max_advance_days : 14;
            $maxDays = max(0, $maxDays);
            $maxBookingDate = \Carbon\Carbon::today()->addDays($maxDays)->toDateString();
        @endphp
        const overviewMaxDateForUser = "{{ $maxBookingDate }}";

        let overviewDates = ["{{ now()->toDateString() }}"];
        let overviewSlots = new Set();
        let overviewType = "single";

        // Slot definitions
        const weekdaySlots = [{
                key: 'slot1',
                label: '08:30-12:30'
            },
            {
                key: 'slot2',
                label: '13:30-17:30'
            },
            {
                key: 'slot3',
                label: '18:30-20:00'
            }
        ];

        const weekendSlots = [{
                key: 'slot_w_1',
                label: '08:20-09:10'
            },
            {
                key: 'slot_w_2',
                label: '09:10-10:00'
            },
            {
                key: 'slot_w_3',
                label: '10:00-10:50'
            },
            {
                key: 'slot_w_4',
                label: '10:50-11:40'
            },
            {
                key: 'slot_w_5',
                label: '11:40-12:30'
            },
            {
                key: 'slot_w_6',
                label: '12:30-13:20'
            },
            {
                key: 'slot_w_7',
                label: '13:20-14:10'
            },
            {
                key: 'slot_w_8',
                label: '14:10-15:00'
            },
            {
                key: 'slot_w_9',
                label: '15:00-15:50'
            },
            {
                key: 'slot_w_10',
                label: '15:50-16:40'
            },
            {
                key: 'slot_w_11',
                label: '16:40-17:30'
            },
            {
                key: 'slot_w_12',
                label: '17:30-18:20'
            }
        ];

        function isWeekendDate(d) {
            const day = d.getDay();
            return day === 0 || day === 6;
        }

        const typeSelector = document.getElementById('overview_booking_type');
        const initialMode = typeSelector.value === 'group' ? 'multiple' : 'single';

        const overviewCalendar = flatpickr("#rooms_overview_calendar", {
            mode: initialMode,
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            minDate: "today",
            maxDate: overviewMaxDateForUser,
            defaultDate: ["{{ now()->toDateString() }}"],
            onChange: function(selectedDates, dateStr) {
                if (selectedDates.length === 0) {
                    overviewDates = [];
                    refreshRoomsOverview();
                    return;
                }

                // Determine weekend/weekday based on first selected date
                const firstDate = selectedDates[0];
                const isWeekend = isWeekendDate(firstDate);
                renderSlots(isWeekend);

                if (overviewType === 'single') {
                    if (selectedDates.length > 1) {
                        const lastDate = selectedDates[selectedDates.length - 1];
                        overviewCalendar.setDate([lastDate]);
                        overviewDates = [overviewCalendar.formatDate(lastDate, "Y-m-d")];

                        // Re-check weekend status for the kept date
                        const newIsWeekend = isWeekendDate(lastDate);
                        if (newIsWeekend !== isWeekend) {
                            renderSlots(newIsWeekend);
                        }
                    } else {
                        overviewDates = selectedDates.map(d => overviewCalendar.formatDate(d, "Y-m-d"));
                    }
                } else {
                    // Group: max 3 days, must be same type (all weekday or all weekend)
                    if (selectedDates.length > 3) {
                        alert("จองแบบกลุ่มเลือกได้ไม่เกิน 3 วันครับ");
                        const keepDates = selectedDates.slice(0, 3);
                        overviewCalendar.setDate(keepDates);
                        overviewDates = keepDates.map(d => overviewCalendar.formatDate(d, "Y-m-d"));
                    } else {
                        // Block mixed weekday + weekend selections
                        const hasMixed = selectedDates.some(d => isWeekendDate(d) !== isWeekend);
                        if (hasMixed) {
                            alert("จองแบบกลุ่มต้องเลือกวันประเภทเดียวกัน (วันธรรมดา หรือ เสาร์-อาทิตย์) ครับ");
                            const lastDate = selectedDates[selectedDates.length - 1];
                            overviewCalendar.setDate([lastDate]);
                            overviewDates = [overviewCalendar.formatDate(lastDate, "Y-m-d")];
                            renderSlots(isWeekendDate(lastDate));
                        } else {
                            overviewDates = selectedDates.map(d => overviewCalendar.formatDate(d, "Y-m-d"));
                        }
                    }
                }
                refreshRoomsOverview();
            }
        });

        typeSelector.addEventListener('change', function() {
            overviewType = this.value;
            overviewCalendar.clear();
            overviewDates = [];
            overviewSlots.clear();

            if (overviewType === 'single') {
                overviewCalendar.set('mode', 'single');
            } else {
                overviewCalendar.set('mode', 'multiple');
            }
            overviewCalendar.setDate("{{ now()->toDateString() }}", true);
            overviewDates = ["{{ now()->toDateString() }}"];

            // Force render slots based on today's date
            const today = new Date();
            renderSlots(isWeekendDate(today));
            refreshRoomsOverview();
        });

        function renderSlots(isWeekend) {
            const container = document.getElementById('slots-container');
            container.innerHTML = '';
            overviewSlots.clear();
            const slots = isWeekend ? weekendSlots : weekdaySlots;

            slots.forEach(s => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-secondary overview-slot-btn mb-1 me-1';
                btn.setAttribute('data-slot', s.key);
                btn.textContent = s.label;

                btn.addEventListener('click', function() {
                    const slot = this.getAttribute('data-slot');

                    if (overviewSlots.has(slot)) {
                        // Prevent deselecting if it's the last selected slot
                        if (overviewSlots.size === 1) {
                            alert("ต้องเลือกช่วงเวลาอย่างน้อย 1 ช่วงครับ");
                            return;
                        }
                        overviewSlots.delete(slot);
                        this.classList.remove('slot-active');
                    } else {
                        overviewSlots.add(slot);
                        this.classList.add('slot-active');
                    }
                    refreshRoomsOverview();
                });

                container.appendChild(btn);
            });

            // Auto-select first slot as default
            if (slots.length > 0) {
                const first = slots[0].key;
                overviewSlots.add(first);
                const firstBtn = container.querySelector(`[data-slot="${first}"]`);
                if (firstBtn) firstBtn.classList.add('slot-active');
            }
        }

        async function fetchOverviewUnavailable(datesArray, slotsArray) {
            const params = new URLSearchParams();
            if (datesArray.length > 0) {
                datesArray.forEach(d => params.append('dates[]', d));
            } else {
                return [];
            }
            slotsArray.forEach(s => params.append('slots[]', s));

            const url = `{{ route('booking_availability') }}?${params.toString()}`;
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const json = await res.json();
            return (json.bookedRoomIds || []);
        }

        async function refreshRoomsOverview() {
            if (overviewDates.length === 0 || overviewSlots.size === 0) {
                document.querySelectorAll('.rooms-overview-card').forEach(btn => {
                    btn.classList.remove('btn-success', 'btn-danger');
                    btn.classList.add('btn-outline-secondary');

                    const newBtn = btn.cloneNode(true);
                    btn.parentNode.replaceChild(newBtn, btn);

                    const text = newBtn.querySelector('.overview-status-text');
                    if (text) text.textContent = 'เลือกวันที่และเวลา';
                });
                ['2', '3', '4', '5'].forEach(f => {
                    const el = document.getElementById(`overview-floor-${f}`);
                    if (el) el.textContent = 'ทั้งหมด 6 ห้อง';
                });
                return;
            }

            const slotsArray = Array.from(overviewSlots);
            const unavailableIds = new Set(
                (await fetchOverviewUnavailable(overviewDates, slotsArray)).map(String)
            );

            const summary = {
                '2': {
                    a: 0,
                    u: 0
                },
                '3': {
                    a: 0,
                    u: 0
                },
                '4': {
                    a: 0,
                    u: 0
                },
                '5': {
                    a: 0,
                    u: 0
                }
            };

            document.querySelectorAll('.rooms-overview-card').forEach(btn => {
                const id = String(btn.getAttribute('data-room-id'));
                const floor = btn.getAttribute('data-floor');
                const text = btn.querySelector('.overview-status-text');

                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);

                if (unavailableIds.has(id)) {
                    newBtn.classList.remove('btn-success', 'btn-outline-secondary');
                    newBtn.classList.add('btn-danger');
                    newBtn.disabled = true;
                    if (text) text.textContent = 'ไม่ว่าง';
                    if (summary[floor]) summary[floor].u += 1;
                } else {
                    newBtn.classList.remove('btn-danger', 'btn-outline-secondary');
                    newBtn.classList.add('btn-success');
                    newBtn.disabled = false;
                    if (text) text.textContent = 'ว่าง (คลิกเพื่อจอง)';
                    if (summary[floor]) summary[floor].a += 1;

                    newBtn.addEventListener('click', function() {
                        const params = new URLSearchParams();
                        params.append('room_id', id);
                        overviewDates.forEach(d => params.append('dates[]', d));
                        params.append('booking_type', overviewType);
                        slotsArray.forEach(s => params.append('slots[]', s));

                        window.location.href = `{{ route('booking') }}?${params.toString()}`;
                    });
                }
            });

            Object.keys(summary).forEach(f => {
                const el = document.getElementById(`overview-floor-${f}`);
                if (el) {
                    el.textContent = `ว่าง ${summary[f].a} ห้อง / ไม่ว่าง ${summary[f].u} ห้อง`;
                }
            });
        }

        // Initialize on page load
        (function initOverview() {
            const today = new Date();
            renderSlots(isWeekendDate(today));
            refreshRoomsOverview();
        })();
    </script>
@endsection
