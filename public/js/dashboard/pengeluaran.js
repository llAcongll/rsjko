/* =========================
   PENGELUARAN JS
========================= */

let pengeluaranPage = 1;
let pengeluaranPerPage = 10;
let pengeluaranKeyword = '';
let currentKategori = '';
let isEditPengeluaran = false;
let editPengeluaranId = null;
let pengeluaranType = '';
let pengeluaranSortBy = 'spending_date';
let pengeluaranSortDir = 'desc';

// Searchable select state
let rekeningOptions = [];
let rekeningDropdownIndex = -1;

// No. Bukti validation state
let noBuktiCheckTimer = null;
let noBuktiIsValid = true;

/* =========================
   ROUTING / APP.JS INTEGRATION
========================= */


window.initPengeluaran = function (kategori) {
    currentKategori = kategori;
    pengeluaranPage = 1;
    pengeluaranKeyword = '';

    bindCurrencyInputs();
    bindNoBuktiValidation();
    bindRekeningSearchable();

    // Bind Search Input Logic
    const searchInput = document.getElementById('searchPengeluaran');
    if (searchInput) {
        searchInput.oninput = (e) => window.handleSearchPengeluaran(e);
    }

    // Bind Metode Change Logic
    const metodeSelect = document.getElementById('pengeluaranMetode');
    if (metodeSelect) {
        metodeSelect.onchange = () => {
            const val = metodeSelect.value;
            const section = document.getElementById('guCycleSection');
            if (val === 'GU') {
                section.style.display = 'block';
                loadAvailableGuCycles();
            } else {
                section.style.display = 'none';
            }
        };
    }

    loadPengeluaran();
}

async function loadAvailableGuCycles() {
    const select = document.getElementById('pengeluaranSiklus');
    if (!select) return;

    try {
        const year = window.tahunAnggaran || new Date().getFullYear();
        const res = await fetch(`/dashboard/disbursements/available-siklus?type=GU&year=${year}`);
        const data = await res.json();

        select.innerHTML = '<option value="">-- Pilih Batch GU --</option>';
        data.forEach(item => {
            select.insertAdjacentHTML('beforeend', `<option value="${item.siklus_up}">GU-${item.siklus_up}</option>`);
        });

        // Auto select the last one if it's a new entry
        if (!isEditPengeluaran && data.length > 0) {
            select.value = data[data.length - 1].siklus_up;
        }
    } catch (err) {
        console.error('Gagal memuat batch GU:', err);
    }
}

// Update summary cards if needed

/* =========================
   NO. BUKTI VALIDATION
========================= */
function bindNoBuktiValidation() {
    const input = document.getElementById('pengeluaranNoBukti');
    if (!input) return;

    input.oninput = function () {
        if (noBuktiCheckTimer) clearTimeout(noBuktiCheckTimer);
        const val = this.value.trim();
        if (!val) {
            hideNoBuktiStatus();
            noBuktiIsValid = true;
            return;
        }

        noBuktiCheckTimer = setTimeout(() => {
            checkNoBuktiAvailability(val);
        }, 500);
    };
}

