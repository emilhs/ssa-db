<?php
include_once('../config/constants.php');
include_once('../config/functions.php');
include_once('VerifyAdminLogin.php');
include_once('lib.php');

// Alberta skaters = anyone with a season at an Alberta club
$albertaSkaters = "SELECT DISTINCT s3.skaterID FROM skaters s3 JOIN club c3 ON c3.clubName = s3.club WHERE c3.alberta = TRUE";

// ---- Save one field (called by the page as the admin types / clicks) ----
if (isset($_POST['action']) && $_POST['action'] === 'save') {
    header('Content-Type: application/json');
    if (!csrf_ok($_POST['csrf'] ?? '')) { http_response_code(403); echo json_encode(array('ok' => false, 'error' => 'Session expired, reload the page.')); exit; }
    $id = (int)($_POST['skaterID'] ?? 0);
    $field = $_POST['field'] ?? '';
    $value = trim($_POST['value'] ?? '');
    if ($id <= 0) { echo json_encode(array('ok' => false, 'error' => 'Unknown skater.')); exit; }

    if ($field === 'gender') {
        if ($value !== 'M' && $value !== 'F') { echo json_encode(array('ok' => false, 'error' => 'Gender must be M or F.')); exit; }
        $stmt = mysqli_prepare($conn, "UPDATE skaters SET gender = ? WHERE skaterID = ?");
        mysqli_stmt_bind_param($stmt, 'si', $value, $id);
        mysqli_stmt_execute($stmt);
    } elseif ($field === 'dob') {
        $d = DateTime::createFromFormat('Y-m-d', $value);
        if (!$d || $d->format('Y-m-d') !== $value) { echo json_encode(array('ok' => false, 'error' => 'Enter a valid date.')); exit; }
        $year = (int)$d->format('Y');
        if ($year < 1940 || $d > new DateTime('today')) { echo json_encode(array('ok' => false, 'error' => 'That birthdate looks wrong.')); exit; }
        apply_dob($conn, $id, $value);
    } else {
        echo json_encode(array('ok' => false, 'error' => 'Unknown field.')); exit;
    }
    echo json_encode(array('ok' => true));
    exit;
}

// ---- Fix season problems (called by the page; each sets a message and the page reloads) ----
if (isset($_POST['action']) && in_array($_POST['action'], array('row_delete', 'row_season', 'comp_season'), true)) {
    header('Content-Type: application/json');
    $fail = function ($m) { echo json_encode(array('ok' => false, 'error' => $m)); exit; };
    if (!csrf_ok($_POST['csrf'] ?? '')) { http_response_code(403); $fail('Session expired, reload the page.'); }
    $act = $_POST['action'];
    $msg = '';

    if ($act === 'comp_season') {
        $compID = (int)($_POST['compID'] ?? 0);
        $new = (int)($_POST['newSeason'] ?? 0);
        if (!season_ok($new)) $fail('Enter a season between 2000 and ' . (current_season() + 1) . ' (2025 means 2024-25).');
        $c = mysqli_fetch_assoc(mysqli_query($conn, "SELECT season FROM comps WHERE compID = $compID"));
        if (!$c) $fail('Competition not found.');
        $old = (int)$c['season'];
        mysqli_begin_transaction($conn);
        try {
            $st = mysqli_prepare($conn, "UPDATE comps SET season = ? WHERE compID = ?");
            mysqli_stmt_bind_param($st, 'ii', $new, $compID);
            mysqli_stmt_execute($st);
            // Move the season rows of the skaters who raced in this competition
            $ids = mysqli_query($conn, "SELECT DISTINCT skaterID FROM results WHERE compID = $compID");
            $moved = 0;
            while ($i = mysqli_fetch_assoc($ids)) {
                $sid = (int)$i['skaterID'];
                $has = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM skaters WHERE skaterID = $sid AND season = $new"));
                if ((int)$has['c'] > 0) {
                    mysqli_query($conn, "DELETE FROM skaters WHERE skaterID = $sid AND season = $old");
                } else {
                    mysqli_query($conn, "UPDATE skaters SET season = $new WHERE skaterID = $sid AND season = $old");
                    $moved++;
                }
            }
            mysqli_commit($conn);
        } catch (Throwable $e) { mysqli_rollback($conn); $fail('Could not update: ' . $e->getMessage()); }
        $msg = "Competition #$compID moved from season $old to $new.";
    } else {
        $sid = (int)($_POST['skaterID'] ?? 0);
        $season = (int)($_POST['season'] ?? 0);
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM skaters WHERE skaterID = $sid AND season = $season"));
        if ((int)$row['c'] === 0) $fail('That season entry no longer exists.');
        $others = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM skaters WHERE skaterID = $sid AND season <> $season"));
        $rc = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM results r JOIN comps c ON c.compID = r.compID WHERE r.skaterID = $sid AND c.season = $season"));
        if ((int)$rc['c'] > 0) $fail('This season has results. If the competition has the wrong season, fix the competition instead.');
        if ($act === 'row_delete') {
            if ((int)$others['c'] === 0) $fail("This is the skater's only season, so it can't be deleted.");
            mysqli_query($conn, "DELETE FROM skaters WHERE skaterID = $sid AND season = $season");
            $msg = "Deleted season $season for skater #$sid.";
        } else {
            $new = (int)($_POST['newSeason'] ?? 0);
            if (!season_ok($new)) $fail('Enter a season between 2000 and ' . (current_season() + 1) . ' (2025 means 2024-25).');
            $exists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM skaters WHERE skaterID = $sid AND season = $new"));
            if ((int)$exists['c'] > 0) {
                mysqli_query($conn, "DELETE FROM skaters WHERE skaterID = $sid AND season = $season");
                $msg = "Season $season removed (skater #$sid already had $new).";
            } else {
                mysqli_query($conn, "UPDATE skaters SET season = $new WHERE skaterID = $sid AND season = $season");
                $msg = "Season $season changed to $new for skater #$sid.";
            }
        }
    }
    $_SESSION['flash'] = array('ok', $msg);
    echo json_encode(array('ok' => true));
    exit;
}

