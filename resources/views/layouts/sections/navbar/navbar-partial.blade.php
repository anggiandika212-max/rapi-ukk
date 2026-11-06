@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if(isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
  <a href="{{url('/')}}" class="app-brand-link gap-2">
    <span class="app-brand-logo demo">@include('_partials.macros')</span>
    <span class="app-brand-text demo menu-text fw-bold text-heading">{{config('variables.templateName')}}</span>
  </a>
</div>
@endif

<!-- ! Not required for layout-without-menu -->
@if(!isset($navbarHideToggle))
<div
  class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
  <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
    <i class="icon-base bx bx-menu icon-md"></i>
  </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">


  <ul class="navbar-nav flex-row align-items-center ms-auto">
    <!-- Place this tag where you want the button to render. -->
 
    <!-- User Dropdown -->
  <li class="nav-item navbar-dropdown dropdown-user dropdown">
    <a
      class="nav-link dropdown-toggle hide-arrow p-0"
      href="javascript:void(0);"
      data-bs-toggle="dropdown"
      aria-expanded="false"
    >
      <div class="avatar avatar-online">
        <img
          src="{{ asset('assets/img/avatars/1.png') }}"
          alt="Foto profil"
          class="w-px-40 h-auto rounded-circle"
        >
      </div>
    </a>

    <ul class="dropdown-menu dropdown-menu-end">
      <!-- User Information -->
      <li>
        <div class="dropdown-item">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
              <div class="avatar avatar-online">
                <img
                  src="{{ asset('assets/img/avatars/1.png') }}"
                  alt="Foto profil"
                  class="w-px-40 h-auto rounded-circle"
                >
              </div>
            </div>

            <div class="flex-grow-1">
              <h6 class="mb-0" id="navbarUserName">Memuat...</h6>
              <small class="text-muted" id="navbarUserRole">Pengguna</small>
            </div>
          </div>
        </div>
      </li>

      <li><div class="dropdown-divider my-1"></div></li>

      <!-- My Profile -->
      <li>
        <a
          class="dropdown-item"
          href="javascript:void(0);"
          onclick="loadMyProfile()"
        >
          <i class="icon-base bx bx-user icon-md me-3"></i>
          <span>My Profile</span>
        </a>
      </li>

      <!-- Notifications -->
      <li>
        <a
          class="dropdown-item"
          href="javascript:void(0);"
          onclick="event.preventDefault(); loadNotifications();"
        >
          <span class="d-flex align-items-center">
            <i class="icon-base bx bx-bell icon-md me-3"></i>
            <span class="flex-grow-1">Notifikasi</span>

            <span
              id="notificationBadge"
              class="badge rounded-pill bg-danger d-none"
            >0</span>
          </span>
        </a>
      </li>

      <li><div class="dropdown-divider my-1"></div></li>

      <!-- Logout -->
      <li>
        <a
          class="dropdown-item"
          href="javascript:void(0);"
          onclick="rapiLogout()"
        >
          <i class="icon-base bx bx-power-off icon-md me-3"></i>
          <span>Log Out</span>
        </a>
      </li>
    </ul>
  </li>
  <!-- /User Dropdown -->

  </ul>

</div>

<!---js--->

<!-- Modal notifikasi global agar tersedia di semua halaman -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="notificationModalLabel"><i class="bx bx-bell me-2"></i>Notifikasi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body"><div id="notificationList"><div class="text-center text-muted py-4">Pilih notifikasi untuk melihat detail.</div></div></div>
  </div></div>
</div>

<script src="{{ asset('js/rapi.js') }}"></script>
<script>
function prepareNotificationModal() {
  const modalElement = document.getElementById('notificationModal');

  if (!modalElement) {
    throw new Error('Modal notifikasi tidak ditemukan.');
  }

  // Pindahkan modal keluar dari navbar agar tidak terjebak
  // dalam stacking context atau aturan posisi elemen induk.
  if (modalElement.parentElement !== document.body) {
    document.body.appendChild(modalElement);
  }

  // Tutup dropdown pengguna sebelum membuka modal.
  document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
    const dropdownElement = menu.closest('.dropdown');

    const toggle = dropdownElement?.querySelector(
      '[data-bs-toggle="dropdown"]'
    );

    if (toggle && window.bootstrap?.Dropdown) {
      bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
    }
  });

  if (!window.bootstrap?.Modal) {
    throw new Error('Bootstrap Modal belum dimuat.');
  }

  return bootstrap.Modal.getOrCreateInstance(modalElement, {
    backdrop: true,
    keyboard: true,
    focus: true
  });
}
async function loadNotifications() {
  const list = document.getElementById('notificationList');
  const badge = document.getElementById('notificationBadge');
  if (!list) { console.error('Elemen notificationList tidak ditemukan.'); return; }
  list.innerHTML = `<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm mb-2"></div><div>Memuat notifikasi...</div></div>`;
  try {
    const result = await rapiApi('/api/notifications');
    const notifications = Array.isArray(result.data) ? result.data : [];
    const unreadCount = notifications.filter(item => item.read_at === null).length;
    if (badge) { badge.textContent = unreadCount; badge.classList.toggle('d-none', unreadCount === 0); }
    if (!notifications.length) {
      list.innerHTML = `<div class="text-center text-muted py-4"><i class="bx bx-bell-off" style="font-size:48px"></i><p class="mb-0 mt-2">Tidak ada notifikasi.</p></div>`;
    } else {
      list.innerHTML = notifications.map(notification => {
        const data = notification.data || {};
        const isUnread = notification.read_at === null;
        const createdAt = notification.created_at ? new Date(notification.created_at).toLocaleString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '-';
        const id = String(notification.id).replace(/[^a-zA-Z0-9-]/g, '');
        return `
        <div class="border-bottom py-3 
        ${isUnread ? 'bg-light' : ''}" role="button" tabindex="0" style="cursor:pointer" 
        onclick="openNotificationDetail('${id}')" 
        onkeydown="if(event.key==='Enter'||event.key===' ')
        {event.preventDefault();openNotificationDetail('${id}');}">
        <div class="d-flex">
        <div class="me-3">
        <div class="avatar">
        <span class="avatar-initial rounded bg-label-primary">
        <i class="bx bx-bell">
        </i></span></div></div><div class="flex-grow-1"><h6 class="mb-1">
        ${rapiEscape(data.title || 'Notifikasi')}</h6>
        <p class="mb-1 text-muted">${rapiEscape(data.message || 'Ada notifikasi baru.')}</p>
        ${data.status_repair
                ? (() => {
                    const statusColors = {
                    'Belum Diperbaiki': 'bg-label-danger',
                    'Sedang Diperbaiki': 'bg-label-warning',
                    'Sudah Diperbaiki': 'bg-label-success',
                    'Menunggu Spare Part': 'bg-label-info'
                    };

                    const status = String(data.status_repair);
                    const color = statusColors[status] || 'bg-label-secondary';

                    return `
                    <span class="badge ${color} mb-1">
                        ${rapiEscape(status)}
                    </span>
                    <br>
                    `;
                })()
                : ''
            }
        
        <small class="text-muted">
        ${createdAt}</small></div>${isUnread ? `<div class="ms-2">
        <span class="badge bg-primary">Baru</span></div>` : ''}</div></div>`;
      }).join('');
    }
    const modalElement = document.getElementById('notificationModal');

if (modalElement) {
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
}
  } catch (error) {
    console.error('Gagal memuat notifikasi:', error);
    list.innerHTML = `<div class="alert alert-danger">Gagal memuat notifikasi.<br><small>${rapiEscape(error.message || 'Terjadi kesalahan.')}</small></div>`;
  }
}
async function openNotificationDetail(notificationId) {
  const list = document.getElementById('notificationList');
  const badge = document.getElementById('notificationBadge');
  if (!list) return;
  list.innerHTML = `<div class="text-center text-muted py-4">
  <div class="spinner-border spinner-border-sm mb-2">
  </div><div>Memuat detail notifikasi...</div></div>`;

  try {
    const result = await rapiApi('/api/notifications');
    const notifications = Array.isArray(result.data) ? result.data : [];
    const notification = notifications.find(item => String(item.id) === String(notificationId));
    if (!notification) throw new Error('Notifikasi tidak ditemukan. Muat ulang daftar notifikasi.');
    const data = notification.data || {};

    if (notification.read_at === null) {
      await rapiApi(`/api/notifications/${encodeURIComponent(notification.id)}/read`, { method: 'PATCH' });
    }

    const latestResult = await rapiApi('/api/notifications');
    const latestNotifications = Array.isArray(latestResult.data) ? latestResult.data : [];
    const unreadCount = latestNotifications.filter(item => item.read_at === null).length;
    if (badge) {
      badge.textContent = unreadCount;
      badge.classList.toggle('d-none', unreadCount === 0);
    }

    let complaint = null;
    if (data.type === 'complaint_escalation' && data.complaint_id) {
      const complaintResult = await rapiApi(`/api/complaints/${encodeURIComponent(data.complaint_id)}`);
      complaint = complaintResult.data || null;
    }

    const createdAt = notification.created_at ? new Date(notification.created_at).toLocaleString('id-ID', {
      day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    }) : '-';

    const detailRows = complaint ? `
        <div class="mb-3"><small class="text-muted d-block">Ruangan</small><div class="fw-semibold">${rapiEscape(complaint.room?.room_name || data.room_name || '-')}</div></div>
        <div class="mb-3">
            <small class="text-muted d-block mb-1">
                Status Perbaikan Saat Ini
            </small>
            ${
                (() => {
                const status = String(complaint.status_repair || '-');

                const statusColors = {
                    'Belum Diperbaiki': 'bg-label-danger',
                    'Sedang Diperbaiki': 'bg-label-warning',
                    'Sudah Diperbaiki': 'bg-label-success',
                    'Menunggu Spare Part': 'bg-label-info'
                };

                const color = statusColors[status] || 'bg-label-secondary';

                return `<span class="badge ${color}">${rapiEscape(status)}</span>`;
                })()
            }
        </div>
        <div class="mb-3"><small class="text-muted d-block">Deskripsi Kerusakan</small><div>${rapiEscape(complaint.description || '-')}</div></div>
        <div class="mb-3"><small class="text-muted d-block">Pelapor</small><div>${rapiEscape(complaint.reporter?.name || '-')}</div></div>
        <div class="mb-3"><small class="text-muted d-block">Tanggal Laporan</small><div>${rapiEscape(complaint.created_at || '-')}</div></div>
        ${complaint.photo ? `<div class="mb-3"><small class="text-muted d-block mb-2">Foto Kerusakan</small><img src="${rapiEscape(complaint.photo)}" alt="Foto kerusakan" class="img-fluid rounded border" style="max-height:300px" loading="lazy"></div>` : ''}
    ` : `
      ${data.old_room ? `<div class="mb-3"><small class="text-muted d-block">Ruangan Sebelumnya</small><div class="fw-semibold">${rapiEscape(data.old_room)}</div></div>` : ''}
      ${data.new_room ? `<div class="mb-3"><small class="text-muted d-block">Ruangan Baru</small><div class="fw-semibold">${rapiEscape(data.new_room)}</div></div>` : ''}
      ${data.move_reason ? `<div class="mb-3"><small class="text-muted d-block">Alasan Pemindahan</small><div>${rapiEscape(data.move_reason)}</div></div>` : ''}
      ${data.date ? `<div class="mb-3"><small class="text-muted d-block">Tanggal</small><div class="fw-semibold">${rapiEscape(data.date)}</div></div>` : ''}
      ${data.time ? `<div class="mb-3"><small class="text-muted d-block">Waktu</small><div class="fw-semibold">${rapiEscape(data.time)}</div></div>` : ''}
    `;

    list.innerHTML = `
      <div>
        <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="loadNotifications()"><i class="bx bx-arrow-back me-1"></i>Kembali ke notifikasi</button>
        <div class="text-center mb-4"><div class="avatar avatar-xl mx-auto mb-3"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-bell" style="font-size:30px"></i></span></div><h4 class="mb-1">${rapiEscape(data.title || 'Notifikasi')}</h4><small class="text-muted">${rapiEscape(createdAt)}</small></div>
        <div class="card border shadow-none"><div class="card-body"><h6 class="mb-2">Pesan</h6><p>${rapiEscape(data.message || 'Tidak ada detail pesan.')}</p>${detailRows}</div></div>
      </div>`;
  } catch (error) {
    console.error('Gagal membuka detail notifikasi:', error);
    list.innerHTML = `<div class="alert alert-danger"><div class="fw-semibold">Gagal membuka detail notifikasi.</div><small>${rapiEscape(error.message || 'Terjadi kesalahan.')}</small><div class="mt-3"><button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadNotifications()">Kembali</button></div></div>`;
  }
}
// Mengambil data pengguna dari cache lokal jika tersedia
function getCurrentUser() {
  try {
    return rapiUser() || {};
  } catch (error) {
    console.error('Gagal membaca data pengguna:', error);
    return {};
  }
}

