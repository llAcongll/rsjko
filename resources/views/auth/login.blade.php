<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Login – Sistem Informasi Pendapatan</title>

  <link rel="icon" type="image/png" sizes="32x32"
    href="https://drive.google.com/uc?id=1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx">

  {{-- GOOGLE FONTS & ICONS --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>

  <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
</head>

<body>

  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <img src="https://drive.google.com/thumbnail?id=1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx&sz=w400" class="logo"
          alt="Logo Provinsi Kepulauan Riau">
        <div class="branding">
          <h1>SISTEM INFORMASI PENDAPATAN</h1>
          <h2>RSJKO ENGKU HAJI DAUD</h2>
          <p class="instansi">Provinsi Kepulauan Riau</p>
        </div>
      </div>

      <div class="login-body">
        <div class="input-group">
          <label for="username">Username</label>
          <div class="input-wrapper">
            <i class="ph ph-user"></i>
            <input type="text" id="username" placeholder="Masukkan username">
          </div>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <i class="ph ph-lock"></i>
            <input type="password" id="password" placeholder="Masukkan password">
            <button type="button" class="toggle-pass" id="btn-toggle-pass">
              <i class="ph ph-eye" id="eye-icon"></i>
            </button>
          </div>
        </div>

        <div class="input-group">
          <label for="tahun">Tahun Anggaran</label>
          <div class="input-wrapper">
            <i class="ph ph-calendar-blank"></i>
            <select id="tahun" class="select-year">
              <option value="2025">Tahun Anggaran 2025</option>
              <option value="2026" selected>Tahun Anggaran 2026</option>
              <option value="2027">Tahun Anggaran 2027</option>
              <option value="2028">Tahun Anggaran 2028</option>
              <option value="2029">Tahun Anggaran 2029</option>
            </select>
            <i class="ph ph-caret-down select-caret"></i>
          </div>
        </div>

        <button id="btn" class="btn-login">
          <span>Masuk ke Sistem</span>
          <i class="ph ph-arrow-right"></i>
        </button>

        <div class="msg" id="msg"></div>
      </div>

      <div class="login-footer">
        &copy; {{ date('Y') }} RSJKO Engku Haji Daud
      </div>
    </div>
  </div>

  {{-- MODAL KONFIRMASI --}}
  <div id="confirmModal" class="confirm-overlay">
    <div class="confirm-box">
      <h3 id="confirmTitle">Konfirmasi</h3>
      <p id="confirmMessage"></p>
      <div class="confirm-actions">
        <button class="btn-cancel" onclick="closeConfirm()">Batal</button>
        <button class="btn-danger" onclick="handleConfirmOk()">Ya</button>
      </div>
    </div>
  </div>

  <div id="toast" class="toast"></div>

  <script src="{{ asset('js/auth/login.js') }}"></script>
  <script src="{{ asset('js/base.js') }}"></script>

  @if(session('toast'))
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        toast('{{ session('toast') }}', 'success');
      });
    </script>
  @endif



</body>

</html>