<?php
include_once('../config/constants.php');
include_once('../config/functions.php');
include_once('VerifyAdminLogin.php');
include_once('lib.php');

// ---- The delete itself: everything is re-checked on the server before anything is removed ----
if (isset($_POST['lighten'])) {
    $chosen = array_values(array_unique(array_map('intval', (array)($_POST['seasons'] ?? array()))));
    $expRows = (int)($_POST['expectedRows'] ?? -1);
    $expResults = (int)($_POST['expectedResults'] ?? -1);
    $fail = function ($msg) { $_SESSION['flash'] = array('error', 'Nothing was deleted: ' . $msg); header('Location: lighten.php'); exit; };

    if (!csrf_ok($_POST['csrf'] ?? '')) $fail('your session expired, please try again.');
    if (clubs_without_province($conn)) $fail('some clubs still have no province.');
    if (!$chosen) $fail('no seasons were selected.');
    if (trim($_POST['confirm'] ?? '') !== 'DELETE ' . $expRows || empty($_POST['backup'])) $fail('the confirmation did not match.');

    $data = lighten_data($conn);
    $rows = 0; $results = 0;
    foreach ($chosen as $se) {
        if ($se >= $data['latest']) $fail('the most recent season is always kept.');
        if (!isset($data['seasons'][$se])) $fail("season $se has nothing to delete.");
        $rows += $data['seasons'][$se]['rows']; $results += $data['seasons'][$se]['results'];
    }
    if ($rows !== $expRows || $results !== $expResults) $fail("the numbers changed since you reviewed them ($rows season entries and $results results now). Review them again.");

    try {
        $c = delete_seasons($conn, $data['ids'], $chosen);
        $labels = implode(', ', array_map('season_label', $chosen));
        $_SESSION['flash'] = array('ok', "Deleted {$c['rows']} season entries, {$c['results']} results and {$c['points']} points for $labels.");
    } catch (Throwable $e) {
        $_SESSION['flash'] = array('error', 'Delete failed and was rolled back: ' . $e->getMessage());
    }
    header('Location: lighten.php');
    exit;
}
$flash = null;
if (isset($_SESSION['flash'])) { $flash = $_SESSION['flash']; unset($_SESSION['flash']); }

$missing = clubs_without_province($conn);
$data = $missing ? null : lighten_data($conn);
$token = csrf_token();

include('navbar.php');
?>
<link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime(__DIR__ . '/../css/admin.css'); ?>">