// Mengisi nama dan role pada dropdown
function updateNavbarUser() {
  const user = getCurrentUser();

  const nameElement = document.getElementById('navbarUserName');
  const roleElement = document.getElementById('navbarUserRole');

  if (nameElement) {
    nameElement.textContent = user.name || user.nama || 'Pengguna';
  }

  if (roleElement) {
    const roleName =
      user.role?.role_name ||
      user.role_name ||
      user.role?.name ||
      'Pengguna';

    roleElement.textContent = roleName;
  }
}

// Membuat baris detail profil
function profileRow(label, value) {
    return `
        <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
            <span class="text-muted">${rapiEscape(label)}</span>
            
            <span class="fw-medium text-end ms-3">
                ${rapiEscape(value ?? '-')}
            </span>
        </div>
    `;
}

// Menampilkan modal dan mengambil detail profil dari API


async function loadMyProfile() {
    const modalElement = document.getElementById('myProfileModal');
    const content = document.getElementById('myProfileContent');

    if (!modalElement || !content) {
        console.error('Elemen modal tidak ditemukan.');
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();

    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-2">Memuat profil...</p>
        </div>
    `;

    try {
        const result = await rapiApi('/api/user');

        const user =
            result?.data?.data ??
            result?.data ??
            result?.user ??
            result;

        if (!user || typeof user !== 'object' || Array.isArray(user)) {
            throw new Error('Data profil tidak valid.');
        }

        renderMyProfile(user);

    } catch (error) {
        console.error('Gagal memuat profil:', error);

        content.innerHTML = `
            <div class="alert alert-danger">
                Gagal memuat profil.
                <div class="small">${rapiEscape(error.message)}</div>
            </div>
        `;
    }
}

// Menampilkan informasi profil
function renderMyProfile(user) {
    const content = document.getElementById('myProfileContent');

    const roleName =
        user.role?.role_name ||
        user.role_name ||
        user.role?.name ||
        '-';

    content.innerHTML = `
        <div class="text-center mb-4">
            <div class="avatar avatar-xl mx-auto mb-3">
                <span class="avatar-initial rounded-circle bg-label-primary">
                    <i class="bx bx-user fs-2"></i>
                </span>
            </div>

            <h5 class="mb-1">${rapiEscape(user.name || '-')}</h5>

            <span class="badge bg-label-primary">
                ${rapiEscape(roleName)}
            </span>
        </div>

        <hr>

        ${profileRow('ID Pengguna', user.id)}
        ${profileRow('Nama Lengkap', user.name)}
        ${profileRow('Email', user.email)}
        ${profileRow('Nomor Telepon', user.phone || '-')}
        ${profileRow('Role', roleName)}

        <div class="d-flex justify-content-end mt-4">
            <button
                type="button"
                class="btn btn-primary"
                onclick="editMyProfile()"
            >
                <i class="bx bx-edit me-1"></i>
                Edit Profil
            </button>
        </div>
    `;
}

// Menampilkan form edit profil
function editMyProfile() {
    const content = document.getElementById('myProfileContent');

    const name = document.querySelector('#myProfileContent h5')?.textContent || '';

    // Ambil data dari tampilan profil
    const rows = content.querySelectorAll('.profile-value');

    const email = rows[0]?.textContent || '';
    const phone = rows[1]?.textContent || '';

    content.innerHTML = `
        <form id="editMyProfileForm">

            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input
                    type="text"
                    class="form-control"
                    id="profileName"
                    value="${rapiEscape(name)}"
                    maxlength="255"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input
                    type="email"
                    class="form-control"
                    id="profileEmail"
                    value="${rapiEscape(email)}"
                    maxlength="255"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Nomor Telepon</label>
                <input
                    type="tel"
                    class="form-control"
                    id="profilePhone"
                    value="${phone === '-' ? '' : rapiEscape(phone)}"
                    maxlength="20"
                    placeholder="Masukkan nomor telepon"
                >
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    onclick="loadMyProfile()"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                    id="saveProfileButton"
                >
                    <i class="bx bx-save me-1"></i>
                    Simpan Perubahan
                </button>
            </div>

        </form>
    `;

    document.getElementById('editMyProfileForm')
        .addEventListener('submit', saveMyProfile);
}

// Menyimpan perubahan profil
async function saveMyProfile(event) {
    event.preventDefault();

    const button = document.getElementById('saveProfileButton');

    const name = document.getElementById('profileName').value.trim();
    const email = document.getElementById('profileEmail').value.trim();
    const phone = document.getElementById('profilePhone').value.trim();

    if (!name || !email) {
        alert('Nama lengkap dan email wajib diisi.');
        return;
    }

    button.disabled = true;
    button.innerHTML = `
        <span class="spinner-border spinner-border-sm me-1"></span>
        Menyimpan...
    `;

    try {
        const result = await rapiApi('/api/user/profile', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name,
                email: email,
                phone: phone || null
            })
        });

        if (result?.success === false) {
            throw new Error(result.message || 'Gagal memperbarui profil.');
        }

        alert(result?.message || 'Profil berhasil diperbarui.');

        // Memperbarui navbar
        if (typeof updateNavbarUser === 'function') {
            await updateNavbarUser();
        }

        // Memuat kembali profil terbaru
        await loadMyProfile();

    } catch (error) {
        console.error('Gagal memperbarui profil:', error);

        alert(
            error.message || 'Terjadi kesalahan saat memperbarui profil.'
        );

        button.disabled = false;
        button.innerHTML = `
            <i class="bx bx-save me-1"></i>
            Simpan Perubahan
        `;
    }
}




document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('navbarSearch');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const keyword = this.value
            .trim()
            .toLocaleLowerCase('id-ID');

        // Cari baris pada semua tabel di halaman aktif
        document.querySelectorAll('table tbody').forEach(function (tbody) {
            tbody.querySelectorAll('tr').forEach(function (row) {
                // Abaikan baris yang hanya digunakan untuk pesan kosong
                if (row.querySelector('td[colspan]')) return;

                const text = row.textContent
                    .toLocaleLowerCase('id-ID');

                row.style.display = text.includes(keyword)
                    ? ''
                    : 'none';
            });
        });

        // Cari elemen daftar yang diberi class searchable-item
        document.querySelectorAll('.searchable-item').forEach(function (item) {
            const text = item.textContent.toLocaleLowerCase('id-ID');

            item.style.display = text.includes(keyword)
                ? ''
                : 'none';
        });
    });
});

// Perbarui nama dan role saat halaman siap
document.addEventListener('DOMContentLoaded', updateNavbarUser);

document.addEventListener('DOMContentLoaded', function () {
  const modalElement = document.getElementById('notificationModal');

  if (!modalElement) return;

  // Pastikan modal berada langsung di dalam body.
  document.body.appendChild(modalElement);

  modalElement.addEventListener('hidden.bs.modal', function () {
    // Jika modal sudah tertutup, pastikan class modal tidak tertinggal.
    modalElement.classList.remove('show');
    modalElement.style.removeProperty('display');
    modalElement.removeAttribute('aria-modal');
    modalElement.setAttribute('aria-hidden', 'true');
    modalElement.removeAttribute('role');
    modalElement.style.removeProperty('padding-right');
  });
});


</script>