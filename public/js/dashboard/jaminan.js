(function () {
    let masterPage = 1;
    let masterPerPage = 10;
    let masterKeyword = '';
    let selectedMasterId = null;
    let currentMasterData = null;

    let jaminanPage = 1;
    let jaminanPerPage = 10;
    let jaminanKeyword = '';
    let isEditJaminan = false;
    let editJaminanId = null;
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
    function loadMasterJaminan(page = masterPage) {
        masterPage = page;
        const tbody = document.getElementById('masterTableBodyJaminan');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Memuat data...</td></tr>';

        const params = new URLSearchParams({
            page: masterPage,
            per_page: masterPerPage,
            search: masterKeyword,
            kategori: 'JAMINAN',
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
                renderPaginationMasterJaminan(res);
                renderMasterSummaryJaminan(res.aggregates);
                updateSelectionUIJaminan();

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-slate-500">Belum ada kelompok pendapatan Jaminan</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.forEach((item, index) => {
                    const info = `${window.formatTanggal(item.tanggal)} ${item.no_bukti ? `(${item.no_bukti})` : ''} - ${item.keterangan || ''}`;
                    const isPosted = !!item.is_posted;
                    const statusBadge = isPosted
                        ? '<span class="badge badge-success" style="display:inline-flex; align-items:center; gap:4px; white-space:nowrap;"><i class="ph-bold ph-check-circle"></i> Diposting</span>'
                        : '<span class="badge badge-warning">Draft</span>';

                    const canCRUD = window.hasPermission('PENDAPATAN_JAMINAN_CRUD');
                    const isSelected = selectedMasterIds.includes(item.id);

                    tbody.insertAdjacentHTML('beforeend', `
                    <tr class="${selectedMasterId === item.id ? 'bg-blue-50' : ''}">
                        <td class="text-center">
                            <input type="checkbox" class="master-checkbox" data-id="${item.id}" data-posted="${isPosted}" 
                                ${isSelected ? 'checked' : ''} onchange="handleMasterCheckboxChangeJaminan(this, ${item.id}, ${isPosted})">
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
                                <button class="btn-aksi detail" onclick="openDetailJaminan(${item.id}, '${escapeHtml(info)}', ${item.is_posted})" title="Buka Rincian">
                                    <i class="ph ph-list-numbers"></i>
                                </button>
                                ${canCRUD ? `
                                    ${!item.is_posted ? `
                                        <button class="btn-aksi edit" onclick="editMasterJaminan(${item.id})" title="Edit Kelompok">
                                            <i class="ph ph-pencil-simple"></i>
                                        </button>
                                        <button class="btn-aksi delete" onclick="deleteMasterJaminan(${item.id})" title="Hapus Kelompok">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    ` : ''}
                                    <button class="btn-aksi ${item.is_posted ? 'warning' : 'success'}" onclick="togglePostMasterJaminan(${item.id}, ${item.is_posted})" 
                                        title="${item.is_posted ? 'Batalkan Posting' : 'Posting Kelompok'}">
                                        <i class="ph ${item.is_posted ? 'ph-x-circle' : 'ph-check-circle'}"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `);
                });
            })
            .catch(err => {
                console.error('loadMasterJaminan error:', err);
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-red-500" style="padding: 20px;">Gagal memuat data: ${err.message}</td></tr>`;
            });
    }

    function renderMasterSummaryJaminan(agg) {
        if (!agg) return;
        const rs = document.getElementById('masterSummaryRsJaminan');
        const pel = document.getElementById('masterSummaryPelayananJaminan');
        const tot = document.getElementById('masterSummaryTotalJaminan');
        if (rs) rs.innerText = formatRupiah(agg.total_rs || 0);
        if (pel) pel.innerText = formatRupiah(agg.total_pelayanan || 0);
        if (tot) tot.innerText = formatRupiah(agg.total_all || 0);
    }

    function renderPaginationMasterJaminan(meta) {
        const info = document.getElementById('paginationInfoMasterJaminan');
        if (info) info.innerText = `Menampilkan ${meta.from || 0}–${meta.to || 0} dari ${meta.total || 0} data`;

        const pageInfo = document.getElementById('pageInfoMasterJaminan');
        if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;

        const prev = document.getElementById('prevPageMasterJaminan');
        const next = document.getElementById('nextPageMasterJaminan');
        if (prev) {
            prev.disabled = (meta.current_page === 1);
            prev.onclick = () => loadMasterJaminan(meta.current_page - 1);
        }
        if (next) {
            next.disabled = (meta.current_page === meta.last_page);
            next.onclick = () => loadMasterJaminan(meta.current_page + 1);
        }
    }

    /* =========================
       MASTER ACTIONS
    ========================= */
    window.openMasterFormJaminan = function () {
        document.getElementById('masterIdJaminan').value = '';
        document.getElementById('formMasterJaminan').reset();
        document.getElementById('masterFormTitleJaminan').innerHTML = '<i class="ph ph-folder-plus"></i> Tambah Kelompok Jaminan';
        document.getElementById('modalMasterFormJaminan').classList.add('show');
    };

    window.closeMasterModalJaminan = function () {
        document.getElementById('modalMasterFormJaminan').classList.remove('show');
    };

    window.editMasterJaminan = function (id) {
        fetch(`/dashboard/revenue-master/${id}`, { headers: { Accept: 'application/json' } })
            .then(res => res.json())
            .then(data => {
                document.getElementById('masterIdJaminan').value = data.id;
                document.getElementById('masterTanggalJaminan').value = formatDateForInput(data.tanggal);
                document.getElementById('masterTanggalRkJaminan').value = data.tanggal_rk ? formatDateForInput(data.tanggal_rk) : '';
                document.getElementById('masterNoBuktiJaminan').value = data.no_bukti || '';
                document.getElementById('masterKeteranganJaminan').value = data.keterangan || '';
                document.getElementById('masterFormTitleJaminan').innerHTML = '<i class="ph ph-pencil-simple"></i> Edit Kelompok Jaminan';
                document.getElementById('modalMasterFormJaminan').classList.add('show');
            });
    };

    const submitMasterJaminan = async function (e) {
        if (e) e.preventDefault();
        const id = document.getElementById('masterIdJaminan').value;
        const btn = document.getElementById('btnSimpanMasterJaminan');
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        const payload = {
            tanggal: document.getElementById('masterTanggalJaminan').value,
            tanggal_rk: document.getElementById('masterTanggalRkJaminan').value,
            no_bukti: document.getElementById('masterNoBuktiJaminan').value,
            keterangan: document.getElementById('masterKeteranganJaminan').value,
            kategori: 'JAMINAN'
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
                closeMasterModalJaminan();
                loadMasterJaminan();
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

    window.deleteMasterJaminan = function (id) {
        openConfirm('Hapus Kelompok', 'Hapus kelompok ini beserta seluruh rincian di dalamnya? Tindakan ini tidak dapat dibatalkan.', async () => {
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
                    loadMasterJaminan();
                } else {
                    const data = await res.json();
                    throw new Error(data.message || 'Gagal menghapus kelompok');
                }
            } catch (err) {
                toast(err.message, 'error');
            }
        });
    };

    window.togglePostMasterJaminan = function (id, currentPosted) {
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
                    loadMasterJaminan();
                    if (selectedMasterId === id) {
                        const infoText = document.getElementById('detailMasterInfoJaminan')?.innerText || '';
                        openDetailJaminan(id, infoText, !currentPosted);
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
    window.handleMasterCheckboxChangeJaminan = function (checkbox, id, isPosted) {
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
        updateSelectionUIJaminan();
    };

    window.toggleAllMasterJaminan = function (checkbox) {
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
        updateSelectionUIJaminan();
    };

    window.selectAllPagesAcrossJaminan = function () {
        isSelectAllPagesAcross = true;
        updateSelectionUIJaminan();
    };

    window.clearSelectionAcrossJaminan = function () {
        selectedMasterIds = [];
        isSelectAllPagesAcross = false;
        const checkAll = document.getElementById('checkAllMasterJaminan');
        if (checkAll) checkAll.checked = false;
        document.querySelectorAll('.master-checkbox').forEach(cb => cb.checked = false);
        updateSelectionUIJaminan();
    };

    function updateSelectionUIJaminan() {
        const banner = document.getElementById('selectionBannerJaminan');
        const bannerAll = document.getElementById('selectionAllBannerJaminan');
        if (!banner || !bannerAll) return;
        const checkAll = document.getElementById('checkAllMasterJaminan');
        const checkboxes = document.querySelectorAll('#masterTableBodyJaminan .master-checkbox');

        const totalInPage = Array.from(checkboxes).filter(cb => cb.dataset.posted === (selectionAcrossMode === 'POSTED' ? 'true' : 'false')).length;
        const selectedInPage = Array.from(checkboxes).filter(cb => cb.checked).length;

        if (selectedInPage > 0 && selectedInPage === totalInPage) {
            const totalOverall = (selectionAcrossMode === 'POSTED') ? totalPostedCount : totalDraftCount;
            if (totalOverall > totalInPage && !isSelectAllPagesAcross) {
                banner.style.display = 'block';
                const countCurrentPageEl = document.getElementById('countCurrentPageJaminan');
                if (countCurrentPageEl) countCurrentPageEl.innerText = selectedInPage;
                // Add or update the "Select all" link
                const label = selectionAcrossMode === 'POSTED' ? 'Diposting' : 'Draft';
                banner.innerHTML = `Semua ${selectedInPage} kelompok ${label} di halaman ini telah terpilih. 
                <a href="javascript:void(0)" onclick="selectAllPagesAcrossJaminan()" style="font-weight: 700; color: #2563eb; text-decoration: underline;">
                Pilih semua ${totalOverall} kelompok Jaminan ${label} yang ada</a>`;
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
            const countTotalDraftSelectedEl = document.getElementById('countTotalDraftSelectedJaminan');
            const labelSelectionAllEl = document.getElementById('labelSelectionAllJaminan');
            if (countTotalDraftSelectedEl) countTotalDraftSelectedEl.innerText = totalOverall;
            if (labelSelectionAllEl) labelSelectionAllEl.innerText = `Pendapatan Jaminan (${label})`;
        } else {
            bannerAll.style.display = 'none';
        }

        if (checkAll) {
            checkAll.checked = (totalInPage > 0 && selectedInPage === totalInPage);
        }
    }

    window.bulkPostMasterJaminan = function () {
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
            return cb ? cb.dataset.posted === 'false' : true;
        }).length;

        if (count === 0) {
            toast('Tidak ada kelompok Draft yang terpilih', 'warning');
            return;
        }

        const modal = document.getElementById('modalConfirm');
        if (modal) {
            modal.querySelector('.confirm-box').style.borderTop = '4px solid #3b82f6';
            modal.querySelector('.btn-danger-confirm').style.background = '#3b82f6';
            modal.querySelector('.btn-danger-confirm').innerText = 'Ya, Posting Masal';
        }

        openConfirm('Posting Masal', `Yakin ingin memposting ${count} kelompok sekaligus?`, async () => {
            try {
                const payload = isSelectAllPagesAcross
                    ? { all_pages: true, kategori: 'Jaminan', search: masterKeyword }
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
                    clearSelectionAcrossJaminan();
                    loadMaster();
                } else {
                    throw new Error(data.message || 'Gagal posting masal');
                }
            } catch (err) {
                toast(err.message, 'error');
            } finally {
                if (modal) {
                    modal.querySelector('.confirm-box').style.borderTop = '';
                    modal.querySelector('.btn-danger-confirm').style.background = '';
                    modal.querySelector('.btn-danger-confirm').innerText = 'Ya, Hapus';
                }
            }
        });
    };

    window.bulkUnpostMasterJaminan = function () {
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
                    ? { all_pages: true, kategori: 'Jaminan', search: masterKeyword }
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
                    clearSelectionAcrossJaminan();
                    loadMaster();
                } else {
                    throw new Error(data.message || 'Gagal batal posting masal');
                }
            } catch (err) {
                toast(err.message, 'error');
            }
        });
    };

    /* =========================
       DETAIL LOGIC (JAMINAN RECORDS)
    ========================= */
    window.openDetailJaminan = function (id, info, isPosted) {
        selectedMasterId = id;
        activeMasterPosted = !!isPosted;
        document.getElementById('masterListSectionJaminan').style.display = 'none';
        document.getElementById('detailListSectionJaminan').style.display = 'block';
        if (info) document.getElementById('detailMasterInfoJaminan').innerText = info;

        const btnTambah = document.getElementById('btnTambahPendapatanJaminan');
        const btnImport = document.getElementById('btnImportJaminan');
        const btnBulk = document.getElementById('btnBulkDeleteJaminan');

        if (btnTambah) btnTambah.style.display = activeMasterPosted ? 'none' : 'flex';
        if (btnImport) btnImport.style.display = activeMasterPosted ? 'none' : 'flex';
        if (btnBulk) btnBulk.style.display = activeMasterPosted ? 'none' : 'flex';

        loadPendapatanJaminan(1);
    };

    window.closeDetailJaminan = function () {
        selectedMasterId = null;
        activeMasterPosted = false;
        const detailSec = document.getElementById('detailListSectionJaminan');
        if (detailSec) detailSec.style.display = 'none';
        document.getElementById('masterListSectionJaminan').style.display = 'block';
        loadMasterJaminan();
    };

    function loadPendapatanJaminan(page = jaminanPage) {
        if (!selectedMasterId) return;
        jaminanPage = page;

        const tbody = document.getElementById('pendapatanJaminanBody');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Memuat rincian...</td></tr>';

        const params = new URLSearchParams({
            page: jaminanPage,
            per_page: jaminanPerPage,
            search: jaminanKeyword,
            revenue_master_id: selectedMasterId,
            _t: Date.now()
        });

        fetch(`/dashboard/pendapatan/jaminan?${params.toString()}`, {
            headers: { Accept: 'application/json' }
        })
            .then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal memuat rincian');
                return json;
            })
            .then(res => {
                const data = res.data || [];
                renderPaginationJaminan(res);
                renderDetailSummaryJaminan(res.aggregates);

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-slate-500">Belum ada rincian data jaminan</td></tr>';
                    return;
                }

                const canCRUD = window.hasPermission('PENDAPATAN_JAMINAN_CRUD') && !activeMasterPosted;

                tbody.innerHTML = '';
                data.forEach((item, index) => {
                    tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td class="text-center">${res.from + index}</td>
                        <td class="text-center">${formatTanggal(item.tanggal)}</td>
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
                                <button class="btn-aksi detail" onclick="detailPendapatanJaminan(${item.id})" title="View">
                                    <i class="ph ph-eye"></i>
                                </button>
                                ${canCRUD ? `
                                    <button class="btn-aksi edit" onclick="editPendapatanJaminan(${item.id})" title="Edit">
                                        <i class="ph ph-pencil-simple"></i>
                                    </button>
                                    <button class="btn-aksi delete" onclick="hapusPendapatanJaminan(${item.id})" title="Hapus">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `);
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-red-500">${err.message}</td></tr>`;
                toast(err.message, 'error');
            });
    }

    function renderDetailSummaryJaminan(agg) {
        if (!agg) return;
        const rs = document.getElementById('detailSummaryRsJaminan');
        const pel = document.getElementById('detailSummaryPelayananJaminan');
        const tot = document.getElementById('detailSummaryTotalJaminan');
        if (rs) rs.innerText = formatRupiah(agg.total_rs || 0);
        if (pel) pel.innerText = formatRupiah(agg.total_pelayanan || 0);
        if (tot) tot.innerText = formatRupiah(agg.total_all || 0);
    }

    function renderPaginationJaminan(meta) {
        const info = document.getElementById('paginationInfoJaminan');
        if (info) info.innerText = `Menampilkan ${meta.from || 0}–${meta.to || 0} dari ${meta.total || 0} data`;
        const pageInfo = document.getElementById('pageInfoJaminan');
        if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;
        const prev = document.getElementById('prevPageJaminan');
        const next = document.getElementById('nextPageJaminan');
        if (prev) {
            prev.disabled = (meta.current_page === 1);
            prev.onclick = () => loadPendapatanJaminan(meta.current_page - 1);
        }
        if (next) {
            next.disabled = (meta.current_page === meta.last_page);
            next.onclick = () => loadPendapatanJaminan(meta.current_page + 1);
        }
    }

    /* =========================
       JAMINAN ITEM ACTIONS
    ========================= */
    window.openPendapatanJaminanModal = async function () {
        if (!selectedMasterId) return;
        const modal = document.getElementById('pendapatanJaminanModal');
        if (!modal) return;

        modal.classList.add('show');
        await Promise.all([
            loadRuanganJaminan(),
            loadPerusahaanJaminan()
        ]);

        if (!isEditJaminan) {
            document.getElementById('formPendapatanJaminan').reset();
            document.getElementById('jaminanTanggal').value = formatDateForInput(currentMasterData.tanggal);
        }
        document.getElementById('jaminanMetodePembayaran')?.dispatchEvent(new Event('change'));
    };

    window.closePendapatanJaminanModal = function () {
        document.getElementById('pendapatanJaminanModal')?.classList.remove('show');
        isEditJaminan = false;
        editJaminanId = null;
    };

    window.submitPendapatanJaminan = async function (e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpanPendapatanJaminan');
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        const formData = new FormData(document.getElementById('formPendapatanJaminan'));
        formData.append('revenue_master_id', selectedMasterId);
        if (isEditJaminan) formData.append('_method', 'PUT');

        const url = isEditJaminan ? `/dashboard/pendapatan/jaminan/${editJaminanId}` : '/dashboard/pendapatan/jaminan';

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
                closePendapatanJaminanModal();
                loadPendapatanJaminan();
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

    window.editPendapatanJaminan = async function (id) {
        isEditJaminan = true;
        editJaminanId = id;
        const title = document.querySelector('#pendapatanJaminanModal .modal-title');
        if (title) title.innerText = '✏️ Edit Pendapatan Jaminan';

        const data = await fetch(`/dashboard/pendapatan/jaminan/${id}`, { headers: { Accept: 'application/json' } }).then(res => res.json());

        await openPendapatanJaminanModal();

        const form = document.getElementById('formPendapatanJaminan');
        form.querySelector('[name="tanggal"]').value = formatDateForInput(data.tanggal);
        form.querySelector('[name="nama_pasien"]').value = data.nama_pasien;
        form.querySelector('[name="ruangan_id"]').value = data.ruangan_id;
        if (data.perusahaan_id) form.querySelector('[name="perusahaan_id"]').value = data.perusahaan_id;

        syncTransaksiJaminan();

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

        form.querySelectorAll('.nominal-display-jaminan').forEach((disp, i) => {
            const val = form.querySelectorAll('.nominal-value-jaminan')[i].value;
            disp.value = formatRibuan(val);
        });

        hitungTotalJaminan();
    };

    window.hapusPendapatanJaminan = function (id) {
        openConfirm('Hapus Data', 'Yakin ingin menghapus rincian ini?', async () => {
            try {
                const res = await fetch(`/dashboard/pendapatan/jaminan/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    toast('Data berhasil dihapus', 'success');
                    loadPendapatanJaminan();
                } else {
                    const data = await res.json();
                    throw new Error(data.message || 'Gagal menghapus data');
                }
            } catch (err) {
                toast(err.message, 'error');
            }
        });
    };

    window.detailPendapatanJaminan = function (id) {
        const modal = document.getElementById('pendapatanJaminanDetailModal');
        const content = document.getElementById('detailPendapatanJaminanContent');
        modal.classList.add('show');
        content.innerHTML = '<div class="text-center py-8"><i class="ph ph-spinner animate-spin text-3xl"></i></div>';

        fetch(`/dashboard/pendapatan/jaminan/${id}`, { headers: { Accept: 'application/json' } })
            .then(res => res.json())
            .then(data => {
                content.innerHTML = `
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Tanggal</span>
                        <span class="font-medium">${formatTanggal(data.tanggal)}</span>
                    </div>
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
                            <span>TOTAL JAMINAN</span>
                            <span class="font-mono text-base">${formatRupiah(data.total)}</span>
                        </div>
                    </div>
                </div>
            `;
            });
    };

    window.closeDetailPendapatanJaminan = function () {
        document.getElementById('pendapatanJaminanDetailModal')?.classList.remove('show');
    };

    /* =========================
       IMPORT & BULK DELETE JAMINAN
    ========================= */
    window.initImportJaminan = function () {
        const btnImport = document.getElementById('btnImportJaminan');
        if (btnImport) btnImport.onclick = () => document.getElementById('modalImportJaminan').classList.add('show');

        const formImport = document.getElementById('formImportJaminan');
        if (formImport) {
            formImport.onsubmit = async (e) => {
                e.preventDefault();
                const btn = e.target.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerText = 'Mengimport...';

                const formData = new FormData(formImport);
                formData.append('revenue_master_id', selectedMasterId);

                try {
                    const res = await fetch('/dashboard/pendapatan/jaminan/import', {
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
                        document.getElementById('modalImportJaminan').classList.remove('show');
                        formImport.reset();
                        loadPendapatanJaminan(1);
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
    };

    window.initBulkDeleteJaminan = function () {
        const btnBulk = document.getElementById('btnBulkDeleteJaminan');
        if (btnBulk) {
            btnBulk.onclick = () => {
                openConfirm('Hapus Massal', 'Yakin ingin menghapus SELURUH rincian pada kelompok ini?', async () => {
                    try {
                        const res = await fetch('/dashboard/pendapatan/jaminan/bulk-delete', {
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
                            loadPendapatanJaminan(1);
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
       UI HELPERS
    ========================= */
    function hitungTotalJaminan() {
        let total = 0;
        document.querySelectorAll('.nominal-value-jaminan').forEach(i => total += parseFloat(i.value || 0));
        document.getElementById('totalPembayaranJaminan').innerText = formatRupiah(total);
    }

    async function loadRuanganJaminan() {
        const select = document.getElementById('jaminanRuanganSelect');
        if (!select) return;
        if (!window._cacheRuangan) {
            window._cacheRuangan = await fetch('/dashboard/ruangan-list').then(res => res.json());
        }
        renderOptions(select, window._cacheRuangan, '-- Pilih Ruangan --', 'kode', 'nama');
    }

    async function loadPerusahaanJaminan() {
        const select = document.getElementById('jaminanPerusahaanSelect');
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

    function syncTransaksiJaminan() {
        const select = document.getElementById('jaminanPerusahaanSelect');
        const hidden = document.getElementById('jaminanTransaksiHidden');
        if (select && hidden) hidden.value = select.options[select.selectedIndex]?.dataset.nama || '';
    }

    window.initPendapatanJaminan = function () {
        const formMasterJaminan = document.getElementById('formMasterJaminan');
        if (formMasterJaminan) {
            formMasterJaminan.onsubmit = submitMasterJaminan;
        }

        loadMasterJaminan(1);

        const searchMasterJaminan = document.getElementById('searchMasterJaminan');
        if (searchMasterJaminan) {
            let timer;
            searchMasterJaminan.oninput = (e) => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    masterKeyword = e.target.value.trim();
                    loadMasterJaminan(1);
                }, 400);
            };
        }

        const searchJaminan = document.getElementById('searchPendapatanJaminan');
        if (searchJaminan) {
            let timer;
            searchJaminan.oninput = (e) => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    jaminanKeyword = e.target.value.trim();
                    loadPendapatanJaminan(1);
                }, 400);
            };
        }

        document.querySelectorAll('.nominal-display-jaminan').forEach(input => {
            input.addEventListener('input', () => {
                const val = parseAngka(input.value);
                input.nextElementSibling.value = val;
                hitungTotalJaminan();
            });
            input.addEventListener('blur', () => {
                input.value = formatRibuan(parseAngka(input.value));
            });
            input.addEventListener('focus', () => {
                const val = parseAngka(input.value);
                input.value = val === 0 ? '' : val.toString().replace('.', ',');
            });
        });

        const metodeSelect = document.getElementById('jaminanMetodePembayaran');
        const bankSelect = document.getElementById('jaminanBank');
        const detailSelect = document.getElementById('jaminanMetodeDetail');

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

        initImportJaminan();
        initBulkDeleteJaminan();
    };

})();
