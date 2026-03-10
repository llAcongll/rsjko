/* =========================
   CORE FUNCTIONS (Global Scope)
========================= */

function guardPermission(permission) {
  if (!window.hasPermission(permission)) {
    if (typeof window.showToast === 'function') {
      window.showToast("Akses ditolak", "error");
    } else {
      alert("Akses ditolak");
    }
    return false;
  }
  return true;
}

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
    mainContent.innerHTML = "<div style='display:flex;justify-content:center;padding:40px;'><p>Ã¢³ Memuat...</p></div>";

    const res = await fetch(`/dashboard/content/${page}`);
    if (!res.ok) {
      mainContent.innerHTML = "<p>Ã¢Å’ Gagal memuat konten</p>";
      return false;
    }

    const html = await res.text();
    mainContent.innerHTML = html;
    return true;
  } catch (err) {
    console.error(err);
    mainContent.innerHTML = "<p>Ã¢Å’ Terjadi kesalahan</p>";
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
  if (!guardPermission('DASHBOARD_VIEW')) return;

  hideSubmenus();
  setActiveMenu(btn);
  closeOnMobile();

  await loadContent("dashboard");

  if (typeof window.initDashboard === 'function') {
    initDashboard();
  }
};

window.openRekening = (btn, type) => {
  const parentBtn = document.getElementById('btnPerencanaan');
  setActiveMenu(parentBtn);
  closeOnMobile();

  if (type) {
    window.openKodeRekening(type, btn);
    return;
  }

  loadContent("rekening");
  document
    .querySelectorAll('#submenuPerencanaan button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  setTimeout(() => {
    if (window.loadRekening) loadRekening();
  }, 300);
};

window.openKodeRekening = async (type, btn) => {
  const perm = type === 'PENGELUARAN' ? 'KODE_REKENING_PENGELUARAN_VIEW' : 'KODE_REKENING_PENDAPATAN_VIEW';
  if (!guardPermission(perm)) return;

  const parentBtn = document.getElementById('btnPerencanaan');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent(`master/kode-rekening/${type}`);

  document
    .querySelectorAll('#submenuPerencanaan button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  if (typeof window.initKodeRekening === 'function') {
    window.initKodeRekening(type);
  }
};

window.openAnggaran = (btn, type) => {
  if (type) {
    window.openAnggaranRekening(type, btn);
    return;
  }
};

window.openAnggaranRekening = async (type, btn) => {
  const perm = type === 'PENGELUARAN' ? 'ANGGARAN_PENGELUARAN_VIEW' : 'ANGGARAN_PENDAPATAN_VIEW';
  if (!guardPermission(perm)) return;

  const parentBtn = document.getElementById('btnPerencanaan');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent(`master/anggaran-rekening/${type}`);

  document
    .querySelectorAll('#submenuPerencanaan button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  if (typeof window.initAnggaranRekening === 'function') {
    window.initAnggaranRekening(type);
  }
};

