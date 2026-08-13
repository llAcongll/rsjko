<div class="page-container" style="padding: 20px;">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div class="page-header-left">
      <h2 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
        <i class="ph ph-shield-plus" style="color: #3b82f6;"></i> Monitoring Skrining SAFE SPACE
      </h2>
      <p style="color: #64748b; font-size: 14px; margin: 0;">Dashboard monitoring hasil skrining mental siswa</p>
    </div>
    <div class="page-header-right" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
      <select id="safeSpacePeriod" class="form-select" onchange="toggleSSCustomDate(); loadSafeSpaceStats()" style="padding: 8px 32px 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; cursor: pointer; background-color: #fff;">
        <option value="all">Semua Periode</option>
        <option value="today">Hari Ini</option>
        <option value="this_week">Minggu Ini</option>
        <option value="this_month">Bulan Ini</option>
        <option value="this_year">Tahun Ini</option>
        <option value="custom">Custom</option>
      </select>

      <div id="ssCustomDateContainer" style="display: none; gap: 8px; align-items: center;">
        <input type="date" id="ssStartDate" class="form-input" style="padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
        <span style="color: #64748b;">-</span>
        <input type="date" id="ssEndDate" class="form-input" style="padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
        <button onclick="applySSCustomDate()" style="padding: 7px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-size: 14px; white-space: nowrap; cursor: pointer;">Terapkan</button>
      </div>

      @if(auth()->user()->role === 'ADMIN')
      <button onclick="confirmDeleteToday()" style="padding: 7px 16px; border: 1px solid #ef4444; background: #fef2f2; color: #ef4444; border-radius: 6px; font-size: 14px; white-space: nowrap; cursor: pointer; margin-left: 10px; display: flex; align-items: center; gap: 6px;">
        <i class="ph ph-trash"></i> Hapus Data Hari Ini
      </button>
      @endif
    </div>
  </div>

  <div id="safeSpaceLoading" style="display: none; padding: 60px; text-align: center;">
    <i class="ph ph-spinner animate-spin" style="font-size: 40px; color: #3b82f6;"></i>
    <p style="margin-top: 12px; color: #64748b; font-weight: 500;">Memuat data statistik...</p>
  </div>

  <div id="safeSpaceError" style="display: none; padding: 60px; text-align: center; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <i class="ph ph-warning-circle" style="font-size: 48px; color: #ef4444;"></i>
    <p style="margin-top: 16px; color: #334155; font-size: 16px;">Gagal memuat data statistik.</p>
    <button onclick="loadSafeSpaceStats()" style="margin-top: 16px; padding: 8px 16px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Coba Lagi</button>
  </div>

  <div id="safeSpaceContent" style="display: none;">
    <!-- TOTAL CARD -->
    <div class="dashboard-box" style="margin-bottom: 24px; display: flex; align-items: center; padding: 24px; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
      <div style="background: #eff6ff; color: #3b82f6; padding: 16px; border-radius: 12px; margin-right: 20px;">
        <i class="ph ph-users" style="font-size: 32px;"></i>
      </div>
      <div>
        <h4 style="color: #64748b; margin-bottom: 4px; font-weight: 500; font-size: 14px;">Total siswa melakukan skrining</h4>
        <h2 id="ss-total" style="font-size: 32px; font-weight: 700; color: #0f172a; margin: 0;">0</h2>
      </div>
    </div>

    <!-- CHARTS GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
      
      <!-- ANSIETAS -->
      <div class="dashboard-box" style="padding: 24px; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h4 style="font-weight: 600; margin-top: 0; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; font-size: 16px; color: #1e293b;">
          <i class="ph ph-brain" style="color: #8b5cf6; font-size: 20px;"></i> Ansietas (Kecemasan)
        </h4>
        <div style="position: relative; height: 220px; margin-bottom: 24px;">
          <canvas id="chartAnxiety"></canvas>
        </div>
        <div class="stat-list" id="listAnxiety" style="font-size: 14px; color: #334155;">
          <!-- Populated via JS -->
        </div>
      </div>

      <!-- DEPRESI -->
      <div class="dashboard-box" style="padding: 24px; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h4 style="font-weight: 600; margin-top: 0; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; font-size: 16px; color: #1e293b;">
          <i class="ph ph-cloud-moon" style="color: #ec4899; font-size: 20px;"></i> Depresi
        </h4>
        <div style="position: relative; height: 220px; margin-bottom: 24px;">
          <canvas id="chartDepression"></canvas>
        </div>
        <div class="stat-list" id="listDepression" style="font-size: 14px; color: #334155;">
          <!-- Populated via JS -->
        </div>
      </div>

      <!-- SAFETY -->
      <div class="dashboard-box" style="padding: 24px; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h4 style="font-weight: 600; margin-top: 0; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; font-size: 16px; color: #1e293b;">
          <i class="ph ph-lifebuoy" style="color: #f59e0b; font-size: 20px;"></i> Safety (Risiko Melukai Diri)
        </h4>
        <div style="position: relative; height: 220px; margin-bottom: 24px;">
          <canvas id="chartSafety"></canvas>
        </div>
        <div class="stat-list" id="listSafety" style="font-size: 14px; color: #334155;">
          <!-- Populated via JS -->
        </div>
        <div id="listSafetyStatus" style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed #cbd5e1; display: none;">
           <!-- Populated via JS -->
        </div>
      </div>

    </div>
  </div>
</div>
