/**
 * Universal admin-table enhancer.
 *
 * For any <table data-admin-table>, this adds:
 *   - A search input that filters visible rows (case-insensitive,
 *     matches across every cell except the actions cell)
 *   - A page-size selector (default 25; options 10 / 25 / 50 / 100 / All)
 *   - A simple Prev / Next pager + "Showing X-Y of Z" counter
 *
 * The page-size + prev/next operate on the rows the SERVER returned
 * (the existing CI pagination keeps working for big lists). For
 * small admin tables (services / campaigns / claims) this gives
 * pagination without any controller change.
 *
 * Existing `<input id="pg-sear">` boxes in the legacy admin views
 * are auto-bound to the nearest <table> below them — no markup
 * change required for those pages.
 */
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    function enhance(table, opts) {
        opts = opts || {};
        if (table.dataset.adminTableEnhanced === '1') return;
        table.dataset.adminTableEnhanced = '1';

        var pageSize = parseInt(table.dataset.pageSize || opts.pageSize || '25', 10);
        var currentPage = 1;
        var allRows = Array.prototype.slice.call(table.tBodies[0] ? table.tBodies[0].rows : []);
        if (!allRows.length) return;

        // Build a toolbar (search + page-size) above the table if not present.
        var toolbar = document.createElement('div');
        toolbar.className = 'admin-table-toolbar';
        toolbar.style.cssText = 'display:flex;gap:12px;align-items:center;margin-bottom:12px;flex-wrap:wrap;';
        toolbar.innerHTML =
            '<input type="search" class="form-control admin-table-search" placeholder="Search this page…" style="max-width:280px;">' +
            '<label style="margin:0;color:#6b7280;font-size:0.9rem;">Show ' +
                '<select class="admin-table-size form-control" style="display:inline-block;width:auto;margin:0 6px;">' +
                    '<option value="10">10</option>' +
                    '<option value="25" selected>25</option>' +
                    '<option value="50">50</option>' +
                    '<option value="100">100</option>' +
                    '<option value="0">All</option>' +
                '</select>' +
            'per page</label>' +
            '<span class="admin-table-counter" style="color:#6b7280;font-size:0.9rem;"></span>' +
            '<span style="flex:1;"></span>' +
            '<button type="button" class="btn btn-sm btn-outline-primary admin-table-prev">&larr; Prev</button>' +
            '<button type="button" class="btn btn-sm btn-outline-primary admin-table-next">Next &rarr;</button>';

        // If a legacy #pg-sear input is already in the DOM above the
        // table, hide it and use the new search box (or repurpose it).
        var legacy = document.getElementById('pg-sear');
        if (legacy && legacy.compareDocumentPosition(table) & Node.DOCUMENT_POSITION_FOLLOWING) {
            legacy.style.display = 'none';
        }
        table.parentNode.insertBefore(toolbar, table);

        var searchInput = toolbar.querySelector('.admin-table-search');
        var sizeSelect  = toolbar.querySelector('.admin-table-size');
        var counter     = toolbar.querySelector('.admin-table-counter');
        var prevBtn     = toolbar.querySelector('.admin-table-prev');
        var nextBtn     = toolbar.querySelector('.admin-table-next');

        sizeSelect.value = String(pageSize);

        function rowText(row) {
            // Cache row text on the element to avoid repeated DOM reads.
            if (row.dataset.adminTableText !== undefined) return row.dataset.adminTableText;
            var cells = row.cells;
            var parts = [];
            for (var i = 0; i < cells.length; i++) {
                // Skip the last column if it looks like an actions cell.
                if (i === cells.length - 1 && /Edit|Delete|View/.test(cells[i].textContent)) continue;
                parts.push((cells[i].innerText || cells[i].textContent || '').trim());
            }
            var s = parts.join(' ┃ ').toLowerCase();
            row.dataset.adminTableText = s;
            return s;
        }

        function render() {
            var q = (searchInput.value || '').trim().toLowerCase();
            var filtered = q ? allRows.filter(function (r) { return rowText(r).indexOf(q) !== -1; }) : allRows;

            var size = parseInt(sizeSelect.value, 10) || filtered.length;
            var pages = Math.max(1, Math.ceil(filtered.length / size));
            if (currentPage > pages) currentPage = pages;

            var start = (currentPage - 1) * size;
            var end = size ? Math.min(filtered.length, start + size) : filtered.length;

            // Hide everything, then show the rows in the active window.
            for (var i = 0; i < allRows.length; i++) allRows[i].style.display = 'none';
            for (var j = start; j < end; j++) filtered[j].style.display = '';

            if (filtered.length === 0) {
                counter.textContent = 'No matches';
            } else {
                counter.textContent = 'Showing ' + (start + 1) + '–' + end + ' of ' + filtered.length;
            }
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= pages;
        }

        searchInput.addEventListener('input', function () { currentPage = 1; render(); });
        sizeSelect.addEventListener('change', function () { currentPage = 1; render(); });
        prevBtn.addEventListener('click', function () { if (currentPage > 1) { currentPage--; render(); } });
        nextBtn.addEventListener('click', function () { currentPage++; render(); });

        render();
    }

    ready(function () {
        var explicit = document.querySelectorAll('table[data-admin-table]');
        for (var i = 0; i < explicit.length; i++) enhance(explicit[i]);

        // Legacy: a #pg-sear input is a strong signal that the next
        // <table> wants search. Wire it up automatically.
        var legacy = document.getElementById('pg-sear');
        if (legacy) {
            var t = legacy.parentNode;
            while (t && t.tagName !== 'TABLE') {
                t = t.nextElementSibling || (t.parentNode && t.parentNode.nextElementSibling);
                if (!t) break;
                if (t.tagName === 'TABLE') break;
                var inner = t.querySelector ? t.querySelector('table') : null;
                if (inner) { t = inner; break; }
            }
            if (t && t.tagName === 'TABLE') enhance(t);
        }
    });
})();
