{{-- MODAL PREVIEW REKENING KORAN --}}
<div id="rekeningPreviewModal" class="confirm-overlay">
    <div class="confirm-box"
        style="max-width: 1100px; width: 95%; max-height: 95vh; display: flex; flex-direction: column; padding: 25px;">
        <div class="modal-header"
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
            <h3 style="margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-printer" style="color: #0ea5e9;"></i> Preview Rekening Koran
            </h3>
            <button onclick="closeRekeningPreview()"
                style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <div id="rekeningPreviewBody"
            style="flex: 1; overflow-y: auto; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
            {{-- Content will be rendered here --}}
        </div>

        <div class="modal-footer"
            style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 20px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="btn-secondary" onclick="closeRekeningPreview()">
                    <i class="ph ph-x-circle"></i> Tutup
                </button>

                <div
                    style="display: flex; gap: 10px; background: #f8fafc; padding: 6px 12px; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div class="filter-item" style="display: flex; align-items: center; gap: 5px;">
                        <label style="font-size: 10px; font-weight: 700; color: #475569;">PT. KIRI:</label>
                        <select id="ptRekeningKiri" onchange="updateRekeningSignatory('Kiri')"
                            style="height: 30px; padding: 0 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 10px; min-width: 140px; background: #fff;">
                            <option value="">-- Kosong --</option>
                        </select>
                    </div>
                    <div class="divider" style="width: 1px; height: 25px; background: #e2e8f0;"></div>
                    <div class="filter-item" style="display: flex; align-items: center; gap: 5px;">
                        <label style="font-size: 10px; font-weight: 700; color: #475569;">PT. TENGAH:</label>
                        <select id="ptRekeningTengah" onchange="updateRekeningSignatory('Tengah')"
                            style="height: 30px; padding: 0 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 10px; min-width: 140px; background: #fff;">
                            <option value="">-- Kosong --</option>
                        </select>
                    </div>
                    <div class="divider" style="width: 1px; height: 25px; background: #e2e8f0;"></div>
                    <div class="filter-item" style="display: flex; align-items: center; gap: 5px;">
                        <label style="font-size: 10px; font-weight: 700; color: #475569;">PT. KANAN:</label>
                        <select id="ptRekeningKanan" onchange="updateRekeningSignatory('Kanan')"
                            style="height: 30px; padding: 0 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 10px; min-width: 140px; background: #fff;">
                            <option value="">-- Kosong --</option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <button class="btn-primary" onclick="printRekeningExcel()"
                    style="background: #10b981; border-color: #10b981; color: white;">
                    <i class="ph ph-file-xls"></i> Unduh Excel
                </button>
                <button class="btn-primary" onclick="printRekening()"
                    style="background: #ff4d4d; border-color: #ff4d4d; color: white;">
                    <i class="ph ph-file-pdf"></i> Unduh PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Saldo Awal Pendapatan -->
<div id="modalRekeningSaldoAwal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 440px; padding: 30px;">
        <h3 style="margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
            <i class="ph ph-wallet" style="font-size: 24px; color: #0369a1;"></i>
            <span style="font-size: 20px; font-weight: 800; color: #1e293b;">Set Saldo Awal Tahun</span>
        </h3>

        <div class="alert alert-info"
            style="margin-bottom: 1.5rem; font-size: 0.9rem; background: #f0f9ff; border-left: 4px solid #0369a1; padding: 12px; color: #0c4a6e; border-radius: 8px;">
            <i class="ph-fill ph-info"></i> Saldo awal hanya perlu di set 1x di awal tahun. Nilai ini akan
            diakumulasikan ke
            perhitungan mutasi.
        </div>

        <form id="formRekeningSaldoAwal" onsubmit="submitRekeningSaldoAwal(event)">
            <div class="form-group" style="margin-bottom: 20px;">
                <label
                    style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">Pilih
                    Bank</label>
                <select id="rekeningSaldoAwalBank" name="bank" class="form-input" required style="height: 42px;">
                    <option value="">Pilih Bank...</option>
                    <option value="Bank Riau Kepri Syariah">Bank Riau Kepri Syariah</option>
                    <option value="Bank Syariah Indonesia">Bank Syariah Indonesia</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label
                    style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">Nominal
                    Saldo Awal</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 11px; color: #64748b; font-weight: 500;">Rp</span>
                    <input type="text" id="rekeningSaldoAwalDisplayInput" class="form-input"
                        style="padding-left: 35px; font-weight: 700; height: 42px;" required placeholder="0">
                    <input type="hidden" id="rekeningSaldoAwalValue" name="jumlah" required>
                </div>
            </div>

            <div class="confirm-actions" style="margin-top: 30px;">
                <button type="button" class="btn-secondary" onclick="closeRekeningSaldoAwalModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="button" id="btnHapusRekeningSaldoAwal" class="btn-danger" style="display: none;"
                    onclick="deleteRekeningSaldoAwal()">
                    <i class="ph ph-trash"></i> Hapus
                </button>
                <button type="submit" class="btn-primary">
                    <i class="ph ph-floppy-disk"></i> Simpan Saldo
                </button>
            </div>
        </form>
    </div>
</div>





