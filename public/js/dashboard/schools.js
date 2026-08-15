let editingSchoolId = null;
let schoolData = [];
let filteredSchool = [];
let currentSchoolPage = 1;
const schoolPerPage = 10;

/* =========================
   OPEN MODAL ADD / EDIT
========================= */
window.openSchoolForm = function (
  id = null,
  code = '',
  name = '',
  isActive = true
) {
  editingSchoolId = id;

  const titleEl = document.getElementById('schoolModalTitle');
  const codeEl = document.getElementById('schoolCode');
  const nameEl = document.getElementById('schoolName');
  const activeEl = document.getElementById('schoolActive');

  if (!titleEl || !codeEl || !nameEl || !activeEl) return;

  titleEl.innerText = id ? 'Edit Sekolah' : 'Tambah Sekolah';

  codeEl.value = code || '';
  nameEl.value = name || '';
  activeEl.checked = isActive !== false;

  codeEl.readOnly = false;

  const modal = document.getElementById('schoolModal');

  if (modal) {
    modal.classList.add('show');
  }
};

/* =========================
   CLOSE MODAL
========================= */
window.closeSchoolModal = function () {
  const modal = document.getElementById('schoolModal');

  if (modal) {
    modal.classList.remove('show');
  }

  editingSchoolId = null;
};

/* =========================
   SUBMIT CREATE / UPDATE
========================= */
window.submitSchool = function () {
  const code = document.getElementById('schoolCode').value.trim();
  const name = document.getElementById('schoolName').value.trim();
  const isActive = document.getElementById('schoolActive').checked;

  if (!code || !name) {
    toast('Kode dan nama sekolah wajib diisi', 'error');
    return;
  }

  const url = editingSchoolId
    ? `/dashboard/schools/${editingSchoolId}`
    : '/dashboard/schools';

  const method = editingSchoolId ? 'PUT' : 'POST';

  fetch(url, {
    method,
    headers: {
      'X-CSRF-TOKEN': csrfToken(),
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      code,
      name,
      is_active: isActive
    })
  })
    .then(async r => {
      const data = await r.json();

      if (!r.ok) {
        throw new Error(
          data.message || 'Gagal menyimpan sekolah'
        );
      }

      return data;
    })
    .then(() => {
      closeSchoolModal();

      if (typeof window.loadSchoolTable === 'function') {
        window.loadSchoolTable();
      }

      toast('Sekolah berhasil disimpan', 'success');
    })
    .catch(err => {
      toast(err.message, 'error');
    });
};

/* =========================
   LOAD DATA
========================= */
window.loadSchoolTable = function () {
  fetch('/dashboard/schools/list', {
    headers: {
      'Accept': 'application/json'
    }
  })
    .then(r => {
      if (!r.ok) {
        throw new Error('Gagal mengambil data sekolah');
      }

      return r.json();
    })
    .then(data => {
      schoolData = Array.isArray(data) ? data : [];
      filteredSchool = [...schoolData];

      currentSchoolPage = 1;

      renderSchoolPage();
      updateSchoolPaginationInfo(filteredSchool.length);

      initSearchSchool();
      initSortableSchool();
      bindSchoolPagination();
    })
    .catch(err => {
      console.error(err);
      toast(err.message, 'error');
    });
};

