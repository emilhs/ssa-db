<?php
include_once('../config/constants.php');
include_once('../config/functions.php');
include_once('VerifyAdminLogin.php');
include_once('lib.php');

$skaterID = (int)($_GET['id'] ?? 0);
$self = 'editskater.php?id=' . $skaterID;

function back_with($self, $type, $msg) {
    $_SESSION['flash'] = array($type, $msg);
    header('Location: ' . $self);
    exit;
}

// ---- Actions (each redirects back so a refresh doesn't resubmit) ----
if ($skaterID && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? '')) back_with($self, 'error', 'Your session expired, please try again.');

    // Save one season's details
    if (isset($_POST['updateinfo'])) {
        $season = (int)($_POST['season'] ?? 0);
        $fName = trim($_POST['fName'] ?? '');
        $lName = trim($_POST['lName'] ?? '');
        $age = trim($_POST['age'] ?? '');
        $gender = strtoupper(trim($_POST['gender'] ?? ''));
        $club = trim($_POST['club'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        if ($fName === '' || $lName === '' || $club === '') back_with($self, 'error', 'First name, last name and club are required.');
        if ($dob !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $dob);
            if (!$d || $d->format('Y-m-d') !== $dob || (int)$d->format('Y') < 1940 || $d > new DateTime('today')) back_with($self, 'error', 'That birthdate looks wrong.');
        }
        $cur = mysqli_prepare($conn, "SELECT dob, club FROM skaters WHERE skaterID = ? AND season = ?");
        mysqli_stmt_bind_param($cur, 'ii', $skaterID, $season);
        mysqli_stmt_execute($cur);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($cur));
        if (!$row) back_with($self, 'error', 'That season no longer exists.');
        // A club can only be changed to a club that already exists and is in Alberta (an unchanged club is always fine)
        if ($club !== $row['club']) {
            $cc = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM club WHERE clubName = ? AND alberta = TRUE");
            mysqli_stmt_bind_param($cc, 's', $club);
            mysqli_stmt_execute($cc);
            $ok = mysqli_fetch_assoc(mysqli_stmt_get_result($cc));
            if ((int)$ok['c'] === 0) back_with($self, 'error', "\"$club\" isn't an existing Alberta club. Choose a club from the list.");
        }

        $st = mysqli_prepare($conn, "UPDATE skaters SET fName = ?, lName = ?, club = ? WHERE skaterID = ? AND season = ?");
        mysqli_stmt_bind_param($st, 'sssii', $fName, $lName, $club, $skaterID, $season);
        mysqli_stmt_execute($st);
        // Gender applies to every season of the skater
        $sg = mysqli_prepare($conn, "UPDATE skaters SET gender = ? WHERE skaterID = ?");
        mysqli_stmt_bind_param($sg, 'si', $gender, $skaterID);
        mysqli_stmt_execute($sg);

        if ($dob !== ($row['dob'] ?? '')) {
            if ($dob === '') {
                mysqli_query($conn, "UPDATE skaters SET dob = NULL WHERE skaterID = $skaterID");
            } else {
                apply_dob($conn, $skaterID, $dob);   // birthdate for every season + age categories
            }
        }
        if ($dob === '') {
            $sa = mysqli_prepare($conn, "UPDATE skaters SET age = ? WHERE skaterID = ? AND season = ?");
            mysqli_stmt_bind_param($sa, 'sii', $age, $skaterID, $season);
            mysqli_stmt_execute($sa);
        }
        back_with($self, 'ok', 'Saved ' . season_label($season) . '.');
    }

    // Save one result
    if (isset($_POST['updatetime'])) {
        $raceID = (int)($_POST['raceID'] ?? 0);
        $ms = parse_time_ms($_POST['time'] ?? '');
        if ($ms === null) back_with($self, 'error', 'Enter the time like 1:23.456 or 53.314.');
        $dist = (int)($_POST['dist'] ?? 0);
        $track = (int)($_POST['track'] ?? 0);
        $disc = (int)($_POST['disc'] ?? 0);
        $st = mysqli_prepare($conn, "UPDATE results SET time = ?, dist = ?, disc = ?, track = ? WHERE raceID = ? AND skaterID = ?");
        mysqli_stmt_bind_param($st, 'iiiiii', $ms, $dist, $disc, $track, $raceID, $skaterID);
        mysqli_stmt_execute($st);
        back_with($self, 'ok', 'Result saved.');
    }

    if (isset($_POST['clearflag'])) {
        mysqli_query($conn, "UPDATE skaters SET checkInfo = FALSE WHERE skaterID = $skaterID");
        back_with($self, 'ok', 'Marked as checked.');
    }

    // Merge another skater into this one
    if (isset($_POST['inherit'])) {
        $child = (int)($_POST['child'] ?? 0);
        list($ok, $msg) = merge_skaters($conn, $skaterID, $child);
        back_with($self, $ok ? 'ok' : 'error', $msg);
    }
}

