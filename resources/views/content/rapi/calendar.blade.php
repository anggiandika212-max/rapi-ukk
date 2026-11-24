 @extends('layouts/contentNavbarLayout')

@section('content')

<div class="calendar-page">
    {{-- Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="calendar-stat-card">
                <div class="stat-icon bg-primary-subtle text-primary">
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
                <div class="stat-icon bg-danger-subtle text-danger">
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
    <div class="card calendar-card border-0">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="calendar-eyebrow">RAPI-SEKOLAH</span>
                    <h3 class="fw-bold mb-1">Kalender Agenda Sekolah</h3>
                    <p class="text-muted mb-0">
                    Pantau jadwal kegiatan, agenda, dan hari libur sekolah.
                    </p>
                    <small class="text-muted">
                        Pilih tanggal atau kegiatan untuk melihat detail.
                    </small>
                </div>

                <div class="calendar-legend">
                    <span><i class="legend-dot bg-primary"></i> Kegiatan</span>
                    <span><i class="legend-dot bg-danger"></i> Hari Libur</span>
                </div>
            </div>
        </div>

        <div class="card-body px-4 pb-4">
            <div id="calendar"></div>
        </div>
    </div>

</div>

{{-- Modal Detail --}}
<div class="modal fade" id="calendarDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detail Agenda</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h5 id="detailTitle" class="fw-bold mb-3"></h5>
                <div id="detailContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-script')

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<style>
.calendar-page {
    padding-bottom: 25px;
}

.calendar-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.calendar-eyebrow {
    color: #696cff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
}

.calendar-heading h3 {
    margin-top: 5px;
}

.calendar-icon {
    width: 58px;
    height: 58px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #696cff;
    color: white;
    border-radius: 16px;
    font-size: 29px;
    box-shadow: 0 5px 15px rgba(105,108,255,.25);
}

.calendar-stat-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    height: 100%;
    background: var(--bs-card-bg, #fff);
    border: 1px solid var(--bs-border-color, #eee);
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

.calendar-stat-card span {
    color: #8592a3;
    font-size: 13px;
}

.calendar-stat-card h3 {
    margin: 3px 0 0;
    font-weight: 700;
}

.calendar-card {
    border-radius: 16px;
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

.fc .fc-toolbar {
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 22px !important;
}

.fc .fc-toolbar-title {
    font-size: 21px;
    font-weight: 700;
    text-transform: capitalize;
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

.fc .fc-daygrid-day {
    transition: background .2s;
}

.fc .fc-daygrid-day:hover {
    background: rgba(105,108,255,.045);
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

.fc .fc-day-sun .fc-col-header-cell-cushion,
.fc .fc-day-sun .fc-daygrid-day-number {
    color: #dc3545!important;
}

.fc .fc-day-sun {
    background: #fff8f8;
}

.holiday-day .fc-daygrid-day-number {
    color: #dc3545 !important;
    font-weight: 800;
}

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

.calendar-detail-row {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 15px;
}

.calendar-detail-row i {
    color: #696cff;
    font-size: 20px;
}

@media (max-width: 767px) {
    .calendar-heading h3 {
        font-size: 21px;
    }

    .calendar-icon {
        width: 48px;
        height: 48px;
        font-size: 23px;
    }

    .calendar-card .card-header,
    .calendar-card .card-body {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    .fc .fc-toolbar-title {
        font-size: 17px;
    }

    .fc .fc-toolbar {
        justify-content: center;
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
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('calendar');

    if (!calendarEl) return;

    let holidayDates = new Set();

    function formatLocalDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function showCalendarDetail(event) {
        const props = event.extendedProps;

        document.getElementById('detailTitle').textContent = event.title;

        const dateText = event.start
            ? event.start.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            })
            : '-';

        let content = `
            <div class="calendar-detail-row">
                <i class="bx bx-calendar"></i>
                <div>
                    <small class="text-muted">Tanggal</small>
                    <div class="fw-semibold">${dateText}</div>
                </div>
            </div>
        `;

        if (props.isHoliday) {
            content += `
                <div class="calendar-detail-row">
                    <i class="bx bx-info-circle"></i>
                    <div>
                        <small class="text-muted">Jenis Hari Libur</small>
                        <div class="fw-semibold">${props.type || '-'}</div>
                    </div>
                </div>
                <div class="calendar-detail-row">
                    <i class="bx bx-note"></i>
                    <div>
                        <small class="text-muted">Keterangan</small>
                        <div>${props.description || '-'}</div>
                    </div>
                </div>
            `;
        } else {
            content += `
                <div class="calendar-detail-row">
                    <i class="bx bx-door-open"></i>
                    <div>
                        <small class="text-muted">Ruangan</small>
                        <div class="fw-semibold">${props.roomName || '-'}</div>
                    </div>
                </div>
                <div class="calendar-detail-row">
                    <i class="bx bx-check-circle"></i>
                    <div>
                        <small class="text-muted">Status</small>
                        <div class="fw-semibold">${props.status || '-'}</div>
                    </div>
                </div>
            `;
        }

        document.getElementById('detailContent').innerHTML = content;

        const modalElement = document.getElementById('calendarDetailModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',
        locale: 'id',
        height: 'auto',
        firstDay: 1,
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

        events: async function (info, successCallback, failureCallback) {
            try {
                const params = new URLSearchParams({
                    start: info.startStr,
                    end: info.endStr
                });

                const [eventResult, holidayResult] = await Promise.all([
                    rapiApi(`/api/events/calendar?${params.toString()}`),
                    rapiApi('/api/holidays')
                ]);

                if (!eventResult || !Array.isArray(eventResult.data)) {
                    throw new Error('Format data kegiatan tidak valid.');
                }

                if (!holidayResult || !Array.isArray(holidayResult.data)) {
                    throw new Error('Format data hari libur tidak valid.');
                }

                holidayDates = new Set(
                    holidayResult.data.map(item =>
                        String(item.date).substring(0, 10)
                    )
                );

                const events = eventResult.data.map(event => {

                    let color = '#696cff';

                    if (event.status === 'Approved') {
                        color = '#28a745';
                    } else if (event.status === 'Pending') {
                        color = '#ffab00';
                    } else if (event.status === 'Rejected') {
                        color = '#dc3545';
                    }

                    return {
                        id: event.id,
                        title: event.room?.name
                            ? `${event.title} - ${event.room.name}`
                            : event.title,
                        start: event.start,
                        end: event.end,
                        backgroundColor: color,
                        borderColor: color,
                        extendedProps: {
                            status: event.status,
                            roomName: event.room?.name ?? '-',
                            isHoliday: false
                        }
                    };
                });

                const holidays = holidayResult.data.map(holiday => ({
                    id: `holiday-${holiday.id}`,
                    title: `Libur: ${holiday.name}`,
                    start: String(holiday.date).substring(0, 10),
                    allDay: true,
                    backgroundColor: '#dc3545',
                    borderColor: '#dc3545',
                    textColor: '#ffffff',
                    extendedProps: {
                        isHoliday: true,
                        type: holiday.type,
                        description: holiday.description
                    }
                }));

                document.getElementById('totalEvents').textContent =
                    events.length;

                document.getElementById('totalHolidays').textContent =
                    holidays.length;

                successCallback([...events, ...holidays]);

                setTimeout(() => calendar.rerenderDates(), 0);

            } catch (error) {
                console.error('Gagal memuat kalender:', error);
                failureCallback(error);
            }
        },

        eventClick: function (info) {
            showCalendarDetail(info.event);
        }
    });

    calendar.render();
});
</script>

@endsection