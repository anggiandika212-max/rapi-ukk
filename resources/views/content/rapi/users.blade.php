
@extends('layouts/contentNavbarLayout')

@section('title', 'Manajemen User')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Manajemen User</h4>
            <p class="text-muted mb-0">
                Kelola akun pengguna RAPI-Sekolah.
            </p>
        </div>

        <button type="button"
                id="btnCreateUser"
                class="btn btn-primary d-none"
                data-bs-toggle="modal"
                data-bs-target="#userModal"
                onclick="openCreateUser()">
            <i class="bx bx-plus me-1"></i>
            Tambah User
        </button>
    </div>

    <div id="userAlert" class="alert d-none" role="alert"></div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-3">Daftar Pengguna</h5>

            <div class="row g-2">
                <div class="col-md-6">
                    <input type="search"
                           id="userSearch"
                           class="form-control"
                           placeholder="Cari nama atau email...">
                </div>

                <div class="col-md-4">
                    <select id="userRoleFilter" class="form-select">
                        <option value="">Semua Role</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button"
                            id="btnSearchUser"
                            class="btn btn-outline-primary w-100">
                        Cari
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>

                <tbody id="userRows">
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            Memuat data user...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <small id="paginationInfo" class="text-muted">
                Memuat data...
            </small>

            <div class="d-flex gap-2">
                <button type="button"
                        id="btnPrevious"
                        class="btn btn-sm btn-outline-secondary"
                        disabled>
                    <<
                </button>

                <button type="button"
                        id="btnNext"
                        class="btn btn-sm btn-outline-secondary"
                        disabled>
                    >>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal tambah/edit user --}}
<div class="modal fade"
     id="userModal"
     tabindex="-1"
     aria-labelledby="userModalTitle"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form id="userForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">
                        Tambah User
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div id="userFormAlert"
                         class="alert d-none"
                         role="alert"></div>

                    <input type="hidden" id="userId">

                    <div class="mb-3">
                        <label for="userNameInput" class="form-label">
                            Nama Lengkap
                        </label>

                        <input type="text"
                               id="userNameInput"
                               class="form-control"
                               maxlength="255"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="userEmailInput" class="form-label">
                            Email
                        </label>

                        <input type="email"
                               id="userEmailInput"
                               class="form-control"
                               maxlength="255"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="userPhoneInput" class="form-label">
                            Nomor Telepon
                        </label>

                        <input type="text"
                               id="userPhoneInput"
                               class="form-control"
                               maxlength="20">
                    </div>

                    <div class="mb-3">
                        <label for="userRoleInput" class="form-label">
                            Role
                        </label>

                        <select id="userRoleInput"
                                class="form-select"
                                required>
                            <option value="">Pilih role</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="userPasswordInput" class="form-label">
                            Password
                        </label>

                        <input type="password"
                               id="userPasswordInput"
                               class="form-control"
                               minlength="8"
                               autocomplete="new-password">

                        <small id="passwordHelp" class="text-muted">
                            Minimal 8 karakter.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="userPasswordConfirmation"
                               class="form-label">
                            Konfirmasi Password
                        </label>

                        <input type="password"
                               id="userPasswordConfirmation"
                               class="form-control"
                               minlength="8"
                               autocomplete="new-password">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            id="userSubmitBtn"
                            class="btn btn-primary">
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

{{-- Data role dikirim oleh controller halaman Blade --}}
<script type="application/json" id="rolesData">@json($roles->map(function ($role) {
    return [
        'id' => $role->id,
        'name' => $role->role_name,
    ];
})->values())</script>

<script>
let users = [];
let roles = [];
let currentPage = 1;
let lastPage = 1;
let currentRole = null;

const perPage = 10;

const userModal = () => bootstrap.Modal.getOrCreateInstance(
    document.getElementById('userModal')
);

function getResponseArray(json) {
    if (Array.isArray(json.data)) {
        return json.data;
    }

    if (Array.isArray(json.data?.data)) {
        return json.data.data;
    }

    return [];
}

function getRoleName(user) {
    return user.role?.role_name
        ?? user.role?.name
        ?? user.role_name
        ?? '-';
}

function getRoleId(user) {
    return user.role?.id
        ?? user.role_id
        ?? '';
}

function showUserAlert(type, message) {
    const element = document.getElementById('userAlert');

    element.className = `alert alert-${type}`;
    element.textContent = message;
}

function hideUserAlert() {
    const element = document.getElementById('userAlert');

    element.className = 'alert d-none';
    element.textContent = '';
}

function showUserFormAlert(type, message) {
    const element = document.getElementById('userFormAlert');

    element.className = `alert alert-${type}`;
    element.textContent = message;
}

function hideUserFormAlert() {
    const element = document.getElementById('userFormAlert');

    element.className = 'alert d-none';
    element.textContent = '';
}

