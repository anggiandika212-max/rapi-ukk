  @extends('layouts/contentNavbarLayout')

  @section('title', 'Pengaduan Fasilitas')

  @section('content')
  <div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <h5 class="mb-1">Pengaduan Fasilitas</h5>
        <small class="text-muted">Guru melaporkan masalah, PIC menangani perbaikannya.</small>
      </div>

      <button id="btnCreateComplaint" class="btn btn-primary d-none" data-bs-toggle="modal"
        data-bs-target="#complaintModal" onclick="openCreateComplaint()">
        <i class="bx bx-message-square-add me-1"></i>
        Buat Pengaduan
      </button>
    </div>

    <div class="card-body border-bottom">
      <div class="row g-3">
        <div class="col-md-4">
          <select id="complaintStatus" class="form-select">
            <option value="">Semua Status</option>
            <option value="Belum Diperbaiki">Belum Diperbaiki</option>
            <option value="Menunggu Spare Part">Menunggu Spare Part</option>
            <option value="Sudah Diperbaiki">Sudah Diperbaiki</option>
          </select>
        </div>
        <div class="col-md-3">
          <button class="btn btn-outline-secondary w-100" onclick="loadComplaints(1)">
            Filter
          </button>
        </div>
      </div>
    </div>

    <div id="complaintAlert" class="alert d-none m-3 mb-0"></div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Ruangan</th>
            <th>Keluhan</th>
            <th>Pelapor</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody id="complaintRows">
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">
              Memuat data...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="complaintPagination" class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2"></div>
  </div>

  {{-- Modal pengaduan --}}
  <div class="modal fade" id="complaintModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="complaintForm">
            <input type="hidden" id="complaintIdInput" value="">
          <div class="modal-header">
            <h5 class="modal-title" id="complaintModalTitle">Buat Pengaduan</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div id="complaintFormAlert" class="alert d-none"></div>

            <div class="row g-4">
              <div class="col-md-6">
                <label class="form-label" for="complaintRoomInput">Ruangan</label>
                <select id="complaintRoomInput" class="form-select" required>
                  <option value="">Memuat ruangan...</option>
                </select>
              </div>

            
          <div class="col-md-6">
            <label class="form-label">Foto Kerusakan</label>

            <div class="border rounded p-3">
              <video
                id="complaintCameraPreview"
                class="w-100 rounded d-none"
                autoplay
                muted
                playsinline
                style="max-height: 300px; object-fit: contain;"
              ></video>

              <canvas id="complaintCameraCanvas" class="d-none"></canvas>

              <img
                id="complaintPhotoPreview"
                class="img-fluid rounded d-none mb-3"
                alt="Pratinjau foto kerusakan"
                style="max-height: 300px;"
              >

              <div id="complaintCameraMessage" class="small text-muted mb-3">
                Aktifkan kamera untuk mengambil foto kerusakan secara langsung.
              </div>

              <div class="d-flex flex-wrap gap-2 mb-3">
                <button
                  type="button"
                  id="btnStartComplaintCamera"
                  class="btn btn-outline-primary btn-sm"
                >
                  <i class="bx bx-camera me-1"></i> Aktifkan Kamera
                </button>

                <button
                  type="button"
                  id="btnCaptureComplaintPhoto"
                  class="btn btn-primary btn-sm d-none"
                >
                  <i class="bx bx-camera me-1"></i> Ambil Foto
                </button>

                <button
                  type="button"
                  id="btnRetakeComplaintPhoto"
                  class="btn btn-outline-secondary btn-sm d-none"
                >
                  <i class="bx bx-refresh me-1"></i> Ulangi Foto
                </button>
              </div>
              <label for="complaintPhotoInput" class="form-label">
                Atau pilih foto dari galeri
              </label>
              <input
                id="complaintPhotoInput"
                type="file"
                class="form-control"
                accept="image/jpeg,image/png,image/webp"
              >
              <small class="text-muted d-block mt-2">
                Format JPG, PNG, atau WebP. Maksimal 5 MB.
                </small>
              </div>
            </div>


              <div class="col-12">
                <label class="form-label" for="complaintDescriptionInput">Deskripsi</label>
                <textarea id="complaintDescriptionInput" class="form-control" rows="5"
                  placeholder="Jelaskan masalah fasilitas..." required></textarea>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              Batal
            </button>
            <button class="btn btn-primary" type="submit" id="complaintSubmitBtn">
              Kirim Pengaduan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  {{-- Modal lihat foto kerusakan --}}
  <div class="modal fade" id="complaintPhotoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Foto Penanganan</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal">
          </button>
        </div>

        <div class="modal-body text-center">
          <img
            id="complaintPhotoModalImage"
            src=""
            alt="Foto kerusakan"
            class="img-fluid rounded"
            style="max-height: 70vh; object-fit: contain;">

          <div
            id="complaintPhotoModalError"
            class="alert alert-danger d-none mt-3">
            Foto gagal dimuat. Periksa URL atau lokasi penyimpanan foto.
          </div>
        </div>
      </div>
    </div>
  </div>
  {{-- Modal pembaruan status PIC --}}
  <div class="modal fade" id="repairStatusModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <form onsubmit="event.preventDefault(); saveComplaintStatus();">

                  <div class="modal-header">
                      <h5 class="modal-title">Pembaruan Status Perbaikan</h5>
                      <button type="button" class="btn-close"
                          data-bs-dismiss="modal"></button>
                  </div>

                  <div class="modal-body">
                      <input type="hidden" id="repairComplaintId">

                      <div id="repairStatusAlert" class="alert d-none"></div>

                      <div class="mb-3">
                          <label class="form-label">Status Perbaikan</label>
                          <select id="repairStatusInput" class="form-select" required>
                              <option value="Belum Diperbaiki">Belum Diperbaiki</option>
                              <option value="Menunggu Spare Part">Menunggu Spare Part</option>
                              <option value="Sudah Diperbaiki">Sudah Diperbaiki</option>
                          </select>
                      </div>

                      <div class="mb-3">
                          <label class="form-label">Foto Bukti Penanganan</label>

                          <video id="repairCameraPreview"
                              class="w-100 rounded d-none"
                              autoplay muted playsinline></video>

                          <canvas id="repairCameraCanvas" class="d-none"></canvas>

                          <img id="repairPhotoPreview"
                              class="img-fluid rounded d-none mt-2"
                              alt="Foto penanganan">

                          <div id="repairCameraMessage"
                              class="small text-muted my-2">
                              Ambil foto bukti penanganan.
                          </div>

                          <div class="d-flex gap-2 flex-wrap">
                              <button type="button" id="btnStartRepairCamera"
                                  class="btn btn-outline-primary btn-sm">
                                  Aktifkan Kamera
                              </button>

                              <button type="button" id="btnCaptureRepairPhoto"
                                  class="btn btn-primary btn-sm d-none">
                                  Ambil Foto
                              </button>

                              <button type="button" id="btnRetakeRepairPhoto"
                                  class="btn btn-outline-secondary btn-sm d-none">
                                  Ulangi Foto
                              </button>
                          </div>

                          <label class="form-label mt-3">Atau pilih dari galeri</label>
                          <input type="file" id="repairPhotoInput"
                              class="form-control"
                              accept="image/jpeg,image/png,image/webp">
                      </div>
                  </div>

                  <div class="modal-footer">
                      <button type="button" class="btn btn-outline-secondary"
                          data-bs-dismiss="modal">
                          Batal
                      </button>

                      <button type="submit" id="btnSaveRepairStatus"
                          class="btn btn-primary">
                          Simpan Perubahan
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
  console.log('Role pengguna:', rapiRole());
  console.log('Status autentikasi:', rapiRequireAuth());

  // Menampilkan foto kerusakan dalam modal.
  document.addEventListener('click', function (event) {
    const button = event.target.closest('.btn-view-complaint-photo');

    if (!button) return;

    const photoUrl = button.dataset.photoUrl;
    const image = document.getElementById('complaintPhotoModalImage');
    const error = document.getElementById('complaintPhotoModalError');

    image.classList.remove('d-none');
    error.classList.add('d-none');

    image.onerror = function () {
      image.classList.add('d-none');
      error.classList.remove('d-none');
    };

    image.onload = function () {
      image.classList.remove('d-none');
      error.classList.add('d-none');
    };

    image.src = photoUrl;

    bootstrap.Modal
      .getOrCreateInstance(
        document.getElementById('complaintPhotoModal')
      )
      .show();
  });

  let complaints = [];
  let complaintRooms = [];

  const COMPLAINT_PER_PAGE = 10;
  let complaintCurrentPage = 1;
  let complaintPagination = {
    current_page: 1,
    last_page: 1,
    per_page: COMPLAINT_PER_PAGE,
    total: 0,
    from: 0,
    to: 0
  };

  let complaintCameraStream = null;
  let capturedComplaintPhoto = null;
  let complaintPhotoPreviewUrl = null;

  const complaintCameraVideo = document.getElementById(
    'complaintCameraPreview'
  );
  const complaintCameraCanvas = document.getElementById(
    'complaintCameraCanvas'
  );
  const complaintPhotoPreview = document.getElementById(
    'complaintPhotoPreview'
  );
  const complaintCameraMessage = document.getElementById(
    'complaintCameraMessage'
  );
  const complaintPhotoInput = document.getElementById(
    'complaintPhotoInput'
  );

  // =====================================
  // KAMERA FOTO BUKTI PENANGANAN PIC
  // =====================================

  let repairCameraStream = null;
  let capturedRepairPhoto = null;
  let repairPhotoPreviewUrl = null;

  const repairCameraVideo = document.getElementById(
      'repairCameraPreview'
  );

  const repairCameraCanvas = document.getElementById(
      'repairCameraCanvas'
  );

  const repairPhotoPreview = document.getElementById(
      'repairPhotoPreview'
  );

  const repairPhotoInput = document.getElementById(
      'repairPhotoInput'
  );

  const repairCameraMessage = document.getElementById(
      'repairCameraMessage'
  );

  const btnStartRepairCamera = document.getElementById(
      'btnStartRepairCamera'
  );

  const btnCaptureRepairPhoto = document.getElementById(
      'btnCaptureRepairPhoto'
  );

  const btnRetakeRepairPhoto = document.getElementById(
      'btnRetakeRepairPhoto'
  );

  // Menghentikan kamera PIC.
  function stopRepairCamera() {
      if (repairCameraStream) {
          repairCameraStream.getTracks().forEach(track => track.stop());
          repairCameraStream = null;
      }

      if (repairCameraVideo) {
          repairCameraVideo.pause();
          repairCameraVideo.srcObject = null;
          repairCameraVideo.classList.add('d-none');
      }

      btnCaptureRepairPhoto?.classList.add('d-none');
      btnStartRepairCamera?.classList.remove('d-none');
  }

  // Mengaktifkan kamera belakang HP.
  async function startRepairCamera() {
      if (!navigator.mediaDevices?.getUserMedia) {
          repairCameraMessage.textContent =
              'Kamera tidak tersedia. Gunakan HTTPS atau localhost.';
          return;
      }

      stopRepairCamera();

      try {
          repairCameraMessage.textContent =
              'Meminta izin akses kamera...';

          repairCameraStream = await navigator.mediaDevices.getUserMedia({
              audio: false,
              video: {
                  facingMode: { ideal: 'environment' },
                  width: { ideal: 1280 },
                  height: { ideal: 720 }
              }
          });

          repairCameraVideo.srcObject = repairCameraStream;
          repairCameraVideo.classList.remove('d-none');

          await repairCameraVideo.play();

          btnStartRepairCamera.classList.add('d-none');
          btnCaptureRepairPhoto.classList.remove('d-none');

          repairCameraMessage.textContent =
              'Arahkan kamera ke kondisi ruangan, kemudian ambil foto.';
      } catch (error) {
          console.error('Kamera PIC gagal:', error);

          stopRepairCamera();

          repairCameraMessage.textContent =
              'Kamera gagal diaktifkan. Periksa izin kamera browser atau pilih foto dari galeri.';
      }
  }

  // Mengambil foto dari kamera.
  function captureRepairPhoto() {
      const video = repairCameraVideo;

      if (
          !repairCameraStream ||
          !video.videoWidth ||
          !video.videoHeight
      ) {
          repairCameraMessage.textContent =
              'Kamera belum siap.';
          return;
      }

      repairCameraCanvas.width = video.videoWidth;
      repairCameraCanvas.height = video.videoHeight;

      const context = repairCameraCanvas.getContext('2d');

      context.drawImage(
          video,
          0,
          0,
          repairCameraCanvas.width,
          repairCameraCanvas.height
      );

      repairCameraCanvas.toBlob(blob => {
          if (!blob) {
              repairCameraMessage.textContent =
                  'Foto gagal diproses. Silakan coba lagi.';
              return;
          }

          capturedRepairPhoto = new File(
              [blob],
              `repair-${Date.now()}.jpg`,
              { type: 'image/jpeg' }
          );

          repairPhotoInput.value = '';

          if (repairPhotoPreviewUrl) {
              URL.revokeObjectURL(repairPhotoPreviewUrl);
          }

          repairPhotoPreviewUrl =
              URL.createObjectURL(capturedRepairPhoto);

          repairPhotoPreview.src = repairPhotoPreviewUrl;
          repairPhotoPreview.classList.remove('d-none');

          stopRepairCamera();

          btnRetakeRepairPhoto.classList.remove('d-none');

          repairCameraMessage.textContent =
              'Foto berhasil diambil. Periksa sebelum menyimpan.';
      }, 'image/jpeg', 0.9);
  }

  // Mengulang pengambilan foto.
  function retakeRepairPhoto() {
      capturedRepairPhoto = null;

      repairPhotoPreview.classList.add('d-none');
      repairPhotoPreview.removeAttribute('src');

      btnRetakeRepairPhoto.classList.add('d-none');

      startRepairCamera();
  }

  // Jika PIC memilih foto dari galeri.
  repairPhotoInput.addEventListener('change', () => {
      const file = repairPhotoInput.files[0];

      if (!file) return;

      stopRepairCamera();
      capturedRepairPhoto = null;

      if (repairPhotoPreviewUrl) {
          URL.revokeObjectURL(repairPhotoPreviewUrl);
      }

      repairPhotoPreviewUrl = URL.createObjectURL(file);

      repairPhotoPreview.src = repairPhotoPreviewUrl;
      repairPhotoPreview.classList.remove('d-none');

      btnRetakeRepairPhoto.classList.add('d-none');
      btnStartRepairCamera.classList.remove('d-none');

      repairCameraMessage.textContent =
          'Foto galeri berhasil dipilih.';
  });
  // Hubungkan tombol kamera.
  btnStartRepairCamera.addEventListener(
      'click',
      startRepairCamera
  );

  btnCaptureRepairPhoto.addEventListener(
      'click',
      captureRepairPhoto
  );

  btnRetakeRepairPhoto.addEventListener(
      'click',
      retakeRepairPhoto
  );

  // Hentikan kamera ketika modal ditutup.
  document.getElementById('repairStatusModal')
      .addEventListener('hidden.bs.modal', () => {
          stopRepairCamera();
      });

  const btnStartComplaintCamera = document.getElementById(
    'btnStartComplaintCamera'
  );
  const btnCaptureComplaintPhoto = document.getElementById(
    'btnCaptureComplaintPhoto'
  );
  const btnRetakeComplaintPhoto = document.getElementById(
    'btnRetakeComplaintPhoto'
  );

  // Menghentikan kamera dan melepaskan akses perangkat.
  function stopComplaintCamera() {
    if (complaintCameraStream) {
      complaintCameraStream.getTracks().forEach(track => track.stop());
      complaintCameraStream = null;
    }

    complaintCameraVideo.pause();
    complaintCameraVideo.srcObject = null;
    complaintCameraVideo.classList.add('d-none');
    btnCaptureComplaintPhoto.classList.add('d-none');
    btnStartComplaintCamera.classList.remove('d-none');
  }

  // Mengaktifkan kamera perangkat.
  async function startComplaintCamera() {
    if (!navigator.mediaDevices?.getUserMedia) {
      complaintCameraMessage.textContent =
        'Kamera tidak tersedia. Gunakan HTTPS atau localhost, atau pilih foto dari galeri.';
      return;
    }

    stopComplaintCamera();

    try {
      complaintCameraMessage.textContent =
        'Meminta izin akses kamera...';

      complaintCameraStream = await navigator.mediaDevices.getUserMedia({
        audio: false,
        video: {
          facingMode: { ideal: 'environment' },
          width: { ideal: 1280 },
          height: { ideal: 720 }
        }
      });

      complaintCameraVideo.srcObject = complaintCameraStream;
      complaintCameraVideo.classList.remove('d-none');

      await complaintCameraVideo.play();

      btnStartComplaintCamera.classList.add('d-none');
      btnCaptureComplaintPhoto.classList.remove('d-none');

      complaintCameraMessage.textContent =
        'Posisikan kamera ke arah kerusakan, lalu tekan Ambil Foto.';
    } catch (error) {
      stopComplaintCamera();
      console.error('Gagal mengaktifkan kamera:', error);

      complaintCameraMessage.textContent =
        'Kamera gagal diaktifkan. Izinkan akses kamera pada browser atau pilih foto dari galeri.';
    }
  }

  // Mengambil satu foto dari tampilan kamera.
  function captureComplaintPhoto() {
    const video = complaintCameraVideo;

    if (
      !complaintCameraStream ||
      !video.videoWidth ||
      !video.videoHeight
    ) {
      complaintCameraMessage.textContent =
        'Kamera belum siap. Coba beberapa saat lagi.';
      return;
    }

    complaintCameraCanvas.width = video.videoWidth;
    complaintCameraCanvas.height = video.videoHeight;

    const context = complaintCameraCanvas.getContext('2d');
    context.drawImage(
      video,
      0,
      0,
      complaintCameraCanvas.width,
      complaintCameraCanvas.height
    );

    complaintCameraCanvas.toBlob(blob => {
      if (!blob) {
        complaintCameraMessage.textContent =
          'Foto gagal diproses. Silakan coba lagi.';
        return;
      }

      capturedComplaintPhoto = new File(
        [blob],
        `complaint-${Date.now()}.jpg`,
        { type: 'image/jpeg' }
      );

      // Hapus pilihan foto galeri sebelumnya.
      complaintPhotoInput.value = '';

      if (complaintPhotoPreviewUrl) {
        URL.revokeObjectURL(complaintPhotoPreviewUrl);
      }

      complaintPhotoPreviewUrl =
        URL.createObjectURL(capturedComplaintPhoto);

      complaintPhotoPreview.src = complaintPhotoPreviewUrl;
      complaintPhotoPreview.classList.remove('d-none');

      stopComplaintCamera();

      btnRetakeComplaintPhoto.classList.remove('d-none');

      complaintCameraMessage.textContent =
        'Foto berhasil diambil. Periksa pratinjau sebelum mengirim.';
    }, 'image/jpeg', 0.9);
  }

  // Mengulangi pengambilan foto.
  function retakeComplaintPhoto() {
    capturedComplaintPhoto = null;
    complaintPhotoPreview.classList.add('d-none');
    complaintPhotoPreview.removeAttribute('src');
    btnRetakeComplaintPhoto.classList.add('d-none');

    startComplaintCamera();
  }

  btnStartComplaintCamera.addEventListener(
    'click',
    startComplaintCamera
  );

  btnCaptureComplaintPhoto.addEventListener(
    'click',
    captureComplaintPhoto
  );

  btnRetakeComplaintPhoto.addEventListener(
    'click',
    retakeComplaintPhoto
  );

  // Jika pengguna memilih foto dari galeri.
  complaintPhotoInput.addEventListener('change', () => {
    const file = complaintPhotoInput.files[0];

    if (!file) return;

    stopComplaintCamera();
    capturedComplaintPhoto = null;

    if (complaintPhotoPreviewUrl) {
      URL.revokeObjectURL(complaintPhotoPreviewUrl);
    }

    complaintPhotoPreviewUrl = URL.createObjectURL(file);
    complaintPhotoPreview.src = complaintPhotoPreviewUrl;
    complaintPhotoPreview.classList.remove('d-none');

    btnRetakeComplaintPhoto.classList.add('d-none');

    complaintCameraMessage.textContent =
      'Foto dipilih dari galeri. Periksa pratinjau sebelum mengirim.';
  });

  // Matikan kamera ketika modal ditutup.
  document.getElementById('complaintModal')
    .addEventListener('hidden.bs.modal', () => {
      stopComplaintCamera();
    });


  const currentRole = rapiRole();

  const complaintModal = () =>
    bootstrap.Modal.getOrCreateInstance(
      document.getElementById('complaintModal')
    );

  function showComplaintAlert(type, message) {
    const element = document.getElementById('complaintAlert');
    element.className = `alert alert-${type} m-3 mb-0`;
    element.textContent = message;
  }

  function showComplaintFormAlert(type, message) {
    const element = document.getElementById('complaintFormAlert');
    element.className = `alert alert-${type}`;
    element.textContent = message;
  }

  function getResponseArray(json) {
    if (Array.isArray(json.data)) {
      return json.data;
    }

    if (Array.isArray(json.data?.data)) {
      return json.data.data;
    }

    return [];
  }

  // Membaca data dan metadata pagination dari respons paginator Laravel.
  function getComplaintPaginationResponse(json) {
    const payload = json?.data && !Array.isArray(json.data)
      ? json.data
      : json;

    const rows = Array.isArray(payload?.data)
      ? payload.data
      : Array.isArray(json?.data)
        ? json.data
        : [];

    const meta = payload?.meta || json?.meta || payload || json || {};
    const currentPage = Number(meta.current_page || meta.currentPage || complaintCurrentPage);
    const perPage = Number(meta.per_page || meta.perPage || COMPLAINT_PER_PAGE);
    const total = Number(meta.total ?? rows.length);
    const lastPage = Number(meta.last_page || meta.lastPage || Math.max(1, Math.ceil(total / perPage)));

    return {
      rows,
      pagination: {
        current_page: currentPage,
        last_page: Math.max(1, lastPage),
        per_page: perPage,
        total,
        from: Number(meta.from ?? (total ? ((currentPage - 1) * perPage) + 1 : 0)),
        to: Number(meta.to ?? (total ? ((currentPage - 1) * perPage) + rows.length : 0))
      }
    };
  }

  function renderComplaintPagination() {
    const container = document.getElementById('complaintPagination');
    const page = complaintPagination.current_page;
    const lastPage = complaintPagination.last_page;
    const total = complaintPagination.total;

    container.innerHTML = `
      <small class="text-muted">
        Menampilkan ${complaintPagination.from}–${complaintPagination.to} dari ${total} pengaduan
      </small>
      <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-primary"
          onclick="loadComplaints(${page - 1})" ${page <= 1 ? 'disabled' : ''}>
          <i class="bx bx-chevron-left"></i> <<
        </button>
        <span class="small text-muted">Halaman ${page} dari ${lastPage}</span>
        <button type="button" class="btn btn-sm btn-outline-primary"
          onclick="loadComplaints(${page + 1})" ${page >= lastPage ? 'disabled' : ''}>
          >> <i class="bx bx-chevron-right"></i>
        </button>
      </div>
    `;
  }

  async function loadComplaints(page = complaintCurrentPage) {
    complaintCurrentPage = Math.max(1, Number(page) || 1);
    const rows = document.getElementById('complaintRows');

    rows.innerHTML = `
          <tr>
              <td colspan="6" class="text-center py-5 text-muted">
                  Memuat data pengaduan...
              </td>
          </tr>
      `;

    try {
      const params = new URLSearchParams({
        page: complaintCurrentPage,
        per_page: COMPLAINT_PER_PAGE
      });
      const status = document.getElementById('complaintStatus').value;

      if (status) {
        params.set('status_repair', status);
      }

      const json = await rapiApi(
        `/api/complaints?${params.toString()}`
      );

      const result = getComplaintPaginationResponse(json);
      complaints = result.rows;
      complaintPagination = result.pagination;

      if (complaints.length === 0 && complaintCurrentPage > 1 && complaintCurrentPage > complaintPagination.last_page) {
        return loadComplaints(complaintPagination.last_page);
      }

      renderComplaints();
      renderComplaintPagination();
    } catch (error) {
      console.error('Gagal memuat pengaduan:', error);

      rows.innerHTML = `
              <tr>
                  <td colspan="6" class="text-center text-danger py-5">
                      Gagal memuat data pengaduan.
                  </td>
              </tr>
          `;
      document.getElementById('complaintPagination').innerHTML = '';

      showComplaintAlert('danger', error.message);
    }
  }

  function renderComplaints() {
    const rows = document.getElementById('complaintRows');

    if (!complaints.length) {
      rows.innerHTML = `
              <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                      Belum ada pengaduan.
                  </td>
              </tr>
          `;
      return;
    }

    rows.innerHTML = complaints.map(complaint => {
      let actions = '-';

    
      if (currentRole === 'PIC') {
        actions = `
          <button
            class="btn btn-sm btn-outline-primary"
            onclick="changeComplaintStatus(${Number(complaint.id)})"
          >
            <i class="bx bx-refresh me-1"></i>Status
          </button>
        `;
      } else if (currentRole === 'Guru' 
      && complaint.can_edit === true
      ) {
        actions = `
          <button
            class="btn btn-sm btn-outline-warning"
            onclick="editComplaint(${Number(complaint.id)})"
          >
            <i class="bx bx-edit me-1"></i>Edit
          </button>
        `;
      } else if (
        currentRole === 'Admin' &&
        complaint.status_repair === 'Belum Diperbaiki'
      ) {
        actions = `
          <button
            class="btn btn-sm btn-outline-success"
            onclick="openWhatsApp(${Number(complaint.id)})"
          >
            <i class="bx bxl-whatsapp me-1"></i>WhatsApp PIC
          </button>
        `;
      }

      const photoLink = complaint.photo
        ? `<div>
            <button
              type="button"
              class="btn btn-link btn-sm p-0 btn-view-complaint-photo"
              data-photo-url="${rapiEscape(complaint.photo)}">
              <i class="bx bx-image me-1"></i>Lihat foto
            </button>
          </div>`
      : '';

      const repairPhotoLink = complaint.repair_photo
      ? `<div>
          <button
              type="button"
              class="btn btn-link btn-sm p-0 btn-view-complaint-photo"
              data-photo-url="${rapiEscape(complaint.repair_photo)}">
              <i class="bx bx-image me-1"></i>
              Lihat foto penanganan
          </button>
      </div>`
      : '';

      return `
              <tr>
                  <td class="fw-semibold">
                      ${rapiEscape(complaint.room?.room_name || '-')}
                  </td>

                  <td>
                      <div>${rapiEscape(complaint.description || '-')}</div>
                      ${photoLink}
                      ${repairPhotoLink}
                  </td>

                  <td>${rapiEscape(complaint.reporter?.name || '-')}</td>

                  <td>${rapiBadge(complaint.status_repair || '-')}</td>

                  <td>${rapiEscape(
                      complaint.created_at
                          ? complaint.created_at.replace('T', ' ').slice(0, 16)
                          : '-'
                  )}</td>

                  <td class="text-end">${actions}</td>
              </tr>
          `;
    }).join('');
  }

  // Memuat daftar ruangan untuk Guru.
  async function loadComplaintRooms() {
    const select = document.getElementById('complaintRoomInput');

    select.innerHTML = '<option value="">Memuat ruangan...</option>';

    const json = await rapiApi('/api/rooms?per_page=100');
    complaintRooms = getResponseArray(json);

    const availableRooms = complaintRooms.filter(
      room => room.status !== 'Maintenance'
    );

    if (!availableRooms.length) {
      select.innerHTML = '<option value="">Tidak ada ruangan tersedia</option>';
      return;
    }

    select.innerHTML = `
          <option value="">Pilih ruangan</option>
          ${availableRooms.map(room => `
              <option value="${Number(room.id)}">
                  ${rapiEscape(room.room_name)}
              </option>
          `).join('')}
      `;
  }


  function openCreateComplaint() {
    stopComplaintCamera();

    document.getElementById('complaintForm').reset();
    complaintEditMode = false;

    document.getElementById('complaintIdInput').value = '';

    document.getElementById('complaintModalTitle').textContent =
      'Buat Pengaduan';

    document.getElementById('complaintSubmitBtn').textContent =
      'Kirim Pengaduan';

    const formAlert = document.getElementById('complaintFormAlert');
    formAlert.className = 'alert d-none';
    formAlert.textContent = '';

    capturedComplaintPhoto = null;

    if (complaintPhotoPreviewUrl) {
      URL.revokeObjectURL(complaintPhotoPreviewUrl);
      complaintPhotoPreviewUrl = null;
    }

    complaintPhotoPreview.removeAttribute('src');
    complaintPhotoPreview.classList.add('d-none');

    btnCaptureComplaintPhoto.classList.add('d-none');
    btnRetakeComplaintPhoto.classList.add('d-none');
    btnStartComplaintCamera.classList.remove('d-none');

    complaintCameraMessage.textContent =
      'Aktifkan kamera untuk mengambil foto kerusakan secara langsung.';

    const select = document.getElementById('complaintRoomInput');

    if (!complaintRooms.length) {
      select.innerHTML =
        '<option value="">Memuat ruangan...</option>';
    }
  }

  let complaintEditMode = false;

  async function editComplaint(id) {
    if (currentRole !== 'Guru') {
      showComplaintAlert('danger', 'Akses edit hanya untuk Guru.');
      return;
    }

    const complaint = complaints.find(
      item => Number(item.id) === Number(id)
    );

    if (!complaint || complaint.can_edit !== true) {
      showComplaintAlert(
        'danger',
        'Pengaduan tidak ditemukan atau tidak dapat diedit.'
      );
      return;
    }

    // Reset form tanpa mengubah data pengaduan di server.
    openCreateComplaint();

    complaintEditMode = true;

    document.getElementById('complaintIdInput').value = complaint.id;
    document.getElementById('complaintRoomInput').value = complaint.room?.id
      ?? complaint.room_id
      ?? '';

    document.getElementById('complaintDescriptionInput').value =
      complaint.description || '';

    document.getElementById('complaintModalTitle').textContent =
      'Edit Pengaduan';

    document.getElementById('complaintSubmitBtn').textContent =
      'Simpan Perubahan';

    // Tampilkan foto yang tersimpan jika tersedia.
    if (complaint.photo) {
      complaintPhotoPreview.src = complaint.photo;
      complaintPhotoPreview.classList.remove('d-none');

      complaintCameraMessage.textContent =
        'Foto pengaduan saat ini. Ambil foto baru atau pilih foto galeri untuk menggantinya.';
    }

    complaintModal().show();
  }



  // PIC memperbarui status perbaikan.
  // Membuka modal pembaruan status PIC.
  function changeComplaintStatus(id) {
      const complaint = complaints.find(
          item => Number(item.id) === Number(id)
      );

      if (!complaint) return;

      stopRepairCamera();

      capturedRepairPhoto = null;
      repairPhotoInput.value = '';

      if (repairPhotoPreviewUrl) {
          URL.revokeObjectURL(repairPhotoPreviewUrl);
          repairPhotoPreviewUrl = null;
      }

      repairPhotoPreview.removeAttribute('src');
      repairPhotoPreview.classList.add('d-none');

      btnCaptureRepairPhoto.classList.add('d-none');
      btnRetakeRepairPhoto.classList.add('d-none');
      btnStartRepairCamera.classList.remove('d-none');

      repairCameraMessage.textContent =
          'Ambil foto terbaru sebagai bukti penanganan pengaduan.';

      document.getElementById('repairComplaintId').value =
          complaint.id;

      document.getElementById('repairStatusInput').value =
          complaint.status_repair || 'Belum Diperbaiki';

      const alert = document.getElementById('repairStatusAlert');
      alert.className = 'alert d-none';
      alert.textContent = '';

      bootstrap.Modal.getOrCreateInstance(
          document.getElementById('repairStatusModal')
      ).show();
  }

  // Admin membuka WhatsApp PIC melalui endpoint API.
  async function openWhatsApp(id) {
    try {
      const json = await rapiApi(`/api/complaints/${id}/whatsapp`, {
        method: 'POST'
      });

      const url = json.data?.whatsapp_url;

      if (!url) {
        throw new Error('URL WhatsApp tidak ditemukan pada respons API.');
      }

      window.open(url, '_blank', 'noopener,noreferrer');
    } catch (error) {
      showComplaintAlert('danger', error.message);
    }
  }

  document.getElementById('complaintForm').addEventListener(
    'submit',
    async event => {
      event.preventDefault();

    if (!['Guru', 'Admin'].includes(currentRole)) {
        showComplaintFormAlert(
            'danger',
            'Hanya Admin dan Guru yang dapat menambahkan pengaduan.'
        );
        return;
    }

      const complaintId =
        document.getElementById('complaintIdInput').value;

      const isEditing = complaintEditMode && Boolean(complaintId);

      const roomId =
        document.getElementById('complaintRoomInput').value;

      const description =
        document.getElementById('complaintDescriptionInput')
          .value.trim();

      const galleryPhoto = complaintPhotoInput.files[0] || null;
      const photo = capturedComplaintPhoto || galleryPhoto;

      if (!roomId || !description) {
        showComplaintFormAlert(
          'danger',
          'Ruangan dan deskripsi wajib diisi.'
        );
        return;
      }

      // Foto wajib saat membuat pengaduan baru,
      // tetapi boleh tidak diisi saat mengedit.
      if (!isEditing && !photo) {
        showComplaintFormAlert(
          'danger',
          'Foto kerusakan wajib diisi.'
        );
        return;
      }

      if (photo) {
        const allowedTypes = [
          'image/jpeg',
          'image/png',
          'image/webp'
        ];

        if (!allowedTypes.includes(photo.type)) {
          showComplaintFormAlert(
            'danger',
            'Foto harus berformat JPG, PNG, atau WebP.'
          );
          return;
        }

        if (photo.size > 5 * 1024 * 1024) {
          showComplaintFormAlert(
            'danger',
            'Ukuran foto maksimal 5 MB.'
          );
          return;
        }
      }

      const formData = new FormData();
      formData.append('room_id', roomId);
      formData.append('description', description);

      // Foto hanya dikirim jika pengguna memilih atau mengambil foto baru.
      if (photo) {
        formData.append('photo', photo);
      }

    const button = document.getElementById('complaintSubmitBtn');

    const originalText = isEditing
      ? 'Simpan Perubahan'
      : 'Kirim Pengaduan';

    button.disabled = true;
    button.textContent = isEditing ? 'Menyimpan...' : 'Mengirim...';

    try {
      const url = isEditing
        ? `/api/complaints/${encodeURIComponent(complaintId)}`
        : '/api/complaints';

      if (isEditing) {
          formData.append('_method', 'PATCH');
      }

      const json = await rapiApi(url, {
          method: 'POST',
          body: formData
      });

        complaintEditMode = false;
        complaintModal().hide();

        showComplaintAlert(
          'success',
          json.message || (
            isEditing
              ? 'Pengaduan berhasil diperbarui.'
              : 'Pengaduan berhasil dikirim.'
          )
        );

        await loadComplaints();
      } catch (error) {
        console.error('Gagal menyimpan pengaduan:', error);

        showComplaintFormAlert(
          'danger',
          error.message || 'Pengaduan gagal disimpan.'
        );
      } finally {
        button.disabled = false;
        button.textContent = originalText;
      }
    }
  );


  async function initComplaintsPage() {
      if (!rapiRequireAuth()) return;

      if (['Guru', 'Admin'].includes(currentRole)) {
          document.getElementById('btnCreateComplaint')
              .classList.remove('d-none');

          try {
              // Memuat daftar ruangan terlebih dahulu
              await loadComplaintRooms();
          } catch (error) {
              console.error('Gagal memuat daftar ruangan:', error);

              showComplaintAlert(
                  'danger',
                  'Daftar ruangan gagal dimuat: ' + error.message
              );
          }
      }

      // Memuat daftar pengaduan
      await loadComplaints();

      // Membaca parameter dari halaman Data Event
      const params = new URLSearchParams(window.location.search);
      const roomId = params.get('room_id');
      const eventTitle = params.get('event_title');

      // Jika berasal dari tombol Complaint pada halaman Event
      if (roomId && ['Guru', 'Admin'].includes(currentRole)) {

          // Pastikan ruangan tersedia pada daftar pilihan
        const selectedRoom = complaintRooms.find(
          room =>
              Number(room.id) === Number(roomId) &&
              room.status !== 'Maintenance'
        );

        if (!selectedRoom) {
          showComplaintAlert(
              'warning',
              'Ruangan event tidak ditemukan atau sedang dalam perbaikan.'
          );
          return;
        }

          // Reset dan siapkan form pengaduan
          openCreateComplaint();

          // Isi ruangan sesuai event
          document.getElementById('complaintRoomInput').value = roomId;

          // Isi deskripsi berdasarkan judul event
          if (eventTitle) {
              document.getElementById('complaintDescriptionInput').value =
                  `Pengaduan terkait event: ${eventTitle}\n\n`;
          }

          // Tampilkan modal secara otomatis
          complaintModal().show();
      }
  }

  initComplaintsPage();
  // Menyimpan status dan foto bukti penanganan PIC.
  async function saveComplaintStatus() {
      const id = document.getElementById('repairComplaintId').value;
      const status = document.getElementById('repairStatusInput').value;

      const photo = capturedRepairPhoto || repairPhotoInput.files[0];

      const alert = document.getElementById('repairStatusAlert');
      const button = document.getElementById('btnSaveRepairStatus');

      if (!id) {
          return;
      }

      if (status === 'Sudah Diperbaiki' && !photo) {
          alert.className = 'alert alert-danger';
          alert.textContent =
              'Foto bukti penanganan wajib diunggah ketika status Sudah Diperbaiki.';
          return;
      }

      // Validasi foto hanya jika foto tersedia.
      if (photo) {
          const allowedTypes = [
              'image/jpeg',
              'image/png',
              'image/webp'
          ];

          if (!allowedTypes.includes(photo.type)) {
              alert.className = 'alert alert-danger';
              alert.textContent = 'Format foto harus JPG, PNG, atau WebP.';
              return;
          }

          if (photo.size > 5 * 1024 * 1024) {
              alert.className = 'alert alert-danger';
              alert.textContent = 'Ukuran foto maksimal 5 MB.';
              return;
          }
      }

      const formData = new FormData();

      formData.append('status_repair', status);

      if (photo) {
          formData.append('repair_photo', photo);
      }

      formData.append('_method', 'PATCH');

      button.disabled = true;
      button.textContent = 'Menyimpan...';

      try {
          const json = await rapiApi(
              `/api/complaints/${encodeURIComponent(id)}/status`,
              {
                  method: 'POST',
                  body: formData
              }
          );

          bootstrap.Modal.getInstance(
              document.getElementById('repairStatusModal')
          )?.hide();

          showComplaintAlert(
              'success',
              json.message || 'Status dan foto berhasil diperbarui.'
          );

          await loadComplaints();

      } catch (error) {
          console.error('Gagal memperbarui status:', error);

          alert.className = 'alert alert-danger';
          alert.textContent =
              error.message || 'Gagal memperbarui status pengaduan.';

      } finally {
          button.disabled = false;
          button.textContent = 'Simpan Perubahan';
      }
  }

  </script>
  @endsection