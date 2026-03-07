let editingRekeningId = null;
let rekeningRawData = [];
let rekeningCurrentPage = 1;
const REKENING_PER_PAGE = 10;
let rekeningFilteredData = [];
let rekeningSortBy = 'tanggal';
let rekeningSortDir = 'asc';

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

  rkTanggal.value = formatDateForInput(row?.tanggal);

  fillBankDropdown(row?.bank || '');
  rkKeterangan.value = row?.keterangan || '';
  rkCD.value = row ? row.cd : '';
  rkJumlah.value = row ? formatNumber(row.jumlah) : '';

  const destGroup = document.getElementById('rkDestinationBankGroup');
  const destBank = document.getElementById('rkDestinationBank');
  if (destGroup && destBank) {
    destGroup.style.display = rkCD.value === 'D' ? 'block' : 'none';
    destBank.value = row?.destination_bank || '';
  }

  rekeningModal.classList.add('show');

  validateRekeningForm();
};

window.closeRekeningModal = function () {
  rekeningModal.classList.remove('show');
  editingRekeningId = null;
};

/* =========================
   SALDO AWAL MODAL
========================= */
window.openRekeningSaldoAwalModal = function () {
  const modal = document.getElementById('modalRekeningSaldoAwal');
  const form = document.getElementById('formRekeningSaldoAwal');
  if (form) form.reset();

  const displayInput = document.getElementById('rekeningSaldoAwalDisplayInput');
  const hiddenInput = document.getElementById('rekeningSaldoAwalValue');

  if (displayInput && hiddenInput) {
    displayInput.value = '0';
    hiddenInput.value = 0;

    displayInput.oninput = () => { hiddenInput.value = parseAngka(displayInput.value); };
    displayInput.onblur = () => { displayInput.value = formatRibuan(hiddenInput.value); };
    displayInput.onfocus = () => {
      const val = parseAngka(displayInput.value);
      displayInput.value = val === 0 ? '' : val.toString().replace('.', ',');
    };
  }

  if (modal) modal.classList.add('show');
};

window.closeRekeningSaldoAwalModal = function () {
  const modal = document.getElementById('modalRekeningSaldoAwal');
  if (modal) modal.classList.remove('show');
};

window.submitRekeningSaldoAwal = function (e) {
  e.preventDefault();
  const form = document.getElementById('formRekeningSaldoAwal');
  const data = Object.fromEntries(new FormData(form));

  fetch('/dashboard/rekening-korans/saldo-awal', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(data)
  })
    .then(async res => {
      const json = await res.json();
      if (!res.ok) throw new Error(json.message || 'Gagal menyimpan saldo awal');
      toast(json.message || 'Saldo awal berhasil diset', 'success');
      closeRekeningSaldoAwalModal();
      loadRekening();
    })
    .catch(err => toast(err.message, 'error'));
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
    jumlah,
    destination_bank: document.getElementById('rkDestinationBank')?.value || null
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

      rekeningRawData = data;
      rekeningFilteredData = [...data];
      applySortRekening();
      rekeningCurrentPage = 1;
      renderRekeningTable(rekeningFilteredData);
    })
    .catch(err => toast(err.message || err, 'error'));
};

/* =========================
   RENDER TABLE + SUMMARY
========================= */
// 🔹 refactor calculation logic for reuse
function getRekeningProcessedData(data) {
  const bankFilter = document.getElementById('filterBank')?.value || '';
  const startDateFilter = document.getElementById('filterStart')?.value || '';

  let saldoAwal = 0;
  let saldoAwalDisplay = 0; // The actual base balance set for this year

  rekeningRawData.forEach(r => {
    const isBaseSaldoAwal = r.is_saldo_awal === 1 || r.is_saldo_awal === true;

    if (isBaseSaldoAwal) {
      if (!bankFilter || r.bank === bankFilter) {
        saldoAwalDisplay += Number(r.jumlah) || 0;
        // Also adds to standard saldoAwal if it's within filter ranges (or before start date)
        if (!startDateFilter || r.tanggal < startDateFilter) {
          saldoAwal += Number(r.jumlah) || 0;
        }
      }
    } else if (startDateFilter && r.tanggal < startDateFilter) {
      if (!bankFilter || r.bank === bankFilter) {
        const j = Number(r.jumlah) || 0;
        saldoAwal += r.cd === 'C' ? j : -j;
      }
    }
  });

  let running = saldoAwal;
  let processed = [];

  data.forEach(r => {
    // Only calculate running balance for non-saldo-awal entries or if we just want to show them 
    // Wait, the data array passed in is the FILTERED data to be shown.
    // We want to calculate running balance including saldo_awal, but NOT display saldo_awal in the table rows
    const isBaseSaldoAwal = r.is_saldo_awal === 1 || r.is_saldo_awal === true;

    // We already accounted for is_saldo_awal in `saldoAwal` if its date matched.
    // BUT what if its date is within the table range? We must add it to running balance but not display it.
    if (isBaseSaldoAwal) {
      // If it wasn't added before startDateFilter, we add it now
      if (!startDateFilter || r.tanggal >= startDateFilter) {
        running += Number(r.jumlah) || 0;
      }
    } else {
      const j = Number(r.jumlah) || 0;
      running += r.cd === 'C' ? j : -j;
      processed.push({ ...r, saldo_running: running });
    }
  });

  return { processed, saldoAwal, saldoAwalDisplay };
}


