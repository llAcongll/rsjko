let penyesuaianPage = 1;
let penyesuaianPerPage = 10;
let penyesuaianKeyword = '';
let penyesuaianBatchKategori = '';
let isEditPenyesuaian = false;
let editPenyesuaianId = null;

window.initPenyesuaian = function () {
    if (!document.getElementById('penyesuaianTable')) return;

    loadPenyesuaian();

    document.getElementById('searchPenyesuaian')?.addEventListener('input', debounce((e) => {
        penyesuaianKeyword = e.target.value;
        penyesuaianPage = 1;
        loadPenyesuaian();
    }, 500));

    document.getElementById('filterKategoriPenyesuaian')?.addEventListener('change', (e) => {
        penyesuaianBatchKategori = e.target.value;
        penyesuaianPage = 1;
        loadPenyesuaian();
    });

    document.getElementById('btnTambahPenyesuaian')?.addEventListener('click', () => {
        isEditPenyesuaian = false;
        editPenyesuaianId = null;
        openPenyesuaianModal();
    });

    document.getElementById('prevPagePenyesuaian')?.addEventListener('click', () => {
        if (penyesuaianPage > 1) {
            penyesuaianPage--;
            loadPenyesuaian();
        }
    });

    document.getElementById('nextPagePenyesuaian')?.addEventListener('click', () => {
        penyesuaianPage++;
        loadPenyesuaian();
    });

    document.getElementById('penyesuaianForm')?.addEventListener('submit', submitPenyesuaian);
};

