let lainPage = 1;
let lainPerPage = 10;
let lainKeyword = '';
let isEditLain = false;
let editLainId = null;

// Global Caches
window._cacheRuangan = window._cacheRuangan || null;
window._cacheMou = window._cacheMou || null;

/* =========================
   MODAL CONTROL
========================= */
window.openPendapatanLainModal = async function () {
    const modal = document.getElementById('pendapatanLainModal');
    if (!modal) return;

    modal.classList.add('show');
    await Promise.all([
        loadRuanganLain(),
        loadMouLain()
    ]);
};

window.closePendapatanLainModal = function () {
    const modal = document.getElementById('pendapatanLainModal');
    modal?.classList.remove('show');

    const form = document.getElementById('formPendapatanLain');
    form?.reset();

    document.querySelectorAll('.nominal-display-lain').forEach(i => i.value = '0');
    document.querySelectorAll('.nominal-value-lain').forEach(i => i.value = 0);

    document.getElementById('totalPembayaranLain').innerText = 'Rp 0';

    const btn = document.getElementById('btnSimpanPendapatanLain');
    if (btn) {
        btn.disabled = true;
        btn.innerText = '💾 Simpan';
    }

    isEditLain = false;
    editLainId = null;

    const title = document.querySelector('#pendapatanLainModal .modal-title');
    if (title) title.innerText = '➕ Tambah Pendapatan Lain-lain';
};

/* =========================
   SUBMIT
========================= */
window.submitPendapatanLain = async function (event) {
    event.preventDefault();

    const form = document.getElementById('formPendapatanLain');
    if (!form) return;

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btnSimpan = document.getElementById('btnSimpanPendapatanLain');
    btnSimpan.disabled = true;
    btnSimpan.innerText = '⏳ Menyimpan...';

    const formData = new FormData(form);

    if (isEditLain) {
        formData.append('_method', 'PUT');
    }

    const url = isEditLain
        ? `/dashboard/pendapatan/lain/${editLainId}`
        : `/dashboard/pendapatan/lain`;

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

        toast(isEditLain ? 'Data berhasil diperbarui' : 'Data berhasil disimpan', 'success');
        closePendapatanLainModal();
        loadPendapatanLain();

    } catch (err) {
        toast(err.message, 'error');
        btnSimpan.disabled = false;
        btnSimpan.innerText = isEditLain ? '💾 Simpan Perubahan' : '💾 Simpan';
    }
};

/* =========================
   DATA LOADING
========================= */
function loadPendapatanLain(page = lainPage) {
    lainPage = page;

    const tbody = document.getElementById('pendapatanLainBody');
    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center" style="padding: 40px; color: #94a3b8;">
                <i class="ph ph-spinner" style="font-size: 32px; animation: spin 1s linear infinite; margin-bottom: 8px;"></i>
                <p>Memuat data...</p>
            </td>
        </tr>
    `;

    const params = new URLSearchParams({
        page: lainPage,
        per_page: lainPerPage,
        search: lainKeyword
    });

    fetch(`/dashboard/pendapatan/lain?${params.toString()}`, {
        headers: { Accept: 'application/json' }
    })
        .then(async res => {
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Gagal memuat data');
            return json;
        })
        .then(res => {
            const data = res.data || [];
            renderPaginationLain(res);
            renderSummaryLain(res.aggregates);

            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <i class="ph ph-warning-circle" style="font-size: 32px; margin-bottom: 8px;"></i>
                            <p>Belum ada data pendapatan Lain-lain</p>
                        </td>
                    </tr>
                `;
                return;
            }

            const canCRUD = window.hasPermission('PENDAPATAN_LAIN_CRUD');

            tbody.innerHTML = ''; // 🔥 Bersihkan "Memuat data..." sebelum render

            data.forEach((item, index) => {
                tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center">${res.from + index}</td>
                    <td>${formatTanggal(item.tanggal)}</td>
                    <td class="font-medium">${escapeHtml(item.nama_pasien ?? '-')}</td>
                    <td>${escapeHtml(item.mou?.nama ?? item.transaksi ?? '-')}</td>
                    <td><span class="badge badge-info">${item.ruangan?.nama ?? '-'}</span></td>
                    <td class="text-right font-bold" style="color: #0f172a;">${formatRupiah(item.total)}</td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button class="btn-aksi detail" onclick="detailPendapatanLain(${item.id})" title="Lihat Detail">
                                <i class="ph ph-eye"></i>
                            </button>
                            ${canCRUD ? `
                                <button class="btn-aksi edit" onclick="editPendapatanLain(${item.id})" title="Edit Data">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn-aksi delete" onclick="hapusPendapatanLain(${item.id})" title="Hapus Data">
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
                    <td colspan="7" class="text-center" style="padding: 40px; color: #ef4444;">
                        <i class="ph ph-x-circle" style="font-size: 32px; margin-bottom: 8px;"></i>
                        <p>Gagal memuat data. Silakan coba lagi.</p>
                    </td>
                </tr>
            `;
        });
}

