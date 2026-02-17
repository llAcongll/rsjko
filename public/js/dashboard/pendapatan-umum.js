/* =========================
   PENDAPATAN UMUM JS
========================= */

let pendapatanPage = 1;
let pendapatanPerPage = 10;
let pendapatanKeyword = '';
let isEditPendapatan = false;
let editPendapatanId = null;

/* =========================
   MODAL CONTROL
========================= */
window.openPendapatanModal = function () {
  const modal = document.getElementById('pendapatanUmumModal');
  if (!modal) return;

  modal.classList.add('show');
  loadRuangan();
};

window.closePendapatanModal = function () {
  const modal = document.getElementById('pendapatanUmumModal');
  modal?.classList.remove('show');

  const form = document.getElementById('formPendapatanUmum');
  form?.reset();

  document.querySelectorAll('.nominal-display').forEach(i => i.value = '0');
  document.querySelectorAll('.nominal-value').forEach(i => i.value = 0);
  document.getElementById('totalPembayaran').innerText = 'Rp 0';

  const btn = document.getElementById('btnSimpanPendapatan');
  if (btn) {
    btn.disabled = true;
    btn.innerText = '💾 Simpan';
  }

  isEditPendapatan = false;
  editPendapatanId = null;

  const title = document.querySelector('.modal-title');
  if (title) title.innerText = '➕ Tambah Pendapatan Umum';
};

/* =========================
   SUBMIT
========================= */
window.submitPendapatanUmum = async function (event) {
  event.preventDefault();

  const form = document.getElementById('formPendapatanUmum');
  if (!form) return;

  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const btnSimpan = document.getElementById('btnSimpanPendapatan');
  btnSimpan.disabled = true;
  btnSimpan.innerText = '⏳ Menyimpan...';

  const formData = new FormData(form);

  if (isEditPendapatan) {
    formData.append('_method', 'PUT');
  }

  // Cleanup conditional fields
  const metode = formData.get('metode_pembayaran');
  if (metode !== 'NON_TUNAI') {
    formData.delete('bank');
    formData.delete('metode_detail');
  }

  const url = isEditPendapatan
    ? `/dashboard/pendapatan/umum/${editPendapatanId}`
    : `/dashboard/pendapatan/umum`;

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      },
      body: formData
    });

    if (!res.ok) {
      const err = await res.json();
      throw new Error(err.message || 'Gagal menyimpan data');
    }

    toast(isEditPendapatan ? 'Data berhasil diperbarui' : 'Data berhasil disimpan', 'success');
    closePendapatanModal();
    loadPendapatanUmum();

  } catch (err) {
    toast(err.message, 'error');
    btnSimpan.disabled = false;
    btnSimpan.innerText = isEditPendapatan ? '💾 Simpan Perubahan' : '💾 Simpan';
  }
};

