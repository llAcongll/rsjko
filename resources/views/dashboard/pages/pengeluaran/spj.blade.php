@php
    $title = 'Surat Pertanggungjawaban (SPJ)';
@endphp

<div class="page-container">
    <div class="page-header">
        <div class="page-header-left">
            <h2><i class="ph ph-files"></i> {{ $title }}</h2>
            <p>Kelola pertanggungjawaban dana UP/GU</p>
        </div>

        <div class="page-header-right">
            @if(auth()->user()->hasPermission('PENGELUARAN_SPJ') || auth()->user()->isAdmin())
                <button class="btn-tambah-data" onclick="openSpjForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Buat SPJ Baru</span>
                </button>
            @endif
        </div>
    </div>

    <div class="dashboard-box">
        <div class="table-toolbar">
            <div class="table-search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchSpj" class="table-search" placeholder="Cari nomor SPJ..."
                    onkeyup="handleSearchSpj(event)">
            </div>
        </div>

        <div class="table-container">
            <table class="table universal-table" id="tableSpj">
                <thead>
                    <tr>
                        <th width="40" class="text-center checkbox-col">No</th>
                        <th width="150" class="sortable" onclick="sortSpj('spj_number')" data-sort="spj_number">Nomor
                            SPJ <i class="ph ph-caret-up-down"></i></th>
                        <th width="120" class="sortable" onclick="sortSpj('spj_date')" data-sort="spj_date">Tanggal <i
                                class="ph ph-caret-up-down"></i></th>
                        <th>Penerima (Bendahara)</th>
                        <th width="120" class="text-center sortable" onclick="sortSpj('items_count')"
                            data-sort="items_count">Items <i class="ph ph-caret-up-down"></i></th>
                        <th width="120" class="text-center sortable" onclick="sortSpj('status')" data-sort="status">
                            Status <i class="ph ph-caret-up-down"></i></th>
                        <th width="100" class="text-center action-col">Aksi</th>
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


<script>
    if (typeof initSpj === 'function') {
        initSpj();
    }
</script>