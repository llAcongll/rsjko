<div id="penandaTanganModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width:500px">
        <h3 id="penandaTanganModalTitle"><i class="ph ph-signature"></i> Tambah Penanda Tangan</h3>
        <p id="penandaTanganModalDesc" style="font-size: 13px; color: #64748b; margin-top: -12px; margin-bottom: 24px;">
            Lengkapi detail penanda tangan laporan</p>

        <form id="penandaTanganForm" onsubmit="savePenandaTangan(event)">
            <input type="hidden" id="penandaTanganId">

            <div class="form-group">
                <label>Jabatan</label>
                <input type="text" id="ptJabatan" name="jabatan" class="form-input" required
                    placeholder="Contoh: Direktur / PPTK">
            </div>

            <div class="form-group">
                <label>Pangkat/Golongan</label>
                <input type="text" id="ptPangkat" name="pangkat" class="form-input"
                    placeholder="Contoh: Pembina Utama Muda / IV.c">
            </div>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" id="ptNama" name="nama" class="form-input" required
                    placeholder="Masukkan nama lengkap beserta gelar">
            </div>

            <div class="form-group">
                <label>NIP</label>
                <input type="text" id="ptNip" name="nip" class="form-input" placeholder="Contoh: 19800101 200501 1 001">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closePenandaTanganForm()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" class="btn-primary" id="btnSavePenandaTangan">
                    <i class="ph ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>