// ---- Auto-fill genders that are known from another season of the same skater ----
if (isset($_POST['autofill'])) {
    if (csrf_ok($_POST['csrf'] ?? '')) {
        mysqli_query($conn, "UPDATE skaters s
            JOIN (SELECT skaterID, MAX(gender) AS g FROM skaters WHERE gender IN ('M','F') GROUP BY skaterID HAVING COUNT(DISTINCT gender) = 1) k ON k.skaterID = s.skaterID
            JOIN ($albertaSkaters) a ON a.skaterID = s.skaterID
            SET s.gender = k.g WHERE s.gender NOT IN ('M','F')");
        $n = mysqli_affected_rows($conn);
        $_SESSION['flash'] = array('ok', $n > 0 ? "Filled in gender for $n season entr" . ($n == 1 ? 'y' : 'ies') . " from other seasons." : 'Nothing to fill in automatically.');
    } else {
        $_SESSION['flash'] = array('error', 'Session expired, please try again.');
    }
    header('Location: checks.php');
    exit;
}
$flash = null;
if (isset($_SESSION['flash'])) { $flash = $_SESSION['flash']; unset($_SESSION['flash']); }

// ---- Skaters that still need a gender or birthdate ----
$sql = "SELECT s.skaterID, l.fName, l.lName, l.club, l.season AS lastSeason,
               MAX(s.dob) AS dob,
               MAX(CASE WHEN s.gender IN ('M','F') THEN s.gender END) AS gender
        FROM skaters s
        JOIN ($albertaSkaters) a ON a.skaterID = s.skaterID
        JOIN (SELECT s2.skaterID, s2.fName, s2.lName, s2.club, s2.season
              FROM skaters s2
              JOIN (SELECT skaterID, MAX(season) AS ms FROM skaters GROUP BY skaterID) m ON m.skaterID = s2.skaterID AND m.ms = s2.season) l ON l.skaterID = s.skaterID
        GROUP BY s.skaterID, l.fName, l.lName, l.club, l.season
        HAVING dob IS NULL OR gender IS NULL
        ORDER BY l.lName, l.fName";
$res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
$rows = array();
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = array('id' => (int)$r['skaterID'], 'f' => $r['fName'], 'l' => $r['lName'], 'c' => $r['club'],
                    's' => season_label((int)$r['lastSeason']), 'g' => $r['gender'], 'd' => $r['dob']);
}

// How many gender entries could be filled in automatically?
$ar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM skaters s
    JOIN (SELECT skaterID FROM skaters WHERE gender IN ('M','F') GROUP BY skaterID HAVING COUNT(DISTINCT gender) = 1) k ON k.skaterID = s.skaterID
    JOIN ($albertaSkaters) a ON a.skaterID = s.skaterID
    WHERE s.gender NOT IN ('M','F')"));
$autofillable = (int)$ar['c'];
$attention = attention_skaters($conn);
$badComps = bad_comps($conn);
$token = csrf_token();

