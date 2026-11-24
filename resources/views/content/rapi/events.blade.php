@extends('layouts/contentNavbarLayout')

@section('title', 'Data Event')

@section('content')
<div
    id="eventConfig"
    data-complaint-url="{{ route('rapi.complaints') }}"
    hidden>
</div>

<div class="card">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h5 class="mb-1">Data Event</h5><small class="text-muted">Event Approved sudah menjadi jadwal resmi dan tidak
        dapat diedit biasa.</small>
    </div><button id="addEventBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal"
      onclick="openCreateEvent()"><i class="bx bx-plus me-1"></i>Tambah Event</button>
  </div>

  <div class="card-body border-bottom">
    <div class="row g-3">
      <div class="col-md-5"><input id="eventSearch" class="form-control" placeholder="Cari judul/deskripsi..."></div>
      <div class="col-md-3"><select id="eventStatus" class="form-select">
          <option value="">Semua Status</option>
          <option>Pending</option>
          <option>Approved</option>
          <option>Rejected</option>
          <option>Cancelled</option>
          <option>Completed</option>
        </select></div>
      <div class="col-md-2"><input id="eventDate" type="date" class="form-control"></div>
      <div class="col-md-2"><button class="btn btn-outline-secondary w-100" onclick="loadEvents(1)">Filter</button></div>
    </div>
  </div>

  <div id="eventAlert" class="alert d-none m-3 mb-0"></div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Event</th>
          <th>Tanggal</th>
          <th>Waktu</th>
          <th>Ruangan</th>
          <th>Penyelenggara</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody id="eventRows">
        <tr>
          <td colspan="7" class="text-center py-5 text-muted">Memuat data...</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div id="eventPagination" class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2"></div>
</div>


<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="eventForm">

                <!-- Header Modal -->
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalTitle">
                        Tambah Event
                    </h5>
                    <button class="btn-close" type="button"
                        data-bs-dismiss="modal"></button>
                </div>

                <!-- Body Modal -->
                <div class="modal-body">
                    <div id="eventFormAlert" class="alert d-none"></div>
                    <input id="eventId" type="hidden">

                    <div class="row g-4">

                        <div class="col-md-8">
                            <label class="form-label">Judul Event</label>
                            <input id="eventTitleInput"
                                class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Ruangan</label>
                            <select id="eventRoomInput"
                                class="form-select" required></select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea id="eventDescriptionInput"
                                class="form-control" rows="3"></textarea>
                        </div>
                       
                        <div class="col-md-4">
                            <label for="eventDateInput" class="form-label">
                                Tanggal
                            </label>
                            <input id="eventDateInput" type="date"
                                class="form-control" required>
                            <small class="text-muted">
                                Peminjaman ruang harus dilakukan minimal H-1
                            </small>
                        </div>

                        <div class="col-md-4">
                            <label for="eventStartInput" class="form-label">
                                Jam Mulai
                            </label>

                            <!-- Dropdown untuk Senin-Jumat -->
                            <select class="form-select" id="eventStartInput">
                                <option value="">Pilih Sesi Mulai</option>
                            </select>

                            <!-- Input manual untuk Sabtu-Minggu -->
                            <input type="time"
                                class="form-control d-none"
                                id="eventStartManualInput"
                                step="60">
                        </div>

                        <div class="col-md-4">
                            <label for="eventEndInput" class="form-label">
                                Jam Selesai
                            </label>

                            <!-- Dropdown untuk Senin-Jumat -->
                            <select class="form-select" id="eventEndInput">
                                <option value="">Pilih Sesi Selesai</option>
                            </select>

                            <!-- Input manual untuk Sabtu-Minggu -->
                            <input type="time"
                                class="form-control d-none"
                                id="eventEndManualInput"
                                step="60">
                        </div>


                    </div> <!-- Penutup row g-4 -->
                </div> <!-- Penutup modal-body -->

                <!-- Footer Modal -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button class="btn btn-primary" type="submit"
                        id="eventSubmitBtn">
                        Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{--modal tambah dan edit event--}}
<div class="modal fade" id="moveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="moveForm">
        <div class="modal-header">
          <h5 class="modal-title">Pindah Ruangan Event</h5><button class="btn-close" type="button"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="moveFormAlert" class="alert d-none"></div><input id="moveEventId" type="hidden"><label
            class="form-label">Ruangan Baru</label><select id="moveRoomInput" class="form-select mb-4"
            required></select><label class="form-label">Alasan</label><textarea id="moveReasonInput"
            class="form-control" rows="4" maxlength="1000" required></textarea>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary"
            data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" type="submit">Pindahkan</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Alasan Penolakan Event -->
