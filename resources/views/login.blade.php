<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Login - RAPI Sekolah</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
  body {
    min-height: 100vh;
    background: #f5f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .login-card {
    width: 100%;
    max-width: 430px;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
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

  .login-title {
    font-weight: 600;
  }

  .btn-login {
    background: #696cff;
    border: none;
    padding: 11px;
  }

  .btn-login:hover {
    background: #5f61e6;
  }

  #loginAlert {
    display: none;
  }
  </style>
</head>

<body>

  <div class="container px-3">

    <div class="card login-card mx-auto">

      <div class="card-body p-4 p-md-5">

        <div class="logo">
          R
        </div>

        <div class="text-center mb-4">
          <h4 class="login-title mb-2">
            RAPI Sekolah
          </h4>

          <p class="text-muted mb-0">
            Rencana Agenda dan Penjadwalan Terintegrasi
          </p>
        </div>

        <div id="loginAlert" class="alert alert-danger" role="alert"></div>

        <form id="loginForm">

          <div class="mb-3">
            <label for="email" class="form-label">
              Email
            </label>

            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email"
              autocomplete="email" required>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label">
              Password
            </label>

            <div class="input-group">

              <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password"
                autocomplete="current-password" required>

              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                👁
              </button>

            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-login w-100" id="loginButton">
            <span id="loginButtonText">
              Login
            </span>

            <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
          </button>

        </form>
        <div class="text-end mb-3">
            <a href="{{ route('password.request') }}">
                Lupa Password?
            </a>
        </div>

        <div class="text-center mt-4">
          <small class="text-muted">
            RAPI-Sekolah &copy; {{ date('Y') }}
          </small>
        </div>

      </div>

    </div>

  </div>


  <script>
  const loginForm = document.getElementById('loginForm');
  const loginAlert = document.getElementById('loginAlert');

  const loginButton = document.getElementById('loginButton');
  const loginButtonText = document.getElementById('loginButtonText');
  const loginSpinner = document.getElementById('loginSpinner');

  const passwordInput = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');


  /*
  ======================================
  TOGGLE PASSWORD
  ======================================
  */

  togglePassword.addEventListener('click', function() {

    if (passwordInput.type === 'password') {

      passwordInput.type = 'text';
      this.textContent = '🙈';

    } else {

      passwordInput.type = 'password';
      this.textContent = '👁';

    }

  });


  /*
  ======================================
  LOGIN
  ======================================
  */

  loginForm.addEventListener('submit', async function(event) {

    event.preventDefault();

    loginAlert.style.display = 'none';

    const email = document.getElementById('email').value.trim();
    const password = passwordInput.value;

    if (!email || !password) {

      showError('Email dan password wajib diisi.');

      return;
    }


    /*
    ==================================
    LOADING
    ==================================
    */

    loginButton.disabled = true;

    loginButtonText.textContent = 'Memproses...';

    loginSpinner.classList.remove('d-none');


    try {

      const response = await fetch('/api/login', {

        method: 'POST',

        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },

        body: JSON.stringify({
          email: email,
          password: password
        })

      });


      const result = await response.json();


      /*
      ==================================
      LOGIN GAGAL
      ==================================
      */

      if (!response.ok) {

        throw new Error(
          result.message || 'Email atau password salah.'
        );

      }


      /*
      ==================================
      SIMPAN TOKEN
      ==================================
      */

      localStorage.setItem(
        'rapi_token',
        result.token
      );


      /*
      ==================================
      SIMPAN DATA USER
      ==================================
      */

      localStorage.setItem(
        'rapi_user',
        JSON.stringify(result.user)
      );


      /*
      ==================================
      REDIRECT
      ==================================
      */

      window.location.href = '/rapi/dashboard';


    } catch (error) {

      showError(error.message);

    } finally {

      loginButton.disabled = false;

      loginButtonText.textContent = 'Login';

      loginSpinner.classList.add('d-none');

    }

  });


  /*
  ======================================
  ERROR MESSAGE
  ======================================
  */

  function showError(message) {

    loginAlert.textContent = message;

    loginAlert.style.display = 'block';

  }
  </script>

</body>

</html>