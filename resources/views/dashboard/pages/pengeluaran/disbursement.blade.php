@php
    $title = 'Pencairan Dana (SP2D)';
@endphp

<div class="dashboard">
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-wallet"></i> {{ $title }}</h2>
            <p>Kelola pencairan dana (UP/GU/LS) &mdash; Alur: SPP &rarr; SPM &rarr; SP2D (Cair)</p>
        </div>

        <div class="dashboard-header-right">
            @if(auth()->user()->hasPermission('PENGELUARAN_CREATE') || auth()->user()->isAdmin())
                <button class="btn-tambah-data" onclick="openDisbursementForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Buat Pengajuan SPP</span>
                </button>
            @endif
        </div>
    </div>

    <div class="dashboard-box">
        <div class="table-container">
            <table id="tableDisbursement">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="80" class="text-center">Paket</th>
                        <th width="70" class="text-center">Tipe</th>
                        <th width="220" class="text-left">No. Dokumen</th>
                        <th width="100" class="text-center">Siklus</th>
                        <th width="100" class="text-center">Tanggal</th>
                        <th>Kegiatan</th>
                        <th class="text-right">Nilai (Rp)</th>
                        <th width="120" class="text-center">Status</th>
                        <th width="140" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableDisbursementBody">
                    <tr>
                        <td colspan="10" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DISBURSEMENT FORM --}}
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
            <div class="form-group">
                <label><i class="ph ph-list-numbers" style="color:#2563eb"></i> Kegiatan / Kode Rekening</label>
                <select name="kode_rekening_id" id="disbursementRekening" class="form-input">
                    <option value="">-- Pilih Kegiatan --</option>
                </select>
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
                    placeholder="Uraian kegiatan yang akan dibayarkan...">
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

            <div id="guSection"
                style="display:none; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #e2e8f0;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="color: #b45309; font-weight: 700;">Hubungkan dengan SPJ (Wajib untuk GU)</label>
                    <select name="spj_id" id="disbursementSpj" class="form-input" style="border-color: #f59e0b;">
                        <option value="">-- Pilih SPJ --</option>
                    </select>
                </div>
            </div>

            {{-- Hidden: status selalu SPP saat buat baru --}}
            <input type="hidden" name="status" value="SPP">

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

<!-- Modal Konfirmasi Aksi UI -->
<div id="modalConfirmAction" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 400px; text-align: center;">
        <div id="confirmActionIcon" style="font-size: 3rem; margin-bottom: 10px;"></div>
        <h3 id="confirmActionTitle" style="margin-bottom: 10px;">Konfirmasi</h3>
        <p id="confirmActionMessage" style="color: #64748b; font-size: 14px; margin-bottom: 25px; line-height: 1.5;">
        </p>
        <div class="confirm-actions" style="justify-content: center; display: flex; gap: 10px;">
            <button type="button" class="btn-secondary" onclick="closeConfirmActionModal()">
                Tutup
            </button>
            <button type="button" id="btnConfirmActionProceed" class="btn-primary" style="flex:1;">
                Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
    if (typeof initDisbursement === 'function') {
        initDisbursement();
    }
</script>