async function loadPenyesuaian() {
    const tbody = document.getElementById('penyesuaianBody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="8" class="text-center"><i class="ph ph-spinner animate-spin"></i> Memuat data...</td></tr>`;

    try {
        const res = await fetch(`/dashboard/penyesuaian?page=${penyesuaianPage}&per_page=${penyesuaianPerPage}&search=${penyesuaianKeyword}&kategori=${penyesuaianBatchKategori}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        renderPenyesuaianTable(data.data, data.from);
        updatePaginationPenyesuaian(data);
        updateSummaryPenyesuaian(data.aggregates);
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-red-500">Error: ${err.message}</td></tr>`;
    }
}

function updateSummaryPenyesuaian(agg) {
    if (!agg) return;
    const potEl = document.getElementById('summaryTotalPotonganPenyesuaian');
    if (potEl) potEl.innerText = formatRupiah(agg.total_potongan || 0);

    const admEl = document.getElementById('summaryTotalAdmPenyesuaian');
    if (admEl) admEl.innerText = formatRupiah(agg.total_adm_bank || 0);
}

function renderPenyesuaianTable(items, from) {
    const tbody = document.getElementById('penyesuaianBody');
    tbody.innerHTML = '';

    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-gray-500">Tidak ada data ditemukan</td></tr>`;
        return;
    }

    items.forEach((item, index) => {
        const tr = document.createElement('tr');
        const subKategori = item.sub_kategori ? ` <small class="text-slate-500">(${item.sub_kategori})</small>` : '';
        tr.innerHTML = `
            <td class="text-center">${from + index}</td>
            <td class="text-center">${formatDateIndo(item.tanggal)}</td>
            <td>
                <span class="badge ${item.kategori === 'BPJS' ? 'badge-primary' : 'badge-info'}">${item.kategori}</span>
                ${subKategori}
            </td>
            <td>${item.perusahaan ? item.perusahaan.nama : '-'}</td>
            <td class="text-red-600">${formatRupiahTable(item.potongan)}</td>
            <td class="text-red-600">${formatRupiahTable(item.administrasi_bank)}</td>
            <td class="text-sm font-medium text-slate-600">${item.keterangan || '-'}</td>
            <td class="text-center">
                <div class="flex justify-center gap-2">
                    <button class="btn-aksi detail" onclick="detailPenyesuaian(${item.id})" title="Detail">
                        <i class="ph ph-eye"></i>
                    </button>
                    ${hasPermission('PENYESUAIAN_CRUD') ? `
                    <button class="btn-aksi edit" onclick="editPenyesuaian(${item.id})" title="Edit">
                        <i class="ph ph-pencil-simple"></i>
                    </button>
                    <button class="btn-aksi delete" onclick="deletePenyesuaian(${item.id})" title="Hapus">
                        <i class="ph ph-trash"></i>
                    </button>
                    ` : ''}
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function updatePaginationPenyesuaian(data) {
    document.getElementById('prevPagePenyesuaian').disabled = data.current_page === 1;
    document.getElementById('nextPagePenyesuaian').disabled = data.current_page === data.last_page;
    document.getElementById('pageInfoPenyesuaian').innerText = `${data.current_page} / ${data.last_page}`;
    document.getElementById('paginationInfoPenyesuaian').innerText = `Menampilkan ${data.from || 0}–${data.to || 0} dari ${data.total} data`;
}

window.openPenyesuaianModal = function () {
    document.getElementById('penyesuaianModal').classList.add('show');
    if (!isEditPenyesuaian) {
        document.getElementById('penyesuaianForm').reset();
        document.getElementById('penyesuaianId').value = '';
        document.getElementById('penyesuaianModalTitle').innerHTML = '<i class="ph ph-plus-circle"></i> Tambah Penyesuaian';
        handleKategoriChange();
    }
}

window.handleKategoriChange = function () {
    const kategori = document.getElementById('penyesuaianKategori').value;
    const groupSub = document.getElementById('groupSubKategori');
    const subSelect = document.getElementById('penyesuaianSubKategori');

    if (kategori === 'BPJS') {
        groupSub.style.display = 'block';
        subSelect.required = true;
    } else {
        groupSub.style.display = 'none';
        subSelect.value = '';
        subSelect.required = false;
    }
    loadPerusahaanByKategori();
}

window.closePenyesuaianModal = function () {
    document.getElementById('penyesuaianModal').classList.remove('show');
}

window.loadPerusahaanByKategori = async function () {
    const kategori = document.getElementById('penyesuaianKategori').value;
    const select = document.getElementById('penyesuaianPerusahaanId');
    if (!select) return;

    select.disabled = true;
    select.innerHTML = '<option value="">Memuat perusahaan...</option>';

    try {
        const res = await fetch('/dashboard/perusahaan-list');
        const allPerusahaan = await res.json();

        select.innerHTML = '<option value="">Pilih Perusahaan...</option>';
        allPerusahaan.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.nama;
            select.appendChild(opt);
        });

        // AUTO SELECT BPJS (ID 3) if kategori is BPJS
        if (kategori === 'BPJS') {
            select.value = "3";
        }

        select.disabled = false;
    } catch (err) {
        select.innerHTML = '<option value="">Gagal memuat data</option>';
    }
}

async function submitPenyesuaian(e) {
    e.preventDefault();
    const id = document.getElementById('penyesuaianId').value;
    const method = id ? 'PUT' : 'POST';
    const url = id ? `/dashboard/penyesuaian/${id}` : '/dashboard/penyesuaian';

    const data = {
        tanggal: document.getElementById('penyesuaianTanggal').value,
        kategori: document.getElementById('penyesuaianKategori').value,
        sub_kategori: document.getElementById('penyesuaianSubKategori').value,
        perusahaan_id: document.getElementById('penyesuaianPerusahaanId').value,
        potongan: document.getElementById('penyesuaianPotongan').value || 0,
        administrasi_bank: document.getElementById('penyesuaianAdm').value || 0,
        keterangan: document.getElementById('penyesuaianKeterangan').value,
        _token: csrfToken()
    };

    try {
        const res = await fetch(url, {
            method: id ? 'POST' : 'POST', // Use POST for both, let Laravel handle PUT via _method
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(id ? { ...data, _method: 'PUT' } : data)
        });

        if (!res.ok) {
            const err = await res.json();
            throw new Error(err.message || 'Gagal menyimpan data');
        }

        toast('Data berhasil disimpan', 'success');
        closePenyesuaianModal();
        loadPenyesuaian();
    } catch (err) {
        toast(err.message, 'error');
    }
}

window.editPenyesuaian = async function (id) {
    try {
        const res = await fetch(`/dashboard/penyesuaian`); // Fetching all and find for now, or implement show
        const dataArr = await (await fetch(`/dashboard/penyesuaian?per_page=100`)).json();
        const item = dataArr.data.find(i => i.id === id);

        if (!item) throw new Error('Data tidak ditemukan');

        isEditPenyesuaian = true;
        editPenyesuaianId = id;

        openPenyesuaianModal();
        document.getElementById('penyesuaianModalTitle').innerHTML = '<i class="ph ph-pencil-simple"></i> Edit Penyesuaian';

        document.getElementById('penyesuaianId').value = item.id;
        document.getElementById('penyesuaianTanggal').value = item.tanggal ? item.tanggal.substring(0, 10) : '';
        document.getElementById('penyesuaianKategori').value = item.kategori;
        handleKategoriChange();
        document.getElementById('penyesuaianSubKategori').value = item.sub_kategori || '';

        await loadPerusahaanByKategori();
        document.getElementById('penyesuaianPerusahaanId').value = item.perusahaan_id;

        document.getElementById('penyesuaianPotongan').value = item.potongan;
        document.getElementById('penyesuaianAdm').value = item.administrasi_bank;
        document.getElementById('penyesuaianKeterangan').value = item.keterangan || '';

    } catch (err) {
        toast(err.message, 'error');
    }
}

window.deletePenyesuaian = function (id) {
    openConfirm(
        'Hapus Penyesuaian',
        'Data potongan ini akan dihapus permanen. Lanjutkan?',
        async () => {
            try {
                const res = await fetch(`/dashboard/penyesuaian/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken() }
                });
                if (!res.ok) throw new Error('Gagal menghapus data');
                toast('Data berhasil dihapus', 'success');
                loadPenyesuaian();
            } catch (err) {
                toast(err.message, 'error');
            }
        }
    );
}