window.openPiutang = async (btn) => {
  if (!guardPermission('PIUTANG_VIEW')) return;

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
  if (!guardPermission('PENYESUAIAN_VIEW')) return;

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

window.openPendapatanUmum = (btn) => window.openPendapatan('UMUM', btn);
window.openPendapatanBpjs = (btn) => window.openPendapatan('BPJS', btn);
window.openPendapatanJaminan = (btn) => window.openPendapatan('JAMINAN', btn);
window.openPendapatanKerjasama = (btn) => window.openPendapatan('KERJASAMA', btn);
window.openPendapatanLain = (btn) => window.openPendapatan('LAIN', btn);

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

  const permMap = {
    'PENDAPATAN': 'LAP_PENDAPATAN_VIEW',
    'PENGELUARAN': 'LAP_PENGELUARAN_VIEW',
    'REKON': 'LAP_REKON_VIEW',
    'PIUTANG': 'LAP_PIUTANG_VIEW',
    'ANGGARAN': 'LAP_LRA_VIEW',
    'LRA': 'LAP_LRA_VIEW',
    'LO': 'LAP_LO_VIEW',
    'NERACA': 'LAP_NERACA_VIEW',
    'LAK': 'LAP_LAK_VIEW',
    'LPE': 'LAP_LPE_VIEW',
    'LPSAL': 'LAP_LPSAL_VIEW',
    'CALK': 'LAP_CALK_VIEW',
    'RKA': 'LAP_RKA_VIEW',
    'RBA': 'LAP_RBA_VIEW',
    'DPA': 'LAP_DPA_VIEW'
  };

  const perm = permMap[type];
  if (perm && !guardPermission(perm)) return;

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
  if (!guardPermission('RUANGAN_VIEW')) return;

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
  if (!guardPermission('PERUSAHAAN_VIEW')) return;

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
  if (!guardPermission('MOU_VIEW')) return;

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
  if (!guardPermission('PENANDATANGAN_VIEW')) return;

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
  if (!guardPermission('USER_VIEW')) return;

  const parentBtn = document.getElementById('btnSystem');
  setActiveMenu(parentBtn);
  closeOnMobile();

  loadContent("users");

  document
    .querySelectorAll('#submenuSystem button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
};

window.openActivityLogs = async (btn) => {
  if (!guardPermission('LOG_VIEW')) return;

  const parentBtn = document.getElementById('btnSystem');
  setActiveMenu(parentBtn);
  closeOnMobile();

  await loadContent("master/logs");

  document
    .querySelectorAll('#submenuSystem button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  if (typeof window.initLogs === 'function') {
    window.initLogs();
  }
};

window.openRekeningKoran = async (btn) => {
  if (!guardPermission('REKKOR_VIEW')) return;

  const parentBtn = document.getElementById('btnKasPend');
  setActiveMenu(parentBtn);
  closeOnMobile();
  await loadContent('pendapatan/rekening-koran');
  document
    .querySelectorAll('#submenuKasPend button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  if (typeof window.initRekening === 'function') {
    window.initRekening();
  }
};

window.openRekeningKoranPengeluaran = async (btn) => {
  if (!guardPermission('REK_KORAN_PENG_VIEW')) return;

  const parentBtn = document.getElementById('btnKasPeng');
  setActiveMenu(parentBtn);
  closeOnMobile();
  await loadContent('pengeluaran/rekening-koran');
  document
    .querySelectorAll('#submenuKasPeng button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  if (typeof window.initBankLedger === 'function') {
    window.initBankLedger();
  }
};

window.openIncomeCashBook = async (btn) => {
  if (!guardPermission('BKU_PENDAPATAN_VIEW')) return;

  const parentBtn = document.getElementById('btnKasPend');
  setActiveMenu(parentBtn);
  closeOnMobile();
  await loadContent('pendapatan/BKU');
  document
    .querySelectorAll('#submenuKasPend button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  if (typeof window.initIncomeCashBook === 'function') {
    window.initIncomeCashBook();
  }
};

window.openTreasurerCash = async (btn) => {
  if (!guardPermission('BKU_PENGELUARAN_VIEW')) return;

  const parentBtn = document.getElementById('btnKasPeng');
  setActiveMenu(parentBtn);
  closeOnMobile();
  await loadContent('pengeluaran/ledger');
  document
    .querySelectorAll('#submenuKasPeng button')
    .forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  if (typeof window.initLedger === 'function') {
    window.initLedger();
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

window.togglePerencanaan = function (btn) {
  const sub = document.getElementById("submenuPerencanaan");
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

window.toggleKasPend = function (btn) {
  const sub = document.getElementById("submenuKasPend");
  const caret = btn.querySelector('.dropdown-icon');
  const isOpen = sub && sub.style.display === "block";
  hideSubmenus();
  if (sub && !isOpen) {
    sub.style.display = "block";
    if (caret) caret.style.transform = 'rotate(180deg)';
    setActiveMenu(btn);
  }
};

window.toggleKasPeng = function (btn) {
  const sub = document.getElementById("submenuKasPeng");
  const caret = btn.querySelector('.dropdown-icon');
  const isOpen = sub && sub.style.display === "block";
  hideSubmenus();
  if (sub && !isOpen) {
    sub.style.display = "block";
    if (caret) caret.style.transform = 'rotate(180deg)';
    setActiveMenu(btn);
  }
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

window.toggleSystem = function (btn) {
  const sub = document.getElementById("submenuSystem");
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

  const permMap = {
    'SP3BP': 'SP3BP_VIEW',
    'SPTJB': 'SPTJB_VIEW',
    'LRKB': 'LRKB_VIEW'
  };
  const perm = permMap[type.toUpperCase()];
  if (perm && !guardPermission(perm)) return;

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
  if (!guardPermission('BELANJA_VIEW')) return;

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
  if (!guardPermission('SPJ_VIEW')) return;
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
  if (!guardPermission('SPP_VIEW')) return;
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
  if (!guardPermission('SPM_VIEW')) return;
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
  if (!guardPermission('SP2D_VIEW')) return;
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
  if (!guardPermission('SP2D_VIEW')) return; // Pencairan usually tied to SP2D access
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

/* =========================
   ALIASES FOR SIDEBAR (Fix ReferenceError)
========================= */
window.openExpenditure = (btn) => window.openPengeluaran('PEGAWAI', btn);
window.openSpp = (btn) => window.openSppPage(btn);
window.openSpm = (btn) => window.openSpmPage(btn);
window.openSp2d = (btn) => window.openSp2dPage(btn);

window.openLaporanSpp = async function (btn) {
  if (!guardPermission('LAP_PENGELUARAN_VIEW')) return;
  const parentBtn = document.getElementById('btnLaporan');
  setActiveMenu(parentBtn);
  closeOnMobile();

  window._disbursementPageMode = 'REPORT_SPP';
  await loadContent('pengeluaran/disbursement');

  document
    .querySelectorAll('#submenuLaporan button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initDisbursement === 'function') {
    window.initDisbursement();
  }
};

window.openLaporanSpm = async function (btn) {
  if (!guardPermission('LAP_PENGELUARAN_VIEW')) return;
  const parentBtn = document.getElementById('btnLaporan');
  setActiveMenu(parentBtn);
  closeOnMobile();

  window._disbursementPageMode = 'REPORT_SPM';
  await loadContent('pengeluaran/disbursement');

  document
    .querySelectorAll('#submenuLaporan button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initDisbursement === 'function') {
    window.initDisbursement();
  }
};

window.openLaporanSp2d = async function (btn) {
  if (!guardPermission('LAP_PENGELUARAN_VIEW')) return;
  const parentBtn = document.getElementById('btnLaporan');
  setActiveMenu(parentBtn);
  closeOnMobile();

  window._disbursementPageMode = 'REPORT_SP2D';
  await loadContent('pengeluaran/disbursement');

  document
    .querySelectorAll('#submenuLaporan button')
    .forEach(b => b.classList.remove('active'));

  if (btn) btn.classList.add('active');

  if (typeof window.initDisbursement === 'function') {
    window.initDisbursement();
  }
};

window.openSaldoDana = async function (btn) {
  if (!guardPermission('BELANJA_VIEW')) return;

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
  const permMap = {
    'UMUM': 'PENDAPATAN_UMUM_VIEW',
    'BPJS': 'PENDAPATAN_BPJS_VIEW',
    'JAMINAN': 'PENDAPATAN_JAMINAN_VIEW',
    'KERJASAMA': 'PENDAPATAN_KERJA_VIEW',
    'LAIN': 'PENDAPATAN_LAIN_VIEW',
    'BKU': 'BKU_PENDAPATAN_VIEW'
  };

  const perm = permMap[jenis];
  if (perm && !guardPermission(perm)) return;

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
  // Use dynamic modal from treasurer.js if available (immune to CSS stacking issues)
  if (typeof window.showActionModal === 'function') {
    const iconName = icon.replace('ph-', '');
    const color = (btnClass === 'btn-danger') ? '#dc2626' : '#047857';
    window.showActionModal({
      icon: iconName,
      iconColor: color,
      title: title,
      message: message,
      confirmText: btnText,
      confirmIcon: iconName,
      confirmColor: color,
      onConfirm: () => { if (typeof onOk === 'function') onOk(); }
    });
    return;
  }

  // Fallback: native confirm
  if (confirm(message)) {
    if (typeof onOk === 'function') onOk();
  }
};

window.closeConfirm = function () {
  const modal = document.getElementById('confirmModal');
  if (modal) {
    modal.classList.remove('show');
  }
  // Also close dynamic modals
  document.querySelectorAll('.dynamic-confirm-overlay').forEach(el => el.remove());
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