<div class="modal fade" id="rejectEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="rejectEventForm">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Tolak Pengajuan Event
                    </h5>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="rejectEventId">

                    <div id="rejectEventAlert"
                        class="alert d-none"></div>

                    <label for="rejectEventReasonInput"
                        class="form-label">
                        Alasan Penolakan
                    </label>

                    <textarea
                        id="rejectEventReasonInput"
                        class="form-control"
                        rows="4"
                        maxlength="2000"
                        placeholder="Tuliskan alasan mengapa event ditolak..."
                        required></textarea>

                    <small class="text-muted">
                        Admin wajib memberikan alasan sebelum menolak event.
                    </small>

                </div>

                <div class="modal-footer">

                    <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                        class="btn btn-danger"
                        id="rejectEventSubmitBtn">
                        Tolak Event
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('js/rapi.js') }}"></script>
<script>
const isAuthenticated = rapiRequireAuth();
const currentRole = rapiRole();

const complaintPageUrl = document
    .getElementById('eventConfig')
    .dataset.complaintUrl;

let events = [],
  eventRooms = [];

const EVENT_PER_PAGE = 10;
let eventCurrentPage = 1;
let eventPagination = {
  current_page: 1,
  last_page: 1,
  per_page: EVENT_PER_PAGE,
  total: 0,
  from: 0,
  to: 0
};

// Mendukung format paginator Laravel langsung maupun yang dibungkus dalam key data.
function getEventPaginationResponse(json) {
  // Backend EventController mengirim metadata pada json.pagination.
  // Struktur ini juga mendukung respons yang dibungkus dalam key data.
  let rows = [];

  if (Array.isArray(json?.data)) {
    rows = json.data;
  } else if (Array.isArray(json?.data?.data)) {
    rows = json.data.data;
  } else if (Array.isArray(json?.data?.data?.data)) {
    rows = json.data.data.data;
  }

  const meta =
    json?.pagination ||
    json?.data?.pagination ||
    json?.meta ||
    json?.data?.meta ||
    (json?.data && !Array.isArray(json.data) ? json.data : {}) ||
    {};

  const currentPage = Number(
    meta.current_page ?? meta.currentPage ?? eventCurrentPage
  );
  const perPage = Number(
    meta.per_page ?? meta.perPage ?? EVENT_PER_PAGE
  );
  const total = Number(meta.total ?? rows.length);
  const lastPage = Number(
    meta.last_page ??
    meta.lastPage ??
    Math.max(1, Math.ceil(total / perPage))
  );

  return {
    rows,
    pagination: {
      current_page: currentPage,
      last_page: Math.max(1, lastPage),
      per_page: perPage,
      total,
      from: Number(
        meta.from ?? (total ? ((currentPage - 1) * perPage) + 1 : 0)
      ),
      to: Number(
        meta.to ?? (total ? ((currentPage - 1) * perPage) + rows.length : 0)
      )
    }
  };
}

//menampilkan navgiasi pagnation
function renderEventPagination() {
  const container = document.getElementById('eventPagination');
  const page = eventPagination.current_page;
  const lastPage = eventPagination.last_page;
  const total = eventPagination.total;

  container.innerHTML = `
    <small class="text-muted">
      Menampilkan ${eventPagination.from}–${eventPagination.to} dari ${total} event
    </small>
    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-sm btn-outline-primary"
        onclick="loadEvents(${page - 1})" ${page <= 1 ? 'disabled' : ''}>
        <i class="bx bx-chevron-left"></i> <<
      </button>
      <span class="small text-muted">Halaman ${page} dari ${lastPage}</span>
      <button type="button" class="btn btn-sm btn-outline-primary"
        onclick="loadEvents(${page + 1})" ${page >= lastPage ? 'disabled' : ''}>
        >> <i class="bx bx-chevron-right"></i>
      </button>
    </div>
  `;
}
const eventModal = () => bootstrap.Modal
  .getOrCreateInstance(document.getElementById('eventModal'));
const moveModal = () => bootstrap.Modal
  .getOrCreateInstance(document.getElementById('moveModal'));

function showEventAlert(type, msg) {
  const e = document.getElementById('eventAlert');
  e.className = `alert alert-${type} m-3 mb-0`;
  e.textContent = msg;
}

