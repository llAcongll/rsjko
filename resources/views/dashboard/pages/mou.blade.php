<div class="dashboard">

    {{-- HEADER --}}
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-handshake"></i> Manajemen MOU</h2>
            <p>Kelola daftar Master MOU dan Instansi Kerjasama</p>
        </div>

        @if(auth()->user()->hasPermission('MASTER_CRUD'))
            <div class="dashboard-header-right">
                <button class="btn-tambah-data" onclick="openMouForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Tambah MOU</span>
                </button>
            </div>
        @endif
    </div>

    {{-- SEARCH BOX --}}
    <div class="dashboard-box mb-4">
        <div class="search-wrapper" style="position: relative; max-width: 420px;">
            <i class="ph ph-magnifying-glass"
                style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
            <input type="text" id="mouSearch" placeholder="Cari kode atau nama instansi MOU..." autocomplete="off"
                style="width: 100%; height: 48px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
        </div>
    </div>

    <style>
        th.sortable i {
            margin-left: 4px;
            font-size: 14px;
            vertical-align: middle;
            transition: color 0.2s;
        }

        th.sortable:hover i {
            color: #64748b !important;
        }
    </style>
    <div class="table-container">
        <table id="mouTable" class="users-table">
            <thead>
                <tr>
                    <th class="text-center sortable" data-sort="id" style="width: 60px; cursor: pointer;">
                        No <i class="ph ph-caret-up-down text-slate-400"></i>
                    </th>
                    <th class="text-center sortable" data-sort="kode" style="width: 140px; cursor: pointer;">
                        Kode <i class="ph ph-caret-up-down text-slate-400"></i>
                    </th>
                    <th class="text-center sortable" data-sort="nama" style="cursor: pointer;">
                        Nama Instansi <i class="ph ph-caret-up-down text-slate-400"></i>
                    </th>
                    <th style="width: 120px;" class="text-center">Aksi</th>
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