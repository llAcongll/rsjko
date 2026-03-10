/* =========================
   PENDAPATAN UMUM JS (MASTER-DETAIL)
========================= */
(function () {
  // Master State
  let masterPage = 1;
  let masterPerPage = 10;
  let masterKeyword = '';
  let masterStatus = '';
  let masterMonth = ''; // Added
  let masterSortBy = 'tanggal';
  let masterSortDir = 'desc';
  let isEditMaster = false;
  let editMasterId = null;
  let selectionAcrossMode = null; // 'DRAFT', 'POSTED', or null
  let totalDraftCount = 0;
  let totalPostedCount = 0;

  // Detail State
  let detailPage = 1;
  let detailPerPage = 10;
  let detailSortBy = 'tanggal';
  let detailSortDir = 'asc';
  let detailKeyword = '';

  window.sortUmum = function (col) {
    if (detailSortBy === col) {
      detailSortDir = (detailSortDir === 'asc' ? 'desc' : 'asc');
    } else {
      detailSortBy = col;
      detailSortDir = 'asc';
    }
    loadPendapatanUmum(1);
  };

  function updateSortIconsDetailUmum() {
    document.querySelectorAll('#pendapatanUmumTable th.sortable i').forEach(icon => {
      icon.className = 'ph ph-caret-up-down text-slate-400';
    });
    const activeHeader = document.querySelector(`#pendapatanUmumTable th.sortable[data-sort="${detailSortBy}"]`);
    if (activeHeader) {
      const icon = activeHeader.querySelector('i');
      if (icon) {
        icon.className = detailSortDir === 'asc' ? 'ph ph-caret-up text-blue-600' : 'ph ph-caret-down text-blue-600';
      }
    }
  }
  let isEditDetail = false;
  let editDetailId = null;
  let activeMasterId = null;
  let activeMasterPosted = false;

  /* =========================
     INITIALIZATION
  ========================= */
  /* INITIALIZATION logic moved to the bottom of IIFE */

  /* =========================
     MASTER LOGIC
  ========================= */
  window.loadMasterUmum = function (page = masterPage) {
    masterPage = page;
    const tbody = document.getElementById('masterTableBodyUmum');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="7" class="text-center" style="padding: 20px;"><i class="ph ph-spinner animate-spin text-2xl"></i></td></tr>';

    const params = new URLSearchParams({
      page: masterPage,
      per_page: masterPerPage,
      search: masterKeyword,
      status: masterStatus, // Added
      month: masterMonth, // Added
      sort_by: masterSortBy,
      sort_dir: masterSortDir,
      kategori: 'UMUM',
      _t: Date.now()
    });

    fetch(`/dashboard/revenue-master?${params.toString()}`, { headers: { Accept: 'application/json' } })
      .then(async res => {
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Gagal memuat master');
        return json;
      })
      .then(res => {
        const data = res.data || [];
        renderPaginationMasterUmum(res);
        renderSummaryMasterUmum(res.aggregates);

        // Update totals for selection functionality
        totalDraftCount = res.total_draft || 0;
        totalPostedCount = res.total_posted || 0;

        if (data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" class="text-center text-slate-500" style="padding: 20px;">Belum ada kelompok pendapatan.</td></tr>';
          return;
        }

        const canEdit = window.hasPermission('PENDAPATAN_UMUM_MANAGE');
        const canDelete = window.hasPermission('PENDAPATAN_UMUM_MANAGE');
        const canPost = window.hasPermission('PENDAPATAN_UMUM_MANAGE');
        let html = '';
        data.forEach((item, index) => {
          const info = `${formatTanggal(item.tanggal)} - ${item.keterangan || 'Pendapatan Umum'}`;
          let isChecked = false;
          if (selectionAcrossMode === 'DRAFT' && !item.is_posted) isChecked = true;
          if (selectionAcrossMode === 'POSTED' && item.is_posted) isChecked = true;

          html += `
          <tr>
            <td class="text-center checkbox-col">
              <input type="checkbox" class="master-checkbox" value="${item.id}" data-posted="${item.is_posted}" onchange="updateSelectionUIUmum()" ${isChecked ? 'checked' : ''} />
            </td>
            <td class="text-center" data-label="Tanggal">
              <div class="font-medium">${formatTanggal(item.tanggal)}</div>
              ${item.tanggal_rk ? `<div class="text-xs text-slate-500">RK: ${formatTanggal(item.tanggal_rk)}</div>` : ''}
            </td>
            <td data-label="Keterangan / No. Bukti">
              <div class="font-medium">${item.keterangan || '-'}</div>
              <div class="text-xs text-slate-500">${item.no_bukti || 'Tanpa No. Bukti'}</div>
            </td>
            <td class="text-right font-medium text-blue-600" data-label="Total Jasa RS">${formatRupiahTable(item.total_rs)}</td>
            <td class="text-right font-medium text-purple-600" data-label="Total Jasa Pelayanan">${formatRupiahTable(item.total_pelayanan)}</td>
            <td class="text-right font-bold text-emerald-600" style="font-size:14px;" data-label="Total Pendapatan">${formatRupiahTable(item.total_all)}</td>
            <td class="text-center" style="white-space: nowrap;" data-label="Status">
              ${item.is_posted
              ? '<span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;"><i class="ph ph-check-circle"></i> Diposting</span>'
              : '<span class="badge badge-warning" style="display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;"><i class="ph ph-clock"></i> Draft</span>'}
            </td>
            <td class="text-center" data-label="Aksi">
              <div class="flex justify-center gap-2">
                <button class="btn-aksi detail" onclick="openDetailUmum(${item.id}, '${escapeHtml(info)}', ${item.is_posted})" title="Lihat Rincian"><i class="ph ph-list-numbers"></i></button>
                ${(canEdit || canDelete || canPost) ? `
                  ${(!item.is_posted && canEdit) ? `
                    <button class="btn-aksi edit" onclick="editMasterUmum(${item.id})" title="Edit"><i class="ph ph-pencil-simple"></i></button>
                  ` : ''}
                  ${(!item.is_posted && canDelete) ? `
                    <button class="btn-aksi delete" onclick="hapusMasterUmum(${item.id})" title="Hapus"><i class="ph ph-trash"></i></button>
                  ` : ''}
                  ${canPost ? `
                    <button class="btn-aksi ${item.is_posted ? 'warning' : 'success'}" onclick="togglePostMasterUmum(${item.id}, ${item.is_posted})" title="${item.is_posted ? 'Batal Posting' : 'Posting'}">
                      <i class="ph ${item.is_posted ? 'ph-x-circle' : 'ph-check-circle'}"></i>
                    </button>
                  ` : ''}
                ` : ''}
              </div>
            </td>
          </tr>
        `;
        });
        tbody.innerHTML = html;
        // Update Header Checkbox and Selection UI
        if (document.getElementById('checkAllMasterUmum')) {
          document.getElementById('checkAllMasterUmum').checked = !!selectionAcrossMode;
        }
        updateSelectionUIUmum();
        updateSortIconsUmum(); // Added here
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-red-500" style="padding: 20px;">${err.message}</td></tr>`;
      });
  };

  function updateSortIconsUmum() {
    document.querySelectorAll('#masterTable th.sortable i').forEach(icon => {
      icon.className = 'ph ph-caret-up-down text-slate-400';
    });
    const activeHeader = document.querySelector(`#masterTable th.sortable[data-sort="${masterSortBy}"]`);
    if (activeHeader) {
      const icon = activeHeader.querySelector('i');
      if (icon) {
        icon.className = masterSortDir === 'asc' ? 'ph ph-caret-up text-blue-600' : 'ph ph-caret-down text-blue-600';
      }
    }
  }

  window.sortMasterUmum = function (column) {
    if (masterSortBy === column) {
      masterSortDir = (masterSortDir === 'asc' ? 'desc' : 'asc');
    } else {
      masterSortBy = column;
      masterSortDir = 'desc';
    }
    loadMasterUmum(1);
  };

  function renderSummaryMasterUmum(agg) {
    if (!agg) return;
    const rs = document.getElementById('masterSummaryRsUmum');
    const pel = document.getElementById('masterSummaryPelayananUmum');
    const tot = document.getElementById('masterSummaryTotalUmum');
    if (rs) rs.innerText = formatRupiah(agg.total_rs || 0);
    if (pel) pel.innerText = formatRupiah(agg.total_pelayanan || 0);
    if (tot) tot.innerText = formatRupiah(agg.total_all || 0);
  }

  function renderSummaryDetailUmum(agg) {
    if (!agg) return;
    const rs = document.getElementById('detailSummaryRsUmum');
    const pel = document.getElementById('detailSummaryPelayananUmum');
    const tot = document.getElementById('detailSummaryTotalUmum');
    if (rs) rs.innerText = formatRupiah(agg.total_rs || 0);
    if (pel) pel.innerText = formatRupiah(agg.total_pelayanan || 0);
    if (tot) tot.innerText = formatRupiah(agg.total_all || 0);
  }
  function renderPaginationMasterUmum(meta) {
    const info = document.getElementById('paginationInfoMasterUmum');
    if (info) info.innerText = `Menampilkan ${meta.from ?? 0}-${meta.to ?? 0} dari ${meta.total ?? 0} data`;

    const pageInfo = document.getElementById('pageInfoMasterUmum');
    if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;

    const prev = document.getElementById('prevPageMasterUmum');
    const next = document.getElementById('nextPageMasterUmum');

    if (prev) {
      prev.disabled = (meta.current_page === 1);
      prev.onclick = () => loadMasterUmum(meta.current_page - 1);
    }
    if (next) {
      next.disabled = (meta.current_page === meta.last_page);
      next.onclick = () => loadMasterUmum(meta.current_page + 1);
    }
  }

  window.openMasterFormUmum = function () {
    const form = document.getElementById('formMasterUmum');
    if (form) form.reset();
    document.getElementById('masterIdUmum').value = '';
    document.getElementById('masterTanggalUmum').value = window.getTodayLocal();
    document.getElementById('masterTanggalRkUmum').value = '';
    document.getElementById('masterFormTitleUmum').innerHTML = '<i class="ph ph-folder-plus"></i> Tambah Kelompok Pendapatan';
    isEditMaster = false;
    document.getElementById('modalMasterFormUmum').classList.add('show');

    const btn = document.getElementById('btnSimpanMasterUmum');
    if (btn) btn.disabled = true;
  };

  window.closeMasterModalUmum = function () {
    document.getElementById('modalMasterFormUmum').classList.remove('show');
  };

  window.editMasterUmum = function (id) {
    fetch(`/dashboard/revenue-master/${id}`, { headers: { Accept: 'application/json' } })
      .then(res => res.json())
      .then(data => {
        isEditMaster = true;
        editMasterId = data.id;
        document.getElementById('masterIdUmum').value = data.id;
        document.getElementById('masterTanggalUmum').value = data.tanggal ? data.tanggal.split('T')[0] : '';
        document.getElementById('masterTanggalRkUmum').value = data.tanggal_rk ? data.tanggal_rk.split('T')[0] : '';
        document.getElementById('masterNoBuktiUmum').value = data.no_bukti || '';
        document.getElementById('masterKeteranganUmum').value = data.keterangan || '';
        document.getElementById('masterFormTitleUmum').innerHTML = '<i class="ph ph-pencil-simple"></i> Edit Kelompok Pendapatan';
        document.getElementById('modalMasterFormUmum').classList.add('show');

        const form = document.getElementById('formMasterUmum');
        const btn = document.getElementById('btnSimpanMasterUmum');
        if (btn && form) btn.disabled = !form.checkValidity();
      });
  };

  window.submitMasterUmum = async function (e) {
    if (e) e.preventDefault();
    const btn = document.getElementById('btnSimpanMasterUmum');
    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    const data = {
      tanggal: document.getElementById('masterTanggalUmum').value,
      tanggal_rk: document.getElementById('masterTanggalRkUmum').value || null,
      kategori: 'UMUM',
      no_bukti: document.getElementById('masterNoBuktiUmum').value,
      keterangan: document.getElementById('masterKeteranganUmum').value
    };

    const url = isEditMaster ? `/dashboard/revenue-master/${editMasterId}` : '/dashboard/revenue-master';
    const method = isEditMaster ? 'PUT' : 'POST';

    try {
      const res = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: JSON.stringify(data)
      });

      if (!res.ok) throw new Error((await res.json()).message || 'Gagal menyimpan');

      toast('Data berhasil disimpan', 'success');
      window.closeMasterModalUmum();
      window.loadMasterUmum();
    } catch (err) {
      toast(err.message, 'error');
    } finally {
      btn.disabled = false;
      btn.innerText = 'Simpan';
    }
  };

  window.hapusMasterUmum = function (id) {
    openConfirm('Hapus Kelompok', 'Hapus kelompok ini? Pastikan Anda sudah mengosongkan/menghapus tabel rincian pasien terlebih dahulu.', async () => {
      try {
        const res = await fetch(`/dashboard/revenue-master/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Gagal menghapus kelompok');

        toast('Berhasil dihapus', 'success');
        window.loadMasterUmum();
      } catch (err) {
        toast(err.message, 'error');
      }
    });
  };

  /* =========================
     TOGGLE POST MASTER
  ========================= */
  window.togglePostMasterUmum = function (id, currentPosted) {
    const title = currentPosted ? 'Batalkan Posting' : 'Posting Kelompok';
    const msg = currentPosted
      ? 'Data rincian akan ditarik dari Rekening Koran dan status kembali ke Draft. Lanjutkan?'
      : 'Data akan diposting ke Rekening Koran dan tidak dapat diubah lagi. Lanjutkan?';

    openConfirm(title, msg, async () => {
      try {
        const res = await fetch(`/dashboard/revenue-master/${id}/toggle-post`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        });
        const data = await res.json();
        if (res.ok) {
          toast(currentPosted ? 'Posting dibatalkan' : 'Berhasil diposting', 'success');
          window.loadMasterUmum();
          if (activeMasterId === id) {
            // Update detail view headers and buttons immediately
            const infoText = document.getElementById('detailMasterInfoUmum')?.innerText || '';
            window.openDetailUmum(id, infoText, !currentPosted);
          }
        } else {
          throw new Error(data.message || 'Gagal mengubah status');
        }
      } catch (err) {
        toast(err.message, 'error');
      }
    }, currentPosted ? 'Batal Posting' : 'Posting', currentPosted ? 'ph-arrow-counter-clockwise' : 'ph-check-circle', currentPosted ? 'btn-warning' : 'btn-primary');
  };

  window.toggleAllMasterUmum = function (source) {
    const checkboxes = document.querySelectorAll('#masterTableBodyUmum .master-checkbox');
    checkboxes.forEach(cb => {
      cb.checked = source.checked;
    });

    if (!source.checked) {
      selectionAcrossMode = null;
    }
    updateSelectionUIUmum();
  };

  window.selectAllPagesAcrossUmum = function (mode) {
    selectionAcrossMode = mode; // 'DRAFT' or 'POSTED'
    const checkboxes = document.querySelectorAll('#masterTableBodyUmum .master-checkbox');
    checkboxes.forEach(cb => {
      const isPosted = cb.getAttribute('data-posted') === 'true';
      if (mode === 'DRAFT' && !isPosted) cb.checked = true;
      if (mode === 'POSTED' && isPosted) cb.checked = true;
    });
    if (document.getElementById('checkAllMasterUmum')) document.getElementById('checkAllMasterUmum').checked = true;
    updateSelectionUIUmum();
  };

  window.clearSelectionAcrossUmum = function () {
    selectionAcrossMode = null;
    const checkboxes = document.querySelectorAll('#masterTableBodyUmum .master-checkbox');
    checkboxes.forEach(cb => { cb.checked = false; });
    if (document.getElementById('checkAllMasterUmum')) document.getElementById('checkAllMasterUmum').checked = false;
    updateSelectionUIUmum();
  };

  function updateSelectionUIUmum() {
    const banner = document.getElementById('selectionBannerUmum');
    const allBanner = document.getElementById('selectionAllBannerUmum');
    if (!banner || !allBanner) return;

    const checkboxes = document.querySelectorAll('#masterTableBodyUmum .master-checkbox');
    const checked = Array.from(checkboxes).filter(cb => cb.checked).length;
    const allInPageChecked = (checkboxes.length > 0 && checked === checkboxes.length);

    banner.style.display = 'none';
    allBanner.style.display = 'none';

    if (selectionAcrossMode) {
      allBanner.style.display = 'block';
      const totalEl = document.getElementById('countTotalDraftSelectedUmum');
      const labelEl = document.getElementById('labelSelectionAllUmum');
      if (totalEl) totalEl.innerText = (selectionAcrossMode === 'DRAFT') ? totalDraftCount : totalPostedCount;
      if (labelEl) labelEl.innerText = (selectionAcrossMode === 'DRAFT') ? 'Draft (Belum Posting)' : 'Posting';
    } else if (allInPageChecked) {
      const hasDraftInPage = Array.from(checkboxes).some(cb => cb.getAttribute('data-posted') === 'false');
      const hasPostedInPage = Array.from(checkboxes).some(cb => cb.getAttribute('data-posted') === 'true');

      let html = `Semua ${checked} kelompok di halaman ini telah terpilih. `;
      if (hasDraftInPage && totalDraftCount > checked) {
        html += `<a href="javascript:void(0)" onclick="selectAllPagesAcrossUmum('DRAFT')" style="font-weight:700; color:#2563eb; text-decoration:underline; margin-right:10px;">Pilih semua ${totalDraftCount} Draft</a>`;
      }
      if (hasPostedInPage && totalPostedCount > checked) {
        html += `<a href="javascript:void(0)" onclick="selectAllPagesAcrossUmum('POSTED')" style="font-weight:700; color:#d97706; text-decoration:underline;">Pilih semua ${totalPostedCount} Posting</a>`;
      }

      if (html.includes('</a>')) {
        banner.innerHTML = html;
        banner.style.display = 'block';
      }
    }
  }

  window.bulkPostMasterUmum = function () {
    const selected = Array.from(document.querySelectorAll('#masterTableBodyUmum .master-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0 && !selectionAcrossMode) {
      toast('Pilih setidaknya satu kelompok untuk diposting', 'warning');
      return;
    }

    openConfirm('Posting Masal', `Apakah Anda yakin ingin memposting ${selectionAcrossMode === 'DRAFT' ? totalDraftCount : selected.length} kelompok pendapatan yang terpilih?`, async () => {
      try {
        const payload = selectionAcrossMode === 'DRAFT'
          ? { all_pages: true, kategori: 'UMUM', search: masterKeyword }
          : { ids: selected };

        const res = await fetch(`/dashboard/revenue-master/bulk-post`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Gagal posting masal');

        let msg = `Berhasil memposting ${data.count || data.posted_count} kelompok.`;
        toast(msg, 'success');
        clearSelectionAcrossUmum();
        loadMasterUmum();
      } catch (err) {
        toast(err.message, 'error');
      }
    }, 'Posting Masal', 'ph-check-circle', 'btn-primary');
  };

  window.bulkUnpostMasterUmum = function () {
    const selected = Array.from(document.querySelectorAll('#masterTableBodyUmum .master-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0 && !selectionAcrossMode) {
      toast('Pilih setidaknya satu kelompok untuk dibatalkan postingnya', 'warning');
      return;
    }

    openConfirm('Batal Posting Masal', `Apakah Anda yakin ingin membatalkan posting ${selectionAcrossMode === 'POSTED' ? totalPostedCount : selected.length} kelompok pendapatan yang terpilih?`, async () => {
      try {
        const payload = selectionAcrossMode === 'POSTED'
          ? { all_pages: true, kategori: 'UMUM', search: masterKeyword }
          : { ids: selected };

        const res = await fetch(`/dashboard/revenue-master/bulk-unpost`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Gagal batal posting masal');

        toast(`Berhasil membatalkan posting ${data.count || data.unposted_count} kelompok.`, 'success');
        clearSelectionAcrossUmum();
        loadMasterUmum();
      } catch (err) {
        toast(err.message, 'error');
      }
    }, 'Batal Posting Masal', 'ph-arrow-counter-clockwise', 'btn-warning');
  };

  /* =========================
     TOGGLE MASTER-DETAIL VIEW
  ========================= */
  window.openDetailUmum = function (masterId, infoText, isPosted = false) {
    activeMasterId = masterId;
    activeMasterPosted = isPosted;
    if (infoText) {
      const infoEl = document.getElementById('detailMasterInfoUmum');
      if (infoEl) infoEl.innerText = infoText;
    }

    // Sembunyikan tombol-tombol yang memungkinkan modifikasi jika sudah posted
    const btnTambahPendapatanUmum = document.getElementById('btnTambahPendapatanUmum');
    const btnImportUmum = document.getElementById('btnImportUmum');
    const btnBulkDeleteUmum = document.getElementById('btnBulkDeleteUmum');
    if (btnTambahPendapatanUmum) btnTambahPendapatanUmum.style.display = isPosted ? 'none' : 'flex';
    if (btnImportUmum) btnImportUmum.style.display = isPosted ? 'none' : 'inline-flex';
    if (btnBulkDeleteUmum) btnBulkDeleteUmum.style.display = isPosted ? 'none' : 'inline-flex';

    // Ubah tanggal form detail untuk otomatis mengikuti tanggal master atau hari ini
    const masterSec = document.getElementById('masterListSectionUmum');
    const detailSec = document.getElementById('detailListSectionUmum');
    if (masterSec) masterSec.style.display = 'none';
    if (detailSec) detailSec.style.display = 'block';

    detailKeyword = '';
    if (document.getElementById('searchPendapatanUmum')) {
      document.getElementById('searchPendapatanUmum').value = '';
    }

    loadPendapatanUmum(1);
  };

  window.closeDetailUmum = function () {
    activeMasterId = null;
    activeMasterPosted = false;
    const detailSec = document.getElementById('detailListSectionUmum');
    const masterSec = document.getElementById('masterListSectionUmum');
    if (detailSec) detailSec.style.display = 'none';
    if (masterSec) masterSec.style.display = 'block';
    // Reload master to parse updated aggregates
    loadMasterUmum();
  };

  /* =========================
     DETAIL (PENDAPATAN UMUM) LOGIC
  ========================= */
  window.loadPendapatanUmum = function (page = detailPage) {
    detailPage = page;
    const tbody = document.getElementById('pendapatanUmumBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding: 20px;"><i class="ph ph-spinner animate-spin"></i> Memuat rincian...</td></tr>';

    const params = new URLSearchParams({
      page: detailPage,
      per_page: detailPerPage,
      search: detailKeyword,
      sort_by: detailSortBy,
      sort_dir: detailSortDir,
      revenue_master_id: activeMasterId,
      _t: Date.now()
    });

    fetch(`/dashboard/pendapatan/umum?${params.toString()}`, { headers: { Accept: 'application/json' } })
      .then(async res => {
        const json = await res.json();
        if (!res.ok) throw new Error(json.message);
        return json;
      })
      .then(res => {
        const data = res.data || [];
        renderPaginationUmum(res);
        renderSummaryDetailUmum(res.aggregates);
        updateSortIconsDetailUmum();

        if (data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" class="text-center text-slate-500" style="padding: 20px;">Belum ada rincian pasien.</td></tr>';
          return;
        }

        const canCreateDetail = window.hasPermission('PENDAPATAN_UMUM_MANAGE') || window.isAdmin;
        const canDeleteDetail = window.hasPermission('PENDAPATAN_UMUM_MANAGE') || window.isAdmin;

        let html = '';

        data.forEach((item, index) => {
          html += `
          <tr>
            <td class="text-center" data-label="No">${res.from + index}</td>
            <td class="text-center" data-label="Tanggal">${formatTanggal(item.tanggal)}</td>
            <td class="font-medium" data-label="Nama Pasien">${escapeHtml(item.nama_pasien)}</td>
            <td data-label="Ruangan"><span class="badge badge-info">${item.ruangan?.nama ?? '-'}</span></td>
            <td class="text-right font-medium text-blue-600" data-label="Jasa RS">${formatRupiahTable((parseFloat(item.rs_tindakan) || 0) + (parseFloat(item.rs_obat) || 0))}</td>
            <td class="text-right font-medium text-purple-600" data-label="Jasa Pelayanan">${formatRupiahTable((parseFloat(item.pelayanan_tindakan) || 0) + (parseFloat(item.pelayanan_obat) || 0))}</td>
            <td class="text-right font-bold text-emerald-600" style="font-size:14px;" data-label="Total">${formatRupiahTable(item.total)}</td>
            <td class="text-center" data-label="Aksi">
              <div class="flex justify-center gap-2">
                <button class="btn-aksi detail" onclick="detailPendapatanUmum(${item.id})"><i class="ph ph-eye"></i></button>
                ${!activeMasterPosted ? `
                  ${canCreateDetail ? `
                    <button class="btn-aksi edit" onclick="editPendapatanUmum(${item.id})" title="Edit"><i class="ph ph-pencil-simple"></i></button>
                  ` : ''}
                  ${canDeleteDetail ? `
                    <button class="btn-aksi delete" onclick="hapusPendapatanUmum(${item.id})" title="Hapus"><i class="ph ph-trash"></i></button>
                  ` : ''}
                ` : ''}
              </div>
            </td>
          </tr>
        `;
        });
        tbody.innerHTML = html;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-red-500">${err.message}</td></tr>`;
      });
  };

  function renderPaginationUmum(meta) {
    const info = document.getElementById('paginationInfoUmum');
    if (info) info.innerText = `Menampilkan ${meta.from ?? 0}-${meta.to ?? 0} dari ${meta.total ?? 0} data`;

    const pageInfo = document.getElementById('pageInfoUmum');
    if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;

    const prev = document.getElementById('prevPageUmum');
    const next = document.getElementById('nextPageUmum');

    if (prev) {
      prev.disabled = (meta.current_page === 1);
      prev.onclick = () => loadPendapatanUmum(meta.current_page - 1);
    }
    if (next) {
      next.disabled = (meta.current_page === meta.last_page);
      next.onclick = () => loadPendapatanUmum(meta.current_page + 1);
    }
  }

  window.openPendapatanModal = function () {
    const modal = document.getElementById('pendapatanUmumModal');
    if (!modal) return;

    const form = document.getElementById('formPendapatanUmum');
    if (form) form.reset();
    isEditDetail = false;

    // Set default values
    if (form) {
      const tanggalInput = form.querySelector('[name="tanggal"]');
      if (tanggalInput) tanggalInput.value = window.getTodayLocal();
    }
    document.querySelectorAll('.nominal-display').forEach(i => i.value = '0');
    document.querySelectorAll('.nominal-value').forEach(i => i.value = '0');
    const totalEl = document.getElementById('totalPembayaran');
    if (totalEl) totalEl.innerText = 'Rp 0';
    const titleEl = document.querySelector('.modal-title');
    if (titleEl) titleEl.innerText = 'Ã¢Å¾â€¢ Tambah Pasien Umum';

    modal.classList.add('show');
    loadRuangan();

    const btnSimpan = document.getElementById('btnSimpanPendapatan');
    if (btnSimpan) btnSimpan.disabled = true;
  };

  window.closePendapatanModal = function () {
    document.getElementById('pendapatanUmumModal')?.classList.remove('show');
  };

  window.submitPendapatanUmum = async function (e) {
    e.preventDefault();
    const form = document.getElementById('formPendapatanUmum');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const btnSimpan = document.getElementById('btnSimpanPendapatan');
    btnSimpan.disabled = true;
    btnSimpan.innerText = 'Ã¢³ Menyimpan...';

    const formData = new FormData(form);
    // Add activeMasterId
    formData.append('revenue_master_id', activeMasterId);

    if (isEditDetail) {
      formData.append('_method', 'PUT');
    }

    const metode = formData.get('metode_pembayaran');
    if (metode !== 'NON_TUNAI') {
      formData.delete('bank');
      formData.delete('metode_detail');
    }

    const url = isEditDetail ? `/dashboard/pendapatan/umum/${editDetailId}` : `/dashboard/pendapatan/umum`;

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: formData
      });

      if (!res.ok) throw new Error((await res.json()).message || 'Gagal menyimpan');

      toast('Berhasil disimpan', 'success');
      closePendapatanModal();
      loadPendapatanUmum();
    } catch (err) {
      toast(err.message, 'error');
    } finally {
      btnSimpan.disabled = false;
      btnSimpan.innerText = 'Ã°Å¸â€™¾ Simpan';
    }
  };

  window.editPendapatanUmum = function (id) {
    isEditDetail = true;
    editDetailId = id;

    Promise.all([
      (document.getElementById('pendapatanUmumModal').classList.add('show'), loadRuangan()),
      fetch(`/dashboard/pendapatan/umum/${id}`, { headers: { Accept: 'application/json' } }).then(res => res.json())
    ]).then(([_, data]) => {
      const form = document.getElementById('formPendapatanUmum');
      form.querySelector('[name="tanggal"]').value = data.tanggal.split('T')[0];
      form.querySelector('[name="nama_pasien"]').value = data.nama_pasien;
      form.querySelector('[name="ruangan_id"]').value = data.ruangan_id;

      const metode = form.querySelector('[name="metode_pembayaran"]');
      metode.value = data.metode_pembayaran;
      metode.dispatchEvent(new Event('change'));

      setTimeout(() => {
        if (data.bank) {
          form.querySelector('[name="bank"]').value = data.bank;
          form.querySelector('[name="bank"]').dispatchEvent(new Event('change'));
        }
        setTimeout(() => {
          if (data.metode_detail) form.querySelector('[name="metode_detail"]').value = data.metode_detail;
          cekSiapSimpan();
        }, 50);
      }, 100);

      form.querySelector('[name="rs_tindakan"]').value = data.rs_tindakan;
      form.querySelector('[name="rs_obat"]').value = data.rs_obat;
      form.querySelector('[name="pelayanan_tindakan"]').value = data.pelayanan_tindakan;
      form.querySelector('[name="pelayanan_obat"]').value = data.pelayanan_obat;

      form.querySelectorAll('.nominal-display').forEach((disp, i) => {
        const val = form.querySelectorAll('.nominal-value')[i].value;
        disp.value = formatRibuan(val);
      });

      hitungTotal();
      cekSiapSimpan();
      document.querySelector('.modal-title').innerText = 'Ã¢Å“Ã¯¸ Edit Pasien Umum';
    });
  };

  window.hapusPendapatanUmum = function (id) {
    if (window.activeMasterPosted) {
      toast('Tidak dapat menghapus data pada kelompok yang sudah diposting.', 'warning');
      return;
    }
    openConfirm('Hapus Data', 'Yakin ingin menghapus pasien ini?', async () => {
      try {
        const res = await fetch(`/dashboard/pendapatan/umum/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        });
        if (!res.ok) throw new Error('Gagal menghapus pasien');

        toast('Pasien berhasil dihapus', 'success');
        loadPendapatanUmum();
      } catch (err) {
        toast(err.message, 'error');
      }
    });
  };

  /* =========================
     HAPUS MASSAL RINCIAN
  ========================= */
  window.hapusMassalPendapatanUmum = function () {
    if (window.activeMasterPosted) {
      toast('Tidak dapat menghapus massal pada rincian kelompok yang sudah diposting.', 'warning');
      return;
    }

    if (!activeMasterId) return;

    openConfirm('Hapus Massal Rincian', `Yakin ingin menghapus SEMUA data rincian pada kelompok pendapatan ini?`, async () => {
      try {
        const res = await fetch(`/dashboard/pendapatan/umum/bulk-delete`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json'
          },
          body: JSON.stringify({ revenue_master_id: activeMasterId })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Gagal hapus massal rincian');

        toast(`Berhasil menghapus ${data.count || 0} data rincian`, 'success');
        loadPendapatanUmum(1);
      } catch (err) {
        toast(err.message, 'error');
      }
    });
  };

  /* =========================
     HELPERS / UI LOGIC
  ========================= */
  function renderSummaryPendapatan(agg) {
    if (!agg) return;
    const setText = (sel, val) => { const el = document.getElementById(sel); if (el) el.innerText = val; };
    setText('masterSummaryRs', formatRupiah(agg.total_rs));
    setText('masterSummaryPelayanan', formatRupiah(agg.total_pelayanan));
    setText('masterSummaryTotal', formatRupiah(agg.total_all));
  }

  function renderSummaryPendapatanDetail(agg) {
    if (!agg) return;
    const setText = (sel, val) => { const el = document.getElementById(sel); if (el) el.innerText = val; };
    setText('detailSummaryRs', formatRupiah(agg.total_rs));
    setText('detailSummaryPelayanan', formatRupiah(agg.total_pelayanan));
    setText('detailSummaryTotal', formatRupiah(agg.total_all));
  }

  function hitungTotal() {
    let total = 0;
    document.getElementById('formPendapatanUmum').querySelectorAll('.nominal-value').forEach(i => total += parseFloat(i.value || 0));
    const el = document.getElementById('totalPembayaran');
    if (el) el.innerText = formatRupiah(total);
  }

  window.cekSiapSimpan = function () {
    const form = document.getElementById('formPendapatanUmum');
    if (!form) return;

    const tanggal = form.querySelector('[name="tanggal"]')?.value;
    const nama = form.querySelector('[name="nama_pasien"]')?.value;
    const ruangan = form.querySelector('[name="ruangan_id"]')?.value;
    const metode = form.querySelector('[name="metode_pembayaran"]')?.value;
    const bank = form.querySelector('[name="bank"]')?.value;
    const detail = form.querySelector('[name="metode_detail"]')?.value;

    // Check if total nominal > 0
    let totalNominal = 0;
    form.querySelectorAll('.nominal-value').forEach(input => {
      totalNominal += parseFloat(input.value || 0);
    });

    let valid = !!(tanggal && nama && ruangan && metode && totalNominal > 0);

    if (metode === 'NON_TUNAI') {
      if (!bank || !detail) {
        valid = false;
      }
    }

    const btn = document.getElementById('btnSimpanPendapatan');
    if (btn) {
      btn.disabled = !valid;
      // Also apply a visual style for clarity
      if (valid) {
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
      } else {
        btn.style.opacity = '0.6';
        btn.style.cursor = 'not-allowed';
      }
    }
  };

  function setupBankLogic() {
    const metode = document.getElementById('metodePembayaran');
    const bank = document.getElementById('bank');
    const detail = document.getElementById('metodeDetail');

    if (!metode || !bank || !detail) return;

    metode.addEventListener('change', () => {
      resetSelect(bank, '-- Pilih Bank --');
      resetSelect(detail, '-- Metode Detail --');
      if (metode.value === 'TUNAI') {
        bank.disabled = detail.disabled = true;
        addOption(bank, { value: 'BRK', label: 'Bank Riau Kepri Syariah' }); bank.value = 'BRK';
        addOption(detail, { value: 'SETOR_TUNAI', label: 'Setor Tunai' }); detail.value = 'SETOR_TUNAI';
      } else if (metode.value === 'NON_TUNAI') {
        bank.disabled = false; bank.removeAttribute('readonly');
        detail.disabled = true;
        addOption(bank, { value: 'BRK', label: 'Bank Riau Kepri Syariah' });
        addOption(bank, { value: 'BSI', label: 'Bank Syariah Indonesia' });
      } else {
        bank.disabled = detail.disabled = true;
      }
      cekSiapSimpan();
    });

    bank.addEventListener('change', () => {
      if (metode.value !== 'NON_TUNAI') return;
      resetSelect(detail, '-- Metode Detail --');
      if (bank.value) {
        detail.disabled = false; detail.removeAttribute('readonly');
        addOption(detail, { value: 'QRIS', label: 'QRIS' });
        addOption(detail, { value: 'TRANSFER', label: 'Transfer' });
      } else { detail.disabled = true; }
      cekSiapSimpan();
    });

    detail.addEventListener('change', cekSiapSimpan);
  }

  function resetSelect(el, defaultText) {
    if (!el) return;
    el.innerHTML = `<option value="">${defaultText}</option>`;
  }
  function addOption(el, opt) {
    if (!el) return;
    el.insertAdjacentHTML('beforeend', `<option value="${opt.value}">${opt.label}</option>`);
  }

  async function loadRuangan() {
    const select = document.getElementById('ruanganSelect');
    if (!select || select.options.length > 1) return;
    select.innerHTML = '<option value="">Memuat...</option>';
    try {
      const res = await fetch('/dashboard/ruangan-list');
      const data = await res.json();
      select.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
      data.forEach(r => select.insertAdjacentHTML('beforeend', `<option value="${r.id}">${r.kode} - ${r.nama}</option>`));
      select.disabled = false;
    } catch { select.innerHTML = '<option value="">Gagal memuat</option>'; }
  }

  window.detailPendapatanUmum = function (id) {
    // Assuming the modal detail is already generated by the blade partial
    const modal = document.getElementById('pendapatanDetailModal');
    const content = document.getElementById('detailPendapatanContent');
    if (!modal || !content) return;

    modal.classList.add('show');

    content.innerHTML = `
    <div class="flex flex-col items-center justify-center py-8 text-slate-500">
        <i class="ph ph-spinner animate-spin text-3xl mb-2"></i>
        <p>Memuat detail...</p>
    </div>`;

    fetch(`/dashboard/pendapatan/umum/${id}`, { headers: { Accept: 'application/json' } })
      .then(res => res.json())
      .then(data => {
        const bankLabel = data.bank === 'BRK' ? 'Bank Riau Kepri Syariah' : (data.bank === 'BSI' ? 'Bank Syariah Indonesia' : (data.bank || '-'));
        content.innerHTML = `
        <div class="detail-row"><span class="label">Tanggal</span><span class="value">${formatTanggal(data.tanggal)}</span></div>
        <div class="detail-row"><span class="label">Pasien</span><span class="value font-medium">${escapeHtml(data.nama_pasien)}</span></div>
        <div class="detail-row"><span class="label">Ruangan</span><span class="value">${data.ruangan?.nama ?? '-'}</span></div>
        <div class="detail-row"><span class="label">Metode</span>
            <span class="value">
                <span class="badge ${data.metode_pembayaran === 'TUNAI' ? 'tunai' : 'non-tunai'}">${data.metode_pembayaran}</span>
                ${data.metode_pembayaran === 'NON_TUNAI' ? `<div class="text-xs text-slate-500 mt-1">${bankLabel} - ${data.metode_detail}</div>` : ''}
            </span>
        </div>
        <div class="my-4 border-t border-slate-100"></div>
        <div class="detail-row"><span class="label">RS Tindakan</span><span class="value">${formatRupiah(data.rs_tindakan)}</span></div>
        <div class="detail-row"><span class="label">RS Obat</span><span class="value">${formatRupiah(data.rs_obat)}</span></div>
        <div class="detail-row"><span class="label">Pelayanan Tindakan</span><span class="value">${formatRupiah(data.pelayanan_tindakan)}</span></div>
        <div class="detail-row"><span class="label">Pelayanan Obat</span><span class="value">${formatRupiah(data.pelayanan_obat)}</span></div>
        <div class="detail-total mt-4"><span>Total Pendapatan</span><strong>${formatRupiah(data.total)}</strong></div>
      `;
      });
  };
  window.closeDetailPendapatan = function () { document.getElementById('pendapatanDetailModal')?.classList.remove('show'); };
  function initImportUmum() {
    const btnImport = document.getElementById('btnImportUmum');
    if (btnImport) btnImport.onclick = () => document.getElementById('modalImportUmum').classList.add('show');

    const formImport = document.getElementById('formImportUmum');
    if (formImport) {
      formImport.onsubmit = async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerText = 'Mengimport...';

        const formData = new FormData(formImport);
        formData.append('revenue_master_id', activeMasterId);

        try {
          const res = await fetch('/dashboard/pendapatan/umum/import', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json'
            },
            body: formData
          });
          const data = await res.json();
          if (data.success) {
            toast(`Berhasil mengimpor ${data.count} data`, 'success');
            document.getElementById('modalImportUmum').classList.remove('show');
            formImport.reset();
            loadPendapatanUmum(1);
          } else {
            throw new Error(data.message || 'Gagal import');
          }
        } catch (err) {
          toast(err.message, 'error');
        } finally {
          btn.disabled = false;
          btn.innerText = 'Mulai Import';
        }
      };
    }
  }

  window.syncOldData = function () {
    openConfirm('Sinkronisasi Data Lama', 'Sistem akan mencari seluruh data pendapatan yang belum terkelompokkan dan memasukkannya ke kelompok master secara otomatis berdasarkan tanggal. Lanjutkan?', async () => {
      try {
        const btn = document.querySelector('button[onclick="syncOldData()"]');
        if (btn) {
          btn.disabled = true;
          btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Sinkronisasi...';
        }

        const res = await fetch('/dashboard/revenue-master/sync', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Gagal sinkronisasi');

        toast(data.message || 'Berhasil sinkronisasi data', 'success');
        loadMasterUmum(1);
      } catch (err) {
        toast(err.message, 'error');
      } finally {
        const btn = document.querySelector('button[onclick="syncOldData()"]');
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = '<i class="ph ph-arrows-counter-clockwise"></i> <span>Sinkronisasi Data Lama</span>';
        }
      }
    }, 'Sinkronisasi', 'ph-arrows-counter-clockwise', 'btn-primary');
  };

  function initBulkDeleteUmum() {
    const btnBulk = document.getElementById('btnBulkDeleteUmum');
    if (btnBulk) {
      btnBulk.onclick = () => {
        hapusMassalPendapatanUmum();
      };
    }
  }

  window.initPendapatanUmum = function () {
    const formMasterUmum = document.getElementById('formMasterUmum');
    if (formMasterUmum) {
      formMasterUmum.onsubmit = submitMasterUmum;
    }

    loadMasterUmum(1);

    // Search Master (Top & Bottom Sync)
    const searchMasterUmumInputs = [
      document.getElementById('searchMasterUmum'),
      document.getElementById('searchMasterUmumBottom')
    ];
    searchMasterUmumInputs.forEach(input => {
      if (input) {
        let timer;
        input.oninput = (e) => {
          const val = e.target.value.trim();
          masterKeyword = val;
          searchMasterUmumInputs.forEach(other => { if (other && other !== e.target) other.value = val; });
          clearTimeout(timer);
          timer = setTimeout(() => { loadMasterUmum(1); }, 400);
        };
      }
    });

    // Filter Status Master (Top & Bottom Sync)
    const filterStatusMasterUmumSelects = [
      document.getElementById('filterStatusMasterUmum'),
      document.getElementById('filterStatusMasterUmumBottom')
    ];
    filterStatusMasterUmumSelects.forEach(sel => {
      if (sel) {
        sel.onchange = (e) => {
          masterStatus = e.target.value;
          filterStatusMasterUmumSelects.forEach(other => { if (other && other !== e.target) other.value = e.target.value; });
          loadMasterUmum(1);
        };
      }
    });

    // Filter Month Master (Top & Bottom Sync)
    const filterMonthMasterUmumInputs = [
      document.getElementById('filterMonthMasterUmum'),
      document.getElementById('filterMonthMasterUmumBottom')
    ];
    filterMonthMasterUmumInputs.forEach(input => {
      if (input) {
        input.onchange = (e) => {
          masterMonth = e.target.value;
          filterMonthMasterUmumInputs.forEach(other => { if (other && other !== e.target) other.value = e.target.value; });
          loadMasterUmum(1);
        };
      }
    });

    // Search Detail (Top & Bottom Sync)
    const searchUmumInputs = [
      document.getElementById('searchPendapatanUmum'),
      document.getElementById('searchPendapatanUmumBottom')
    ];
    searchUmumInputs.forEach(input => {
      if (input) {
        let timer;
        input.oninput = (e) => {
          const val = e.target.value.trim();
          detailKeyword = val;
          searchUmumInputs.forEach(other => { if (other && other !== e.target) other.value = val; });
          clearTimeout(timer);
          timer = setTimeout(() => { loadPendapatanUmum(1); }, 400);
        };
      }
    });

    const btnTambah = document.getElementById('btnTambahPendapatanUmum');
    if (btnTambah) {
      btnTambah.onclick = () => {
        openPendapatanModal();
      };
    }

    // Bind nominal logic for detail form
    document.querySelectorAll('.nominal-display').forEach(input => {
      input.addEventListener('input', () => {
        const val = parseAngka(input.value);
        const hidden = input.closest('.input-group').querySelector('.nominal-value');
        if (hidden) hidden.value = val;
        hitungTotal();
      });
      input.addEventListener('blur', () => {
        let val = parseAngka(input.value);
        input.value = formatRibuan(val);
      });
      input.addEventListener('focus', () => {
        let val = parseAngka(input.value);
        input.value = val === 0 ? '' : val.toString().replace('.', ',');
      });
    });

    setupBankLogic();

    const formUmum = document.getElementById('formPendapatanUmum');
    if (formUmum) {
      formUmum.addEventListener('input', cekSiapSimpan);
      formUmum.addEventListener('change', cekSiapSimpan);
    }

    initImportUmum();
    initBulkDeleteUmum();

    // Form Validation for Master Group (Kelompok)
    const formMasterUmumVal = document.getElementById('formMasterUmum');
    if (formMasterUmumVal) {
      const validateMasterForm = () => {
        const btn = document.getElementById('btnSimpanMasterUmum');
        if (btn) btn.disabled = !formMasterUmumVal.checkValidity();
      };
      formMasterUmumVal.addEventListener('input', validateMasterForm);
      formMasterUmumVal.addEventListener('change', validateMasterForm);
    }
  };

})();




