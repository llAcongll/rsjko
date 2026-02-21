let editingRekeningId = null;
let rekeningRawData = [];
let rekeningCurrentPage = 1;
const REKENING_PER_PAGE = 10;
let rekeningFilteredData = [];

// Helper untuk mendapatkan base URL dashboard secara dinamis
const getDashboardUrl = (path) => {
  const base = window.location.pathname.split('/dashboard')[0];
  return `${base}/dashboard/${path}`;
};

const BANK_LIST = [
  'Bank Riau Kepri Syariah',
  'Bank Syariah Indonesia'
];

/* =========================
   OPEN / CLOSE MODAL
========================= */
window.openRekeningForm = function (row = null) {
  editingRekeningId = row ? row.id : null;

  rekeningModalTitle.innerText =
    row ? '✏️ Edit Rekening' : '➕ Tambah Rekening';

  rkTanggal.value = row?.tanggal
    ? new Date(row.tanggal).toISOString().slice(0, 10)
    : '';

  fillBankDropdown(row?.bank || '');
  rkKeterangan.value = row?.keterangan || '';
  rkCD.value = row ? row.cd : '';
  rkJumlah.value = row ? formatNumber(row.jumlah) : '';

  rekeningModal.classList.add('show');

  validateRekeningForm();
};

window.closeRekeningModal = function () {
  rekeningModal.classList.remove('show');
  editingRekeningId = null;
};

/* =========================
   DETAIL
========================= */
window.detailRekening = function (id) {
  const modal = document.getElementById('rekeningDetailModal');
  const content = document.getElementById('detailRekeningContent');

  if (!modal || !content) return;
  modal.classList.add('show');

  content.innerHTML = `
        <div class="flex items-center justify-center py-8 text-slate-500">
            <i class="ph ph-spinner animate-spin text-3xl mr-2"></i>
            <p>Memuat detail...</p>
        </div>
    `;

  const url = getDashboardUrl(`rekening-korans/${id}`);

  fetch(url, {
    headers: { Accept: 'application/json' }
  })
    .then(r => r.json())
    .then(row => {
      content.innerHTML = `
                <div class="detail-row">
                    <div class="label">Tanggal</div>
                    <div class="value">${formatDate(row.tanggal)}</div>
                </div>
                <div class="detail-row">
                    <div class="label">Bank</div>
                    <div class="value">${row.bank}</div>
                </div>
                <div class="detail-row">
                    <div class="label">Keterangan</div>
                    <div class="value">${row.keterangan}</div>
                </div>
                <div class="detail-row">
                    <div class="label">Jenis (C/D)</div>
                    <div class="value">
                        <span class="badge ${row.cd === 'C' ? 'success' : 'danger'}">
                            ${row.cd === 'C' ? 'Credit (Masuk)' : 'Debit (Keluar)'}
                        </span>
                    </div>
                </div>
                <div class="detail-total">
                    <span>Jumlah</span>
                    <strong>${formatRupiah(row.jumlah)}</strong>
                </div>
            `;
    })
    .catch(err => {
      content.innerHTML = `<p class="text-danger">Gagal memuat detail: ${err.message}</p>`;
    });
};

window.closeDetailRekening = function () {
  document.getElementById('rekeningDetailModal')?.classList.remove('show');
};

