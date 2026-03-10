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
          grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
          gap: 8px;
        }

        .permission-item {
          font-size: 11px;
          display: flex;
          align-items: center;
          gap: 4px;
        }

        .permission-item label {
          cursor: pointer;
          display: flex;
          align-items: center;
          gap: 4px;
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

        .sub-group-title {
          font-size: 10px;
          font-weight: 700;
          margin-top: 10px;
          margin-bottom: 5px;
          color: #64748b;
          border-left: 3px solid #cbd5e1;
          padding-left: 6px;
        }
      </style>

      <div style="max-height: 450px; overflow-y: auto; padding-right: 10px;">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
          {{-- LEFT COLUMN --}}
          <div>
            {{-- 1. DASHBOARD --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-chart-pie"></i> 1. DASHBOARD</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="DASHBOARD_VIEW"> Lihat
                    Dashboard</label></div>
              </div>
            </div>

            {{-- 2. PERENCANAAN --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-clipboard-text"></i> 2. PERENCANAAN</div>
              <div class="sub-group-title">Kode Rekening Pendapatan</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENDAPATAN_VIEW">
                    Lihat</label></div>
                <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENDAPATAN_MANAGE">
                    Tambah</label></div>
                <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENDAPATAN_MANAGE">
                    Edit</label></div>
                <div class="permission-item"><label class="text-red"><input type="checkbox"
                      value="KODE_REKENING_PENDAPATAN_MANAGE"> Hapus</label></div>
              </div>
              <div class="sub-group-title">Kode Rekening Pengeluaran</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENGELUARAN_VIEW">
                    Lihat</label></div>
                <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENGELUARAN_MANAGE">
                    Tambah</label></div>
                <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENGELUARAN_MANAGE">
                    Edit</label></div>
                <div class="permission-item"><label class="text-red"><input type="checkbox"
                      value="KODE_REKENING_PENGELUARAN_MANAGE"> Hapus</label></div>
              </div>
              <div class="sub-group-title">Anggaran (DPA)</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="ANGGARAN_PENDAPATAN_VIEW"> Lihat Angg.
                    Pend</label></div>
                <div class="permission-item"><label><input type="checkbox" value="ANGGARAN_PENDAPATAN_MANAGE"> Kelola
                    Angg. Pend</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="ANGGARAN_PENDAPATAN_EXPORT"> Export</label></div>
                <div class="permission-item"><label><input type="checkbox" value="ANGGARAN_PENGELUARAN_VIEW"> Lihat
                    Angg. Peng</label></div>
                <div class="permission-item"><label><input type="checkbox" value="ANGGARAN_PENGELUARAN_MANAGE"> Kelola
                    Angg. Peng</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="ANGGARAN_PENGELUARAN_EXPORT"> Export</label></div>
              </div>
            </div>

            {{-- 3. PENDAPATAN --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-coins"></i> 3. PENDAPATAN</div>
              <div class="sub-group-title">Pendapatan Umum</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_VIEW"> Lihat</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_MANAGE">
                    Kelola</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox"
                      value="REVENUE_MASTER_SYNC">
                    Sinkron (Revenue Master)</label></div>
              </div>
              <div class="sub-group-title">Asuransi & Pihak Ke-3</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_VIEW"> Lihat
                    BPJS</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_MANAGE"> Kelola
                    BPJS</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_VIEW"> Lihat
                    Jaminan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_MANAGE"> Kelola
                    Jaminan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_VIEW"> Lihat
                    Kerja</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_MANAGE"> Kelola
                    Kerja</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_VIEW"> Lihat
                    Lain</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_MANAGE"> Kelola
                    Lain</label></div>
              </div>
              <div class="sub-group-title">Piutang</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="PIUTANG_VIEW"> Lihat Piutang</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="PIUTANG_MANAGE"> Kelola
                    Piutang</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENYESUAIAN_VIEW"> Lihat
                    Penyesuaian</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENYESUAIAN_MANAGE"> Kelola
                    Penyesuaian</label></div>
              </div>
            </div>

            {{-- 4. KAS PENDAPATAN --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-bank"></i> 4. KAS PENDAPATAN</div>
              <div class="sub-group-title">Rekening Koran</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="REKKOR_VIEW"> Lihat</label>
                </div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox" value="REKKOR_MANAGE">
                    Kelola</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="REKKOR_EXPORT">
                    Cetak/Export</label></div>
              </div>
              <div class="sub-group-title">BKU Pendapatan</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="BKU_PENDAPATAN_VIEW"> Lihat</label>
                </div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="BKU_PENDAPATAN_SYNC"> Sinkron</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox"
                      value="BKU_PENDAPATAN_MANAGE"> Kelola (Manual)</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="BKU_PENDAPATAN_EXPORT"> Export</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="BKU_PENDAPATAN_PRINT"> Print</label></div>
              </div>
            </div>

            {{-- 5. MASTER DATA --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-database"></i> 9. MASTER DATA</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="RUANGAN_VIEW"> Lihat Ruangan</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="RUANGAN_MANAGE"> Kelola
                    Ruangan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PERUSAHAAN_VIEW"> Lihat
                    Perusahaan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PERUSAHAAN_MANAGE"> Kelola
                    Perusahaan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="MOU_VIEW"> Lihat MOU</label></div>
                <div class="permission-item"><label><input type="checkbox" value="MOU_MANAGE"> Kelola MOU</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENANDATANGAN_VIEW"> Lihat TTD</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="PENANDATANGAN_MANAGE"> Kelola
                    TTD</label></div>
              </div>
            </div>
          </div>

          {{-- RIGHT COLUMN --}}
          <div>
            {{-- 6. PENGELUARAN --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-hand-coins"></i> 5. PENGELUARAN</div>
              <div class="sub-group-title">SPP / SPM / SP2D</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="SPP_VIEW"> Lihat SPP</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SPP_MANAGE"> Kelola SPP</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SPM_VIEW"> Lihat SPM</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SPM_MANAGE"> Kelola SPM</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SP2D_VIEW"> Lihat SP2D</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SP2D_MANAGE"> Kelola SP2D</label>
                </div>
              </div>
              <div class="sub-group-title">Belanja (Direct) & SPJ</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="BELANJA_VIEW"> Lihat</label></div>
                <div class="permission-item"><label><input type="checkbox" value="BELANJA_MANAGE"> Kelola</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SPJ_VIEW"> Lihat SPJ</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SPJ_MANAGE"> Kelola SPJ</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="SPJ_PRINT">
                    Cetak</label></div>
              </div>
            </div>

            {{-- 7. KAS PENGELUARAN --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-wallet"></i> 6. KAS PENGELUARAN</div>
              <div class="sub-group-title">Rekening Koran</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="REK_KORAN_PENG_VIEW"> Lihat</label>
                </div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox"
                      value="REK_KORAN_PENG_IMPORT"> Import</label></div>
                <div class="permission-item"><label><input type="checkbox" value="REK_KORAN_PENG_MANAGE">
                    Kelola</label></div>
              </div>
              <div class="sub-group-title">BKU Pengeluaran</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="BKU_PENGELUARAN_VIEW"> Lihat</label>
                </div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="BKU_PENGELUARAN_SYNC"> Sinkron</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="BKU_PENGELUARAN_EXPORT"> Export</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="BKU_PENGELUARAN_PRINT"> Print</label></div>
              </div>
            </div>

            {{-- 8. LAPORAN KEUANGAN --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-chart-bar"></i> 7. LAPORAN KEUANGAN</div>
              <div class="sub-group-title">Operasional & Piutang</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="LAP_PENDAPATAN_VIEW">
                    Pendapatan</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="LAP_PENDAPATAN_EXPORT"> Export</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_PENGELUARAN_VIEW">
                    Pengeluaran</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="LAP_PENGELUARAN_EXPORT"> Export</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_REKON_VIEW"> Rekonsiliasi</label>
                </div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_REKON_EXPORT">
                    Export</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_PIUTANG_VIEW"> Piutang</label>
                </div>
                <div class="permission-item"><label class="text-green"><input type="checkbox"
                      value="LAP_PIUTANG_EXPORT"> Export</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_MOU_VIEW"> MOU</label>
                </div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_MOU_EXPORT">
                    Export</label></div>
              </div>
              <div class="sub-group-title">SAP Akrual (LRA / LO / Neraca)</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="LAP_LRA_VIEW"> LRA</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_LRA_EXPORT">
                    Exp</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_LO_VIEW"> LO</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_LO_EXPORT">
                    Exp</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_NERACA_VIEW"> Neraca</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_NERACA_EXPORT">
                    Exp</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox" value="LAP_NERACA_APPROVE">
                    Input (Manual)</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_LAK_VIEW"> LAK</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_LAK_EXPORT">
                    Exp</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_LPE_VIEW"> LPE</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_LPE_EXPORT">
                    Exp</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_LPSAL_VIEW"> LPSAL</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_LPSAL_EXPORT">
                    Exp</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_CALK_VIEW"> CaLK</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox" value="LAP_CALK_APPROVE">
                    Input (Manual)</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_CALK_EXPORT">
                    Exp</label></div>
              </div>
              <div class="sub-group-title">Perencanaan Anggaran</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="LAP_RKA_VIEW"> RKA</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_RKA_EXPORT">
                    Exp</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_RBA_VIEW"> RBA</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_RBA_EXPORT">
                    Exp</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LAP_DPA_VIEW"> DPA</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LAP_DPA_EXPORT">
                    Exp</label></div>
              </div>
            </div>

            {{-- 9. PENGESAHAN --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-seal-check"></i> 8. PENGESAHAN</div>
              <div class="sub-group-title">SP3BP / LRKB / SPTJB</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="SP3BP_VIEW"> SP3BP</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox" value="SP3BP_GENERATE">
                    Gen</label></div>
                <div class="permission-item"><label class="text-red"><input type="checkbox" value="SP3BP_APPROVE">
                    Sah</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="SP3BP_PRINT">
                    Prn</label></div>
                <div class="permission-item"><label><input type="checkbox" value="LRKB_VIEW"> LRKB</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox" value="LRKB_GENERATE">
                    Gen</label></div>
                <div class="permission-item"><label class="text-red"><input type="checkbox" value="LRKB_APPROVE">
                    Sah</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="LRKB_PRINT">
                    Prn</label></div>
                <div class="permission-item"><label><input type="checkbox" value="SPTJB_VIEW"> SPTJB</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox" value="SPTJB_GENERATE">
                    Gen</label></div>
                <div class="permission-item"><label class="text-red"><input type="checkbox" value="SPTJB_APPROVE">
                    Sah</label></div>
                <div class="permission-item"><label class="text-green"><input type="checkbox" value="SPTJB_PRINT">
                    Prn</label></div>
              </div>
            </div>

            {{-- 10. SYSTEM --}}
            <div class="perm-group">
              <div class="perm-group-title"><i class="ph ph-desktop"></i> 10. SYSTEM</div>
              <div class="sub-group-title">User Management</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="USER_VIEW"> Lihat</label></div>
                <div class="permission-item"><label><input type="checkbox" value="USER_MANAGE"> Tambah</label></div>
                <div class="permission-item"><label><input type="checkbox" value="USER_MANAGE"> Edit</label></div>
                <div class="permission-item"><label class="text-red"><input type="checkbox" value="USER_MANAGE">
                    Hapus</label></div>
                <div class="permission-item"><label class="text-blue"><input type="checkbox" value="USER_PERM">
                    Izin</label></div>
              </div>
              <div class="sub-group-title">Activity Log</div>
              <div class="perm-grid">
                <div class="permission-item"><label><input type="checkbox" value="LOG_VIEW"> Lihat Logs</label></div>
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
</div>