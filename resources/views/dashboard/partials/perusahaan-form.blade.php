<div id="perusahaanModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width:420px">
        <h3 id="perusahaanModalTitle"><i class="ph ph-buildings"></i> Tambah Perusahaan</h3>

        <div class="form-group">
            <label>Kode Perusahaan</label>
            <input id="perusahaanKode" class="form-input" readonly>
        </div>

        <div class="form-group">
            <label>Nama Perusahaan</label>
            <input type="text" id="perusahaanNama" class="form-input" placeholder="Contoh: PT. Asuransi Jiwa">
        </div>

        <div class="modal-actions">
            <button class="btn-secondary" onclick="closePerusahaanModal()">
                <i class="ph ph-x"></i> Batal
            </button>
            <button class="btn-primary" onclick="submitPerusahaan()">
                <i class="ph ph-floppy-disk"></i> Simpan
            </button>
        </div>
    </div>
</div>