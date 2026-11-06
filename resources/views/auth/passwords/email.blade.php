
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password - RAPI Sekolah</title>
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
                <h4>Lupa Password?</h4>
                <p class="text-muted">
                    Masukkan email akun RAPI-Sekolah untuk menerima kode OTP.
                </p>
            </div>

            <div id="message" class="alert d-none"></div>

            <form id="forgotForm">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        id="email"
                        class="form-control"
                        placeholder="Masukkan email terdaftar"
                        autocomplete="email"
                        required
                    >
                </div>

                <button type="submit" id="submitBtn" class="btn btn-main w-100">
                    Kirim Kode OTP
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}">Kembali ke Login</a>
            </div>

        </div>
    </div>
</div>

<script>
const form = document.getElementById('forgotForm');
const message = document.getElementById('message');
const submitBtn = document.getElementById('submitBtn');

function showMessage(text, type = 'danger') {
    message.className = `alert alert-${type}`;
    message.textContent = text;
}

form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();

    submitBtn.disabled = true;
    submitBtn.textContent = 'Mengirim OTP...';
    message.classList.add('d-none');

    try {
        const response = await fetch('/api/forgot-password', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Gagal mengirim OTP.');
        }

        sessionStorage.setItem('reset_email', email);

        showMessage(
            'Jika email terdaftar, kode OTP akan dikirim. Periksa kotak masuk email Anda.',
            'success'
        );

        setTimeout(() => {
            window.location.href = "{{ route('password.verify') }}";
        }, 1200);

    } catch (error) {
        showMessage(error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Kirim Kode OTP';
    }
});
</script>
</body>
</html>