/* =========================
   DATA LOADING
========================= */
function loadPendapatanUmum(page = pendapatanPage) {
  pendapatanPage = page;

  const tbody = document.getElementById('pendapatanUmumBody');
  if (!tbody) return;

  tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding: 40px; color: #94a3b8;">
                <i class="ph ph-spinner" style="font-size: 32px; animation: spin 1s linear infinite; margin-bottom: 8px;"></i>
                <p>Memuat data...</p>
            </td>
        </tr>
    `;

  const params = new URLSearchParams({
    page: pendapatanPage,
    per_page: pendapatanPerPage,
    search: pendapatanKeyword
  });

  fetch(`/dashboard/pendapatan/umum?${params.toString()}`, {
    headers: { Accept: 'application/json' }
  })
    .then(async res => {
      const json = await res.json();
      if (!res.ok) throw new Error(json.message || 'Gagal memuat data');
      return json;
    })
    .then(res => {
      const data = res.data || [];
      renderPaginationPendapatan(res);
      renderSummaryPendapatan(res.aggregates);

      if (!data || data.length === 0) {
        tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <i class="ph ph-warning-circle" style="font-size: 32px; margin-bottom: 8px;"></i>
                            <p>Belum ada data pendapatan Umum</p>
                        </td>
                    </tr>
                `;
        return;
      }

      const canCRUD = window.hasPermission('PENDAPATAN_UMUM_CRUD');

      tbody.innerHTML = ''; // 🔥 Bersihkan "Memuat data..." sebelum render

      data.forEach((item, index) => {
        tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center">${res.from + index}</td>
                    <td>${formatTanggal(item.tanggal)}</td>
                    <td class="font-medium">${escapeHtml(item.nama_pasien)}</td>
                    <td><span class="badge badge-info">${item.ruangan?.nama ?? '-'}</span></td>
                    <td class="text-right font-bold" style="color: #0f172a;">${formatRupiah(item.total)}</td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button class="btn-aksi detail" onclick="detailPendapatanUmum(${item.id})" title="Lihat Detail">
                                <i class="ph ph-eye"></i>
                            </button>
                            ${canCRUD ? `
                                <button class="btn-aksi edit" onclick="editPendapatanUmum(${item.id})" title="Edit Data">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn-aksi delete" onclick="hapusPendapatanUmum(${item.id})" title="Hapus Data">
                                    <i class="ph ph-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `);
      });
    })
    .catch((err) => {
      console.error(err);
      tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center" style="padding: 40px; color: #ef4444;">
                        <i class="ph ph-x-circle" style="font-size: 32px; margin-bottom: 8px;"></i>
                        <p>Gagal memuat data. Silakan coba lagi.</p>
                    </td>
                </tr>
            `;
    });
}

function renderSummaryPendapatan(agg) {
  if (!agg) return;

  const setText = (sel, val) => {
    const el = document.querySelector(sel);
    if (el) el.innerText = val;
  };

  setText('[data-summary="rs"]', formatRupiah(agg.total_rs));
  setText('[data-summary="pelayanan"]', formatRupiah(agg.total_pelayanan));
  setText('[data-summary="total"]', formatRupiah(agg.total_all));

  const rsPercent = agg.total_all ? Math.round((agg.total_rs / agg.total_all) * 100) : 0;
  const pelPercent = agg.total_all ? (100 - rsPercent) : 0;
  setText('[data-summary-percent="rs"]', rsPercent + '% dari total');
  setText('[data-summary-percent="pelayanan"]', pelPercent + '% dari total');
}

window.loadPendapatanUmum = loadPendapatanUmum;

/* =========================
   ACTIONS
========================= */
window.detailPendapatanUmum = function (id) {
  const modal = document.getElementById('pendapatanDetailModal');
  const content = document.getElementById('detailPendapatanContent');

  if (!modal || !content) return;
  modal.classList.add('show');

  content.innerHTML = `
        <div class="flex flex-col items-center justify-center py-8 text-slate-500">
            <i class="ph ph-spinner animate-spin text-3xl mb-2"></i>
            <p>Memuat detail...</p>
        </div>
    `;

  fetch(`/dashboard/pendapatan/umum/${id}`, {
    headers: { Accept: 'application/json' }
  })
    .then(res => res.json())
    .then(data => {
      const bankLabel = data.bank === 'BRK' ? 'Bank Riau Kepri Syariah' :
        data.bank === 'BSI' ? 'Bank Syariah Indonesia' :
          (data.bank || '-');

      content.innerHTML = `
            <div class="detail-row">
                <span class="label">Tanggal</span>
                <span class="value">${formatTanggal(data.tanggal)}</span>
            </div>
            <div class="detail-row">
                <span class="label">Pasien</span>
                <span class="value font-medium">${escapeHtml(data.nama_pasien)}</span>
            </div>
            <div class="detail-row">
                <span class="label">Ruangan</span>
                <span class="value">${data.ruangan?.nama ?? '-'}</span>
            </div>
            <div class="detail-row">
                <span class="label">Metode</span>
                <span class="value">
                    <span class="badge ${data.metode_pembayaran === 'TUNAI' ? 'tunai' : 'non-tunai'}">
                        ${data.metode_pembayaran}
                    </span>
                    ${data.metode_pembayaran === 'NON_TUNAI' ? `<div class="text-xs text-slate-500 mt-1">${bankLabel} — ${data.metode_detail}</div>` : ''}
                </span>
            </div>
            
            <div class="my-4 border-t border-slate-100"></div>

            <div class="detail-row">
                <span class="label">RS Tindakan</span>
                <span class="value">${formatRupiah(data.rs_tindakan)}</span>
            </div>
            <div class="detail-row">
                <span class="label">RS Obat</span>
                <span class="value">${formatRupiah(data.rs_obat)}</span>
            </div>
            <div class="detail-row">
                <span class="label">Pelayanan Tindakan</span>
                <span class="value">${formatRupiah(data.pelayanan_tindakan)}</span>
            </div>
            <div class="detail-row">
                <span class="label">Pelayanan Obat</span>
                <span class="value">${formatRupiah(data.pelayanan_obat)}</span>
            </div>
            
            <div class="detail-total mt-4">
                <span>Total Pendapatan</span>
                <strong>${formatRupiah(data.total)}</strong>
            </div>
        `;
    });
};

window.closeDetailPendapatan = function () {
  document.getElementById('pendapatanDetailModal')?.classList.remove('show');
};

window.editPendapatanUmum = function (id) {
  isEditPendapatan = true;
  editPendapatanId = id;

  Promise.all([
    openPendapatanModal(),
    fetch(`/dashboard/pendapatan/umum/${id}`, { headers: { Accept: 'application/json' } }).then(res => res.json())
  ]).then(([_, data]) => {
    const form = document.getElementById('formPendapatanUmum');

    form.querySelector('[name="tanggal"]').value = data.tanggal.substring(0, 10);
    form.querySelector('[name="nama_pasien"]').value = data.nama_pasien;
    form.querySelector('[name="ruangan_id"]').value = data.ruangan_id;

    const metode = form.querySelector('[name="metode_pembayaran"]');
    metode.value = data.metode_pembayaran;
    metode.dispatchEvent(new Event('change'));

    setTimeout(() => {
      if (data.bank) form.querySelector('[name="bank"]').value = data.bank;
      if (data.metode_detail) form.querySelector('[name="metode_detail"]').value = data.metode_detail;
    }, 100);

    form.querySelector('[name="rs_tindakan"]').value = data.rs_tindakan;
    form.querySelector('[name="rs_obat"]').value = data.rs_obat;
    form.querySelector('[name="pelayanan_tindakan"]').value = data.pelayanan_tindakan;
    form.querySelector('[name="pelayanan_obat"]').value = data.pelayanan_obat;

    form.querySelectorAll('.nominal-display').forEach((disp, i) => {
      const val = form.querySelectorAll('.nominal-value')[i].value;
      disp.value = formatRibuan(val);
    });

    hitungTotal();
    cekSiapSimpan();
  });

  const title = document.querySelector('.modal-title');
  if (title) title.innerText = '✏️ Edit Pendapatan Umum';
};

window.hapusPendapatanUmum = function (id) {
  openConfirm('Hapus Data', 'Yakin ingin menghapus data ini?', async () => {
    try {
      const res = await fetch(`/dashboard/pendapatan/umum/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        }
      });
      if (!res.ok) throw new Error();
      toast('Data berhasil dihapus', 'success');
      loadPendapatanUmum();
    } catch {
      toast('Gagal menghapus data', 'error');
    }
  });
};

