/* =========================
   PENGELUARAN JS
========================= */

let pengeluaranPage = 1;
let pengeluaranPerPage = 10;
let pengeluaranKeyword = '';
let currentKategori = '';
let isEditPengeluaran = false;
let editPengeluaranId = null;

/* =========================
   ROUTING / APP.JS INTEGRATION
========================= */


window.initPengeluaran = function (kategori) {
    currentKategori = kategori;
    pengeluaranPage = 1;
    pengeluaranKeyword = '';

    // Bind Nominal Input Logic
    const nominalInput = document.getElementById('pengeluaranNominalDisplay');
    const nominalHidden = document.getElementById('pengeluaranNominalValue');

    if (nominalInput && nominalHidden) {
        nominalInput.oninput = () => {
            const val = parseAngka(nominalInput.value);
            nominalHidden.value = val;
            if (window.calculateTotalDibayarkan) window.calculateTotalDibayarkan();
        };
        nominalInput.onblur = () => {
            nominalInput.value = formatRibuan(nominalHidden.value);
        };
        nominalInput.onfocus = () => {
            const val = parseAngka(nominalInput.value);
            nominalInput.value = val === 0 ? '' : val.toString().replace('.', ',');
        };
    }

    // Bind Potongan Pajak Logic
    const pajakInput = document.getElementById('pengeluaranPotonganPajakDisplay');
    const pajakHidden = document.getElementById('pengeluaranPotonganPajakValue');

    if (pajakInput && pajakHidden) {
        pajakInput.oninput = () => {
            const val = parseAngka(pajakInput.value);
            pajakHidden.value = val;
            if (window.calculateTotalDibayarkan) window.calculateTotalDibayarkan();
        };
        pajakInput.onblur = () => {
            pajakInput.value = formatRibuan(pajakHidden.value);
        };
        pajakInput.onfocus = () => {
            const val = parseAngka(pajakInput.value);
            pajakInput.value = val === 0 ? '' : val.toString().replace('.', ',');
        };
    }

    // Bind Search Input Logic
    const searchInput = document.getElementById('searchPengeluaran');
    if (searchInput) {
        searchInput.oninput = (e) => window.handleSearchPengeluaran(e);
    }

    // Bind Auto SPP Logic (only for create, or if user explicitly wants)
    const tglEl = document.getElementById('pengeluaranTanggal');
    const mtdEl = document.getElementById('pengeluaranMetode');
    if (tglEl) tglEl.addEventListener('change', () => updateAutoSpp(true));
    if (mtdEl) mtdEl.addEventListener('change', () => updateAutoSpp(true));

    loadPengeluaran();
}