// =========================
// DETAIL / PREVIEW
// =========================
window.detailPenyesuaian = async function (id) {
    const modal = document.getElementById('previewModal');
    const content = document.getElementById('previewContent');
    const title = document.getElementById('previewTitle');

    if (!modal || !content) return;

    title.innerHTML = '<i class="ph ph-scissors"></i> Detail Penyesuaian';
    content.innerHTML = '<div class="text-center py-8"><i class="ph ph-spinner animate-spin text-2xl"></i></div>';
    modal.classList.add('show');

    try {
        const resArr = await fetch(`/dashboard/penyesuaian?per_page=100`);
        const dataArr = await resArr.json();
        const item = dataArr.data.find(i => i.id === id);

        if (!item) throw new Error('Data tidak ditemukan');

        content.innerHTML = `
            <div class="space-y-6">
                <!-- Header Section -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                            <i class="ph-duotone ph-scissors text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Perusahaan / Debitur</h4>
                            <h3 class="text-lg font-bold text-slate-800 leading-tight">${item.perusahaan ? item.perusahaan.nama : 'Tanpa Perusahaan'}</h3>
                        </div>
                    </div>
                    <div class="badge ${item.kategori === 'BPJS' ? 'badge-primary' : 'badge-info'} px-3 py-2">
                        ${item.kategori} ${item.sub_kategori ? `(${item.sub_kategori})` : ''}
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-2 gap-y-4 gap-x-6 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Tanggal Transaksi</span>
                        <div class="flex items-center gap-2 text-slate-700">
                            <i class="ph ph-calendar-blank text-slate-400"></i>
                            <span class="font-medium">${formatDateIndo(item.tanggal)}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Kategori Potongan</span>
                        <div class="flex items-center gap-2 text-slate-700">
                            <i class="ph ph-tag-simple text-slate-400"></i>
                            <span class="font-medium">${item.kategori}</span>
                        </div>
                    </div>
                </div>

                <!-- Financial Calculation -->
                <div class="bg-white border-2 border-slate-100 rounded-2xl p-5 shadow-sm space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 font-medium">Potongan Jasa (70:30)</span>
                        <span class="font-bold text-slate-700">${formatRupiah(item.potongan)}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 font-medium">Adm Bank (100% RS)</span>
                        <span class="font-bold text-slate-700">${formatRupiah(item.administrasi_bank)}</span>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-900 uppercase tracking-wide">Total Pengurang</span>
                        <span class="text-xl font-black text-red-600">${formatRupiah(parseFloat(item.potongan) + parseFloat(item.administrasi_bank))}</span>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="relative">
                    <span class="text-xs font-semibold text-slate-400 block mb-2 px-1">Keterangan</span>
                    <div class="p-4 bg-slate-light border-l-4 border-slate-200 rounded-r-xl text-sm text-slate-600 leading-relaxed italic">
                        "${item.keterangan || 'Tidak ada keterangan tambahan untuk penyesuaian ini.'}"
                    </div>
                </div>
            </div>
        `;

    } catch (err) {
        content.innerHTML = `<div class="text-center text-red-500 py-4">${err.message}</div>`;
    }
}