/* =========================
   SUBMIT
========================= */
window.submitRekening = function () {
  const jumlah = parseAngka(rkJumlah.value);

  if (jumlah <= 0) {
    toast('Jumlah harus lebih dari 0', 'error');
    return;
  }

  const payload = {
    tanggal: rkTanggal.value,
    bank: rkBank.value,
    keterangan: rkKeterangan.value.trim(),
    cd: rkCD.value,
    jumlah
  };

  if (!BANK_LIST.includes(payload.bank)) {
    toast('Bank tidak valid', 'error');
    return;
  }

  const baseUrl = getDashboardUrl('rekening-korans');
  const url = editingRekeningId ? `${baseUrl}/${editingRekeningId}` : baseUrl;

  fetch(url, {
    method: editingRekeningId ? 'PUT' : 'POST',
    headers: {
      'X-CSRF-TOKEN': csrfToken(),
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(payload)
  })
    .then(r => r.ok ? r.json() : r.text().then(e => { throw e; }))
    .then(() => {
      closeRekeningModal();
      loadRekening();
      toast('Data berhasil disimpan', 'success');
    })
    .catch(err => toast(err || 'Server error', 'error'));
};

/* =========================
   DELETE
========================= */
window.deleteRekening = function (id) {
  openConfirm(
    'Hapus Data',
    'Data rekening akan dihapus permanen',
    () => {
      const url = getDashboardUrl(`rekening-korans/${id}`);
      fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json'
        }
      })
        .then(r => r.json())
        .then(() => {
          loadRekening();
          toast('Data dihapus', 'success');
        });
    }
  );
};

/* =========================
   LOAD DATA
========================= */
window.loadRekening = function () {
  const url = getDashboardUrl('rekening-korans');
  fetch(url, {
    headers: { 'Accept': 'application/json' }
  })
    .then(async r => {
      const json = await r.json();
      if (!r.ok) throw new Error(json.message || 'Gagal memuat data');
      return json;
    })
    .then(data => {
      // Pastikan data adalah array
      if (!Array.isArray(data)) data = [];

      // Urutkan dari tanggal paling awal (asc by tanggal)
      data.sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));
      rekeningRawData = data;
      rekeningFilteredData = data;
      rekeningCurrentPage = 1;
      renderRekeningTable(rekeningFilteredData);
    })
    .catch(err => toast(err.message || err, 'error'));
};

/* =========================
   RENDER TABLE + SUMMARY
========================= */
function renderRekeningTable(data) {
  const tbody = document.querySelector('#rekeningTable tbody');
  if (!tbody) return;

  tbody.innerHTML = '';

  const start = (rekeningCurrentPage - 1) * REKENING_PER_PAGE;
  const end = start + REKENING_PER_PAGE;
  const pageData = data.slice(start, end);

  let saldoBRKS = 0;
  let saldoBSI = 0;
  let saldoRunning = 0;

  // 🔹 hitung saldo global (summary)
  data.forEach(row => {
    const j = Number(row.jumlah) || 0;
    const v = row.cd === 'C' ? j : -j;

    if (row.bank === BANK_LIST[0]) saldoBRKS += v;
    if (row.bank === BANK_LIST[1]) saldoBSI += v;
  });

  // 🔹 hitung saldo sampai sebelum page
  for (let i = 0; i < start; i++) {
    const r = data[i];
    const j = Number(r.jumlah) || 0;
    saldoRunning += r.cd === 'C' ? j : -j;
  }

  // 🔹 render baris + saldo running
  const canCRUD = window.hasPermission('REKENING_CRUD');

  pageData.forEach((row, i) => {
    const jumlah = Number(row.jumlah) || 0;
    saldoRunning += row.cd === 'C' ? jumlah : -jumlah;

    tbody.innerHTML += `
      <tr>
        <td>${start + i + 1}</td>
        <td class="text-center">${formatDate(row.tanggal)}</td>
        <td class="ellipsis" title="${row.bank}">${row.bank}</td>
        <td><div class="ellipsis-content" title="${row.keterangan}">${row.keterangan}</div></td>
        <td class="cd ${row.cd === 'C' ? 'credit' : 'debit'}">${row.cd}</td>
        <td class="amount">${formatRupiahTable(jumlah)}</td>
        <td class="amount">${formatRupiahTable(saldoRunning)}</td>
        <td>
          <div class="flex justify-center gap-2">
            <button class="btn-aksi detail" onclick="detailRekening(${row.id})" title="Lihat Detail">
              <i class="ph ph-eye"></i>
            </button>
            ${canCRUD ? `
              <button class="btn-aksi edit" onclick='openRekeningForm(${JSON.stringify(row).replace(/'/g, "&apos;")})' title="Edit Data">
                <i class="ph ph-pencil-simple"></i>
              </button>
              <button class="btn-aksi delete" onclick="deleteRekening(${row.id})" title="Hapus Data">
                <i class="ph ph-trash"></i>
              </button>
            ` : ''}
          </div>
        </td>
      </tr>
    `;
  });

  updateRekeningInfo(start + 1, Math.min(end, data.length), data.length);
  renderRekeningPagination(data.length);

  const total = saldoBRKS + saldoBSI;

  document.getElementById('saldoBRKS').innerText = formatRupiah(saldoBRKS);
  document.getElementById('saldoBSI').innerText = formatRupiah(saldoBSI);
  document.getElementById('saldoTotal').innerText = formatRupiah(total);

  const pBRK = total ? ((saldoBRKS / total) * 100).toFixed(1) : '0';
  const elBRK = document.getElementById('percentBRKS');
  if (elBRK) {
    elBRK.innerText = `${pBRK}% dari total`;
    elBRK.className = Number(pBRK) > 0 ? 'growth-up' : '';
  }

  const pBSI = total ? ((saldoBSI / total) * 100).toFixed(1) : '0';
  const elBSI = document.getElementById('percentBSI');
  if (elBSI) {
    elBSI.innerText = `${pBSI}% dari total`;
    elBSI.className = Number(pBSI) > 0 ? 'growth-up' : '';
  }
}

