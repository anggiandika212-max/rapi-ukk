@extends('layouts/contentNavbarLayout')

@section('title', 'Data Ruangan')

@section('content')
<div class="card">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h5 class="mb-1">Data Ruangan</h5>
      <small class="text-muted">
        Ruangan yang dihapus menjadi nonaktif sehingga histori event dan complaint tetap ada.
      </small>
    </div>

    <button id="addRoomBtn" type="button" class="btn btn-primary d-none" data-bs-toggle="modal"
      data-bs-target="#roomModal" onclick="openCreateRoom()">
      <i class="bx bx-plus me-1"></i>Tambah Ruangan
    </button>
  </div>

  <div class="card-body border-bottom">
    <div class="row g-3">
      <div class="col-md-6">
        <input id="roomSearch" class="form-control" placeholder="Cari nama ruangan...">
      </div>
      <div class="col-md-3">
        <select id="roomStatus" class="form-select">
          <option value="">Semua Status</option>
          <option value="Available">Available</option>
          <option value="Booked">Booked</option>
          <option value="Maintenance">Maintenance</option>
        </select>
      </div>
      <div class="col-md-3">
        <button type="button" class="btn btn-outline-secondary w-100" onclick="loadRooms(1)">
          Filter
        </button>
      </div>
    </div>
  </div>

  <div id="roomAlert" class="alert d-none m-3 mb-0"></div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Ruangan</th>
          <th>PIC</th>
          <th>Fasilitas</th>
          <th>Kondisi</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody id="roomRows">
        <tr>
          <td colspan="6" class="text-center py-5 text-muted">
            Memuat data...
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div id="roomPagination" class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2"></div>
</div>

{{-- Modal selalu dirender; akses pengelolaannya dikendalikan oleh JavaScript. --}}
<div class="modal fade" id="roomModal" tabindex="-1" aria-labelledby="roomModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="roomForm">
        <div class="modal-header">
          <h5 class="modal-title" id="roomModalTitle">Tambah Ruangan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <div class="modal-body">
          <div id="roomFormAlert" class="alert d-none"></div>
          <input id="roomId" type="hidden">

          <div class="row g-4">
            <div class="col-md-6">
              <label for="roomNameInput" class="form-label">Nama Ruangan</label>
              <input id="roomNameInput" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label for="roomPicInput" class="form-label">PIC</label>
              <select id="roomPicInput" class="form-select" required></select>
            </div>

            <div class="col-md-6">
              <label for="roomFacilitiesInput" class="form-label">Fasilitas</label>
              <textarea id="roomFacilitiesInput" class="form-control" rows="3" required></textarea>
            </div>

            <div class="col-md-6">
              <label for="roomConditionInput" class="form-label">Kondisi</label>
              <input id="roomConditionInput" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label for="roomStatusInput" class="form-label">Status</label>
              <select id="roomStatusInput" class="form-select" required>
                <option value="Available">Available</option>
                <option value="Booked">Booked</option>
                <option value="Maintenance">Maintenance</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="roomPhotoInput" class="form-label">
                Path/URL Foto <span class="text-muted">(opsional)</span>
              </label>
              <input id="roomPhotoInput" class="form-control" placeholder="https://...">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Batal
          </button>
          <button class="btn btn-primary" type="submit" id="roomSubmitBtn">
            Simpan
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
let rooms = [];
let picUsers = [];

const ROOM_PER_PAGE = 10;
let roomCurrentPage = 1;
let roomLastPage = 1;
let roomTotal = 0;
let roomFrom = 0;
let roomTo = 0;

const currentRole = rapiRole();
const roomModal = () => bootstrap.Modal.getOrCreateInstance(
  document.getElementById('roomModal')
);

function showRoomAlert(type, message) {
  const alert = document.getElementById('roomAlert');
  alert.className = `alert alert-${type} m-3 mb-0`;
  alert.textContent = message;
}

function showRoomFormAlert(type, message) {
  const alert = document.getElementById('roomFormAlert');
  alert.className = `alert alert-${type}`;
  alert.textContent = message;
}

