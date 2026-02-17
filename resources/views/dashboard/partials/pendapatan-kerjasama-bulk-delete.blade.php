<div id="modalBulkDeleteKerjasama" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 480px;">
        <h3><i class="ph ph-trash"></i> Hapus Data Per Tanggal</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
            Gunakan fitur ini untuk menghapus sekumpulan data Kerjasama pada tanggal tertentu.
        </p>

        <form id="formBulkDeleteKerjasama">
            <div class="form-group">
                <label>Pilih Tanggal</label>
                <input type="date" id="bulkDeleteDateKerjasama" class="form-input" required>
            </div>

            <div class="confirm-actions">
                <button type="submit" class="btn-danger">Hapus Permanen</button>
                <button type="button" class="btn-secondary"
                    onclick="closeModal('modalBulkDeleteKerjasama')">Batal</button>
            </div>
        </form>
    </div>
</div>