<!-- Navbar -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{ $containerNav }} {{ $navbarDetached }} navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    @include('layouts/sections/navbar/navbar-partial')
</nav>
@else
<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{ $containerNav }}">@include('layouts/sections/navbar/navbar-partial')</div>
</nav>
@endif
<!-- / Navbar -->
 
<!-- My Profile Modal -->
<div
    class="modal fade"
    id="myProfileModal"
    tabindex="-1"
    aria-labelledby="myProfileModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="myProfileModalLabel">
                    <i class="bx bx-user-circle me-2"></i>
                    My Profile
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Tutup"
                ></button>
            </div>

            <div class="modal-body" id="myProfileContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="text-muted mt-2 mb-0">
                        Memuat detail pengguna...
                    </p>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
<!-- /My Profile Modal -->
