<div class="dashboard">

    {{-- HEADER --}}
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2>Potongan & Adm Bank</h2>
            <p>Kelola potongan tagihan dan biaya administrasi bank</p>
        </div>

        <div class="dashboard-header-right">
            @if(auth()->user()->hasPermission('PENYESUAIAN_CRUD'))
                <button class="btn-tambah-data" id="btnTambahPenyesuaian">
                    <i class="ph-bold ph-plus"></i>
                    <span>Tambah Penyesuaian</span>
                </button>
            @endif
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="dashboard-box">
        <div class="box-header">
            <div class="toolbar-row">
                <div class="search-wrapper">
                    <div class="input-group" style="position: relative;">
                        <i class="ph ph-magnifying-glass"
                            style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                        <input type="text" id="searchPenyesuaian" placeholder="Cari keterangan atau perusahaan..."
                            style="width: 100%; height: 44px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
                    </div>
                </div>

                <div class="toolbar-actions">
                    <select id="filterKategoriPenyesuaian" class="form-input" style="width: 150px; margin-bottom: 0;">
                        <option value="">Semua Kategori</option>
                        <option value="BPJS">BPJS</option>
                        <option value="JAMINAN">JAMINAN</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table id="penyesuaianTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 110px;">Tanggal</th>
                        <th>Kategori</th>
                        <th>Perusahaan</th>
                        <th class="text-right">Potongan</th>
                        <th class="text-right">Adm Bank</th>
                        <th>Keterangan</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="penyesuaianBody">
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <i class="ph ph-tray" style="font-size: 32px; margin-bottom: 8px;"></i>
                            <p>Memuat data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-2">
            <p id="paginationInfoPenyesuaian" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0
                data
            </p>

            <div class="flex items-center gap-2">
                <button id="prevPagePenyesuaian" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                <span id="pageInfoPenyesuaian" class="font-medium"
                    style="font-size: 14px; min-width: 100px; text-align: center;">1 / 1</span>
                <button id="nextPagePenyesuaian" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
    </div>

</div>