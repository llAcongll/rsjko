<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Login – Sistem Informasi Pendapatan</title>

  <link rel="icon" type="image/png" sizes="32x32"
        href="https://drive.google.com/uc?id=1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx">

  <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
</head>

<body>

<div class="card">
  <img
    src="https://drive.google.com/thumbnail?id=1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx&sz=w400"
    class="logo"
    alt="Logo Provinsi Kepulauan Riau">

  <h1>SISTEM INFORMASI PENDAPATAN</h1>
  <h2>RSJKO ENGKU HAJI DAUD</h2>

  <div class="instansi">Provinsi Kepulauan Riau</div>

  <input type="text" id="username" placeholder="Username">

  <div class="password-wrap">
    <input type="password" id="password" placeholder="Password">
    <span class="toggle-pass" onclick="togglePassword()">👁️</span>
  </div>

  <button id="btn">Masuk</button>
  <div class="msg" id="msg"></div>
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
