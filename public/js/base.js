// =========================
// GLOBAL TOAST NOTIFICATION
// =========================
window.toast = function (message = '', type = 'info') {
  if (!message) message = 'Notifikasi';

  const el = document.getElementById('toast');
  if (!el) return;

  el.className = `toast ${type}`;
  el.textContent = String(message);

  el.classList.add('show');

  setTimeout(() => {
    el.classList.remove('show');
  }, 3000);
};

// =========================
// SHARED UTILS
// =========================
window.formatRibuan = function (num) {
  if (!num) return '0';
  let parts = num.toString().split('.');
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  return parts.join(',');
};

window.parseAngka = function (val) {
  if (!val) return 0;
  // Hapus semua karakter kecuali angka, koma, titik, dan minus
  let s = val.toString().replace(/[^0-9,.-]/g, '');

  // Di format Indonesia: titik adalah ribuan, koma adalah desimal.
  // Kita hapus titik, lalu ubah koma menjadi titik agar bisa di-parse oleh parseFloat.
  let clean = s.replace(/\./g, '').replace(',', '.');

  return parseFloat(clean) || 0;
};

window.formatRupiah = function (num) {
  return 'Rp ' + Number(num || 0).toLocaleString('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

window.formatTanggal = function (dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return isNaN(d.getTime()) ? '-' : d.toLocaleDateString('id-ID');
};

window.escapeHtml = function (str = '') {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
};

window.resetSelect = function (select, placeholder) {
  if (!select) return;
  select.innerHTML = `<option value="">${placeholder}</option>`;
  select.disabled = true;
};

window.addOption = function (select, item) {
  if (!select) return;
  const opt = document.createElement('option');
  opt.value = item.value;
  opt.textContent = item.label;
  select.appendChild(opt);
};

window.closeModal = function (modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.remove('show');
};

window.csrfToken = function () {
  return document.querySelector('meta[name="csrf-token"]')?.content;
};

window.debounce = function (func, wait) {
  let timeout;
  return function (...args) {
    const context = this;
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(context, args), wait);
  };
};

window.formatDateIndo = function (dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return isNaN(d.getTime()) ? '-' : d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};