/* =========================
   FILTER (FRONTEND ONLY)
========================= */
window.applyRekeningFilter = function () {
  const bank = filterBank.value;
  const start = filterStart.value;
  const end = filterEnd.value;

  let filtered = [...rekeningRawData];

  if (bank) filtered = filtered.filter(r => r.bank === bank);
  if (start) filtered = filtered.filter(r => r.tanggal >= start);
  if (end) filtered = filtered.filter(r => r.tanggal <= end);

  rekeningFilteredData = filtered;
  rekeningCurrentPage = 1; // reset ke hal 1 tiap filter
  renderRekeningTable(rekeningFilteredData);
};

/* =========================
   HELPERS
========================= */
window.formatDate = d => {
  if (!d) return '-';
  const date = new Date(d);
  return isNaN(date.getTime()) ? '-' : date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};



function formatNumber(n) {
  return formatRibuan(n);
}

function fillBankDropdown(selected = '') {
  rkBank.innerHTML = `<option value="">-- Pilih Bank --</option>`;
  BANK_LIST.forEach(b => {
    const o = document.createElement('option');
    o.value = b;
    o.textContent = b;
    if (b === selected) o.selected = true;
    rkBank.appendChild(o);
  });
}

/* =========================
   VALIDATION
========================= */
function validateRekeningForm() {
  const jumlah = parseAngka(rkJumlah.value);

  document.querySelector('#rekeningModal .btn-primary').disabled =
    !rkTanggal.value ||
    !rkBank.value ||
    !rkKeterangan.value.trim() ||
    !rkCD.value ||
    jumlah <= 0;
}

/* =========================
   AUTO FORMAT RUPIAH
========================= */
window.formatRupiahInput = function (el) {
  // We allow decimals now, so don't strip everything
  validateRekeningForm();
};

// Add blur/focus listeners for decimal entry
rkJumlah?.addEventListener('blur', function (e) {
  e.target.value = formatRibuan(parseAngka(e.target.value));
});
rkJumlah?.addEventListener('focus', function (e) {
  let val = parseAngka(e.target.value);
  e.target.value = val === 0 ? '' : val.toString().replace('.', ',');
});

/* =========================
   EVENT BINDING
========================= */
['rkTanggal', 'rkBank', 'rkKeterangan', 'rkCD', 'rkJumlah'].forEach(id => {
  const el = document.getElementById(id);
  if (!el) return;
  el.addEventListener('input', validateRekeningForm);
  el.addEventListener('change', validateRekeningForm);
});

