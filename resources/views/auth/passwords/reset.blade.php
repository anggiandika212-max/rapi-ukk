
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Baru - RAPI Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: #f5f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-card {
            width: 100%;
            max-width: 430px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,.08);
        }
        .logo {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            background: #696cff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 25px;
            font-weight: bold;
        }
        .btn-main {
            background: #696cff;
            border: none;
            padding: 11px;
            color: white;
        }
        .btn-main:hover {
            background: #5f61e6;
            color: white;
        }
    </style>
</head>
<body>
<div class="container px-3">
    <div class="card reset-card mx-auto">
        <div class="card-body p-4 p-md-5">

            <div class="logo">R</div>

            <div class="text-center mb-4">
                <h4>Password Baru</h4>
                <p class="text-muted">
                    Buat password baru untuk akun RAPI-Sekolah Anda.
                </p>
            </div>

            <div id="message" class="alert d-none"></div>

            <form id="resetForm">
                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input
                        type="password"
                        id="password"
                        class="form-control"
                        placeholder="Minimal 8 karakter"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label">Konfirmasi Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        class="form-control"
                        placeholder="Ulangi password baru"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" id="submitBtn" class="btn btn-main w-100">
                    Simpan Password Baru
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}">Kembali ke Login</a>
            </div>

        </div>
    </div>
</div>

<script>
const email = sessionStorage.getItem('reset_email');
const otp = sessionStorage.getItem('reset_otp');

const form = document.getElementById('resetForm');
const message = document.getElementById('message');
const submitBtn = document.getElementById('submitBtn');

if (!email || !otp) {
    window.location.href = "{{ route('password.request') }}";
}

function showMessage(text, type = 'danger') {
    message.className = `alert alert-${type}`;
    message.textContent = text;
}

form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const password = document.getElementById('password').value;
    const passwordConfirmation =
        document.getElementById('password_confirmation').value;

    if (password.length < 8) {
        showMessage('Password minimal 8 karakter.');
        return;
    }

    if (password !== passwordConfirmation) {
        showMessage('Konfirmasi password tidak sama.');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
    message.classList.add('d-none');

    try {
        const response = await fetch('/api/reset-password', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                otp: otp,
                password: password,
                password_confirmation: passwordConfirmation
            })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Gagal mengubah password.');
        }

        sessionStorage.removeItem('reset_email');
        sessionStorage.removeItem('reset_otp');

        form.classList.add('d-none');

        showMessage(
            'Password berhasil diperbarui. Silakan login menggunakan password baru.',
            'success'
        );

        setTimeout(() => {
            window.location.href = "{{ route('login') }}";
        }, 2500);

    } catch (error) {
        showMessage(error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan Password Baru';
    }
});
</script>
</body>
</html>
