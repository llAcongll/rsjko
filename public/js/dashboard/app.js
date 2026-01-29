document.addEventListener("DOMContentLoaded", () => {

  const mainContent = document.getElementById("mainContent");
  const submenuPendapatan = document.getElementById("submenuPendapatan");

  window.loadContent = async function (page) {
    if (!mainContent) {
      console.error("mainContent element not found");
      return;
    }

    try {
      mainContent.innerHTML = "<p>⏳ Memuat...</p>";

      const res = await fetch(`/dashboard/content/${page}`);

      if (!res.ok) {
        mainContent.innerHTML = "<p>❌ Gagal memuat konten</p>";
        return;
      }

      mainContent.innerHTML = await res.text();

    } catch (err) {
      console.error(err);
      mainContent.innerHTML = "<p>❌ Terjadi kesalahan</p>";
    }
  };

function setActiveMenu(btn) {
  document
    .querySelectorAll('.sidebar button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');
}

window.openDashboard = (btn) => {
  hideSubmenu();
  setActiveMenu(btn);
  loadContent("dashboard");
};

window.openRekening = (btn) => {
  hideSubmenu();
  setActiveMenu(btn);
  loadContent("rekening");

  setTimeout(() => {
    if (window.loadRekening) loadRekening();
  }, 300);
};

window.openLaporan = (btn) => {
  hideSubmenu();
  setActiveMenu(btn);
  loadContent("laporan");
};

window.openRuangan = (btn) => {
  hideSubmenu();
  setActiveMenu(btn);
  loadContent("ruangan");

  // 🔥 INI KUNCI UTAMA
  setTimeout(() => {
    if (typeof window.loadRuangan === 'function') {
      loadRuangan();
    }
  }, 300);
};

window.openUsers = (btn) => {
  hideSubmenu();
  setActiveMenu(btn);
  loadContent("users");
};

window.togglePendapatan = function (btn) {
  const submenuPendapatan =
    document.getElementById("submenuPendapatan");

  if (!submenuPendapatan) return;

  const isOpen = submenuPendapatan.style.display === "block";

  hideSubmenu();

  if (!isOpen) {
    submenuPendapatan.style.display = "block";
    setActiveMenu(btn);
  }
};

window.openPendapatan = async function (jenis, btn) {
  if (!jenis) return;

  jenis = jenis.toUpperCase();

  const parentBtn = document.getElementById('btnPendapatan');
  setActiveMenu(parentBtn);

  // ⏳ TUNGGU HTML BENAR-BENAR MASUK
  await loadContent(`pendapatan/${jenis}`);

  document
    .querySelectorAll('#submenuPendapatan button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  // 🔥 HOOK KHUSUS PENDAPATAN UMUM
  if (jenis === 'UMUM') {
    if (typeof initPendapatanUmum === 'function') {
      initPendapatanUmum();
    }

    if (typeof loadPendapatanUmum === 'function') {
      loadPendapatanUmum();
    }
  }
};

  function hideSubmenu() {
    if (submenuPendapatan) submenuPendapatan.style.display = "none";
  }

  window.openPreview = function (title, html) {
    const modal = document.getElementById("previewModal");
    if (!modal) return;
    document.getElementById("previewTitle").innerText = title;
    document.getElementById("previewContent").innerHTML = html;
    modal.style.opacity = "1";
    modal.style.pointerEvents = "auto";
  };

  window.closePreview = function () {
    const modal = document.getElementById("previewModal");
    if (!modal) return;
    modal.style.opacity = "0";
    modal.style.pointerEvents = "none";
  };

  // AUTO LOAD
  loadContent("dashboard");
});

function csrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : '';
}

// =========================
// GLOBAL CONFIRM MODAL
// =========================
window.confirmCallback = null;

window.openConfirm = function (title, message, onOk) {
  const modal = document.getElementById('confirmModal');
  if (!modal) return;

  document.getElementById('confirmTitle').innerText = title;
  document.getElementById('confirmMessage').innerText = message;

  window.confirmCallback = onOk; // 🔑 KUNCI UTAMA

  modal.style.opacity = '1';
  modal.style.pointerEvents = 'auto';
};

window.closeConfirm = function () {
  const modal = document.getElementById('confirmModal');
  if (!modal) return;

  modal.style.opacity = '0';
  modal.style.pointerEvents = 'none';
};

window.handleConfirmOk = function () {
  if (typeof window.confirmCallback === 'function') {
    window.confirmCallback();   // 🔥 DELETE DIEKSEKUSI DI SINI
  }
  window.confirmCallback = null;
  closeConfirm();
};

window.confirmLogout = function () {
  openConfirm(
    'Keluar dari Sistem',
    'Sesi Anda akan diakhiri dan perlu login kembali.',
    () => doLogout()
  );
};

function doLogout() {
  const form = document.getElementById('logoutForm');
  const btn  = form.querySelector('button');

  // disable button
  btn.disabled = true;
  btn.innerHTML = '⏳ Keluar...';

  // submit form
  form.submit();
}