/* =========================
   INITIALIZATION
========================= */
window.initPendapatanUmum = function () {
  const btn = document.getElementById('btnTambahPendapatanUmum');
  if (btn) {
    btn.onclick = () => {
      const form = document.getElementById('formPendapatanUmum');
      form?.reset();
      isEditPendapatan = false;
      editPendapatanId = null;
      openPendapatanModal();
    };
  }

  const searchInput = document.getElementById('searchPendapatanUmum');
  if (searchInput) {
    let timer;
    searchInput.oninput = (e) => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        pendapatanKeyword = e.target.value.trim();
        loadPendapatanUmum(1);
      }, 400);
    };
  }

  document.querySelectorAll('.nominal-display').forEach(input => {
    input.addEventListener('input', () => {
      const val = parseAngka(input.value);
      input.closest('.input-group').querySelector('.nominal-value').value = val;
      hitungTotal();
    });
    input.addEventListener('blur', () => {
      let val = parseAngka(input.value);
      input.value = formatRibuan(val);
    });
    input.addEventListener('focus', () => {
      let val = parseAngka(input.value);
      input.value = val === 0 ? '' : val.toString().replace('.', ',');
    });
  });

  setupBankLogic();

  const form = document.getElementById('formPendapatanUmum');
  if (form) {
    ['tanggal', 'nama_pasien', 'ruangan_id'].forEach(name => {
      form.querySelector(`[name="${name}"]`)?.addEventListener('input', cekSiapSimpan);
    });
    ['metode_pembayaran', 'bank', 'metode_detail'].forEach(id => {
      if (id === 'metode_pembayaran') {
        form.querySelector('[name="metode_pembayaran"]')?.addEventListener('change', cekSiapSimpan);
      } else {
        document.getElementById(id)?.addEventListener('change', cekSiapSimpan);
      }
    });
  }

  // =========================
  // IMPORT & BULK DELETE UMUM
  // =========================
  const btnImport = document.getElementById('btnImportUmum');
  if (btnImport) {
    btnImport.onclick = () => {
      document.getElementById('modalImportUmum')?.classList.add('show');
    };
  }

  const formImport = document.getElementById('formImportUmum');
  if (formImport) {
    formImport.onsubmit = async (e) => {
      e.preventDefault();
      const btn = formImport.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.innerText = '⏳ Mengimport...';

      try {
        const res = await fetch('/dashboard/pendapatan/umum/import', {
          method: 'POST',
          body: new FormData(formImport),
          headers: { 'X-CSRF-TOKEN': csrfToken() }
        });
        const resData = await res.json();
        if (!res.ok) throw new Error(resData.message || 'Gagal import');

        toast(`Berhasil mengimport ${resData.count} data`, 'success');
        closeModal('modalImportUmum');
        formImport.reset();
        loadPendapatanUmum();
      } catch (err) {
        toast(err.message, 'error');
      } finally {
        btn.disabled = false;
        btn.innerText = 'Mulai Import';
      }
    };
  }

  const btnBulkDelete = document.getElementById('btnBulkDeleteUmum');
  if (btnBulkDelete) {
    btnBulkDelete.onclick = () => {
      document.getElementById('modalBulkDeleteUmum')?.classList.add('show');
    };
  }

  const formBulkDelete = document.getElementById('formBulkDeleteUmum');
  if (formBulkDelete) {
    formBulkDelete.onsubmit = async (e) => {
      e.preventDefault();
      const tgl = document.getElementById('bulkDeleteDateUmum').value;
      if (!tgl) return;

      openConfirm('Hapus Massal', `Yakin ingin menghapus SEMUA data Umum pada tanggal ${formatTanggal(tgl)}?`, async () => {
        const btn = formBulkDelete.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerText = '⏳ Menghapus...';

        try {
          const res = await fetch('/dashboard/pendapatan/umum/bulk-delete', {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken()
            },
            body: JSON.stringify({ tanggal: tgl })
          });
          const resData = await res.json();
          if (!res.ok) throw new Error();

          toast(`Berhasil menghapus ${resData.count} data`, 'success');
          closeModal('modalBulkDeleteUmum');
          loadPendapatanUmum();
        } catch {
          toast('Gagal melakukan hapus massal', 'error');
        } finally {
          btn.disabled = false;
          btn.innerText = 'Hapus Permanen';
        }
      });
    };
  }
};

