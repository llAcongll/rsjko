let editingPtId = null;
let penandaTanganData = [];
let filteredPt = [];
let currentPtPage = 1;
const ptPerPage = 10;

/* =========================
   OPEN MODAL (ADD / EDIT)
========================= */
window.openPenandaTanganForm = function (id = null, jabatan = '', pangkat = '', nama = '', nip = '') {
    editingPtId = id;

    const titleEl = document.getElementById('penandaTanganModalTitle');
    const descEl = document.getElementById('penandaTanganModalDesc');
    const jabatanEl = document.getElementById('ptJabatan');
    const pangkatEl = document.getElementById('ptPangkat');
    const namaEl = document.getElementById('ptNama');
    const nipEl = document.getElementById('ptNip');

    titleEl.innerHTML = id ? '<i class="ph ph-pencil"></i> Edit Penanda Tangan' : '<i class="ph ph-signature"></i> Tambah Penanda Tangan';
    descEl.innerText = id ? 'Perbarui detail data penanda tangan' : 'Lengkapi detail penanda tangan laporan';

    jabatanEl.value = jabatan || '';
    pangkatEl.value = pangkat || '';
    namaEl.value = nama || '';
    nipEl.value = nip || '';

    const modal = document.getElementById('penandaTanganModal');
    modal.classList.add('show');
};

/* =========================
   CLOSE MODAL
========================= */
window.closePenandaTanganForm = function () {
    const modal = document.getElementById('penandaTanganModal');
    modal.classList.remove('show');
    editingPtId = null;
    document.getElementById('penandaTanganForm').reset();
};

/* =========================
   SUBMIT (CREATE / UPDATE)
========================= */
window.savePenandaTangan = function (e) {
    e.preventDefault();

    const jabatan = document.getElementById('ptJabatan').value.trim();
    const pangkat = document.getElementById('ptPangkat').value.trim();
    const nama = document.getElementById('ptNama').value.trim();
    const nip = document.getElementById('ptNip').value.trim();

    if (!jabatan || !nama) {
        toast('Jabatan dan Nama wajib diisi', 'error');
        return;
    }

    const url = editingPtId
        ? `/dashboard/penanda-tangans/${editingPtId}`
        : `/dashboard/penanda-tangans`;

    const method = editingPtId ? 'PUT' : 'POST';

    const btn = document.getElementById('btnSavePenandaTangan');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Menyimpan...';

    fetch(url, {
        method,
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ jabatan, pangkat, nama, nip })
    })
        .then(async r => {
            const data = await r.json();
            if (!r.ok) throw new Error(data.message || 'Gagal menyimpan data');
            return data;
        })
        .then(() => {
            closePenandaTanganForm();
            loadPenandaTanganTable();
            toast('Data penanda tangan berhasil disimpan', 'success');
        })
        .catch(err => {
            toast(err.message, 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        });
};

/* =========================
   LOAD DATA
========================= */
window.loadPenandaTanganTable = function () {
    fetch('/dashboard/penanda-tangan-list', {
        headers: { 'Accept': 'application/json' }
    })
        .then(r => r.json())
        .then(data => {
            penandaTanganData = data;
            filteredPt = [...data];
            currentPtPage = 1;

            renderPtPage();
            updatePtPaginationInfo(filteredPt.length);
            initSearchPt();
        })
        .catch(() => {
            toast('Gagal memuat data penanda tangan', 'error');
        });
};

/* =========================
   DELETE (CONFIRM)
========================= */
window.deletePenandaTangan = function (id) {
    openConfirm(
        'Hapus Penanda Tangan',
        'Data yang dihapus tidak dapat dikembalikan.',
        () => {
            fetch(`/dashboard/penanda-tangans/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                }
            })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok) throw new Error(data.message || 'Gagal menghapus data');
                    return data;
                })
                .then(() => {
                    loadPenandaTanganTable();
                    toast('Data berhasil dihapus', 'success');
                })
                .catch(err => toast(err.message, 'error'));
        }
    );
};

/* =========================
   SEARCH
========================= */
function initSearchPt() {
    const input = document.getElementById('penandaTanganSearch');
    if (!input) return;

    if (input.dataset.bound === '1') return;
    input.dataset.bound = '1';

    input.addEventListener('input', () => {
        const keyword = input.value.toLowerCase().trim();

        filteredPt = penandaTanganData.filter(d =>
            (d.jabatan && d.jabatan.toLowerCase().includes(keyword)) ||
            (d.pangkat && d.pangkat.toLowerCase().includes(keyword)) ||
            (d.nama && d.nama.toLowerCase().includes(keyword)) ||
            (d.nip && d.nip.toLowerCase().includes(keyword))
        );

        currentPtPage = 1;
        renderPtPage();
        updatePtPaginationInfo(filteredPt.length);
    });
}

/* =========================
   RENDER TABLE
========================= */
function renderPtPage() {
    const tbody = document.querySelector('#penandaTanganTable tbody');
    if (!tbody) return;

    const start = (currentPtPage - 1) * ptPerPage;
    const end = start + ptPerPage;
    const pageData = filteredPt.slice(start, end);

    tbody.innerHTML = '';

    const canCRUD = window.hasPermission('MASTER_CRUD') || window.isAdmin;

    if (pageData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding: 24px; color: #64748b;">Tidak ada data penanda tangan</td></tr>';
        return;
    }

    pageData.forEach((d, i) => {
        tbody.innerHTML += `
      <tr>
        <td class="text-center">${start + i + 1}</td>
        <td>${d.jabatan}</td>
        <td class="text-center">${d.pangkat || '-'}</td>
        <td>${d.nama}</td>
        <td class="text-center">${d.nip || '-'}</td>
        <td>
          <div class="flex justify-center gap-2">
            ${canCRUD ? `
              <button class="btn-aksi edit" 
                onclick="openPenandaTanganForm(${d.id}, '${d.jabatan}', '${d.pangkat || ''}', '${d.nama}', '${d.nip || ''}')" title="Edit Data">
                <i class="ph ph-pencil"></i>
              </button>
              <button class="btn-aksi delete" 
                onclick="deletePenandaTangan(${d.id})" title="Hapus Data">
                <i class="ph ph-trash"></i>
              </button>
            ` : '-'}
          </div>
        </td>
      </tr>
    `;
    });
}

/* =========================
   PAGINATION
========================= */
function updatePtPaginationInfo(total) {
    const pageInfo = document.getElementById('pageInfoText');
    const infoText = document.getElementById('penandaTanganInfo');
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');

    if (!pageInfo || !infoText || !prevBtn || !nextBtn) return;

    const totalPage = Math.max(1, Math.ceil(total / ptPerPage));
    const start = (currentPtPage - 1) * ptPerPage + 1;
    const end = Math.min(currentPtPage * ptPerPage, total);

    pageInfo.innerText = `Halaman ${currentPtPage} / ${totalPage}`;
    infoText.innerText = total > 0 ? `Menampilkan ${start}–${end} dari ${total} data` : 'Menampilkan 0 data';

    prevBtn.disabled = currentPtPage === 1;
    nextBtn.disabled = currentPtPage === totalPage;

    prevBtn.onclick = () => {
        if (currentPtPage > 1) {
            currentPtPage--;
            renderPtPage();
            updatePtPaginationInfo(total);
        }
    };

    nextBtn.onclick = () => {
        if (currentPtPage < totalPage) {
            currentPtPage++;
            renderPtPage();
            updatePtPaginationInfo(total);
        }
    };
}

window.initPenandaTangan = function () {
    if (document.getElementById('penandaTanganTable')) {
        loadPenandaTanganTable();
    }
};
