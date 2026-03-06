<div class="page-container">
    <div class="page-header">
        <div class="page-header-left">
            <h2><i class="ph ph-clock-counter-clockwise"></i> Log Aktivitas Pengguna</h2>
            <p>Riwayat perubahan data pendapatan dan pengeluaran</p>
        </div>
        <div class="page-header-right">
            <button class="btn-toolbar btn-toolbar-danger" onclick="purgeLogs()">
                <i class="ph ph-broom"></i>
                <span>Bersihkan Log Lama</span>
            </button>
        </div>
    </div>

    <div class="dashboard-box">
        <div class="table-toolbar">
            <div class="flex items-center gap-4">
                <div class="form-group-inline">
                    <select id="filterModule" onchange="loadLogs()" class="form-input"
                        style="height:48px; width:220px; border-radius:12px;">
                        <option value="">Semua Modul</option>
                        <option value="PENDAPATAN_UMUM">Pendapatan Umum</option>
                        <option value="PENDAPATAN_BPJS">Pendapatan BPJS</option>
                        <option value="PENDAPATAN_JAMINAN">Pendapatan Jaminan</option>
                        <option value="PENDAPATAN_KERJA">Pendapatan Kerjasama</option>
                        <option value="PENDAPATAN_LAIN">Pendapatan Lain-lain</option>
                        <option value="PENGELUARAN">Pengeluaran</option>
                        <option value="PIUTANG">Piutang</option>
                        <option value="RUANGAN">Ruangan</option>
                        <option value="USER">User</option>
                    </select>
                </div>
            </div>
            <div class="table-search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="logSearch" class="table-search" placeholder="Cari aktivitas atau user..."
                    oninput="handleLogSearch()">
            </div>
        </div>

        <div class="table-container">
            <table class="table universal-table" id="logTable">
                <thead>
                    <tr>
                        <th class="text-center">Waktu</th>
                        <th class="text-center">User</th>
                        <th class="text-center">Tipe Aksi</th>
                        <th class="text-center">Modul</th>
                        <th class="text-center">Deskripsi</th>
                        <th class="text-center">IP Address</th>
                        <th class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody id="logTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-5">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pagination-container mt-4" id="logPagination"></div>
    </div>