function hitungTotal() {
  let total = 0;
  document.querySelectorAll('.nominal-value').forEach(i => total += parseFloat(i.value || 0));
  document.getElementById('totalPembayaran').innerText = formatRupiah(total);
}

function cekSiapSimpan() {
  const form = document.getElementById('formPendapatanUmum');
  if (!form) return;
  const data = new FormData(form);

  let valid = data.get('tanggal') && data.get('nama_pasien') && data.get('ruangan_id');

  const metode = data.get('metode_pembayaran');
  if (!metode) valid = false;
  if (metode === 'NON_TUNAI' && (!data.get('bank') || !data.get('metode_detail'))) valid = false;

  const btn = document.getElementById('btnSimpanPendapatan');
  if (btn) btn.disabled = !valid;
}

function setupBankLogic() {
  const metode = document.getElementById('metodePembayaran');
  const bank = document.getElementById('bank');
  const detail = document.getElementById('metodeDetail'); // Ensure IDs match HTML

  if (!metode || !bank || !detail) return;

  metode.onchange = () => {
    resetSelect(bank, '-- Pilih Bank --');
    resetSelect(detail, '-- Metode Detail --');

    if (metode.value === 'TUNAI') {
      bank.disabled = true;
      detail.disabled = true;
      addOption(bank, { value: 'BRK', label: 'Bank Riau Kepri Syariah' });
      bank.value = 'BRK';
      addOption(detail, { value: 'SETOR_TUNAI', label: 'Setor Tunai' });
      detail.value = 'SETOR_TUNAI';
    } else if (metode.value === 'NON_TUNAI') {
      bank.disabled = false;
      detail.disabled = false;

      bank.removeAttribute('readonly');
      detail.removeAttribute('readonly');

      addOption(bank, { value: 'BRK', label: 'Bank Riau Kepri Syariah' });
      addOption(bank, { value: 'BSI', label: 'Bank Syariah Indonesia' });
      addOption(detail, { value: 'QRIS', label: 'QRIS' });
      addOption(detail, { value: 'TRANSFER', label: 'Transfer' });
    }
    cekSiapSimpan();
  };
}