//mengambil data ruangan dari API
async function loadRoomsForEvents() {
    const roomSelect = document.getElementById('eventRoomInput');
    const moveRoomSelect = document.getElementById('moveRoomInput');

    roomSelect.innerHTML =
        '<option value="">Memuat data ruangan...</option>';

    try {
        const json = await rapiApi('/api/rooms?per_page=100');

        console.log('Respons API ruangan:', json);

        if (Array.isArray(json.data)) {
            eventRooms = json.data;
        } else if (Array.isArray(json.data?.data)) {
            eventRooms = json.data.data;
        } else {
            eventRooms = [];
        }

        const availableRooms = eventRooms.filter(
            room => room.status !== 'Maintenance'
        );

        if (['Admin', 'Guru'].includes(currentRole)) {
            roomSelect.innerHTML =
                '<option value="">Pilih Ruangan</option>';

            availableRooms.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = room.room_name;
                roomSelect.appendChild(option);
            });

            if (availableRooms.length === 0) {
                roomSelect.innerHTML =
                    '<option value="">Tidak ada ruangan tersedia</option>';
            }
        }

        if (currentRole === 'Admin') {
            moveRoomSelect.innerHTML =
                '<option value="">Pilih Ruangan Baru</option>';

            availableRooms.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = room.room_name;
                moveRoomSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Gagal memuat ruangan:', error);

        roomSelect.innerHTML =
            '<option value="">Gagal memuat data ruangan</option>';

        throw error;
    }
}

function getEventSessions(date) {
    if (!date) return [];

    const [year, month, day] = date.split('-').map(Number);
    const selectedDate = new Date(year, month - 1, day);
    const dayOfWeek = selectedDate.getDay();

    let duration;

    if (dayOfWeek >= 1 && dayOfWeek <= 4) {
        duration = 45;
    } else if (dayOfWeek === 5) {
        duration = 35;
    } else {
        return [];
    }

    const sessions = [];
    const startMinutes = 7 * 60;

    function formatTime(minutes) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;

        return `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`;
    }

    for (let i = 0; i < 10; i++) {
        const start = startMinutes + (i * duration);
        const end = start + duration;

        sessions.push({
            session: i + 1,
            start_time: formatTime(start),
            end_time: formatTime(end)
        });
    }

    return sessions;
}


function populateEventSessions(selectedStart = '', selectedEnd = '') {
    const date = document.getElementById('eventDateInput').value;

    const startSelect = document.getElementById('eventStartInput');
    const endSelect = document.getElementById('eventEndInput');

    const startManual = document.getElementById('eventStartManualInput');
    const endManual = document.getElementById('eventEndManualInput');

    let dayOfWeek = -1;

    if (date) {
        const [year, month, day] = date.split('-').map(Number);
        dayOfWeek = new Date(year, month - 1, day).getDay();
    }

    // Sabtu = 6, Minggu = 0
    const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

    // Atur tampilan dropdown dan input manual
    startSelect.classList.toggle('d-none', isWeekend);
    endSelect.classList.toggle('d-none', isWeekend);

    startManual.classList.toggle('d-none', !isWeekend);
    endManual.classList.toggle('d-none', !isWeekend);

    // Aktifkan hanya input yang sesuai dengan hari
    startSelect.disabled = !date || isWeekend;
    endSelect.disabled = !date || isWeekend;

    startManual.disabled = !date || !isWeekend;
    endManual.disabled = !date || !isWeekend;

    startSelect.required = !!date && !isWeekend;
    endSelect.required = !!date && !isWeekend;

    startManual.required = !!date && isWeekend;
    endManual.required = !!date && isWeekend;

    // Reset dropdown dan input manual
    startSelect.innerHTML =
        '<option value="">Pilih Sesi Mulai</option>';

    endSelect.innerHTML =
        '<option value="">Pilih Sesi Selesai</option>';

    startManual.value = selectedStart;
    endManual.value = selectedEnd;

    // Jika tanggal belum dipilih, hentikan
    if (!date) return;

    // Sabtu dan Minggu menggunakan input jam manual
    if (isWeekend) {
        return;
    }

    // Senin-Jumat menggunakan dropdown sesi
    const sessions = getEventSessions(date);

    if (sessions.length === 0) {
        return;
    }

    sessions.forEach(session => {
        const startOption = document.createElement('option');

        startOption.value = session.start_time;
        startOption.textContent =
            `Sesi ${session.session} - ${session.start_time}`;

        startSelect.appendChild(startOption);

        const endOption = document.createElement('option');

        endOption.value = session.end_time;
        endOption.textContent =
            `Sesi ${session.session} - ${session.end_time}`;

        endSelect.appendChild(endOption);
    });

    // Mempertahankan jam event saat membuka form edit
    startSelect.value = selectedStart;
    endSelect.value = selectedEnd;
}

