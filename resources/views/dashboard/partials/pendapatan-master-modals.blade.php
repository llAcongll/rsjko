{{-- MASTER FORMS FOR PENDAPATAN MODULES --}}

{{-- KERJASAMA --}}
<div id="modalMasterFormKerjasama" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 500px;">
        <h3 id="masterFormTitleKerjasama"><i class="ph ph-folder-plus"></i> Tambah Kelompok Kerjasama</h3>
        <form id="formMasterKerjasama" autocomplete="off">
            <input type="hidden" id="masterIdKerjasama">
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Tanggal Pendapatan</label>
                <input type="date" id="masterTanggalKerjasama" required class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Tanggal Rekening Koran (Opsional)</label>
                <input type="date" id="masterTanggalRkKerjasama" class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>No. Bukti (Opsional)</label>
                <input type="text" id="masterNoBuktiKerjasama" class="form-input"
                    placeholder="Masukkan nomor bukti jika ada">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Keterangan / Uraian</label>
                <textarea id="masterKeteranganKerjasama" class="form-input" rows="3"
                    placeholder="Contoh: Pendapatan Kerjasama Bulan Januari"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeMasterModalKerjasama()">Batal</button>
                <button type="submit" class="btn-primary" id="btnSimpanMasterKerjasama">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- UMUM --}}
<div id="modalMasterFormUmum" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 500px;">
        <h3 id="masterFormTitleUmum"><i class="ph ph-folder-plus"></i> Tambah Kelompok Umum</h3>
        <form id="formMasterUmum" autocomplete="off">
            <input type="hidden" id="masterIdUmum">
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Tanggal Pendapatan</label>
                <input type="date" id="masterTanggalUmum" required class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Tanggal Rekening Koran (Opsional)</label>
                <input type="date" id="masterTanggalRkUmum" class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>No. Bukti (Opsional)</label>
                <input type="text" id="masterNoBuktiUmum" class="form-input"
                    placeholder="Masukkan nomor bukti jika ada">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Keterangan / Uraian</label>
                <textarea id="masterKeteranganUmum" class="form-input" rows="3"
                    placeholder="Contoh: Pendapatan Pasien Umum Tanggal ..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeMasterModalUmum()">Batal</button>
                <button type="submit" class="btn-primary" id="btnSimpanMasterUmum">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- BPJS --}}
<div id="modalMasterFormBpjs" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 500px;">
        <h3 id="masterFormTitleBpjs"><i class="ph ph-folder-plus"></i> Tambah Kelompok BPJS</h3>
        <form id="formMasterBpjs" autocomplete="off">
            <input type="hidden" id="masterIdBpjs">
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Tanggal Pendapatan</label>
                <input type="date" id="masterTanggalBpjs" required class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Tanggal Rekening Koran (Opsional)</label>
                <input type="date" id="masterTanggalRkBpjs" class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>No. Bukti (Opsional)</label>
                <input type="text" id="masterNoBuktiBpjs" class="form-input"
                    placeholder="Masukkan nomor bukti jika ada">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Keterangan / Uraian</label>
                <textarea id="masterKeteranganBpjs" class="form-input" rows="3"
                    placeholder="Contoh: Klaim BPJS Bulan ..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeMasterModalBpjs()">Batal</button>
                <button type="submit" class="btn-primary" id="btnSimpanMasterBpjs">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- JAMINAN --}}
<div id="modalMasterFormJaminan" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 500px;">
        <h3 id="masterFormTitleJaminan"><i class="ph ph-folder-plus"></i> Tambah Kelompok Jaminan</h3>
        <form id="formMasterJaminan" autocomplete="off">
            <input type="hidden" id="masterIdJaminan">
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Tanggal Pendapatan</label>
                <input type="date" id="masterTanggalJaminan" required class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Tanggal Rekening Koran (Opsional)</label>
                <input type="date" id="masterTanggalRkJaminan" class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>No. Bukti (Opsional)</label>
                <input type="text" id="masterNoBuktiJaminan" class="form-input"
                    placeholder="Masukkan nomor bukti jika ada">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Keterangan / Uraian</label>
                <textarea id="masterKeteranganJaminan" class="form-input" rows="3"
                    placeholder="Contoh: Pendapatan Jaminan Tanggal ..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeMasterModalJaminan()">Batal</button>
                <button type="submit" class="btn-primary" id="btnSimpanMasterJaminan">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- LAIN-LAIN --}}
<div id="modalMasterFormLain" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 500px;">
        <h3 id="masterFormTitleLain"><i class="ph ph-folder-plus"></i> Tambah Kelompok Lain-lain</h3>
        <form id="formMasterLain" autocomplete="off">
            <input type="hidden" id="masterIdLain">
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Tanggal Pendapatan</label>
                <input type="date" id="masterTanggalLain" required class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Tanggal Rekening Koran (Opsional)</label>
                <input type="date" id="masterTanggalRkLain" class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>No. Bukti (Opsional)</label>
                <input type="text" id="masterNoBuktiLain" class="form-input"
                    placeholder="Masukkan nomor bukti jika ada">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Keterangan / Uraian</label>
                <textarea id="masterKeteranganLain" class="form-input" rows="3"
                    placeholder="Contoh: Pendapatan Lain-lain Tanggal ..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeMasterModalLain()">Batal</button>
                <button type="submit" class="btn-primary" id="btnSimpanMasterLain">Simpan</button>
            </div>
        </form>
    </div>
</div>