$flash = null;
if (isset($_SESSION['flash'])) { $flash = $_SESSION['flash']; unset($_SESSION['flash']); }

// ---- Load the skater ----
$seasonRows = array();
$res = mysqli_query($conn, "SELECT * FROM skaters WHERE skaterID = $skaterID ORDER BY season DESC");
while ($r = mysqli_fetch_assoc($res)) $seasonRows[] = $r;
if (!$seasonRows) { header('Location: viewskaters.php'); exit; }
$latest = $seasonRows[0];
$flagged = false;
foreach ($seasonRows as $r) if ($r['checkInfo']) $flagged = true;

$clubs = array();
$cr = mysqli_query($conn, "SELECT clubName FROM club WHERE alberta = TRUE ORDER BY clubName");
while ($c = mysqli_fetch_assoc($cr)) $clubs[] = $c['clubName'];

$results = array();
$rr = mysqli_query($conn, "SELECT r.*, c.compName, c.season, d.date FROM results r JOIN comps c ON c.compID = r.compID LEFT JOIN dates d ON d.compID = r.compID AND d.dayID = r.dayID WHERE r.skaterID = $skaterID ORDER BY d.date DESC, r.track, r.dist ASC");
while ($r = mysqli_fetch_assoc($rr)) $results[] = $r;

$others = array();
$or = mysqli_query($conn, "SELECT skaterID, fName, lName FROM skaters s WHERE skaterID <> $skaterID AND season = (SELECT MAX(season) FROM skaters WHERE skaterID = s.skaterID) ORDER BY lName, fName");
while ($o = mysqli_fetch_assoc($or)) $others[] = $o;

$token = csrf_token();
include('navbar.php');
?>
<link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime(__DIR__ . '/../css/admin.css'); ?>">

