let editingRekeningId = null;
let rekeningRawData = [];
let rekeningCurrentPage = 1;
const REKENING_PER_PAGE = 50;
let rekeningFilteredData = [];

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

  rekeningModal.style.opacity = '1';
  rekeningModal.style.pointerEvents = 'auto';

  validateRekeningForm();
};

window.closeRekeningModal = function () {
  rekeningModal.style.opacity = '0';
  rekeningModal.style.pointerEvents = 'none';
  editingRekeningId = null;
};

/* =========================
   SUBMIT
========================= */
window.submitRekening = function () {
  const jumlah = parseInt(rkJumlah.value.replace(/\D/g, ''), 10) || 0;

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

  const url = editingRekeningId
    ? `/dashboard/rekening-korans/${editingRekeningId}`
    : `/dashboard/rekening-korans`;

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
      fetch(`/dashboard/rekening-korans/${id}`, {
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
  fetch('/dashboard/rekening-korans', {
    headers: { 'Accept': 'application/json' }
  })
    .then(r => r.json())
    .then(data => {
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
  pageData.forEach((row, i) => {
    const jumlah = Number(row.jumlah) || 0;
    saldoRunning += row.cd === 'C' ? jumlah : -jumlah;

    tbody.innerHTML += `
      <tr>
        <td>${start + i + 1}</td>
        <td>${formatDate(row.tanggal)}</td>
        <td>${row.bank}</td>
        <td class="ellipsis">${row.keterangan}</td>
        <td class="cd ${row.cd === 'C' ? 'credit' : 'debit'}">${row.cd}</td>
        <td class="amount">${formatRupiah(jumlah)}</td>
        <td class="amount">${formatRupiah(saldoRunning)}</td>
        <td>
          <button class="btn-action" onclick='openRekeningForm(${JSON.stringify(row)})'>✏️</button>
          <button class="btn-action" onclick='deleteRekening(${row.id})'>🗑️</button>
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

  document.getElementById('percentBRKS').innerText =
    total ? ((saldoBRKS / total) * 100).toFixed(1) + '%' : '0%';

  document.getElementById('percentBSI').innerText =
    total ? ((saldoBSI / total) * 100).toFixed(1) + '%' : '0%';
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
  rekeningCurrentPage = 1;
  renderRekeningTable(rekeningFilteredData);
};

/* =========================
   HELPERS
========================= */
window.formatDate = d =>
  d ? new Date(d).toLocaleDateString('id-ID') : '-';

window.formatRupiah = n =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(n || 0);

function formatNumber(n) {
  return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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
  const jumlah = parseInt(rkJumlah.value.replace(/\D/g, ''), 10) || 0;

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
  const raw = el.value.replace(/\D/g, '');
  el.value = raw ? formatNumber(raw) : '';
  validateRekeningForm();
};

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

  info.innerText = `Menampilkan ${from}–${to} dari ${total} data`;
}

function renderRekeningPagination(total) {
  const wrap = document.getElementById('rekeningPagination');
  if (!wrap) return;

  const totalPages = Math.ceil(total / REKENING_PER_PAGE);
  wrap.innerHTML = '';

  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.innerText = i;
    if (i === rekeningCurrentPage) btn.classList.add('active');

    btn.onclick = () => {
      rekeningCurrentPage = i;
      renderRekeningTable(rekeningFilteredData);
    };

    wrap.appendChild(btn);
  }
}

document.querySelector('.main')?.classList.add('rekening-mode');