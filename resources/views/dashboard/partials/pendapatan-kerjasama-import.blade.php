<div id="modalImportKerjasama" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 480px;">
        <h3><i class="ph ph-upload-simple"></i> Import Data Kerjasama</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
            Pastikan file menggunakan format CSV sesuai template. Data akan diproses masuk ke database.
        </p>

        <form id="formImportKerjasama" enctype="multipart/form-data">
            <div class="form-group">
                <label>Pilih File CSV</label>
                <input type="file" name="file" accept=".csv" class="form-input" style="padding: 8px;" required>
            </div>

            <div class="confirm-actions">
                <button type="submit" class="btn-primary">Mulai Import</button>
                <button type="button" class="btn-secondary" onclick="closeModal('modalImportKerjasama')">Batal</button>
            </div>
        </form>
    </div>
</div>