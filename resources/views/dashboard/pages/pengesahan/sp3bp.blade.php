@php
    $title = 'SP3BP (Surat Perintah Pengesahan Pendapatan & Belanja)';
@endphp

<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-file-text"></i> Pengesahan SP3BP</h2>
            <p>Kelola Pengesahan Pendapatan & Belanja (Bulanan)</p>
        </div>
        <div class="header-right">
            <button class="btn-filter" onclick="showNewSp3bpModal()">
                <i class="ph ph-plus"></i>
                <span>Tambah Periode</span>
            </button>
        </div>
    </div>

    <div class="dashboard-box">
        <div class="table-container"><table class="laporan-table universal-table" id="sp3bpTable">
            <thead>
                <tr>
                    <th class="text-center checkbox-col">No</th>
                    <th class="text-center">Periode</th>
                    <th class="text-center">Tahun</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Tgl Pengesahan</th>
                    <th class="text-center action-col">Aksi</th>
                </tr>
            </thead>
            <tbody id="sp3bpBody">
                <tr>
                    <td colspan="6" class="text-center">Memuat data...</td>
                </tr>
            </tbody>
        </table></div>
    </div>
</div>


<script>
    if (typeof initSp3bp === 'function') {
        initSp3bp();
    }
</script>





