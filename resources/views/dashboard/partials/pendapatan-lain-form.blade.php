<div id="pendapatanLainModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 800px;">
        <h3 class="modal-title"><i class="ph ph-plus-circle"></i> Tambah Pendapatan Lain-lain</h3>
        <form id="formPendapatanLain" onsubmit="submitPendapatanLain(event)" autocomplete="off">
            <h4 class="section-title">Rincian Pasien</h4>
            <div class="form-grid grid-3">
                <div class="form-group"><label>Tanggal</label><input type="date" name="tanggal" class="form-input"
                        required></div>
                <div class="form-group"><label>Nama Pasien</label><input type="text" name="nama_pasien"
                        class="form-input" required></div>
                <div class="form-group"><label>Ruangan</label><select name="ruangan_id" id="lainRuanganSelect"
                        class="form-input" required>
                        <option value="">-- Pilih Ruangan --</option>
                    </select></div>
            </div>
            <div class="form-grid grid-1">
                <div class="form-group"><label>MOU / Jenis Transaksi</label>
                    <select name="mou_id" id="lainMouSelect" class="form-input" required>
                        <option value="">-- Pilih MOU --</option>
                    </select>
                    <input type="hidden" name="transaksi" id="lainTransaksiHidden">
                </div>
            </div>
            <h4 class="section-title">Rincian Bank</h4>
            <div class="form-grid grid-3">
                <div class="form-group"><label>Metode Pembayaran</label><select id="lainMetodePembayaran"
                        name="metode_pembayaran" class="form-input" required>
                        <option value="">-- Pilih --</option>
                        <option value="TUNAI">Tunai</option>
                        <option value="NON_TUNAI">Non Tunai</option>
                    </select></div>
                <div class="form-group"><label>Bank</label><select id="lainBank" name="bank" class="form-input"
                        disabled>
                        <option value="">-- Pilih Bank --</option>
                    </select></div>
                <div class="form-group"><label>Metode Detail</label><select id="lainMetodeDetail" name="metode_detail"
                        class="form-input" disabled>
                        <option value="">-- Detail --</option>
                    </select></div>
            </div>
            <h4 class="section-title">Rincian Nominal</h4>
            <div class="form-grid grid-2">
                <div class="form-group"><label>RS Tindakan</label>
                    <div class="input-group"><span>Rp</span><input type="text" class="form-input nominal-display-lain"
                            placeholder="0"><input type="hidden" name="rs_tindakan" class="nominal-value-lain"
                            value="0"></div>
                </div>
                <div class="form-group"><label>RS Obat</label>
                    <div class="input-group"><span>Rp</span><input type="text" class="form-input nominal-display-lain"
                            placeholder="0"><input type="hidden" name="rs_obat" class="nominal-value-lain" value="0">
                    </div>
                </div>
                <div class="form-group"><label>Pelayanan Tindakan</label>
                    <div class="input-group"><span>Rp</span><input type="text" class="form-input nominal-display-lain"
                            placeholder="0"><input type="hidden" name="pelayanan_tindakan" class="nominal-value-lain"
                            value="0"></div>
                </div>
                <div class="form-group"><label>Pelayanan Obat</label>
                    <div class="input-group"><span>Rp</span><input type="text" class="form-input nominal-display-lain"
                            placeholder="0"><input type="hidden" name="pelayanan_obat" class="nominal-value-lain"
                            value="0"></div>
                </div>
            </div>
            <div class="total-box"><span>Total Pembayaran</span><strong id="totalPembayaranLain">Rp 0</strong></div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closePendapatanLainModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" id="btnSimpanPendapatanLain" class="btn-primary" disabled>
                    <i class="ph ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="pendapatanLainDetailModal" class="confirm-overlay">
    <div class="confirm-box detail-box" style="max-width: 600px;">
        <h3 class="modal-title"><i class="ph ph-file-text"></i> Detail Lain-lain</h3>
        <div id="detailPendapatanLainContent" class="detail-grid"></div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeDetailPendapatanLain()">Tutup</button>
        </div>
    </div>
</div>