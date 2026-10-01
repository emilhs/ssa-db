<?php
include_once('../config/constants.php');
include_once('../config/functions.php');
include_once('VerifyAdminLogin.php');
include_once('lib.php');

if (isset($_POST['merge'])) {
    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $_SESSION['flash'] = array('error', 'Session expired, please try again.');
    } else {
        list($ok, $msg) = merge_skaters($conn, (int)($_POST['keep'] ?? 0), (int)($_POST['remove'] ?? 0));
        $_SESSION['flash'] = array($ok ? 'ok' : 'error', $msg);
    }
    header('Location: merge.php');
    exit;
}
$flash = null;
if (isset($_SESSION['flash'])) { $flash = $_SESSION['flash']; unset($_SESSION['flash']); }

// ---- Find duplicate pairs ----
$sql = "SELECT s.skaterID, l.fName, l.lName, l.gender, l.club,
               GROUP_CONCAT(DISTINCT s.season ORDER BY s.season) AS seasons,
               MAX(s.dob) AS dob,
               MAX(c.alberta) AS alberta,
               (SELECT COUNT(*) FROM results r WHERE r.skaterID = s.skaterID) AS results
        FROM skaters s
        JOIN (SELECT s2.skaterID, s2.fName, s2.lName, s2.gender, s2.club
              FROM skaters s2
              JOIN (SELECT skaterID, MAX(season) AS ms FROM skaters GROUP BY skaterID) m ON m.skaterID = s2.skaterID AND m.ms = s2.season) l ON l.skaterID = s.skaterID
        LEFT JOIN club c ON c.clubName = s.club
        GROUP BY s.skaterID, l.fName, l.lName, l.gender, l.club";
$res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
$skaters = array();
while ($r = mysqli_fetch_assoc($res)) {
    $skaters[] = array(
        'id' => (int)$r['skaterID'], 'fname' => $r['fName'], 'lname' => $r['lName'],
        'gender' => known_gender($r['gender']), 'club' => $r['club'],
        'seasons' => array_map('intval', array_filter(explode(',', (string)$r['seasons']))),
        'dob' => $r['dob'], 'results' => (int)$r['results'],
        'alberta' => (bool)$r['alberta'],
        'first' => norm_name($r['fName']), 'last' => norm_name($r['lName'])
    );
}

$pairs = array();
$addPair = function ($a, $b, $reason, $hint = null) use (&$pairs) {
    if (!$a['alberta'] && !$b['alberta']) return;           // only duplicates that involve an Alberta skater
    if ($a['id'] > $b['id']) { $t = $a; $a = $b; $b = $t; }
    $key = $a['id'] . '|' . $b['id'];
    if (isset($pairs[$key])) return;
    $warning = $hint;
    if ($a['dob'] && $b['dob'] && $a['dob'] !== $b['dob']) $warning = 'Different birthdates';
    elseif ($a['gender'] && $b['gender'] && $a['gender'] !== $b['gender']) $warning = 'Different genders';
    $pairs[$key] = array('a' => $a, 'b' => $b, 'reason' => $reason, 'warning' => $warning, 'key' => $key);
};

$byName = array(); $byDob = array();
foreach ($skaters as $s) {
    $byName[$s['first'] . '|' . $s['last']][] = $s;
    if ($s['dob']) $byDob[$s['dob']][] = $s;
}
foreach ($byName as $group) {
    for ($i = 0; $i < count($group); $i++) for ($j = $i + 1; $j < count($group); $j++) $addPair($group[$i], $group[$j], 'Same name');
}
foreach ($byDob as $group) {
    for ($i = 0; $i < count($group); $i++) for ($j = $i + 1; $j < count($group); $j++) {
        $a = $group[$i]; $b = $group[$j];
        if ($a['last'] === $b['last']) $addPair($a, $b, 'Same birthdate and last name', 'Could be siblings or twins');
        elseif ($a['first'] === $b['first']) $addPair($a, $b, 'Same birthdate and first name');
    }
}

