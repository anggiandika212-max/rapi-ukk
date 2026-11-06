
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

    <style>
        body {
            background: #f4f5f9;
            color: #161b24;
            font-family: Arial, sans-serif;
        }

        .public-navbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }

        .calendar-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .05);
        }

        #calendar {
            min-height: 600px;
        }
        
        #calendar .fc-daygrid-day-number{
          color: #000000;
          text-decoration: none;
        }
        #calendar .fc-col-header-cell-cushion {
          color: #000206;
          text-decoration: none;
          font-weight: 600;
        }

        .fc .fc-toolbar-title {
            font-size: 1.4rem;
        }

        .fc .fc-button-primary {
            background: #121b25;
            border-color: #2c3e50;
        }

        .fc .fc-button-primary:hover {
            background: #1d2939;
            border-color: #1d2939;
        }
        .holiday-day .fc-daygrid-day-number {
            color: #dc3545 !important;
            font-weight: bold;
        }
        /* Warna merah untuk nama hari Minggu */
        .fc .fc-col-header-cell.fc-day-sun .fc-col-header-cell-cushion {
            color: #dc3545 !important;
            font-weight: bold;
        }

        /* Warna merah untuk angka tanggal hari Minggu */
        .fc .fc-day-sun .fc-daygrid-day-number {
            color: #dc3545 !important;
            font-weight: bold;
        }

        /* Warna merah untuk seluruh area tanggal hari Minggu */
        .fc .fc-day-sun {
            background-color: #fff5f5;
        }
    </style>
</head>
<div class="modal fade" id="publicEventModal" tabindex="-1"
     aria-labelledby="publicEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title" id="publicEventModalLabel">
                    Detail Kegiatan
                </h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <h5 id="publicEventTitle" class="fw-bold mb-3"></h5>

                <div class="mb-3">
                    <div class="text-muted small">Tanggal</div>
                    <div id="publicEventDate"></div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Waktu</div>
                    <div id="publicEventTime"></div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Ruangan</div>
                    <div id="publicEventRoom"></div>
                </div>

                <div>
                    <div class="text-muted small">Deskripsi</div>
                    <div id="publicEventDescription"
                         style="white-space: pre-wrap;"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
<body>


    <nav class="navbar public-navbar px-3 px-md-5 py-3">
        <div class="container-fluid">
            <a href="{{ url('/calendar') }}"
            class="navbar-brand fw-bold text-primary">
                RAPI-Sekolah
            </a>

            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary small d-none d-md-inline">
                    Kalender Kegiatan Sekolah
                </span>

                <a href="{{ url('/login') }}"
                class="btn btn-primary px-4">
                    <i class="bx bx-log-in me-1"></i>
                    Login
                </a>
            </div>
        </div>
    </nav>


    <main class="container-fluid px-3 px-md-5 py-4">
        <div class="calendar-card p-3 p-md-4">
            <h1 class="h4 fw-semibold mb-2">
                Kalender Kegiatan
            </h1>

            <p class="text-secondary mb-4">
                Informasi jadwal kegiatan dan penggunaan ruangan sekolah.
            </p>

            <div id="calendar"></div>
        </div>
    </main>

    <footer class="text-center text-secondary py-4 small">
        &copy; {{ date('Y') }} RAPI-Sekolah
    </footer>

    <div class="modal fade" id="eventModal" tabindex="-1"
         aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">
                        Detail Kegiatan
                    </h5>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <h5 id="eventTitle"></h5>

                    <p class="mb-2">
                        <strong>Tanggal:</strong>
                        <span id="eventDate"></span>
                    </p>

                    <p class="mb-0">
                        <strong>Ruangan:</strong>
                        <span id="eventRoom"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/locales/id.global.min.js"></script>
    


<script>
    document.addEventListener('DOMContentLoaded', function () {
        let holidayDates = new Set();

        function formatLocalDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }
        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            dayCellClassNames: function (arg) {
                return holidayDates.has(formatLocalDate(arg.date))
                    ? ['holiday-day']
                    : [];
            },
            initialView: 'dayGridMonth',
            locale: 'id',
            firstDay: 0,
            height: 'auto',
            dayMaxEvents: true,

            headerToolbar: {
                left: 'title',
                right: 'today prev,next'
            },

            buttonText: {
                today: 'Hari ini'
            },
              eventClick: function (info) {
            const event = info.event;
            const props = event.extendedProps;
            if (props.isHoliday) {
                alert(
                    `${event.title}\n` +
                    `Tanggal: ${event.start.toLocaleDateString('id-ID')}\n` +
                    `Jenis: ${props.type}\n` +
                    `Keterangan: ${props.description || '-'}`
                );

                return;
            }

            const formatDate = (date) => {
                if (!date) return '-';

                return date.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
            };

            const formatTime = (date) => {
                if (!date) return '-';

                return date.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
            };

            document.getElementById('publicEventTitle').textContent =
                event.title || '-';

            document.getElementById('publicEventDate').textContent =
                formatDate(event.start);

            document.getElementById('publicEventTime').textContent =
                `${formatTime(event.start)} - ${formatTime(event.end)}`;

            document.getElementById('publicEventRoom').textContent =
                props.room || 'Belum ditentukan';

            document.getElementById('publicEventDescription').textContent =
                props.description || 'Tidak ada deskripsi.';

            bootstrap.Modal
                .getOrCreateInstance(
                    document.getElementById('publicEventModal')
                )
                .show();
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

                    const [eventResponse, holidayResponse] = await Promise.all([
                        fetch(`/api/public/events?${params.toString()}`, {
                            headers: {
                                Accept: 'application/json'
                            }
                        }),
                        fetch('/api/holidays', {
                            headers: {
                                Accept: 'application/json'
                            }
                        })
                    ]);

                    if (!eventResponse.ok || !holidayResponse.ok) {
                        throw new Error('Gagal mengambil data kalender.');
                    }

                    const [eventJson, holidayJson] = await Promise.all([
                        eventResponse.json(),
                        holidayResponse.json()
                    ]);

                    if (!eventJson.success || !Array.isArray(eventJson.data)) {
                        throw new Error('Format data kegiatan tidak sesuai.');
                    }

                    if (!holidayJson.success || !Array.isArray(holidayJson.data)) {
                        throw new Error('Format data hari libur tidak sesuai.');
                    }

                    const events = eventJson.data.map(event => ({
                        id: event.id,
                        title: event.title,
                        start: event.start,
                        end: event.end,
                        extendedProps: {
                            room: event.room?.name ?? 'Belum ditentukan',
                            description: event.description ?? '',
                            isHoliday: false
                        }
                    }));

                    const holidays = holidayJson.data.map(holiday => {
                        const holidayDate = holiday.date.substring(0, 10);

                        // Simpan tanggal libur agar angka tanggal berwarna merah
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

                    successCallback([...events, ...holidays]);

                } catch (error) {
                    console.error('Gagal memuat kalender:', error);
                    failureCallback(error);
                }
            }
        });

        calendar.render();
    });
</script>

</body>
</html>
