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

window.formatRupiahTable = function (num) {
  const val = Number(num || 0).toLocaleString('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
  return `
    <div style="display: flex; justify-content: space-between; width: 100%; gap: 10px;">
        <span style="font-weight: 500; opacity: 0.7;">Rp</span>
        <span style="font-weight: 700;">${val}</span>
    </div>
  `;
};

window.formatTanggal = function (dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return isNaN(d.getTime()) ? '-' : d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
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

window.renderPagination = function (elementId, meta, callbackName) {
  const wrap = document.getElementById(elementId);
  if (!wrap) return;

  if (meta.total <= meta.per_page) {
    wrap.innerHTML = '';
    return;
  }

  let html = `
    <div class="pagination-info">
      Menampilkan ${meta.from ?? 0}–${meta.to ?? 0} dari ${meta.total ?? 0} data
    </div>
    <div class="pagination-actions">
      <button class="btn-pagi" ${meta.current_page === 1 ? 'disabled' : ''} onclick="${callbackName}(${meta.current_page - 1})">
        <i class="ph ph-caret-left"></i>
      </button>
      <span class="pagi-text">${meta.current_page} / ${meta.last_page}</span>
      <button class="btn-pagi" ${meta.current_page === meta.last_page ? 'disabled' : ''} onclick="${callbackName}(${meta.current_page + 1})">
        <i class="ph ph-caret-right"></i>
      </button>
    </div>
  `;
  wrap.innerHTML = html;
};

// Keep for compatibility if needed
window.renderPaginationGeneric = function (meta, elementId, callback) {
  window.renderPagination(elementId, meta, typeof callback === 'string' ? callback : callback.name);
};
