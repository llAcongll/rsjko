/* =========================
   BANK ACCOUNT LEDGER (REKENING KORAN PENGELUARAN)
   ========================= */

(function () {
    const fmt = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
    const fmtTgl = (d) => {
        const dt = new Date(d);
        return dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    };

    let currentSaldoAwalAmount = 0;
    let cachedLedgerData = [];
    let bankLedgerSortBy = 'date';
    let bankLedgerSortDir = 'desc';

    window.initBankLedger = function () {
        const now = new Date();
        const mEl = document.getElementById('bankLedgerMonth');
        const yEl = document.getElementById('bankLedgerYear');

        if (mEl && !mEl.value) mEl.value = now.getMonth() + 1;
        if (yEl && !yEl.value) yEl.value = now.getFullYear();

        // Bind currency input for deposit
        const display = document.getElementById('bankLedgerAmountDisplay');
        const hidden = document.getElementById('bankLedgerAmountValue');
        if (display && hidden) {
            display.oninput = () => { hidden.value = parseAngka(display.value); };
            display.onblur = () => { display.value = formatRibuan(hidden.value); };
            display.onfocus = () => {
                const val = parseAngka(display.value);
                display.value = val === 0 ? '' : val.toString().replace('.', ',');
            };
        }

        // Bind currency input for adjustment
        const displayAdj = document.getElementById('adjustmentAmountDisplay');
        const hiddenAdj = document.getElementById('adjustmentAmountValue');
        if (displayAdj && hiddenAdj) {
            displayAdj.oninput = () => { hiddenAdj.value = parseAngka(displayAdj.value); };
            displayAdj.onblur = () => { displayAdj.value = formatRibuan(hiddenAdj.value); };
            displayAdj.onfocus = () => {
                const val = parseAngka(displayAdj.value);
                displayAdj.value = val === 0 ? '' : val.toString().replace('.', ',');
            };
        }

        loadBankLedger();
    };

    window.openDepositModal = function () {
        const form = document.getElementById('formBankLedger');
        if (form) form.reset();

        document.getElementById('bankLedgerId').value = '';
        document.getElementById('bankLedgerModalTitle').innerHTML = '<i class="ph ph-bank"></i> Tambah Saldo Rekening';

        const dateEl = document.getElementById('bankLedgerDate');
        if (dateEl) dateEl.value = window.getTodayLocal();

        const hiddenAmount = document.getElementById('bankLedgerAmountValue');
        if (hiddenAmount) hiddenAmount.value = 0;

        const displayAmount = document.getElementById('bankLedgerAmountDisplay');
        if (displayAmount) displayAmount.value = '0';

        const modal = document.getElementById('modalBankLedger');
        if (modal) modal.classList.add('show');
    };

    window.closeBankLedgerModal = function () {
        const modal = document.getElementById('modalBankLedger');
        if (modal) modal.classList.remove('show');
    };

    // --- SALDO AWAL MODAL ---
    window.openBankLedgerSaldoAwalModal = function () {
        const form = document.getElementById('formBankLedgerSaldoAwal');
        if (form) form.reset();

        document.getElementById('bankLedgerSaldoAwalValue').value = 0;
        document.getElementById('bankLedgerSaldoAwalDisplayInput').value = '';

        // Bind currency logic for this specific input
        const display = document.getElementById('bankLedgerSaldoAwalDisplayInput');
        const hidden = document.getElementById('bankLedgerSaldoAwalValue');
        if (display && hidden) {
            display.oninput = () => { hidden.value = parseAngka(display.value); };
            display.onblur = () => { display.value = formatRibuan(hidden.value); };
            display.onfocus = () => {
                const val = parseAngka(display.value);
                display.value = val === 0 ? '' : val.toString().replace('.', ',');
            };
        }

        const bankSelect = document.getElementById('bankLedgerSaldoAwalBank');
        const mainFilterBank = document.getElementById('bankLedgerBank');

        if (bankSelect) {
            if (mainFilterBank && mainFilterBank.value && mainFilterBank.value !== 'Semua Bank') {
                bankSelect.value = mainFilterBank.value;
            } else {
                bankSelect.value = '';
            }

            bankSelect.onchange = function () {
                const bankVal = this.value;
                if (!bankVal) return;
                fetch(`/dashboard/bank-account-ledger/saldo-awal?bank=${encodeURIComponent(bankVal)}`, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(res => res.json())
                    .then(data => {
                        const amt = parseFloat(data.amount) || 0;
                        document.getElementById('bankLedgerSaldoAwalValue').value = amt;
                        if (display) {
                            display.value = amt > 0 ? formatRibuan(amt) : '';
                        }
                        const btnHapus = document.getElementById('btnHapusBankLedgerSaldoAwal');
                        if (btnHapus) {
                            btnHapus.style.display = amt > 0 ? 'inline-flex' : 'none';
                        }
                    })
                    .catch(err => console.error(err));
            };

            if (bankSelect.value) {
                bankSelect.dispatchEvent(new Event('change'));
            } else {
                const btnHapus = document.getElementById('btnHapusBankLedgerSaldoAwal');
                if (btnHapus) btnHapus.style.display = 'none';
            }
        }

        const modal = document.getElementById('modalBankLedgerSaldoAwal');
        if (modal) modal.classList.add('show');
    };

    window.closeBankLedgerSaldoAwalModal = function () {
        const modal = document.getElementById('modalBankLedgerSaldoAwal');
        if (modal) modal.classList.remove('show');
    };

    window.deleteBankLedgerSaldoAwal = function () {
        if (!confirm('Yakin ingin menghapus saldo awal tahun?')) return;

        const bankVal = document.getElementById('bankLedgerSaldoAwalBank').value;
        if (!bankVal) {
            toast('Pilih bank terlebih dahulu', 'error');
            return;
        }

        fetch('/dashboard/bank-account-ledger/saldo-awal', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ bank: bankVal })
        })
            .then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menghapus');
                toast(json.message || 'Saldo awal dihapus', 'success');
                closeBankLedgerSaldoAwalModal();
                loadBankLedger();
            })
            .catch(err => toast(err.message, 'error'));
    };

    window.submitBankLedgerSaldoAwal = function (e) {
        e.preventDefault();
        const form = document.getElementById('formBankLedgerSaldoAwal');
        const data = Object.fromEntries(new FormData(form));

        fetch('/dashboard/bank-account-ledger/saldo-awal', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ amount: data.amount, bank: data.bank })
        })
            .then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menyimpan saldo awal');
                toast(json.message || 'Saldo awal berhasil diset', 'success');
                closeBankLedgerSaldoAwalModal();
                loadBankLedger();
            })
            .catch(err => toast(err.message, 'error'));
    };

    // --- ADJUSTMENT MODAL ---
    window.openAdjustmentModal = function () {
        const form = document.getElementById('formAdjustment');
        if (form) form.reset();

        document.getElementById('adjustmentId').value = '';
        document.getElementById('adjustmentModalTitle').innerHTML = '<i class="ph ph-sliders"></i> Penyesuaian Saldo';

        const dateEl = document.getElementById('adjustmentDate');
        if (dateEl) dateEl.value = window.getTodayLocal();

        const hiddenAmount = document.getElementById('adjustmentAmountValue');
        if (hiddenAmount) hiddenAmount.value = 0;

        const displayAmount = document.getElementById('adjustmentAmountDisplay');
        if (displayAmount) displayAmount.value = '0';

        const modal = document.getElementById('modalAdjustment');
        if (modal) modal.classList.add('show');
    };

    window.closeAdjustmentModal = function () {
        const modal = document.getElementById('modalAdjustment');
        if (modal) modal.classList.remove('show');
    };

    window.submitAdjustment = function (e) {
        e.preventDefault();
        const form = document.getElementById('formAdjustment');
        const data = Object.fromEntries(new FormData(form));
        const id = document.getElementById('adjustmentId').value;

        const url = id ? `/dashboard/bank-account-ledger/adjustment/${id}` : '/dashboard/bank-account-ledger/adjustment';
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menyimpan data');
                toast(json.message || 'Berhasil menyimpan data', 'success');
                closeAdjustmentModal();
                loadBankLedger();
            })
            .catch(err => toast(err.message, 'error'));
    };

    window.editAdjustment = function (id) {
        const item = cachedLedgerData.find(i => i.id === id);
        if (!item) return;

        window.openAdjustmentModal();
        document.getElementById('adjustmentId').value = item.id;
        document.getElementById('adjustmentModalTitle').innerHTML = '<i class="ph ph-pencil-simple"></i> Edit Penyesuaian Saldo';
        document.getElementById('adjustmentDate').value = item.date.split(' ')[0];
        document.getElementById('adjustmentDirection').value = item.debit > 0 ? 'DEBIT' : 'CREDIT';
        document.getElementById('adjustmentDescription').value = item.description;
        document.getElementById('adjustmentBank').value = item.bank || '';
        const amount = item.debit > 0 ? item.debit : item.credit;
        document.getElementById('adjustmentAmountValue').value = amount;
        document.getElementById('adjustmentAmountDisplay').value = Number(amount).toLocaleString('id-ID');
    };

    window.deleteAdjustment = function (id) {
        if (!confirm('Yakin ingin menghapus mutasi penyesuaian ini?')) return;

        fetch(`/dashboard/bank-account-ledger/adjustment/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
            .then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menghapus');
                toast('Mutasi penyesuaian berhasil dihapus', 'success');
                loadBankLedger();
            })
            .catch(err => toast(err.message, 'error'));
    };

    window.submitBankLedger = function (e) {
        e.preventDefault();
        const form = document.getElementById('formBankLedger');
        const data = Object.fromEntries(new FormData(form));
        const id = document.getElementById('bankLedgerId').value;

        const url = id ? `/dashboard/bank-account-ledger/deposit/${id}` : '/dashboard/bank-account-ledger/deposit';
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menyimpan data');
                toast(json.message || 'Berhasil menyimpan data', 'success');
                closeBankLedgerModal();
                loadBankLedger();
            })
            .catch(err => toast(err.message, 'error'));
    };

    window.editDeposit = function (id) {
        const item = cachedLedgerData.find(i => i.id === id);
        if (!item) return;

        window.openDepositModal();
        document.getElementById('bankLedgerId').value = item.id;
        document.getElementById('bankLedgerModalTitle').innerHTML = '<i class="ph ph-pencil-simple"></i> Edit Setoran Manual';
        document.getElementById('bankLedgerDate').value = item.date.split(' ')[0];
        document.getElementById('bankLedgerDescription').value = item.description;
        document.getElementById('bankLedgerDepositBank').value = item.bank || '';
        document.getElementById('bankLedgerAmountValue').value = item.debit;
        document.getElementById('bankLedgerAmountDisplay').value = Number(item.debit).toLocaleString('id-ID');
    };

    window.deleteDeposit = function (id) {
        if (!confirm('Yakin ingin menghapus mutasi setoran manual ini? Data di BKU juga akan ikut dihapus.')) return;

        fetch(`/dashboard/bank-account-ledger/deposit/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
            .then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menghapus');
                toast('Mutasi deposit berhasil dihapus', 'success');
                loadBankLedger();
            })
            .catch(err => toast(err.message, 'error'));
    };

    window.viewDetailFromLedger = function (id) {
        const item = cachedLedgerData.find(i => i.id === id);
        if (!item || !item.ref_table || !item.ref_id) {
            toast('Detail transaksi tidak tersedia untuk item ini.', 'info');
            return;
        }

        if (item.ref_table === 'fund_disbursements') {
            if (typeof window.viewDisbursement === 'function') {
                window.viewDisbursement(item.ref_id);
            } else {
                toast('Modul Pencairan tidak ditemukan.', 'error');
            }
        } else if (item.ref_table === 'expenditures') {
            if (typeof window.openPengeluaranDetail === 'function') {
                window.openPengeluaranDetail(item.ref_id);
            } else {
                toast('Modul Pengeluaran tidak ditemukan.', 'error');
            }
        } else {
            toast('Detail untuk tipe ' + item.ref_table + ' belum didukung.', 'info');
        }
    };

    window.loadBankLedger = function () {
        const mEl = document.getElementById('bankLedgerMonth');
        const yEl = document.getElementById('bankLedgerYear');
        if (!mEl || !yEl) return;

        const month = mEl.value;
        const year = yEl.value;
        const bankEl = document.getElementById('bankLedgerBank');
        const bank = bankEl ? bankEl.value : '';
        const tbody = document.getElementById('tableBankLedgerBody');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="9" class="text-center"><i class="ph ph-spinner animate-spin"></i> Memuat data...</td></tr>';

        fetch(`/dashboard/bank-account-ledger?month=${month}&year=${year}&bank=${encodeURIComponent(bank)}&sort_by=${bankLedgerSortBy}&sort_dir=${bankLedgerSortDir}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(res => {
                cachedLedgerData = res.data || [];
                const balEl = document.getElementById('bankLedgerCurrentBalance');
                if (balEl) balEl.textContent = fmt(res.current_balance);

                let saldoAwalDisplay = res.saldo_awal || 0;
                currentSaldoAwalAmount = saldoAwalDisplay;

                if (cachedLedgerData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center">Belum ada mutasi rekening koran.</td></tr>';
                    const saldoDisplayEl = document.getElementById('bankLedgerSaldoAwalDisplay');
                    if (saldoDisplayEl) saldoDisplayEl.textContent = `Saldo Awal Tahun: ${fmt(saldoAwalDisplay)}`;
                    return;
                }

                let html = '';
                let indexDisplay = 1;

                cachedLedgerData.forEach((item) => {
                    if (item.type === 'SALDO_AWAL') {
                        return; // Skip rendering in table body
                    }

                    const badgeClass = item.credit > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';

                    let actionHtml = '';

                    if (item.type === 'DEPOSIT_MANUAL' || item.type === 'PENYESUAIAN') {
                        const editFn = item.type === 'PENYESUAIAN' ? 'editAdjustment' : 'editDeposit';
                        const deleteFn = item.type === 'PENYESUAIAN' ? 'deleteAdjustment' : 'deleteDeposit';
                        actionHtml = `
                            <div class="flex justify-center gap-2">
                                <button class="btn-aksi edit" title="Edit" onclick="${editFn}(${item.id})">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn-aksi delete" title="Hapus" onclick="${deleteFn}(${item.id})">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        `;
                    } else if (item.ref_table && item.ref_id) {
                        actionHtml = `
                            <div class="flex justify-center gap-2">
                                <button class="btn-aksi detail" title="Lihat Detail" onclick="viewDetailFromLedger(${item.id})">
                                    <i class="ph ph-eye"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        actionHtml = '<span class="text-slate-400">-</span>';
                    }

                    html += `
                    <tr>
                        <td class="text-center">${indexDisplay++}</td>
                        <td class="text-center">${fmtTgl(item.date)}</td>
                        <td class="text-center">
                            <span class="badge-mini ${badgeClass}" style="font-size:0.65rem; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                                ${item.type}
                            </span>
                        </td>
                        <td>${item.description || '-'}</td>
                        <td class="text-center">${item.bank || '-'}</td>
                        <td class="text-right text-green-700 font-medium">${item.debit > 0 ? fmt(item.debit) : '-'}</td>
                        <td class="text-right text-red-700 font-medium">${item.credit > 0 ? fmt(item.credit) : '-'}</td>
                        <td class="text-right font-bold" style="color: #0f172a;">${fmt(item.balance)}</td>
                        <td class="text-center">${actionHtml}</td>
                    </tr>
                `;
                });
                tbody.innerHTML = html;
                updateSortIconsBankLedger();

                const btnSetSaldo = document.getElementById('btnSetSaldoAwal');
                if (btnSetSaldo) {
                    btnSetSaldo.style.display = (month == 1) ? 'inline-flex' : 'none';
                }

                const saldoDisplayEl = document.getElementById('bankLedgerSaldoAwalDisplay');
                if (saldoDisplayEl) {
                    const label = (month == 1) ? 'Saldo Awal Tahun' : 'Saldo Awal Bulan';
                    saldoDisplayEl.textContent = `${label}: ${fmt(saldoAwalDisplay)}`;
                }
            })

            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-red-500">Gagal memuat data</td></tr>';
            });
    };

    window.sortBankLedger = function (col) {
        if (bankLedgerSortBy === col) {
            bankLedgerSortDir = bankLedgerSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            bankLedgerSortBy = col;
            bankLedgerSortDir = 'asc';
        }
        loadBankLedger();
    };

    function updateSortIconsBankLedger() {
        document.querySelectorAll('#tableBankLedger th.sortable i').forEach(i => {
            i.className = 'ph ph-caret-up-down text-slate-400';
        });
        const activeHeader = document.querySelector(`#tableBankLedger th.sortable[data-sort="${bankLedgerSortBy}"]`);
        if (activeHeader) {
            const i = activeHeader.querySelector('i');
            if (i) {
                i.className = bankLedgerSortDir === 'asc' ? 'ph ph-caret-up text-blue-600' : 'ph ph-caret-down text-blue-600';
            }
        }
    }
})();