async function loadRuangan() {
  const select = document.getElementById('ruanganSelect');
  if (!select || select.options.length > 1) return;

  select.innerHTML = '<option value="">Memuat...</option>';

  try {
    const res = await fetch('/dashboard/ruangan-list');
    const data = await res.json();

    select.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
    data.forEach(r => {
      select.insertAdjacentHTML('beforeend', `<option value="${r.id}">${r.kode} — ${r.nama}</option>`);
    });
    select.disabled = false;
  } catch {
    select.innerHTML = '<option value="">Gagal memuat</option>';
  }
}

function renderPaginationPendapatan(meta) {
  const info = document.getElementById('paginationInfo');
  if (info) info.innerText = `Menampilkan ${meta.from ?? 0}–${meta.to ?? 0} dari ${meta.total ?? 0} data`;

  const pageInfo = document.getElementById('pageInfo');
  if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;

  const prev = document.getElementById('prevPage');
  const next = document.getElementById('nextPage');

  if (prev) {
    prev.disabled = (meta.current_page === 1);
    prev.onclick = () => loadPendapatanUmum(meta.current_page - 1);
  }
  if (next) {
    next.disabled = (meta.current_page === meta.last_page);
    next.onclick = () => loadPendapatanUmum(meta.current_page + 1);
  }
}

/* =========================
   HELPERS
========================= */
// Helpers are now in base.js

