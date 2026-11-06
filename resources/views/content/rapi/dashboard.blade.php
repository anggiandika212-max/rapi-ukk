@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard RAPI-Sekolah')

@section('content')
<div class="row g-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm rapi-hero">
      <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
          <div>
            <span class="badge bg-white text-primary mb-3">RAPI-Sekolah</span>
            <h2 class="text-white mb-2">Selamat datang, <span id="userName">...</span></h2>
            <p class="text-white mb-2">Pusat pengelolaan fasilitas, kegiatan, dan pengaduan sekolah.</p>
          </div>
          <div class="text-end">
            <div class="text-white-50 small">Role</div>
            <div class="fs-4 fw-semibold text-white" id="userRole">...</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="statsGrid" class="row g-4 m-0 p-0"></div>

  <div class="col-xl-7">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-1">Event Mendatang</h5><small class="text-muted">Jadwal yang sudah disetujui</small>
        </div>
        <a href="{{ route('rapi.events') }}" class="btn btn-sm btn-outline-primary">Kelola Event</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Event</th>
              <th>Tanggal</th>
              <th>Jam</th>
              <th>Ruangan</th>
            </tr>
          </thead>
          <tbody id="upcomingEvents">
            <tr>
              <td colspan="4" class="text-center py-5 text-muted">Memuat...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-1">Complaint Terbaru</h5><small class="text-muted">Status penanganan fasilitas</small>
        </div><a href="{{ route('rapi.complaints') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="card-body" id="recentComplaints">
        <div class="text-muted">Memuat...</div>
      </div>
    </div>
  </div>

  <div class="col-12" id="adminQuickLinks"></div>
</div>
<div id="dashboardAlert" class="alert alert-danger d-none mt-4"></div>

@endsection

@section('page-style')
<style>
.rapi-hero {
  background: linear-gradient(135deg, #696cff 0%, #4f46e5 100%);
}

.rapi-stat-icon {
  width: 46px;
  height: 46px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  font-size: 1.35rem;
}
</style>
@endsection

@section('page-script')
<script src="{{ asset('js/rapi.js') }}"></script>
<script>
const statDefinitions = [
  ['rooms_total', 'Total Ruangan', 'bx-building-house'],
  ['rooms_available', 'Ruangan Tersedia', 'bx-check-circle'],
  ['events_pending', 'Event Pending', 'bx-calendar-exclamation'],
  ['events_today', 'Event Hari Ini', 'bx-calendar-check'],
  ['events_approved', 'Event Approved', 'bx-calendar-star'],
  ['complaints_open', 'Complaint Terbuka', 'bx-message-square-error'],
];

function renderStats(stats) {
  document.getElementById('statsGrid').innerHTML = statDefinitions
    .filter(([key]) => stats[key] !== undefined && stats[key] !== null)
    .map(([key, label, icon]) => `
            <div class="col-md-6 col-xl-3">
                <div class="card h-100"><div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                    <div class="text-muted small">${rapiEscape(label)}</div>
                    <div class="display-6 fw-semibold mt-2">${rapiEscape(stats[key])}</div>
                    </div>
                    <div class="rapi-stat-icon bg-label-primary"><i class="bx ${icon}"></i></div>
                </div></div>
            </div>`).join('');

  if (rapiRole === 'Admin') {
    document.getElementById('statsGrid').insertAdjacentHTML('beforeend', ['users_total', 'users_pic', 'users_guru'].map(
      (key, index) => {
        const labels = ['Total User', 'PIC', 'Guru'];
        const icons = ['bx-group', 'bx-user-check', 'bx-user-voice'];
        return `<div class="col-md-6 col-xl-3">
        <div class="card h-100">
        <div class="card-body d-flex justify-content-between align-items-center">
        <div>
        <div class="text-muted small">${labels[index]}</div>
        <div class="display-6 fw-semibold mt-2">${stats[key] ?? 0}</div>
        </div>
        <div class="rapi-stat-icon bg-label-success">
        <i class="bx ${icons[index]}"></i>
        </div>
        </div>
        </div>
        </div>`;
      }).join(''));
  }
}

async function loadDashboard() {
  const json = await rapiApi('/api/dashboard');
  const data = json.data;
  document.getElementById('userName').textContent = data.user.name;
  document.getElementById('userRole').textContent = data.user.role;
  renderStats(data.stats);

  document.getElementById('upcomingEvents').innerHTML = data.upcoming_events.length ? data.upcoming_events.map(
    event =>
    `
        <tr><td class="fw-semibold">${rapiEscape(event.title)}</td>
        <td>${rapiFormatDate(event.date)}</td>
        <td>${rapiEscape(event.start_time)} - ${rapiEscape(event.end_time)}</td>
        <td>${rapiEscape(event.room || '-')}</td>
        </tr>`
  ).join('') : '<tr><td colspan="4" class="text-center py-5 text-muted">Belum ada event.</td></tr>';

  document.getElementById('recentComplaints').innerHTML = data.recent_complaints.length ? data.recent_complaints.map(
    item =>
    `
        <div class="border-bottom py-3">
        <div class="d-flex justify-content-between gap-2">
        <span class="fw-semibold">${rapiEscape(item.room || '-')}</span>${rapiBadge(item.status)}
        </div>
        <div class="small mt-2">${rapiEscape(item.description)}
        </div>
        <div class="small text-muted mt-1">${rapiEscape(item.created_at || '-')}
        </div>
        </div>`
  ).join('') : '<div class="text-muted">Belum ada complaint.</div>';

  if (rapiRole === 'Admin') {
    document.getElementById('adminQuickLinks').innerHTML =
      `<div class="card">
      <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div><h5 class="mb-1">Administrasi</h5><p class="text-muted mb-0">Kelola akun dan fasilitas sekolah.</p></div>
      <div class="d-flex gap-2"><a href="{{ route('rapi.users') }}" class="btn btn-primary">Kelola User</a>
      <a href="{{ route('rapi.rooms') }}" class="btn btn-outline-primary">Kelola Ruangan</a>
      </div>
      </div>
      </div>`;
  }
}

loadDashboard().catch(error => {
  const box = document.getElementById('dashboardAlert');
  box.textContent = error.message;
  box.classList.remove('d-none');
});
</script>
@endsection