// Suggest keeping the record with a birthdate, then a gender, then accented spelling, then more results
function suggested_keeper($p) {
    $checks = array(
        function ($s) { return (int)!!$s['dob']; },
        function ($s) { return (int)!!$s['gender']; },
        function ($s) { return (int)(has_accents($s['fname'] . $s['lname'])); },
        function ($s) { return $s['results']; },
    );
    foreach ($checks as $c) {
        if ($c($p['a']) !== $c($p['b'])) return $c($p['a']) > $c($p['b']) ? $p['a']['id'] : $p['b']['id'];
    }
    return $p['a']['id'];
}

uasort($pairs, function ($x, $y) {
    return ((int)!!$x['warning'] - (int)!!$y['warning']) ?: strcmp($x['a']['lname'], $y['a']['lname']);
});
$likely = array_filter($pairs, function ($p) { return !$p['warning']; });
$careful = array_filter($pairs, function ($p) { return (bool)$p['warning']; });
$token = csrf_token();

include('navbar.php');

function pair_card($p, $token) {
    $keeper = suggested_keeper($p);
    $rows = array(
        'Name'      => function ($s) { return $s['fname'] . ' ' . $s['lname']; },
        'Club'      => function ($s) { return $s['club'] ?: '—'; },
        'Seasons'   => function ($s) { return implode(', ', array_map('season_label', $s['seasons'])) ?: '—'; },
        'Birthdate' => function ($s) { return $s['dob'] ?: '—'; },
        'Gender'    => function ($s) { return $s['gender'] ?: '—'; },
        'Results'   => function ($s) { return $s['results']; },
    );
    ?>
    <div class = "pair-card" data-key = "<?php echo $p['key']; ?>">
        <div class = "pair-head">
            <strong><?php echo htmlspecialchars($p['a']['fname'] . ' ' . $p['a']['lname']); ?></strong>
            <span class = "pair-tag"><?php echo htmlspecialchars($p['reason']); ?></span>
            <?php if ($p['warning']) { ?><span class = "pair-tag pair-warn">&#9888; <?php echo htmlspecialchars($p['warning']); ?></span><?php } ?>
        </div>
        <table class = "pair-table">
            <tr><th></th>
                <?php foreach (array($p['a'], $p['b']) as $s) { ?>
                    <th><a href = "../athlete.php?id=<?php echo $s['id']; ?>" target = "_blank">#<?php echo $s['id']; ?> &#8599;</a></th>
                <?php } ?>
            </tr>
            <?php foreach ($rows as $label => $fn) { $differs = (string)$fn($p['a']) !== (string)$fn($p['b']); ?>
            <tr>
                <td class = "pair-label"><?php echo $label; ?></td>
                <?php foreach (array($p['a'], $p['b']) as $s) { ?>
                    <td class = "<?php echo $differs ? 'pair-diff' : ''; ?>"><?php echo htmlspecialchars((string)$fn($s)); ?></td>
                <?php } ?>
            </tr>
            <?php } ?>
        </table>
        <div class = "pair-actions">
            <?php foreach (array(array($p['a'], $p['b']), array($p['b'], $p['a'])) as $pair) { list($keep, $remove) = $pair; ?>
            <?php $msg = "Keep {$keep['fname']} {$keep['lname']} (#{$keep['id']}) and merge #{$remove['id']} into it?\n#{$remove['id']}'s {$remove['results']} results will move to #{$keep['id']}, and #{$remove['id']} will be deleted.\n\nThis can't be undone."; ?>
            <form method = "post" data-msg = "<?php echo htmlspecialchars($msg, ENT_QUOTES); ?>" onsubmit = "return confirm(this.dataset.msg);">
                <input type = "hidden" name = "csrf" value = "<?php echo $token; ?>">
                <input type = "hidden" name = "keep" value = "<?php echo $keep['id']; ?>">
                <input type = "hidden" name = "remove" value = "<?php echo $remove['id']; ?>">
                <button class = "pair-btn <?php echo $keep['id'] === $keeper ? 'primary' : ''; ?>" type = "submit" name = "merge" value = "1">Keep #<?php echo $keep['id']; ?><?php echo $keep['id'] === $keeper ? ' (suggested)' : ''; ?></button>
            </form>
            <?php } ?>
            <button class = "pair-btn dismiss" type = "button" onclick = "dismissPair('<?php echo $p['key']; ?>')">Not a duplicate</button>
        </div>
    </div>
    <?php
}
?>
<link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime(__DIR__ . '/../css/admin.css'); ?>">

<main class = "admin-page">
    <h1 class = "admin-h1 bebas-neue">Merge Duplicate Skaters</h1>
    <p class = "admin-hint">Pick which record to keep. The other record's results, points and seasons move over (and its birthdate, if the kept one has none), then it's deleted. Names are matched ignoring accents, so &ldquo;FÉLIX&rdquo; and &ldquo;FELIX&rdquo; count as the same.</p>

    <?php if ($flash) { ?><p class = "admin-msg <?php echo $flash[0] === 'ok' ? 'ok' : 'err'; ?>"><?php echo htmlspecialchars($flash[1]); ?></p><?php } ?>

    <div class = "admin-section-title bebas-neue">Likely duplicates (<span id = "likelyCount"><?php echo count($likely); ?></span>)</div>
    <div id = "likely"><?php foreach ($likely as $p) pair_card($p, $token); ?></div>
    <p id = "likelyNone" class = "admin-hint" style = "display:none;">None</p>

    <div class = "admin-section-title bebas-neue">Check carefully (<span id = "carefulCount"><?php echo count($careful); ?></span>)</div>
    <p class = "admin-hint">These might be different people, such as siblings. Only merge if you're sure.</p>
    <div id = "careful"><?php foreach ($careful as $p) pair_card($p, $token); ?></div>
    <p id = "carefulNone" class = "admin-hint" style = "display:none;">None</p>

    <p id = "dismissedNote" class = "admin-hint" style = "display:none;"><span id = "dismissedCount"></span> pair(s) marked &ldquo;not a duplicate&rdquo;. <a href = "#" onclick = "showDismissed(); return false;">Show again</a></p>

    <div class = "admin-section-title bebas-neue">Merge by ID</div>
    <p class = "admin-hint">For duplicates the list doesn't catch. Skater IDs are shown on the Edit Skaters page.</p>
    <form method = "post" class = "merge-id" onsubmit = "return confirm('Merge skater #' + this.remove.value + ' into #' + this.keep.value + '? All of #' + this.remove.value + ' results will move to #' + this.keep.value + ' and #' + this.remove.value + ' will be deleted. This cannot be undone.');">
        <input type = "hidden" name = "csrf" value = "<?php echo $token; ?>">
        <label>Keep ID <input name = "keep" inputmode = "numeric" pattern = "[0-9]+" required></label>
        <label>Merge &amp; delete ID <input name = "remove" inputmode = "numeric" pattern = "[0-9]+" required></label>
        <button class = "pair-btn primary" type = "submit" name = "merge" value = "1">Merge</button>
    </form>
</main>

<script>
const DKEY = 'merge-dismissed-pairs';
const load = () => { try { return new Set(JSON.parse(localStorage.getItem(DKEY) || '[]')); } catch (e) { return new Set(); } };
const save = s => { try { localStorage.setItem(DKEY, JSON.stringify([...s])); } catch (e) {} };
function refresh() {
    const dismissed = load();
    let hidden = 0;
    document.querySelectorAll('.pair-card').forEach(c => { const d = dismissed.has(c.dataset.key); c.style.display = d ? 'none' : ''; if (d) hidden++; });
    ['likely', 'careful'].forEach(id => {
        const n = [...document.querySelectorAll('#' + id + ' .pair-card')].filter(c => c.style.display !== 'none').length;
        document.getElementById(id + 'Count').textContent = n;
        document.getElementById(id + 'None').style.display = n ? 'none' : '';
    });
    document.getElementById('dismissedCount').textContent = hidden;
    document.getElementById('dismissedNote').style.display = hidden ? '' : 'none';
}
function dismissPair(key) { const s = load(); s.add(key); save(s); refresh(); }
function showDismissed() { save(new Set()); refresh(); }
refresh();
</script>

<?php include('../footer.php'); ?>