<main class = "admin-page admin-wide">
    <h1 class = "admin-h1 bebas-neue">Lighten Database</h1>
    <p class = "admin-hint">Remove out-of-province skaters and their results, one season at a time, to make the database smaller. Skaters who have ever been in Alberta are never touched.</p>

    <?php if ($flash) { ?><p class = "admin-msg <?php echo $flash[0] === 'ok' ? 'ok' : 'err'; ?>"><?php echo htmlspecialchars($flash[1]); ?></p><?php } ?>

    <?php if ($missing) { ?>
    <section class = "edit-card">
        <div class = "edit-card-title bebas-neue">Assign every club a province first</div>
        <p class = "admin-hint">The delete button only appears once every skater's club is marked as Alberta or not Alberta. These clubs are still unassigned:</p>
        <table class = "edit-table">
            <tr><th>Club</th><th>Skaters</th></tr>
            <?php foreach ($missing as $m) { ?><tr><td><?php echo htmlspecialchars($m['club'] ?: '(no club)'); ?></td><td><?php echo (int)$m['skaters']; ?></td></tr><?php } ?>
        </table>
        <p class = "admin-hint" style = "margin-top: 14px;"><a href = "viewclubs.php">Go to Assign Province &rsaquo;</a></p>
    </section>
    <?php } elseif (!$data['seasons']) { ?>
    <section class = "edit-card"><p class = "admin-hint" style = "padding: 24px 0 14px;">Every club has a province, and every skater has been in Alberta. Nothing to remove.</p></section>
    <?php } else { ?>
    <form method = "post" id = "lightenForm" onsubmit = "return confirmDelete(this);">
    <input type = "hidden" name = "csrf" value = "<?php echo $token; ?>">
    <input type = "hidden" name = "expectedRows" id = "expRows" value = "0">
    <input type = "hidden" name = "expectedResults" id = "expResults" value = "0">

    <section class = "edit-card">
        <div class = "edit-card-title bebas-neue">1. Choose seasons to clean out</div>
        <p class = "admin-hint">Counts are for skaters who have never been in Alberta. The most recent season (<?php echo season_label($data['latest']); ?>) is always kept.</p>
        <div class = "edit-scroll">
        <table class = "edit-table" id = "seasonTable">
            <tr><th></th><th>Season</th><th>Skater entries</th><th>Results</th><th>Points</th></tr>
            <?php foreach ($data['seasons'] as $se => $c) { $locked = ($se >= $data['latest']); ?>
            <tr class = "<?php echo $locked ? 'season-locked' : ''; ?>">
                <td><input type = "checkbox" name = "seasons[]" value = "<?php echo $se; ?>" <?php echo $locked ? 'disabled' : ''; ?>></td>
                <td class = "edit-season"><?php echo season_ok($se) ? htmlspecialchars(season_label($se)) : '<span class = "edit-badseason">' . $se . '</span>'; ?><?php echo $locked ? ' <span class = "check-sub">kept</span>' : ''; ?></td>
                <td><?php echo $c['rows']; ?></td><td><?php echo $c['results']; ?></td><td><?php echo $c['points']; ?></td>
            </tr>
            <?php } ?>
        </table>
        </div>
    </section>

    <section class = "edit-card" id = "reviewCard" style = "display:none;">
        <div class = "edit-card-title bebas-neue">2. Review</div>
        <div class = "lighten-stats">
            <div><strong id = "sRows">0</strong><span>skater entries</span></div>
            <div><strong id = "sGone">0</strong><span>skaters removed completely</span></div>
            <div><strong id = "sResults">0</strong><span>results</span></div>
        </div>
        <p class = "admin-hint">Skaters in the list who still have other seasons stay in the database; only the chosen seasons are removed.</p>
        <div class = "check-toolbar" style = "padding: 0 20px;"><input id = "q" type = "search" placeholder = "Filter this list" autocomplete = "off" spellcheck = "false"></div>
        <div class = "edit-scroll" style = "max-height: 340px; overflow-y: auto;">
            <table class = "edit-table" id = "victimTable"><tr><th>Skater</th><th>Last club</th><th>Entries</th><th>Results</th><th>Other seasons kept</th></tr></table>
        </div>
    </section>

    <section class = "edit-card lighten-danger" id = "deleteCard" style = "display:none;">
        <div class = "edit-card-title bebas-neue">3. Delete</div>
        <p class = "admin-hint"><strong>This can't be undone.</strong> Download a backup first.</p>
        <div class = "lighten-form">
            <a class = "pair-btn" href = "export.php">Download results backup (CSV)</a>
            <label class = "lighten-check"><input type = "checkbox" name = "backup" value = "1"> I have downloaded a backup</label>
            <label><span>Type <strong id = "confirmWord">DELETE 0</strong> to confirm</span>
                <input name = "confirm" id = "confirmBox" autocomplete = "off">
            </label>
            <button class = "pair-btn danger" type = "submit" name = "lighten" value = "1" id = "goBtn">Delete</button>
        </div>
    </section>
    </form>

    <script>
    const SK = <?php echo json_encode($data['skaters'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
    const LABEL = s => String(s - 1).slice(-2).padStart(2, '0') + '-' + String(s).slice(-2).padStart(2, '0');
    const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const norm = s => (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    let rowsHtml = [];

    function chosen() { return [...document.querySelectorAll('input[name="seasons[]"]:checked')].map(c => c.value); }

    function refresh() {
        const sel = chosen();
        document.getElementById('reviewCard').style.display = sel.length ? '' : 'none';
        document.getElementById('deleteCard').style.display = sel.length ? '' : 'none';
        if (!sel.length) return;
        let rows = 0, results = 0, gone = 0;
        rowsHtml = [];
        Object.keys(SK).forEach(id => {
            const s = SK[id], seasons = s.s || {};
            let r = 0, res = 0, kept = 0;
            Object.keys(seasons).forEach(se => {
                if (sel.includes(se)) { r += seasons[se].rows; res += seasons[se].results; }
                else if (seasons[se].rows) kept++;
            });
            if (!r && !res) return;
            rows += r; results += res;
            if (kept === 0) gone++;
            rowsHtml.push('<tr data-key = "' + esc(norm(s.name + ' ' + s.club)) + '"><td><a href = "editskater.php?id=' + id + '" target = "_blank">' + esc(s.name) + '</a> <span class = "check-sub">#' + id + '</span></td><td>' + esc(s.club) + '</td><td>' + r + '</td><td>' + res + '</td><td>' + (kept || '—') + '</td></tr>');
        });
        document.getElementById('sRows').textContent = rows;
        document.getElementById('sGone').textContent = gone;
        document.getElementById('sResults').textContent = results;
        document.getElementById('expRows').value = rows;
        document.getElementById('expResults').value = results;
        document.getElementById('confirmWord').textContent = 'DELETE ' + rows;
        document.getElementById('goBtn').textContent = 'Delete ' + rows + ' skater entries and ' + results + ' results (' + sel.map(s => LABEL(+s)).join(', ') + ')';
        filter();
    }
    function filter() {
        const t = norm(document.getElementById('q').value).split(/\s+/).filter(Boolean);
        const table = document.getElementById('victimTable');
        table.querySelectorAll('tr[data-key]').forEach(r => r.remove());
        table.insertAdjacentHTML('beforeend', rowsHtml.filter(h => t.every(w => h.includes(w))).join(''));
    }
    function confirmDelete(f) {
        const rows = document.getElementById('expRows').value, res = document.getElementById('expResults').value;
        return confirm('Permanently delete ' + rows + ' skater entries and ' + res + ' results? This cannot be undone.');
    }
    document.getElementById('seasonTable').addEventListener('change', refresh);
    document.getElementById('q').addEventListener('input', filter);
    </script>
    <?php } ?>
</main>

<?php include('../footer.php'); ?>