function renderSummaryLain(agg) {
    if (!agg) return;

    const setText = (sel, val) => {
        const el = document.querySelector(sel);
        if (el) el.innerText = val;
    };

    setText('[data-summary-lain="rs"]', formatRupiah(agg.total_rs));
    setText('[data-summary-lain="pelayanan"]', formatRupiah(agg.total_pelayanan));
    setText('[data-summary-lain="total"]', formatRupiah(agg.total_all));

    const rsPercent = agg.total_all ? Math.round((agg.total_rs / agg.total_all) * 100) : 0;
    const pelPercent = agg.total_all ? (100 - rsPercent) : 0;
    setText('[data-summary-percent-lain="rs"]', rsPercent + '% dari total');
    setText('[data-summary-percent-lain="pelayanan"]', pelPercent + '% dari total');
}

window.loadPendapatanLain = loadPendapatanLain;

/* =========================
   ACTIONS
========================= */
window.detailPendapatanLain = function (id) {
    const modal = document.getElementById('pendapatanLainDetailModal');
    const content = document.getElementById('detailPendapatanLainContent');

    modal.classList.add('show');
    content.innerHTML = `
        <div class="flex flex-col items-center justify-center py-8 text-slate-500">
            <i class="ph ph-spinner animate-spin text-3xl mb-2"></i>
            <p>Memuat detail...</p>
        </div>
    `;

    fetch(`/dashboard/pendapatan/lain/${id}`, {
        headers: { Accept: 'application/json' }
    })
        .then(res => res.json())
        .then(data => {
            content.innerHTML = `
            <div class="detail-row">
                <span class="label">Tanggal</span>
                <span class="value">${formatTanggal(data.tanggal)}</span>
            </div>
            <div class="detail-row">
                <span class="label">Pasien</span>
                <span class="value font-medium">${escapeHtml(data.nama_pasien ?? '-')}</span>
            </div>
            <div class="detail-row">
                <span class="label">MOU / Instansi</span>
                <span class="value">${data.mou?.nama ?? (data.transaksi || '-')}</span>
            </div>
            <div class="detail-row">
                <span class="label">Ruangan</span>
                <span class="value">${data.ruangan?.nama ?? '-'}</span>
            </div>
            <div class="detail-row">
                <span class="label">Metode</span>
                <span class="value">${data.metode_pembayaran} ${data.bank ? `(${data.bank})` : ''}</span>
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

window.closeDetailPendapatanLain = function () {
    document.getElementById('pendapatanLainDetailModal')?.classList.remove('show');
};

window.editPendapatanLain = async function (id) {
    isEditLain = true;
    editLainId = id;

    const title = document.querySelector('#pendapatanLainModal .modal-title');
    if (title) title.innerText = '✏️ Edit Pendapatan Lain-lain';

    const [, data] = await Promise.all([
        openPendapatanLainModal(),
        fetch(`/dashboard/pendapatan/lain/${id}`, { headers: { Accept: 'application/json' } }).then(res => res.json())
    ]);

    const form = document.getElementById('formPendapatanLain');
    form.querySelector('[name="tanggal"]').value = data.tanggal.substring(0, 10);
    form.querySelector('[name="nama_pasien"]').value = data.nama_pasien;
    form.querySelector('[name="ruangan_id"]').value = data.ruangan_id;

    if (data.mou_id) {
        form.querySelector('[name="mou_id"]').value = data.mou_id;
        syncTransaksiLain();
    }

    form.querySelector('[name="metode_pembayaran"]').value = data.metode_pembayaran;
    form.querySelector('[name="metode_pembayaran"]').onchange();

    setTimeout(() => {
        if (data.bank) form.querySelector('[name="bank"]').value = data.bank;
        if (data.metode_detail) form.querySelector('[name="metode_detail"]').value = data.metode_detail;
        cekSiapSimpanLain();
    }, 150);

    form.querySelector('[name="rs_tindakan"]').value = data.rs_tindakan;
    form.querySelector('[name="rs_obat"]').value = data.rs_obat;
    form.querySelector('[name="pelayanan_tindakan"]').value = data.pelayanan_tindakan;
    form.querySelector('[name="pelayanan_obat"]').value = data.pelayanan_obat;

    form.querySelectorAll('.nominal-display-lain').forEach((disp, i) => {
        const val = form.querySelectorAll('.nominal-value-lain')[i].value;
        disp.value = formatRibuan(val);
    });

    hitungTotalLain();
    cekSiapSimpanLain();
};

window.hapusPendapatanLain = function (id) {
    openConfirm('Hapus Data', 'Yakin ingin menghapus data ini?', async () => {
        try {
            const res = await fetch(`/dashboard/pendapatan/lain/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error();
            toast('Data berhasil dihapus', 'success');
            loadPendapatanLain();
        } catch {
            toast('Gagal menghapus data', 'error');
        }
    });
};

