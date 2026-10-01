<?php include('navbar.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_ok($_POST['csrf'] ?? '')) { $_POST = array(); admin_block('Your session expired, nothing was changed. Please try again.'); }

if (isset($_POST["addcomp"]) && guard_comp_date($_POST["date"] ?? '')) {
    $compName = $_POST["compName"];
    $location = $_POST["location"];
    $date = $_POST["date"];

    $year = $date[0].$date[1].$date[2].$date[3];
    $month = $date[5].$date[6];

    // EC
    if ($month > 6){
        $season = $year + 1;
    }
    else {
        $season = $year;
    }

    $date = date("Ymd", strtotime($date));
    echo $date;

    $sqlcheck = "SELECT * FROM comps WHERE compName = '$compName' AND location = '$location' AND season = '$season';";
    $result0 = mysqli_query($conn, $sqlcheck) or die(mysqli_error());

    if ($result0 == TRUE){
        $count0 = mysqli_num_rows($result0);
        if ($count0 > 0){
            echo "ALREADY EXISTS";
        }
        else {
            $sql1 = "INSERT INTO comps SET compName = '$compName', location = '$location', season = '$season';";
            $result1 = mysqli_query($conn, $sql1) or die(mysqli_error());
            $sqlget = "SELECT compID FROM comps WHERE compName = '$compName' AND location = '$location' AND season = '$season';";
            $result2 = mysqli_query($conn, $sqlget) or die(mysqli_error());
            $count2 = mysqli_num_rows($result2);
            if ($count2 == 1){
                $rows2 = mysqli_fetch_assoc($result2);
                $compID = $rows2['compID'];

                $sqlinsert = "INSERT INTO dates SET compID = '$compID', dayID = 1, date = '$date';";
                echo $sqlinsert;
                $result3 = mysqli_query($conn, $sqlinsert) or die(mysqli_error());
                echo "SUCCESS";
            }
            else {
                "SOMETHING WENT WRONG";
            }
        }
    }



    // $result1 = mysqli_query($conn, $sql1) or die(mysqli_error());
    // echo "Success!"

}

if (isset($_POST["updatecomp"]) && guard_comp_date($_POST["date"] ?? '')) {
    $compID = $_POST["compID"];
    $compName = $_POST["compName"];
    $location = $_POST["location"];
    $date = $_POST["date"];

    $date = date("Ymd", strtotime($date));
    $year = $date[0].$date[1].$date[2].$date[3];
    $month = $date[4].$date[5];

    if ($month > 6){
        $season = $year + 1;
    }
    else{
        $season = $year;
    }

    $sql1 = "UPDATE comps SET compName = '$compName', location = '$location', season = '$season'
    WHERE compID = '$compID';";
    $result1 = mysqli_query($conn, $sql1) or die(mysqli_error());

    $sql2 = "UPDATE dates SET date = '$date'
            WHERE compID = '$compID';";
    $result2 = mysqli_query($conn, $sql2) or die(mysqli_error());
}

if (isset($_POST["deletecomp"])){
    $compID = $_POST["compID"];
    $sql1 = "DELETE FROM comps WHERE compID = '$compID';";
    $result1 = mysqli_query($conn, $sql1) or die(mysqli_error());

    $sql2 = "DELETE FROM results WHERE compID = '$compID'";
    $result2 = mysqli_query($conn, $sql2) or die(mysqli_error());

    $sql3 = "DELETE FROM points WHERE compID = '$compID'";
    $result3 = mysqli_query($conn, $sql3) or die(mysqli_error());

    $sql4 = "DELETE FROM dates WHERE compID = '$compID'";
    $result4 = mysqli_query($conn, $sql4) or die(mysqli_error());
}

if (isset($_POST["inseries"])) {
    $compID = $_POST["compID"];
    if ($compID != NULL){
        $sql = "UPDATE comps SET series = TRUE
        WHERE compID = '$compID';";
        $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    }
}

if (isset($_POST["notinseries"])) {
    $compID = $_POST["compID"];
    if ($compID != NULL){
        $sql = "UPDATE comps SET series = FALSE
        WHERE compID = '$compID';";
        $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    }
}

if (isset($_GET['y'])){
    $currSeason = $_GET["y"]; 
}
?>

<link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime(__DIR__ . '/../css/admin.css'); ?>">
<?php
$token = csrf_token();
$discByComp = array();
$dq = mysqli_query($conn, "SELECT compID, GROUP_CONCAT(DISTINCT disc) AS d FROM results GROUP BY compID");
while ($dr = mysqli_fetch_assoc($dq)) {
    $names = array();
    foreach (explode(',', $dr['d']) as $dk) { if (isset($discSort[$dk])) $names[] = $discSort[$dk]; }
    $discByComp[$dr['compID']] = implode(', ', $names);
}
$seasonList = array();
$sq = mysqli_query($conn, "SELECT DISTINCT season FROM comps ORDER BY season DESC");
while ($sr = mysqli_fetch_assoc($sq)) $seasonList[] = (int)$sr['season'];
$currSeasonInt = isset($currSeason) ? (int)$currSeason : 0;
if ($currSeasonInt) {
    $cst = mysqli_prepare($conn, "SELECT * FROM comps NATURAL JOIN dates WHERE season = ? ORDER BY date DESC");
    mysqli_stmt_bind_param($cst, 'i', $currSeasonInt);
    mysqli_stmt_execute($cst);
    $compResult = mysqli_stmt_get_result($cst);
} else {
    $compResult = mysqli_query($conn, "SELECT * FROM comps NATURAL JOIN dates ORDER BY date DESC");
}
?>

<main class = "admin-page admin-wide">
    <h1 class = "admin-h1 bebas-neue">Edit Competitions</h1>

    <section class = "edit-card">
        <div class = "edit-card-title bebas-neue">Add a competition</div>
        <p class = "admin-hint">To add a competition with results, use <a href = "submit.php">Add a Competition</a>. This adds an empty one.</p>
        <form method = "post" class = "comp-add">
            <input type = "hidden" name = "csrf" value = "<?php echo $token; ?>">
            <label>Name <input type = "text" name = "compName" required></label>
            <label>Location <input type = "text" name = "location"></label>
            <label>Date <input type = "date" name = "date" required></label>
            <button class = "pair-btn primary" type = "submit" name = "addcomp" value = "1">Add</button>
        </form>
    </section>

    <div class = "check-tabs season-tabs">
        <a class = "<?php echo $currSeasonInt ? '' : 'active'; ?>" href = "viewcomps.php">All</a>
        <?php foreach ($seasonList as $se) { ?>
            <a class = "<?php echo $se === $currSeasonInt ? 'active' : ''; ?>" href = "viewcomps.php?y=<?php echo $se; ?>"><?php echo season_ok($se) ? htmlspecialchars(season_label($se)) : $se; ?></a>
        <?php } ?>
    </div>

    <section class = "edit-card">
        <div class = "edit-card-title bebas-neue">Competitions<?php echo $currSeasonInt ? ' ' . (season_ok($currSeasonInt) ? season_label($currSeasonInt) : $currSeasonInt) : ''; ?></div>
        <?php
        $rows = array();
        if ($compResult) { while ($r = mysqli_fetch_assoc($compResult)) $rows[] = $r; }
        if (!$rows) { ?><p class = "admin-hint" style = "padding-bottom: 14px;">No competitions.</p><?php } else {
            foreach ($rows as $r) { ?><form id = "cf<?php echo (int)$r['compID'] . '-' . (int)$r['dayID']; ?>" method = "post"><input type = "hidden" name = "csrf" value = "<?php echo $token; ?>"><input type = "hidden" name = "compID" value = "<?php echo (int)$r['compID']; ?>"></form><?php } ?>
        <div class = "edit-scroll">
        <table class = "edit-table filterable">
            <tr><th>Name</th><th>Disciplines</th><th>Location</th><th>Date</th><th>Series</th><th></th></tr>
            <?php foreach ($rows as $r) { $f = 'cf' . (int)$r['compID'] . '-' . (int)$r['dayID']; $inSeries = ($r['series'] !== null && $r['series'] > 0); $disc = $discByComp[$r['compID']] ?? ''; ?>
            <tr>
                <td><input form = "<?php echo $f; ?>" name = "compName" value = "<?php echo htmlspecialchars($r['compName']); ?>"></td>
                <td><?php echo $disc !== '' ? htmlspecialchars($disc) : '<span class = "edit-badseason">More info needed</span>'; ?></td>
                <td><input form = "<?php echo $f; ?>" name = "location" value = "<?php echo htmlspecialchars($r['location']); ?>"></td>
                <td><input form = "<?php echo $f; ?>" type = "date" name = "date" value = "<?php echo htmlspecialchars((string)$r['date']); ?>"></td>
                <td class = "seg"><?php if ($inSeries) { ?>
                        <button form = "<?php echo $f; ?>" class = "pair-btn" type = "submit" name = "notinseries" value = "1">Not in series</button><span class = "pair-btn sel">In series</span>
                    <?php } else { ?>
                        <span class = "pair-btn sel">Not in series</span><button form = "<?php echo $f; ?>" class = "pair-btn" type = "submit" name = "inseries" value = "1">In series</button>
                    <?php } ?></td>
                <td class = "comp-actions">
                    <button form = "<?php echo $f; ?>" class = "pair-btn primary" type = "submit" name = "updatecomp" value = "1">Save</button>
                    <a class = "pair-btn" href = "fullcomp.php?comp=<?php echo (int)$r['compID']; ?>">Results</a>
                    <a class = "pair-btn" href = "comppoint.php?comp=<?php echo (int)$r['compID']; ?>">Points</a>
                    <button form = "<?php echo $f; ?>" class = "pair-btn danger" type = "submit" name = "deletecomp" value = "1" onclick = "return confirm('Delete this competition and ALL of its results and points? This cannot be undone.');">Delete</button>
                </td>
            </tr>
            <?php } ?>
        </table>
        </div>
        <?php } ?>
    </section>
</main>

<?php
$filterLabel = 'Filter competitions'; include('rowfilter.php');
include("../footer.php");