function getRoomPaginationResponse(json) {
  // Backend RoomController mengirim data[] dan metadata pada json.pagination.
  const payload = json?.data && !Array.isArray(json.data) &&
    typeof json.data === 'object' ? json.data : json;

  const data = Array.isArray(payload?.data) ? payload.data :
    (Array.isArray(json?.data) ? json.data : []);

  // Prioritaskan format respons aktual RoomController: { data: [...], pagination: {...} }.
  const meta = json?.pagination || payload?.pagination ||
    payload?.meta || json?.meta || payload || json || {};

  const currentPage = Number(meta.current_page ?? roomCurrentPage);
  const lastPage = Number(meta.last_page ?? 1);
  const total = Number(meta.total ?? data.length);
  const perPage = Number(meta.per_page ?? ROOM_PER_PAGE);

  return {
    data,
    currentPage,
    lastPage,
    total,
    from: total === 0 ? 0 : ((currentPage - 1) * perPage) + 1,
    to: Math.min(currentPage * perPage, total)
  };
}

function renderRoomPagination() {
  const container = document.getElementById('roomPagination');

  if (!roomTotal) {
    container.innerHTML = '<small class="text-muted">Tidak ada data ruangan.</small>';
    return;
  }

  container.innerHTML = `
    <small class="text-muted">
      Menampilkan ${roomFrom}–${roomTo} dari ${roomTotal} ruangan
    </small>
    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-sm btn-outline-secondary"
        ${roomCurrentPage <= 1 ? 'disabled' : ''}
        onclick="loadRooms(${roomCurrentPage - 1})">
        <<
      </button>
      <span class="small text-muted">
        Halaman ${roomCurrentPage} dari ${roomLastPage}
      </span>
      <button type="button" class="btn btn-sm btn-outline-secondary"
        ${roomCurrentPage >= roomLastPage ? 'disabled' : ''}
        onclick="loadRooms(${roomCurrentPage + 1})">
        >>
      </button>
    </div>
  `;
}

async function loadRooms(page = roomCurrentPage) {
  const rows = document.getElementById('roomRows');
  const pagination = document.getElementById('roomPagination');

  roomCurrentPage = Math.max(1, Number(page) || 1);

  rows.innerHTML = `
    <tr>
      <td colspan="6" class="text-center py-4">Memuat data...</td>
    </tr>
  `;
  pagination.innerHTML = '';

  try {
    const params = new URLSearchParams({
      page: roomCurrentPage,
      per_page: ROOM_PER_PAGE
    });
    const search = document.getElementById('roomSearch').value.trim();
    const status = document.getElementById('roomStatus').value;

    if (search) params.set('search', search);
    if (status) params.set('status', status);

    const json = await rapiApi(`/api/rooms?${params.toString()}`);
    const result = getRoomPaginationResponse(json);

    rooms = result.data;
    roomCurrentPage = result.currentPage;
    roomLastPage = Math.max(1, result.lastPage);
    roomTotal = result.total;
    roomFrom = result.from;
    roomTo = result.to;

    // Jika halaman terakhir menjadi kosong setelah data dinonaktifkan.
    if (rooms.length === 0 && roomCurrentPage > 1 && roomCurrentPage > roomLastPage) {
      await loadRooms(roomLastPage);
      return;
    }

    console.log('Jumlah ruangan:', rooms.length);
    console.log('Data ruangan:', rooms);

    renderRooms();
    renderRoomPagination();
  } catch (error) {
    console.error('Gagal memuat ruangan:', error);

    rows.innerHTML = `
      <tr>
        <td colspan="6" class="text-center text-danger py-4">
          Gagal memuat data: ${rapiEscape(error.message)}
        </td>
      </tr>
    `;
    pagination.innerHTML = '';
  }
}

