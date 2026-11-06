@extends('layouts/contentNavbarLayout')

@section('title', 'Data Event')

@section('content')
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
        <div class="modal-header">
          <h5 class="modal-title" id="eventModalTitle">Tambah Event</h5><button class="btn-close" type="button"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="eventFormAlert" class="alert d-none"></div><input id="eventId" type="hidden">
          <div class="row g-4">
            <div class="col-md-8"><label class="form-label">Judul Event</label><input id="eventTitleInput"
                class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Ruangan</label><select id="eventRoomInput"
                class="form-select" required></select></div>
            <div class="col-12"><label class="form-label">Deskripsi</label><textarea id="eventDescriptionInput"
                class="form-control" rows="3"></textarea></div>
            <div class="col-md-4"><label class="form-label">Tanggal</label><input id="eventDateInput" type="date"
                class="form-control" required><small class="text-muted">Peminjaman ruang harus dilakukan minimal H-1</small></div>
            <div class="col-md-4"><label class="form-label">Jam Mulai</label><input id="eventStartInput" type="time"
                class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Jam Selesai</label><input id="eventEndInput" type="time"
                class="form-control" required></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary"
            data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" type="submit"
            id="eventSubmitBtn">Simpan</button></div>
      </form>
    </div>
  </div>
</div>
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
@endsection

@section('page-script')
<script src="{{ asset('js/rapi.js') }}"></script>
<script>
const isAuthenticated = rapiRequireAuth();
const currentRole = rapiRole();

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

function openCreateEvent() {
  if (!['Admin', 'Guru'].includes(currentRole)) return;
  document.getElementById('eventForm').reset();
  document.getElementById('eventId').value = '';
  document.getElementById('eventModalTitle').textContent = 'Tambah Event';
  document.getElementById('eventDateInput').value = new Date().toISOString().slice(0, 10);
  document.getElementById('eventFormAlert').className = 'alert d-none';
}

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
  document.getElementById('eventStartInput').value = e.start_time?.slice(0, 5) || '';
  document.getElementById('eventEndInput').value = e.end_time?.slice(0, 5) || '';
  document.getElementById('eventModalTitle').textContent = 'Edit Event';
  document.getElementById('eventFormAlert').className = 'alert d-none';
  eventModal().show();
}

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

    return `
            <tr>
                <td>
                    <div class="fw-semibold">
                        ${rapiEscape(e.title)}
                    </div>
                    <small class="text-muted">
                        ${rapiEscape(e.description || '-')}
                    </small>
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
async function processEvent(id, status) {
  if (!confirm(status === 'Approved' ? 'Setujui event ini?' : 'Tolak event ini?')) return;
  try {
    const json = await rapiApi(`/api/events/${id}/approval`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        status
      })
    });
    showEventAlert('success', json.message);
    loadEvents();
  } catch (e) {
    showEventAlert('danger', e.message);
  }
}
async function deleteEvent(id) {
  if (!confirm('Anda yakin ingin menghapus event ini?')) return;
  try {
    const json = await rapiApi(`/api/events/${id}`, {
      method: 'DELETE'
    });
    showEventAlert('success', json.message);
    loadEvents();
  } catch (e) {
    showEventAlert('danger', e.message);
  }
}

function openMoveEvent(id) {
  if (currentRole !== 'Admin') return;
  const e = events.find(item => item.id === id);
  document.getElementById('moveEventId').value = id;
  document.getElementById('moveRoomInput').value = e?.room?.id || '';
  document.getElementById('moveReasonInput').value = '';
  document.getElementById('moveFormAlert').className = 'alert d-none';
  moveModal().show();
}
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

if (['Admin', 'Guru'].includes(currentRole)) {
  document.getElementById('eventForm').addEventListener('submit', async e => {
    e.preventDefault();
    const id = document.getElementById('eventId').value;
    const body = {
      room_id: Number(document.getElementById('eventRoomInput').value),
      title: document.getElementById('eventTitleInput').value.trim(),
      description: document.getElementById('eventDescriptionInput').value.trim() || null,
      event_date: document.getElementById('eventDateInput').value,
      start_time: document.getElementById('eventStartInput').value,
      end_time: document.getElementById('eventEndInput').value
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

const addEventBtn = document.getElementById('addEventBtn');
if (addEventBtn) {
  addEventBtn.classList.toggle('d-none', !['Admin', 'Guru'].includes(currentRole));
}

if (isAuthenticated) {
  loadRoomsForEvents()
    .then(loadEvents)
    .catch(e => showEventAlert('danger', e.message));
}

</script>
@endsection