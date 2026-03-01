/* =========================
   CORE FUNCTIONS (Global Scope)
========================= */

function hideSubmenus() {
  document.querySelectorAll('.submenu-child').forEach(s => s.style.display = 'none');
  document.querySelectorAll('.dropdown-icon').forEach(c => c.style.transform = 'rotate(0deg)');
}

window.setActiveMenu = function (btn) {
  document
    .querySelectorAll('.sidebar-content button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');
};

window.loadContent = async function (page) {
  const mainContent = document.getElementById("mainContent");
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

window.toggleSidebar = function () {
  const sidebar = document.getElementById("sidebar");
  const overlay = document.querySelector(".sidebar-overlay");
  if (sidebar) sidebar.classList.toggle('show');
  if (overlay) overlay.classList.toggle('show');
};

window.closeOnMobile = () => {
  const sidebar = document.getElementById("sidebar");
  const overlay = document.querySelector(".sidebar-overlay");
  if (window.innerWidth <= 1024) {
    if (sidebar) sidebar.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
  }
};


/* =========================
   MODULE OPENERS
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

window.openRekening = (btn) => {
  const parentBtn = document.getElementById('btnPendapatan');
  setActiveMenu(parentBtn);
  closeOnMobile();

  loadContent("rekening");

  document
    .querySelectorAll('#submenuPendapatan button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  setTimeout(() => {
    if (window.loadRekening) loadRekening();
  }, 300);
};

window.openPiutang = async (btn) => {
  const parentBtn = document.getElementById('btnPendapatan');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent("piutang");

  document
    .querySelectorAll('#submenuPendapatan button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  if (typeof window.initPiutang === 'function') {
    window.initPiutang();
  }
};

window.openPenyesuaian = async (btn) => {
  const parentBtn = document.getElementById('btnPendapatan');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent("penyesuaian");

  document
    .querySelectorAll('#submenuPendapatan button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  if (typeof window.initPenyesuaian === 'function') {
    window.initPenyesuaian();
  }
};

window.toggleLaporan = function (btn) {
  const sub = document.getElementById("submenuLaporan");
  const caret = btn.querySelector('.dropdown-icon');

  const isOpen = sub && sub.style.display === "block";
  hideSubmenus();

  if (sub && !isOpen) {
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

window.openRuangan = async (btn) => {
  const parentBtn = document.getElementById('btnMaster');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent("ruangan");

  document
    .querySelectorAll('#submenuMaster button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

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

window.openPerusahaanPage = async (btn) => {
  const parentBtn = document.getElementById('btnMaster');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent("perusahaan");

  document
    .querySelectorAll('#submenuMaster button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  const table = document.getElementById('perusahaanTable');
  if (!table) return;

  const search = document.getElementById('perusahaanSearch');
  if (search) search.value = '';

  if (typeof window.loadPerusahaanTable === 'function') {
    window.loadPerusahaanTable();
  }
};

window.openMouPage = async (btn) => {
  const parentBtn = document.getElementById('btnMaster');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent("mou");

  document
    .querySelectorAll('#submenuMaster button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  const table = document.getElementById('mouTable');
  if (!table) return;

  const search = document.getElementById('mouSearch');
  if (search) search.value = '';

  if (typeof window.loadMouTable === 'function') {
    window.loadMouTable();
  }
};

window.openPenandaTangan = async (btn) => {
  const parentBtn = document.getElementById('btnMaster');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent("penanda_tangan");

  document
    .querySelectorAll('#submenuMaster button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  if (typeof window.initPenandaTangan === 'function') {
    window.initPenandaTangan();
  }
};

window.openUsers = (btn) => {
  const parentBtn = document.getElementById('btnMaster');
  setActiveMenu(parentBtn);
  closeOnMobile();

  loadContent("users");

  document
    .querySelectorAll('#submenuMaster button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
};

window.openActivityLogs = async (btn) => {
  const parentBtn = document.getElementById('btnMaster');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent("master/logs");

  document
    .querySelectorAll('#submenuMaster button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  if (typeof window.initLogs === 'function') {
    window.initLogs();
  }
};

/* =========================
   SUBMENU LOGIC
========================= */
window.togglePendapatan = function (btn) {
  const sub = document.getElementById("submenuPendapatan");
  const caret = btn.querySelector('.dropdown-icon');

  const isOpen = sub && sub.style.display === "block";
  hideSubmenus();

  if (sub && !isOpen) {
    sub.style.display = "block";
    if (caret) caret.style.transform = 'rotate(180deg)';
    setActiveMenu(btn);
  }
};

window.togglePengeluaran = function (btn) {
  const sub = document.getElementById("submenuPengeluaran");
  const caret = btn.querySelector('.dropdown-icon');

  const isOpen = sub && sub.style.display === "block";
  hideSubmenus();

  if (sub && !isOpen) {
    sub.style.display = "block";
    if (caret) caret.style.transform = 'rotate(180deg)';
    setActiveMenu(btn);
  }
};

window.togglePengesahan = function (btn) {
  const sub = document.getElementById("submenuPengesahan");
  const caret = btn.querySelector('.dropdown-icon');

  const isOpen = sub && sub.style.display === "block";
  hideSubmenus();

  if (sub && !isOpen) {
    sub.style.display = "block";
    if (caret) caret.style.transform = 'rotate(180deg)';
    setActiveMenu(btn);
  }
};

window.openPengesahan = async function (type, btn) {
  if (!type) return;

  type = type.toUpperCase();
  const parentBtn = document.getElementById('btnPengesahan');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent(`pengesahan/${type}`);

  document
    .querySelectorAll('#submenuPengesahan button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (type === 'SP3BP' && typeof window.initSp3bp === 'function') window.initSp3bp();
  if (type === 'SPTJB' && typeof window.initSptjb === 'function') window.initSptjb();
  if (type === 'LRKB' && typeof window.initLrkb === 'function') window.initLrkb();
};

window.openPengeluaran = async function (kategori, btn) {
  if (!kategori) return;

  const parentBtn = document.getElementById('btnPengeluaran');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent(`pengeluaran/${kategori}`);

  document
    .querySelectorAll('#submenuPengeluaran button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  // Let pengeluaran.js handle its own initialization since it loads dynamically
  if (typeof window.initPengeluaran === 'function') {
    window.initPengeluaran(kategori);
  }
};

window.openSpj = async function (btn) {
  const parentBtn = document.getElementById('btnPengeluaran');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent('pengeluaran/spj');

  document
    .querySelectorAll('#submenuPengeluaran button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initSpj === 'function') {
    window.initSpj();
  }
};

window.openSppPage = async function (btn) {
  const parentBtn = document.getElementById('btnPengeluaran');
  setActiveMenu(parentBtn);
  closeOnMobile();

  window._disbursementPageMode = 'SPP';
  await loadContent('pengeluaran/disbursement');

  document
    .querySelectorAll('#submenuPengeluaran button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initDisbursement === 'function') {
    window.initDisbursement();
  }
};

window.openSpmPage = async function (btn) {
  const parentBtn = document.getElementById('btnPengeluaran');
  setActiveMenu(parentBtn);
  closeOnMobile();

  window._disbursementPageMode = 'SPM';
  await loadContent('pengeluaran/disbursement');

  document
    .querySelectorAll('#submenuPengeluaran button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initDisbursement === 'function') {
    window.initDisbursement();
  }
};

window.openSp2dPage = async function (btn) {
  const parentBtn = document.getElementById('btnPengeluaran');
  setActiveMenu(parentBtn);
  closeOnMobile();

  window._disbursementPageMode = 'SP2D';
  await loadContent('pengeluaran/disbursement');

  document
    .querySelectorAll('#submenuPengeluaran button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initDisbursement === 'function') {
    window.initDisbursement();
  }
};

window.openPencairanPage = async function (btn) {
  const parentBtn = document.getElementById('btnPengeluaran');
  setActiveMenu(parentBtn);
  closeOnMobile();

  window._disbursementPageMode = 'PENCAIRAN';
  await loadContent('pengeluaran/disbursement');

  document
    .querySelectorAll('#submenuPengeluaran button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initDisbursement === 'function') {
    window.initDisbursement();
  }
};

window.openTreasurerCash = async function (btn) {
  const parentBtn = document.getElementById('btnPengeluaran');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent('pengeluaran/ledger');

  document
    .querySelectorAll('#submenuPengeluaran button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initLedger === 'function') {
    window.initLedger();
  }
};

window.openRekeningKoranPengeluaran = async function (btn) {
  const parentBtn = document.getElementById('btnPengeluaran');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent('pengeluaran/rekening-koran');

  document
    .querySelectorAll('#submenuPengeluaran button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initBankLedger === 'function') {
    window.initBankLedger();
  }
};

window.openSaldoDana = async function (btn) {
  const parentBtn = document.getElementById('btnPengeluaran');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent('pengeluaran/saldo');

  document
    .querySelectorAll('#submenuPengeluaran button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initSaldoDana === 'function') {
    window.initSaldoDana();
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

  const isOpen = sub && sub.style.display === "block";
  hideSubmenus();

  if (sub && !isOpen) {
    sub.style.display = "block";
    if (caret) caret.style.transform = 'rotate(180deg)';
    setActiveMenu(btn);
  }
};


/* =========================
   AUTO LOAD
========================= */
document.addEventListener("DOMContentLoaded", () => {

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

  // Update sidebar button visually
  const btn = form.querySelector('.btn-logout');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> <span>Keluar...</span>';
  }

  // Tampilkan overlay global agar layar tidak bisa diklik (mencegah interaksi ganda)
  const loader = document.getElementById('globalLoader');
  if (loader) loader.classList.add('show');

  // Gunakan metode submit standar
  try {
    form.submit();
  } catch (err) {
    HTMLFormElement.prototype.submit.call(form);
  }
}

/* =========================
   GLOBAL MODAL CLOSERS
   (Close on ESC or Background Click)
   Fitur ini dinonaktifkan agar modal hanya bisa ditutup via tombol Batal/X
========================= */
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    // const activeModal = document.querySelector('.confirm-overlay.show');
    // if (activeModal) {
    //   activeModal.classList.remove('show');
    //   activeModal.dispatchEvent(new Event('modalClosed'));
    // }
  }
});

document.addEventListener('click', (e) => {
  if (e.target.classList.contains('confirm-overlay')) {
    // e.target.classList.remove('show');
    // e.target.dispatchEvent(new Event('modalClosed'));
  }
});
