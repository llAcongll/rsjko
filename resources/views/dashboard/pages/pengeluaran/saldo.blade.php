@php
    $title = 'Saldo Dana';
@endphp

<div class="dashboard">
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-piggy-bank"></i> {{ $title }}</h2>
            <p>Kelola saldo kas — UP dan GU</p>
        </div>

        <div class="dashboard-header-right">
            @if(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin())
                <button class="btn-tambah-data" onclick="openSaldoForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Tambah Saldo</span>
                </button>
            @endif
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div id="saldoSummaryCards"
        style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:20px;">
        {{-- dynamically filled by JS --}}
    </div>

    {{-- TABLE --}}
    <div class="dashboard-box">
        <div class="table-container">
            <table id="tableSaldo">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="80" class="text-center">Tipe</th>
                        <th width="80" class="text-center">Siklus</th>
                        <th width="120" class="text-center">Tanggal</th>
                        <th>Uraian</th>
                        <th class="text-right">Nilai (Rp)</th>
                        <th width="100" class="text-center">Status</th>
                        <th width="80" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableSaldoBody">
                    <tr>
                        <td colspan="8" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH SALDO --}}
<div id="saldoFormModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 500px;">
        <h3 class="modal-title">
            <i class="ph ph-piggy-bank"></i>
            <span>Tambah Saldo Dana</span>
        </h3>
        <p style="font-size: 13px; color: #64748b; margin-top: -12px; margin-bottom: 20px;">
            Catat penerimaan dana masuk ke kas bendahara</p>

        <form id="formSaldo" onsubmit="submitSaldo(event)" autocomplete="off">
            <div class="form-group">
                <label>Tipe Dana</label>
                <select name="type" id="saldoType" class="form-input" required>
                    <option value="UP">Uang Persediaan (UP)</option>
                    <option value="GU">Ganti Uang (GU)</option>
                </select>
            </div>

            <div class="form-group" id="saldoSiklusGroup" style="display:none;">
                <label>Siklus Ke (misal: 1 = GU-1)</label>
                <input type="number" name="siklus_up" id="saldoSiklus" class="form-input" placeholder="1" min="1">
            </div>

            <div class="form-grid grid-2">
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="sp2d_date" id="saldoDate" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Nilai (Rp)</label>
                    <input type="number" name="value" id="saldoValue" class="form-input" placeholder="0" required
                        min="0">
                </div>
            </div>

            <div class="form-group">
                <label>Uraian / Keterangan</label>
                <input type="text" name="description" id="saldoDescription" class="form-input"
                    placeholder="Contoh: Penerimaan UP Triwulan I">
            </div>

            <input type="hidden" name="status" value="CAIR">

            <div class="confirm-actions">
                <button type="button" class="btn-secondary" onclick="closeSaldoModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" class="btn-primary">
                    <i class="ph ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    if (typeof initSaldoDana === 'function') {
        initSaldoDana();
    }
</script>