function escapeHtml(value) {
    if (typeof rapiEscape === 'function') {
        return rapiEscape(String(value ?? ''));
    }

    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[character]);
}

function formatDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return date.toLocaleString('id-ID', {
        dateStyle: 'short',
        timeStyle: 'short'
    });
}

function renderRoleFilter() {
    const select = document.getElementById('userRoleFilter');

    select.innerHTML = '<option value="">Semua Role</option>';

    roles.forEach(role => {
        const option = document.createElement('option');

        option.value = role.id;
        option.textContent = role.name;

        select.appendChild(option);
    });
}

function renderRoleOptions(selected = '') {
    const select = document.getElementById('userRoleInput');

    select.innerHTML = '<option value="">Pilih role</option>';

    roles.forEach(role => {
        const option = document.createElement('option');

        option.value = role.id;
        option.textContent = role.name;
        option.selected = Number(role.id) === Number(selected);

        select.appendChild(option);
    });
}

function openCreateUser() {
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';

    document.getElementById('userModalTitle').textContent =
        'Tambah User';

    document.getElementById('userPasswordInput').required = true;
    document.getElementById('userPasswordConfirmation').required = true;

    document.getElementById('passwordHelp').textContent =
        'Minimal 8 karakter.';

    hideUserFormAlert();
    renderRoleOptions();

    userModal().show();
}

function openEditUser(id) {
    const user = users.find(item => Number(item.id) === Number(id));

    if (!user) {
        showUserAlert('warning', 'Data user tidak ditemukan.');
        return;
    }

    document.getElementById('userForm').reset();

    document.getElementById('userId').value = user.id;
    document.getElementById('userNameInput').value = user.name ?? '';
    document.getElementById('userEmailInput').value = user.email ?? '';
    document.getElementById('userPhoneInput').value = user.phone ?? '';

    renderRoleOptions(getRoleId(user));

    document.getElementById('userPasswordInput').required = false;
    document.getElementById('userPasswordConfirmation').required = false;

    document.getElementById('passwordHelp').textContent =
        'Kosongkan jika tidak ingin mengganti password.';

    document.getElementById('userModalTitle').textContent =
        'Edit User';

    hideUserFormAlert();
    userModal().show();
}

