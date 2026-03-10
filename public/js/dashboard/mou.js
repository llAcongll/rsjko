let editingMouId = null;
let mouData = [];
let filteredMou = [];
let currentPageMou = 1;
const perPageMou = 10;

/* =========================
   OPEN MODAL (ADD / EDIT)
========================= */
window.openMouForm = function (id = null, kode = '', nama = '') {
    editingMouId = id;

    const titleEl = document.getElementById('mouModalTitle');
    const kodeEl = document.getElementById('mouKode');
    const namaEl = document.getElementById('mouNama');

    titleEl.innerText = id ? 'Ã¢Å“Ã¯¸ Edit MOU' : 'Ã°Å¸¤ Tambah MOU';
    namaEl.value = nama || '';

    if (id) {
        // EDIT Ã¢â€ â€™ kode tetap
        kodeEl.value = kode;
        kodeEl.readOnly = true;
    } else {
        // TAMBAH Ã¢â€ â€™ auto-generate kode
        kodeEl.value = '';
        kodeEl.readOnly = true;

        fetch('/dashboard/mous/next-kode', {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(d => {
                kodeEl.value = d.kode;
            })
            .catch(() => {
                toast('Gagal mengambil kode MOU', 'error');
            });
    }

    const modal = document.getElementById('mouModal');
    modal.classList.add('show');
};

/* =========================
   CLOSE MODAL
========================= */
window.closeMouModal = function () {
    const modal = document.getElementById('mouModal');
    modal.classList.remove('show');
    editingMouId = null;
};

/* =========================
   SUBMIT (CREATE / UPDATE)
========================= */
window.submitMou = function () {
    const kode = document.getElementById('mouKode').value.trim();
    const nama = document.getElementById('mouNama').value.trim();

    if (!kode || !nama) {
        toast('Kode dan nama instansi wajib diisi', 'error');
        return;
    }

    const url = editingMouId
        ? `/dashboard/mous/${editingMouId}`
        : `/dashboard/mous`;

    const method = editingMouId ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ kode, nama })
    })
        .then(async r => {
            const data = await r.json();
            if (!r.ok) throw new Error(data.message || 'Gagal menyimpan MOU');
            return data;
        })
        .then(() => {
            closeMouModal();
            if (typeof window.loadMouTable === 'function') {
                window.loadMouTable();
            }
            toast('MOU berhasil disimpan', 'success');
        })
        .catch(err => toast(err.message, 'error'));
};

/* =========================
   LOAD DATA MOU
========================= */
window.loadMouTable = function () {
    console.log('Ã°Å¸â€¥ loadMouTable DIPANGGIL');

    fetch('/dashboard/mou-list', {
        headers: { 'Accept': 'application/json' }
    })
        .then(r => r.json())
        .then(data => {
            mouData = data;
            filteredMou = [...data];
            currentPageMou = 1;

            renderMouPage();
            updatePaginationInfoMou(filteredMou.length);
            updateSortIconsMou();

            initSearchMou();
            initSortableMou();
            bindPaginationMou();
        });
};

