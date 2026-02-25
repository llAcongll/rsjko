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

            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select name="metode_pembayaran" id="pengeluaranMetode" class="form-input" required>
                    <option value="">-- Pilih Metode --</option>
                    <option value="UP">Uang Persediaan (UP)</option>
                    <option value="GU">Ganti Uang (GU)</option>
                    <option value="LS">Langsung (LS)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Vendor / Penerima</label>
                <input type="text" name="vendor" id="pengeluaranVendor" class="form-input"
                    placeholder="Nama toko, rekanan, atau perorangan...">
            </div>

            <div class="form-group" id="guCycleSection" style="display:none;">
                <label>Pilih Batch GU</label>
                <select name="siklus_up" id="pengeluaranSiklus" class="form-input">
                    <option value="">-- Pilih Batch GU --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Uraian Belanja</label>
                <input type="text" name="uraian" id="pengeluaranUraian" class="form-input"
                    placeholder="Masukkan uraian pengeluaran..." required>
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