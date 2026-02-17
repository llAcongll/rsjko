/* ==================================================
   USERS CRUD – FINAL STABLE VERSION
================================================== */

let editingUserId = null;

/* ==================================================
   SAFE JSON PARSER (ANTI <!DOCTYPE ERROR)
================================================== */
async function fetchJsonSafe(response) {
  const text = await response.text();

  try {
    return JSON.parse(text);
  } catch (e) {
    console.error('❌ RESPONSE BUKAN JSON:', text);
    throw new Error('Server tidak mengembalikan JSON');
  }
}

/* ==================================================
   OPEN MODAL (ADD / EDIT)
================================================== */
window.openUserForm = function (id = null, username = '', role = 'USER', permissions = []) {
  editingUserId = id;

  document.getElementById('userModalTitle').innerText =
    id ? '✏️ Edit User' : '➕ Tambah User';

  document.getElementById('userUsername').value = username;
  document.getElementById('userPassword').value = '';
  document.getElementById('userRole').value = role;

  // Reset & Populate Checkboxes
  const checkboxes = document.querySelectorAll('#permissionSection input[type="checkbox"]');
  checkboxes.forEach(cb => {
    cb.checked = (role === 'ADMIN') || permissions.includes(cb.value);
  });

  togglePermissionSection();

  const modal = document.getElementById('userModal');
  modal.classList.add('show');
};

window.togglePermissionSection = function () {
  const role = document.getElementById('userRole').value;
  const section = document.getElementById('permissionSection');

  if (role === 'ADMIN') {
    section.style.opacity = '0.5';
    section.style.pointerEvents = 'none';
    // Centang semua jika admin (visual only)
    section.querySelectorAll('input').forEach(i => i.checked = true);
  } else {
    section.style.opacity = '1';
    section.style.pointerEvents = 'auto';
  }
};

/* ==================================================
   CLOSE MODAL
================================================== */
window.closeUserModal = function () {
  const modal = document.getElementById('userModal');
  modal.classList.remove('show');
  editingUserId = null;
};

/* ==================================================
   SUBMIT (CREATE / UPDATE)
================================================== */
window.submitUser = function () {
  const username = document.getElementById('userUsername').value.trim();
  const password = document.getElementById('userPassword').value;
  const role = document.getElementById('userRole').value;

  if (!username) {
    alert('Username wajib diisi');
    return;
  }

  // Collect Permissions
  let permissions = [];
  if (role === 'USER') {
    document.querySelectorAll('#permissionSection input[type="checkbox"]:checked').forEach(cb => {
      permissions.push(cb.value);
    });
  }

  const url = editingUserId
    ? `/dashboard/users/${editingUserId}`
    : `/dashboard/users`;

  const method = editingUserId ? 'PUT' : 'POST';

  fetch(url, {
    method,
    credentials: 'same-origin',
    headers: {
      'X-CSRF-TOKEN': csrfToken(),
      'Content-Type': 'application/json',
      'Accept': 'application/json' // 🔑 WAJIB
    },
    body: JSON.stringify({ username, password, role, permissions })
  })
    .then(async r => {
      const data = await fetchJsonSafe(r);

      if (!r.ok) {
        const msg =
          data?.errors
            ? Object.values(data.errors)[0][0]
            : data.message || 'Gagal menyimpan user';

        throw new Error(msg);
      }

      return data;
    })
    .then(() => {
      closeUserModal();
      toast('User berhasil disimpan', 'success');
      openUsers();
    })
    .catch(err => toast(err.message, 'error'));

};

/* ==================================================
   EDIT USER (FETCH DATA)
================================================== */
window.editUser = function (id) {
  fetch(`/dashboard/users/${id}`, {
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json'
    }
  })
    .then(fetchJsonSafe)
    .then(user => {
      openUserForm(user.id, user.username, user.role, user.permissions || []);
    })
    .catch(err => toast(err.message, 'error'));
};

/* ==================================================
   DELETE USER (CONFIRM MODAL)
================================================== */
window.deleteUser = function (id) {
  openConfirm(
    'Hapus User',
    'User yang dihapus tidak dapat dikembalikan',
    () => {
      fetch(`/dashboard/users/${id}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json'
        }
      })
        .then(fetchJsonSafe)
        .then(() => {
          toast('User berhasil dihapus', 'success'); // ✅ TAMBAHAN
          openUsers();
        })
        .catch(err => toast(err.message, 'error'));
    }
  );
};

console.log('✅ users.js loaded (FINAL)');
