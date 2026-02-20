let bpjsPage = 1;
let bpjsPerPage = 10;
let bpjsKeyword = '';
let currentBpjsTab = 'REGULAR';
let isEditBpjs = false;
let editBpjsId = null;

// Global Caches
window._cacheRuangan = window._cacheRuangan || null;
window._cachePerusahaan = window._cachePerusahaan || null;

/* =========================
   TABS CONTROL
========================= */
window.switchBpjsTab = function (jenis, btn) {
    currentBpjsTab = jenis;

    document.querySelectorAll('.bpjs-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const thNoSep = document.getElementById('thNoSep');
    if (thNoSep) {
        thNoSep.style.display = (jenis === 'REGULAR') ? '' : 'none';
    }

    loadPendapatanBpjs(1);
};

/* =========================
   MODAL CONTROL
========================= */
window.openPendapatanBpjsModal = async function () {
    const modal = document.getElementById('pendapatanBpjsModal');
    if (!modal) return;

    modal.classList.add('show');
    await Promise.all([
        loadRuanganBpjs(),
        loadPerusahaanBpjs()
    ]);

    const jenisSelect = document.getElementById('bpjsJenisSelect');
    if (jenisSelect && !isEditBpjs) {
        jenisSelect.value = currentBpjsTab;
        toggleNoSepField();
    }

    // Trigger Non-Tunai logic
    document.getElementById('bpjsMetodePembayaran')?.dispatchEvent(new Event('change'));
};

window.closePendapatanBpjsModal = function () {
    const modal = document.getElementById('pendapatanBpjsModal');
    modal?.classList.remove('show');

    const form = document.getElementById('formPendapatanBpjs');
    form?.reset();

    document.querySelectorAll('.nominal-display-bpjs').forEach(i => i.value = '0');
    document.querySelectorAll('.nominal-value-bpjs').forEach(i => i.value = 0);

    document.getElementById('totalPembayaranBpjs').innerText = 'Rp 0';

    const btn = document.getElementById('btnSimpanPendapatanBpjs');
    if (btn) {
        btn.disabled = true;
        btn.innerText = '💾 Simpan';
    }

    isEditBpjs = false;
    editBpjsId = null;

    const title = document.querySelector('#pendapatanBpjsModal .modal-title');
    if (title) title.innerText = '➕ Tambah Pendapatan BPJS';
};

/* =========================
   UI HELPERS
========================= */
function toggleNoSepField() {
    const jenis = document.getElementById('bpjsJenisSelect')?.value;
    const noSepGroup = document.getElementById('noSepGroup');
    const noSepInput = document.getElementById('bpjsNoSep');

    if (!noSepGroup || !noSepInput) return;

    if (jenis === 'REGULAR') {
        noSepGroup.style.display = 'block';
        noSepInput.setAttribute('required', 'required');
    } else {
        noSepGroup.style.display = 'none';
        noSepInput.removeAttribute('required');
    }
}

/* =========================
   SUBMIT
========================= */
window.submitPendapatanBpjs = async function (event) {
    event.preventDefault();

    const form = document.getElementById('formPendapatanBpjs');
    if (!form) return;

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btnSimpan = document.getElementById('btnSimpanPendapatanBpjs');
    btnSimpan.disabled = true;
    btnSimpan.innerText = '⏳ Menyimpan...';

    const formData = new FormData(form);

    if (isEditBpjs) {
        formData.append('_method', 'PUT');
    }

    const url = isEditBpjs
        ? `/dashboard/pendapatan/bpjs/${editBpjsId}`
        : `/dashboard/pendapatan/bpjs`;

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

        toast(isEditBpjs ? 'Data berhasil diperbarui' : 'Data berhasil disimpan', 'success');
        closePendapatanBpjsModal();
        loadPendapatanBpjs();

    } catch (err) {
        toast(err.message, 'error');
        btnSimpan.disabled = false;
        btnSimpan.innerText = isEditBpjs ? '💾 Simpan Perubahan' : '💾 Simpan';
    }
};