//fungsi membuka form tamabh event
function openCreateEvent() {
  if (!['Admin', 'Guru'].includes(currentRole)) return;
  document.getElementById('eventForm').reset();
  document.getElementById('eventId').value = '';
  document.getElementById('eventModalTitle').textContent = 'Tambah Event';
  const today = new Date();
  const localDate =
      today.getFullYear() + '-' +
      String(today.getMonth() + 1).padStart(2, '0') + '-' +
      String(today.getDate()).padStart(2, '0');

  document.getElementById('eventDateInput').value = localDate;
  document.getElementById('eventFormAlert').className = 'alert d-none';
  document.getElementById('eventStartInput').value = '';
  document.getElementById('eventEndInput').value = '';
  populateEventSessions();
}

//fungsi membuka form edit event
function openEditEvent(id) {
  if (!['Admin', 'Guru'].includes(currentRole)) return;
  const e = events.find(item => item.id === id);
  if (!e) return;
  document.getElementById('eventForm').reset();
  document.getElementById('eventId').value = e.id;
  document.getElementById('eventTitleInput').value = e.title || '';
  document.getElementById('eventRoomInput').value = e.room?.id || '';
  document.getElementById('eventDescriptionInput').value = e.description || '';
  document.getElementById('eventDateInput').value = e.event_date || '';
  populateEventSessions(
      e.start_time?.slice(0, 5) || '',
      e.end_time?.slice(0, 5) || ''
  );
  document.getElementById('eventModalTitle').textContent = 'Edit Event';
  document.getElementById('eventFormAlert').className = 'alert d-none';
  eventModal().show();
}

//mengambil daftar event dari API
async function loadEvents(page = eventCurrentPage) {
  try {
    eventCurrentPage = Math.max(1, Number(page) || 1);

    const p = new URLSearchParams({
      page: eventCurrentPage,
      per_page: EVENT_PER_PAGE
    });

    const q = document.getElementById('eventSearch').value.trim();
    const s = document.getElementById('eventStatus').value;
    const d = document.getElementById('eventDate').value;

    if (q) p.set('search', q);
    if (s) p.set('status', s);
    if (d) p.set('event_date', d);

    const json = await rapiApi(`/api/events?${p.toString()}`);
    const result = getEventPaginationResponse(json);

    events = result.rows;
    eventPagination = result.pagination;

    // Jika halaman terakhir kosong setelah data dihapus, kembali ke halaman sebelumnya.
    if (events.length === 0 && eventCurrentPage > 1 && eventCurrentPage > eventPagination.last_page) {
      return loadEvents(eventPagination.last_page);
    }

    console.log('Jumlah event pada halaman:', events.length);
    console.log('Pagination event:', eventPagination);

    renderEvents();
    renderEventPagination();

  } catch (error) {
    console.error('Gagal memuat events:', error);

    document.getElementById('eventRows').innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger py-4">
                    Gagal memuat data: ${rapiEscape(error.message)}
                </td>
            </tr>
        `;
    document.getElementById('eventPagination').innerHTML = '';
  }
}

//menampilkan isi tabel event
function renderEvents() {
  const eventRows = document.getElementById('eventRows');

  if (!events.length) {
    eventRows.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    Tidak ada event.
                </td>
            </tr>
        `;
    return;
  }

  eventRows.innerHTML = events.map(e => {
    const own = Number(e.user?.id) === Number(rapiUserId());
    let actions = '';

    // Tombol persetujuan dan penolakan untuk Admin.
    if (currentRole === 'Admin' && e.status === 'Pending') {
      actions += `
                <button
                    type="button"
                    class="btn btn-sm btn-outline-success me-1"
                    onclick="processEvent(${e.id}, 'Approved')"
                    title="Setujui">
                    <i class="bx bx-check"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger me-1"
                    onclick="processEvent(${e.id}, 'Rejected')"
                    title="Tolak">
                    <i class="bx bx-x"></i>
                </button>
            `;
    }

    // Tombol pindah ruangan untuk event yang disetujui.
    if (currentRole === 'Admin' && e.status === 'Approved') {
      actions += `
                <button
                    type="button"
                    class="btn btn-sm btn-outline-info me-1"
                    onclick="openMoveEvent(${e.id})"
                    title="Pindahkan ruangan">
                    <i class="bx bx-transfer"></i>
                </button>
            `;
    }

    // Tombol edit dan hapus untuk Admin atau pemilik event (Guru).
    if (
      (currentRole === 'Admin' || (currentRole === 'Guru' && own)) &&
      e.status === 'Pending'
    ) {
      actions += `
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary me-1"
                    onclick="openEditEvent(${e.id})"
                    title="Edit">
                    <i class="bx bx-edit"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    onclick="deleteEvent(${e.id})"
                    title="Hapus">
                    <i class="bx bx-trash"></i>
                </button>
            `;
    }

    // Tombol Complaint untuk Admin dan Guru
  if (['Admin', 'Guru'].includes(currentRole)) {
      actions += `
          <button
              type="button"
              class="btn btn-sm btn-outline-warning me-1"
              onclick="openEventComplaint(${e.id})"
              title="Buat Pengaduan">
              <i class="bx bx-message-square-error"></i>
          </button>
      `;
  }

    return `
            <tr>
                <td>
                    <div class="fw-semibold">
                        ${rapiEscape(e.title)}
                    </div>
                    <small class="text-muted">
                        ${rapiEscape(e.description || '-')}
                    </small>
                    ${e.status === 'Rejected' && e.rejection_reason ? `
                      <small class="d-block text-danger mt-2">
                          <strong>Alasan penolakan:</strong>
                          ${rapiEscape(e.rejection_reason)}
                      </small>
                  ` : ''}
                </td>

                <td>${rapiFormatDate(e.event_date)}</td>

                <td>
                    ${rapiEscape((e.start_time || '').slice(0, 5))}
                    -
                    ${rapiEscape((e.end_time || '').slice(0, 5))}
                </td>

                <td>${rapiEscape(e.room?.room_name || '-')}</td>

                <td>${rapiEscape(e.user?.name || '-')}</td>

                <td>${rapiBadge(e.status)}</td>

                <td class="text-end">${actions || '-'}</td>
            </tr>
        `;
  }).join('');
}