async function checkNoBuktiAvailability(noBukti) {
    const statusEl = document.getElementById('noBuktiStatus');
    const msgEl = document.getElementById('noBuktiMessage');
    const input = document.getElementById('pengeluaranNoBukti');

    if (!statusEl || !msgEl) return;

    // Show loading
    statusEl.style.display = 'inline';
    statusEl.innerHTML = '<i class="ph ph-spinner-gap" style="animation: spin 1s linear infinite; color: #94a3b8;"></i>';
    msgEl.style.display = 'none';

    try {
        let url = `/dashboard/expenditures/check-no-bukti?no_bukti=${encodeURIComponent(noBukti)}`;
        if (isEditPengeluaran && editPengeluaranId) {
            url += `&exclude_id=${editPengeluaranId}`;
        }
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        const data = await res.json();

        if (data.available) {
            statusEl.innerHTML = '<i class="ph ph-check-circle" style="color: #22c55e;"></i>';
            msgEl.style.display = 'block';
            msgEl.textContent = 'Nomor bukti tersedia';
            msgEl.style.color = '#22c55e';
            input.style.borderColor = '#22c55e';
            noBuktiIsValid = true;
        } else {
            statusEl.innerHTML = '<i class="ph ph-x-circle" style="color: #ef4444;"></i>';
            msgEl.style.display = 'block';
            msgEl.textContent = 'Nomor bukti sudah digunakan pada kegiatan lain';
            msgEl.style.color = '#ef4444';
            input.style.borderColor = '#ef4444';
            noBuktiIsValid = false;
        }
    } catch (err) {
        console.error('Gagal cek no bukti:', err);
        hideNoBuktiStatus();
        noBuktiIsValid = true; // Allow submission, server will re-validate
    }
}

function hideNoBuktiStatus() {
    const statusEl = document.getElementById('noBuktiStatus');
    const msgEl = document.getElementById('noBuktiMessage');
    const input = document.getElementById('pengeluaranNoBukti');
    if (statusEl) statusEl.style.display = 'none';
    if (msgEl) msgEl.style.display = 'none';
    if (input) input.style.borderColor = '';
}

