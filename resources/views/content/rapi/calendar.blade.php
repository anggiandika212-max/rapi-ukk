@extends('layouts/contentNavbarLayout')

@section('content')

<div class="card">
  <div class="card-header">
    <h4>Kalender Kegiatan</h4>
  </div>

  <div class="card-body">
    <div id="calendar"></div>
  </div>
</div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<style>
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

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        height: 'auto',

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

                // Ambil kegiatan dan hari libur secara bersamaan
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

                // Simpan tanggal merah untuk pewarnaan angka tanggal
                holidayDates = new Set(
                    holidayResult.data.map(item => item.date.substring(0, 10))
                );

                // Data kegiatan sekolah
                const events = eventResult.data.map(event => ({
                    id: event.id,
                    title: event.room?.name
                        ? `${event.title} - ${event.room.name}`
                        : event.title,
                    start: event.start,
                    end: event.end,
                    extendedProps: {
                        status: event.status,
                        roomName: event.room?.name ?? '-',
                        isHoliday: false
                    }
                }));

                // Data hari libur nasional, cuti bersama, dan libur sekolah
                const holidays = holidayResult.data.map(holiday => ({
                    id: `holiday-${holiday.id}`,
                    title: `Libur: ${holiday.name}`,
                    start: holiday.date.substring(0, 10),
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

                calendar.rerenderDates();

                successCallback([...events, ...holidays]);

            } catch (error) {
                console.error('Gagal memuat kalender:', error);
                failureCallback(error);
            }
        },

        eventClick: function (info) {
            const event = info.event;
            const props = event.extendedProps;

            if (props.isHoliday) {
                alert(
                    `${event.title}\n` +
                    `Jenis: ${props.type}\n` +
                    `Tanggal: ${event.start.toLocaleDateString('id-ID')}\n` +
                    `Keterangan: ${props.description || '-'}`
                );
            }
        }
    });

    calendar.render();
});
</script>
@endsection