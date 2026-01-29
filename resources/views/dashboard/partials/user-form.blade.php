<div id="userModal" class="confirm-overlay">
  <div class="confirm-box" style="max-width:420px">

    <h3 id="userModalTitle">➕ Tambah User</h3>

    <div class="form-group">
      <label>Username</label>
      <input id="userUsername" type="text" class="form-input">
    </div>

    <div class="form-group">
      <label>Password</label>
      <input id="userPassword" type="password" class="form-input">
    </div>

    <div class="form-group">
      <label>Role</label>
      <select id="userRole" class="form-input">
        <option value="USER">USER</option>
        <option value="ADMIN">ADMIN</option>
      </select>
    </div>

    <div class="confirm-actions">
      <button class="btn-secondary" onclick="closeUserModal()">Batal</button>
      <button class="btn-primary" onclick="submitUser()">Simpan</button>
    </div>

  </div>
</div>
