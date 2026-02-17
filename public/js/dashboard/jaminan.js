let jaminanPage = 1;
let jaminanPerPage = 10;
let jaminanKeyword = '';
let isEditJaminan = false;
let editJaminanId = null;

// Global Caches to prevent race conditions and "empty then fill" flicker
window._cacheRuangan = window._cacheRuangan || null;
window._cachePerusahaan = window._cachePerusahaan || null;

/* =========================
   MODAL CONTROL
========================= */
window.openPendapatanJaminanModal = async function () {
    const modal = document.getElementById('pendapatanJaminanModal');
    if (!modal) return;

    modal.classList.add('show');
    // We await both to ensure dropdowns are ready before any value setting happens
    await Promise.all([
        loadRuanganJaminan(),
        loadPerusahaanJaminan()
    ]);

    // Trigger Non-Tunai logic
    document.getElementById('jaminanMetodePembayaran')?.dispatchEvent(new Event('change'));
};

window.closePendapatanJaminanModal = function () {
    const modal = document.getElementById('pendapatanJaminanModal');
    modal?.classList.remove('show');

    const form = document.getElementById('formPendapatanJaminan');
    form?.reset();

    document.querySelectorAll('.nominal-display-jaminan').forEach(i => i.value = '0');
    document.querySelectorAll('.nominal-value-jaminan').forEach(i => i.value = 0);

    document.getElementById('totalPembayaranJaminan').innerText = 'Rp 0';

    const btn = document.getElementById('btnSimpanPendapatanJaminan');
    if (btn) {
        btn.disabled = true;
        btn.innerText = '💾 Simpan';
    }

    isEditJaminan = false;
    editJaminanId = null;

    const title = document.querySelector('#pendapatanJaminanModal .modal-title');
    if (title) title.innerText = '➕ Tambah Pendapatan Jaminan';
};

/* =========================
   SUBMIT
========================= */
window.submitPendapatanJaminan = async function (event) {
    event.preventDefault();

    const form = document.getElementById('formPendapatanJaminan');
    if (!form) return;

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btnSimpan = document.getElementById('btnSimpanPendapatanJaminan');
    btnSimpan.disabled = true;
    btnSimpan.innerText = '⏳ Menyimpan...';

    const formData = new FormData(form);

    if (isEditJaminan) {
        formData.append('_method', 'PUT');
    }

    const url = isEditJaminan
        ? `/dashboard/pendapatan/jaminan/${editJaminanId}`
        : `/dashboard/pendapatan/jaminan`;

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

        toast(isEditJaminan ? 'Data berhasil diperbarui' : 'Data berhasil disimpan', 'success');
        closePendapatanJaminanModal();
        loadPendapatanJaminan();

    } catch (err) {
        toast(err.message, 'error');
        btnSimpan.disabled = false;
        btnSimpan.innerText = isEditJaminan ? '💾 Simpan Perubahan' : '💾 Simpan';
    }
};

