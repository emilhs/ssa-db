<?php // Adds a live "filter rows" box above the last results table on the page. Set $filterLabel before including. ?>
<script>
(function () {
    const tables = document.querySelectorAll('table.filterable, table.searchresult');
    const table = tables[tables.length - 1];
    if (!table) return;
    const rows = Array.from(table.querySelectorAll('tr')).filter(r => !r.classList.contains('toprow') && !r.querySelector('th') && r.closest('table') === table);
    if (rows.length < 2) return;
    const norm = s => (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    const text = r => norm(r.textContent + ' ' + Array.from(r.querySelectorAll('input[type=text],input[type=date]')).map(i => i.value).join(' '));
    rows.forEach(r => { r.dataset.key = text(r); });
    const box = document.createElement('div');
    box.className = 'admin-filter';
    box.innerHTML = '<input type = "search" placeholder = "<?php echo htmlspecialchars(isset($filterLabel) ? $filterLabel : 'Filter', ENT_QUOTES); ?>" autocomplete = "off" spellcheck = "false"><span class = "admin-filter-count"></span>';
    table.parentNode.insertBefore(box, table);
    const input = box.querySelector('input'), count = box.querySelector('.admin-filter-count');
    function apply() {
        const tokens = norm(input.value.trim()).split(/\s+/).filter(Boolean);
        let shown = 0, stripe = 0;
        rows.forEach(r => {
            const ok = tokens.every(t => r.dataset.key.includes(t));
            r.style.display = ok ? '' : 'none';
            if (ok) { shown++; r.classList.toggle('odd', stripe++ % 2 === 1); }
        });
        count.textContent = tokens.length ? shown + ' of ' + rows.length : rows.length + ' total';
    }
    input.addEventListener('input', apply);
    apply();
})();
</script>