// Membuka form pengaduan berdasarkan event
function openEventComplaint(id) {
    const event = events.find(
        item => Number(item.id) === Number(id)
    );

    if (!event) {
        showEventAlert('danger', 'Data event tidak ditemukan.');
        return;
    }

    if (!event.room?.id) {
        showEventAlert('danger', 'Ruangan event tidak ditemukan.');
        return;
    }

    const url = new URL(complaintPageUrl, window.location.origin);

    url.searchParams.set('room_id', event.room.id);
    url.searchParams.set('event_title', event.title || '');

    window.location.href = url.toString();
}
// Modal alasan penolakan
const rejectEventModal = () => bootstrap.Modal
    .getOrCreateInstance(
        document.getElementById('rejectEventModal')
    );

// Proses persetujuan dan penolakan event
async function processEvent(id, status) {
    if (status === 'Rejected') {
        document.getElementById('rejectEventForm').reset();
        document.getElementById('rejectEventId').value = id;

        const alertBox = document.getElementById('rejectEventAlert');
        alertBox.className = 'alert d-none';
        alertBox.textContent = '';

        rejectEventModal().show();
        return;
    }

    if (!confirm('Setujui event ini?')) return;

    try {
        const json = await rapiApi(`/api/events/${id}/approval`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: 'Approved'
            })
        });

        showEventAlert('success', json.message);
        loadEvents();

    } catch (err) {
        showEventAlert('danger', err.message);
    }
}

// Kirim alasan penolakan ke API
if (currentRole === 'Admin') {
    document.getElementById('rejectEventForm')
        .addEventListener('submit', async e => {
            e.preventDefault();

            const id = document.getElementById('rejectEventId').value;
            const reason = document.getElementById('rejectEventReasonInput')
                .value.trim();

            const alertBox = document.getElementById('rejectEventAlert');

            if (!reason) {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = 'Alasan penolakan wajib diisi.';
                return;
            }

            const button = document.getElementById('rejectEventSubmitBtn');
            button.disabled = true;

            try {
                const json = await rapiApi(`/api/events/${id}/approval`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        status: 'Rejected',
                        rejection_reason: reason
                    })
                });

                rejectEventModal().hide();
                showEventAlert('success', json.message);
                loadEvents();

            } catch (err) {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = err.message;

            } finally {
                button.disabled = false;
            }
        });
}

