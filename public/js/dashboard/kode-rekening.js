/* =====================================================
   KODE REKENING - MODE STRUKTUR (CRUD ONLY)
===================================================== */

let editingKodeRekeningId = null;
let kodeRekeningRaw = [];

/* =========================
   DOM BINDING
========================= */
function bindKodeRekeningDom() {
  const modal = document.getElementById('kodeRekeningModal');
  if (!modal) return;

  window.kodeRekeningModal = modal;
  window.krKode = modal.querySelector('#krKode');
  window.krNama = modal.querySelector('#krNama');
  window.krParentId = modal.querySelector('#krParentId');
  window.krLevel = modal.querySelector('#krLevel');
  window.krTipe = modal.querySelector('#krTipe');
  window.krSumberData = modal.querySelector('#krSumberData');
  window.krSumberDataWrapper = modal.querySelector('#krSumberDataWrapper');
  window.kodeModalTitle = modal.querySelector('#kodeModalTitle');
}

/* =========================
   LOAD TREE (NO ANGGARAN)
========================= */
window.loadKodeRekening = function () {
  fetch('/dashboard/master/kode-rekening', {
    headers: { Accept: 'application/json' }
  })
    .then(r => r.json())
    .then(data => {
      kodeRekeningRaw = data || [];
      renderKodeRekeningTree(kodeRekeningRaw);
    })
    .catch(e => toast(e, 'error'));
};

/* =========================
   RENDER TREE (STRUKTUR)
========================= */
function renderKodeRekeningTree(data) {
  const wrap = document.getElementById('kodeRekeningTree');
  if (!wrap) return;

  wrap.innerHTML = '';
  data.forEach(node => wrap.appendChild(renderNode(node)));
}

function renderNode(node) {
  const wrap = document.createElement('div');
  wrap.className = 'kode-node';

  const canCRUD = window.hasPermission('KODE_REKENING_CRUD');

  const badge = node.tipe === 'detail' && node.sumber_data
    ? `<span class="sumber-badge">${node.sumber_data.replace('_', ' ')}</span>`
    : '';

  wrap.innerHTML = `
    <div class="kode-row">
      <div class="kode-label ${node.tipe}">
        <div class="node-icon ${node.tipe}">
          <i class="ph-fill ${node.tipe === 'header' ? 'ph-folder' : 'ph-file-text'}"></i>
        </div>
        <div class="node-text">
          <span class="node-kode">${node.kode}</span>
          <span class="node-nama">${node.nama}</span>
          ${badge}
        </div>
      </div>
      <div class="kode-actions">
        ${canCRUD ? `
          <button class="btn-aksi add" onclick='openKodeRekeningForm(${JSON.stringify(node)}, "child")' title="Tambah Sub">
            <i class="ph ph-plus"></i>
          </button>
          <button class="btn-aksi edit" onclick='openKodeRekeningForm(${JSON.stringify(node)}, "edit")' title="Edit">
            <i class="ph ph-pencil-line"></i>
          </button>
          <button class="btn-aksi delete" onclick='deleteKodeRekening(${node.id})' title="Hapus">
            <i class="ph ph-trash"></i>
          </button>
        ` : ''}
      </div>
    </div>
  `;

  if (node.children?.length) {
    const c = document.createElement('div');
    c.className = 'kode-children';
    node.children.forEach(n => c.appendChild(renderNode(n)));
    wrap.appendChild(c);
  }

  return wrap;
}

/* =========================
   MODAL CRUD
========================= */
window.openKodeRekeningForm = function (row = null, mode = 'create') {
  if (!window.krKode) return;

  editingKodeRekeningId = null;
  krKode.value = '';
  krNama.value = '';
  krParentId.value = '';
  krLevel.value = 1;
  krTipe.value = 'header';
  kodeModalTitle.innerText = 'Tambah Kode Rekening';

  if (mode === 'edit') {
    editingKodeRekeningId = row.id;
    krKode.value = row.kode;
    krNama.value = row.nama;
    krParentId.value = row.parent_id || '';
    krLevel.value = row.level;
    krTipe.value = row.tipe;
    krSumberData.value = row.sumber_data || '';
    kodeModalTitle.innerText = 'Edit Kode Rekening';
  }

  toggleSumberDataField();

  if (mode === 'child') {
    krParentId.value = row.id;
    krLevel.value = row.level + 1;
    kodeModalTitle.innerText = 'Tambah Sub Kode';
  }

  kodeRekeningModal.classList.add('show');
};

window.closeKodeRekeningModal = function () {
  kodeRekeningModal.classList.remove('show');
};

/* =========================
   SUBMIT
========================= */
window.submitKodeRekening = function () {
  const payload = {
    kode: krKode.value,
    nama: krNama.value,
    parent_id: krParentId.value || null,
    level: krLevel.value,
    tipe: krTipe.value,
    sumber_data: krSumberData.value || null
  };

  const url = editingKodeRekeningId
    ? `/dashboard/master/kode-rekening/${editingKodeRekeningId}`
    : `/dashboard/master/kode-rekening`;

  fetch(url, {
    method: editingKodeRekeningId ? 'PUT' : 'POST',
    headers: {
      'X-CSRF-TOKEN': csrfToken(),
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(payload)
  })
    .then(async res => {
      if (!res.ok) {
        const err = await res.json();
        throw new Error(err.message || err || 'Gagal menyimpan data');
      }
      return res.json();
    })
    .then(() => {
      closeKodeRekeningModal();
      loadKodeRekening();
      toast('Tersimpan', 'success');
    })
    .catch(e => {
      console.error(e);
      toast(e.message || e, 'error');
    });
};

window.toggleSumberDataField = function () {
  if (!window.krTipe || !window.krSumberDataWrapper) return;
  if (krTipe.value === 'detail') {
    krSumberDataWrapper.style.display = 'block';
  } else {
    krSumberDataWrapper.style.display = 'none';
    krSumberData.value = '';
  }
};

/* =========================
   DELETE
========================= */
window.deleteKodeRekening = function (id) {
  openConfirm(
    'Hapus Kode Rekening',
    'Data akan dihapus permanen',
    () => {
      fetch(`/dashboard/master/kode-rekening/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json'
        }
      })
        .then(async res => {
          if (!res.ok) {
            const err = await res.json();
            throw new Error(err.message || err || 'Gagal menghapus data');
          }
        })
        .then(() => {
          loadKodeRekening();
          toast('Dihapus', 'success');
        })
        .catch(e => toast(e.message || e, 'error'));
    }
  );
};

document.addEventListener('DOMContentLoaded', bindKodeRekeningDom);

/* =====================================================
   MENU HANDLER - KODE REKENING
   Uses loadContent() from app.js for proper AJAX loading
===================================================== */
window.openKodeRekening = async function (btn) {
  if (typeof window.setActiveMenu === 'function') {
    window.setActiveMenu(btn);
  }
  if (typeof window.closeOnMobile === 'function') {
    window.closeOnMobile();
  }

  // Muat konten halaman via AJAX
  const ok = await window.loadContent('master/kode-rekening');
  if (!ok) return;

  // Bind DOM elements for the modal
  bindKodeRekeningDom();

  // Load the tree data
  loadKodeRekening();
};
