<?php include('navbar.php');

$currSeason = isset($_GET['y']) ? (int)$_GET['y'] : 0;
$label = function ($s) { return ($s - 1) . '-' . sprintf('%02d', $s % 100); };

// Seasons that have competitions, oldest to newest
$seasons = array();
$sr = mysqli_query($conn, "SELECT DISTINCT season FROM comps ORDER BY season ASC");
while ($r = mysqli_fetch_assoc($sr)) $seasons[] = (int)$r['season'];
// Always show one season; the most recent (last button) unless another one is chosen
if (!in_array($currSeason, $seasons)) $currSeason = $seasons ? end($seasons) : 0;

// Disciplines raced at each competition
$discByComp = array();
$dq = mysqli_query($conn, "SELECT compID, GROUP_CONCAT(DISTINCT disc) AS d FROM results GROUP BY compID");
while ($r = mysqli_fetch_assoc($dq)) {
    $names = array();
    foreach (explode(',', $r['d']) as $k) { if (isset($discSort[$k])) $names[] = $discSort[$k]; }
    $discByComp[$r['compID']] = implode(', ', $names);
}

// One row per competition, however many days it ran
$where = $currSeason ? "WHERE c.season = $currSeason" : "";
$cr = mysqli_query($conn, "SELECT c.compID, c.compName, c.location, c.season, MIN(d.date) AS firstDate, MAX(d.date) AS lastDate
    FROM comps c JOIN dates d ON d.compID = c.compID $where
    GROUP BY c.compID, c.compName, c.location, c.season
    ORDER BY firstDate DESC, c.compName");
$comps = array();
while ($r = mysqli_fetch_assoc($cr)) $comps[] = $r;
?>
<link rel="stylesheet" href="css/search.css?v=<?php echo filemtime(__DIR__ . '/css/search.css'); ?>">

<main class = "search-page">
    <div class = "search-card wide-card">
        <div class = "search-card-title bebas-neue">Competitions</div>
        <div class = "season-btns under-title">
            <?php foreach ($seasons as $s) { ?>
                <a class = "bebas-neue<?php echo $s === $currSeason ? ' on' : ''; ?>" href = "competitions.php?y=<?php echo $s; ?>"><?php echo $label($s); ?></a>
            <?php } ?>
        </div>
        <p class = "search-note">These competitions, and the skaters who raced them, are included in the database.</p>
        <?php if (!$comps) { ?>
            <p class = "search-empty">No competitions found</p>
        <?php } else { ?>
        <table class = "results-table comps-results">
            <tr class = "head"><th>Competition</th><th>Disciplines</th><th>Location</th><th>Date</th></tr>
            <?php foreach ($comps as $i => $c) {
                $date = ($c['firstDate'] === $c['lastDate']) ? $c['firstDate'] : $c['firstDate'] . ' to ' . $c['lastDate'];
                ?>
            <tr<?php echo $i % 2 ? ' class = "odd"' : ''; ?>>
                <td><?php echo htmlspecialchars($c['compName']); ?></td>
                <td><?php echo htmlspecialchars($discByComp[$c['compID']] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($c['location']); ?></td>
                <td><?php echo htmlspecialchars($date); ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } ?>
        <p class = "search-count"><?php echo count($comps); ?> competition<?php echo count($comps) == 1 ? '' : 's'; ?><?php echo $currSeason ? ' in ' . $label($currSeason) : ''; ?></p>
    </div>
</main>

<?php include("footer.php");