/* =========================
   DELETE
========================= */
window.deleteSchool = function (id) {
  openConfirm(
    'Hapus Sekolah',
    'Sekolah hanya dapat dihapus jika belum memiliki data skrining.',
    () => {
      fetch(`/dashboard/schools/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json'
        }
      })
        .then(async r => {
          const data = await r.json();

          if (!r.ok) {
            throw new Error(
              data.message || 'Gagal menghapus sekolah'
            );
          }

          return data;
        })
        .then(() => {
          loadSchoolTable();
          toast('Sekolah berhasil dihapus', 'success');
        })
        .catch(err => {
          toast(err.message, 'error');
        });
    }
  );
};

/* =========================
   INIT
========================= */
window.initSchools = function () {
  if (document.getElementById('schoolTable')) {
    loadSchoolTable();
  }
};

/* =========================
   SORTING
========================= */
let schoolSort = {
  key: null,
  direction: 'asc'
};

function initSortableSchool() {
  const table = document.getElementById('schoolTable');

  if (!table) return;

  const headers = table.querySelectorAll('th.sortable');

  headers.forEach(th => {
    if (th.dataset.bound === '1') return;

    th.dataset.bound = '1';

    th.addEventListener('click', () => {
      const key = th.dataset.sort;

      if (schoolSort.key === key) {
        schoolSort.direction =
          schoolSort.direction === 'asc'
            ? 'desc'
            : 'asc';
      } else {
        schoolSort.key = key;
        schoolSort.direction = 'asc';
      }

      applySortSchool();

      currentSchoolPage = 1;

      renderSchoolPage();
      updateSchoolPaginationInfo(filteredSchool.length);
      updateSchoolSortIcons();
    });
  });
}

function updateSchoolSortIcons() {
  document
    .querySelectorAll('#schoolTable th.sortable i')
    .forEach(i => {
      i.className =
        'ph ph-caret-up-down text-slate-400';
    });

  const activeHeader = document.querySelector(
    `#schoolTable th.sortable[data-sort="${schoolSort.key}"]`
  );

  if (!activeHeader) return;

  const icon = activeHeader.querySelector('i');

  if (!icon) return;

  icon.className =
    schoolSort.direction === 'asc'
      ? 'ph ph-caret-up text-blue-600'
      : 'ph ph-caret-down text-blue-600';
}

function applySortSchool() {
  if (!schoolSort.key) return;

  filteredSchool.sort((a, b) => {
    let A;
    let B;

    if (schoolSort.key === 'id') {
      A = Number(a.id);
      B = Number(b.id);
    } else {
      A = String(a[schoolSort.key] ?? '').toLowerCase();
      B = String(b[schoolSort.key] ?? '').toLowerCase();
    }

    if (A < B) {
      return schoolSort.direction === 'asc' ? -1 : 1;
    }

    if (A > B) {
      return schoolSort.direction === 'asc' ? 1 : -1;
    }

    return 0;
  });
}

/* =========================
   SEARCH
========================= */
function initSearchSchool() {
  const input = document.getElementById('schoolSearch');

  if (!input) return;

  if (input.dataset.bound === '1') return;

  input.dataset.bound = '1';

  input.addEventListener('input', () => {
    const keyword =
      input.value.toLowerCase().trim();

    filteredSchool = schoolData.filter(s =>
      String(s.code ?? '')
        .toLowerCase()
        .includes(keyword) ||

      String(s.name ?? '')
        .toLowerCase()
        .includes(keyword)
    );

    applySortSchool();

    currentSchoolPage = 1;

    renderSchoolPage();
    updateSchoolPaginationInfo(filteredSchool.length);
    updateSchoolSortIcons();
  });
}

/* =========================
   RENDER TABLE
========================= */
function renderSchoolPage() {
  const tbody =
    document.querySelector('#schoolTable tbody');

  if (!tbody) return;

  const start =
    (currentSchoolPage - 1) * schoolPerPage;

  const end =
    start + schoolPerPage;

  const pageData =
    filteredSchool.slice(start, end);

  tbody.innerHTML = '';

  const canCRUD =
    window.hasPermission('MASTER_MANAGE');

  pageData.forEach((school, i) => {
    const status = school.is_active
      ? `
        <span class="badge badge-success">
          Aktif
        </span>
      `
      : `
        <span class="badge badge-secondary">
          Nonaktif
        </span>
      `;

    tbody.innerHTML += `
      <tr>
        <td class="text-center" data-label="No">
          ${start + i + 1}
        </td>

        <td data-label="Kode">
          ${escapeSchoolHtml(school.code)}
        </td>

        <td data-label="Nama Sekolah">
          ${escapeSchoolHtml(school.name)}
        </td>

        <td class="text-center" data-label="Status">
          ${status}
        </td>

        <td data-label="Aksi">
          <div class="flex justify-center gap-2">
            ${
              canCRUD
                ? `
                  <button
                    class="btn-aksi edit"
                    onclick="openSchoolForm(
                      ${school.id},
                      '${escapeSchoolJs(school.code)}',
                      '${escapeSchoolJs(school.name)}',
                      ${school.is_active ? 'true' : 'false'}
                    )"
                    title="Edit Sekolah"
                  >
                    <i class="ph ph-pencil"></i>
                  </button>

                  <button
                    class="btn-aksi delete"
                    onclick="deleteSchool(${school.id})"
                    title="Hapus Sekolah"
                  >
                    <i class="ph ph-trash"></i>
                  </button>
                `
                : '-'
            }
          </div>
        </td>
      </tr>
    `;
  });
}

/* =========================
   PAGINATION
========================= */
function bindSchoolPagination() {
  const prevBtn =
    document.getElementById('schoolPrevPage');

  const nextBtn =
    document.getElementById('schoolNextPage');

  if (!prevBtn || !nextBtn) return;

  prevBtn.onclick = () => {
    if (currentSchoolPage > 1) {
      currentSchoolPage--;

      renderSchoolPage();
      updateSchoolPaginationInfo(
        filteredSchool.length
      );
    }
  };

  nextBtn.onclick = () => {
    const totalPage =
      Math.ceil(
        filteredSchool.length /
        schoolPerPage
      );

    if (currentSchoolPage < totalPage) {
      currentSchoolPage++;

      renderSchoolPage();
      updateSchoolPaginationInfo(
        filteredSchool.length
      );
    }
  };
}

function updateSchoolPaginationInfo(total) {
  const pageInfo =
    document.getElementById('schoolPageInfo');

  const schoolInfo =
    document.getElementById('schoolInfo');

  const prevBtn =
    document.getElementById('schoolPrevPage');

  const nextBtn =
    document.getElementById('schoolNextPage');

  if (
    !pageInfo ||
    !schoolInfo ||
    !prevBtn ||
    !nextBtn
  ) {
    return;
  }

  const totalPage =
    Math.max(
      1,
      Math.ceil(total / schoolPerPage)
    );

  const start =
    total === 0
      ? 0
      : (currentSchoolPage - 1) *
          schoolPerPage + 1;

  const end =
    Math.min(
      currentSchoolPage *
        schoolPerPage,
      total
    );

  pageInfo.innerText =
    `Halaman ${currentSchoolPage} / ${totalPage}`;

  schoolInfo.innerText =
    `Menampilkan ${start}-${end} dari ${total} data`;

  prevBtn.disabled =
    currentSchoolPage === 1;

  nextBtn.disabled =
    currentSchoolPage === totalPage;
}

/* =========================
   HTML SAFETY
========================= */
function escapeSchoolHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function escapeSchoolJs(value) {
  return String(value ?? '')
    .replace(/\\/g, '\\\\')
    .replace(/'/g, "\\'")
    .replace(/\r?\n/g, '\\n');
}
