/**
 * Universal admin-table enhancer.
 *
 * For any <table data-admin-table>, this adds:
 *   - A search input above the table (filters visible rows, case-insensitive,
 *     matches across every cell except the actions cell)
 *   - Numbered pagination BELOW the table (Bootstrap .pagination styling,
 *     matching the server-side CI pager used on the big list pages)
 *
 * Page size is fixed per table via data-page-size (default 10). The
 * client-side pager only renders when the filtered rows exceed pageSize,
 * so tables that already use CI server-side pagination see no duplicate
 * pager (the server has already trimmed the rowset to one page).
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

        // Rows per page comes from the operator's dropdown, published by the
        // admin footer as window.P411_ADMIN_PER_PAGE so the client-side pager
        // and the server-side one agree. A table can still pin its own with
        // data-page-size; 0 means "no client paging, show what the server sent".
        var pageSize = parseInt(
            table.dataset.pageSize || window.P411_ADMIN_PER_PAGE || opts.pageSize || '10', 10
        );
        var currentPage = 1;
        var allRows = Array.prototype.slice.call(table.tBodies[0] ? table.tBodies[0].rows : []);
        if (!allRows.length) return;

        // Toolbar above the table: search box, per-page dropdown, counter.
        var toolbar = document.createElement('div');
        toolbar.className = 'admin-table-toolbar';
        toolbar.style.cssText = 'display:flex;gap:12px;align-items:center;margin-bottom:12px;flex-wrap:wrap;';

        var opts_html = (window.P411_ADMIN_PER_PAGE_OPTIONS || [])
            .map(function (o) {
                var sel = (String(o.value) === String(window.P411_ADMIN_PER_PAGE_CHOICE)) ? ' selected' : '';
                return '<option value="' + o.value + '"' + sel + '>' + o.label + '</option>';
            }).join('');

        // data-server-search: this list is searched by the server, so a second
        // "Search this page" box over the rows already sent would be two
        // searches with different reach sitting on top of each other.
        var serverSearch = table.hasAttribute('data-server-search');

        toolbar.innerHTML =
            (serverSearch ? '' :
              '<input type="search" class="form-control admin-table-search" placeholder="Search this page…" style="max-width:280px;">') +
            (opts_html
                ? '<label class="admin-table-size" style="display:flex;align-items:center;gap:6px;margin:0;font-weight:400;color:#6b7280;font-size:0.9rem;">' +
                  // no-select2: the admin footer runs select2 over every <select>
                  // that does not opt out. It replaces the control with its own
                  // widget and fires jQuery events, which do NOT reach a native
                  // addEventListener('change') — so this dropdown rendered with
                  // select2's arrow and did nothing when you picked a size.
                  'Show <select class="form-control admin-table-per-page admin-table-size no-select2" style="width:auto;display:inline-block;padding:2px 8px;height:auto;">' +
                  opts_html + '</select></label>'
                : '') +
            '<span class="admin-table-counter" style="color:#6b7280;font-size:0.9rem;"></span>';

        // Pager below the table: numbered Bootstrap pagination, matches CI styling.
        var pager = document.createElement('nav');
        pager.className = 'admin-table-pager';
        pager.style.cssText = 'margin-top:12px;';
        pager.setAttribute('aria-label', 'Table pagination');
        pager.innerHTML = '<ul class="pagination" style="margin:0;justify-content:flex-end;flex-wrap:wrap;"></ul>';

        // Hide legacy #pg-sear input that sits above this table.
        var legacy = document.getElementById('pg-sear');
        if (legacy && legacy.compareDocumentPosition(table) & Node.DOCUMENT_POSITION_FOLLOWING) {
            legacy.style.display = 'none';
        }
        table.parentNode.insertBefore(toolbar, table);
        if (table.nextSibling) {
            table.parentNode.insertBefore(pager, table.nextSibling);
        } else {
            table.parentNode.appendChild(pager);
        }

        var searchInput = toolbar.querySelector('.admin-table-search');
        var counter     = toolbar.querySelector('.admin-table-counter');
        var pagerList   = pager.querySelector('ul');

        function rowText(row) {
            if (row.dataset.adminTableText !== undefined) return row.dataset.adminTableText;
            var cells = row.cells;
            var parts = [];
            for (var i = 0; i < cells.length; i++) {
                // Skip the last column if it looks like an actions cell.
                if (i === cells.length - 1 && /Edit|Delete|View/.test(cells[i].textContent)) continue;
                parts.push((cells[i].innerText || cells[i].textContent || '').trim());
            }
            var s = parts.join(' ').toLowerCase();
            row.dataset.adminTableText = s;
            return s;
        }

        function pageItem(label, page, opts) {
            opts = opts || {};
            var li = document.createElement('li');
            li.className = 'page-item' + (opts.active ? ' active' : '') + (opts.disabled ? ' disabled' : '');
            var a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.innerHTML = label;
            if (!opts.disabled && !opts.active) {
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    currentPage = page;
                    render();
                });
            } else {
                a.addEventListener('click', function (e) { e.preventDefault(); });
            }
            li.appendChild(a);
            return li;
        }

        function renderPager(totalPages) {
            pagerList.innerHTML = '';
            if (totalPages <= 1) { pager.style.display = 'none'; return; }
            pager.style.display = '';

            pagerList.appendChild(pageItem('&laquo; Prev', currentPage - 1, { disabled: currentPage === 1 }));

            // Show up to ~7 page links with ellipsis around the current page.
            var windowSize = 5;
            var start = Math.max(1, currentPage - Math.floor(windowSize / 2));
            var end   = Math.min(totalPages, start + windowSize - 1);
            start = Math.max(1, end - windowSize + 1);

            if (start > 1) {
                pagerList.appendChild(pageItem('1', 1));
                if (start > 2) pagerList.appendChild(pageItem('&hellip;', 0, { disabled: true }));
            }
            for (var p = start; p <= end; p++) {
                pagerList.appendChild(pageItem(String(p), p, { active: p === currentPage }));
            }
            if (end < totalPages) {
                if (end < totalPages - 1) pagerList.appendChild(pageItem('&hellip;', 0, { disabled: true }));
                pagerList.appendChild(pageItem(String(totalPages), totalPages));
            }

            pagerList.appendChild(pageItem('Next &raquo;', currentPage + 1, { disabled: currentPage === totalPages }));
        }

        function render() {
            var q = (searchInput && searchInput.value || '').trim().toLowerCase();
            var filtered = q ? allRows.filter(function (r) { return rowText(r).indexOf(q) !== -1; }) : allRows;

            var size = pageSize > 0 ? pageSize : filtered.length;
            var pages = Math.max(1, Math.ceil(filtered.length / size));
            if (currentPage > pages) currentPage = pages;

            var start = (currentPage - 1) * size;
            var end = Math.min(filtered.length, start + size);

            for (var i = 0; i < allRows.length; i++) allRows[i].style.display = 'none';
            for (var j = start; j < end; j++) filtered[j].style.display = '';

            if (filtered.length === 0) {
                counter.textContent = 'No matches';
            } else if (filtered.length <= size) {
                counter.textContent = filtered.length + ' row' + (filtered.length === 1 ? '' : 's');
            } else {
                counter.textContent = 'Showing ' + (start + 1) + '–' + end + ' of ' + filtered.length;
            }
            renderPager(pages);
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () { currentPage = 1; render(); });
        }

        var sizeSelect = toolbar.querySelector('.admin-table-per-page');
        if (sizeSelect) {
            function applySize() {
                // Round-trip rather than re-slice locally: most admin lists are
                // paginated by the server, so the rows for page 1 of 50 are not
                // in the DOM to begin with. ?per_page is remembered in the
                // session, so the choice sticks across the admin.
                var url = new URL(window.location.href);
                // ?show=, not ?per_page= — see admin_helper.php.
                url.searchParams.set('show', sizeSelect.value);

                // Back to page one. CodeIgniter's pager puts the page number in
                // the PATH — /admin/all_listings/20 — not the query string, so
                // a trailing numeric segment has to come off too; leaving it
                // would apply the old offset to the new page size.
                url.searchParams.delete('page');
                url.pathname = url.pathname.replace(/\/\d+\/?$/, '');

                window.location.href = url.toString();
            }

            sizeSelect.addEventListener('change', applySize);
            // Belt and braces: if anything ever does wrap this in select2 or
            // chosen, those fire jQuery events only, which the native listener
            // above would never see.
            if (window.jQuery) { window.jQuery(sizeSelect).on('change', applySize); }
        }

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
