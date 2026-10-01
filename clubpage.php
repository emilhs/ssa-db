<?php include('config/constants.php'); 
include('config/functions.php'); 
if (is_file(__DIR__ . '/config/visibility.php')) { include_once(__DIR__ . '/config/visibility.php'); }
if (!function_exists('visible_sql')) { function visible_sql($col = 'skaterID') { return '1 = 1'; } }   // keeps the page working if config/visibility.php hasn't been uploaded
if (!defined('REQUIRE_BIRTHDAY')) { define('REQUIRE_BIRTHDAY', false); }

$currClub = isset($_GET['club']) ? (string)$_GET['club'] : '';
$clubSql = mysqli_real_escape_string($conn, $currClub);            // for SQL
$clubHtml = htmlspecialchars($currClub);                          // for the page
$clubUrl = urlencode($currClub);                                  // for links
if (isset($_GET['y'])){
    $currSeasons = array_values(array_unique(array_map('intval', array_filter(explode("s", $_GET["y"])))));
}else{
    $currSeasons = array();
}

$sql = "SELECT season, club, COUNT(skaterID) as regd FROM skaters WHERE club = '$clubSql' AND " . visible_sql('skaterID') . " GROUP BY season ORDER BY season ASC;";

$sql0 = "SELECT MAX(season) as maxs, MIN(CASE WHEN season >= 2000 THEN season END) as mins, COUNT(DISTINCT skaterID) as regd FROM skaters WHERE club = '$clubSql' AND " . visible_sql('skaterID') . ";";

// Executing the sql query
$result = mysqli_query($conn, $sql);
$result0 = mysqli_query($conn, $sql0);
// Verify that SQL Query is executed or not
if($result == TRUE and $result0 == TRUE) {
    // Count the number of rows which will be a way to verify if there is data in the database
    $count = mysqli_num_rows($result);
    $count0 = mysqli_num_rows($result0);
    $enum = 0;
    // Initialize display of Athlete Number 
    if($count > 0 and $count0 == 1) {
        ?>
        <html>
            <head>
                <meta charset="UTF-8">
                <title><?php echo strtoupper($clubHtml); ?> Club Overview</title>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <?php include_once(__DIR__ . '/config/ga.php'); ga_tag(); ?>
                <link rel="stylesheet" href="css/profilestyle.css?v=<?php echo filemtime(__DIR__ . '/css/profilestyle.css'); ?>">
            </head>
            <?php $pageTitle = 'Clubs'; include('header.php'); ?>
</html>

        <?php
        $rows0 = mysqli_fetch_assoc($result0);
        $mins = $rows0['mins'] ?? $rows0['maxs'];
        $maxs = $rows0['maxs'];
        $tregd = $rows0['regd'];
        ?>

        <?php
        $seasonCounts = array();
        while($rows = mysqli_fetch_assoc($result)){
            $seasonCounts[(int)$rows['season']] = (int)$rows['regd'];
            $club = $rows['club'];
        }
        // Default to the most recent season (also when every season has been deselected)
        $currSeasons = array_values(array_intersect($currSeasons, array_keys($seasonCounts)));
        if (!$currSeasons && $seasonCounts) { $currSeasons = array(max(array_keys($seasonCounts))); }
        ?>
        <div class = "athlete-page">
        <div class = "profile-hero club-hero">
            <div class = "profile-frost">
                <div class = "profile-name bebas-neue"><?php echo $clubHtml; ?></div>
                <table class = "darktext profiletbl">
                    <tr class = "boldtext arimo"><td>Seasons</td><td>Skaters</td></tr>
                    <tr class = "arimo"><td><?php echo ($mins-1) . '-' . sprintf('%02d', $mins%100) . ($mins != $maxs ? ' to ' . ($maxs-1) . '-' . sprintf('%02d', $maxs%100) : ''); ?></td><td><?php echo (int)$tregd; ?></td></tr>
                </table>
            </div>
        </div>
        <div class = "hero-panel">
            <div class = "btn-tbl">
            <?php
            foreach ($seasonCounts as $season => $regd) {
                $on = in_array($season, $currSeasons);
                $next = $on ? implode('s', array_diff($currSeasons, array($season))) : implode('s', $currSeasons) . 's' . $season;
                ?>
                <button onclick = "document.location='clubpage.php?club=<?php echo $clubUrl; ?>&y=<?php echo $next; ?>'" class = "darktext bebas-neue pbtns<?php echo $on ? ' activebtn' : ''; ?>"><?php echo ($season-1)."-".sprintf('%02d', $season%100); ?> <span class = "season-count"><?php echo $regd; ?></span></button>
                <?php
            }
            ?>
            </div>
        </div>
        <?php
    } else {
        header('location: clubs.php');
        exit;
    }
}
?>