/* =========================
   DELETE MOU (CONFIRM)
========================= */
window.deleteMou = function (id) {
    openConfirm(
        'Hapus MOU',
        'Data MOU yang dihapus tidak dapat dikembalikan.',
        () => {
            fetch(`/dashboard/mous/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                }
            })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok) throw new Error(data.message || 'Gagal menghapus MOU');
                    return data;
                })
                .then(() => {
                    if (typeof window.loadMouTable === 'function') {
                        window.loadMouTable();
                    }
                    toast('MOU berhasil dihapus', 'success');
                })
                .catch(err => toast(err.message, 'error'));
        }
    );
};

window.initMou = function () {
    console.log('initMou jalan');

    if (document.getElementById('mouTable')) {
        loadMouTable();
    }
};

let mouSort = {
    key: null,
    direction: 'asc'
};

function initSortableMou() {
    const table = document.getElementById('mouTable');
    if (!table) return;

    const headers = table.querySelectorAll('th.sortable');

    headers.forEach((th) => {
        th.addEventListener('click', () => {
            const key = th.dataset.sort;

            if (mouSort.key === key) {
                mouSort.direction =
                    mouSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                mouSort.key = key;
                mouSort.direction = 'asc';
            }

            applySortMou();
            currentPageMou = 1;
            renderMouPage();
            updatePaginationInfoMou(filteredMou.length);
            updateSortIconsMou();
        });
    });
}

function updateSortIconsMou() {
    document.querySelectorAll('#mouTable th.sortable i').forEach(i => {
        i.className = 'ph ph-caret-up-down text-slate-400';
    });
    const activeHeader = document.querySelector(`#mouTable th.sortable[data-sort="${mouSort.key}"]`);
    if (activeHeader) {
        const i = activeHeader.querySelector('i');
        if (i) {
            i.className = mouSort.direction === 'asc' ? 'ph ph-caret-up text-blue-600' : 'ph ph-caret-down text-blue-600';
        }
    }
}

function applySortMou() {
    if (!mouSort.key) return;

    filteredMou.sort((a, b) => {
        let A, B;

        if (mouSort.key === 'id') {
            A = Number(a.id);
            B = Number(b.id);
        } else {
            A = String(a[mouSort.key]).toLowerCase();
            B = String(b[mouSort.key]).toLowerCase();
        }

        if (A < B) return mouSort.direction === 'asc' ? -1 : 1;
        if (A > B) return mouSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
}

function initSearchMou() {
    const input = document.getElementById('mouSearch');
    if (!input) return;

    if (input.dataset.bound === '1') return;
    input.dataset.bound = '1';

    input.addEventListener('input', () => {
        const keyword = input.value.toLowerCase().trim();

        filteredMou = mouData.filter(r =>
            r.kode.toLowerCase().includes(keyword) ||
            r.nama.toLowerCase().includes(keyword)
        );

        applySortMou();
        currentPageMou = 1;
        renderMouPage();
        updatePaginationInfoMou(filteredMou.length);
        updateSortIconsMou();
    });
}

function renderMouPage() {
    const tbody = document.querySelector('#mouTable tbody');
    if (!tbody) return;

    const start = (currentPageMou - 1) * perPageMou;
    const end = start + perPageMou;

    const pageData = filteredMou.slice(start, end);

    tbody.innerHTML = '';

    const canCRUD = window.hasPermission('MASTER_MANAGE');

    pageData.forEach((r, i) => {
        const escapedNama = r.nama.replace(/'/g, "\\'");
        tbody.innerHTML += `
      <tr>
        <td class="text-center">${start + i + 1}</td>
        <td>${r.kode}</td>
        <td>${r.nama}</td>
        <td>
          <div class="flex justify-center gap-2">
            ${canCRUD ? `
              <button class="btn-aksi edit" 
                onclick="openMouForm(${r.id}, '${r.kode}', '${escapedNama}')" title="Edit MOU">
                <i class="ph ph-pencil"></i>
              </button>
              <button class="btn-aksi delete" 
                onclick="deleteMou(${r.id})" title="Hapus MOU">
                <i class="ph ph-trash"></i>
              </button>
            ` : '-'}
          </div>
        </td>
      </tr>
    `;
    });
}

function bindPaginationMou() {
    const prevBtn = document.getElementById('prevPageMou');
    const nextBtn = document.getElementById('nextPageMou');

    if (!prevBtn || !nextBtn) return;

    prevBtn.onclick = () => {
        if (currentPageMou > 1) {
            currentPageMou--;
            renderMouPage();
            updatePaginationInfoMou(filteredMou.length);
        }
    };

    nextBtn.onclick = () => {
        const totalPage = Math.ceil(filteredMou.length / perPageMou);
        if (currentPageMou < totalPage) {
            currentPageMou++;
            renderMouPage();
            updatePaginationInfoMou(filteredMou.length);
        }
    };
}

function updatePaginationInfoMou(total) {
    const pageInfo = document.getElementById('pageInfoMou');
    const mouInfo = document.getElementById('mouInfo');
    const prevBtn = document.getElementById('prevPageMou');
    const nextBtn = document.getElementById('nextPageMou');

    if (!pageInfo || !mouInfo || !prevBtn || !nextBtn) return;

    const totalPage = Math.max(1, Math.ceil(total / perPageMou));

    const start = (currentPageMou - 1) * perPageMou + 1;
    const end = Math.min(currentPageMou * perPageMou, total);

    pageInfo.innerText = `Halaman ${currentPageMou} / ${totalPage}`;
    mouInfo.innerText = total > 0
        ? `Menampilkan ${start}-${end} dari ${total} data`
        : 'Tidak ada data';

    prevBtn.disabled = currentPageMou === 1;
    nextBtn.disabled = currentPageMou === totalPage;
}




