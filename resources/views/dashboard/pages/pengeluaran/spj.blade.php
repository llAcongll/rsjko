@php
    $title = 'Surat Pertanggungjawaban (SPJ)';
@endphp

<div class="dashboard">
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-files"></i> {{ $title }}</h2>
            <p>Kelola pertanggungjawaban dana UP/GU</p>
        </div>

        <div class="dashboard-header-right">
            @if(auth()->user()->hasPermission('PENGELUARAN_CREATE') || auth()->user()->isAdmin())
                <button class="btn-tambah-data" onclick="openSpjForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Buat SPJ Baru</span>
                </button>
            @endif
        </div>
    </div>

    <div class="dashboard-box">
        <div class="dashboard-box-header">
            <div class="search-wrapper flex-1">
                <input type="text" id="searchSpj" placeholder="Cari nomor SPJ..." onkeyup="handleSearchSpj(event)"
                    style="width: 100%; height: 48px; padding-left: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
            </div>
        </div>

        <div class="table-container">
            <table id="tableSpj">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="150">Nomor SPJ</th>
                        <th width="120">Tanggal</th>
                        <th>Penerima (Bendahara)</th>
                        <th width="120" class="text-center">Items</th>
                        <th width="120" class="text-center">Status</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableSpjBody">
                    <tr>
                        <td colspan="7" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL SPJ FORM --}}
<div id="spjFormModal" class="confirm-overlay">
    <div class="confirm-modal modal-large">
        <div class="modal-header">
            <h3><i class="ph ph-file-plus"></i> <span id="spjFormTitle">Buat SPJ</span></h3>
            <button class="btn-close" onclick="closeSpjModal()"><i class="ph ph-x"></i></button>
        </div>
        <div class="modal-body">
            <form id="formSpj">
                <input type="hidden" name="id" id="spjId">
                <div class="form-grid grid-2">
                    <div class="form-group">
                        <label>Nomor SPJ</label>
                        <input type="text" name="spj_number" id="spjNumber" class="form-input" required
                            placeholder="Contoh: 001/SPJ-UP/2026">
                    </div>
                    <div class="form-group">
                        <label>Tanggal SPJ</label>
                        <input type="date" name="spj_date" id="spjDate" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Bendahara Pengeluaran</label>
                    <select name="bendahara_id" id="spjBendahara" class="form-input" required>
                        <option value="{{ auth()->id() }}">{{ auth()->user()->username }} (Anda)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Pilih Belanja untuk di-SPJ-kan (Tipe UP yang belum ber-SPJ)</label>
                    <div id="unlinkedExpendituresList"
                        style="max-height: 200px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                        <p class="text-slate-500 text-center">Memuat belanja...</p>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeSpjModal()">Batal</button>
            <button class="btn-ok" onclick="submitSpj(event)" id="btnSimpanSpj">Simpan SPJ</button>
        </div>
    </div>
</div>

<script>
    if (typeof initSpj === 'function') {
        initSpj();
    }
</script>