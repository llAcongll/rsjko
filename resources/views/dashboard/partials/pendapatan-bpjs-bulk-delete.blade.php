<div id="modalBulkDeleteBpjs" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 480px;">
        <h3><i class="ph ph-trash"></i> Hapus Data Per Tanggal</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
            Gunakan fitur ini untuk menghapus sekumpulan data pada tanggal tertentu jika terjadi kesalahan import atau
            double data.
        </p>

        <form id="formBulkDeleteBpjs">
            <div class="form-group">
                <label>Pilih Tanggal</label>
                <input type="date" id="bulkDeleteDate" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Hanya Jenis BPJS (Opsional)</label>
                <select id="bulkDeleteJenis" class="form-input">
                    <option value="">Semua Jenis</option>
                    <option value="REGULAR">REGULAR</option>
                    <option value="EVAKUASI">EVAKUASI</option>
                    <option value="OBAT">OBAT</option>
                </select>
            </div>

            <div class="confirm-actions">
                <button type="submit" class="btn-danger">Hapus Permanen</button>
                <button type="button" class="btn-secondary" onclick="closeModal('modalBulkDeleteBpjs')">Batal</button>
            </div>
        </form>
    </div>
</div>