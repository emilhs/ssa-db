<?php
// Download every result as a CSV file (backup). Admins only.
include_once('../config/constants.php');
include_once('../config/functions.php');
include_once('VerifyAdminLogin.php');
include_once('lib.php');

$filename = 'ssa-results-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");   // lets Excel read the accents correctly
fputcsv($out, array('raceID', 'compID', 'compName', 'season', 'date', 'location', 'skaterID', 'firstName', 'lastName', 'gender', 'club', 'distance', 'track', 'discipline', 'time', 'timeMs'));

$disc = $discSort;
$sql = "SELECT r.raceID, r.compID, c.compName, c.season, d.date, c.location, r.skaterID,
               s.fName, s.lName, s.gender, s.club, r.dist, r.track, r.disc, r.time
        FROM results r
        JOIN comps c ON c.compID = r.compID
        LEFT JOIN dates d ON d.compID = r.compID AND d.dayID = r.dayID
        LEFT JOIN skaters s ON s.skaterID = r.skaterID AND s.season = c.season
        ORDER BY d.date, r.compID, r.skaterID, r.dist, r.raceID";
$res = mysqli_query($conn, $sql, MYSQLI_USE_RESULT);
while ($r = mysqli_fetch_assoc($res)) {
    fputcsv($out, array(
        $r['raceID'], $r['compID'], $r['compName'], $r['season'], $r['date'], $r['location'], $r['skaterID'],
        $r['fName'], $r['lName'], $r['gender'], $r['club'], $r['dist'], $r['track'],
        isset($disc[$r['disc']]) ? $disc[$r['disc']] : $r['disc'],
        $r['time'] ? format_time_ms($r['time']) : '', $r['time']
    ));
}
fclose($out);