include('navbar.php');
?>
<link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime(__DIR__ . '/../css/admin.css'); ?>">

<main class = "admin-page">
    <h1 class = "admin-h1 bebas-neue">Skater Data Checks</h1>
    <p class = "admin-hint">Skaters worth a second look: impossible seasons, big gaps between seasons, club changes, and conflicting genders or birthdates.</p>

    <?php if ($flash) { ?><p class = "admin-msg <?php echo $flash[0] === 'ok' ? 'ok' : 'err'; ?>"><?php echo htmlspecialchars($flash[1]); ?></p><?php } ?>

    <div class = "check-toolbar">
        <div class = "check-tabs">
            <button type = "button" class = "active" data-tab = "attention">Needs attention <span id = "nAttention"><?php echo count($attention) + count($badComps); ?></span></button>
            <button type = "button" data-tab = "gender">Missing gender <span id = "nGender"></span></button>
            <button type = "button" data-tab = "dob">Missing birthdate <span id = "nDob"></span></button>
        </div>
        <input id = "q" type = "search" placeholder = "Filter by name or club" autocomplete = "off" spellcheck = "false">
    </div>

    <div id = "attentionPanel">
        <?php if ($badComps) { ?>
        <div class = "att-card att-comps">
            <div class = "att-head"><strong>Competitions with an impossible season</strong><span class = "pair-tag pair-warn">Fix these first</span></div>
            <p class = "admin-hint" style = "text-align:left;">Moving a competition to the right season also moves its skaters' season entries.</p>
            <?php foreach ($badComps as $c) { ?>
            <div class = "att-comp" data-comp = "<?php echo (int)$c['compID']; ?>">
                <div><strong><?php echo htmlspecialchars($c['compName']); ?></strong>
                    <span class = "check-sub">season <?php echo (int)$c['season']; ?><?php echo $c['firstDate'] ? ' &middot; first date ' . htmlspecialchars($c['firstDate']) : ''; ?> &middot; <?php echo (int)$c['results']; ?> results</span></div>
                <div class = "att-fix">
                    <input type = "number" class = "att-season" min = "2000" max = "<?php echo current_season() + 1; ?>" placeholder = "e.g. <?php echo current_season(); ?>">
                    <button type = "button" class = "pair-btn primary" onclick = "fixComp(<?php echo (int)$c['compID']; ?>, this)">Set season</button>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <?php foreach ($attention as $a) { ?>
        <div class = "att-card" data-id = "<?php echo $a['id']; ?>" data-sig = "<?php echo htmlspecialchars($a['sig']); ?>" data-key = "<?php echo htmlspecialchars(norm_name($a['fName'] . ' ' . $a['lName'])); ?>">
            <div class = "att-head">
                <a href = "editskater.php?id=<?php echo $a['id']; ?>" target = "_blank"><strong><?php echo htmlspecialchars($a['fName']); ?> <?php echo htmlspecialchars($a['lName']); ?></strong></a>
                <span class = "check-sub">#<?php echo $a['id']; ?></span>
                <?php foreach ($a['reasons'] as $k => $text) { ?><span class = "pair-tag <?php echo $k === 'club' ? '' : 'pair-warn'; ?>"><?php echo htmlspecialchars($text); ?></span><?php } ?>
                <button type = "button" class = "pair-btn dismiss" onclick = "dismissSkater(this)">Looks fine</button>
            </div>
            <table class = "att-table">
                <tr><th>Season</th><th>Club</th><th>Gender</th><th>Birthdate</th><th>Results</th><th></th></tr>
                <?php foreach ($a['rows'] as $i => $r) { $bad = !season_ok($r['season']); ?>
                <tr class = "<?php echo $bad ? 'att-bad' : ''; ?>">
                    <td><?php echo $bad ? (int)$r['season'] : htmlspecialchars(season_label($r['season'])); ?></td>
                    <td><?php echo htmlspecialchars($r['club']); ?></td>
                    <td><?php echo htmlspecialchars($r['gender'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($r['dob'] ?: '—'); ?></td>
                    <td><?php echo $r['res']; ?></td>
                    <td class = "att-fix">
                        <?php if ($r['res'] == 0 && ($bad || true)) { ?>
                            <input type = "number" class = "att-season" min = "2000" max = "<?php echo current_season() + 1; ?>" placeholder = "season">
                            <button type = "button" class = "pair-btn" onclick = "fixRow(<?php echo $a['id']; ?>, <?php echo (int)$r['season']; ?>, 'row_season', this)">Change</button>
                            <?php if (count($a['rows']) > 1) { ?><button type = "button" class = "pair-btn danger" onclick = "fixRow(<?php echo $a['id']; ?>, <?php echo (int)$r['season']; ?>, 'row_delete', this)">Delete</button><?php } ?>
                        <?php } else { ?><span class = "check-sub">has results</span><?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
        <?php } ?>
        <p id = "attNone" class = "admin-hint" style = "display:none; padding: 24px;">Nothing needs attention &#127881;</p>
        <p id = "dismissedNote" class = "admin-hint" style = "display:none;"><span id = "dismissedCount"></span> skater(s) marked &ldquo;looks fine&rdquo;. <a href = "#" onclick = "showDismissed(); return false;">Show again</a></p>
    </div>

    <div id = "missingPanel" style = "display:none;">
    <?php if ($autofillable > 0) { ?>
    <form method = "post" class = "check-auto">
        <input type = "hidden" name = "csrf" value = "<?php echo $token; ?>">
        <span><strong><?php echo $autofillable; ?></strong> gender entr<?php echo $autofillable == 1 ? 'y is' : 'ies are'; ?> missing but known from another season.</span>
        <button class = "pair-btn primary" type = "submit" name = "autofill" value = "1">Fill in automatically</button>
    </form>
    <?php } ?>
    <div class = "check-card">
        <p id = "allDone" class = "admin-hint" style = "display:none; text-align:center; padding: 24px;">All done &mdash; every Alberta skater has a gender and birthdate.</p>
        <table id = "checkTable" class = "check-table">
            <tr class = "head"><th>Skater</th><th>Club</th><th>Gender</th><th>Birthdate</th><th></th></tr>
        </table>
    </div>
    </div>
</main>

<script>
const ROWS = <?php echo json_encode($rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
const CSRF = <?php echo json_encode($token); ?>;
const TODAY = new Date().toISOString().slice(0, 10);
const norm = s => (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let tab = 'attention';
const table = document.getElementById('checkTable');

function rowHtml(r) {
    return '<tr data-id = "' + r.id + '" data-key = "' + esc(norm(r.f + ' ' + r.l + ' ' + r.c)) + '">' +
        '<td><a href = "editskater.php?id=' + r.id + '" target = "_blank">' + esc(r.f) + ' <strong>' + esc(r.l) + '</strong></a><span class = "check-sub">#' + r.id + ' &middot; ' + esc(r.s) + '</span></td>' +
        '<td>' + esc(r.c) + '</td>' +
        '<td class = "check-gender"><button type = "button" data-g = "M" class = "' + (r.g === 'M' ? 'sel' : '') + '">M</button><button type = "button" data-g = "F" class = "' + (r.g === 'F' ? 'sel' : '') + '">F</button></td>' +
        '<td><input type = "date" min = "1940-01-01" max = "' + TODAY + '" value = "' + (r.d || '') + '"></td>' +
        '<td class = "check-status"></td></tr>';
}
table.insertAdjacentHTML('beforeend', ROWS.map(rowHtml).join(''));

function missingG(r) { return !r.g; }
function missingD(r) { return !r.d; }
function visible(r) {
    const q = norm(document.getElementById('q').value.trim()).split(/\s+/).filter(Boolean);
    const key = norm(r.f + ' ' + r.l + ' ' + r.c);
    if (!q.every(t => key.includes(t))) return false;
    return (tab === 'gender' && missingG(r)) || (tab === 'dob' && missingD(r));
}
const DKEY = 'check-dismissed-skaters';
const loadD = () => { try { return new Set(JSON.parse(localStorage.getItem(DKEY) || '[]')); } catch (e) { return new Set(); } };
const saveD = d => { try { localStorage.setItem(DKEY, JSON.stringify([...d])); } catch (e) {} };

function renderAttention() {
    const q = norm(document.getElementById('q').value.trim()).split(/\s+/).filter(Boolean);
    const dismissed = loadD();
    let shown = 0, hidden = 0;
    document.querySelectorAll('.att-card[data-id]').forEach(c => {
        const d = dismissed.has(c.dataset.id + '|' + c.dataset.sig);
        if (d) hidden++;
        const ok = !d && q.every(t => c.dataset.key.includes(t));
        c.style.display = ok ? '' : 'none';
        if (ok) shown++;
    });
    const comps = document.querySelectorAll('.att-comps').length ? document.querySelectorAll('.att-comp').length : 0;
    document.getElementById('nAttention').textContent = (document.querySelectorAll('.att-card[data-id]').length - hidden) + comps;
    document.getElementById('attNone').style.display = (shown === 0 && comps === 0 && !q.length) ? '' : 'none';
    document.getElementById('dismissedCount').textContent = hidden;
    document.getElementById('dismissedNote').style.display = hidden ? '' : 'none';
}
function dismissSkater(btn) {
    const c = btn.closest('.att-card'), d = loadD();
    d.add(c.dataset.id + '|' + c.dataset.sig); saveD(d); renderAttention();
}
function showDismissed() { saveD(new Set()); renderAttention(); }

async function post(params) {
    params.csrf = CSRF;
    const res = await fetch('checks.php', { method: 'POST', body: new URLSearchParams(params) });
    const data = await res.json();
    if (!data.ok) throw new Error(data.error || 'Could not save');
}
async function fixRow(skaterID, season, action, btn) {
    const cell = btn.closest('.att-fix');
    const input = cell.querySelector('.att-season');
    if (action === 'row_season' && !input.value) { alert('Enter the correct season first, for example 2025 for 2024-25.'); return; }
    const text = action === 'row_delete' ? 'Delete season ' + season + ' for this skater? This can\'t be undone.' : 'Change season ' + season + ' to ' + input.value + '?';
    if (!confirm(text)) return;
    try { await post({ action, skaterID, season, newSeason: input ? input.value : '' }); location.reload(); }
    catch (e) { alert(e.message); }
}
async function fixComp(compID, btn) {
    const input = btn.closest('.att-comp').querySelector('.att-season');
    if (!input.value) { alert('Enter the correct season first, for example 2025 for 2024-25.'); return; }
    if (!confirm('Move this competition (and its skaters\' season entries) to season ' + input.value + '?')) return;
    try { await post({ action: 'comp_season', compID, newSeason: input.value }); location.reload(); }
    catch (e) { alert(e.message); }
}

function render() {
    const att = tab === 'attention';
    document.getElementById('attentionPanel').style.display = att ? '' : 'none';
    document.getElementById('missingPanel').style.display = att ? 'none' : '';
    document.getElementById('nGender').textContent = ROWS.filter(missingG).length;
    document.getElementById('nDob').textContent = ROWS.filter(missingD).length;
    if (att) { renderAttention(); return; }
    const open = ROWS.filter(r => missingG(r) || missingD(r));
    ROWS.forEach(r => {
        const tr = table.querySelector('tr[data-id="' + r.id + '"]');
        if (!tr) return;
        tr.style.display = ((missingG(r) || missingD(r) || tr.classList.contains('saved')) && visible(r)) ? '' : 'none';
    });
    document.getElementById('allDone').style.display = open.length === 0 ? '' : 'none';
    table.style.display = open.length === 0 ? 'none' : '';
}

async function save(tr, r, field, value) {
    const st = tr.querySelector('.check-status');
    st.textContent = 'Saving…'; st.className = 'check-status';
    const body = new URLSearchParams({ action: 'save', csrf: CSRF, skaterID: r.id, field, value });
    try {
        const res = await fetch('checks.php', { method: 'POST', body });
        const data = await res.json();
        if (!data.ok) throw new Error(data.error || 'Could not save');
        if (field === 'gender') r.g = value; else r.d = value;
        tr.querySelectorAll('.check-gender button').forEach(b => b.classList.toggle('sel', b.dataset.g === r.g));
        st.textContent = 'Saved ✓'; st.className = 'check-status ok';
        if (!missingG(r) && !missingD(r)) {
            tr.classList.add('saved');
            setTimeout(() => { tr.classList.remove('saved'); render(); }, 900);
        }
        render();
    } catch (e) {
        st.textContent = e.message; st.className = 'check-status err';
    }
}

table.addEventListener('click', e => {
    const b = e.target.closest('.check-gender button');
    if (!b) return;
    const tr = b.closest('tr'), r = ROWS.find(x => x.id == tr.dataset.id);
    if (r.g === b.dataset.g) return;
    save(tr, r, 'gender', b.dataset.g);
});
table.addEventListener('change', e => {
    if (e.target.type !== 'date') return;
    const tr = e.target.closest('tr'), r = ROWS.find(x => x.id == tr.dataset.id);
    if (!e.target.value) return;
    save(tr, r, 'dob', e.target.value);
});
document.querySelector('.check-tabs').addEventListener('click', e => {
    const b = e.target.closest('button'); if (!b) return;
    tab = b.dataset.tab;
    document.querySelectorAll('.check-tabs button').forEach(x => x.classList.toggle('active', x === b));
    render();
});
document.getElementById('q').addEventListener('input', render);
render();
</script>

<?php include('../footer.php'); ?>