function renderUsers() {
    const rows = document.getElementById('userRows');

    if (!users.length) {
        rows.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    Tidak ada user yang ditemukan.
                </td>
            </tr>
        `;

        return;
    }

    rows.innerHTML = users.map(user => {
        const roleName = getRoleName(user);

        const actions = currentRole === 'Admin'
            ? `
                <button type="button"
                        class="btn btn-sm btn-outline-primary me-1"
                        onclick="openEditUser(${Number(user.id)})"
                        title="Edit user"
                        aria-label="Edit user">
                    <i class="bx bx-edit"></i>
                </button>

                <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        onclick="deleteUser(${Number(user.id)})"
                        title="Hapus user"
                        aria-label="Hapus user">
                    <i class="bx bx-trash"></i>
                </button>
            `
            : '-';

        return `
            <tr>
                <td class="fw-semibold">
                    ${escapeHtml(user.name || '-')}
                </td>
                <td>${escapeHtml(user.email || '-')}</td>
                <td>${escapeHtml(user.phone || '-')}</td>
                <td>${escapeHtml(roleName)}</td>
                <td>${escapeHtml(formatDate(user.created_at))}</td>
                <td class="text-end text-nowrap">${actions}</td>
            </tr>
        `;
    }).join('');
}

async function loadUsers(page = currentPage) {
    const rows = document.getElementById('userRows');

    currentPage = page;

    rows.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-5">
                Memuat data user...
            </td>
        </tr>
    `;

    hideUserAlert();

    try {
        const params = new URLSearchParams({
            page: String(currentPage),
            per_page: String(perPage),
            sort_by: 'id',
            sort_order: 'asc'
        });

        const search = document.getElementById('userSearch')
            .value.trim();

        const roleId = document.getElementById('userRoleFilter')
            .value;

        if (search) {
            params.set('search', search);
        }

        if (roleId) {
            params.set('role_id', roleId);
        }

        const json = await rapiApi(`/api/users?${params.toString()}`);

        users = getResponseArray(json);

        const pagination = json.pagination
            ?? json.data?.meta
            ?? {};

        currentPage = Number(
            pagination.current_page ?? page
        );

        lastPage = Number(
            pagination.last_page ?? currentPage
        );

        renderUsers();

        document.getElementById('paginationInfo').textContent =
            `Halaman ${currentPage} dari ${lastPage}` +
            (pagination.total != null
                ? ` · Total ${pagination.total} user`
                : '');

        document.getElementById('btnPrevious').disabled =
            currentPage <= 1;

        document.getElementById('btnNext').disabled =
            currentPage >= lastPage;

    } catch (error) {
        console.error('Gagal memuat user:', error);

        rows.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-5">
                    Gagal memuat data user.
                </td>
            </tr>
        `;

        showUserAlert('danger', error.message || 'Gagal memuat user.');
    }
}

async function deleteUser(id) {
    const user = users.find(item => Number(item.id) === Number(id));

    if (!user) return;

    const confirmed = confirm(
        `Yakin ingin menghapus akun ${user.name}? ` +
        'Tindakan ini menghapus akun, bukan sekadar menonaktifkannya.'
    );

    if (!confirmed) return;

    try {
        const json = await rapiApi(`/api/users/${id}`, {
            method: 'DELETE'
        });

        showUserAlert(
            'success',
            json.message || 'User berhasil dihapus.'
        );

        await loadUsers(currentPage);

    } catch (error) {
        console.error('Gagal menghapus user:', error);

        showUserAlert(
            'danger',
            error.message || 'Gagal menghapus user.'
        );
    }
}

document.getElementById('userForm').addEventListener(
    'submit',
    async event => {
        event.preventDefault();

        if (currentRole !== 'Admin') {
            showUserFormAlert(
                'danger',
                'Hanya Admin yang dapat mengelola akun.'
            );
            return;
        }

        const id = document.getElementById('userId').value;
        const roleId = document.getElementById('userRoleInput').value;
        const name = document.getElementById('userNameInput').value.trim();
        const email = document.getElementById('userEmailInput').value.trim();
        const phone = document.getElementById('userPhoneInput').value.trim();
        const password = document.getElementById('userPasswordInput').value;
        const confirmation = document.getElementById(
            'userPasswordConfirmation'
        ).value;

        if (!roleId) {
            showUserFormAlert('danger', 'Silakan pilih role user.');
            return;
        }

        if (!id && !password) {
            showUserFormAlert(
                'danger',
                'Password wajib diisi saat membuat akun.'
            );
            return;
        }

        if (password && password.length < 8) {
            showUserFormAlert(
                'danger',
                'Password minimal 8 karakter.'
            );
            return;
        }

        if (password !== confirmation && (password || confirmation)) {
            showUserFormAlert(
                'danger',
                'Password dan konfirmasi password tidak sama.'
            );
            return;
        }

        const payload = {
            role_id: Number(roleId),
            name,
            email,
            phone: phone || null
        };

        if (password) {
            payload.password = password;
            payload.password_confirmation = confirmation;
        }

        const button = document.getElementById('userSubmitBtn');

        button.disabled = true;
        button.textContent = 'Menyimpan...';

        try {
            const json = await rapiApi(
                id ? `/api/users/${id}` : '/api/users',
                {
                    method: id ? 'PUT' : 'POST',
                    body: JSON.stringify(payload)
                }
            );

            userModal().hide();

            showUserAlert(
                'success',
                json.message || 'Data user berhasil disimpan.'
            );

            await loadUsers(currentPage);

        } catch (error) {
            console.error('Gagal menyimpan user:', error);

            showUserFormAlert(
                'danger',
                error.message || 'Gagal menyimpan user.'
            );

        } finally {
            button.disabled = false;
            button.textContent = 'Simpan';
        }
    }
);

document.getElementById('btnSearchUser').addEventListener(
    'click',
    () => loadUsers(1)
);

document.getElementById('userRoleFilter').addEventListener(
    'change',
    () => loadUsers(1)
);

document.getElementById('userSearch').addEventListener(
    'keydown',
    event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            loadUsers(1);
        }
    }
);

document.getElementById('btnPrevious').addEventListener(
    'click',
    () => {
        if (currentPage > 1) {
            loadUsers(currentPage - 1);
        }
    }
);

document.getElementById('btnNext').addEventListener(
    'click',
    () => {
        if (currentPage < lastPage) {
            loadUsers(currentPage + 1);
        }
    }
);

async function initUsersPage() {

    if (typeof rapiRequireAuth === 'function') {
        if (!rapiRequireAuth()) return;
    }

    currentRole = typeof rapiRole === 'function'
        ? rapiRole()
        : null;

    if (currentRole !== 'Admin') {
        showUserAlert(
            'warning',
            'Halaman manajemen user hanya dapat digunakan oleh Admin.'
        );

        document.getElementById('userRows').innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5">
                    Akses halaman ini dibatasi untuk Admin.
                </td>
            </tr>
        `;

        return;
    }

    try {
        const rolesElement = document.getElementById('rolesData');

        roles = JSON.parse(rolesElement.textContent || '[]');

        renderRoleFilter();

        document.getElementById('btnCreateUser')
            .classList.remove('d-none');

        await loadUsers(1);

    } catch (error) {
        console.error('Gagal menginisialisasi halaman user:', error);

        showUserAlert(
            'danger',
            'Gagal memuat konfigurasi role. Periksa data role dari controller halaman.'
        );
    }
}

initUsersPage();

</script>
@endsection