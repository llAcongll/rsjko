(function () {
    let masterPage = 1;
    let masterPerPage = 10;
    let masterKeyword = '';
    let masterStatus = ''; // Added
    let masterSortBy = 'tanggal'; // Added
    let masterSortDir = 'desc'; // Added
    let selectedMasterId = null;
    let currentMasterData = null;

    let bpjsPage = 1;
    let bpjsPerPage = 10;
    let bpjsSortBy = 'tanggal';
    let bpjsSortDir = 'asc';
    let bpjsKeyword = '';

    window.sortBpjs = function (col) {
        if (bpjsSortBy === col) {
            bpjsSortDir = (bpjsSortDir === 'asc' ? 'desc' : 'asc');
        } else {
            bpjsSortBy = col;
            bpjsSortDir = 'asc';
        }
        loadPendapatanBpjs(1);
    };

    function updateSortIconsDetailBpjs() {
        document.querySelectorAll('#pendapatanBpjsTable th.sortable i').forEach(icon => {
            icon.className = 'ph ph-caret-up-down text-slate-400';
        });
        const activeHeader = document.querySelector(`#pendapatanBpjsTable th.sortable[data-sort="${bpjsSortBy}"]`);
        if (activeHeader) {
            const icon = activeHeader.querySelector('i');
            if (icon) {
                icon.className = bpjsSortDir === 'asc' ? 'ph ph-caret-up text-blue-600' : 'ph ph-caret-down text-blue-600';
            }
        }
    }
    let currentBpjsTab = 'REGULAR'; // REGULAR, EVAKUASI, OBAT
    let isEditBpjs = false;
    let editBpjsId = null;
    let activeMasterPosted = false;

    // Multi-selection across pages state
    let selectionAcrossMode = 'DRAFT'; // DRAFT or POSTED
    let selectedMasterIds = [];
    let isSelectAllPagesAcross = false;
    let totalDraftCount = 0;
    let totalPostedCount = 0;

    // Global Caches
    window._cacheRuangan = window._cacheRuangan || null;
    window._cachePerusahaan = window._cachePerusahaan || null;

    /* =========================
       MASTER LIST LOGIC
    ========================= */
    function loadMasterBpjs(page = masterPage) {
        masterPage = page;
        const tbody = document.getElementById('masterTableBodyBpjs');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Memuat data...</td></tr>';

        const params = new URLSearchParams({
            page: masterPage,
            per_page: masterPerPage,
            search: masterKeyword,
            status: masterStatus, // Added
            sort_by: masterSortBy, // Added
            sort_dir: masterSortDir, // Added
            kategori: 'BPJS',
            _t: Date.now()
        });

        fetch(`/dashboard/revenue-master?${params.toString()}`, {
            headers: { Accept: 'application/json' }
        })
            .then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal memuat master');
                return json;
            })
            .then(res => {
                const data = res.data || [];
                totalDraftCount = res.total_draft || 0;
                totalPostedCount = res.total_posted || 0;
                renderPaginationMasterBpjs(res);
                renderMasterSummaryBpjs(res.aggregates);
                updateSelectionUIBpjs();
                updateSortIconsBpjs();

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-slate-500">Belum ada kelompok pendapatan BPJS</td></tr>';
                    return;
                }

                let html = '';
                data.forEach((item, index) => {
                    const info = `${formatTanggal(item.tanggal)} ${item.no_bukti ? `(${item.no_bukti})` : ''} - ${item.keterangan || ''}`;
                    const isPosted = !!item.is_posted;
                    const statusBadge = isPosted
                        ? '<span class="badge badge-success" style="display:inline-flex; align-items:center; gap:4px; white-space:nowrap;"><i class="ph-bold ph-check-circle"></i> Diposting</span>'
                        : '<span class="badge badge-warning">Draft</span>';

                    const canEdit = window.hasPermission('PENDAPATAN_BPJS_CREATE') || window.hasPermission('PENDAPATAN_BPJS_CRUD') || window.isAdmin;
                    const canDelete = window.hasPermission('PENDAPATAN_BPJS_DELETE') || window.hasPermission('PENDAPATAN_BPJS_CRUD') || window.isAdmin;
                    const canPost = window.hasPermission('PENDAPATAN_BPJS_POST') || window.isAdmin;
                    const isSelected = selectedMasterIds.includes(item.id);

                    html += `
                    <tr class="${selectedMasterId === item.id ? 'bg-blue-50' : ''}">
                        <td class="text-center">
                            <input type="checkbox" class="master-checkbox" data-id="${item.id}" data-posted="${isPosted}" 
                                ${isSelected ? 'checked' : ''} onchange="handleMasterCheckboxChangeBpjs(this, ${item.id}, ${isPosted})">
                        </td>
                        <td class="text-center">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-700">${formatTanggal(item.tanggal)}</span>
                                ${item.tanggal_rk ? `<span class="text-xs text-slate-400">RK: ${formatTanggal(item.tanggal_rk)}</span>` : ''}
                            </div>
                        </td>
                        <td>
                            <div class="font-medium text-slate-700">${escapeHtml(item.keterangan || '-')}</div>
                            <div class="text-xs text-slate-400">${escapeHtml(item.no_bukti || '-')}</div>
                        </td>
                        <td class="text-right font-mono text-blue-600">${formatRupiahTable(item.total_rs)}</td>
                        <td class="text-right font-mono text-purple-600">${formatRupiahTable(item.total_pelayanan)}</td>
                        <td class="text-right font-bold font-mono text-emerald-600">${formatRupiahTable(item.total_all)}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">
                            <div class="flex justify-center gap-2">
                                <button class="btn-aksi detail" onclick="openDetailBpjs(${item.id}, '${escapeHtml(info)}', ${item.is_posted})" title="Buka Rincian">
                                    <i class="ph ph-list-numbers"></i>
                                </button>
                                ${(canEdit || canDelete || canPost) ? `
                                    ${(!item.is_posted && canEdit) ? `
                                        <button class="btn-aksi edit" onclick="editMasterBpjs(${item.id})" title="Edit Kelompok">
                                            <i class="ph ph-pencil-simple"></i>
                                        </button>
                                    ` : ''}
                                    ${(!item.is_posted && canDelete) ? `
                                        <button class="btn-aksi delete" onclick="deleteMasterBpjs(${item.id})" title="Hapus Kelompok">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    ` : ''}
                                    ${canPost ? `
                                        <button class="btn-aksi ${item.is_posted ? 'warning' : 'success'}" onclick="togglePostMasterBpjs(${item.id}, ${item.is_posted})" 
                                            title="${item.is_posted ? 'Batalkan Posting' : 'Posting Kelompok'}">
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
            })
            .catch(err => {
                console.error('loadMasterBpjs error:', err);
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-red-500" style="padding: 20px;">Gagal memuat data: ${err.message}</td></tr>`;
            });
    }

    function renderMasterSummaryBpjs(agg) {
        if (!agg) return;
        const rs = document.getElementById('masterSummaryRsBpjs');
        const pel = document.getElementById('masterSummaryPelayananBpjs');
        const tot = document.getElementById('masterSummaryTotalBpjs');
        if (rs) rs.innerText = formatRupiah(agg.total_rs || 0);
        if (pel) pel.innerText = formatRupiah(agg.total_pelayanan || 0);
        if (tot) tot.innerText = formatRupiah(agg.total_all || 0);
    }

    function renderPaginationMasterBpjs(meta) {
        const info = document.getElementById('paginationInfoMasterBpjs');
        if (info) info.innerText = `Menampilkan ${meta.from || 0}–${meta.to || 0} dari ${meta.total || 0} data`;

        const pageInfo = document.getElementById('pageInfoMasterBpjs');
        if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;

        const prev = document.getElementById('prevPageMasterBpjs');
        const next = document.getElementById('nextPageMasterBpjs');
        if (prev) {
            prev.disabled = (meta.current_page === 1);
            next.onclick = () => loadMasterBpjs(meta.current_page + 1);
        }
    }

    function updateSortIconsBpjs() {
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

    window.sortMasterBpjs = function (column) {
        if (masterSortBy === column) {
            masterSortDir = (masterSortDir === 'asc' ? 'desc' : 'asc');
        } else {
            masterSortBy = column;
            masterSortDir = 'desc';
        }
        loadMasterBpjs(1);
    };

    /* =========================
       MASTER ACTIONS
    ========================= */
    window.openMasterFormBpjs = function () {
        const modal = document.getElementById('modalMasterFormBpjs');
        if (!modal) return;
        document.getElementById('masterIdBpjs').value = '';
        document.getElementById('formMasterBpjs').reset();
        document.getElementById('masterFormTitleBpjs').innerHTML = '<i class="ph ph-folder-plus"></i> Tambah Kelompok BPJS';
        modal.classList.add('show');

        const btn = document.getElementById('btnSimpanMasterBpjs');
        if (btn) btn.disabled = true;
    };

    window.closeMasterModalBpjs = function () {
        const modal = document.getElementById('modalMasterFormBpjs');
        if (modal) modal.classList.remove('show');
    };

    window.editMasterBpjs = function (id) {
        fetch(`/dashboard/revenue-master/${id}`, { headers: { Accept: 'application/json' } })
            .then(res => res.json())
            .then(data => {
                document.getElementById('masterIdBpjs').value = data.id;
                document.getElementById('masterTanggalBpjs').value = formatDateForInput(data.tanggal);
                document.getElementById('masterTanggalRkBpjs').value = data.tanggal_rk ? formatDateForInput(data.tanggal_rk) : '';
                document.getElementById('masterNoBuktiBpjs').value = data.no_bukti || '';
                document.getElementById('masterKeteranganBpjs').value = data.keterangan || '';
                document.getElementById('masterFormTitleBpjs').innerHTML = '<i class="ph ph-pencil-simple"></i> Edit Kelompok BPJS';
                document.getElementById('modalMasterFormBpjs').classList.add('show');

                const form = document.getElementById('formMasterBpjs');
                const btn = document.getElementById('btnSimpanMasterBpjs');
                if (btn && form) btn.disabled = !form.checkValidity();
            });
    };

    const submitMasterBpjs = async function (e) {
        if (e) e.preventDefault();
        const id = document.getElementById('masterIdBpjs').value;
        const btn = document.getElementById('btnSimpanMasterBpjs');
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        const payload = {
            tanggal: document.getElementById('masterTanggalBpjs').value,
            tanggal_rk: document.getElementById('masterTanggalRkBpjs').value,
            no_bukti: document.getElementById('masterNoBuktiBpjs').value,
            keterangan: document.getElementById('masterKeteranganBpjs').value,
            kategori: 'BPJS'
        };

        const url = id ? `/dashboard/revenue-master/${id}` : '/dashboard/revenue-master';
        const method = id ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                toast('Kelompok berhasil disimpan', 'success');
                closeMasterModalBpjs();
                loadMasterBpjs();
            } else {
                throw new Error(data.message || 'Gagal menyimpan kelompok');
            }
        } catch (err) {
            toast(err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Simpan';
        }
    };

    window.deleteMasterBpjs = function (id) {
        openConfirm('Hapus Kelompok', 'Hapus kelompok ini beserta seluruh rinci-an di dalamnya? Tindakan ini tidak dapat dibatalkan.', async () => {
            try {
                const res = await fetch(`/dashboard/revenue-master/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    toast('Kelompok berhasil dihapus', 'success');
                    loadMasterBpjs();
                } else {
                    const data = await res.json();
                    throw new Error(data.message || 'Gagal menghapus kelompok');
                }
            } catch (err) {
                toast(err.message, 'error');
            }
        });
    };

    window.togglePostMasterBpjs = function (id, currentPosted) {
        const title = currentPosted ? 'Batalkan Posting' : 'Posting Kelompok';
        const msg = currentPosted
            ? 'Data rincian akan ditarik dari Rekening Koran dan status kembali ke Draft. Lanjutkan?'
            : 'Data rincian akan diposting ke Rekening Koran dan tidak dapat diubah lagi. Lanjutkan?';

        openConfirm(title, msg, async () => {
            try {
                const res = await fetch(`/dashboard/revenue-master/${id}/toggle-post`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    toast(currentPosted ? 'Posting dibatalkan' : 'Berhasil diposting', 'success');
                    loadMasterBpjs();
                    if (selectedMasterId === id) {
                        const infoText = document.getElementById('detailMasterInfoBpjs')?.innerText || '';
                        openDetailBpjs(id, infoText, !currentPosted);
                    }
                } else {
                    const data = await res.json();
                    throw new Error(data.message || 'Gagal memproses posting');
                }
            } catch (err) {
                toast(err.message, 'error');
            }
        }, currentPosted ? 'Batal Posting' : 'Posting', currentPosted ? 'ph-arrow-counter-clockwise' : 'ph-check-circle', currentPosted ? 'btn-warning' : 'btn-primary');
    };

    /* =========================
       BULK POST/UNPOST MASTER
    ========================= */
    window.handleMasterCheckboxChangeBpjs = function (checkbox, id, isPosted) {
        const status = isPosted ? 'POSTED' : 'DRAFT';

        if (selectedMasterIds.length > 0 && selectionAcrossMode !== status) {
            selectedMasterIds = [];
            isSelectAllPagesAcross = false;
        }

        selectionAcrossMode = status;

        if (checkbox.checked) {
            if (!selectedMasterIds.includes(id)) selectedMasterIds.push(id);
        } else {
            selectedMasterIds = selectedMasterIds.filter(mid => mid !== id);
            isSelectAllPagesAcross = false;
        }
        updateSelectionUIBpjs();
    };

    window.toggleAllMasterBpjs = function (checkbox) {
        const checkboxes = document.querySelectorAll('.master-checkbox');
        if (checkboxes.length === 0) return;

        const firstCheckbox = checkboxes[0];
        const status = firstCheckbox.dataset.posted === 'true' ? 'POSTED' : 'DRAFT';

        selectionAcrossMode = status;

        checkboxes.forEach(cb => {
            if (cb.dataset.posted === (status === 'POSTED' ? 'true' : 'false')) {
                cb.checked = checkbox.checked;
                const id = parseInt(cb.dataset.id);
                if (checkbox.checked) {
                    if (!selectedMasterIds.includes(id)) selectedMasterIds.push(id);
                } else {
                    selectedMasterIds = selectedMasterIds.filter(mid => mid !== id);
                }
            } else {
                cb.checked = false;
                selectedMasterIds = selectedMasterIds.filter(mid => mid !== parseInt(cb.dataset.id));
            }
        });

        if (!checkbox.checked) isSelectAllPagesAcross = false;
        updateSelectionUIBpjs();
    };

    window.selectAllPagesAcrossBpjs = function () {
        isSelectAllPagesAcross = true;
        updateSelectionUIBpjs();
    };

    window.clearSelectionAcrossBpjs = function () {
        selectedMasterIds = [];
        isSelectAllPagesAcross = false;
        const checkAll = document.getElementById('checkAllMasterBpjs');
        if (checkAll) checkAll.checked = false;
        document.querySelectorAll('.master-checkbox').forEach(cb => cb.checked = false);
        updateSelectionUIBpjs();
    };

    function updateSelectionUIBpjs() {
        const banner = document.getElementById('selectionBannerBpjs');
        const bannerAll = document.getElementById('selectionAllBannerBpjs');
        if (!banner || !bannerAll) return;
        const checkAll = document.getElementById('checkAllMasterBpjs');
        const checkboxes = document.querySelectorAll('#masterTableBodyBpjs .master-checkbox');

        const totalInPage = Array.from(checkboxes).filter(cb => cb.dataset.posted === (selectionAcrossMode === 'POSTED' ? 'true' : 'false')).length;
        const selectedInPage = Array.from(checkboxes).filter(cb => cb.checked).length;

        if (selectedInPage > 0 && selectedInPage === totalInPage) {
            const totalOverall = (selectionAcrossMode === 'POSTED') ? totalPostedCount : totalDraftCount;
            if (totalOverall > totalInPage && !isSelectAllPagesAcross) {
                banner.style.display = 'block';
                // Use innerHTML because we are injecting a link
                const label = selectionAcrossMode === 'POSTED' ? 'Diposting' : 'Draft';
                banner.innerHTML = `Semua ${selectedInPage} kelompok ${label} di halaman ini telah terpilih. 
                <a href="javascript:void(0)" onclick="selectAllPagesAcrossBpjs()" style="font-weight: 700; color: #2563eb; text-decoration: underline;">
                Pilih semua ${totalOverall} kelompok BPJS ${label} yang ada</a>`;
            } else {
                banner.style.display = 'none';
            }
        } else {
            banner.style.display = 'none';
        }

        if (isSelectAllPagesAcross) {
            banner.style.display = 'none';
            bannerAll.style.display = 'block';
            const totalOverall = (selectionAcrossMode === 'POSTED') ? totalPostedCount : totalDraftCount;
            const label = selectionAcrossMode === 'POSTED' ? 'Diposting' : 'Draft';
            bannerAll.innerHTML = `Semua ${totalOverall} kelompok Pendapatan BPJS (${label}) telah terpilih lintas halaman.
            <a href="javascript:void(0)" onclick="clearSelectionAcrossBpjs()" style="font-weight: 700; color: #059669; text-decoration: underline;">Batalkan pilihan</a>`;
        } else {
            bannerAll.style.display = 'none';
        }
        if (checkAll) {
            checkAll.checked = (totalInPage > 0 && selectedInPage === totalInPage);
        }
    }

    window.bulkPostMasterBpjs = function () {
        if (!isSelectAllPagesAcross && selectedMasterIds.length === 0) {
            toast('Pilih kelompok yang ingin diposting (Draft)', 'warning');
            return;
        }

        if (selectionAcrossMode === 'POSTED' && !isSelectAllPagesAcross) {
            toast('Kelompok yang dipilih sudah diposting', 'warning');
            return;
        }

        const count = isSelectAllPagesAcross ? totalDraftCount : selectedMasterIds.filter(id => {
            const cb = document.querySelector(`.master-checkbox[data-id="${id}"]`);
            return cb ? cb.dataset.posted === 'false' : true; // Assume draft if not in DOM
        }).length;

        if (count === 0) {
            toast('Tidak ada kelompok Draft yang terpilih', 'warning');
            return;
        }

        // Custom confirm box styling to NOT look like delete
        const modal = document.getElementById('modalConfirm');
        if (modal) {
            modal.querySelector('.confirm-box').style.borderTop = '4px solid #3b82f6';
            modal.querySelector('.btn-danger-confirm').style.background = '#3b82f6';
            modal.querySelector('.btn-danger-confirm').innerText = 'Ya, Posting Masal';
        }

        openConfirm('Posting Masal', `Yakin ingin memposting ${count} kelompok sekaligus?`, async () => {
            try {
                const payload = isSelectAllPagesAcross
                    ? { all_pages: true, kategori: 'BPJS', search: masterKeyword }
                    : { ids: selectedMasterIds };

                const res = await fetch('/dashboard/revenue-master/bulk-post', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    toast(`Berhasil memposting ${data.count} kelompok`, 'success');
                    clearSelectionAcrossBpjs();
                    loadMasterBpjs();
                } else {
                    throw new Error(data.message || 'Gagal posting masal');
                }
            } catch (err) {
                toast(err.message, 'error');
            } finally {
                // Restore modal style
                if (modal) {
                    modal.querySelector('.confirm-box').style.borderTop = '';
                    modal.querySelector('.btn-danger-confirm').style.background = '';
                    modal.querySelector('.btn-danger-confirm').innerText = 'Ya, Hapus';
                }
            }
        });
    };

    window.bulkUnpostMasterBpjs = function () {
        if (!isSelectAllPagesAcross && selectedMasterIds.length === 0) {
            toast('Pilih kelompok yang ingin dibatalkan postingnya', 'warning');
            return;
        }

        if (selectionAcrossMode === 'DRAFT' && !isSelectAllPagesAcross) {
            toast('Kelompok yang dipilih masih berstatus Draft', 'warning');
            return;
        }

        const count = isSelectAllPagesAcross ? totalPostedCount : selectedMasterIds.length;

        if (count === 0) {
            toast('Tidak ada kelompok Diposting yang terpilih', 'warning');
            return;
        }

        openConfirm('Batal Posting Masal', `Yakin ingin membatalkan posting ${count} kelompok sekaligus?`, async () => {
            try {
                const payload = isSelectAllPagesAcross
                    ? { all_pages: true, kategori: 'BPJS', search: masterKeyword }
                    : { ids: selectedMasterIds };

                const res = await fetch('/dashboard/revenue-master/bulk-unpost', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    toast(`Berhasil membatalkan ${data.count} kelompok`, 'success');
                    clearSelectionAcrossBpjs();
                    loadMasterBpjs();
                } else {
                    throw new Error(data.message || 'Gagal batal posting masal');
                }
            } catch (err) {
                toast(err.message, 'error');
            }
        });
    };

    /* =========================
       DETAIL LOGIC (BPJS RECORDS)
    ========================= */
    window.openDetailBpjs = function (id, info, isPosted) {
        selectedMasterId = id;
        activeMasterPosted = !!isPosted;

        document.getElementById('masterListSectionBpjs').style.display = 'none';
        document.getElementById('detailListSectionBpjs').style.display = 'block';
        if (info) document.getElementById('detailMasterInfoBpjs').innerText = info;

        // Handle Read Only
        const btnTambah = document.getElementById('btnTambahPendapatanBpjs');
        const btnImport = document.getElementById('btnImportBpjs');
        const btnBulk = document.getElementById('btnBulkDeleteBpjs');

        if (btnTambah) btnTambah.style.display = activeMasterPosted ? 'none' : 'flex';
        if (btnImport) btnImport.style.display = activeMasterPosted ? 'none' : 'flex';
        if (btnBulk) btnBulk.style.display = activeMasterPosted ? 'none' : 'flex';

        loadPendapatanBpjs(1);
    };

    window.closeDetailBpjs = function () {
        selectedMasterId = null;
        activeMasterPosted = false;
        document.getElementById('detailListSectionBpjs').style.display = 'none';
        document.getElementById('masterListSectionBpjs').style.display = 'block';
        loadMasterBpjs();
    };

    window.switchBpjsTab = function (jenis, btn) {
        currentBpjsTab = jenis;
        document.querySelectorAll('.bpjs-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const thNoSep = document.getElementById('thNoSep');
        if (thNoSep) {
            thNoSep.style.display = (jenis === 'REGULAR') ? '' : 'none';
        }

        loadPendapatanBpjs(1);
    };

    function loadPendapatanBpjs(page = bpjsPage) {
        if (!selectedMasterId) return;
        bpjsPage = page;

        const tbody = document.getElementById('pendapatanBpjsBody');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Memuat rincian...</td></tr>';

        const params = new URLSearchParams({
            page: bpjsPage,
            per_page: bpjsPerPage,
            search: bpjsKeyword,
            sort_by: bpjsSortBy,
            sort_dir: bpjsSortDir,
            revenue_master_id: selectedMasterId,
            jenis_bpjs: currentBpjsTab,
            _t: Date.now()
        });

        fetch(`/dashboard/pendapatan/bpjs?${params.toString()}`, {
            headers: { Accept: 'application/json' }
        })
            .then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal memuat rincian');
                return json;
            })
            .then(res => {
                const data = res.data || [];
                renderPaginationBpjs(res);
                renderDetailSummaryBpjs(res.aggregates);
                updateSortIconsDetailBpjs();

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-slate-500">Belum ada rincian data klaim</td></tr>';
                    return;
                }

                const canEditDetail = (window.hasPermission('PENDAPATAN_BPJS_CREATE') || window.hasPermission('PENDAPATAN_BPJS_CRUD') || window.isAdmin) && !activeMasterPosted;
                const canDeleteDetail = (window.hasPermission('PENDAPATAN_BPJS_DELETE') || window.hasPermission('PENDAPATAN_BPJS_CRUD') || window.isAdmin) && !activeMasterPosted;

                let html = '';
                data.forEach((item, index) => {
                    const noSepCol = (currentBpjsTab === 'REGULAR')
                        ? `<td class="text-center font-mono text-xs">${item.no_sep || '-'}</td>`
                        : '';

                    html += `
                    <tr>
                        <td class="text-center">${res.from + index}</td>
                        <td class="text-center">${formatTanggal(item.tanggal)}</td>
                        ${noSepCol}
                        <td>
                            <div class="font-medium">${escapeHtml(item.nama_pasien ?? '-')}</div>
                            <div class="text-xs text-slate-400">${item.perusahaan?.nama ?? (item.transaksi || '-')}</div>
                        </td>
                        <td><span class="badge badge-info">${item.ruangan?.nama ?? '-'}</span></td>
                        <td class="text-right">
                            <div class="nominal-group">
                                <div class="nom-row">
                                    <div class="nom-val val-rs">${formatRupiahTable((parseFloat(item.rs_tindakan) || 0) + (parseFloat(item.rs_obat) || 0))}</div>
                                    <span class="nom-label label-rs">Rs</span>
                                </div>
                                <div class="nom-row">
                                    <div class="nom-val val-pelayanan">${formatRupiahTable((parseFloat(item.pelayanan_tindakan) || 0) + (parseFloat(item.pelayanan_obat) || 0))}</div>
                                    <span class="nom-label label-pelayanan">Pel</span>
                                </div>
                                <div class="nom-row" style="border-top: 1px dashed #e2e8f0; margin-top: 2px;">
                                    <div class="nom-val val-total">${formatRupiahTable(item.total)}</div>
                                    <span class="nom-label label-total">Tot</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="flex justify-center gap-1">
                                <button class="btn-aksi detail" onclick="detailPendapatanBpjs(${item.id})" title="View">
                                    <i class="ph ph-eye"></i>
                                </button>
                                ${canEditDetail ? `
                                    <button class="btn-aksi edit" onclick="editPendapatanBpjs(${item.id})" title="Edit">
                                        <i class="ph ph-pencil-simple"></i>
                                    </button>
                                ` : ''}
                                ${canDeleteDetail ? `
                                    <button class="btn-aksi delete" onclick="hapusPendapatanBpjs(${item.id})" title="Hapus">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
                });
                tbody.innerHTML = html;
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-red-500">${err.message}</td></tr>`;
                toast(err.message, 'error');
            });
    }

    function renderDetailSummaryBpjs(agg) {
        if (!agg) return;
        const rs = document.getElementById('detailSummaryRsBpjs');
        const pel = document.getElementById('detailSummaryPelayananBpjs');
        const tot = document.getElementById('detailSummaryTotalBpjs');
        if (rs) rs.innerText = formatRupiah(agg.total_rs || 0);
        if (pel) pel.innerText = formatRupiah(agg.total_pelayanan || 0);
        if (tot) tot.innerText = formatRupiah(agg.total_all || 0);
    }

    function renderPaginationBpjs(meta) {
        const info = document.getElementById('paginationInfoBpjs');
        if (info) info.innerText = `Menampilkan ${meta.from || 0}–${meta.to || 0} dari ${meta.total || 0} data`;
        const pageInfo = document.getElementById('pageInfoBpjs');
        if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;
        const prev = document.getElementById('prevPageBpjs');
        const next = document.getElementById('nextPageBpjs');
        if (prev) {
            prev.disabled = (meta.current_page === 1);
            prev.onclick = () => loadPendapatanBpjs(meta.current_page - 1);
        }
        if (next) {
            next.disabled = (meta.current_page === meta.last_page);
            next.onclick = () => loadPendapatanBpjs(meta.current_page + 1);
        }
    }

    /* =========================
       BPJS ITEM ACTIONS
    ========================= */
    window.openPendapatanBpjsModal = async function () {
        if (!selectedMasterId) return;
        const modal = document.getElementById('pendapatanBpjsModal');
        if (!modal) return;

        modal.classList.add('show');
        await Promise.all([
            loadRuanganBpjs(),
            loadPerusahaanBpjs()
        ]);

        if (!isEditBpjs) {
            const form = document.getElementById('formPendapatanBpjs');
            form.reset();
            const dateInput = form.querySelector('[name="tanggal"]');
            if (dateInput) dateInput.value = new Date().toISOString().split('T')[0];
            document.getElementById('bpjsJenisSelect').value = currentBpjsTab;

            document.querySelectorAll('.nominal-display-bpjs').forEach(disp => disp.value = '');
            document.querySelectorAll('.nominal-value-bpjs').forEach(val => val.value = '0');
            hitungTotalBpjs();

            toggleNoSepField();
            toggleBpjsNomis();

            const btn = document.getElementById('btnSimpanPendapatanBpjs');
            if (btn) btn.disabled = true;
        }
        document.getElementById('bpjsMetodePembayaran')?.dispatchEvent(new Event('change'));
    };

    window.closePendapatanBpjsModal = function () {
        document.getElementById('pendapatanBpjsModal')?.classList.remove('show');
        isEditBpjs = false;
        editBpjsId = null;
    };

    window.submitPendapatanBpjs = async function (e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpanPendapatanBpjs');
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        const formData = new FormData(document.getElementById('formPendapatanBpjs'));
        formData.append('revenue_master_id', selectedMasterId);
        if (isEditBpjs) formData.append('_method', 'PUT');

        const url = isEditBpjs ? `/dashboard/pendapatan/bpjs/${editBpjsId}` : '/dashboard/pendapatan/bpjs';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                toast('Data berhasil disimpan', 'success');
                closePendapatanBpjsModal();
                loadPendapatanBpjs();
            } else {
                throw new Error(data.message || 'Gagal menyimpan data');
            }
        } catch (err) {
            toast(err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Simpan';
        }
    };

    window.editPendapatanBpjs = async function (id) {
        isEditBpjs = true;
        editBpjsId = id;
        const title = document.querySelector('#pendapatanBpjsModal .modal-title');
        if (title) title.innerText = '✏️ Edit Pendapatan BPJS';

        const data = await fetch(`/dashboard/pendapatan/bpjs/${id}`, { headers: { Accept: 'application/json' } }).then(res => res.json());

        await openPendapatanBpjsModal();

        const form = document.getElementById('formPendapatanBpjs');
        form.querySelector('[name="tanggal"]').value = formatDateForInput(data.tanggal);
        form.querySelector('[name="jenis_bpjs"]').value = data.jenis_bpjs;
        form.querySelector('[name="no_sep"]').value = data.no_sep || '';
        form.querySelector('[name="nama_pasien"]').value = data.nama_pasien;
        form.querySelector('[name="ruangan_id"]').value = data.ruangan_id;
        if (data.perusahaan_id) form.querySelector('[name="perusahaan_id"]').value = data.perusahaan_id;

        syncTransaksiBpjs();
        toggleNoSepField();
        toggleBpjsNomis();

        form.querySelector('[name="metode_pembayaran"]').value = data.metode_pembayaran;
        form.querySelector('[name="metode_pembayaran"]').dispatchEvent(new Event('change'));

        setTimeout(() => {
            if (data.bank) {
                form.querySelector('[name="bank"]').value = data.bank;
                form.querySelector('[name="bank"]').dispatchEvent(new Event('change'));
                if (data.metode_detail) form.querySelector('[name="metode_detail"]').value = data.metode_detail;
            }
        }, 100);

        form.querySelector('[name="rs_tindakan"]').value = data.rs_tindakan;
        form.querySelector('[name="rs_obat"]').value = data.rs_obat;
        form.querySelector('[name="pelayanan_tindakan"]').value = data.pelayanan_tindakan;
        form.querySelector('[name="pelayanan_obat"]').value = data.pelayanan_obat;

        form.querySelectorAll('.nominal-display-bpjs').forEach((disp, i) => {
            const val = form.querySelectorAll('.nominal-value-bpjs')[i].value;
            disp.value = formatRibuan(val);
        });

        hitungTotalBpjs();
        setTimeout(() => {
            const btn = document.getElementById('btnSimpanPendapatanBpjs');
            if (btn) btn.disabled = !form.checkValidity();
        }, 150);
    };

    window.hapusPendapatanBpjs = function (id) {
        openConfirm('Hapus Data', 'Yakin ingin menghapus data rincian ini?', async () => {
            try {
                const res = await fetch(`/dashboard/pendapatan/bpjs/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    toast('Data berhasil dihapus', 'success');
                    loadPendapatanBpjs();
                } else {
                    const data = await res.json();
                    throw new Error(data.message || 'Gagal menghapus data');
                }
            } catch (err) {
                toast(err.message, 'error');
            }
        });
    };

    window.detailPendapatanBpjs = function (id) {
        const modal = document.getElementById('pendapatanBpjsDetailModal');
        const content = document.getElementById('detailPendapatanBpjsContent');
        modal.classList.add('show');
        content.innerHTML = '<div class="text-center py-8"><i class="ph ph-spinner animate-spin text-3xl"></i></div>';

        fetch(`/dashboard/pendapatan/bpjs/${id}`, { headers: { Accept: 'application/json' } })
            .then(res => res.json())
            .then(data => {
                content.innerHTML = `
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Tanggal</span>
                        <span class="font-medium">${formatTanggal(data.tanggal)}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Jenis BPJS</span>
                        <span class="badge badge-info">${data.jenis_bpjs}</span>
                    </div>
                    ${data.no_sep ? `
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">No SEP</span>
                        <span class="font-mono text-xs">${data.no_sep}</span>
                    </div>` : ''}
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Nama Pasien</span>
                        <span class="font-medium">${escapeHtml(data.nama_pasien)}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Ruangan</span>
                        <span>${data.ruangan?.nama || '-'}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Perusahaan</span>
                        <span>${data.perusahaan?.nama || (data.transaksi || '-')}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Metode</span>
                        <span>${data.metode_pembayaran} ${data.bank ? `(${data.bank})` : ''} - ${data.metode_detail || ''}</span>
                    </div>
                    
                    <div class="mt-4 p-3 bg-slate-50 rounded-lg space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-blue-600 font-bold">Jasa Rumah Sakit</span>
                            <span class="font-mono">${formatRupiah(parseFloat(data.rs_tindakan) + parseFloat(data.rs_obat))}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-purple-600 font-bold">Jasa Pelayanan</span>
                            <span class="font-mono">${formatRupiah(parseFloat(data.pelayanan_tindakan) + parseFloat(data.pelayanan_obat))}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 mt-2 font-bold text-emerald-600">
                            <span>TOTAL KLAIM</span>
                            <span class="font-mono text-base">${formatRupiah(data.total)}</span>
                        </div>
                    </div>
                </div>
            `;
            });
    };

    window.closeDetailPendapatanBpjs = function () {
        document.getElementById('pendapatanBpjsDetailModal')?.classList.remove('show');
    };

    /* =========================
       IMPORT & BULK DELETE
    ========================= */
    window.initImportBpjs = function () {
        const btnImport = document.getElementById('btnImportBpjs');
        if (btnImport) btnImport.onclick = () => document.getElementById('modalImportBpjs').classList.add('show');

        const formImport = document.getElementById('formImportBpjs');
        if (formImport) {
            formImport.onsubmit = async (e) => {
                e.preventDefault();
                const btn = e.target.querySelector('button[type="submit"]');
                const originalText = btn.innerText;
                btn.disabled = true;
                btn.innerText = 'Mengimport...';

                const formData = new FormData(formImport);
                formData.append('revenue_master_id', selectedMasterId);

                try {
                    const res = await fetch('/dashboard/pendapatan/bpjs/import', {
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
                        document.getElementById('modalImportBpjs').classList.remove('show');
                        formImport.reset();
                        loadPendapatanBpjs(1);
                    } else {
                        throw new Error(data.message || 'Gagal import');
                    }
                } catch (err) {
                    toast(err.message, 'error');
                } finally {
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            };
        }
    };

    window.initBulkDeleteBpjs = function () {
        const btnBulk = document.getElementById('btnBulkDeleteBpjs');
        if (btnBulk) {
            btnBulk.onclick = () => {
                openConfirm('Hapus Massal', 'Yakin ingin menghapus SELURUH data rincian pada kelompok ini?', async () => {
                    try {
                        const res = await fetch('/dashboard/pendapatan/bpjs/bulk-delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ revenue_master_id: selectedMasterId })
                        });
                        const data = await res.json();
                        if (data.success) {
                            toast(`Berhasil menghapus ${data.count} data`, 'success');
                            loadPendapatanBpjs(1);
                        } else {
                            throw new Error(data.message || 'Gagal hapus massal');
                        }
                    } catch (err) {
                        toast(err.message, 'error');
                    }
                });
            };
        }
    }

    /* =========================
       UI HELPERS & PERMISSIONS
    ========================= */
    function toggleNoSepField() {
        const jenis = document.getElementById('bpjsJenisSelect')?.value;
        const noSepGroup = document.getElementById('noSepGroup');
        const noSepInput = document.getElementById('bpjsNoSep');
        if (!noSepGroup || !noSepInput) return;

        if (jenis === 'REGULAR') {
            noSepGroup.style.display = 'block';
            noSepInput.setAttribute('required', 'required');
        } else {
            noSepGroup.style.display = 'none';
            noSepInput.removeAttribute('required');
        }
    }

    function toggleBpjsNomis() {
        const jenis = document.getElementById('bpjsJenisSelect')?.value;
        const gTindakan = document.getElementById('groupTindakan');
        const gObat = document.getElementById('groupObat');
        if (!gTindakan || !gObat) return;

        if (jenis === 'OBAT') {
            gTindakan.style.display = 'none';
            gObat.style.display = 'grid';
        } else {
            gTindakan.style.display = 'grid';
            gObat.style.display = 'none';
        }
        hitungTotalBpjs();
    }

    function hitungTotalBpjs() {
        let total = 0;
        const jenis = document.getElementById('bpjsJenisSelect')?.value;
        const containerId = (jenis === 'OBAT') ? 'groupObat' : 'groupTindakan';
        document.querySelectorAll(`#${containerId} .nominal-value-bpjs`).forEach(i => total += parseFloat(i.value || 0));
        document.getElementById('totalPembayaranBpjs').innerText = formatRupiah(total);
    }

    async function loadRuanganBpjs() {
        const select = document.getElementById('bpjsRuanganSelect');
        if (!select) return;
        if (!window._cacheRuangan) {
            window._cacheRuangan = await fetch('/dashboard/ruangan-list').then(res => res.json());
        }
        renderOptions(select, window._cacheRuangan, '-- Pilih Ruangan --', 'kode', 'nama');
    }

    async function loadPerusahaanBpjs() {
        const select = document.getElementById('bpjsPerusahaanSelect');
        if (!select) return;
        if (!window._cachePerusahaan) {
            window._cachePerusahaan = await fetch('/dashboard/perusahaan-list').then(res => res.json());
        }
        renderOptions(select, window._cachePerusahaan, '-- Pilih Perusahaan --', 'kode', 'nama');
    }

    function renderOptions(select, data, placeholder, codeKey, nameKey) {
        const val = select.value;
        select.innerHTML = `<option value="">${placeholder}</option>`;
        data.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.innerText = `${item[codeKey]} — ${item[nameKey]}`;
            opt.dataset.nama = item.nama;
            select.appendChild(opt);
        });
        if (val) select.value = val;
    }

    function syncTransaksiBpjs() {
        const select = document.getElementById('bpjsPerusahaanSelect');
        const hidden = document.getElementById('bpjsTransaksiHidden');
        if (select && hidden) hidden.value = select.options[select.selectedIndex]?.dataset.nama || '';
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
                loadMasterBpjs(1);
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

    window.initPendapatanBpjs = function () {
        const formMasterBpjs = document.getElementById('formMasterBpjs');
        if (formMasterBpjs) {
            formMasterBpjs.onsubmit = submitMasterBpjs;
        }

        loadMasterBpjs(1);

        // Search Master
        const searchMasterBpjs = document.getElementById('searchMasterBpjs');
        if (searchMasterBpjs) {
            let timer;
            searchMasterBpjs.oninput = (e) => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    masterKeyword = e.target.value.trim();
                    loadMasterBpjs(1);
                }, 400);
            };
        }

        const filterStatusMasterBpjs = document.getElementById('filterStatusMasterBpjs');
        if (filterStatusMasterBpjs) {
            filterStatusMasterBpjs.onchange = (e) => {
                masterStatus = e.target.value;
                loadMasterBpjs(1);
            };
        }

        // Search BPJS Records
        const searchBpjs = document.getElementById('searchPendapatanBpjs');
        if (searchBpjs) {
            let timer;
            searchBpjs.oninput = (e) => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    bpjsKeyword = e.target.value.trim();
                    loadPendapatanBpjs(1);
                }, 400);
            };
        }

        // Nominal formatting logic for BPJS details
        document.querySelectorAll('.nominal-display-bpjs').forEach(input => {
            input.addEventListener('input', () => {
                const val = parseAngka(input.value);
                input.nextElementSibling.value = val;
                hitungTotalBpjs();
            });
            input.addEventListener('blur', () => {
                input.value = formatRibuan(parseAngka(input.value));
            });
            input.addEventListener('focus', () => {
                const val = parseAngka(input.value);
                input.value = val === 0 ? '' : val.toString().replace('.', ',');
            });
        });

        // BPJS Form dynamic logic (metode, bank, etc.)
        const metodeSelect = document.getElementById('bpjsMetodePembayaran');
        const bankSelect = document.getElementById('bpjsBank');
        const detailSelect = document.getElementById('bpjsMetodeDetail');

        if (metodeSelect) {
            metodeSelect.onchange = () => {
                resetSelect(bankSelect, '-- Pilih Bank --');
                resetSelect(detailSelect, '-- Metode Detail --');
                if (metodeSelect.value === 'TUNAI') {
                    bankSelect.disabled = true;
                    detailSelect.disabled = true;
                    addOption(bankSelect, { value: 'BRK', label: 'Bank Riau Kepri Syariah' });
                    bankSelect.value = 'BRK';
                    addOption(detailSelect, { value: 'SETOR_TUNAI', label: 'Setor Tunai' });
                    detailSelect.value = 'SETOR_TUNAI';
                } else if (metodeSelect.value === 'NON_TUNAI') {
                    bankSelect.disabled = false;
                    addOption(bankSelect, { value: 'BRK', label: 'Bank Riau Kepri Syariah' });
                    addOption(bankSelect, { value: 'BSI', label: 'Bank Syariah Indonesia' });
                }
            };
        }

        if (bankSelect) {
            bankSelect.onchange = () => {
                if (metodeSelect.value !== 'NON_TUNAI') return;
                resetSelect(detailSelect, '-- Metode Detail --');
                if (bankSelect.value) {
                    detailSelect.disabled = false;
                    addOption(detailSelect, { value: 'QRIS', label: 'QRIS' });
                    addOption(detailSelect, { value: 'TRANSFER', label: 'Transfer' });
                } else {
                    detailSelect.disabled = true;
                }
            };
        }

        initImportBpjs();
        initBulkDeleteBpjs();

        // Form Validation for Tambah Data
        const formBpjs = document.getElementById('formPendapatanBpjs');
        if (formBpjs) {
            const validateForm = () => {
                const btn = document.getElementById('btnSimpanPendapatanBpjs');
                if (btn) btn.disabled = !formBpjs.checkValidity();
            };
            formBpjs.addEventListener('input', validateForm);
            formBpjs.addEventListener('change', validateForm);
        }

        // Form Validation for Master Group (Kelompok)
        const formMasterBpjsVal = document.getElementById('formMasterBpjs');
        if (formMasterBpjsVal) {
            const validateMasterForm = () => {
                const btn = document.getElementById('btnSimpanMasterBpjs');
                if (btn) btn.disabled = !formMasterBpjsVal.checkValidity();
            };
            formMasterBpjsVal.addEventListener('input', validateMasterForm);
            formMasterBpjsVal.addEventListener('change', validateMasterForm);
        }
    };

})();
