document.addEventListener("DOMContentLoaded", () => {

  const mainContent = document.getElementById("mainContent");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.querySelector(".sidebar-overlay");

  /* =========================
     CORE CONTENT LOADER
  ========================= */
  window.loadContent = async function (page) {
    if (!mainContent) return false;

    try {
      mainContent.innerHTML = "<div style='display:flex;justify-content:center;padding:40px;'><p>⏳ Memuat...</p></div>";

      const res = await fetch(`/dashboard/content/${page}`);
      if (!res.ok) {
        mainContent.innerHTML = "<p>❌ Gagal memuat konten</p>";
        return false;
      }

      const html = await res.text();
      mainContent.innerHTML = html;
      return true;
    } catch (err) {
      console.error(err);
      mainContent.innerHTML = "<p>❌ Terjadi kesalahan</p>";
      return false;
    }
  };

  window.setActiveMenu = function (btn) {
    document
      .querySelectorAll('.sidebar-content button')
      .forEach(b => b.classList.remove('active'));

    if (btn) btn.classList.add('active');
  };

  /* =========================
     RESPONSIVE SIDEBAR
  ========================= */
  window.toggleSidebar = function () {
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
  };

  window.closeOnMobile = () => {
    if (window.innerWidth <= 1024) {
      if (sidebar) sidebar.classList.remove('show');
      if (overlay) overlay.classList.remove('show');
    }
  };

  /* =========================
     DASHBOARD
  ========================= */
  window.openDashboard = async (btn) => {
    hideSubmenus();
    setActiveMenu(btn);
    closeOnMobile();

    await loadContent("dashboard");

    if (typeof window.initDashboard === 'function') {
      initDashboard();
    }
  };

  /* =========================
     REKENING KORAN
  ========================= */
  window.openRekening = (btn) => {
    hideSubmenus();
    setActiveMenu(btn);
    closeOnMobile();
    loadContent("rekening");

    setTimeout(() => {
      if (window.loadRekening) loadRekening();
    }, 300);
  };

  /* =========================
     PIUTANG
  ========================= */
  window.openPiutang = async (btn) => {
    hideSubmenus();
    setActiveMenu(btn);
    closeOnMobile();

    await loadContent("piutang");

    if (typeof window.initPiutang === 'function') {
      window.initPiutang();
    }
  };

  /* =========================
     POTONGAN & ADM BANK (PENYESUAIAN)
  ========================= */
  window.openPenyesuaian = async (btn) => {
    hideSubmenus();
    setActiveMenu(btn);
    closeOnMobile();

    await loadContent("penyesuaian");

    if (typeof window.initPenyesuaian === 'function') {
      window.initPenyesuaian();
    }
  };

  /* =========================
     LAPORAN
  ========================= */
  /* =========================
     LAPORAN (SUBMENU)
  ========================= */
  window.toggleLaporan = function (btn) {
    const sub = document.getElementById("submenuLaporan");
    const caret = btn.querySelector('.dropdown-icon');

    const isOpen = sub.style.display === "block";
    hideSubmenus();

    if (!isOpen) {
      sub.style.display = "block";
      if (caret) caret.style.transform = 'rotate(180deg)';
      setActiveMenu(btn);
    }
  };

  window.openLaporan = async function (type, btn) {
    if (!type) return;

    type = type.toUpperCase();
    const parentBtn = document.getElementById('btnLaporan');
    setActiveMenu(parentBtn);
    closeOnMobile();

    await loadContent(`laporan/${type}`);

    document
      .querySelectorAll('#submenuLaporan button')
      .forEach(b => b.classList.remove('active'));

    if (btn) btn.classList.add('active');

    if (typeof window.initLaporan === 'function') {
      window.initLaporan(type);
    }
  };

  /* =========================
     RUANGAN
  ========================= */
  window.openRuangan = async (btn) => {
    setActiveMenu(btn);
    closeOnMobile();

    await loadContent("ruangan");

    const table = document.getElementById('ruanganTable');
    if (!table) return;

    const search = document.getElementById('ruanganSearch');
    if (search) search.value = '';

    if (typeof window.loadRuanganTable === 'function') {
      window.loadRuanganTable();
    }

    if (typeof bindPaginationRuangan === 'function') {
      bindPaginationRuangan();
    }
  };

  /* =========================
     PERUSAHAAN
  ========================= */
  window.openPerusahaanPage = async (btn) => {
    setActiveMenu(btn);
    closeOnMobile();

    await loadContent("perusahaan");

    const table = document.getElementById('perusahaanTable');
    if (!table) return;

    const search = document.getElementById('perusahaanSearch');
    if (search) search.value = '';

    if (typeof window.loadPerusahaanTable === 'function') {
      window.loadPerusahaanTable();
    }
  };

  /* =========================
     MOU
  ========================= */
  window.openMouPage = async (btn) => {
    setActiveMenu(btn);
    closeOnMobile();

    await loadContent("mou");

    const table = document.getElementById('mouTable');
    if (!table) return;

    const search = document.getElementById('mouSearch');
    if (search) search.value = '';

    if (typeof window.loadMouTable === 'function') {
      window.loadMouTable();
    }
  };

  /* =========================
     USERS
  ========================= */
  window.openUsers = (btn) => {
    setActiveMenu(btn);
    closeOnMobile();
    loadContent("users");
  };

  /* =========================
     MASTER DATA
     Catatan: openKodeRekening dan openAnggaranRekening
     sudah didefinisikan di kode-rekening.js dan anggaran-rekening.js
     dengan logic inisialisasi yang lengkap. JANGAN definisikan di sini
     karena DOMContentLoaded akan menimpa definisi dari file tersebut.
  ========================= */

  /* =========================
     SUBMENU LOGIC
  ========================= */
  window.togglePendapatan = function (btn) {
    const sub = document.getElementById("submenuPendapatan");
    const caret = btn.querySelector('.dropdown-icon');

    const isOpen = sub.style.display === "block";
    hideSubmenus();

    if (!isOpen) {
      sub.style.display = "block";
      if (caret) caret.style.transform = 'rotate(180deg)';
      setActiveMenu(btn);
    }
  };

  window.openPendapatan = async function (jenis, btn) {
    if (!jenis) return;

    jenis = jenis.toUpperCase();
    const parentBtn = document.getElementById('btnPendapatan');
    setActiveMenu(parentBtn);
    closeOnMobile();

    await loadContent(`pendapatan/${jenis}`);

    document
      .querySelectorAll('#submenuPendapatan button')
      .forEach(b => b.classList.remove('active'));

    if (btn) btn.classList.add('active');

    // Init relevant JS for the module
    const inits = {
      'UMUM': () => {
        if (typeof initPendapatanUmum === 'function') initPendapatanUmum();
        if (typeof loadPendapatanUmum === 'function') loadPendapatanUmum();
      },
      'BPJS': () => {
        if (typeof initPendapatanBpjs === 'function') initPendapatanBpjs();
        if (typeof loadPendapatanBpjs === 'function') loadPendapatanBpjs();
      },
      'JAMINAN': () => {
        if (typeof initPendapatanJaminan === 'function') initPendapatanJaminan();
        if (typeof loadPendapatanJaminan === 'function') loadPendapatanJaminan();
      },
      'KERJASAMA': () => {
        if (typeof initPendapatanKerjasama === 'function') initPendapatanKerjasama();
        if (typeof loadPendapatanKerjasama === 'function') loadPendapatanKerjasama();
      },
      'LAIN': () => {
        if (typeof initPendapatanLain === 'function') initPendapatanLain();
        if (typeof loadPendapatanLain === 'function') loadPendapatanLain();
      }
    };

    if (inits[jenis]) inits[jenis]();
  };

  window.toggleMaster = function (btn) {
    const sub = document.getElementById("submenuMaster");
    const caret = btn.querySelector('.dropdown-icon');

    const isOpen = sub.style.display === "block";
    hideSubmenus();

    if (!isOpen) {
      sub.style.display = "block";
      if (caret) caret.style.transform = 'rotate(180deg)';
      setActiveMenu(btn);
    }
  };

  function hideSubmenus() {
    document.querySelectorAll('.submenu-child').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.dropdown-icon').forEach(c => c.style.transform = 'rotate(0deg)');
  }

  /* =========================
     AUTO LOAD
  ========================= */
  (async () => {
    await loadContent("dashboard");
    if (typeof window.initDashboard === 'function') initDashboard();
  })();

});

