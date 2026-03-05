<div id="userModal" class="confirm-overlay">
  <div class="confirm-box" style="max-width: 800px; width: 95%;">
    <h3 id="userModalTitle"><i class="ph ph-plus-circle"></i> Tambah User</h3>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
      <div class="form-group">
        <label>Username</label>
        <input id="userUsername" type="text" class="form-input">
      </div>

      <div class="form-group">
        <label>Role</label>
        <select id="userRole" class="form-input" onchange="togglePermissionSection()">
          <option value="USER">USER (Staff)</option>
          <option value="ADMIN">ADMIN (Full Access)</option>
        </select>
      </div>
    </div>

    <div class="form-group" id="userPasswordGroup">
      <label>Password</label>
      <input id="userPassword" type="password" class="form-input">
    </div>

    {{-- PERMISSIONS SECTION --}}
    <div id="permissionSection" style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
      <h4 style="font-size: 14px; color: #64748b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
        <i class="ph ph-lock-key"></i> Hak Akses (Permissions)
      </h4>

      <style>
        .perm-group {
          margin-bottom: 20px;
          background: #f8fafc;
          padding: 12px;
          border-radius: 8px;
          border: 1px solid #f1f5f9;
        }

        .perm-group-title {
          font-size: 11px;
          font-weight: 700;
          color: #1e293b;
          margin-bottom: 8px;
          border-bottom: 1px solid #e2e8f0;
          padding-bottom: 4px;
          text-transform: uppercase;
        }

        .perm-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
          gap: 8px;
        }

        .permission-item {
          font-size: 12px;
          display: flex;
          align-items: center;
          gap: 6px;
        }

        .permission-item label {
          cursor: pointer;
          display: flex;
          align-items: center;
          gap: 6px;
          font-weight: 500;
          color: #475569;
        }

        .permission-item input {
          width: 14px;
          height: 14px;
        }

        .text-blue {
          color: #2563eb !important;
        }

        .text-red {
          color: #dc2626 !important;
        }

        .text-green {
          color: #16a34a !important;
        }
      </style>

      <div style="max-height: 450px; overflow-y: auto; padding-right: 10px;">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
          {{-- LEFT COLUMN --}}
          <div>
            {{-- 1. PERENCANAAN --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-clipboard-text"></i> 1. PERENCANAAN</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="PERENCANAAN_VIEW"> Menu
                    Perencanaan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENDAPATAN_VIEW"> Lihat
                    Rek. Pendapatan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENDAPATAN_CRUD"> Kelola
                    Rek. Pendapatan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="ANGGARAN_PENDAPATAN_VIEW"> Lihat Angg.
                    Pendapatan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="ANGGARAN_PENDAPATAN_CRUD"> Kelola
                    Angg. Pendapatan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENGELUARAN_VIEW"> Lihat
                    Rek. Pengeluaran</label></div>
                <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENGELUARAN_CRUD">
                    Kelola Rek. Pengeluaran</label></div>
                <div class="permission-item"><label><input type="checkbox" value="ANGGARAN_PENGELUARAN_VIEW"> Lihat
                    Angg. Pengeluaran</label></div>
                <div class="permission-item"><label><input type="checkbox" value="ANGGARAN_PENGELUARAN_CRUD"> Kelola
                    Angg. Pengeluaran</label></div>
              </div>
            </div>

            {{-- 2. PENDAPATAN --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-coins"></i> 2. PENDAPATAN</div>

              <p style="font-size: 10px; font-weight: 700; margin-bottom: 5px;">A. PASIEN & RINCIAN</p>
              <div class="perm-grid" style="margin-bottom: 10px;">
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_VIEW"> Pasien Umum
                    (Lihat)</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_CRUD"> Pasien Umum
                    (CRUD)</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox"
                      value="PENDAPATAN_UMUM_POST"> Pasien Umum (Post)</label></div>

                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_VIEW"> BPJS
                    (Lihat)</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_CRUD"> BPJS
                    (CRUD)</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox"
                      value="PENDAPATAN_BPJS_POST"> BPJS (Post)</label></div>

                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_VIEW"> Jaminan
                    (Lihat)</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_CRUD"> Jaminan
                    (CRUD)</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox"
                      value="PENDAPATAN_JAMINAN_POST"> Jaminan (Post)</label></div>

                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_VIEW"> Kerjasama
                    (Lihat)</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_CRUD"> Kerjasama
                    (CRUD)</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox"
                      value="PENDAPATAN_KERJA_POST"> Kerjasama (Post)</label></div>

                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_VIEW"> Lain-lain
                    (Lihat)</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_CRUD"> Lain-lain
                    (CRUD)</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox"
                      value="PENDAPATAN_LAIN_POST"> Lain-lain (Post)</label></div>
              </div>

              <p style="font-size: 10px; font-weight: 700; margin-bottom: 5px;">B. KAS & PIUTANG</p>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="REKENING_PENDAPATAN_VIEW"> Lihat Rek.
                    Koran</label></div>
                <div class="permission-item"><label><input type="checkbox" value="REKENING_PENDAPATAN_CRUD"> Kelola Rek.
                    Koran</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PIUTANG_VIEW"> Lihat Piutang</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="PIUTANG_CRUD"> Kelola Piutang</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="PENYESUAIAN_VIEW"> Lihat
                    Potongan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENYESUAIAN_CRUD"> Kelola
                    Potongan</label></div>
              </div>
            </div>

            {{-- 3. PENGESAHAN --}}
            <div class="perm-group" style="background: #fff7ed; border-color: #fed7aa;">
              <div class="perm-group-title"><i class="ph ph-seal-check"></i> 3. PENGESAHAN (VERIFIKASI)</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="PENGESAHAN_VIEW"> Lihat Menu</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="PENGESAHAN_CRUD"> Kelola Data</label>
                </div>
                <div class="permission-item"><label class="text-red" style="font-weight: 700;"><input type="checkbox"
                      value="PENGESAHAN_POST"> SAHKAN/BATAL SAHKAN</label></div>
              </div>
            </div>
          </div>

          {{-- RIGHT COLUMN --}}
          <div>
            {{-- 4. PENGELUARAN --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-shopping-bag"></i> 4. PENGELUARAN (BELANJA)</div>

              <p style="font-size: 10px; font-weight: 700; margin-bottom: 5px;">A. PENCAIRAN (SPP/SPM/SP2D)</p>
              <div class="perm-grid" style="margin-bottom: 10px;">
                <div class="permission-item"><label><input type="checkbox" value="SPP_VIEW"> Lihat SPP</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SPP_CRUD"> Kelola SPP</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SPM_VIEW"> Lihat SPM</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SPM_CRUD"> Kelola SPM</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SP2D_VIEW"> Lihat SP2D</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SP2D_CRUD"> Kelola SP2D</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENCAIRAN_VIEW"> Lihat
                    Pencairan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENCAIRAN_CRUD"> Kelola
                    Pencairan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENGELUARAN_SPJ"> Kelola SPJ</label>
                </div>
              </div>

              <p style="font-size: 10px; font-weight: 700; margin-bottom: 5px;">B. KAS & BKU</p>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="REKENING_PENGELUARAN_VIEW"> Lihat Rek.
                    Koran</label></div>
                <div class="permission-item"><label><input type="checkbox" value="REKENING_PENGELUARAN_CRUD"> Kelola
                    Rek. Koran</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SALDO_DANA_VIEW"> Lihat Saldo</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="SALDO_DANA_CRUD"> Kelola Saldo</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="BKU_VIEW"> Lihat BKU</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox" value="BKU_SYNC"> Sync
                    BKU</label></div>
              </div>

              <p style="font-size: 10px; font-weight: 700; margin-bottom: 5px;">C. BELANJA (Direct/Expenditure)</p>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="BELANJA_VIEW"> Lihat Belanja</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="BELANJA_CRUD"> Kelola Belanja</label>
                </div>
              </div>

              {{-- 5. LAPORAN --}}
              <div class="perm-group" style="background: #f0fdf4; border-color: #dcfce7;">
                <div class="perm-group-title"><i class="ph ph-chart-bar"></i> 5. LAPORAN</div>
                <div class="perm-grid">
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_VIEW"> Menu Laporan</label>
                  </div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_PENDAPATAN_VIEW"> Lap.
                      Pendapatan</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_REKON_VIEW"> Lap.
                      Rekon</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_PIUTANG_VIEW"> Lap.
                      Piutang</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_MOU_VIEW"> Lap. MOU</label>
                  </div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_PENGELUARAN_VIEW"> Lap.
                      Belanja</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_SPP_VIEW"> Lap. SPP</label>
                  </div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_SPM_VIEW"> Lap. SPM</label>
                  </div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_SP2D_VIEW"> Lap.
                      SP2D</label>
                  </div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_ANGGARAN_VIEW"> Lap.
                      Realisasi
                      (LRA)</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_DPA_VIEW"> Lap. DPA</label>
                  </div>
                  <div class="permission-item"><label><input type="checkbox" value="LAPORAN_SAP_AKRUAL_VIEW"> Lap. SAP
                      Akrual</label></div>
                  <div class="permission-item"><label class="text-green"><input type="checkbox"
                        value="LAPORAN_EXPORT_EXCEL"> Export Excel</label></div>
                  <div class="permission-item"><label class="text-red"><input type="checkbox"
                        value="LAPORAN_EXPORT_PDF">
                      Export PDF</label></div>
                </div>
              </div>

              {{-- 6. PENGATURAN / MASTER --}}
              <div class="perm-group" style="background: #f1f5f9; border-color: #e2e8f0;">
                <div class="perm-group-title"><i class="ph ph-gear"></i> 6. MASTER & SISTEM</div>
                <div class="perm-grid">
                  <div class="permission-item"><label><input type="checkbox" value="MASTER_RUANGAN_VIEW"> Lihat
                      Ruangan</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="MASTER_RUANGAN_CRUD"> Kelola
                      Ruangan</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="MASTER_PERUSAHAAN_VIEW"> Lihat
                      Perusahaan</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="MASTER_PERUSAHAAN_CRUD"> Kelola
                      Perusahaan</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="MASTER_MOU_VIEW"> Lihat MOU</label>
                  </div>
                  <div class="permission-item"><label><input type="checkbox" value="MASTER_MOU_CRUD"> Kelola MOU</label>
                  </div>
                  <div class="permission-item"><label><input type="checkbox" value="MASTER_PENANDA_TANGAN_VIEW"> Lihat
                      Penanda Tangan</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="MASTER_PENANDA_TANGAN_CRUD"> Kelola
                      Penanda Tangan</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="USER_VIEW"> Lihat User</label></div>
                  <div class="permission-item"><label class="text-red"><input type="checkbox" value="USER_CRUD"> Full
                      Kelola User</label></div>
                  <div class="permission-item"><label><input type="checkbox" value="ACTIVITY_LOG_VIEW"> Lihat Log
                      Sistem</label></div>
                  <div class="permission-item"><label class="text-blue" style="font-weight: 700;"><input type="checkbox"
                        value="REVENUE_SYNC"> Revenue Sync Tool</label></div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-actions" style="margin-top: 25px;">
        <button class="btn-secondary" onclick="closeUserModal()">
          <i class="ph ph-x"></i> Batal
        </button>
        <button class="btn-primary" onclick="submitUser()">
          <i class="ph ph-floppy-disk"></i> Simpan
        </button>
      </div>
    </div>
  </div>