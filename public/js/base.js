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

window.formatDateForInput = function (dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return '';
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
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

window.printElement = function (elementId, title = 'Cetak') {
  const el = document.getElementById(elementId);
  if (!el) return;

  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
        <!DOCTYPE html>
        <html>
            <head>
                <title>${title}</title>
                <style>
                    body { font-family: 'Inter', Arial, sans-serif; padding: 20px; color: #333; }
                    h2 { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px;}
                    .detail-row { display: flex; margin-bottom: 8px; border-bottom: 1px dashed #ccc; padding-bottom: 4px; }
                    .detail-row .label { font-weight: bold; width: 150px; flex-shrink: 0; }
                    .detail-row .value { flex-grow: 1; text-align: right; }
                    .detail-total { display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2em; margin-top: 15px; border-top: 2px solid #333; padding-top: 10px; }
                    .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
                    .badge-info { background: #e0f2fe; color: #0284c7; }
                    .font-mono { font-family: monospace; }
                    @media print {
                        @page { margin: 1cm; }
                        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    }
                </style>
            </head>
            <body>
                <h2>${title}</h2>
                ${el.innerHTML}
                <script>
                    setTimeout(() => {
                        window.print();
                        window.close();
                    }, 500);
                </script>
            </body>
        </html>
    `);
  printWindow.document.close();
};

window.previewExportExcel = function (tableId, filename = 'Export') {
  let modal = document.getElementById('modalExportPreview');
  if (!modal) {
    document.body.insertAdjacentHTML('beforeend', `
            <div id="modalExportPreview" class="confirm-overlay" style="z-index: 10000; flex-direction: column;">
                <div class="confirm-box" style="max-width: 90vw; width: 1000px; max-height: 90vh; display: flex; flex-direction: column;">
                    <h3 style="margin-top:0; display:flex; align-items:center; gap:8px;">
                        <i class="ph ph-file-xls" style="color:#10b981;"></i> Preview Export Excel
                    </h3>
                    <div id="exportPreviewContent" class="table-container" style="flex:1; overflow:auto; margin: 16px 0; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; background: #fff;"></div>
                    <div class="modal-actions" style="justify-content: space-between; align-items: center; display: flex; width: 100%;">
                        <span style="font-size:12px; color:#64748b;">Preview tabel yang akan diunduh. Kolom aksi ditiadakan.</span>
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="btn-secondary" onclick="closeModal('modalExportPreview')">Batal</button>
                            <button type="button" class="btn-primary" id="btnDownloadExportExcel" style="background:#10b981; border-color:#10b981;">
                                <i class="ph ph-download-simple"></i> Unduh Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
    modal = document.getElementById('modalExportPreview');
  }

  const tableEl = document.getElementById(tableId);
  if (!tableEl) return;

  const clone = tableEl.cloneNode(true);
  clone.removeAttribute('id'); // Prevent duplicate IDs in DOM

  // Convert forms/inputs into text if exist, but simple text content is usually enough.
  const ths = clone.querySelectorAll('th');
  const colsToRemove = [];
  ths.forEach((th, index) => {
    if (th.innerText.toLowerCase().trim() === 'aksi') {
      colsToRemove.push(index);
    }
  });

  clone.querySelectorAll('tr').forEach(tr => {
    const cells = tr.children;
    for (let i = colsToRemove.length - 1; i >= 0; i--) {
      if (cells[i]) cells[i].remove();
    }
  });

  const previewContainer = document.getElementById('exportPreviewContent');
  previewContainer.innerHTML = '';
  previewContainer.appendChild(clone);

  modal.classList.add('show');

  document.getElementById('btnDownloadExportExcel').onclick = function () {
    const exportClone = clone.cloneNode(true);
    exportClone.style.borderCollapse = 'collapse';
    exportClone.style.width = '100%';
    exportClone.setAttribute('border', '1');

    exportClone.querySelectorAll('td, th').forEach(cell => {
      const nominalGroup = cell.querySelector('.nominal-group');
      if (nominalGroup) {
        let text = '';
        nominalGroup.querySelectorAll('.nom-row').forEach(row => {
          const label = row.querySelector('.nom-label')?.innerText.trim() || '';
          const val = row.querySelector('.nom-val')?.innerText.trim() || '';
          text += label + ': ' + val + '<br style="mso-data-placement:same-cell;">';
        });
        cell.innerHTML = text;
      } else {
        const badges = cell.querySelectorAll('.badge');
        badges.forEach(b => {
          b.outerHTML = '<b>[' + b.innerText.trim() + ']</b>';
        });
      }
    });

    let html = exportClone.outerHTML;

    const completeHtml = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta charset="utf-8">
                <style>
                    table { border-collapse: collapse; width: 100%; border: 1px solid #000; font-family: sans-serif; }
                    th, td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; mso-number-format: "\\@"; }
                    th { border-bottom: 2px solid #000; background-color: #f1f5f9; font-weight: bold; }
                </style>
            </head>
            <body>
                <h2>${filename}</h2>
                ${html}
            </body>
            </html>
        `;

    const blob = new Blob([completeHtml], { type: 'application/vnd.ms-excel' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };
};