<?php
$seasoncall = "season = ".implode(" OR season = ", array_map('intval', $currSeasons));
if (sizeof($currSeasons) > 0){
    $bignum = 0;
    foreach ($ageCats as $c){
        ?>
        <div class = "bestbox">
        <div class = "bebas-neue darktext bestbox-banner"><?php echo $c; ?></div>
        <table class = "bannertable arimo darktext">
        <?php
            foreach ($ageSort[$c] as $a){
                foreach (array("M", "F") as $g){
                    if (is_numeric($a) and $a >= 20){
                        $track = 111;
                        $dist = 500;
                        $skatercall = 
                        "SELECT DISTINCT S.skaterID, fName, lName, PB
                        FROM (SELECT fName, lName, skaterID FROM skaters WHERE ".visible_sql('skaterID')." AND (".$seasoncall.") AND club = '".$clubSql."' AND age >= $a AND gender = '$g') AS S
                        LEFT JOIN
                        (SELECT skaterID, MIN(time) AS PB FROM skaters NATURAL JOIN results NATURAL JOIN comps WHERE age >= $a AND gender = '$g' AND (".$seasoncall.") AND time > 0 AND track = $track AND dist = $dist GROUP BY skaterID) AS T
                        ON S.skaterID = T.skaterID
                        ORDER BY PB IS NULL ASC, PB ASC;";
                        $result = mysqli_query($conn, $skatercall);
                        $count = mysqli_num_rows($result);
                        $enum = 0;
                        if ($count > 0){
                            $bignum++;
                            ?>
                            <tr class = "subtable darktext bestbox-subbanner">
                                <th width = "30%" style = 'text-align: left'>Name</th>
                                <th width = "40%" style = 'text-align:center;'><?php echo $a; ?>+ (<?php echo $g; ?>)</th>
                                <th width = "30%" style = 'text-align: right'><?php echo $dist; ?> (<?php echo $track; ?>)</th>
                            </tr>
                            <?php
                        }
                    }
                    else {
                        if ($c == "Neo-Junior" or $c == "Junior" or $c == "Senior"){
                            $track = 111;
                            $dist = 500;
                        }
                        else{
                            $track = 100;
                            $dist = 400;
                        }
                        $skatercall = 
                        "SELECT DISTINCT S.skaterID, fName, lName, PB
                        FROM (SELECT fName, lName, skaterID FROM skaters WHERE ".visible_sql('skaterID')." AND (".$seasoncall.") AND club = '".$clubSql."' AND age = '$a' AND gender = '$g') AS S
                        LEFT JOIN
                        (SELECT skaterID, MIN(time) AS PB FROM skaters NATURAL JOIN results NATURAL JOIN comps WHERE age = '$a' AND gender = '$g' AND (".$seasoncall.") AND time > 0 AND track = $track AND dist = $dist GROUP BY skaterID) AS T
                        ON S.skaterID = T.skaterID
                        ORDER BY PB IS NULL ASC, PB ASC;";
                        $result = mysqli_query($conn, $skatercall);
                        $count = mysqli_num_rows($result);
                        $enum = 0;
                        if ($count > 0){
                            $bignum++;
                            ?>
                            <tr class = "subtable darktext bestbox-subbanner">
                                <th width = "30%" style = 'text-align: left'>Name</th>
                                <th width = "40%" style = 'text-align:center;'><?php echo $a; ?> (<?php echo $g; ?>)</th>
                                <th width = "30%" style = 'text-align: right'><?php echo $dist; ?> (<?php echo $track; ?>)</th>
                            </tr>
                            <?php
                        }
                    }
                    while($rows = mysqli_fetch_assoc($result)){
                        $skaterID = $rows['skaterID'];
                        $fName = $rows['fName'];
                        $lName = $rows['lName'];
                        $time = $rows['PB']/1000;
                        ?>
                        <tr <?php if($enum%2==0){?> class = "odd" <?php } ?> onclick="window.location='athlete.php?id=<?php echo $skaterID?>';">
                            <td colspan = "2"><?php echo $fName; ?> <?php echo strtoupper($lName); ?></td>
                            <?php
                                if ($time == 0){
                                    ?><td></td><?php
                                }
                                else if ($time == round($time, 0)){
                                    ?><td style = "text-align: right;"><?php echo gmdate("i:s", $time); ?>.00</td><?php
                                }
                                else{
                                    $decimals = end(explode(".", $time));
                                    $moreO = 3-strlen($decimals);
                                    ?><td style = "text-align: right;"><?php echo gmdate("i:s", $time); ?>.<?php echo $decimals.str_repeat("0",$moreO); ?></td><?php
                                }    
                            ?>
                        </tr>
                        <?php
                        $enum++;
                    }
                }
            }
            // $compcall = "SELECT * FROM comps WHERE ".$seasoncall;
            // $distcall = "SELECT * FROM (SELECT * FROM skaters WHERE club = '".$club."') AS S NATURAL JOIN (SELECT *, MIN(time) AS SB FROM results NATURAL JOIN (".$compcall.") AS C WHERE dist = '400' AND track = '100' GROUP BY skaterID) AS d100;";
            // echo $skatercall;
            if ($bignum == 0){ ?>
                <tr>
                    <td class = "odd" style = "text-align: center;">No skaters</td>
                </tr>
            <?php }
        ?>
        </table>
        </div>
        <?php
        $bignum = 0;
    }
}
?>
</div>

<?php include("footer.php");