/* =========================
   DATA LOADING
========================= */
function loadPendapatanJaminan(page = jaminanPage) {
    jaminanPage = page;

    const tbody = document.getElementById('pendapatanJaminanBody');
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
        page: jaminanPage,
        per_page: jaminanPerPage,
        search: jaminanKeyword
    });

    fetch(`/dashboard/pendapatan/jaminan?${params.toString()}`, {
        headers: { Accept: 'application/json' }
    })
        .then(async res => {
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Gagal memuat data');
            return json;
        })
        .then(res => {
            const data = res.data || [];
            renderPaginationJaminan(res);
            renderSummaryJaminan(res.aggregates);

            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <i class="ph ph-warning-circle" style="font-size: 32px; margin-bottom: 8px;"></i>
                            <p>Belum ada data pendapatan Jaminan</p>
                        </td>
                    </tr>
                `;
                return;
            }

            const canCRUD = window.hasPermission('PENDAPATAN_JAMINAN_CRUD');

            tbody.innerHTML = ''; // 🔥 Bersihkan "Memuat data..." sebelum render

            data.forEach((item, index) => {
                tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center">${res.from + index}</td>
                    <td>${formatTanggal(item.tanggal)}</td>
                    <td class="font-medium">${escapeHtml(item.nama_pasien ?? '-')}</td>
                    <td>${escapeHtml(item.perusahaan?.nama ?? item.transaksi ?? '-')}</td>
                    <td><span class="badge badge-info">${item.ruangan?.nama ?? '-'}</span></td>
                    <td class="text-right font-bold" style="color: #0f172a;">${formatRupiah(item.total)}</td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button class="btn-aksi detail" onclick="detailPendapatanJaminan(${item.id})" title="Lihat Detail">
                                <i class="ph ph-eye"></i>
                            </button>
                            ${canCRUD ? `
                                <button class="btn-aksi edit" onclick="editPendapatanJaminan(${item.id})" title="Edit Data">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn-aksi delete" onclick="hapusPendapatanJaminan(${item.id})" title="Hapus Data">
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

function renderSummaryJaminan(agg) {
    if (!agg) return;

    const setText = (sel, val) => {
        const el = document.querySelector(sel);
        if (el) el.innerText = val;
    };

    setText('[data-summary-jaminan="rs"]', formatRupiah(agg.total_rs));
    setText('[data-summary-jaminan="pelayanan"]', formatRupiah(agg.total_pelayanan));
    setText('[data-summary-jaminan="total"]', formatRupiah(agg.total_all));

    const rsPercent = agg.total_all ? Math.round((agg.total_rs / agg.total_all) * 100) : 0;
    const pelPercent = agg.total_all ? (100 - rsPercent) : 0;
    setText('[data-summary-percent-jaminan="rs"]', rsPercent + '% dari total');
    setText('[data-summary-percent-jaminan="pelayanan"]', pelPercent + '% dari total');
}

window.loadPendapatanJaminan = loadPendapatanJaminan;

/* =========================
   ACTIONS
========================= */
window.detailPendapatanJaminan = function (id) {
    const modal = document.getElementById('pendapatanJaminanDetailModal');
    const content = document.getElementById('detailPendapatanJaminanContent');

    modal.classList.add('show');
    content.innerHTML = `
        <div class="flex flex-col items-center justify-center py-8 text-slate-500">
            <i class="ph ph-spinner animate-spin text-3xl mb-2"></i>
            <p>Memuat detail...</p>
        </div>
    `;

    fetch(`/dashboard/pendapatan/jaminan/${id}`, {
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
                <span class="label">Perusahaan</span>
                <span class="value">${data.perusahaan?.nama ?? (data.transaksi || '-')}</span>
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

window.closeDetailPendapatanJaminan = function () {
    document.getElementById('pendapatanJaminanDetailModal')?.classList.remove('show');
};

window.editPendapatanJaminan = async function (id) {
    isEditJaminan = true;
    editJaminanId = id;

    const title = document.querySelector('#pendapatanJaminanModal .modal-title');
    if (title) title.innerText = '✏️ Edit Pendapatan Jaminan';

    const [, data] = await Promise.all([
        openPendapatanJaminanModal(),
        fetch(`/dashboard/pendapatan/jaminan/${id}`, { headers: { Accept: 'application/json' } }).then(res => res.json())
    ]);

    const form = document.getElementById('formPendapatanJaminan');
    form.querySelector('[name="tanggal"]').value = data.tanggal.substring(0, 10);
    form.querySelector('[name="nama_pasien"]').value = data.nama_pasien;
    form.querySelector('[name="ruangan_id"]').value = data.ruangan_id;

    if (data.perusahaan_id) {
        form.querySelector('[name="perusahaan_id"]').value = data.perusahaan_id;
        syncTransaksiJaminan();
    }

    form.querySelector('[name="metode_pembayaran"]').value = data.metode_pembayaran;
    // Trigger consolidated listener
    form.querySelector('[name="metode_pembayaran"]').onchange();

    setTimeout(() => {
        if (data.bank) form.querySelector('[name="bank"]').value = data.bank;
        if (data.metode_detail) form.querySelector('[name="metode_detail"]').value = data.metode_detail;
        cekSiapSimpanJaminan();
    }, 150);

    form.querySelector('[name="rs_tindakan"]').value = data.rs_tindakan;
    form.querySelector('[name="rs_obat"]').value = data.rs_obat;
    form.querySelector('[name="pelayanan_tindakan"]').value = data.pelayanan_tindakan;
    form.querySelector('[name="pelayanan_obat"]').value = data.pelayanan_obat;

    form.querySelectorAll('.nominal-display-jaminan').forEach((disp, i) => {
        const val = form.querySelectorAll('.nominal-value-jaminan')[i].value;
        disp.value = formatRibuan(val);
    });

    hitungTotalJaminan();
    cekSiapSimpanJaminan();
};

window.hapusPendapatanJaminan = function (id) {
    openConfirm('Hapus Data', 'Yakin ingin menghapus data Jaminan ini?', async () => {
        try {
            const res = await fetch(`/dashboard/pendapatan/jaminan/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error();
            toast('Data berhasil dihapus', 'success');
            loadPendapatanJaminan();
        } catch {
            toast('Gagal menghapus data', 'error');
        }
    });
};

/* =========================
   INITIALIZATION
========================= */
window.initPendapatanJaminan = function () {
    jaminanKeyword = '';
    const btn = document.getElementById('btnTambahPendapatanJaminan');
    if (btn) {
        btn.onclick = async () => {
            const form = document.getElementById('formPendapatanJaminan');
            form?.reset();
            isEditJaminan = false;
            editJaminanId = null;
            const title = document.querySelector('#pendapatanJaminanModal .modal-title');
            if (title) title.innerText = '➕ Tambah Pendapatan Jaminan';
            await openPendapatanJaminanModal();
        };
    }

    const searchInput = document.getElementById('searchPendapatanJaminan');
    if (searchInput) {
        let timer;
        searchInput.oninput = (e) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                jaminanKeyword = e.target.value.trim();
                loadPendapatanJaminan(1);
            }, 400);
        };
    }

    document.querySelectorAll('.nominal-display-jaminan').forEach(input => {
        input.addEventListener('input', () => {
            const val = parseAngka(input.value);
            input.nextElementSibling.value = val;
            hitungTotalJaminan();
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

    const form = document.getElementById('formPendapatanJaminan');
    if (form) {
        // Patients & Date
        form.querySelector('[name="tanggal"]').oninput = cekSiapSimpanJaminan;
        form.querySelector('[name="nama_pasien"]').oninput = cekSiapSimpanJaminan;

        // Dropdowns
        form.querySelector('[name="ruangan_id"]').onchange = cekSiapSimpanJaminan;
        form.querySelector('[name="bank"]').onchange = cekSiapSimpanJaminan;
        form.querySelector('[name="metode_detail"]').onchange = cekSiapSimpanJaminan;

        // Combined for Perusahaan
        const perusSelect = document.getElementById('jaminanPerusahaanSelect');
        if (perusSelect) {
            perusSelect.onchange = () => {
                syncTransaksiJaminan();
                cekSiapSimpanJaminan();
            };
        }

        const metodeSelect = document.getElementById('jaminanMetodePembayaran');
        if (metodeSelect) {
            metodeSelect.onchange = () => {
                const bank = document.getElementById('jaminanBank');
                const detail = document.getElementById('jaminanMetodeDetail');
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
                cekSiapSimpanJaminan();
            };
        }

        // =========================
        // IMPORT & BULK DELETE JAMINAN
        // =========================
        const btnImport = document.getElementById('btnImportJaminan');
        if (btnImport) {
            btnImport.onclick = () => {
                document.getElementById('modalImportJaminan')?.classList.add('show');
            };
        }

        const formImport = document.getElementById('formImportJaminan');
        if (formImport) {
            formImport.onsubmit = async (e) => {
                e.preventDefault();
                const btn = formImport.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerText = '⏳ Mengimport...';

                try {
                    const res = await fetch('/dashboard/pendapatan/jaminan/import', {
                        method: 'POST',
                        body: new FormData(formImport),
                        headers: { 'X-CSRF-TOKEN': csrfToken() }
                    });
                    const resData = await res.json();
                    if (!res.ok) throw new Error(resData.message || 'Gagal import');

                    toast(`Berhasil mengimport ${resData.count} data`, 'success');
                    closeModal('modalImportJaminan');
                    formImport.reset();
                    loadPendapatanJaminan();
                } catch (err) {
                    toast(err.message, 'error');
                } finally {
                    btn.disabled = false;
                    btn.innerText = 'Mulai Import';
                }
            };
        }

        const btnBulkDelete = document.getElementById('btnBulkDeleteJaminan');
        if (btnBulkDelete) {
            btnBulkDelete.onclick = () => {
                document.getElementById('modalBulkDeleteJaminan')?.classList.add('show');
            };
        }

        const formBulkDelete = document.getElementById('formBulkDeleteJaminan');
        if (formBulkDelete) {
            formBulkDelete.onsubmit = async (e) => {
                e.preventDefault();
                const tgl = document.getElementById('bulkDeleteDateJaminan').value;
                if (!tgl) return;

                openConfirm('Hapus Massal', `Yakin ingin menghapus SEMUA data Jaminan pada tanggal ${formatTanggal(tgl)}?`, async () => {
                    const btn = formBulkDelete.querySelector('button[type="submit"]');
                    btn.disabled = true;
                    btn.innerText = '⏳ Menghapus...';

                    try {
                        const res = await fetch('/dashboard/pendapatan/jaminan/bulk-delete', {
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
                        closeModal('modalBulkDeleteJaminan');
                        loadPendapatanJaminan();
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

function hitungTotalJaminan() {
    let total = 0;
    document.querySelectorAll('.nominal-value-jaminan').forEach(i => total += parseFloat(i.value || 0));
    document.getElementById('totalPembayaranJaminan').innerText = formatRupiah(total);
}

function cekSiapSimpanJaminan() {
    const form = document.getElementById('formPendapatanJaminan');
    if (!form) return;
    const data = new FormData(form);

    let valid = data.get('tanggal') && data.get('nama_pasien') && data.get('ruangan_id') && data.get('perusahaan_id');

    const metode = data.get('metode_pembayaran');
    if (!metode) valid = false;
    if (metode === 'NON_TUNAI' && (!data.get('bank') || !data.get('metode_detail'))) valid = false;

    const btn = document.getElementById('btnSimpanPendapatanJaminan');
    if (btn) btn.disabled = !valid;
}

async function loadRuanganJaminan() {
    const select = document.getElementById('jaminanRuanganSelect');
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

async function loadPerusahaanJaminan() {
    const select = document.getElementById('jaminanPerusahaanSelect');
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
    // We do NOT set onchange here anymore to avoid overwriting init listeners
}

function syncTransaksiJaminan() {
    const select = document.getElementById('jaminanPerusahaanSelect');
    const hidden = document.getElementById('jaminanTransaksiHidden');
    if (!select || !hidden) return;
    const selectedOption = select.options[select.selectedIndex];
    hidden.value = selectedOption?.dataset?.nama || '';
}

function renderPaginationJaminan(meta) {
    const info = document.getElementById('paginationInfoJaminan');
    if (info) info.innerText = `Menampilkan ${meta.from ?? 0}–${meta.to ?? 0} dari ${meta.total ?? 0} data`;

    const pageInfo = document.getElementById('pageInfoJaminan');
    if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;

    const prev = document.getElementById('prevPageJaminan');
    const next = document.getElementById('nextPageJaminan');

    if (prev) {
        prev.disabled = (meta.current_page === 1);
        prev.onclick = () => loadPendapatanJaminan(meta.current_page - 1);
    }
    if (next) {
        next.disabled = (meta.current_page === meta.last_page);
        next.onclick = () => loadPendapatanJaminan(meta.current_page + 1);
    }
}