/* =========================
   GLOBAL UTILS
 ========================= */
function csrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : '';
}

window.confirmCallback = null;
let isLoggingOut = false;

window.openConfirm = function (title, message, onOk, btnText = 'Hapus', icon = 'ph-trash', btnClass = 'btn-danger') {
  const modal = document.getElementById('confirmModal');
  if (!modal) return;

  modal.classList.remove('show');

  const titleEl = document.getElementById('confirmTitle');
  const msgEl = document.getElementById('confirmMessage');
  if (titleEl) titleEl.innerText = title;
  if (msgEl) msgEl.innerText = message;

  const iconEl = modal.querySelector('.confirm-icon i');
  if (iconEl) {
    iconEl.className = `ph ${icon}`;
    iconEl.style.color = (btnClass === 'btn-danger') ? '#ef4444' : '#3b82f6';
  }

  const okBtn = modal.querySelector('.modal-actions .btn-ok');
  if (okBtn) {
    okBtn.innerText = btnText;
    okBtn.className = `btn-ok ${btnClass}`;
  }

  window.confirmCallback = onOk;
  setTimeout(() => {
    modal.classList.add('show');
  }, 10);
};

window.closeConfirm = function () {
  const modal = document.getElementById('confirmModal');
  if (modal) {
    modal.classList.remove('show');
  }
  window.confirmCallback = null;
};

window.handleConfirmOk = function () {
  const cb = window.confirmCallback;
  window.closeConfirm();
  if (typeof cb === 'function') {
    cb();
  }
};

window.confirmLogout = function () {
  if (isLoggingOut) return;

  if (typeof window.closeOnMobile === 'function') {
    window.closeOnMobile();
  }

  openConfirm(
    'Keluar dari Sistem',
    'Sesi Anda akan diakhiri dan perlu login kembali.',
    () => doLogout(),
    'Keluar',
    'ph-sign-out',
    'btn-primary'
  );
};

function doLogout() {
  if (isLoggingOut) return;

  const form = document.getElementById('logoutForm');
  if (!form) {
    console.error('Logout form not found');
    return;
  }

  isLoggingOut = true;
  const btn = form.querySelector('.btn-logout');

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> <span>Keluar...</span>';
  }

  // Use native submit to bypass any potential overrides or event listeners
  HTMLFormElement.prototype.submit.call(form);
}
