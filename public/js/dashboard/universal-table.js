/**
 * Universal Table System - Performance Engine
 * High-performance sorting and live searching with minimal DOM reflow.
 */

(function () {
    // --- HELPER: Parse values for sorting (Handles Numbers, Currency, Dates, Strings) ---
    const getSortValue = (cell) => {
        if (!cell) return "";
        let val = cell.textContent.trim();

        // 1. Check if it's financial/number (remove Rp, dots, commas)
        if (val.includes("Rp") || /^-?[\d.,]+$/.test(val)) {
            let clean = val.replace(/Rp\s?|[.]/g, "").replace(/,/g, ".");
            let num = parseFloat(clean);
            if (!isNaN(num)) return num;
        }

        // 2. Check if it's a date
        const date = Date.parse(val);
        if (!isNaN(date) && isNaN(val)) return date;

        return val.toLowerCase();
    };

    // --- SORTING ENGINE ---
    window.initUniversalSorting = function (tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const headers = table.querySelectorAll("th.sortable");
        headers.forEach((header) => {
            // Add icon if missing
            if (!header.querySelector(".sort-icon")) {
                const icon = document.createElement("i");
                icon.className = "ph-bold ph-caret-up-down sort-icon";
                header.appendChild(icon);
            }

            header.addEventListener("click", function () {
                const tbody = table.querySelector("tbody");
                const rows = Array.from(tbody.querySelectorAll("tr:not(.no-result-row)"));
                if (rows.length < 2) return;

                const index = Array.from(header.parentElement.children).indexOf(header);
                const isAsc = header.classList.contains("asc");

                // Reset all headers in this table
                headers.forEach((h) => {
                    h.classList.remove("asc", "desc");
                    const icon = h.querySelector(".sort-icon");
                    if (icon) icon.className = "ph-bold ph-caret-up-down sort-icon";
                });

                // Set active header state
                header.classList.toggle("asc", !isAsc);
                header.classList.toggle("desc", isAsc);
                const currentIcon = header.querySelector(".sort-icon");
                if (currentIcon) {
                    currentIcon.className = isAsc ? "ph-bold ph-caret-down sort-icon" : "ph-bold ph-caret-up sort-icon";
                }

                // Sort logic
                rows.sort((a, b) => {
                    const aVal = getSortValue(a.children[index]);
                    const bVal = getSortValue(b.children[index]);

                    if (aVal === bVal) return 0;
                    const comparison = aVal > bVal ? 1 : -1;
                    return isAsc ? -comparison : comparison;
                });

                // PERFORMANCE: Use DocumentFragment to avoid multiple reflows
                const fragment = document.createDocumentFragment();
                rows.forEach((row) => fragment.appendChild(row));
                tbody.innerHTML = "";
                tbody.appendChild(fragment);
            });
        });
    };

    // --- SEARCH ENGINE (Live Filter) ---
    window.initUniversalSearch = function (inputId, tableId) {
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        if (!input || !table) return;

        input.addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();
            const tbody = table.querySelector("tbody");
            const rows = tbody.querySelectorAll("tr:not(.no-result-row)");
            let visibleCount = 0;

            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                const isMatch = text.includes(query);
                row.style.display = isMatch ? "" : "none";
                if (isMatch) visibleCount++;
            });

            // Handle "No Results" display
            let emptyMsg = tbody.querySelector(".no-result-row");
            if (visibleCount === 0) {
                if (!emptyMsg) {
                    emptyMsg = document.createElement("tr");
                    emptyMsg.className = "no-result-row";
                    const colCount = table.querySelectorAll("thead th").length;
                    emptyMsg.innerHTML = `<td colspan="${colCount}" class="text-center py-8 text-slate-400">
                        <i class="ph ph-magnifying-glass" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                        Data tidak ditemukan untuk pencarian "<strong>${query}</strong>"
                    </td>`;
                    tbody.appendChild(emptyMsg);
                } else {
                    emptyMsg.style.display = "";
                    emptyMsg.querySelector("strong").textContent = query;
                }
            } else if (emptyMsg) {
                emptyMsg.style.display = "none";
            }
        });
    };

    // --- GLOBAL AUTO-INIT ---
    window.initUniversalTables = function () {
        document.querySelectorAll(".universal-table").forEach((table) => {
            // 1. Auto-assign ID if missing
            if (!table.id) table.id = "table_" + Math.random().toString(36).substr(2, 6);

            // 2. Init sorting if sortable headers exist
            if (table.querySelector("th.sortable")) {
                initUniversalSorting(table.id);
            }

            // 3. Auto-link search input if data-table attribute matches
            const searchInput = document.querySelector(`input[data-table="${table.id}"]`);
            if (searchInput) {
                initUniversalSearch(searchInput.id, table.id);
            }
        });
    };

    // Run on load
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initUniversalTables);
    } else {
        initUniversalTables();
    }
})();

