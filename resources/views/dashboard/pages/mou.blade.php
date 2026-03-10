<div class="page-container">
    {{-- HEADER --}}
    <div class="page-header">
        <div class="page-header-left">
            <h2><i class="ph ph-handshake"></i> Manajemen MOU</h2>
            <p>Kelola daftar Master MOU dan Instansi Kerjasama</p>
        </div>

        @if(auth()->user()->hasPermission('MASTER_MANAGE'))
            <div class="page-header-right">
                <button class="btn-tambah-data" onclick="openMouForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>MOU Baru</span>
                </button>
            </div>
        @endif
    </div>

    {{-- TABLE BOX --}}
    <div class="dashboard-box">
        <div class="table-toolbar">
            <div class="table-search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="mouSearch" class="table-search"
                    placeholder="Cari kode atau nama instansi MOU...">
            </div>
        </div>

        <div class="table-container">
            <table id="mouTable" class="table universal-table">
                <thead>
                    <tr>
                        <th class="text-center checkbox-col">No</th>
                        <th class="text-center sortable">Kode</th>
                        <th class="text-center sortable">Nama Instansi</th>
                        <th class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-4">
            <p id="mouInfo" class="text-slate-500" style="font-size: 13px;"></p>

            <div class="flex items-center gap-2">
                <button id="prevPageMou" class="btn-aksi">
                    <i class="ph ph-caret-left"></i>
                </button>
                <span id="pageInfoMou" class="font-medium"
                    style="font-size: 14px; min-width: 100px; text-align: center;"></span>
                <button id="nextPageMou" class="btn-aksi">
                    <i class="ph ph-caret-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

</div>





