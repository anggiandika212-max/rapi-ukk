
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi OTP - RAPI Sekolah</title>
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
                <h4>Verifikasi OTP</h4>
                <p class="text-muted">
                    Masukkan kode OTP 6 digit yang dikirim ke email Anda.
                </p>
                <small id="emailInfo" class="text-muted"></small>
            </div>

            <div id="message" class="alert d-none"></div>

            <form id="verifyForm">
                <div class="mb-3">
                    <label class="form-label">Kode OTP</label>
                    <input
                        type="text"
                        id="otp"
                        class="form-control text-center"
                        placeholder="Masukkan 6 digit OTP"
                        inputmode="numeric"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        autocomplete="one-time-code"
                        required
                    >
                </div>

                <button type="submit" id="submitBtn" class="btn btn-main w-100">
                    Verifikasi OTP
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('password.request') }}">Kirim OTP baru</a>
                <br>
                <a href="{{ route('login') }}" class="d-inline-block mt-2">Kembali ke Login</a>
            </div>

        </div>
    </div>
</div>

<script>
const email = sessionStorage.getItem('reset_email');
const form = document.getElementById('verifyForm');
const message = document.getElementById('message');
const submitBtn = document.getElementById('submitBtn');

if (!email) {
    window.location.href = "{{ route('password.request') }}";
} else {
    document.getElementById('emailInfo').textContent = email;
}

function showMessage(text, type = 'danger') {
    message.className = `alert alert-${type}`;
    message.textContent = text;
}

document.getElementById('otp').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
});

form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const otp = document.getElementById('otp').value.trim();

    if (!/^\d{6}$/.test(otp)) {
        showMessage('Masukkan OTP yang terdiri dari 6 digit.');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Memverifikasi...';
    message.classList.add('d-none');

    try {
        const response = await fetch('/api/verify-otp', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, otp })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Verifikasi OTP gagal.');
        }

        sessionStorage.setItem('reset_otp', otp);

        window.location.href = "{{ route('password.reset') }}";

    } catch (error) {
        showMessage(error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Verifikasi OTP';
    }
});
</script>
</body>
</html>
