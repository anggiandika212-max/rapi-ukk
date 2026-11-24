<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kalender Public - RAPI-Sekolah</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.css"
        rel="stylesheet"
    >

    <link
        href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f4f5f9;
            color: #30364d;
            font-family: Arial, sans-serif;
        }

        /* Navbar */
        .public-navbar {
            background: #fff;
            border-bottom: 1px solid #e8eaf0;
            box-shadow: 0 2px 10px rgba(0,0,0,.025);
        }

        .navbar-brand {
            font-size: 21px;
            letter-spacing: -.5px;
        }

        .login-button {
            background: #696cff;
            border-color: #696cff;
            border-radius: 9px;
            font-weight: 600;
        }

        .login-button:hover {
            background: #5558e8;
            border-color: #5558e8;
        }

        /* Header */
        .calendar-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .calendar-eyebrow {
            color: #696cff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .calendar-header h2 {
            font-weight: 700;
            margin-top: 7px;
            color: #30364d;
        }

        .calendar-header p {
            color: #8592a3;
            margin-bottom: 0;
        }

        .calendar-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #696cff;
            color: white;
            border-radius: 16px;
            font-size: 30px;
            box-shadow: 0 5px 15px rgba(105,108,255,.25);
        }

        /* Statistik */
        .calendar-stat-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            height: 100%;
            background: #fff;
            border: 1px solid #edf0f5;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0,0,0,.035);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 25px;
        }

        .stat-icon-primary {
            background: #eeeeff;
            color: #696cff;
        }

        .stat-icon-danger {
            background: #fff0f0;
            color: #dc3545;
        }

        .calendar-stat-card span {
            color: #8592a3;
            font-size: 13px;
        }

        .calendar-stat-card h3 {
            margin: 3px 0 0;
            font-weight: 700;
            color: #30364d;
        }

        /* Calendar Card */
        .calendar-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #edf0f5;
            box-shadow: 0 4px 20px rgba(0,0,0,.045);
        }

        .calendar-legend {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 12px;
            color: #8592a3;
        }

        .legend-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-right: 5px;
        }

        #calendar {
            min-height: 550px;
        }

        /* FullCalendar */
        .fc .fc-toolbar {
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 22px !important;
        }

        .fc .fc-toolbar-title {
            font-size: 21px;
            font-weight: 700;
            text-transform: capitalize;
            color: #30364d;
        }

        .fc .fc-button {
            background: #696cff !important;
            border-color: #696cff !important;
            border-radius: 8px !important;
            padding: 8px 13px !important;
            font-weight: 600;
            box-shadow: none !important;
        }

        .fc .fc-button:hover {
            background: #5558e8 !important;
            border-color: #5558e8 !important;
        }

        .fc .fc-button-primary:disabled {
            opacity: .5;
        }

        .fc .fc-col-header-cell {
            padding: 12px 0;
            background: #f5f6ff;
        }

        .fc .fc-col-header-cell-cushion {
            color: #566078;
            font-weight: 700;
            text-decoration: none;
        }

        .fc .fc-daygrid-day-number {
            padding: 9px;
            color: #566078;
            text-decoration: none;
            font-weight: 500;
        }

        .fc .fc-daygrid-day {
            transition: background .2s;
        }

        .fc .fc-daygrid-day:hover {
            background: rgba(105,108,255,.045);
        }

        .fc .fc-day-today {
            background: rgba(105,108,255,.08) !important;
        }

        .fc .fc-day-today .fc-daygrid-day-number {
            background: #696cff;
            color: white !important;
            border-radius: 8px;
            margin: 5px;
            padding: 5px 8px;
        }

        /* Hari Minggu */
        .fc .fc-day-sun .fc-col-header-cell-cushion,
        .fc .fc-day-sun .fc-daygrid-day-number {
            color: #dc3545 !important;
            font-weight: bold;
        }

        .fc .fc-day-sun {
            background: #fff8f8;
        }

        /* Tanggal merah */
        .holiday-day .fc-daygrid-day-number {
            color: #dc3545 !important;
            font-weight: 800;
        }

        /* Event */
        .fc-event {
            border: none !important;
            border-radius: 5px !important;
            padding: 3px 5px !important;
            font-size: 11px !important;
            cursor: pointer;
        }

        .fc-event-title {
            font-weight: 600;
        }

        .fc .fc-daygrid-more-link {
            color: #696cff;
            font-weight: 600;
        }

        /* Modal */
        .calendar-modal .modal-content {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0,0,0,.12);
        }

        .calendar-modal .modal-header {
            padding: 20px 22px;
            background: #f5f6ff;
            border-bottom: 1px solid #edf0f5;
        }

        .calendar-modal .modal-body {
            padding: 24px;
        }

        .calendar-detail-row {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            margin-bottom: 19px;
        }

        .calendar-detail-icon {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eeeeff;
            color: #696cff;
            border-radius: 10px;
            font-size: 20px;
        }

        .calendar-detail-label {
            color: #8592a3;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .calendar-detail-value {
            color: #30364d;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        /* Footer */
        .public-footer {
            color: #8592a3;
            font-size: 12px;
            border-top: 1px solid #e8eaf0;
            background: #fff;
        }

        @media (max-width: 767px) {
            .calendar-heading h2 {
                font-size: 21px;
            }

            .calendar-icon {
                width: 48px;
                height: 48px;
                font-size: 23px;
            }

            .calendar-heading {
                align-items: flex-start;
            }

            .fc .fc-toolbar {
                justify-content: center;
            }

            .fc .fc-toolbar-title {
                font-size: 17px;
            }

            .fc .fc-button {
                padding: 6px 9px !important;
                font-size: 12px;
            }

            .fc .fc-daygrid-day-number {
                padding: 5px;
                font-size: 12px;
            }

            .fc-event {
                font-size: 10px !important;
            }

            .calendar-card .card-header,
            .calendar-card .card-body {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
        }
    </style>
</head>

<body>

    {{-- Navbar --}}
    <nav class="navbar public-navbar px-3 px-md-5 py-3">
        <div class="container-fluid">

            <a href="{{ url('/calendar') }}"
               class="navbar-brand fw-bold text-primary">
                <i class="bx bx-calendar-check me-1"></i>
                RAPI-Sekolah
            </a>

            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary small d-none d-md-inline">
                    Kalender Kegiatan Sekolah
                </span>

                <a href="{{ url('/login') }}"
                   class="btn btn-primary login-button px-4">
                    <i class="bx bx-log-in me-1"></i>
                    Login
                </a>
            </div>

        </div>
    </nav>

    {{-- Main --}}
    <main class="container-fluid px-3 px-md-5 py-4">


        {{-- Statistik --}}
        <div class="row g-3 mb-4">

            <div class="col-md-6">
                <div class="calendar-stat-card">

                    <div class="stat-icon stat-icon-primary">
                        <i class="bx bx-calendar-check"></i>
                    </div>

                    <div>
                        <span>Total Kegiatan</span>
                        <h3 id="totalEvents">0</h3>
                    </div>

                </div>
            </div>

            <div class="col-md-6">
                <div class="calendar-stat-card">

                    <div class="stat-icon stat-icon-danger">
                        <i class="bx bx-calendar-x"></i>
                    </div>

                    <div>
                        <span>Total Hari Libur</span>
                        <h3 id="totalHolidays">0</h3>
                    </div>

                </div>
            </div>

        </div>

        {{-- Kalender --}}
        <div class="calendar-card">

            <div class="card-header bg-transparent border-0 pt-4 px-4">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div class="calendar-header">
                        <span class="calendar-eyebrow">RAPI-SEKOLAH</span>
                        <h2>Kalender Agenda Sekolah</h2>
                        <p>
                        Pantau jadwal kegiatan, agenda, dan hari libur sekolah.
                        </p>
                        <p>
                        Pilih kegiatan atau hari libur untuk melihat detail.
                        </p>
                    </div>

                    <div class="calendar-legend">

                        <span>
                            <i class="legend-dot bg-primary"></i>
                            Kegiatan
                        </span>

                        <span>
                            <i class="legend-dot bg-danger"></i>
                            Hari Libur
                        </span>

                    </div>

                </div>

            </div>

            <div class="card-body px-4 pb-4">
                <div id="calendar"></div>
            </div>

        </div>

    </main>

    {{-- Footer --}}
    <footer class="public-footer text-center py-4 mt-4">
        &copy; {{ date('Y') }} RAPI-Sekolah. Semua hak dilindungi.
    </footer>

    {{-- Modal Detail --}}
    <div class="modal fade calendar-modal"
         id="publicEventModal"
         tabindex="-1"
         aria-labelledby="publicEventModalLabel"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold"
                        id="publicEventModalLabel">
                        <i class="bx bx-calendar-event text-primary me-1"></i>
                        Detail Kegiatan
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Tutup"></button>

                </div>

                <div class="modal-body">

                    <h5 id="publicEventTitle"
                        class="fw-bold mb-4"></h5>

                    <div class="calendar-detail-row">

                        <div class="calendar-detail-icon">
                            <i class="bx bx-calendar"></i>
                        </div>

                        <div>
                            <div class="calendar-detail-label">Tanggal</div>
                            <div class="calendar-detail-value"
                                 id="publicEventDate"></div>
                        </div>

                    </div>

                    <div class="calendar-detail-row">

                        <div class="calendar-detail-icon">
                            <i class="bx bx-time-five"></i>
                        </div>

                        <div>
                            <div class="calendar-detail-label">Waktu</div>
                            <div class="calendar-detail-value"
                                 id="publicEventTime"></div>
                        </div>

                    </div>

                    <div class="calendar-detail-row">

                        <div class="calendar-detail-icon">
                            <i class="bx bx-door-open"></i>
                        </div>

                        <div>
                            <div class="calendar-detail-label">Ruangan</div>
                            <div class="calendar-detail-value"
                                 id="publicEventRoom"></div>
                        </div>

                    </div>

                    <div class="calendar-detail-row mb-0">

                        <div class="calendar-detail-icon">
                            <i class="bx bx-notepad"></i>
                        </div>

                        <div>
                            <div class="calendar-detail-label">
                                Deskripsi / Keterangan
                            </div>

                            <div class="calendar-detail-value"
                                 id="publicEventDescription"
                                 style="white-space: pre-wrap;"></div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer border-0 pt-0">

                    <button type="button"
                            class="btn btn-secondary px-4"
                            data-bs-dismiss="modal">
                        Tutup
                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/locales/id.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const calendarEl = document.getElementById('calendar');

            if (!calendarEl) return;

            let holidayDates = new Set();
            let calendar;

            function formatLocalDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            }

            function formatDate(date) {
                if (!date) return '-';

                return date.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
            }

            function formatTime(date) {
                if (!date) return '-';

                return date.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
            }

            function showEventDetail(event) {

                const props = event.extendedProps;

                document.getElementById('publicEventTitle').textContent =
                    event.title || '-';

                document.getElementById('publicEventDate').textContent =
                    formatDate(event.start);

                if (props.isHoliday) {

                    document.getElementById('publicEventTime').textContent =
                        'Hari libur';

                    document.getElementById('publicEventRoom').textContent =
                        'Tidak berlaku';

                    document.getElementById('publicEventDescription').textContent =
                        `Jenis: ${props.type || '-'}\n\n${props.description || 'Tidak ada keterangan.'}`;

                } else {

                    document.getElementById('publicEventTime').textContent =
                        `${formatTime(event.start)} - ${formatTime(event.end)}`;

                    document.getElementById('publicEventRoom').textContent =
                        props.room || 'Belum ditentukan';

                    document.getElementById('publicEventDescription').textContent =
                        props.description || 'Tidak ada deskripsi.';
                }

                bootstrap.Modal
                    .getOrCreateInstance(
                        document.getElementById('publicEventModal')
                    )
                    .show();
            }

            calendar = new FullCalendar.Calendar(calendarEl, {

                initialView: 'dayGridMonth',
                locale: 'id',
                firstDay: 1,
                height: 'auto',
                dayMaxEvents: 3,
                fixedWeekCount: false,
                navLinks: true,

                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },

                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    week: 'Minggu',
                    list: 'Daftar'
                },

                dayCellClassNames: function (arg) {
                    return holidayDates.has(formatLocalDate(arg.date))
                        ? ['holiday-day']
                        : [];
                },

                eventClick: function (info) {
                    showEventDetail(info.event);
                },

                events: async function (
                    fetchInfo,
                    successCallback,
                    failureCallback
                ) {

                    try {

                        const params = new URLSearchParams({
                            start: fetchInfo.startStr.slice(0, 10),
                            end: fetchInfo.endStr.slice(0, 10)
                        });

                        const [eventResponse, holidayResponse] =
                            await Promise.all([

                                fetch(
                                    `/api/public/events?${params.toString()}`,
                                    {
                                        headers: {
                                            Accept: 'application/json'
                                        }
                                    }
                                ),

                                fetch('/api/holidays', {
                                    headers: {
                                        Accept: 'application/json'
                                    }
                                })

                            ]);

                        if (!eventResponse.ok || !holidayResponse.ok) {
                            throw new Error(
                                'Gagal mengambil data kalender.'
                            );
                        }

                        const [eventJson, holidayJson] =
                            await Promise.all([
                                eventResponse.json(),
                                holidayResponse.json()
                            ]);

                        if (
                            !eventJson.success ||
                            !Array.isArray(eventJson.data)
                        ) {
                            throw new Error(
                                'Format data kegiatan tidak sesuai.'
                            );
                        }

                        if (
                            !holidayJson.success ||
                            !Array.isArray(holidayJson.data)
                        ) {
                            throw new Error(
                                'Format data hari libur tidak sesuai.'
                            );
                        }

                        // Data kegiatan sekolah
                        const events = eventJson.data.map(event => ({

                            id: event.id,
                            title: event.title,
                            start: event.start,
                            end: event.end,

                            backgroundColor: '#696cff',
                            borderColor: '#696cff',

                            extendedProps: {
                                room: event.room?.name ?? 'Belum ditentukan',
                                description: event.description ?? '',
                                isHoliday: false
                            }

                        }));

                        // Reset tanggal libur sebelum memuat ulang
                        holidayDates = new Set();

                        // Data hari libur
                        const holidays = holidayJson.data.map(holiday => {

                            const holidayDate =
                                String(holiday.date).substring(0, 10);

                            holidayDates.add(holidayDate);

                            return {

                                id: `holiday-${holiday.id}`,
                                title: `Libur: ${holiday.name}`,
                                start: holidayDate,
                                allDay: true,

                                backgroundColor: '#dc3545',
                                borderColor: '#dc3545',
                                textColor: '#ffffff',

                                extendedProps: {
                                    isHoliday: true,
                                    type: holiday.type,
                                    description: holiday.description
                                }

                            };

                        });

                        // Statistik
                        document.getElementById('totalEvents').textContent =
                            events.length;

                        document.getElementById('totalHolidays').textContent =
                            holidays.length;

                        successCallback([...events, ...holidays]);

                        setTimeout(() => {
                            calendar.rerenderDates();
                        }, 0);

                    } catch (error) {

                        console.error(
                            'Gagal memuat kalender:',
                            error
                        );

                        failureCallback(error);

                    }

                }

            });

            calendar.render();

        });
    </script>

</body>
</html>