async function updateAutoSpp(isExplicitChange = false) {
    // If editing, only update if the user explicitly changed something (via listener)
    if (isEditPengeluaran && !isExplicitChange) return;

    const tgl = document.getElementById('pengeluaranTanggal').value;
    const mtd = document.getElementById('pengeluaranMetode').value;
    const sppEl = document.getElementById('pengeluaranNoSPP');
    const spmEl = document.getElementById('pengeluaranNoSPM');
    const sp2dEl = document.getElementById('pengeluaranNoSP2D');

    if (!tgl || !mtd || !sppEl) return;

    try {
        const idParam = isEditPengeluaran ? `&id=${editPengeluaranId}` : '';
        const res = await fetch(`/dashboard/pengeluaran/next-spp?tanggal=${tgl}&metode=${mtd}${idParam}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        // Update fields automatically
        if (sppEl) sppEl.value = data.no_spp;
        if (spmEl) spmEl.value = data.no_spm;
        if (sp2dEl) sp2dEl.value = data.no_sp2d;

        if (isEditPengeluaran && isExplicitChange) {
            toast('Nomor administrasi disesuaikan', 'info');
        }
    } catch (err) {
        console.error('Failed to gen numbers', err);
    }
}

/* =========================
   MODAL CONTROL
 ========================= */
window.openPengeluaranForm = function (kategori, id = null) {
    const modal = document.getElementById('pengeluaranModal');
    if (!modal) return;

    modal.classList.add('show');
    resetPengeluaranForm();

    document.getElementById('pengeluaranKategori').value = kategori;

    const titleEl = document.getElementById('pengeluaranModalTitle');
    const sppEl = document.getElementById('pengeluaranNoSPP');
    const spmEl = document.getElementById('pengeluaranNoSPM');
    const sp2dEl = document.getElementById('pengeluaranNoSP2D');

    if (id) {
        isEditPengeluaran = true;
        editPengeluaranId = id;
        titleEl.innerText = 'Edit Pengeluaran';
        // Keep read-only to prevent manual override during edit
        [sppEl, spmEl, sp2dEl].forEach(el => {
            if (el) {
                el.readOnly = true;
                el.style.background = '#f8fafc';
                el.style.cursor = 'not-allowed';
            }
        });
        loadEditData(id);
    } else {
        isEditPengeluaran = false;
        editPengeluaranId = null;
        titleEl.innerText = 'Tambah Pengeluaran';
        [sppEl, spmEl, sp2dEl].forEach(el => {
            if (el) {
                el.readOnly = true;
                el.style.background = '#f8fafc';
                el.style.cursor = 'not-allowed';
            }
        });
        document.getElementById('pengeluaranTanggal').value = new Date().toISOString().split('T')[0];
        updateAutoSpp();
    }

    loadRekeningPengeluaran(kategori);
};

window.closePengeluaranModal = function () {
    const modal = document.getElementById('pengeluaranModal');
    modal?.classList.remove('show');
};

function resetPengeluaranForm() {
    const form = document.getElementById('formPengeluaran');
    form?.reset();
    document.getElementById('pengeluaranId').value = '';
    document.getElementById('pengeluaranNominalValue').value = 0;
    document.getElementById('pengeluaranNominalDisplay').value = '0';
    document.getElementById('pengeluaranPotonganPajakValue').value = 0;
    document.getElementById('pengeluaranPotonganPajakDisplay').value = '0';
    document.getElementById('pengeluaranTotalDibayarkanValue').value = 0;
    document.getElementById('pengeluaranTotalDibayarkanDisplay').value = '0';
}

window.calculateTotalDibayarkan = function () {
    const nominal = parseAngka(document.getElementById('pengeluaranNominalValue').value) || 0;
    const pajak = parseAngka(document.getElementById('pengeluaranPotonganPajakValue').value) || 0;
    const total = nominal - pajak;

    document.getElementById('pengeluaranTotalDibayarkanValue').value = total;
    document.getElementById('pengeluaranTotalDibayarkanDisplay').value = formatRibuan(total);
};

/* =========================
   DATA LOADING
========================= */
function loadPengeluaran(page = 1) {
    pengeluaranPage = page;
    const tbody = document.querySelector('#tablePengeluaran tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" class="text-center">Memuat data...</td></tr>`;



    const params = new URLSearchParams({
        kategori: currentKategori,
        page: pengeluaranPage,
        limit: pengeluaranPerPage,
        search: pengeluaranKeyword,

    });

    fetch(`/dashboard/pengeluaran?${params.toString()}`, {
        headers: { Accept: 'application/json' }
    })
        .then(res => {
            if (!res.ok) throw new Error(res.statusText || 'Gagal memuat data');
            return res.json();
        })
        .then(res => {
            // Update Summary Cards
            const countEl = document.getElementById('totalCountPengeluaran');
            const totalEl = document.getElementById('totalNominalPengeluaran');
            const upEl = document.getElementById('totalUP');
            const guEl = document.getElementById('totalGU');
            const lsEl = document.getElementById('totalLS');

            if (res.aggregates) {
                if (countEl) countEl.innerText = res.aggregates.total_count.toLocaleString('id-ID') + ' Transaksi';
                if (totalEl) totalEl.innerText = formatRupiah(res.aggregates.total_nominal);

                const pajakCardEl = document.getElementById('totalPajakPengeluaran');
                if (pajakCardEl) pajakCardEl.innerText = formatRupiah(res.aggregates.total_pajak || 0);

                const dibayarkanCardEl = document.getElementById('totalDibayarkanPengeluaran');
                if (dibayarkanCardEl) dibayarkanCardEl.innerText = formatRupiah(res.aggregates.total_dibayarkan || 0);

                if (upEl) upEl.innerText = formatRupiah(res.aggregates.total_up);
                const countUPEl = document.getElementById('countUP');
                if (countUPEl) countUPEl.innerText = res.aggregates.count_up.toLocaleString('id-ID') + ' Transaksi';

                if (guEl) guEl.innerText = formatRupiah(res.aggregates.total_gu);
                const countGUEl = document.getElementById('countGU');
                if (countGUEl) countGUEl.innerText = res.aggregates.count_gu.toLocaleString('id-ID') + ' Transaksi';

                if (lsEl) lsEl.innerText = formatRupiah(res.aggregates.total_ls);
                const countLSEl = document.getElementById('countLS');
                if (countLSEl) countLSEl.innerText = res.aggregates.count_ls.toLocaleString('id-ID') + ' Transaksi';
            }

            const data = res.data || [];
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center">Belum ada data.</td></tr>`;
                renderPaginationPengeluaran(res); // Ensure pagination is reset/updated even if empty
                return;
            }

            tbody.innerHTML = '';
            let no = res.from || 1;
            data.forEach(item => {
                tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center">${no++}</td>
                    <td class="text-center">${formatTanggal(item.tanggal)}</td>
                    <td style="line-height: 1.4;">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2"><span class="badge-mini bg-blue-100 text-blue-700">SPP</span> <small class="font-mono text-slate-500">${item.no_spp || '-'}</small></div>
                            <div class="flex items-center gap-2"><span class="badge-mini bg-emerald-100 text-emerald-700">SPM</span> <small class="font-mono text-slate-500">${item.no_spm || '-'}</small></div>
                            <div class="flex items-center gap-2"><span class="badge-mini bg-purple-100 text-purple-700">SP2D</span> <small class="font-mono text-slate-500">${item.no_sp2d || '-'}</small></div>
                        </div>
                    </td>
                    <td>${escapeHtml(item.uraian)}</td>
                    <td>
                        <div class="nominal-group">
                            <div class="nom-row">
                                <div class="nom-val val-bruto">${formatRupiahTable(item.nominal)}</div>
                                <span class="nom-label label-bruto">Bruto</span>
                            </div>
                            <div class="nom-row">
                                <div class="nom-val val-pajak">${formatRupiahTable(item.potongan_pajak || 0)}</div>
                                <span class="nom-label label-pajak">Pajak</span>
                            </div>
                            <div class="nom-row" style="margin-top: 2px; padding-top: 2px; border-top: 1px dashed #e2e8f0;">
                                <div class="nom-val val-netto">${formatRupiahTable(item.total_dibayarkan || 0)}</div>
                                <span class="nom-label label-netto">Netto</span>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button class="btn-aksi detail" onclick="window.openPengeluaranDetail(${item.id})" title="Preview">
                                <i class="ph ph-eye"></i>
                            </button>
                            ${(window.hasPermission('PENGELUARAN_UPDATE') || window.isAdmin) ? `
                            <button class="btn-aksi edit" onclick="window.openPengeluaranForm('${currentKategori}', ${item.id})" title="Edit">
                                <i class="ph ph-pencil-simple"></i>
                            </button>` : ''}
                            ${(window.hasPermission('PENGELUARAN_DELETE') || window.isAdmin) ? `
                            <button class="btn-aksi delete" onclick="hapusPengeluaran(${item.id})" title="Hapus">
                                <i class="ph ph-trash"></i>
                            </button>` : ''}
                        </div>
                    </td>
                </tr>
            `);
            });

            renderPaginationPengeluaran(res);
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-red-500">Gagal memuat data: ${err.message}. Silakan coba lagi.</td></tr>`;
        });
}

function renderPaginationPengeluaran(meta) {
    const info = document.getElementById('paginationInfoPengeluaran');
    if (info) info.innerText = `Menampilkan ${meta.from ?? 0}–${meta.to ?? 0} dari ${meta.total ?? 0} data`;

    const pageInfo = document.getElementById('pageInfoPengeluaran');
    if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;

    const prev = document.getElementById('prevPagePengeluaran');
    const next = document.getElementById('nextPagePengeluaran');

    if (prev) {
        prev.disabled = (meta.current_page === 1);
        prev.onclick = () => loadPengeluaran(meta.current_page - 1);
    }
    if (next) {
        next.disabled = (meta.current_page === meta.last_page);
        next.onclick = () => loadPengeluaran(meta.current_page + 1);
    }
}

function loadEditData(id) {
    fetch(`/dashboard/pengeluaran/${id}`, { headers: { Accept: 'application/json' } })
        .then(res => res.json())
        .then(data => {
            const idEl = document.getElementById('pengeluaranId');
            if (!idEl) return; // Modal closed or elements missing

            idEl.value = data.id;

            if (data.tanggal) {
                document.getElementById('pengeluaranTanggal').value = data.tanggal.substring(0, 10);
            }

            document.getElementById('pengeluaranUraian').value = data.uraian;
            document.getElementById('pengeluaranKeterangan').value = data.keterangan || '';
            document.getElementById('pengeluaranNominalValue').value = data.nominal;
            document.getElementById('pengeluaranNominalDisplay').value = formatRibuan(data.nominal);

            document.getElementById('pengeluaranPotonganPajakValue').value = data.potongan_pajak || 0;
            document.getElementById('pengeluaranPotonganPajakDisplay').value = formatRibuan(data.potongan_pajak || 0);

            if (window.calculateTotalDibayarkan) window.calculateTotalDibayarkan();

            document.getElementById('pengeluaranMetode').value = data.metode_pembayaran || '';
            document.getElementById('pengeluaranNoSPP').value = data.no_spp || '';
            document.getElementById('pengeluaranNoSPM').value = data.no_spm || '';
            document.getElementById('pengeluaranNoSP2D').value = data.no_sp2d || '';

            // Pilih rekening (nunggu loadRekeningPengeluaran selesai)
            let attempts = 0;
            const check = setInterval(() => {
                const select = document.getElementById('pengeluaranRekening');
                if (!select) {
                    clearInterval(check); // Element gone
                    return;
                }

                if (select.options.length > 1) {
                    select.value = data.kode_rekening_id;
                    clearInterval(check);
                }

                attempts++;
                if (attempts > 50) clearInterval(check); // Stop after 5 seconds
            }, 100);
        })
        .catch(err => {
            console.error(err);
            toast('Gagal memuat data edit', 'error');
        });
}

async function loadRekeningPengeluaran(kategori) {
    const select = document.getElementById('pengeluaranRekening');

    // Selalu reload jika kategori berbeda atau belum dimuat
    if (select.getAttribute('data-loaded-for') === kategori) return;

    try {
        const res = await fetch('/dashboard/master/kode-rekening?category=PENGELUARAN', {
            headers: { 'Accept': 'application/json' }
        });
        const tree = await res.json();

        select.innerHTML = '<option value="">-- Pilih Rekening --</option>';

        function flatten(nodes) {
            nodes.forEach(node => {
                if (node.tipe === 'detail') {
                    // Filter berdasarkan sumber_data yang cocok dengan kategori pengeluaran
                    // Jika kode tersebut di-map ke kategori yang sedang dibuka (atau jika user ingin semua pengeluaran tampil, hapus if ini)
                    if (node.sumber_data === kategori) {
                        select.insertAdjacentHTML('beforeend', `<option value="${node.id}">${node.kode} — ${node.nama}</option>`);
                    }
                }
                if (node.children && node.children.length > 0) {
                    flatten(node.children);
                }
            });
        }

        flatten(tree);
        select.setAttribute('data-loaded-for', kategori);

        // Jika tidak ada yang cocok dengan mapping, tampilkan semua detail pengeluaran sebagai fallback
        if (select.options.length <= 1) {
            function flattenAll(nodes) {
                nodes.forEach(node => {
                    if (node.tipe === 'detail') {
                        select.insertAdjacentHTML('beforeend', `<option value="${node.id}">${node.kode} — ${node.nama}</option>`);
                    }
                    if (node.children && node.children.length > 0) {
                        flattenAll(node.children);
                    }
                });
            }
            flattenAll(tree);
            select.setAttribute('data-loaded-for', 'ALL');
        }

    } catch (err) {
        console.error(err);
    }
}

/* =========================
   SUBMIT & ACTIONS
========================= */
window.submitPengeluaran = async function (event) {
    event.preventDefault();
    const form = document.getElementById('formPengeluaran');
    const btn = document.getElementById('btnSimpanPengeluaran');

    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    const formData = new FormData(form);
    const id = formData.get('id');
    const url = id ? `/dashboard/pengeluaran/${id}` : '/dashboard/pengeluaran';

    if (id) formData.append('_method', 'PUT');

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
            if (err.errors) {
                const firstErrorKey = Object.keys(err.errors)[0];
                throw new Error(err.errors[firstErrorKey][0]);
            }
            throw new Error(err.message || 'Gagal menyimpan data');
        }

        toast('Data berhasil disimpan', 'success');
        closePengeluaranModal();
        loadPengeluaran();
    } catch (err) {
        toast(err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerText = 'Simpan';
    }
};

window.handleSearchPengeluaran = function (e) {
    const val = e.target.value.trim();
    if (window.pengeluaranSearchTimer) clearTimeout(window.pengeluaranSearchTimer);
    window.pengeluaranSearchTimer = setTimeout(() => {
        pengeluaranKeyword = val;
        loadPengeluaran(1);
    }, 400);
};

window.hapusPengeluaran = function (id) {
    openConfirm(
        'Hapus Transaksi',
        'Yakin ingin menghapus data pengeluaran ini? Data yang dihapus tidak dapat dikembalikan.',
        () => {
            fetch(`/dashboard/pengeluaran/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(res => {
                    if (!res.ok) throw new Error();
                    toast('Data berhasil dihapus', 'success');
                    loadPengeluaran();
                })
                .catch(() => toast('Gagal menghapus data', 'error'));
        }
    );
};

/* =========================
   INITIALIZATION
========================= */


window.openPengeluaranDetail = function (id) {
    const modal = document.getElementById('pengeluaranDetailModal');
    const content = document.getElementById('detailPengeluaranContent');
    if (!modal || !content) return;

    content.innerHTML = '<div class="col-span-2 text-center py-4"><i class="ph ph-spinner animate-spin text-2xl"></i></div>';
    modal.classList.add('show');

    fetch(`/dashboard/pengeluaran/${id}`, { headers: { Accept: 'application/json' } })
        .then(res => res.json())
        .then(data => {
            content.innerHTML = `
                <div class="detail-row">
                    <span class="label">Tanggal</span>
                    <span class="value">${formatTanggal(data.tanggal)}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Kategori</span>
                    <span class="value">${data.kategori}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Kode Rekening</span>
                    <span class="value"><code>${data.kode_rekening?.kode ?? '-'}</code></span>
                </div>
                <div class="detail-row">
                    <span class="label">Nama Rekening</span>
                    <span class="value">${data.kode_rekening?.nama ?? '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Uraian</span>
                    <span class="value">${escapeHtml(data.uraian)}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Keterangan</span>
                    <span class="value">${escapeHtml(data.keterangan || '-')}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Metode Pembayaran</span>
                    <span class="value">${data.metode_pembayaran === 'UP' ? 'Uang Persediaan' : (data.metode_pembayaran === 'GU' ? 'Ganti Uang' : (data.metode_pembayaran === 'LS' ? 'Langsung' : '-'))}</span>
                </div>
                <div class="detail-row">
                    <span class="label">No. SPP</span>
                    <span class="value">${data.no_spp ?? '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">No. SPM</span>
                    <span class="value">${data.no_spm ?? '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">No. SP2D</span>
                    <span class="value">${data.no_sp2d ?? '-'}</span>
                </div>
                <div class="detail-total mt-4">
                    <span>Jumlah yang diminta</span>
                    <strong>${formatRupiah(data.nominal)}</strong>
                </div>
                <div class="detail-row" style="margin-top: 12px; border-bottom: none; padding-bottom: 0;">
                    <span class="label">Potongan Pajak</span>
                    <span class="value text-red-500">${formatRupiah(data.potongan_pajak || 0)}</span>
                </div>
                <div class="detail-total mt-2" style="background: #ecfdf5; border-color: #6ee7b7;">
                    <span style="color: #047857;">Total Dibayarkan</span>
                    <strong style="color: #059669;">${formatRupiah(data.total_dibayarkan || 0)}</strong>
                </div>
            `;
        })
        .catch(err => {
            content.innerHTML = '<div class="col-span-2 text-center text-red-500 py-4">Gagal memuat data</div>';
        });
};

window.closeDetailPengeluaran = function () {
    const modal = document.getElementById('pengeluaranDetailModal');
    modal?.classList.remove('show');
};