/* =========================
   SEARCHABLE SELECT (KODE REKENING)
========================= */
function bindRekeningSearchable() {
    const searchInput = document.getElementById('pengeluaranRekeningSearch');
    const dropdown = document.getElementById('pengeluaranRekeningDropdown');
    const hiddenInput = document.getElementById('pengeluaranRekening');
    if (!searchInput || !dropdown || !hiddenInput) return;

    searchInput.onfocus = function () {
        if (this.readOnly) return;
        renderRekeningDropdown(this.value);
        dropdown.style.display = 'block';
    };

    searchInput.oninput = function () {
        if (this.readOnly) return;
        rekeningDropdownIndex = -1;
        renderRekeningDropdown(this.value);
        dropdown.style.display = 'block';
        // Clear selection if user is typing
        hiddenInput.value = '';
    };

    searchInput.onkeydown = function (e) {
        const items = dropdown.querySelectorAll('.rek-option');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            rekeningDropdownIndex = Math.min(rekeningDropdownIndex + 1, items.length - 1);
            highlightRekeningOption(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            rekeningDropdownIndex = Math.max(rekeningDropdownIndex - 1, 0);
            highlightRekeningOption(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (rekeningDropdownIndex >= 0 && items[rekeningDropdownIndex]) {
                items[rekeningDropdownIndex].click();
            }
        } else if (e.key === 'Escape') {
            dropdown.style.display = 'none';
        }
    };

    // Close dropdown on click outside
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

function renderRekeningDropdown(keyword = '') {
    const dropdown = document.getElementById('pengeluaranRekeningDropdown');
    if (!dropdown) return;

    const kw = keyword.toLowerCase().trim();
    const filtered = kw
        ? rekeningOptions.filter(opt => opt.label.toLowerCase().includes(kw))
        : rekeningOptions;

    if (filtered.length === 0) {
        dropdown.innerHTML = '<div style="padding: 12px 16px; color: #94a3b8; font-size: 13px; text-align: center;">Tidak ada rekening ditemukan</div>';
        return;
    }

    dropdown.innerHTML = filtered.map((opt, i) => `
        <div class="rek-option" data-value="${opt.value}" data-index="${i}"
            style="padding: 10px 16px; cursor: pointer; font-size: 13px; line-height: 1.4;
                border-bottom: 1px solid #f1f5f9; transition: background 0.15s;"
            onmouseenter="this.style.background='#f0f4ff'"
            onmouseleave="this.style.background='${rekeningDropdownIndex === i ? '#eef2ff' : '#fff'}'"
            onclick="selectRekeningOption('${opt.value}', '${escapeAttr(opt.label)}')">
            <div style="font-weight: 600; color: #1e293b;">${highlightMatch(opt.kode, kw)}</div>
            <div style="color: #64748b; font-size: 12px; margin-top: 2px;">${highlightMatch(opt.nama, kw)}</div>
        </div>
    `).join('');
}

function highlightMatch(text, kw) {
    if (!kw) return escapeHtml(text);
    const escaped = escapeHtml(text);
    const regex = new RegExp(`(${kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return escaped.replace(regex, '<mark style="background:#fef08a; padding:0 1px; border-radius:2px;">$1</mark>');
}

function escapeAttr(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
}

function highlightRekeningOption(items) {
    items.forEach((item, i) => {
        item.style.background = i === rekeningDropdownIndex ? '#eef2ff' : '#fff';
    });
    if (items[rekeningDropdownIndex]) {
        items[rekeningDropdownIndex].scrollIntoView({ block: 'nearest' });
    }
}

window.selectRekeningOption = function (value, label) {
    const searchInput = document.getElementById('pengeluaranRekeningSearch');
    const hiddenInput = document.getElementById('pengeluaranRekening');
    const dropdown = document.getElementById('pengeluaranRekeningDropdown');

    if (searchInput) searchInput.value = label;
    if (hiddenInput) hiddenInput.value = value;
    if (dropdown) dropdown.style.display = 'none';
    rekeningDropdownIndex = -1;
};

/* =========================
   MODAL CONTROL
 ========================= */
window.openPengeluaranForm = function (kategori, id = null) {
    const modal = document.getElementById('pengeluaranModal');
    if (!modal) return;

    modal.classList.add('show');
    resetPengeluaranForm();

    document.getElementById('pengeluaranKategori').value = kategori;

    const titleEl = document.getElementById('pengeluaranModalTitle');
    const sppEl = document.getElementById('pengeluaranNoSPP');
    const spmEl = document.getElementById('pengeluaranNoSPM');
    const sp2dEl = document.getElementById('pengeluaranNoSP2D');

    if (id) {
        isEditPengeluaran = true;
        editPengeluaranId = id;
        titleEl.innerText = 'Edit Pengeluaran';
        // Keep read-only to prevent manual override during edit
        [sppEl, spmEl, sp2dEl].forEach(el => {
            if (el) {
                el.readOnly = true;
                el.style.background = '#f8fafc';
                el.style.cursor = 'not-allowed';
            }
        });
        loadEditData(id);
    } else {
        isEditPengeluaran = false;
        editPengeluaranId = null;
        titleEl.innerText = 'Tambah Pengeluaran';
        [sppEl, spmEl, sp2dEl].forEach(el => {
            if (el) {
                el.readOnly = true;
                el.style.background = '#f8fafc';
                el.style.cursor = 'not-allowed';
            }
        });
        document.getElementById('pengeluaranTanggal').value = window.getTodayLocal();
    }

    loadRekeningPengeluaran(kategori);
    bindCurrencyInputs();
    bindNoBuktiValidation();
    bindRekeningSearchable();
};

function bindCurrencyInputs() {
    const nomDisp = document.getElementById('pengeluaranNominalDisplay');
    const nomHidden = document.getElementById('pengeluaranNominalValue');
    const pajDisp = document.getElementById('pengeluaranPotonganPajakDisplay');
    const pajHidden = document.getElementById('pengeluaranPotonganPajakValue');

    if (!nomDisp || !pajDisp) return;

    const setup = (disp, hidden) => {
        if (!disp || !hidden) return;

        disp.oninput = function () {
            let val = this.value.replace(/\D/g, '');
            this.value = val;
            hidden.value = val || 0;
            if (window.calculateTotalDibayarkan) window.calculateTotalDibayarkan();
        };

        disp.onblur = function () {
            const val = parseFloat(hidden.value) || 0;
            this.value = val > 0 ? formatRibuan(val) : '';
        };

        disp.onfocus = function () {
            const val = parseFloat(hidden.value) || 0;
            this.value = val === 0 ? '' : val.toString();
        };
    };

    setup(nomDisp, nomHidden);
    setup(pajDisp, pajHidden);
}

window.closePengeluaranModal = function () {
    const modal = document.getElementById('pengeluaranModal');
    modal?.classList.remove('show');
};

function resetPengeluaranForm() {
    const form = document.getElementById('formPengeluaran');
    form?.reset();
    document.getElementById('pengeluaranId').value = '';
    document.getElementById('pengeluaranNominalValue').value = 0;
    document.getElementById('pengeluaranNominalDisplay').value = '';
    document.getElementById('pengeluaranPotonganPajakValue').value = 0;
    document.getElementById('pengeluaranPotonganPajakDisplay').value = '';
    document.getElementById('pengeluaranTotalDibayarkanValue').value = 0;
    document.getElementById('pengeluaranTotalDibayarkanDisplay').value = '';
    const guSection = document.getElementById('guCycleSection');
    if (guSection) guSection.style.display = 'none';
    const siklusSelect = document.getElementById('pengeluaranSiklus');
    if (siklusSelect) siklusSelect.value = '';

    // Clear disbursement link if exists
    const hiddenId = document.getElementById('pengeluaranFundDisbursementId');
    if (hiddenId) hiddenId.value = '';

    // Re-enable all options
    const metodeSelect = document.getElementById('pengeluaranMetode');
    if (metodeSelect) {
        Array.from(metodeSelect.options).forEach(opt => opt.disabled = false);
    }

    // Reset No. Bukti
    const noBuktiInput = document.getElementById('pengeluaranNoBukti');
    if (noBuktiInput) noBuktiInput.value = '';
    hideNoBuktiStatus();
    noBuktiIsValid = true;

    // Reset Searchable Select
    const rekeningSearch = document.getElementById('pengeluaranRekeningSearch');
    if (rekeningSearch) rekeningSearch.value = '';
    const rekeningHidden = document.getElementById('pengeluaranRekening');
    if (rekeningHidden) rekeningHidden.value = '';
}

window.calculateTotalDibayarkan = function () {
    const nomVal = document.getElementById('pengeluaranNominalValue');
    const pajVal = document.getElementById('pengeluaranPotonganPajakValue');
    const totVal = document.getElementById('pengeluaranTotalDibayarkanValue');
    const totDisp = document.getElementById('pengeluaranTotalDibayarkanDisplay');

    if (!nomVal || !totDisp) return;

    const nominal = parseFloat(nomVal.value) || 0;
    const pajak = parseFloat(pajVal?.value || 0) || 0;
    const total = nominal - pajak;

    if (totVal) totVal.value = total;
    if (totDisp) {
        // As long as there is a nominal value, show the total (even if it's 0)
        totDisp.value = (nominal > 0 || pajak > 0) ? formatRibuan(total) : '';
    }
};

/* =========================
   DATA LOADING
========================= */
function loadPengeluaran(page = 1) {
    pengeluaranPage = page;
    const tbody = document.querySelector('#tablePengeluaran tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" class="text-center">Memuat data...</td></tr>`;

    const params = new URLSearchParams({
        kategori: currentKategori,
        page: pengeluaranPage,
        limit: pengeluaranPerPage,
        search: pengeluaranKeyword,
        spending_type: pengeluaranType,
        sort_by: pengeluaranSortBy,
        sort_dir: pengeluaranSortDir
    });

    fetch(`/dashboard/expenditures?${params.toString()}`, {
        headers: { Accept: 'application/json' }
    })
        .then(res => {
            if (!res.ok) throw new Error(res.statusText || 'Gagal memuat data');
            return res.json();
        })
        .then(res => {
            // Update Summary Cards
            const countEl = document.getElementById('totalCountPengeluaran');
            const totalEl = document.getElementById('totalNominalPengeluaran');
            const taxEl = document.getElementById('totalPajakPengeluaran');
            const netEl = document.getElementById('totalDibayarkanPengeluaran');

            if (res.aggregates) {
                if (countEl) countEl.innerText = res.aggregates.total_count.toLocaleString('id-ID') + ' Transaksi';
                if (totalEl) totalEl.innerText = formatRupiah(res.aggregates.total_gross);
                if (taxEl) taxEl.innerText = formatRupiah(res.aggregates.total_tax || 0);
                if (netEl) netEl.innerText = formatRupiah(res.aggregates.total_net || 0);

                // Per-type cards
                if (document.getElementById('totalUP')) document.getElementById('totalUP').innerText = formatRupiah(res.aggregates.up.total);
                if (document.getElementById('countUP')) document.getElementById('countUP').innerText = res.aggregates.up.count + ' Transaksi';
                if (document.getElementById('totalGU')) document.getElementById('totalGU').innerText = formatRupiah(res.aggregates.gu.total);
                if (document.getElementById('countGU')) document.getElementById('countGU').innerText = res.aggregates.gu.count + ' Transaksi';
                if (document.getElementById('totalLS')) document.getElementById('totalLS').innerText = formatRupiah(res.aggregates.ls.total);
                if (document.getElementById('countLS')) document.getElementById('countLS').innerText = res.aggregates.ls.count + ' Transaksi';
            }

            const data = res.data || [];
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center">Belum ada data.</td></tr>`;
                renderPaginationPengeluaran(res);
                return;
            }

            tbody.innerHTML = '';
            let no = res.from || 1;
            data.forEach(item => {
                tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center">${no++}</td>
                    <td class="text-center">${formatTanggal(item.spending_date)}</td>
                    <td style="line-height: 1.4;">
                        <div class="flex flex-col gap-1">
                            <span class="badge-mini ${item.spending_type === 'UP' ? 'bg-orange-100 text-orange-700' :
                        (item.spending_type === 'GU' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700')
                    }">${item.spending_type}</span>
                            <div style="color: #6366f1; font-weight: 600; font-size: 0.75rem; margin-top: 2px;">BUKTI: ${item.no_bukti || '-'}</div>
                        </div>
                    </td>
                    <td>
                        <div class="font-medium">${escapeHtml(item.description)}</div>
                        <small class="text-slate-400">${item.vendor || 'Tanpa Vendor'}</small>
                    </td>
                    <td>
                        <div class="nominal-group">
                            <div class="nom-row">
                                <div class="nom-val val-bruto">${formatRupiahTable(item.gross_value)}</div>
                                <span class="nom-label label-bruto">Bruto</span>
                            </div>
                            <div class="nom-row">
                                <div class="nom-val val-pajak">${formatRupiahTable(item.tax || 0)}</div>
                                <span class="nom-label label-pajak">Pajak</span>
                            </div>
                            <div class="nom-row" style="margin-top: 2px; padding-top: 2px; border-top: 1px dashed #e2e8f0;">
                                <div class="nom-val val-netto">${formatRupiahTable(item.net_value || 0)}</div>
                                <span class="nom-label label-netto">Netto</span>
                            </div>
                        </div>
                    </td>
                </tr>
            `);
            });

            renderPaginationPengeluaran(res);
            updateSortIconsPengeluaran();
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-red-500">Gagal memuat data: ${err.message}.</td></tr>`;
        });
}

function renderPaginationPengeluaran(meta) {
    const info = document.getElementById('paginationInfoPengeluaran');
    if (info) info.innerText = `Menampilkan ${meta.from ?? 0}–${meta.to ?? 0} dari ${meta.total ?? 0} data`;

    const pageInfo = document.getElementById('pageInfoPengeluaran');
    if (pageInfo) pageInfo.innerText = `${meta.current_page} / ${meta.last_page}`;

    const prev = document.getElementById('prevPagePengeluaran');
    const next = document.getElementById('nextPagePengeluaran');

    if (prev) {
        prev.disabled = (meta.current_page === 1);
        prev.onclick = () => loadPengeluaran(meta.current_page - 1);
    }
    if (next) {
        next.disabled = (meta.current_page === meta.last_page);
        next.onclick = () => loadPengeluaran(meta.current_page + 1);
    }
}

function loadEditData(id) {
    fetch(`/dashboard/expenditures/${id}`, { headers: { Accept: 'application/json' } })
        .then(res => res.json())
        .then(data => {
            const idEl = document.getElementById('pengeluaranId');
            if (!idEl) return;

            idEl.value = data.id;

            const fdIdEl = document.getElementById('pengeluaranFundDisbursementId');
            if (fdIdEl) fdIdEl.value = data.fund_disbursement_id || '';

            if (data.spending_date) {
                // Ensure date doesn't shift by using local date parts if it's already a JS date-like string
                const d = new Date(data.spending_date);
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                document.getElementById('pengeluaranTanggal').value = `${year}-${month}-${day}`;
            }

            // No. Bukti
            const noBuktiInput = document.getElementById('pengeluaranNoBukti');
            if (noBuktiInput) noBuktiInput.value = data.no_bukti || '';

            document.getElementById('pengeluaranUraian').value = data.description;
            document.getElementById('pengeluaranVendor').value = data.vendor || '';

            document.getElementById('pengeluaranNominalValue').value = data.gross_value;
            document.getElementById('pengeluaranNominalDisplay').value = formatRibuan(data.gross_value);

            document.getElementById('pengeluaranPotonganPajakValue').value = data.tax || 0;
            document.getElementById('pengeluaranPotonganPajakDisplay').value = formatRibuan(data.tax || 0);

            if (window.calculateTotalDibayarkan) window.calculateTotalDibayarkan();

            document.getElementById('pengeluaranMetode').value = data.spending_type || '';
            const cycleSection = document.getElementById('guCycleSection');
            if (data.spending_type === 'GU') {
                cycleSection.style.display = 'block';
                loadAvailableGuCycles().then(() => {
                    document.getElementById('pengeluaranSiklus').value = data.siklus_up || '';
                });
            } else {
                cycleSection.style.display = 'none';
            }

            // Pilih rekening via searchable select
            let attempts = 0;
            const check = setInterval(() => {
                if (rekeningOptions.length > 0) {
                    const opt = rekeningOptions.find(o => o.value == data.kode_rekening_id);
                    if (opt) {
                        document.getElementById('pengeluaranRekening').value = opt.value;
                        document.getElementById('pengeluaranRekeningSearch').value = opt.label;
                    }
                    clearInterval(check);
                }
                attempts++;
                if (attempts > 50) clearInterval(check);
            }, 100);
        })
        .catch(err => {
            console.error(err);
            toast('Gagal memuat data edit', 'error');
        });
}

async function loadRekeningPengeluaran(kategori) {
    const hiddenInput = document.getElementById('pengeluaranRekening');
    const searchInput = document.getElementById('pengeluaranRekeningSearch');

    // Selalu reload jika kategori berbeda atau belum dimuat
    if (hiddenInput && hiddenInput.getAttribute('data-loaded-for') === kategori && rekeningOptions.length > 0) return;

    try {
        const res = await fetch('/dashboard/master/kode-rekening?category=PENGELUARAN', {
            headers: { 'Accept': 'application/json' }
        });
        const tree = await res.json();

        rekeningOptions = [];

        function flatten(nodes) {
            nodes.forEach(node => {
                if (node.tipe === 'detail') {
                    if (node.sumber_data === kategori) {
                        rekeningOptions.push({
                            value: node.id,
                            kode: node.kode,
                            nama: node.nama,
                            label: `${node.kode} — ${node.nama}`
                        });
                    }
                }
                if (node.children && node.children.length > 0) {
                    flatten(node.children);
                }
            });
        }

        flatten(tree);

        if (hiddenInput) hiddenInput.setAttribute('data-loaded-for', kategori);

        // Jika tidak ada yang cocok dengan mapping, tampilkan semua detail pengeluaran sebagai fallback
        if (rekeningOptions.length === 0) {
            function flattenAll(nodes) {
                nodes.forEach(node => {
                    if (node.tipe === 'detail') {
                        rekeningOptions.push({
                            value: node.id,
                            kode: node.kode,
                            nama: node.nama,
                            label: `${node.kode} — ${node.nama}`
                        });
                    }
                    if (node.children && node.children.length > 0) {
                        flattenAll(node.children);
                    }
                });
            }
            flattenAll(tree);
            if (hiddenInput) hiddenInput.setAttribute('data-loaded-for', 'ALL');
        }

    } catch (err) {
        console.error(err);
    }
}

/* =========================
   SUBMIT & ACTIONS
========================= */
window.submitPengeluaran = async function (event) {
    event.preventDefault();

    // Check No. Bukti validity before submit
    if (!noBuktiIsValid) {
        toast('Nomor bukti sudah digunakan pada kegiatan lain. Gunakan nomor lain.', 'error');
        return;
    }

    const form = document.getElementById('formPengeluaran');
    const formData = new FormData(form);

    if (!formData.get('kode_rekening_id')) {
        toast('Silakan pilih kode rekening terlebih dahulu.', 'error');
        return;
    }

    const btn = document.getElementById('btnSimpanPengeluaran');

    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    const id = formData.get('id');
    const url = id ? `/dashboard/expenditures/${id}` : '/dashboard/expenditures';

    // Map frontend fields (if different) to API fields
    const apiData = {
        spending_date: formData.get('tanggal'),
        kode_rekening_id: formData.get('kode_rekening_id'),
        description: formData.get('uraian'),
        gross_value: formData.get('nominal'),
        tax: formData.get('potongan_pajak') || 0,
        spending_type: formData.get('metode_pembayaran'),
        siklus_up: formData.get('siklus_up'),
        vendor: formData.get('vendor') || '',
        fund_disbursement_id: formData.get('fund_disbursement_id') || null,
        no_bukti: formData.get('no_bukti') || '',
    };

    if (id) apiData['_method'] = 'PUT';

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(apiData)
        });

        if (!res.ok) {
            const err = await res.json();
            if (err.errors) {
                const firstErrorKey = Object.keys(err.errors)[0];
                throw new Error(err.errors[firstErrorKey][0]);
            }
            throw new Error(err.message || 'Gagal menyimpan data');
        }

        toast('Data berhasil disimpan', 'success');
        closePengeluaranModal();

        // Refresh appropriate lists
        if (typeof loadPengeluaran === 'function' && document.getElementById('tablePengeluaran')) {
            loadPengeluaran();
        }

        // If we are in the "Manage Activities" view in Pencairan
        if (typeof loadBelanjaItems === 'function' && window.currentBelanjaDisbursement) {
            loadBelanjaItems(window.currentBelanjaDisbursement.id);
        }
    } catch (err) {
        toast(err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerText = 'Simpan';
    }
};

window.handleSearchPengeluaran = function (e) {
    const val = e.target.value.trim();
    if (window.pengeluaranSearchTimer) clearTimeout(window.pengeluaranSearchTimer);
    window.pengeluaranSearchTimer = setTimeout(() => {
        pengeluaranKeyword = val;
        loadPengeluaran(1);
    }, 400);
};

window.handleFilterType = function (val) {
    pengeluaranType = val;
    loadPengeluaran(1);
};

window.hapusPengeluaran = function (id) {
    openConfirm(
        'Hapus Transaksi',
        'Yakin ingin menghapus data pengeluaran ini? Data yang dihapus tidak dapat dikembalikan.',
        () => {
            fetch(`/dashboard/expenditures/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(async res => {
                    if (!res.ok) {
                        const err = await res.json();
                        throw new Error(err.message || 'Gagal menghapus data');
                    }
                    toast('Data berhasil dihapus', 'success');
                    loadPengeluaran();
                })
                .catch((err) => toast(err.message || 'Gagal menghapus data', 'error'));
        }
    );
};

/* =========================
   INITIALIZATION
========================= */


window.openPengeluaranDetail = function (id) {
    const modal = document.getElementById('pengeluaranDetailModal');
    const content = document.getElementById('detailPengeluaranContent');
    if (!modal || !content) return;

    content.innerHTML = '<div class="col-span-2 text-center py-4"><i class="ph ph-spinner animate-spin text-2xl"></i></div>';
    modal.classList.add('show');

    fetch(`/dashboard/expenditures/${id}`, { headers: { Accept: 'application/json' } })
        .then(res => res.json())
        .then(data => {
            const rek = data.kode_rekening || {};
            content.innerHTML = `
                <div class="detail-row">
                    <span class="label">Tanggal</span>
                    <span class="value">${formatTanggal(data.spending_date)}</span>
                </div>
                <div class="detail-row">
                    <span class="label">No. Bukti</span>
                    <span class="value" style="color: #6366f1; font-weight: 700; font-family: monospace;">${data.no_bukti || '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Kode Rekening</span>
                    <span class="value"><code>${rek.kode ?? '-'}</code></span>
                </div>
                <div class="detail-row">
                    <span class="label">Nama Rekening</span>
                    <span class="value">${rek.nama ?? '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Vendor</span>
                    <span class="value">${data.vendor || '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Uraian</span>
                    <span class="value">${escapeHtml(data.description)}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Metode Pembayaran</span>
                    <span class="value">${data.spending_type === 'UP' ? 'Uang Persediaan' : (data.spending_type === 'GU' ? 'Ganti Uang' : (data.spending_type === 'LS' ? 'Langsung' : '-'))}</span>
                </div>
                
                <div class="detail-total mt-4">
                    <span>Bruto (Rp)</span>
                    <strong>${formatRupiah(data.gross_value)}</strong>
                </div>
                <div class="detail-row" style="margin-top: 12px; border-bottom: none; padding-bottom: 0;">
                    <span class="label">Potongan Pajak</span>
                    <span class="value text-red-500">${formatRupiah(data.tax || 0)}</span>
                </div>
                <div class="detail-total mt-2" style="background: #f0fdf4; border-color: #86efac;">
                    <span style="color: #166534;">Netto (Total Dibayar)</span>
                    <strong style="color: #15803d;">${formatRupiah(data.net_value || 0)}</strong>
                </div>
            `;
        })
        .catch(err => {
            content.innerHTML = '<div class="col-span-2 text-center text-red-500 py-4">Gagal memuat data</div>';
        });
};

window.closeDetailPengeluaran = function () {
    const modal = document.getElementById('pengeluaranDetailModal');
    modal?.classList.remove('show');
};

window.sortPengeluaran = function (col) {
    if (pengeluaranSortBy === col) {
        pengeluaranSortDir = pengeluaranSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        pengeluaranSortBy = col;
        pengeluaranSortDir = 'asc';
    }
    loadPengeluaran(1);
}

function updateSortIconsPengeluaran() {
    document.querySelectorAll('#tablePengeluaran th.sortable i').forEach(i => {
        i.className = 'ph ph-caret-up-down text-slate-400';
    });
    const activeHeader = document.querySelector(`#tablePengeluaran th.sortable[data-sort="${pengeluaranSortBy}"]`);
    if (activeHeader) {
        const i = activeHeader.querySelector('i');
        if (i) {
            i.className = pengeluaranSortDir === 'asc' ? 'ph ph-caret-up text-blue-600' : 'ph ph-caret-down text-blue-600';
        }
    }
}