function renderRekeningTable(data) {
  const tbody = document.querySelector('#rekeningTable tbody');
  if (!tbody) return;

  tbody.innerHTML = '';

  // 1. Calculate SALDO GLOBAL (Always from RAW DATA for Current Status)
  let totalBRKS = 0;
  let totalBSI = 0;

  rekeningRawData.forEach(row => {
    const j = Number(row.jumlah) || 0;
    const v = row.cd === 'C' ? j : -j;
    if (row.bank === BANK_LIST[0]) totalBRKS += v;
    if (row.bank === BANK_LIST[1]) totalBSI += v;
  });

  const total = totalBRKS + totalBSI;
  document.getElementById('saldoBRKS').innerText = formatRupiah(totalBRKS);
  document.getElementById('saldoBSI').innerText = formatRupiah(totalBSI);
  document.getElementById('saldoTotal').innerText = formatRupiah(total);

  // Update percentages
  const pBRK = total ? ((totalBRKS / total) * 100).toFixed(1) : '0';
  const elBRK = document.getElementById('percentBRKS');
  if (elBRK) {
    elBRK.innerText = `${pBRK}% dari total`;
    elBRK.className = Number(pBRK) > 0 ? 'growth-up' : '';
  }

  const pBSI = total ? ((totalBSI / total) * 100).toFixed(1) : '0';
  const elBSI = document.getElementById('percentBSI');
  if (elBSI) {
    elBSI.innerText = `${pBSI}% dari total`;
    elBSI.className = Number(pBSI) > 0 ? 'growth-up' : '';
  }

  const { processed, saldoAwalDisplay } = getRekeningProcessedData(data);

  // Update Saldo Awal Display Block
  const saldoAwalDisplayEl = document.getElementById('rekeningSaldoAwalDisplay');
  if (saldoAwalDisplayEl) {
    saldoAwalDisplayEl.innerText = `Saldo Awal Tahun: ${formatRupiah(saldoAwalDisplay)}`;
  }


  // 3. Render PAGINATED rows
  const start = (rekeningCurrentPage - 1) * REKENING_PER_PAGE;
  const end = start + REKENING_PER_PAGE;
  const pageData = processed.slice(start, end);

  const canCRUD = window.hasPermission('REKENING_CRUD');

  pageData.forEach((row, i) => {
    tbody.innerHTML += `
      <tr>
        <td data-label="No">${start + i + 1}</td>
        <td class="text-center" data-label="Tanggal">${formatDate(row.tanggal)}</td>
        <td class="ellipsis" title="${row.bank}" data-label="Bank">${row.bank}</td>
        <td data-label="Keterangan"><div class="ellipsis-content" title="${row.keterangan}">${row.keterangan}</div></td>
        <td class="cd ${row.cd === 'C' ? 'credit' : 'debit'}" data-label="C/D">${row.cd}</td>
        <td class="amount" data-label="Jumlah">${formatRupiahTable(row.jumlah)}</td>
        <td class="amount" data-label="Saldo">${formatRupiahTable(row.saldo_running)}</td>
        <td data-label="Aksi">
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

  renderRekeningPagination(data.length);
  updateSortIconsRekening();
}

function applySortRekening() {
  rekeningFilteredData.sort((a, b) => {
    let valA = a[rekeningSortBy];
    let valB = b[rekeningSortBy];

    if (rekeningSortBy === 'tanggal') {
      valA = new Date(valA).getTime();
      valB = new Date(valB).getTime();
    } else if (rekeningSortBy === 'jumlah') {
      valA = Number(valA);
      valB = Number(valB);
    } else {
      valA = String(valA).toLowerCase();
      valB = String(valB).toLowerCase();
    }

    if (valA < valB) return rekeningSortDir === 'asc' ? -1 : 1;
    if (valA > valB) return rekeningSortDir === 'asc' ? 1 : -1;
    return a.id - b.id; // Tie-breaker
  });
}

window.sortRekening = function (col) {
  if (rekeningSortBy === col) {
    rekeningSortDir = rekeningSortDir === 'asc' ? 'desc' : 'asc';
  } else {
    rekeningSortBy = col;
    rekeningSortDir = (col === 'tanggal' ? 'asc' : 'desc');
  }
  applySortRekening();
  renderRekeningTable(rekeningFilteredData);
};

function updateSortIconsRekening() {
  document.querySelectorAll('#rekeningTable th.sortable i').forEach(i => {
    i.className = 'ph ph-caret-up-down text-slate-400';
  });
  const activeHeader = document.querySelector(`#rekeningTable th.sortable[data-sort="${rekeningSortBy}"]`);
  if (activeHeader) {
    const i = activeHeader.querySelector('i');
    if (i) {
      i.className = rekeningSortDir === 'asc' ? 'ph ph-caret-up text-blue-600' : 'ph ph-caret-down text-blue-600';
    }
  }
}

/* =========================
   FILTER (FRONTEND ONLY)
========================= */
window.applyRekeningFilter = function () {
  const bank = document.getElementById('filterBank')?.value || '';
  const start = document.getElementById('filterStart')?.value || '';
  const end = document.getElementById('filterEnd')?.value || '';

  let filtered = [...rekeningRawData];

  if (bank && bank !== 'Semua Bank') filtered = filtered.filter(r => r.bank === bank);
  if (start) filtered = filtered.filter(r => r.tanggal >= start);
  if (end) filtered = filtered.filter(r => r.tanggal <= end);

  rekeningFilteredData = filtered;
  applySortRekening();
  rekeningCurrentPage = 1; // reset ke hal 1 tiap filter
  renderRekeningTable(rekeningFilteredData);
};

window.openPreviewRekening = function () {
  const bank = document.getElementById('filterBank')?.value || 'Semua Bank';
  const start = document.getElementById('filterStart')?.value;
  const end = document.getElementById('filterEnd')?.value;

  const { processed, saldoAwal } = getRekeningProcessedData(rekeningFilteredData);

  const modal = document.getElementById('rekeningPreviewModal');
  const body = document.getElementById('rekeningPreviewBody');
  if (!modal || !body) return;

  modal.classList.add('show');

  const rowsHtml = processed.map((row, i) => `
    <tr>
      <td class="text-center" style="border: 1px solid black; padding: 5px;">${i + 1}</td>
      <td class="text-center" style="border: 1px solid black; padding: 5px;">${formatDate(row.tanggal)}</td>
      <td style="border: 1px solid black; padding: 5px;">${row.keterangan}</td>
      <td class="text-center" style="border: 1px solid black; padding: 5px; color: ${row.cd === 'C' ? 'green' : 'red'};">${row.cd}</td>
      <td class="text-right" style="border: 1px solid black; padding: 5px;">${formatRupiahTable(row.jumlah)}</td>
      <td class="text-right" style="border: 1px solid black; padding: 5px; font-weight: bold;">${formatRupiahTable(row.saldo_running)}</td>
    </tr>
  `).join('');

  body.innerHTML = `
    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
        <!-- HEADER -->
        <div style="display: flex; align-items: center; width: 100%; min-height: 160px; margin-bottom: 0;">
            <div style="width: 140px; display: flex; justify-content: flex-start;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w400"
                    style="height: 140px; width: auto; object-fit: contain;">
            </div>
            <div style="flex: 1; text-align: center; padding: 0 10px;">
                <h1 style="margin: 0; padding: 0; font-size: 15pt; font-weight: normal; color: #000; line-height: 1.2;">
                    PEMERINTAH PROVINSI KEPULAUAN RIAU</h1>
                <h2 style="margin: 0; padding: 0; font-size: 16pt; font-weight: bold; color: #000; line-height: 1.2;">
                    RUMAH SAKIT JIWA DAN KETERGANTUNGAN OBAT</h2>
                <h2 style="margin: 0; padding: 0; font-size: 16pt; font-weight: bold; color: #000; line-height: 1.2;">
                    ENGKU HAJI DAUD</h2>
                <div style="line-height: 1.4; margin-top: 5px; font-size: 9pt; font-weight: normal; color: #000;">
                    Jalan Indun Suri – Simpang Busung Nomor. 1 Tanjung Uban Kode Pos 29152<br>
                    Telepon ( 0771 ) 482655, 482796 Faksimile. ( 0771 ) 482795<br>
                    Pos-el: rsjkoehd@kepriprov.go.id Laman : www.rsudehd.kepriprov.go.id
                </div>
            </div>
            <div style="width: 140px;"></div>
        </div>
        <div style="height: 4px; background: #000; margin: 5px 0 20px;"></div>

        <!-- TITLE -->
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="font-size: 16px; font-weight: bold; text-decoration: underline;">LAPORAN REKENING KORAN PENDAPATAN</div>
            <div style="font-size: 12px; margin-top: 5px;">Bank: ${bank}</div>
            <div style="font-size: 11px;">Periode: ${start ? formatDateNoDay(start) : 'Awal'} s/d ${end ? formatDateNoDay(end) : 'Sekarang'}</div>
        </div>

        <!-- SALDO AWAL -->
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; width: fit-content; display: flex; align-items: center; gap: 15px;">
            <div style="font-weight: bold; color: #475569; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Saldo Awal :</div>
            <div style="font-weight: 800; font-size: 16px; color: #0f172a;">${formatRupiah(saldoAwal)}</div>
        </div>

        <!-- TABLE -->
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f8fafc;">
                    <th style="border: 1px solid black; padding: 8px;">No</th>
                    <th style="border: 1px solid black; padding: 8px;">Tanggal</th>
                    <th style="border: 1px solid black; padding: 8px;">Keterangan / Uraian</th>
                    <th style="border: 1px solid black; padding: 8px;">C/D</th>
                    <th style="border: 1px solid black; padding: 8px;">Jumlah (Rp)</th>
                    <th style="border: 1px solid black; padding: 8px;">Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHtml || '<tr><td colspan="6" style="border: 1px solid black; padding: 20px; text-align: center; color: #64748b;">Tidak ada data pada periode ini</td></tr>'}
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="5" style="border: 1px solid black; padding: 8px; text-align: right;">SALDO AKHIR PERIODE</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: right;">${formatRupiah(processed.length ? processed[processed.length - 1].saldo_running : saldoAwal)}</td>
                </tr>
            </tfoot>
        </table>

        <!-- FOOTER SIGNATURE -->
        <div style="margin-top: 50px; display: flex; justify-content: space-between; align-items: flex-start; gap: 15px;">
            <div id="ptRekeningAreaKiri" style="width: 32%; text-align: center; visibility: hidden;">
                <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                <p id="prevPtRekeningJabatanKiri" style="margin: 0; min-height: 1.25em;"></p>
                <div style="height: 60px;"></div>
                <p id="prevPtRekeningNamaKiri" style="margin: 0; font-weight: bold; text-decoration: underline;">( ......................................... )</p>
                <p id="prevPtRekeningNipKiri" style="margin: 0;">NIP. .........................................</p>
            </div>
            <div id="ptRekeningAreaTengah" style="width: 32%; text-align: center; visibility: hidden;">
                <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                <p id="prevPtRekeningJabatanTengah" style="margin: 0; min-height: 1.25em;"></p>
                <div style="height: 60px;"></div>
                <p id="prevPtRekeningNamaTengah" style="margin: 0; font-weight: bold; text-decoration: underline;">( ......................................... )</p>
                <p id="prevPtRekeningNipTengah" style="margin: 0;">NIP. .........................................</p>
            </div>
            <div id="ptRekeningAreaKanan" style="width: 32%; text-align: center;">
                <p style="margin: 0;">Tanjung Uban, ${formatDateNoDay(new Date())}</p>
                <p id="prevPtRekeningJabatanKanan" style="margin: 0; min-height: 1.25em;">Bendahara Penerimaan</p>
                <div style="height: 60px;"></div>
                <p id="prevPtRekeningNamaKanan" style="margin: 0; font-weight: bold; text-decoration: underline;">( ......................................... )</p>
                <p id="prevPtRekeningNipKanan" style="margin: 0;">NIP. .........................................</p>
            </div>
        </div>
    </div>
  `;

  loadRekeningSignatories();
};

let ptRekeningList = [];
function loadRekeningSignatories() {
  fetch('/dashboard/penanda-tangan-list')
    .then(r => r.json())
    .then(data => {
      ptRekeningList = data;
      ['Kiri', 'Tengah', 'Kanan'].forEach(pos => {
        const select = document.getElementById(`ptRekening${pos}`);
        if (select) {
          const currentVal = select.value;
          select.innerHTML = '<option value="">-- Kosong --</option>';
          data.forEach(pt => {
            const opt = document.createElement('option');
            opt.value = pt.id;
            opt.textContent = `${pt.jabatan} - ${pt.nama}`;
            select.appendChild(opt);
          });
          select.value = currentVal;
          updateRekeningSignatory(pos);
        }
      });
    });
}

window.updateRekeningSignatory = function (pos) {
  const select = document.getElementById(`ptRekening${pos}`);
  const area = document.getElementById(`ptRekeningArea${pos}`);
  const jabEl = document.getElementById(`prevPtRekeningJabatan${pos}`);
  const namaEl = document.getElementById(`prevPtRekeningNama${pos}`);
  const nipEl = document.getElementById(`prevPtRekeningNip${pos}`);

  if (!select || !area) return;

  const pt = ptRekeningList.find(i => i.id == select.value);
  if (pt) {
    area.style.visibility = 'visible';
    jabEl.innerText = pt.jabatan;
    namaEl.innerText = `(${pt.nama})`;
    nipEl.innerText = `NIP. ${pt.nip || '.........................................'}`;
  } else {
    // Kanan always shown, others hidden if empty
    if (pos === 'Kanan') {
      area.style.visibility = 'visible';
      jabEl.innerText = 'Bendahara Penerimaan';
      namaEl.innerText = '( ......................................... )';
      nipEl.innerText = 'NIP. .........................................';
    } else {
      area.style.visibility = 'hidden';
      jabEl.innerText = '';
      namaEl.innerText = '';
      nipEl.innerText = '';
    }
  }
};

window.closeRekeningPreview = function () {
  const modal = document.getElementById('rekeningPreviewModal');
  if (modal) {
    modal.classList.remove('show');
  }
};

window.printRekening = function () {
  const bank = document.getElementById('filterBank')?.value || '';
  const start = document.getElementById('filterStart')?.value || '';
  const end = document.getElementById('filterEnd')?.value || '';

  const params = new URLSearchParams({
    bank: bank === 'Semua Bank' ? '' : bank,
    start: start || '',
    end: end || '',
    ptKiri: document.getElementById('ptRekeningKiri')?.value || '',
    ptTengah: document.getElementById('ptRekeningTengah')?.value || '',
    ptKanan: document.getElementById('ptRekeningKanan')?.value || ''
  });

  window.open(`/dashboard/rekening-korans/print?${params.toString()}`, '_blank');
};

window.printRekeningExcel = function () {
  const bank = document.getElementById('filterBank')?.value || '';
  const start = document.getElementById('filterStart')?.value || '';
  const end = document.getElementById('filterEnd')?.value || '';

  const params = new URLSearchParams({
    bank: bank === 'Semua Bank' ? '' : bank,
    start: start || '',
    end: end || '',
    ptKiri: document.getElementById('ptRekeningKiri')?.value || '',
    ptTengah: document.getElementById('ptRekeningTengah')?.value || '',
    ptKanan: document.getElementById('ptRekeningKanan')?.value || ''
  });

  window.location.href = `/dashboard/rekening-korans/export-excel?${params.toString()}`;
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

function formatDateNoDay(d) {
  if (!d) return '-';
  const date = new Date(d);
  return isNaN(date.getTime()) ? '-' : date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
}



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
['rkTanggal', 'rkBank', 'rkKeterangan', 'rkCD', 'rkJumlah', 'rkDestinationBank'].forEach(id => {
  const el = document.getElementById(id);
  if (!el) return;
  el.addEventListener('input', validateRekeningForm);
  el.addEventListener('change', validateRekeningForm);
});

rkCD?.addEventListener('change', function () {
  const destGroup = document.getElementById('rkDestinationBankGroup');
  if (destGroup) {
    destGroup.style.display = this.value === 'D' ? 'block' : 'none';
  }
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


/* =========================
   SALDO AWAL TAHUN
========================= */
window.openRekeningSaldoAwalModal = function () {
  const form = document.getElementById('formRekeningSaldoAwal');
  if (form) form.reset();

  const hiddenInput = document.getElementById('rekeningSaldoAwalValue');
  const displayInput = document.getElementById('rekeningSaldoAwalDisplayInput');
  const bankInput = document.getElementById('rekeningSaldoAwalBank');
  const btnHapus = document.getElementById('btnHapusRekeningSaldoAwal');

  // Find existing saldo awal from raw data
  const currentFilterBank = document.getElementById('filterBank')?.value;
  let existingAmount = 0;
  let selectedBank = '';

  if (currentFilterBank && currentFilterBank !== 'Semua Bank') {
    selectedBank = currentFilterBank;
    const existing = rekeningRawData.find(r => r.bank === selectedBank && (r.is_saldo_awal === 1 || r.is_saldo_awal === true));
    if (existing) {
      existingAmount = Number(existing.jumlah) || 0;
    }
  }

  if (bankInput) {
    bankInput.value = selectedBank;
    bankInput.onchange = function () {
      const bank = this.value;
      const existing = rekeningRawData.find(r => r.bank === bank && (r.is_saldo_awal === 1 || r.is_saldo_awal === true));
      const amt = existing ? (Number(existing.jumlah) || 0) : 0;
      if (hiddenInput) hiddenInput.value = amt;
      if (displayInput) displayInput.value = amt > 0 ? formatRibuan(amt) : '';
      if (btnHapus) btnHapus.style.display = amt > 0 ? 'inline-flex' : 'none';
    };
  }

  if (hiddenInput) hiddenInput.value = existingAmount;
  if (displayInput) {
    displayInput.value = existingAmount > 0 ? formatRibuan(existingAmount) : '';
    // Bind currency formatting for this input
    displayInput.oninput = function () {
      let val = parseAngka(this.value);
      if (hiddenInput) hiddenInput.value = val;
    };
    displayInput.onblur = function () {
      let val = Number(hiddenInput.value) || 0;
      this.value = val > 0 ? formatRibuan(val) : '';
    };
    displayInput.onfocus = function () {
      let val = Number(hiddenInput.value) || 0;
      this.value = val === 0 ? '' : val.toString().replace('.', ',');
    };
  }

  if (btnHapus) {
    btnHapus.style.display = existingAmount > 0 ? 'inline-flex' : 'none';
  }

  const modal = document.getElementById('modalRekeningSaldoAwal');
  if (modal) modal.classList.add('show');
};

window.closeRekeningSaldoAwalModal = function () {
  const modal = document.getElementById('modalRekeningSaldoAwal');
  if (modal) modal.classList.remove('show');
};

window.deleteRekeningSaldoAwal = function () {
  const bank = document.getElementById('rekeningSaldoAwalBank').value;
  if (!bank) return toast('Pilih bank terlebih dahulu', 'error');

  openConfirm(
    'Hapus Saldo Awal',
    `Yakin ingin menghapus saldo awal tahun untuk ${bank}?`,
    () => {
      fetch('/dashboard/rekening-korans/saldo-awal', {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ bank })
      })
        .then(async r => {
          const json = await r.json();
          if (!r.ok) throw new Error(json.message || 'Gagal menghapus');
          return json;
        })
        .then(res => {
          toast(res.message || 'Saldo awal dihapus', 'success');
          closeRekeningSaldoAwalModal();
          loadRekening();
        })
        .catch(err => toast(err.message || err, 'error'));
    },
    'Hapus Saldo',
    'ph-trash',
    'btn-danger'
  );
}

window.submitRekeningSaldoAwal = function (e) {
  e.preventDefault();
  const form = document.getElementById('formRekeningSaldoAwal');
  const formData = new FormData(form);
  const payload = {
    bank: formData.get('bank'),
    jumlah: formData.get('jumlah'),
    tahun: window.tahunAnggaran || new Date().getFullYear()
  };

  fetch('/dashboard/rekening-korans/saldo-awal', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrfToken(),
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(payload)
  })
    .then(async r => {
      const json = await r.json();
      if (!r.ok) throw new Error(json.message || 'Gagal menyimpan saldo awal');
      return json;
    })
    .then(res => {
      toast(res.message || 'Saldo awal berhasil disimpan', 'success');
      closeRekeningSaldoAwalModal();
      loadRekening();
    })
    .catch(err => toast(err.message || err, 'error'));
};

document.querySelector('.main')?.classList.add('rekening-mode');