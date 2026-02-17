<div id="confirmModal" class="confirm-overlay">
  <div class="confirm-box danger">
    <div class="confirm-icon"><i class="ph ph-trash" style="font-size: 32px; color: #ef4444;"></i></div>

    <h3 id="confirmTitle">Hapus Data</h3>
    <p id="confirmMessage">
      Data yang dihapus tidak dapat dikembalikan
    </p>

    <div class="modal-actions">
      <button class="btn-secondary" onclick="closeConfirm()">Batal</button>
      <button class="btn-danger btn-ok" onclick="handleConfirmOk()">Hapus</button>
    </div>
  </div>
</div>