<main class = "admin-page admin-wide">
    <div class = "edit-top">
        <a class = "edit-back" href = "viewskaters.php">&lsaquo; All skaters</a>
        <a class = "edit-back" href = "../athlete.php?id=<?php echo $skaterID; ?>" target = "_blank">View public page &#8599;</a>
    </div>
    <h1 class = "admin-h1 bebas-neue"><?php echo htmlspecialchars($latest['fName'] . ' ' . $latest['lName']); ?> <small>#<?php echo $skaterID; ?></small></h1>

    <?php if ($flash) { ?><p class = "admin-msg <?php echo $flash[0] === 'ok' ? 'ok' : 'err'; ?>"><?php echo htmlspecialchars($flash[1]); ?></p><?php } ?>

    <?php if ($flagged) { ?>
    <form method = "post" class = "check-auto">
        <input type = "hidden" name = "csrf" value = "<?php echo $token; ?>">
        <span>This skater was flagged <strong>CHECK INFO</strong> when their details were uploaded.</span>
        <button class = "pair-btn primary" type = "submit" name = "clearflag" value = "1">Mark as checked</button>
    </form>
    <?php } ?>

    <section class = "edit-card">
        <div class = "edit-card-title bebas-neue">Details by season</div>
        <p class = "admin-hint">Gender and birthdate apply to every season. Changing the birthdate updates the age category of each season.</p>
        <?php foreach ($seasonRows as $r) { ?><form id = "sf<?php echo (int)$r['season']; ?>" method = "post"><input type = "hidden" name = "csrf" value = "<?php echo $token; ?>"><input type = "hidden" name = "season" value = "<?php echo (int)$r['season']; ?>"></form><?php } ?>
        <div class = "edit-scroll">
        <table class = "edit-table">
            <tr><th>Season</th><th>First name</th><th>Last name</th><th>Gender</th><th>Club</th><th>Birthdate</th><th>Age</th><th></th></tr>
            <?php foreach ($seasonRows as $r) { $g = $r['gender']; $f = 'sf' . (int)$r['season']; ?>
                <tr>
                    <td class = "edit-season"><?php echo season_ok((int)$r['season']) ? htmlspecialchars(season_label((int)$r['season'])) : '<span class = "edit-badseason">' . (int)$r['season'] . '</span>'; ?></td>
                    <td><input form = "<?php echo $f; ?>" name = "fName" value = "<?php echo htmlspecialchars($r['fName']); ?>" required></td>
                    <td><input form = "<?php echo $f; ?>" name = "lName" value = "<?php echo htmlspecialchars($r['lName']); ?>" required></td>
                    <td><select form = "<?php echo $f; ?>" name = "gender">
                        <option value = "M" <?php echo $g === 'M' ? 'selected' : ''; ?>>M</option>
                        <option value = "F" <?php echo $g === 'F' ? 'selected' : ''; ?>>F</option>
                        <?php if ($g !== 'M' && $g !== 'F') { ?><option value = "<?php echo htmlspecialchars($g); ?>" selected><?php echo htmlspecialchars($g ?: 'Unknown'); ?></option><?php } ?>
                    </select></td>
                    <td><select form = "<?php echo $f; ?>" name = "club" required>
                        <?php if (!in_array($r['club'], $clubs, true)) { ?><option value = "<?php echo htmlspecialchars($r['club']); ?>" selected><?php echo htmlspecialchars($r['club']); ?> (not an Alberta club)</option><?php } ?>
                        <?php foreach ($clubs as $c) { ?><option value = "<?php echo htmlspecialchars($c); ?>" <?php echo $c === $r['club'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option><?php } ?>
                    </select></td>
                    <td><input form = "<?php echo $f; ?>" type = "date" name = "dob" value = "<?php echo htmlspecialchars((string)$r['dob']); ?>"></td>
                    <td><input form = "<?php echo $f; ?>" name = "age" class = "edit-age" value = "<?php echo htmlspecialchars($r['age']); ?>" <?php echo $r['dob'] ? 'readonly title = "Worked out from the birthdate"' : ''; ?>></td>
                    <td><button form = "<?php echo $f; ?>" class = "pair-btn primary" type = "submit" name = "updateinfo" value = "1">Save</button></td>
                </tr>
            <?php } ?>
        </table>
        </div>
    </section>

    <section class = "edit-card">
        <div class = "edit-card-title bebas-neue">Results (<?php echo count($results); ?>)</div>
        <?php if (!$results) { ?><p class = "admin-hint">No results yet.</p><?php } else { ?>
        <p class = "admin-hint">Enter times like 1:23.456 or 53.314.</p>
        <?php foreach ($results as $r) { ?><form id = "rf<?php echo (int)$r['raceID']; ?>" method = "post"><input type = "hidden" name = "csrf" value = "<?php echo $token; ?>"><input type = "hidden" name = "raceID" value = "<?php echo (int)$r['raceID']; ?>"></form><?php } ?>
        <div class = "edit-scroll">
        <table class = "edit-table">
            <tr><th>Competition</th><th>Date</th><th>Distance</th><th>Track</th><th>Discipline</th><th>Time</th><th></th></tr>
            <?php foreach ($results as $r) { $f = 'rf' . (int)$r['raceID']; ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['compName']); ?> <span class = "check-sub"><?php echo season_ok((int)$r['season']) ? htmlspecialchars(season_label((int)$r['season'])) : (int)$r['season']; ?></span></td>
                    <td><?php echo htmlspecialchars((string)$r['date']); ?></td>
                    <td><select form = "<?php echo $f; ?>" name = "dist"><?php foreach ($alldistances as $d) { ?><option value = "<?php echo $d; ?>" <?php echo $d == $r['dist'] ? 'selected' : ''; ?>><?php echo $d; ?></option><?php } ?></select></td>
                    <td><select form = "<?php echo $f; ?>" name = "track"><?php foreach ($Alltracks as $t) { ?><option value = "<?php echo $t; ?>" <?php echo $t == $r['track'] ? 'selected' : ''; ?>><?php echo $t; ?></option><?php } ?></select></td>
                    <td><select form = "<?php echo $f; ?>" name = "disc"><?php foreach (array_keys($discSort) as $d) { ?><option value = "<?php echo $d; ?>" <?php echo $d == $r['disc'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($discSort[$d]); ?></option><?php } ?></select></td>
                    <td><input form = "<?php echo $f; ?>" name = "time" class = "edit-time" value = "<?php echo $r['time'] ? format_time_ms($r['time']) : ''; ?>" placeholder = "m:ss.mmm" required></td>
                    <td><button form = "<?php echo $f; ?>" class = "pair-btn primary" type = "submit" name = "updatetime" value = "1">Save</button></td>
                </tr>
            <?php } ?>
        </table>
        </div>
        <?php } ?>
    </section>

    <section class = "edit-card">
        <div class = "edit-card-title bebas-neue">Merge another skater into this one</div>
        <p class = "admin-hint">Moves the other skater's results, points and seasons here, then deletes them. This can't be undone. To find likely duplicates, use <a href = "merge.php">Merge Duplicates</a>.</p>
        <form method = "post" class = "edit-merge" onsubmit = "const m = this.pick.value.match(/#(\d+)\)\s*$/); if (!m) { alert('Choose a skater from the list.'); return false; } this.child.value = m[1]; return confirm('Merge ' + this.pick.value + ' into this skater? This cannot be undone.');">
            <input type = "hidden" name = "csrf" value = "<?php echo $token; ?>">
            <input type = "hidden" name = "child" value = "">
            <input name = "pick" list = "others" placeholder = "Type a name to choose a skater" autocomplete = "off">
            <datalist id = "others"><?php foreach ($others as $o) { ?><option value = "<?php echo htmlspecialchars($o['fName'] . ' ' . $o['lName'] . ' (#' . $o['skaterID'] . ')'); ?>"><?php } ?></datalist>
            <button class = "pair-btn danger" type = "submit" name = "inherit" value = "1">Merge into this skater</button>
        </form>
    </section>
</main>

<?php include('../footer.php'); ?>