/* =========================
   DATA LOADING
========================= */
function loadPendapatanBpjs(page = bpjsPage) {
    bpjsPage = page;

    const tbody = document.getElementById('pendapatanBpjsBody');
    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center" style="padding: 40px; color: #94a3b8;">
                <i class="ph ph-spinner" style="font-size: 32px; animation: spin 1s linear infinite; margin-bottom: 8px;"></i>
                <p>Memuat data...</p>
            </td>
        </tr>
    `;

    const params = new URLSearchParams({
        page: bpjsPage,
        per_page: bpjsPerPage,
        search: bpjsKeyword,
        jenis_bpjs: currentBpjsTab
    });

    fetch(`/dashboard/pendapatan/bpjs?${params.toString()}`, {
        headers: { Accept: 'application/json' }
    })
        .then(async res => {
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Gagal memuat data');
            return json;
        })
        .then(res => {
            const data = res.data || [];
            renderPaginationBpjs(res);
            renderSummaryBpjs(res.aggregates);

            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <i class="ph ph-warning-circle" style="font-size: 32px; margin-bottom: 8px;"></i>
                            <p>Belum ada data pendapatan BPJS</p>
                        </td>
                    </tr>
                `;
                return;
            }

            const canCRUD = window.hasPermission('PENDAPATAN_BPJS_CRUD');

            tbody.innerHTML = ''; // 🔥 Bersihkan "Memuat data..." sebelum render

            data.forEach((item, index) => {
                const noSepCol = (currentBpjsTab === 'REGULAR')
                    ? `<td><span class="font-mono text-slate-600">${item.no_sep || '-'}</span></td>`
                    : '';

                tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center">${res.from + index}</td>
                    <td>${formatTanggal(item.tanggal)}</td>
                    ${noSepCol}
                    <td class="font-medium">${escapeHtml(item.nama_pasien ?? '-')}</td>
                    <td>${escapeHtml(item.perusahaan?.nama ?? item.transaksi ?? '-')}</td>
                    <td><span class="badge badge-info">${item.ruangan?.nama ?? '-'}</span></td>
                    <td class="text-right font-bold" style="color: #0f172a;">${formatRupiah(item.total)}</td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button class="btn-aksi detail" onclick="detailPendapatanBpjs(${item.id})" title="Lihat Detail">
                                <i class="ph ph-eye"></i>
                            </button>
                            ${canCRUD ? `
                                <button class="btn-aksi edit" onclick="editPendapatanBpjs(${item.id})" title="Edit Data">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn-aksi delete" onclick="hapusPendapatanBpjs(${item.id})" title="Hapus Data">
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
                    <td colspan="8" class="text-center" style="padding: 40px; color: #ef4444;">
                        <i class="ph ph-x-circle" style="font-size: 32px; margin-bottom: 8px;"></i>
                        <p>Gagal memuat data. Silakan coba lagi.</p>
                    </td>
                </tr>
            `;
        });
}

function renderSummaryBpjs(agg) {
    if (!agg) return;

    const setText = (sel, val) => {
        const el = document.querySelector(sel);
        if (el) el.innerText = val;
    };

    setText('[data-summary-bpjs="rs"]', formatRupiah(agg.total_rs));
    setText('[data-summary-bpjs="pelayanan"]', formatRupiah(agg.total_pelayanan));
    setText('[data-summary-bpjs="total"]', formatRupiah(agg.total_all));

    const rsPercent = agg.total_all ? Math.round((agg.total_rs / agg.total_all) * 100) : 0;
    const pelPercent = agg.total_all ? (100 - rsPercent) : 0;
    setText('[data-summary-percent-bpjs="rs"]', rsPercent + '% dari total');
    setText('[data-summary-percent-bpjs="pelayanan"]', pelPercent + '% dari total');
}

window.loadPendapatanBpjs = loadPendapatanBpjs;

