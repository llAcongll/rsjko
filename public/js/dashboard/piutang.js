let piutangPage = 1;
let piutangPerPage = 10;
let piutangKeyword = '';
let isEditPiutang = false;
let editPiutangId = null;

// Global Cache for Perusahaan
window._cachePerusahaan = window._cachePerusahaan || null;

window.initPiutang = function () {
    // Only run if Piutang table exists
    if (!document.getElementById('piutangTable')) return;

    // Reset state if needed (optional, but good for fresh view)
    // piutangPage = 1; 

    loadPiutang();

    // Toolbar Events
    document.getElementById('searchPiutang')?.addEventListener('input', debounce((e) => {
        piutangKeyword = e.target.value;
        piutangPage = 1;
        loadPiutang();
    }, 500));

    document.getElementById('btnTambahPiutang')?.addEventListener('click', () => {
        isEditPiutang = false;
        editPiutangId = null;
        openPiutangModal();
    });

    // Pagination
    document.getElementById('prevPagePiutang')?.addEventListener('click', () => {
        if (piutangPage > 1) {
            piutangPage--;
            loadPiutang();
        }
    });

    document.getElementById('nextPagePiutang')?.addEventListener('click', () => {
        piutangPage++;
        loadPiutang();
    });

    // Nominal Formatter in Modal
    document.querySelectorAll('.nominal-display-piutang').forEach(input => {
        input.addEventListener('input', function (e) {
            const hidden = e.target.parentElement.querySelector('.nominal-value-piutang');
            if (hidden) hidden.value = parseAngka(e.target.value);
        });
        input.addEventListener('blur', function (e) {
            let val = parseAngka(e.target.value);
            e.target.value = formatRibuan(val);
        });
        input.addEventListener('focus', function (e) {
            let val = parseAngka(e.target.value);
            e.target.value = val === 0 ? '' : val.toString().replace('.', ',');
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initPiutang();
});

async function loadPiutang() {
    const tableBody = document.getElementById('piutangBody');
    if (!tableBody) return;

    tableBody.innerHTML = `<tr><td colspan="9" class="text-center"><i class="ph ph-spinner animate-spin"></i> Memuat data...</td></tr>`;

    try {
        const res = await fetch(`/dashboard/piutang?page=${piutangPage}&per_page=${piutangPerPage}&search=${piutangKeyword}`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) throw new Error('Gagal memuat data');
        const data = await res.json();

        renderPiutangTable(data.data, data.from);
        updatePaginationPiutang(data);
        updateSummaryPiutang(data.aggregates);

    } catch (err) {
        tableBody.innerHTML = `<tr><td colspan="9" class="text-center text-red-500">Error: ${err.message}</td></tr>`;
    }
}

function renderPiutangTable(items, from) {
    const tbody = document.getElementById('piutangBody');
    tbody.innerHTML = '';

    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-gray-500">Tidak ada data ditemukan</td></tr>`;
        return;
    }

    items.forEach((item, index) => {
        const tr = document.createElement('tr');
        const statusClass = item.status === 'LUNAS' ? 'badge-success' : 'badge-warning';
        const perusahaanName = item.perusahaan ? item.perusahaan.nama : '-';

        tr.innerHTML = `
            <td class="text-center">${from + index}</td>
            <td class="text-center">${formatDateIndo(item.tanggal)}</td>
            <td>
                <strong>${perusahaanName}</strong>
                <div class="text-xs text-gray-500">${item.keterangan || '-'}</div>
            </td>
            <td>${item.bulan_pelayanan}</td>
            <td class="font-medium">${formatRupiahTable(item.jumlah_piutang)}</td>
            <td class="text-center">
                <span class="badge ${statusClass}">${item.status.replace('_', ' ')}</span>
            </td>
            <td class="text-center">
                <div class="flex justify-center gap-2">
                    <button class="btn-aksi detail" onclick="detailPiutang(${item.id})" title="Lihat Detail">
                        <i class="ph ph-eye"></i>
                    </button>
                    ${hasPermission('PIUTANG_CRUD') ? `
                    <button class="btn-aksi edit" onclick="editPiutang(${item.id})" title="Edit Data">
                        <i class="ph ph-pencil-simple"></i>
                    </button>
                    <button class="btn-aksi delete" onclick="deletePiutang(${item.id})" title="Hapus Data">
                        <i class="ph ph-trash"></i>
                    </button>
                    ` : ''}
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function updatePaginationPiutang(data) {
    const prevBtn = document.getElementById('prevPagePiutang');
    const nextBtn = document.getElementById('nextPagePiutang');
    const pageInfo = document.getElementById('pageInfoPiutang');

    if (prevBtn) prevBtn.disabled = data.current_page === 1;
    if (nextBtn) nextBtn.disabled = data.current_page === data.last_page;
    if (pageInfo) pageInfo.innerText = `${data.current_page} / ${data.last_page}`;

    // Note: ID in View might need check, using generic ID
    const info = document.getElementById('paginationInfoPiutang');
    if (info) info.innerText = `Menampilkan ${data.from || 0}–${data.to || 0} dari ${data.total} data`;
}

function updateSummaryPiutang(agg) {
    if (!agg) return;
    const el = document.getElementById('summaryTotalPiutang');
    if (el) el.innerText = formatRupiah(agg.total_piutang);

    const potEl = document.getElementById('summaryTotalPotongan');
    if (potEl) potEl.innerText = formatRupiah(agg.total_potongan || 0);

    const admEl = document.getElementById('summaryTotalAdm');
    if (admEl) admEl.innerText = formatRupiah(agg.total_adm_bank || 0);
}

// ================= MODAL LOGIC =================

async function openPiutangModal() {
    const modal = document.getElementById('modalPiutang');
    if (!modal) return;

    modal.classList.add('show');
    await loadPerusahaanDropdown();

    if (!isEditPiutang) {
        const form = document.getElementById('formPiutang');
        if (form) form.reset();
        document.querySelectorAll('.nominal-display-piutang').forEach(i => i.value = '');
        document.querySelectorAll('.nominal-value-piutang').forEach(i => i.value = 0);
        document.querySelector('#modalPiutang .modal-title').innerText = 'Catat Piutang Baru';
    }
}

function closePiutangModal() {
    document.getElementById('modalPiutang')?.classList.remove('show');
}

async function loadPerusahaanDropdown() {
    const select = document.getElementById('piutangPerusahaanSelect');
    if (!select) return;

    if (window._cachePerusahaan) {
        populateSelect(select, window._cachePerusahaan);
        return;
    }

    try {
        const res = await fetch('/dashboard/perusahaan-list');
        const data = await res.json();
        window._cachePerusahaan = data;
        populateSelect(select, data);
    } catch (err) {
        console.error('Gagal load perusahaan:', err);
    }
}

function populateSelect(select, data) {
    // Keep first option
    const first = select.firstElementChild;
    select.innerHTML = '';
    select.appendChild(first);

    data.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.nama;
        select.appendChild(opt);
    });
}



window.submitPiutang = async function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    if (isEditPiutang) formData.append('_method', 'PUT');

    const url = isEditPiutang ? `/dashboard/piutang/${editPiutangId}` : '/dashboard/piutang';

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!res.ok) throw new Error('Gagal menyimpan data');

        toast('Data berhasil disimpan', 'success');
        closePiutangModal();
        loadPiutang();

    } catch (err) {
        toast(err.message, 'error');
    }
};

window.editPiutang = async function (id) {
    try {
        const res = await fetch(`/dashboard/piutang/${id}`);
        const data = await res.json();

        isEditPiutang = true;
        editPiutangId = id;

        await openPiutangModal();
        document.querySelector('#modalPiutang .modal-title').innerText = 'Edit Data Piutang';

        // Populate Form
        const form = document.getElementById('formPiutang');
        form.querySelector('[name="tanggal"]').value = data.tanggal ? data.tanggal.substring(0, 10) : '';
        form.querySelector('[name="perusahaan_id"]').value = data.perusahaan_id;
        form.querySelector('[name="bulan_pelayanan"]').value = data.bulan_pelayanan;
        form.querySelector('[name="status"]').value = data.status;
        form.querySelector('[name="keterangan"]').value = data.keterangan || '';

        // Nominals
        setNominalValue(form, 'jumlah_piutang', data.jumlah_piutang);
    } catch (err) {
        toast('Gagal memuat data edit', 'error');
    }
};

function setNominalValue(form, name, val) {
    const hidden = form.querySelector(`[name="${name}"]`);
    if (hidden) hidden.value = val;

    // Find sibling display
    const display = hidden.parentElement.querySelector('.nominal-display-piutang');
    if (display) display.value = formatRibuan(val);
}

window.deletePiutang = function (id) {
    if (typeof window.openConfirm !== 'function') {
        if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;
        proceedDelete(id);
        return;
    }

    openConfirm(
        'Hapus Data Piutang',
        'Apakah Anda yakin ingin menghapus data ini? Data yang dihapus tidak dapat dikembalikan.',
        () => proceedDelete(id)
    );
};

function proceedDelete(id) {
    fetch(`/dashboard/piutang/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(res => {
        if (res.ok) {
            toast('Data berhasil dihapus', 'success');
            loadPiutang();
        } else {
            toast('Gagal menghapus data', 'error');
        }
    }).catch(err => {
        toast('Terjadi kesalahan koneksi', 'error');
    });
}

// =========================
// DETAIL MODAL
// =========================
window.detailPiutang = function (id) {
    const modal = document.getElementById('piutangDetailModal');
    const content = document.getElementById('detailPiutangContent');

    if (!modal || !content) return;
    modal.classList.add('show');

    // Reset content
    content.innerHTML = `
        <div class="flex flex-col items-center justify-center py-8 text-slate-500">
            <i class="ph ph-spinner animate-spin text-3xl mb-2"></i>
            <p>Memuat detail...</p>
        </div>
    `;

    fetch(`/dashboard/piutang/${id}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat detail');
            return res.json();
        })
        .then(data => {
            const statusClass = data.status === 'LUNAS' ? 'badge-success' : 'badge-danger';
            const statusIcon = data.status === 'LUNAS' ? 'ph-check-circle' : 'ph-clock-countdown';
            const perusahaanName = data.perusahaan ? data.perusahaan.nama : 'Tanpa Perusahaan';

            content.innerHTML = `
                <div class="space-y-6">
                    <!-- Header Section -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="ph-duotone ph-invoice text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Perusahaan / Debitur</h4>
                                <h3 class="text-lg font-bold text-slate-800 leading-tight">${perusahaanName}</h3>
                            </div>
                        </div>
                        <div class="badge ${statusClass} flex items-center gap-1.5 px-3 py-2">
                            <i class="ph ${statusIcon}"></i>
                            <span>${data.status.replace('_', ' ')}</span>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 gap-y-4 gap-x-6 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <span class="text-xs font-semibold text-slate-400 block mb-1">Tanggal Pencatatan</span>
                            <div class="flex items-center gap-2 text-slate-700">
                                <i class="ph ph-calendar-blank text-slate-400"></i>
                                <span class="font-medium">${formatDateIndo(data.tanggal)}</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-400 block mb-1">Bulan Pelayanan</span>
                            <div class="flex items-center gap-2 text-slate-700">
                                <i class="ph ph-calendar-check text-slate-400"></i>
                                <span class="font-medium">${data.bulan_pelayanan}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="bg-white border-2 border-slate-100 rounded-2xl p-5 shadow-sm">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-bold text-slate-500 uppercase tracking-wide">Total Tagihan</span>
                            <span class="text-xs text-slate-400">Bruto</span>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-slate-900">${formatRupiah(data.jumlah_piutang)}</span>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="relative">
                        <span class="text-xs font-semibold text-slate-400 block mb-2 px-1">Keterangan</span>
                        <div class="p-4 bg-slate-light border-l-4 border-slate-200 rounded-r-xl text-sm text-slate-600 leading-relaxed italic">
                            "${data.keterangan || 'Tidak ada keterangan tambahan untuk catatan ini.'}"
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            content.innerHTML = `
            <div class="text-center py-8 text-red-500">
                <i class="ph ph-warning-circle text-3xl mb-2"></i>
                <p>Gagal memuat detail data.</p>
            </div>
        `;
        });
};

window.closeDetailPiutang = function () {
    document.getElementById('piutangDetailModal')?.classList.remove('show');
};