function updateRekeningInfo(from, to, total) {
  const info = document.getElementById('rekeningInfo');
  if (!info) return;

  if (!total) {
    info.innerText = 'Menampilkan 0–0 dari 0 data';
    return;
  }

  const totalPages = Math.ceil(total / REKENING_PER_PAGE);
  info.innerText = `Menampilkan ${from}–${to} dari ${total} data • Halaman ${rekeningCurrentPage} dari ${totalPages}`;
}

/* =========================
   PAGINATION (CONSISTENT STYLE)
========================= */
/* =========================
   PAGINATION (CONSISTENT STYLE)
========================= */
function renderRekeningPagination(totalCount) {
  const info = document.getElementById('rekeningInfo');
  const prevBtn = document.getElementById('prevPageRekening');
  const nextBtn = document.getElementById('nextPageRekening');
  const pageInfo = document.getElementById('pageInfoRekening');

  if (!info || !prevBtn || !nextBtn || !pageInfo) return;

  const totalPages = Math.ceil(totalCount / REKENING_PER_PAGE) || 1;
  const from = totalCount ? (rekeningCurrentPage - 1) * REKENING_PER_PAGE + 1 : 0;
  const to = Math.min(rekeningCurrentPage * REKENING_PER_PAGE, totalCount);

  info.innerText = `Menampilkan ${from}–${to} dari ${totalCount} data`;
  pageInfo.innerText = `${rekeningCurrentPage} / ${totalPages}`;

  prevBtn.disabled = rekeningCurrentPage === 1;
  nextBtn.disabled = rekeningCurrentPage === totalPages;
}

window.changeRekeningPage = function (dir) {
  rekeningCurrentPage += dir;
  renderRekeningTable(rekeningFilteredData);
};

/* =========================
   IMPORT
========================= */
window.uploadRekeningImport = function (input) {
  if (!input.files || !input.files[0]) return;

  const file = input.files[0];
  const formData = new FormData();
  formData.append('file', file);

  // Show loading indicator
  const btn = document.querySelector('button[onclick*="importRekeningFile"]');
  const originalText = btn ? btn.innerHTML : '';
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Uploading...';
  }

  fetch('/dashboard/rekening-korans/import', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrfToken()
    },
    body: formData
  })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        toast(res.message, 'success');
        loadRekening();
      } else {
        if (res.errors && res.errors.length) {
          toast(res.errors[0] + (res.errors.length > 1 ? ` (+${res.errors.length - 1} lainnya)` : ''), 'error');
          console.error(res.errors);
        } else {
          toast(res.message || 'Gagal import data', 'error');
        }
      }
    })
    .catch(err => {
      console.error(err);
      toast('Terjadi kesalahan saat upload', 'error');
    })
    .finally(() => {
      input.value = '';
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    });
};
/* =========================
   BULK DELETE
========================= */
window.deleteBulkRekening = function () {
  const bank = document.getElementById('filterBank').value;
  const start = document.getElementById('filterStart').value;
  const end = document.getElementById('filterEnd').value;

  let msg = 'Hapus semua data rekening?';
  if (bank || start || end) {
    msg = `Hapus data${bank ? ` ${bank}` : ''}${start ? ` dari ${formatDateIndo(start)}` : ''}${end ? ` sampai ${formatDateIndo(end)}` : ''}?`;
  }

  openConfirm(
    'Hapus Massal',
    msg,
    () => {
      const url = getDashboardUrl('rekening-korans/bulk-delete');
      fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ bank, start, end }) // Send filters
      })
        .then(r => r.json())
        .then(res => {
          toast(`Berhasil menghapus ${res} data`, 'success');
          loadRekening();
        })
        .catch(err => {
          console.error(err);
          toast('Gagal menghapus data', 'error');
        });
    },
    'Hapus Data',
    'ph-trash',
    'btn-danger'
  );
};

document.querySelector('.main')?.classList.add('rekening-mode');