/* =========================
   INITIALIZATION
========================= */
window.initPendapatanLain = function () {
    lainKeyword = '';
    const btn = document.getElementById('btnTambahPendapatanLain');
    if (btn) {
        btn.onclick = async () => {
            const form = document.getElementById('formPendapatanLain');
            form?.reset();
            isEditLain = false;
            editLainId = null;
            const title = document.querySelector('#pendapatanLainModal .modal-title');
            if (title) title.innerText = '➕ Tambah Pendapatan Lain-lain';
            await openPendapatanLainModal();
        };
    }

    const searchInput = document.getElementById('searchPendapatanLain');
    if (searchInput) {
        let timer;
        searchInput.oninput = (e) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                lainKeyword = e.target.value.trim();
                loadPendapatanLain(1);
            }, 400);
        };
    }

    document.querySelectorAll('.nominal-display-lain').forEach(input => {
        input.addEventListener('input', () => {
            const val = parseAngka(input.value);
            input.nextElementSibling.value = val;
            hitungTotalLain();
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

    const form = document.getElementById('formPendapatanLain');
    if (form) {
        form.querySelector('[name="tanggal"]').oninput = cekSiapSimpanLain;
        form.querySelector('[name="nama_pasien"]').oninput = cekSiapSimpanLain;
        form.querySelector('[name="ruangan_id"]').onchange = cekSiapSimpanLain;
        form.querySelector('[name="bank"]').onchange = cekSiapSimpanLain;
        form.querySelector('[name="metode_detail"]').onchange = cekSiapSimpanLain;

        const mouSelect = document.getElementById('lainMouSelect');
        if (mouSelect) {
            mouSelect.onchange = () => {
                syncTransaksiLain();
                cekSiapSimpanLain();
            };
        }

        const metodeSelect = document.getElementById('lainMetodePembayaran');
        if (metodeSelect) {
            metodeSelect.onchange = () => {
                const bank = document.getElementById('lainBank');
                const detail = document.getElementById('lainMetodeDetail');
                resetSelect(bank, '-- Pilih Bank --');
                resetSelect(detail, '-- Metode Detail --');

                if (metodeSelect.value === 'TUNAI') {
                    bank.disabled = true;
                    detail.disabled = true;
                    addOption(bank, { value: 'BRK', label: 'Bank Riau Kepri Syariah' });
                    bank.value = 'BRK';
                    addOption(detail, { value: 'SETOR_TUNAI', label: 'Setor Tunai' });
                    detail.value = 'SETOR_TUNAI';
                } else if (metodeSelect.value === 'NON_TUNAI') {
                    bank.disabled = false;
                    detail.disabled = false;
                    addOption(bank, { value: 'BRK', label: 'Bank Riau Kepri Syariah' });
                    addOption(bank, { value: 'BSI', label: 'Bank Syariah Indonesia' });
                    addOption(detail, { value: 'QRIS', label: 'QRIS' });
                    addOption(detail, { value: 'TRANSFER', label: 'Transfer' });
                }
                cekSiapSimpanLain();
            };
        }

        // =========================
        // IMPORT & BULK DELETE LAIN-LAIN
        // =========================
        const btnImport = document.getElementById('btnImportLain');
        if (btnImport) {
            btnImport.onclick = () => {
                document.getElementById('modalImportLain')?.classList.add('show');
            };
        }

        const formImport = document.getElementById('formImportLain');
        if (formImport) {
            formImport.onsubmit = async (e) => {
                e.preventDefault();
                const btn = formImport.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerText = '⏳ Mengimport...';

                try {
                    const res = await fetch('/dashboard/pendapatan/lain/import', {
                        method: 'POST',
                        body: new FormData(formImport),
                        headers: { 'X-CSRF-TOKEN': csrfToken() }
                    });
                    const resData = await res.json();
                    if (!res.ok) throw new Error(resData.message || 'Gagal import');

                    toast(`Berhasil mengimport ${resData.count} data`, 'success');
                    closeModal('modalImportLain');
                    formImport.reset();
                    loadPendapatanLain();
                } catch (err) {
                    toast(err.message, 'error');
                } finally {
                    btn.disabled = false;
                    btn.innerText = 'Mulai Import';
                }
            };
        }

        const btnBulkDelete = document.getElementById('btnBulkDeleteLain');
        if (btnBulkDelete) {
            btnBulkDelete.onclick = () => {
                document.getElementById('modalBulkDeleteLain')?.classList.add('show');
            };
        }

        const formBulkDelete = document.getElementById('formBulkDeleteLain');
        if (formBulkDelete) {
            formBulkDelete.onsubmit = async (e) => {
                e.preventDefault();
                const tgl = document.getElementById('bulkDeleteDateLain').value;
                if (!tgl) return;

                openConfirm('Hapus Massal', `Yakin ingin menghapus SEMUA data Lain-lain pada tanggal ${formatTanggal(tgl)}?`, async () => {
                    const btn = formBulkDelete.querySelector('button[type="submit"]');
                    btn.disabled = true;
                    btn.innerText = '⏳ Menghapus...';

                    try {
                        const res = await fetch('/dashboard/pendapatan/lain/bulk-delete', {
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
                        closeModal('modalBulkDeleteLain');
                        loadPendapatanLain();
                    } catch {
                        toast('Gagal melakukan hapus massal', 'error');
                    } finally {
                        btn.disabled = false;
                        btn.innerText = 'Hapus Permanen';
                    }
                });
            };
        }
    }
};

function hitungTotalLain() {
    let total = 0;
    document.querySelectorAll('.nominal-value-lain').forEach(i => total += parseFloat(i.value || 0));
    document.getElementById('totalPembayaranLain').innerText = formatRupiah(total);
}

function cekSiapSimpanLain() {
    const form = document.getElementById('formPendapatanLain');
    if (!form) return;
    const data = new FormData(form);

    let valid = data.get('tanggal') && data.get('nama_pasien') && data.get('ruangan_id') && data.get('mou_id');

    const metode = data.get('metode_pembayaran');
    if (!metode) valid = false;
    if (metode === 'NON_TUNAI' && (!data.get('bank') || !data.get('metode_detail'))) valid = false;

    const btn = document.getElementById('btnSimpanPendapatanLain');
    if (btn) btn.disabled = !valid;
}

async function loadRuanganLain() {
    const select = document.getElementById('lainRuanganSelect');
    if (!select) return;

    if (window._cacheRuangan) {
        renderOptions(select, window._cacheRuangan, '-- Pilih Ruangan --', 'kode', 'nama');
    }

    try {
        const res = await fetch('/dashboard/ruangan-list');
        const data = await res.json();
        window._cacheRuangan = data;
        renderOptions(select, data, '-- Pilih Ruangan --', 'kode', 'nama');
    } catch (e) {
        console.error('Failed to load room list', e);
    }
}

async function loadMouLain() {
    const select = document.getElementById('lainMouSelect');
    if (!select) return;

    if (window._cacheMou) {
        renderOptions(select, window._cacheMou, '-- Pilih MOU --', 'kode', 'nama');
    }

    try {
        const res = await fetch('/dashboard/mou-list');
        const data = await res.json();
        window._cacheMou = data;
        renderOptions(select, data, '-- Pilih MOU --', 'kode', 'nama');
    } catch (e) {
        console.error('Failed to load MOU list', e);
    }
}

function renderOptions(select, data, placeholder, codeKey, nameKey) {
    const currentVal = select.value;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    data.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.innerText = `${item[codeKey]} — ${item[nameKey]}`;
        option.dataset.nama = item.nama;
        select.appendChild(option);
    });
    if (currentVal) select.value = currentVal;
}

function syncTransaksiLain() {
    const select = document.getElementById('lainMouSelect');
    const hidden = document.getElementById('lainTransaksiHidden');
    if (!select || !hidden) return;
    const selectedOption = select.options[select.selectedIndex];
    hidden.value = selectedOption?.dataset?.nama || '';
}

function renderPaginationLain(meta) {
    const info = document.getElementById('paginationInfoLain');
    if (info) info.innerText = `Menampilkan ${meta.from ?? 0}–${meta.to ?? 0} dari ${meta.total ?? 0} data`;

    const pageInfo = document.getElementById('pageInfoLain');
    if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;

    const prev = document.getElementById('prevPageLain');
    const next = document.getElementById('nextPageLain');

    if (prev) {
        prev.disabled = (meta.current_page === 1);
        prev.onclick = () => loadPendapatanLain(meta.current_page - 1);
    }
    if (next) {
        next.disabled = (meta.current_page === meta.last_page);
        next.onclick = () => loadPendapatanLain(meta.current_page + 1);
    }
}