function renderRooms() {
  const rows = document.getElementById('roomRows');

  if (!rooms.length) {
    rows.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    Tidak ada ruangan.
                </td>
            </tr>
        `;
    return;
  }

  rows.innerHTML = rooms.map(room => {
    const photo = room.photo ?
      `<a href="${rapiEscape(room.photo)}" target="_blank" rel="noopener noreferrer" class="small">Lihat foto</a>` :
      '';

    const actions = currentRole === 'Admin' ?
      `
                <button type="button" class="btn btn-sm btn-outline-primary me-1"
                    onclick="openEditRoom(${room.id})" title="Edit">
                    <i class="bx bx-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="deleteRoom(${room.id})" title="Nonaktifkan">
                    <i class="bx bx-trash"></i>
                </button>
            ` :
      '-';

    return `
            <tr>
                <td>
                    <div class="fw-semibold">${rapiEscape(room.room_name)}</div>
                    ${photo}
                </td>
                <td>${rapiEscape(room.pic?.name || 'Belum ada PIC')}</td>
                <td>${rapiEscape(room.facilities || '-')}</td>
                <td>${rapiEscape(room.facility_condition || '-')}</td>
                <td>${rapiBadge(room.status)}</td>
                <td class="text-end">${actions}</td>
            </tr>
        `;
  }).join('');
}

async function loadPicUsers() {
  const select = document.getElementById('roomPicInput');

  select.innerHTML = '<option value="">Memuat PIC...</option>';

  const json = await rapiApi('/api/users?per_page=100');

  const users = Array.isArray(json.data) ?
    json.data :
    (Array.isArray(json.data?.data) ? json.data.data : []);

  picUsers = users.filter(user =>
    (user.role?.role_name ?? user.role?.name ?? user.role_name) === 'PIC'
  );

  select.innerHTML = `
        <option value="">Pilih PIC</option>
        ${picUsers.map(user => `
            <option value="${user.id}">${rapiEscape(user.name)}</option>
        `).join('')}
    `;

  if (picUsers.length === 0) {
    showRoomFormAlert('warning', 'Belum ada pengguna dengan role PIC.');
  }
}

function openCreateRoom() {
  if (currentRole !== 'Admin') return;

  document.getElementById('roomForm').reset();
  document.getElementById('roomId').value = '';
  document.getElementById('roomModalTitle').textContent = 'Tambah Ruangan';
  document.getElementById('roomFormAlert').className = 'alert d-none';
}

function openEditRoom(id) {
  if (currentRole !== 'Admin') return;

  const room = rooms.find(item => Number(item.id) === Number(id));
  if (!room) return;

  document.getElementById('roomForm').reset();
  document.getElementById('roomId').value = room.id;
  document.getElementById('roomNameInput').value = room.room_name || '';
  document.getElementById('roomPicInput').value = room.pic?.id || '';
  document.getElementById('roomFacilitiesInput').value = room.facilities || '';
  document.getElementById('roomConditionInput').value = room.facility_condition || '';
  document.getElementById('roomStatusInput').value = room.status || 'Available';
  document.getElementById('roomPhotoInput').value = room.photo || '';
  document.getElementById('roomModalTitle').textContent = 'Edit Ruangan';
  document.getElementById('roomFormAlert').className = 'alert d-none';

  roomModal().show();
}

async function deleteRoom(id) {
  if (currentRole !== 'Admin') return;

  const room = rooms.find(item => Number(item.id) === Number(id));

  if (!room || !confirm(`Nonaktifkan ${room.room_name}? Histori event/complaint tetap tersimpan.`)) {
    return;
  }

  try {
    const json = await rapiApi(`/api/rooms/${id}`, {
      method: 'DELETE'
    });

    showRoomAlert('success', json.message || 'Ruangan berhasil dinonaktifkan.');
    await loadRooms();
  } catch (error) {
    showRoomAlert('danger', error.message);
  }
}

document.getElementById('roomForm').addEventListener('submit', async event => {
  event.preventDefault();

  if (currentRole !== 'Admin') {
    showRoomFormAlert('danger', 'Kamu tidak memiliki izin untuk mengelola ruangan.');
    return;
  }

  const id = document.getElementById('roomId').value;
  const picId = document.getElementById('roomPicInput').value;

  if (!picId) {
    showRoomFormAlert('danger', 'Silakan pilih PIC ruangan.');
    return;
  }

  const body = {
    pic_id: Number(picId),
    room_name: document.getElementById('roomNameInput').value.trim(),
    facilities: document.getElementById('roomFacilitiesInput').value.trim(),
    facility_condition: document.getElementById('roomConditionInput').value.trim(),
    status: document.getElementById('roomStatusInput').value,
    photo: document.getElementById('roomPhotoInput').value.trim() || null
  };

  const button = document.getElementById('roomSubmitBtn');
  button.disabled = true;

  try {
    const json = await rapiApi(
      id ? `/api/rooms/${id}` : '/api/rooms', {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify(body)
      }
    );

    roomModal().hide();
    showRoomAlert('success', json.message || 'Data ruangan berhasil disimpan.');
    await loadRooms();
  } catch (error) {
    showRoomFormAlert('danger', error.message);
  } finally {
    button.disabled = false;
  }
});

async function initRoomsPage() {
  if (!rapiRequireAuth()) return;

  const addButton = document.getElementById('addRoomBtn');

  if (currentRole === 'Admin') {
    addButton.classList.remove('d-none');

    try {
      await loadPicUsers();
    } catch (error) {
      console.error('Gagal memuat daftar PIC:', error);
      showRoomAlert('danger', `Gagal memuat daftar PIC: ${error.message}`);
    }
  }

  await loadRooms();
}

initRoomsPage();

</script>
@endsection