<div class="log-page">
    <div class="page-header">
        <div class="header-left">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div
                    style="width: 44px; height: 44px; background: #eff6ff; color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="ph ph-fingerprint"></i>
                </div>
                <div>
                    <h2 style="margin: 0;">Log Aktivitas Pengguna</h2>
                    <p style="margin: 0; color: #64748b; font-size: 13px;">Riwayat perubahan data pendapatan dan
                        pengeluaran</p>
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="logSearch" placeholder="Cari aktivitas atau user..." oninput="handleLogSearch()">
            </div>
        </div>
    </div>

    <div class="filter-bar mb-4"
        style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px;">
        <div class="filter-group">
            <label>Modul:</label>
            <select id="filterModule" onchange="loadLogs()" class="filter-date-input" style="min-width: 200px;">
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
        <div>
            <button class="btn-toolbar btn-toolbar-danger" onclick="purgeLogs()"
                style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 1px solid #fca5a5; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);">
                <i class="ph ph-broom" style="font-size: 18px;"></i>
                <span style="font-weight: 700;">Bersihkan Log Lama</span>
            </button>
        </div>
    </div>

    <div class="table-container shadow-sm">
        <table class="table" id="logTable">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th style="width: 120px;">Aksi</th>
                    <th style="width: 150px;">Modul</th>
                    <th>Deskripsi</th>
                    <th style="width: 130px;">IP Address</th>
                    <th style="width: 120px;" class="text-center">Aksi</th>
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

{{-- MODAL PURGE LOGS --}}
<div id="modalPurgeLogs" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 450px; border-radius: 24px; padding: 0; overflow: hidden;">
        <div style="padding: 32px; text-align: center;">
            <div
                style="width: 64px; height: 64px; background: #fee2e2; color: #ef4444; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 32px;">
                <i class="ph ph-broom"></i>
            </div>
            <h3 style="margin: 0 0 8px; color: #0f172a; font-size: 20px; font-weight: 700;">Bersihkan Log Lama</h3>
            <p style="margin: 0 0 24px; color: #64748b; font-size: 14px; line-height: 1.5;">
                Hapus riwayat aktivitas yang sudah lama untuk mengoptimalkan database. Log yang lebih lama dari hari
                yang
                ditentukan akan dihapus permanen.
            </p>

            <div
                style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0; text-align: left;">
                <label
                    style="display: block; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 8px;">
                    Simpan log selama (Hari)
                </label>
                <div style="position: relative;">
                    <i class="ph ph-calendar text-slate-400"
                        style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%);"></i>
                    <input type="number" id="purgeDaysInput" value="30" min="1"
                        style="width: 100%; height: 48px; padding: 0 16px 0 44px; border-radius: 12px; border: 1.5px solid #cbd5e1; font-weight: 600; font-size: 16px;">
                </div>
                <small style="display: block; margin-top: 8px; color: #94a3b8; font-size: 11px;">
                    Contoh: 30 = Hapus log yang berusia lebih dari 1 bulan.
                </small>
            </div>
        </div>

        <div
            style="padding: 24px 32px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <button class="btn-toolbar-outline" style="height: 48px; font-size: 14px; font-weight: 600;"
                onclick="closeModal('modalPurgeLogs')">Batal</button>
            <button class="btn-toolbar btn-toolbar-danger"
                style="height: 48px; font-size: 14px; font-weight: 700; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; border: none; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);"
                onclick="submitPurgeLogs()">Hapus Permanen</button>
        </div>
    </div>
</div>

{{-- MODAL DETAIL LOG --}}
<div id="modalLogDetail" class="confirm-overlay" onclick="if(event.target === this) closeModal('modalLogDetail')">
    <div class="confirm-box" style="max-width: 800px; padding: 0; overflow: hidden; border-radius: 20px;">
        {{-- Header --}}
        <div
            style="padding: 24px 32px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fafafa;">
            <div>
                <h3 style="margin: 0; color: #0f172a; font-size: 18px;" id="detailLogTitle">Detail Aktivitas</h3>
                <p style="margin: 4px 0 0; font-size: 13px; color: #64748b;" id="dtLogTime">-</p>
            </div>
            <button
                style="background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #64748b;"
                onclick="closeModal('modalLogDetail')">
                <i class="ph ph-x" style="font-size: 20px;"></i>
            </button>
        </div>

        {{-- Body --}}
        <div style="padding: 32px; max-height: calc(100vh - 200px); overflow-y: auto;">
            <div class="log-info-grid mb-4">
                <div class="info-item">
                    <label>Audit User</label>
                    <p id="dtLogUser">-</p>
                </div>
                <div class="info-item">
                    <label>Jenis Aksi</label>
                    <div id="dtLogAction">-</div>
                </div>
                <div class="info-item">
                    <label>Modul Sistem</label>
                    <p id="dtLogModule">-</p>
                </div>
            </div>

            <div class="mb-4">
                <label class="fw-bold mb-2 d-block text-slate-700">Keterangan Aktivitas</label>
                <div id="dtLogDescription"
                    style="padding: 16px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; color: #334155; line-height: 1.6;">
                    -</div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label class="fw-bold mb-2 d-block text-red-600"
                        style="display: flex; align-items: center; gap: 6px;">
                        <i class="ph ph-clock-counter-clockwise"></i> Data Lama
                    </label>
                    <div id="dtLogOldValues" class="json-preview bg-slate-900 text-slate-300 p-4 rounded-xl"
                        style="height: 350px; overflow: auto; font-size: 11px; margin: 0;"></div>
                </div>
                <div>
                    <label class="fw-bold mb-2 d-block text-blue-600"
                        style="display: flex; align-items: center; gap: 6px;">
                        <i class="ph ph-check-circle"></i> Data Baru
                    </label>
                    <div id="dtLogNewValues" class="json-preview bg-slate-900 text-slate-300 p-4 rounded-xl"
                        style="height: 350px; overflow: auto; font-size: 11px; margin: 0;"></div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div
            style="padding: 20px 32px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button class="btn-toolbar-outline" style="height: 44px; padding: 0 24px;"
                onclick="closeModal('modalLogDetail')">Tutup Jendela</button>
        </div>
    </div>
</div>