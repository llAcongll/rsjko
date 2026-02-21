<div id="pengeluaranModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 600px;">
        <h3 class="modal-title">
            <i class="ph ph-plus-circle"></i>
            <span id="pengeluaranModalTitle">Tambah Pengeluaran</span>
        </h3>

        <form id="formPengeluaran" onsubmit="submitPengeluaran(event)" autocomplete="off">
            <input type="hidden" name="id" id="pengeluaranId">
            <input type="hidden" name="kategori" id="pengeluaranKategori">

            <div class="form-grid grid-2">
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" id="pengeluaranTanggal" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>Kode Rekening</label>
                    <select name="kode_rekening_id" id="pengeluaranRekening" class="form-input" required>
                        <option value="">-- Pilih Rekening --</option>
                    </select>
                </div>
            </div>

            <div class="form-grid grid-2">
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="pengeluaranMetode" class="form-input">
                        <option value="">-- Pilih Metode --</option>
                        <option value="UP">Uang Persediaan</option>
                        <option value="GU">Ganti Uang</option>
                        <option value="LS">Langsung</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>No. SPP</label>
                    <input type="text" name="no_spp" id="pengeluaranNoSPP" class="form-input" placeholder="Automatis..."
                        readonly style="background: #f8fafc; cursor: not-allowed;">
                    <small style="color: #64748b; font-size: 10px;">* Terisi otomatis berdasarkan tanggal &
                        metode</small>
                </div>
            </div>

            <div class="form-grid grid-2">
                <div class="form-group">
                    <label>No. SPM</label>
                    <input type="text" name="no_spm" id="pengeluaranNoSPM" class="form-input" placeholder="Automatis..."
                        readonly style="background: #f8fafc; cursor: not-allowed;">
                    <small style="color: #64748b; font-size: 10px;">* Terisi otomatis</small>
                </div>

                <div class="form-group">
                    <label>No. SP2D</label>
                    <input type="text" name="no_sp2d" id="pengeluaranNoSP2D" class="form-input"
                        placeholder="Automatis..." readonly style="background: #f8fafc; cursor: not-allowed;">
                    <small style="color: #64748b; font-size: 10px;">* Terisi otomatis</small>
                </div>
            </div>

            <div class="form-group">
                <label>Uraian</label>
                <input type="text" name="uraian" id="pengeluaranUraian" class="form-input"
                    placeholder="Masukkan uraian pengeluaran..." required>
            </div>

            <div class="form-group">
                <label>Keterangan (Opsional)</label>
                <textarea name="keterangan" id="pengeluaranKeterangan" class="form-input" rows="3"
                    placeholder="Tambahkan catatan tambahan..."></textarea>
            </div>

            <div class="form-grid grid-2">
                <div class="form-group">
                    <label>Jumlah yang diminta</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="pengeluaranNominalDisplay" class="form-input nominal-display"
                            placeholder="0" inputmode="numeric" required>
                        <input type="hidden" name="nominal" id="pengeluaranNominalValue" class="nominal-value"
                            value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>Potongan Pajak</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="pengeluaranPotonganPajakDisplay" class="form-input nominal-display"
                            placeholder="0" inputmode="numeric">
                        <input type="hidden" name="potongan_pajak" id="pengeluaranPotonganPajakValue"
                            class="nominal-value" value="0">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Total Dibayarkan</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" id="pengeluaranTotalDibayarkanDisplay" class="form-input" placeholder="0"
                        readonly
                        style="background: #f8fafc; cursor: not-allowed; font-weight: bold; color: #16a34a; font-size: 16px;">
                    <input type="hidden" name="total_dibayarkan" id="pengeluaranTotalDibayarkanValue" value="0">
                </div>
            </div>

            <div class="confirm-actions">
                <button type="button" class="btn-secondary" onclick="closePengeluaranModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" id="btnSimpanPengeluaran" class="btn-primary">
                    <i class="ph ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>