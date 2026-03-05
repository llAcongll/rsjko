@php
    $title = 'Rekening Koran Pengeluaran';
@endphp

<div class="dashboard">
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-bank"></i> {{ $title }}</h2>
            <p>Kelola saldo rekening bank khusus pencairan dana</p>
        </div>

        <div class="dashboard-header-right">
            @if(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin())
                <button class="btn-tambah-data btn-secondary" onclick="openBankLedgerSaldoAwalModal()"
                    style="margin-right: 8px;">
                    <i class="ph-bold ph-wallet"></i>
                    <span>Set Saldo Awal</span>
                </button>
                <button class="btn-tambah-data btn-info" onclick="openAdjustmentModal()"
                    style="margin-right: 8px; background: #0369a1;">
                    <i class="ph-bold ph-sliders"></i>
                    <span>Penyesuaian</span>
                </button>
                <button class="btn-tambah-data" onclick="openDepositModal()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Deposit / Setor Tunai</span>
                </button>
            @endif

        </div>
    </div>

    <div class="dashboard-box">
        <div class="dashboard-box-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; gap:16px;">
                <div class="form-group" style="margin:0;">
                    <label style="font-size:11px;">Bulan</label>
                    <select id="bankLedgerMonth" class="form-input" style="height:38px; width:150px;"
                        onchange="loadBankLedger()">
                        <option value="">Semua Bulan</option>
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $m)
                            <option value="{{ $i + 1 }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:11px;">Tahun</label>
                    <select id="bankLedgerYear" class="form-input" style="height:38px; width:100px;"
                        onchange="loadBankLedger()">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
            </div>
            <div style="text-align: right;">
                <div id="bankLedgerSaldoAwalDisplay"
                    style="font-size:12px; color:#0369a1; font-weight:600; margin-bottom:4px;">Saldo Awal Tahun: Rp 0
                </div>
                <div style="font-size:11px; color:#64748b;">Saldo Rekening Saat Ini</div>
                <div id="bankLedgerCurrentBalance" style="font-weight:800; font-size:22px; color:#166534;">Rp 0</div>
            </div>
        </div>


        <div class="table-container">
            <table id="tableBankLedger">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="140" class="text-center">Tanggal</th>
                        <th width="120" class="text-center">Jenis Mutasi</th>
                        <th>Uraian / Keterangan</th>
                        <th width="160" class="text-right">Debit (Masuk)</th>
                        <th width="160" class="text-right">Kredit (Keluar)</th>
                        <th width="180" class="text-right">Saldo</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBankLedgerBody">
                    <tr>
                        <td colspan="8" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('dashboard.partials.bank-ledger-form')
@include('dashboard.partials.adjustment-form')

<!-- Modal Saldo Awal Pengeluaran -->
<div id="modalBankLedgerSaldoAwal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 440px; padding: 30px;">
        <h3 style="margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
            <i class="ph ph-wallet" style="font-size: 24px; color: #0f766e;"></i>
            <span style="font-size: 20px; font-weight: 800; color: #1e293b;">Set Saldo Awal Tahun</span>
        </h3>

        <div class="alert alert-info"
            style="margin-bottom: 1.5rem; font-size: 0.9rem; background: #f0fdf4; border-left: 4px solid #0f766e; padding: 12px; color: #064e3b; border-radius: 8px;">
            <i class="ph-fill ph-info"></i> Saldo awal hanya perlu di set 1x di awal tahun. Nilai ini akan
            diakumulasikan ke perhitungan mutasi.
        </div>

        <form id="formBankLedgerSaldoAwal" onsubmit="submitBankLedgerSaldoAwal(event)">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">
                    Nominal Saldo Awal
                </label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 11px; color: #64748b; font-weight: 500;">Rp</span>
                    <input type="text" id="bankLedgerSaldoAwalDisplayInput" class="form-input"
                        style="padding-left: 35px; font-weight: 700; height: 42px;" required placeholder="0">
                    <input type="hidden" id="bankLedgerSaldoAwalValue" name="amount" required>
                </div>
            </div>

            <div class="confirm-actions" style="margin-top: 30px;">
                <button type="button" class="btn-secondary" onclick="closeBankLedgerSaldoAwalModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="button" id="btnHapusBankLedgerSaldoAwal" class="btn-danger" style="display: none;"
                    onclick="deleteBankLedgerSaldoAwal()">
                    <i class="ph ph-trash"></i> Hapus
                </button>
                <button type="submit" class="btn-primary">
                    <i class="ph ph-floppy-disk"></i> Simpan Saldo
                </button>
            </div>
        </form>
    </div>
</div>