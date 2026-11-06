@extends('layouts/contentNavbarLayout')

@section('title', 'Laporan Bulanan')

@section('content')
<div class="card mb-4 no-print">
  <div class="card-header">
    <h5 class="mb-1">Laporan Bulanan RAPI-Sekolah</h5>
    <small class="text-muted">Rekap pengaduan, kondisi ruangan terkini, dan agenda kegiatan.</small>
  </div>
  <div class="card-body">
    <div class="row g-3 align-items-end">
      <div class="col-md-4">
        <label for="reportMonth" class="form-label">Bulan</label>
        <select id="reportMonth" class="form-select">
          @foreach ([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $number => $name)
            <option value="{{ $number }}" @selected((int) date('n') === $number)>{{ $name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label for="reportYear" class="form-label">Tahun</label>
        <select id="reportYear" class="form-select">
          @for ($year = (int) date('Y'); $year >= 2020; $year--)
            <option value="{{ $year }}">{{ $year }}</option>
          @endfor
        </select>
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button id="filterReportBtn" class="btn btn-primary flex-grow-1" type="button">
          <i class="bx bx-filter-alt me-1"></i>Tampilkan
        </button>
        <button id="printReportBtn" class="btn btn-outline-secondary" type="button" disabled>
          <i class="bx bx-printer me-1"></i>Cetak
        </button>
      </div>
    </div>
    <div id="reportAlert" class="alert d-none mt-3 mb-0" role="alert"></div>
  </div>
</div>

<div id="reportContent">
  <div class="card mb-4">
    <div class="card-body text-center py-5 text-muted">Pilih bulan dan tahun, lalu klik Tampilkan.</div>
  </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('js/rapi.js') }}"></script>
<script>
const reportMonthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const reportRole = typeof rapiRole === 'function' ? rapiRole() : '';

function reportEscape(value) {
  if (typeof rapiEscape === 'function') return rapiEscape(value ?? '-');
  return String(value ?? '-').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

function reportDate(value) {
  if (!value) return '-';
  const date = new Date(`${value}T00:00:00`);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'});
}

function reportTime(value) {
  return value ? String(value).slice(0,5) : '-';
}

function reportBadge(status) {
  const colors = {
    'Sudah Diperbaiki':'success', 'Belum Diperbaiki':'danger',
    'Menunggu Spare Part':'warning', 'Approved':'success',
    'Completed':'primary', 'Pending':'warning', 'Rejected':'danger',
    'Cancelled':'secondary', 'Available':'success', 'Booked':'warning',
    'Maintenance':'danger'
  };
  const color = colors[status] || 'secondary';
  return `<span class="badge bg-label-${color}">${reportEscape(status || '-')}</span>`;
}

function reportTable(headers, rows, emptyText, renderRow) {
  return `<div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr></thead>
    <tbody>${rows.length ? rows.map(renderRow).join('') : `<tr><td colspan="${headers.length}" class="text-center text-muted py-4">${emptyText}</td></tr>`}</tbody>
  </table></div>`;
}

function renderMonthlyReport(data) {
  const monthLabel = `${reportMonthNames[data.period.month - 1]} ${data.period.year}`;
  const s = data.summary;
  const complaintTable = reportTable(
    ['No.','Tanggal Laporan','Ruangan','Pelapor','Deskripsi','Status Perbaikan'], data.complaints,
    'Tidak ada pengaduan pada periode ini.', (item, index) => `<tr>
      <td>${index + 1}</td><td>${reportDate(item.created_at?.slice(0,10))}</td>
      <td>${reportEscape(item.room?.room_name)}</td><td>${reportEscape(item.reporter?.name)}</td>
      <td style="min-width:220px;white-space:normal">${reportEscape(item.description)}</td><td>${reportBadge(item.status_repair)}</td></tr>`
  );
  const roomTable = reportTable(
    ['No.','Nama Ruangan','PIC','Fasilitas','Kondisi Terkini','Status'], data.rooms,
    'Belum ada data ruangan.', (item, index) => `<tr>
      <td>${index + 1}</td><td>${reportEscape(item.room_name)}</td><td>${reportEscape(item.pic?.name || 'Belum ditentukan')}</td>
      <td style="min-width:180px;white-space:normal">${reportEscape(item.facilities)}</td>
      <td>${reportEscape(item.facility_condition)}</td><td>${reportBadge(item.status)}</td></tr>`
  );
  const eventTable = reportTable(
    ['No.','Tanggal','Waktu','Nama Kegiatan','Ruangan','Penanggung Jawab','Status'], data.events,
    'Tidak ada agenda pada periode ini.', (item, index) => `<tr>
      <td>${index + 1}</td><td>${reportDate(item.event_date)}</td>
      <td>${reportTime(item.start_time)}–${reportTime(item.end_time)}</td>
      <td>${reportEscape(item.title)}</td><td>${reportEscape(item.room?.room_name)}</td>
      <td>${reportEscape(item.user?.name)}</td><td>${reportBadge(item.status)}</td></tr>`
  );

  document.getElementById('reportContent').innerHTML = `
    <div class="report-heading text-center mb-4">
      <h4 class="mb-1">LAPORAN BULANAN RAPI-SEKOLAH</h4>
      <h5 class="mb-1">Periode ${reportEscape(monthLabel)}</h5>
      <small class="text-muted">Dicetak pada ${new Date().toLocaleDateString('id-ID', {day:'2-digit',month:'long',year:'numeric'})}</small>
    </div>
    <div class="row g-3 mb-4">
      ${[
        ['Total Pengaduan',s.total_complaints,'bx-message-square-error'],
        ['Pengaduan Selesai',s.completed_complaints,'bx-check-circle'],
        ['Belum Selesai',s.outstanding_complaints,'bx-time-five'],
        ['Total Agenda',s.total_events,'bx-calendar-check'],
        ['Jumlah Ruangan',s.total_rooms,'bx-buildings']
      ].map(card => `<div class="col-6 col-md"><div class="card h-100"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center"><span class="text-muted small">${card[0]}</span><i class="bx ${card[2]} fs-3"></i></div>
        <h3 class="mb-0 mt-2">${card[1]}</h3></div></div></div>`).join('')}
    </div>
    <div class="card mb-4"><div class="card-header"><h5 class="mb-0">1. Rekap Pengaduan Kerusakan</h5></div>${complaintTable}</div>
    <div class="card mb-4"><div class="card-header"><h5 class="mb-0">2. Kondisi Ruangan Saat Ini</h5><small class="text-muted">Data kondisi ruangan merupakan kondisi terakhir saat laporan dibuka.</small></div>${roomTable}</div>
    <div class="card mb-4"><div class="card-header"><h5 class="mb-0">3. Rekap Agenda Kegiatan</h5></div>${eventTable}</div>
    <div class="report-footer text-end mt-5"><p>Mengetahui,</p><br><br><p>(________________________)</p><small>Admin RAPI-Sekolah</small></div>`;
  document.getElementById('printReportBtn').disabled = false;
}

async function loadMonthlyReport() {
  const alertBox = document.getElementById('reportAlert');
  const button = document.getElementById('filterReportBtn');
  alertBox.className = 'alert d-none mt-3 mb-0';
  button.disabled = true;
  button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memuat...';
  try {
    const month = document.getElementById('reportMonth').value;
    const year = document.getElementById('reportYear').value;
    const data = await rapiApi(`/api/reports/monthly?month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}`);
    renderMonthlyReport(data);
  } catch (error) {
    alertBox.className = 'alert alert-danger mt-3 mb-0';
    alertBox.textContent = error.message || 'Gagal memuat laporan.';
    document.getElementById('reportContent').innerHTML = '<div class="card"><div class="card-body text-center text-danger py-5">Laporan gagal dimuat.</div></div>';
    document.getElementById('printReportBtn').disabled = true;
  } finally {
    button.disabled = false;
    button.innerHTML = '<i class="bx bx-filter-alt me-1"></i>Tampilkan';
  }
}

document.getElementById('filterReportBtn').addEventListener('click', loadMonthlyReport);
document.getElementById('printReportBtn').addEventListener('click', () => window.print());

document.addEventListener('DOMContentLoaded', () => {
  if (typeof rapiRequireAuth === 'function' && !rapiRequireAuth()) return;
  if (reportRole !== 'Admin') {
    document.getElementById('reportContent').innerHTML = '<div class="alert alert-danger">Halaman laporan hanya dapat diakses oleh Admin.</div>';
    document.getElementById('filterReportBtn').disabled = true;
    return;
  }
  loadMonthlyReport();
});
</script>
<style>
@media print {
  @page { size: A4 landscape; margin: 12mm; }
  body { background: #fff !important; }
  .layout-menu, .layout-navbar, .content-footer, .no-print, .btn, .menu, nav { display: none !important; }
  .layout-page, .content-wrapper, .container-xxl, .container-p-y { margin: 0 !important; padding: 0 !important; width: 100% !important; }
  .card { border: 1px solid #ddd !important; box-shadow: none !important; break-inside: avoid; }
  .card-header { padding: 8px 10px !important; }
  .card-body { padding: 10px !important; }
  table { font-size: 9px !important; }
  th, td { padding: 5px !important; }
  .report-heading { margin-top: 0 !important; }
  .report-footer { page-break-inside: avoid; }
}
</style>
@endsection
