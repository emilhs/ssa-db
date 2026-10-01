<?php include('navbar2.php');
if (is_file(__DIR__ . '/config/visibility.php')) { include_once(__DIR__ . '/config/visibility.php'); }
if (!function_exists('visible_sql')) { function visible_sql($col = 'skaterID') { return '1 = 1'; } }   // keeps the page working if config/visibility.php hasn't been uploaded
if (!defined('REQUIRE_BIRTHDAY')) { define('REQUIRE_BIRTHDAY', false); }

// Most recent real season in the data (ignores stray entries like 1970)
$lr = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(season) AS m FROM skaters WHERE season BETWEEN 2000 AND " . ((int)date('Y') + 2)));
$latest = (int)$lr['m'];

// One row per Alberta club. Skaters are counted once each, however many seasons they raced.
$sql = "SELECT c.clubName,
               COUNT(DISTINCT s.skaterID) AS total,
               COUNT(DISTINCT CASE WHEN s.season = $latest THEN s.skaterID END) AS current,
               MIN(CASE WHEN s.season >= 2000 THEN s.season END) AS firstSeason,
               MAX(s.season) AS lastSeason
        FROM club c JOIN skaters s ON s.club = c.clubName
        WHERE c.alberta = TRUE AND " . visible_sql('s.skaterID') . "
        GROUP BY c.clubName
        ORDER BY current DESC, total DESC, c.clubName ASC;";
$result = mysqli_query($conn, $sql);
$clubs = array();
if ($result) { while ($r = mysqli_fetch_assoc($result)) $clubs[] = $r; }
$seasonLabel = function ($s) { return sprintf('%02d-%02d', ($s - 1) % 100, $s % 100); };
?>

<link rel="stylesheet" href="css/search.css?v=<?php echo filemtime(__DIR__ . '/css/search.css'); ?>">

<main class = "search-page">
    <div class = "search-card clubs-card">
        <div class = "search-card-title bebas-neue">Clubs</div>
        <p class = "search-note">Skaters are counted once, however many seasons they raced.</p>
        <table class = "results-table clubs-results">
            <tr class = "head"><th>Club</th><th><?php echo $latest ? $seasonLabel($latest) : 'Current'; ?></th><th>All time</th></tr>
            <?php foreach ($clubs as $i => $c) { ?>
            <tr<?php echo $i % 2 ? ' class = "odd"' : ''; ?> onclick = "window.location = 'clubpage.php?club=<?php echo urlencode($c['clubName']); ?>';">
                <td><?php echo htmlspecialchars($c['clubName']); ?></td>
                <td><?php echo (int)$c['current']; ?></td>
                <td><?php echo (int)$c['total']; ?></td>
            </tr>
            <?php } ?>
        </table>
        <p class = "search-count"><?php echo count($clubs); ?> Alberta club<?php echo count($clubs) == 1 ? '' : 's'; ?></p>
    </div>
</main>

<?php include("footer.php");