/* =========================
   ACTIONS
========================= */
window.detailPendapatanBpjs = function (id) {
    const modal = document.getElementById('pendapatanBpjsDetailModal');
    const content = document.getElementById('detailPendapatanBpjsContent');

    modal.classList.add('show');
    content.innerHTML = `
        <div class="flex flex-col items-center justify-center py-8 text-slate-500">
            <i class="ph ph-spinner animate-spin text-3xl mb-2"></i>
            <p>Memuat detail...</p>
        </div>
    `;

    fetch(`/dashboard/pendapatan/bpjs/${id}`, {
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
                <span class="label">Jenis</span>
                <span class="badge badge-info">${data.jenis_bpjs}</span>
            </div>
            ${data.no_sep ? `
            <div class="detail-row">
                <span class="label">No SEP</span>
                <span class="value font-mono">${data.no_sep}</span>
            </div>
            ` : ''}
            <div class="detail-row">
                <span class="label">Nama Pasien</span>
                <span class="value font-medium">${escapeHtml(data.nama_pasien ?? '-')}</span>
            </div>
            <div class="detail-row">
                <span class="label">Ruangan</span>
                <span class="value">${data.ruangan?.nama ?? '-'}</span>
            </div>
            <div class="detail-row">
                <span class="label">Perusahaan</span>
                <span class="value">${data.perusahaan?.nama ?? (data.transaksi || '-')}</span>
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

window.closeDetailPendapatanBpjs = function () {
    document.getElementById('pendapatanBpjsDetailModal')?.classList.remove('show');
};

window.editPendapatanBpjs = async function (id) {
    isEditBpjs = true;
    editBpjsId = id;

    const title = document.querySelector('#pendapatanBpjsModal .modal-title');
    if (title) title.innerText = '✏️ Edit Pendapatan BPJS';

    const [, data] = await Promise.all([
        openPendapatanBpjsModal(),
        fetch(`/dashboard/pendapatan/bpjs/${id}`, { headers: { Accept: 'application/json' } }).then(res => res.json())
    ]);

    const form = document.getElementById('formPendapatanBpjs');
    form.querySelector('[name="tanggal"]').value = data.tanggal.substring(0, 10);
    form.querySelector('[name="jenis_bpjs"]').value = data.jenis_bpjs;
    form.querySelector('[name="no_sep"]').value = data.no_sep || '';
    form.querySelector('[name="nama_pasien"]').value = data.nama_pasien;
    form.querySelector('[name="ruangan_id"]').value = data.ruangan_id;

    if (data.perusahaan_id) {
        form.querySelector('[name="perusahaan_id"]').value = data.perusahaan_id;
        syncTransaksiBpjs();
    }

    toggleNoSepField();
    form.querySelector('[name="metode_pembayaran"]').value = data.metode_pembayaran;
    form.querySelector('[name="metode_pembayaran"]').onchange();

    setTimeout(() => {
        if (data.bank) {
            form.querySelector('[name="bank"]').value = data.bank;
            if (form.querySelector('[name="bank"]').onchange) form.querySelector('[name="bank"]').onchange();
            form.querySelector('[name="bank"]').dispatchEvent(new Event('change'));
        }
        setTimeout(() => {
            if (data.metode_detail) form.querySelector('[name="metode_detail"]').value = data.metode_detail;
            cekSiapSimpanBpjs();
        }, 50);
    }, 150);

    form.querySelector('[name="rs_tindakan"]').value = data.rs_tindakan;
    form.querySelector('[name="rs_obat"]').value = data.rs_obat;
    form.querySelector('[name="pelayanan_tindakan"]').value = data.pelayanan_tindakan;
    form.querySelector('[name="pelayanan_obat"]').value = data.pelayanan_obat;

    form.querySelectorAll('.nominal-display-bpjs').forEach((disp, i) => {
        const val = form.querySelectorAll('.nominal-value-bpjs')[i].value;
        disp.value = formatRibuan(val);
    });

    hitungTotalBpjs();
    cekSiapSimpanBpjs();
};

window.hapusPendapatanBpjs = function (id) {
    openConfirm('Hapus Data', 'Yakin ingin menghapus data BPJS ini?', async () => {
        try {
            const res = await fetch(`/dashboard/pendapatan/bpjs/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error();
            toast('Data berhasil dihapus', 'success');
            loadPendapatanBpjs();
        } catch {
            toast('Gagal menghapus data', 'error');
        }
    });
};

/* =========================
   INITIALIZATION
========================= */
window.initPendapatanBpjs = function () {
    bpjsKeyword = '';
    const btnTambah = document.getElementById('btnTambahPendapatanBpjs');
    if (btnTambah) {
        btnTambah.onclick = async () => {
            const form = document.getElementById('formPendapatanBpjs');
            form?.reset();
            isEditBpjs = false;
            editBpjsId = null;
            const title = document.querySelector('#pendapatanBpjsModal .modal-title');
            if (title) title.innerText = '➕ Tambah Pendapatan BPJS';
            await openPendapatanBpjsModal();
        };
    }

    const searchInput = document.getElementById('searchPendapatanBpjs');
    if (searchInput) {
        let timer;
        searchInput.oninput = (e) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                bpjsKeyword = e.target.value.trim();
                loadPendapatanBpjs(1);
            }, 400);
        };
    }

    document.querySelectorAll('.nominal-display-bpjs').forEach(input => {
        input.addEventListener('input', () => {
            const val = parseAngka(input.value);
            input.nextElementSibling.value = val;
            hitungTotalBpjs();
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

    const form = document.getElementById('formPendapatanBpjs');
    if (form) {
        form.querySelector('[name="tanggal"]').oninput = cekSiapSimpanBpjs;
        form.querySelector('[name="nama_pasien"]').oninput = cekSiapSimpanBpjs;
        form.querySelector('[name="no_sep"]').addEventListener('input', cekSiapSimpanBpjs);
        form.querySelector('[name="ruangan_id"]').addEventListener('change', cekSiapSimpanBpjs);
        form.querySelector('[name="bank"]').addEventListener('change', cekSiapSimpanBpjs);
        form.querySelector('[name="metode_detail"]').addEventListener('change', cekSiapSimpanBpjs);

        const jenisSelect = document.getElementById('bpjsJenisSelect');
        if (jenisSelect) {
            jenisSelect.onchange = () => {
                toggleNoSepField();
                toggleBpjsNomis();
                cekSiapSimpanBpjs();
            };
        }

        const perusSelect = document.getElementById('bpjsPerusahaanSelect');
        if (perusSelect) {
            perusSelect.onchange = () => {
                syncTransaksiBpjs();
                cekSiapSimpanBpjs();
            };
        }

        const metodeSelect = document.getElementById('bpjsMetodePembayaran');
        const bankSelect = document.getElementById('bpjsBank');
        const detailSelect = document.getElementById('bpjsMetodeDetail');

        if (metodeSelect) {
            metodeSelect.addEventListener('change', () => {
                resetSelect(bankSelect, '-- Pilih Bank --');
                resetSelect(detailSelect, '-- Metode Detail --');

                if (metodeSelect.value === 'TUNAI') {
                    bankSelect.disabled = true;
                    detailSelect.disabled = true;
                    addOption(bankSelect, { value: 'BRK', label: 'Bank Riau Kepri Syariah' });
                    bankSelect.value = 'BRK';
                    addOption(detailSelect, { value: 'SETOR_TUNAI', label: 'Setor Tunai' });
                    detailSelect.value = 'SETOR_TUNAI';
                } else if (metodeSelect.value === 'NON_TUNAI') {
                    bankSelect.disabled = false;
                    bankSelect.removeAttribute('readonly');
                    detailSelect.disabled = true; // Wait for bank
                    addOption(bankSelect, { value: 'BRK', label: 'Bank Riau Kepri Syariah' });
                    addOption(bankSelect, { value: 'BSI', label: 'Bank Syariah Indonesia' });
                } else {
                    bankSelect.disabled = true;
                    detailSelect.disabled = true;
                }
                cekSiapSimpanBpjs();
            });
        }

        if (bankSelect) {
            bankSelect.addEventListener('change', () => {
                if (metodeSelect.value !== 'NON_TUNAI') return;

                resetSelect(detailSelect, '-- Metode Detail --');

                if (bankSelect.value) {
                    detailSelect.disabled = false;
                    detailSelect.removeAttribute('readonly');
                    addOption(detailSelect, { value: 'QRIS', label: 'QRIS' });
                    addOption(detailSelect, { value: 'TRANSFER', label: 'Transfer' });
                } else {
                    detailSelect.disabled = true;
                }
                cekSiapSimpanBpjs();
            });
        }
    }

    const btnImport = document.getElementById('btnImportBpjs');
    const modalImport = document.getElementById('modalImportBpjs');
    if (btnImport && modalImport) {
        btnImport.onclick = () => modalImport.classList.add('show');
    }

    const btnBulk = document.getElementById('btnBulkDeleteBpjs');
    const modalBulk = document.getElementById('modalBulkDeleteBpjs');
    if (btnBulk && modalBulk) {
        btnBulk.onclick = () => modalBulk.classList.add('show');
    }

    const formImport = document.getElementById('formImportBpjs');
    if (formImport) {
        formImport.onsubmit = async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.disabled = true;
            btn.innerText = '⏳ Mengimport...';

            const formData = new FormData(formImport);
            try {
                const res = await fetch('/dashboard/pendapatan/bpjs/import', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    toast(`Berhasil mengimport ${data.count} data`, 'success');
                    closeModal('modalImportBpjs');
                    formImport.reset();
                    loadPendapatanBpjs(1);
                } else {
                    throw new Error(data.message || 'Gagal import data');
                }
            } catch (err) {
                toast(err.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerText = originalText;
            }
        };
    }

    const formBulkDelete = document.getElementById('formBulkDeleteBpjs');
    if (formBulkDelete) {
        formBulkDelete.onsubmit = async (e) => {
            e.preventDefault();
            const tanggal = document.getElementById('bulkDeleteDate').value;
            const jenis = document.getElementById('bulkDeleteJenis').value;

            openConfirm('Hapus Massal', `Yakin ingin menghapus SEMUA data BPJS pada tanggal ${formatTanggal(tanggal)}?`, async () => {
                try {
                    const res = await fetch('/dashboard/pendapatan/bpjs/bulk-delete', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ tanggal, jenis_bpjs: jenis })
                    });
                    const data = await res.json();
                    if (data.success) {
                        toast(`Berhasil menghapus ${data.count} data`, 'success');
                        closeModal('modalBulkDeleteBpjs');
                        loadPendapatanBpjs(1);
                    } else {
                        throw new Error(data.message || 'Gagal hapus massal');
                    }
                } catch (err) {
                    toast(err.message, 'error');
                }
            });
        };
    }
    toggleBpjsNomis();
};

function toggleBpjsNomis() {
    const jenis = document.getElementById('bpjsJenisSelect')?.value;
    const gTindakan = document.getElementById('groupTindakan');
    const gObat = document.getElementById('groupObat');

    if (!gTindakan || !gObat) return;

    if (jenis === 'OBAT') {
        gTindakan.style.display = 'none';
        gObat.style.display = 'grid';
        // Reset tindakan
        gTindakan.querySelectorAll('.nominal-value-bpjs').forEach(i => i.value = 0);
        gTindakan.querySelectorAll('.nominal-display-bpjs').forEach(i => i.value = '0');
    } else {
        // REGULAR / EVAKUASI
        gTindakan.style.display = 'grid';
        gObat.style.display = 'none';
        // Reset obat
        gObat.querySelectorAll('.nominal-value-bpjs').forEach(i => i.value = 0);
        gObat.querySelectorAll('.nominal-display-bpjs').forEach(i => i.value = '0');
    }
    hitungTotalBpjs();
    cekSiapSimpanBpjs();
}

function hitungTotalBpjs() {
    let total = 0;
    const jenis = document.getElementById('bpjsJenisSelect')?.value;

    if (jenis === 'OBAT') {
        document.querySelectorAll('#groupObat .nominal-value-bpjs').forEach(i => total += parseFloat(i.value || 0));
    } else {
        document.querySelectorAll('#groupTindakan .nominal-value-bpjs').forEach(i => total += parseFloat(i.value || 0));
    }

    document.getElementById('totalPembayaranBpjs').innerText = formatRupiah(total);
}

function cekSiapSimpanBpjs() {
    const form = document.getElementById('formPendapatanBpjs');
    if (!form) return;
    const data = new FormData(form);

    let valid = data.get('tanggal') && data.get('nama_pasien') && data.get('ruangan_id');
    if (data.get('jenis_bpjs') === 'REGULAR' && !data.get('no_sep')) valid = false;

    const metode = data.get('metode_pembayaran');
    if (!metode) valid = false;
    if (metode === 'NON_TUNAI' && (!data.get('bank') || !data.get('metode_detail'))) valid = false;

    const btn = document.getElementById('btnSimpanPendapatanBpjs');
    if (btn) btn.disabled = !valid;
}

async function loadRuanganBpjs() {
    const select = document.getElementById('bpjsRuanganSelect');
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

async function loadPerusahaanBpjs() {
    const select = document.getElementById('bpjsPerusahaanSelect');
    if (!select) return;

    if (window._cachePerusahaan) {
        renderOptions(select, window._cachePerusahaan, '-- Pilih Perusahaan --', 'kode', 'nama');
    }

    try {
        const res = await fetch('/dashboard/perusahaan-list');
        const data = await res.json();
        window._cachePerusahaan = data;
        renderOptions(select, data, '-- Pilih Perusahaan --', 'kode', 'nama');
    } catch (e) {
        console.error('Failed to load perusahaan list', e);
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

function syncTransaksiBpjs() {
    const select = document.getElementById('bpjsPerusahaanSelect');
    const hidden = document.getElementById('bpjsTransaksiHidden');
    if (!select || !hidden) return;
    const selectedOption = select.options[select.selectedIndex];
    hidden.value = selectedOption?.dataset?.nama || '';
}

function renderPaginationBpjs(meta) {
    const info = document.getElementById('paginationInfoBpjs');
    if (info) info.innerText = `Menampilkan ${meta.from ?? 0}–${meta.to ?? 0} dari ${meta.total ?? 0} data`;

    const pageInfo = document.getElementById('pageInfoBpjs');
    if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;

    const prev = document.getElementById('prevPageBpjs');
    const next = document.getElementById('nextPageBpjs');

    if (prev) {
        prev.disabled = (meta.current_page === 1);
        prev.onclick = () => loadPendapatanBpjs(meta.current_page - 1);
    }
    if (next) {
        next.disabled = (meta.current_page === meta.last_page);
        next.onclick = () => loadPendapatanBpjs(meta.current_page + 1);
    }
}
