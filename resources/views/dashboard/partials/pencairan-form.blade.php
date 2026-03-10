{{-- MODAL DISBURSEMENT FORM (Moved to partial to avoid stacking issues) --}}
<div id="disbursementFormModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 650px;">
        <h3 class="modal-title">
            <i class="ph ph-file-text"></i>
            <span id="disbursementModalTitle">Buat Pengajuan SPP</span>
        </h3>
        <p style="font-size: 13px; color: #64748b; margin-top: -12px; margin-bottom: 20px;">
            Lengkapi detail pengajuan pencairan dana. Nomor SPP dibuat otomatis.</p>

        <form id="formDisbursement" onsubmit="submitDisbursement(event)" autocomplete="off">
            <input type="hidden" name="id" id="disbursementId">
            <div class="form-grid grid-2">
                <div class="form-group">
                    <label>Tipe Pencairan</label>
                    <select name="type" id="disbursementType" class="form-input" required>
                        <option value="UP">Uang Persediaan (UP)</option>
                        <option value="GU">Ganti Uang (GU)</option>
                        <option value="LS">Langsung (LS)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Pengajuan</label>
                    <input type="date" name="sp2d_date" class="form-input" id="disbursementDate" required>
                </div>
                <div class="form-group">
                    <label>Rekening Bank</label>
                    <select name="bank" id="disbursementBank" class="form-input" required>
                        <option value="BRK">Bank Riau Kepri Syariah</option>
                        <option value="BSI">Bank Syariah Indonesia</option>
                    </select>
                </div>
            </div>

            <div class="form-group" id="siklusGroup" style="display:none;">
                <label>Siklus Ke (misal: 1 untuk GU-1)</label>
                <input type="number" name="siklus_up" id="disbursementSiklus" class="form-input" placeholder="1"
                    min="1">
            </div>

            {{-- SALDO KAS UP/GU --}}
            <div id="saldoKasInfo"
                style="display:none; background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #93c5fd; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                <div style="font-size: 12px; font-weight: 700; color: #1e40af; margin-bottom: 8px;">
                    <i class="ph ph-wallet"></i> Saldo Kas <span id="saldoKasType">UP</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                    <div>
                        <span style="color: #64748b;">Total Dana Cair:</span>
                        <div style="font-weight: 700; color: #0f172a;" id="saldoTotalCair">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">Total Belanja:</span>
                        <div style="font-weight: 700; color: #dc2626;" id="saldoTotalBelanja">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">SPP Dalam Proses:</span>
                        <div style="font-weight: 700; color: #b45309;" id="saldoSppPending">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">Sisa Saldo Kas:</span>
                        <div style="font-weight: 800; font-size: 15px;" id="saldoSisaKas">-</div>
                    </div>
                </div>
            </div>

            {{-- KEGIATAN / KODE REKENING --}}
            <div class="form-group" id="rekeningGroup">
                <label><i class="ph ph-list-numbers" style="color:#2563eb"></i> Kegiatan / Kode Rekening</label>
                <div id="disbursementRekeningSearchableSelect" style="position: relative;">
                    <input type="text" id="disbursementRekeningSearch" class="form-input"
                        placeholder="Ketik untuk mencari kode atau nama kegiatan..." autocomplete="off">
                    <input type="hidden" name="kode_rekening_id" id="disbursementRekening">
                    <div id="disbursementRekeningDropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
                        background: #fff; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px;
                        max-height: 220px; overflow-y: auto; box-shadow: 0 8px 25px rgba(0,0,0,0.15);">
                    </div>
                </div>
            </div>

            {{-- SISA SALDO INFO --}}
            <div id="sisaSaldoInfo"
                style="display:none; background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                <div style="font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 8px;">
                    <i class="ph ph-info"></i> Informasi Anggaran
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                    <div>
                        <span style="color: #64748b;">Pagu Anggaran:</span>
                        <div style="font-weight: 700; color: #0f172a;" id="infoAnggaran">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">Realisasi:</span>
                        <div style="font-weight: 700; color: #dc2626;" id="infoRealisasi">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">SPP Dalam Proses:</span>
                        <div style="font-weight: 700; color: #b45309;" id="infoSppPending">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">Sisa Tersedia:</span>
                        <div style="font-weight: 800; font-size: 15px;" id="infoSisa">-</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Uraian Kegiatan</label>
                <input type="text" name="uraian" id="disbursementUraian" class="form-input"
                    placeholder="Uraian kegiatan yang akan dibayarkan..." list="disbursementUraianList">
                <datalist id="disbursementUraianList"></datalist>
            </div>

            <div class="form-group">
                <label>Nilai Pencairan (Rp)</label>
                <input type="number" name="value" class="form-input" id="disbursementValue" placeholder="0" required
                    min="0">
            </div>

            <div class="form-group">
                <label>Keterangan Tambahan</label>
                <textarea name="description" id="disbursementDescription" class="form-input" rows="2"
                    placeholder="Keterangan tambahan (opsional)..."></textarea>
            </div>

            <input type="hidden" name="status" id="disbursementStatus" value="SPP">

            <div class="confirm-actions">
                <button type="button" class="btn-secondary" onclick="closeDisbursementModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" id="btnSimpanDisbursement" class="btn-primary">
                    <i class="ph ph-paper-plane-tilt"></i> Ajukan SPP
                </button>
            </div>
        </form>
    </div>
</div>





