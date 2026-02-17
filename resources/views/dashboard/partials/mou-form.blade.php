<div id="mouModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width:420px">
        <h3 id="mouModalTitle"><i class="ph ph-handshake"></i> Tambah MOU</h3>

        <div class="form-group">
            <label>Kode MOU</label>
            <input id="mouKode" class="form-input" readonly>
        </div>

        <div class="form-group">
            <label>Nama Instansi</label>
            <input type="text" id="mouNama" class="form-input" placeholder="Contoh: RS Mitra Sehat">
        </div>

        <div class="modal-actions">
            <button class="btn-secondary" onclick="closeMouModal()">
                <i class="ph ph-x"></i> Batal
            </button>
            <button class="btn-primary" onclick="submitMou()">
                <i class="ph ph-floppy-disk"></i> Simpan
            </button>
        </div>
    </div>
</div>