// Fungsi menghapus event
async function deleteEvent(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus event ini?')) {
        return;
    }

    try {
        const json = await rapiApi(`/api/events/${id}`, {
            method: 'DELETE'
        });

        showEventAlert('success', json.message);
        loadEvents();

    } catch (err) {
        showEventAlert('danger', err.message);
    }
}

//memnbuka modal pindah ruangan
function openMoveEvent(id) {
  if (currentRole !== 'Admin') return;
  const e = events.find(item => item.id === id);
  document.getElementById('moveEventId').value = id;
  document.getElementById('moveRoomInput').value = e?.room?.id || '';
  document.getElementById('moveReasonInput').value = '';
  document.getElementById('moveFormAlert').className = 'alert d-none';
  moveModal().show();
}
//proses pemindahan ruang
if (currentRole === 'Admin') {
  document.getElementById('moveForm').addEventListener('submit', async e => {
    e.preventDefault();
    try {
      const json = await rapiApi(`/api/events/${document.getElementById('moveEventId').value}/move`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          room_id: Number(document.getElementById('moveRoomInput').value),
          move_reason: document.getElementById('moveReasonInput').value.trim()
        })
      });
      moveModal().hide();
      showEventAlert('success', json.message);
      loadEvents();
    } catch (err) {
      const a = document.getElementById('moveFormAlert');
      a.className = 'alert alert-danger';
      a.textContent = err.message;
    }
  });
}


//proses tambah dan edit event
if (['Admin', 'Guru'].includes(currentRole)) {
  document.getElementById('eventForm').addEventListener('submit', async e => {
    e.preventDefault();
    // VALIDASI JAM MULAI DAN JAM SELESAI
    const date = document.getElementById('eventDateInput').value;

    const [year, month, day] = date.split('-').map(Number);
    const dayOfWeek = date
        ? new Date(year, month - 1, day).getDay()
        : -1;

    // Sabtu = 6, Minggu = 0
    const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

    // Ambil jam sesuai jenis input
    const startTime = isWeekend
        ? document.getElementById('eventStartManualInput').value
        : document.getElementById('eventStartInput').value;

    const endTime = isWeekend
        ? document.getElementById('eventEndManualInput').value
        : document.getElementById('eventEndInput').value;

    // Validasi jam kosong
    if (!startTime || !endTime) {
        const a = document.getElementById('eventFormAlert');
        a.className = 'alert alert-danger';
        a.textContent = 'Silakan isi jam mulai dan jam selesai.';
        return;
    }

    // Validasi urutan jam
    if (endTime <= startTime) {
        const a = document.getElementById('eventFormAlert');
        a.className = 'alert alert-danger';
        a.textContent = 'Jam selesai harus lebih besar daripada jam mulai.';
        return;
    }

    const id = document.getElementById('eventId').value;

    const body = {
      room_id: Number(document.getElementById('eventRoomInput').value),
      title: document.getElementById('eventTitleInput').value.trim(),
      description: document.getElementById('eventDescriptionInput').value.trim() || null,
      event_date: document.getElementById('eventDateInput').value,
      start_time: startTime,
      end_time: endTime
    };

    const b = document.getElementById('eventSubmitBtn');
    b.disabled = true;

    try {
      const json = await rapiApi(id ? `/api/events/${id}` : '/api/events', {
        method: id ? 'PUT' : 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(body)
      });

      eventModal().hide();
      showEventAlert('success', json.message);
      loadEvents();

    } catch (err) {
      const a = document.getElementById('eventFormAlert');
      a.className = 'alert alert-danger';
      a.textContent = err.message;

    } finally {
      b.disabled = false;
    }
  });
}

//mengatur tombol tambah event berdasarkan role
const addEventBtn = document.getElementById('addEventBtn');
if (addEventBtn) {
  addEventBtn.classList.toggle('d-none', !['Admin', 'Guru'].includes(currentRole));
}

if (isAuthenticated) {
  loadRoomsForEvents()
    .then(loadEvents)
    .catch(e => showEventAlert('danger', e.message));
}

document.getElementById('eventDateInput').addEventListener('change', function () {
    populateEventSessions('', '');
});
</script>
@endsection