<?php include('config/constants.php'); 
include('config/functions.php'); 
if (is_file(__DIR__ . '/config/visibility.php')) { include_once(__DIR__ . '/config/visibility.php'); }
if (!function_exists('visible_sql')) { function visible_sql($col = 'skaterID') { return '1 = 1'; } }   // keeps the page working if config/visibility.php hasn't been uploaded
if (!defined('REQUIRE_BIRTHDAY')) { define('REQUIRE_BIRTHDAY', false); }

if (isset($_GET['id'])){
    $skaterID = (int)$_GET["id"];   // numbers only
}

$sql = "SELECT fName, lName, age, club, gender, season, dob, (SELECT MAX(dob) FROM skaters WHERE skaterID = '$skaterID') AS anydob FROM skaters WHERE skaterID = '$skaterID' ORDER BY season DESC LIMIT 1";

// Executing the sql query
$result = mysqli_query($conn, $sql);
// Verify that SQL Query is executed or not
if($result == TRUE) {
    // Count the number of rows which will be a way to verify if there is data in the database
    $count = mysqli_num_rows($result);
    // Initialize display of Athlete Number 
    if($count == 1) {
        // For everything in the database, display
        $rows = mysqli_fetch_assoc($result);
        // Store database details in variables. 
        $fName = $rows['fName'];
        $lName = $rows['lName'];
        $season = $rows['season'];
        $gender = $rows['gender'];
        $age = $rows['age'];
        $club = $rows['club'];
        $dob = $rows['dob'] ?: $rows['anydob'];
        if (REQUIRE_BIRTHDAY && !$dob) { header('location: search.php'); exit; }

        // Real age today, not the age category from the last season raced
        $ageShown = $age;                                         // fallback: a category such as Senior or Active Start
        if ($dob) {
            $ageShown = (new DateTime($dob))->diff(new DateTime('today'))->y;
        } elseif (is_numeric($age)) {
            // No birthdate on file: age at the start of the last season raced, plus the years since (approximate)
            $ageShown = '~' . ((int)$age + (new DateTime(($season - 1) . '-06-30'))->diff(new DateTime('today'))->y);
        }

    } else {
        header('location: search.php');
    }
}
?>

<html>
    <meta charset="UTF-8">
    <head>
        <title><?php echo strtoupper($fName); ?> <?php echo ucfirst($lName); ?> SSA Skater Profile</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
                <?php include_once(__DIR__ . '/config/ga.php'); ga_tag(); ?>
                <link rel="stylesheet" href="css/profilestyle.css?v=<?php echo filemtime(__DIR__ . '/css/profilestyle.css'); ?>">
    </head>
    <?php include('header.php'); ?>
</html>

<div class = "athlete-page">

<div class = "profile-hero">
    <div class = "profile-frost">
        <div class = "profile-name bebas-neue"><?php echo $fName; ?> <?php echo $lName; ?></div>
<table class = "darktext profiletbl">
    <tr class = "boldtext arimo">
        <td>Age</td>
        <td>Gender</td>
        <td>Club</td>
    </tr>
    <tr class = "arimo">
        <td>
            <?php echo htmlspecialchars((string)$ageShown); ?>
        </td>
        <td>
            <?php echo $gender; ?>
        </td>
        <td>
            <?php
            $sql = "SELECT club, MIN(CASE WHEN season >= 2000 THEN season END) as mins, MAX(season) as maxs FROM skaters WHERE skaterID = '$skaterID' GROUP BY club ORDER BY maxs DESC;";
            $result = mysqli_query($conn, $sql);
            // Verify that SQL Query is executed or not
            if($result == TRUE) {
                // Count the number of rows which will be a way to verify if there is data in the database
                $count = mysqli_num_rows($result);
                // Initialize display of Athlete Number 
                if($count > 0) {
                    // For everything in the database, display
                    while($rows = mysqli_fetch_assoc($result)){
                        // Store database details in variables.
                        // Ignore bad entries (e.g. season 1970) when working out the first season
                        $seasonmin = $rows['mins'] ?? $rows['maxs']; 
                        $season = $rows['maxs'];
                        $club = $rows['club'];
                        ?>
                            <?php echo $club." (".($seasonmin-1)."-".(substr($season,2,3)).")"; ?><br>
                        <?php
                    }
                } else {
                    header('location: search.php');
                }
            }
            ?>
        </td>
    </tr>
</table>
    </div>
</div>
<!-- <p class = "arimo darktext text-center tinysize">Note: skater information (age, club) is not updated until a race is done.</p> -->

<div class = "bestbox">
<div class = "bebas-neue darktext bestbox-banner">Personal Bests</div>
<?php
# DISTANCE BUTTONS
$mytracks = array();
$sqlTrack = "SELECT DISTINCT track FROM results WHERE skaterID = $skaterID ORDER BY track ASC"; 
    $resultTrack = mysqli_query($conn, $sqlTrack);
    // Verify that SQL Query is executed or not
    if($resultTrack == TRUE) {
        // Count the number of rows which will be a way to verify if there is data in the database
        $countTrack = mysqli_num_rows($resultTrack);
        // Initialize display of Athlete Number 
        if($countTrack > 0){
            $numer = 1;
            ?>
            <div class = "btn-tbl">
                <tr>
                <?php
                while($rowsTrack = mysqli_fetch_assoc($resultTrack)){
                    $track = $rowsTrack['track'];
                    if (in_array($track, $Alltracks)){
                        $mytracks[] = $track;
                        ?>
                        <td>
                        <button onclick="showTable('<?php echo $track; ?>')" id = "btn<?php echo $track; ?>" class = "darktext bebas-neue pbtns <?php if($numer == 1){?> activebtn <?php } ?>"><?php echo $track; ?></button>
                        </td>
                        <?php
                    }
                    $numer++;
                }
                ?>
                </tr>
            </div>
            <?php
        }
    }
?>
<?php
foreach ($mytracks as $t){
    $sql2 = "SELECT * FROM dates AS D NATURAL JOIN comps AS C NATURAL JOIN results AS R NATURAL JOIN 
    (SELECT dist, track, MIN(time) AS best 
        FROM results 
        WHERE track = '$t' AND skaterID = $skaterID AND time > 0 AND time < 2000000 GROUP BY dist) AS T 
        WHERE R.time = T.best AND skaterID = $skaterID ORDER BY R.dist ASC;";
        #$sql = "SELECT fName, lName, country FROM athletes WHERE athleteID = '$athleteID';";
        // Executing the sql query
        $result2 = mysqli_query($conn, $sql2);
        // Verify that SQL Query is executed or not
        if($result2 == TRUE) {
        // Count the number of rows which will be a way to verify if there is data in the database
        $count2 = mysqli_num_rows($result2);
        // Initialize display of Athlete Number 
        if($count2 > 0){
        ?>
        <div id = "table<?php echo $t; ?>" class = "pb-table<?php if ($t == $mytracks[0]){ ?> active <?php } ?>">
            <table class = "bannertable arimo darktext">
                <tr class = "darktext bestbox-subbanner">
                    <th width = "20%">Race</th>
                    <th width = "20%">Time</th>
                    <th width = "40%">Competition</th>
                    <th width = "20%">Date</th>
                </tr>
            <?php
            $displayNum = 1;
            while($rows2 = mysqli_fetch_assoc($result2)){
                $time = $rows2['best']/1000;
                $dist = $rows2['dist'];
                $track = $rows2['track'];
                $comp = $rows2['compName'];
                $season = $rows2['season'];
                $date = $rows2['date'];
                ?>
                <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>
                    <td><?php echo $dist; ?>m</td>
                    <?php
                    if ($time == round($time, 0)){
                        ?><td><?php echo gmdate("i:s", $time); ?>.00</td><?php
                    }
                    else{
                        $decimals = end(explode(".", $time));
                        $moreO = 3-strlen($decimals);
                        ?><td><?php echo gmdate("i:s", $time); ?>.<?php echo $decimals.str_repeat("0",$moreO); ?></td><?php
                    }                    
                    ?>
                    <td><?php echo $comp; ?></td>
                    <td><?php echo $date; ?></td>
                </tr>
                <?php
                $displayNum++;
            }
            ?>
            </table>
        </div>
        <?php
        }
        }
}
?>
</div>

<div class = "bestbox">
<div class = "bebas-neue darktext bestbox-banner">Season Bests</div>
<?php
# DISTANCE BUTTONS
$mytracks = array();
$sqlTrack = "SELECT DISTINCT season FROM results NATURAL JOIN comps WHERE skaterID = $skaterID ORDER BY season ASC"; 
    $resultTrack = mysqli_query($conn, $sqlTrack);
    // Verify that SQL Query is executed or not
    if($resultTrack == TRUE) {
        // Count the number of rows which will be a way to verify if there is data in the database
        $countTrack = mysqli_num_rows($resultTrack);
        // Initialize display of Athlete Number 
        if($countTrack > 0){
            $numer = 1;
            ?>
            <div class = "btn-tbl">
                <tr>
                <?php
                while($rowsTrack = mysqli_fetch_assoc($resultTrack)){
                    $track = $rowsTrack['season'];
                        $myseasons[] = $track;
                        ?>
                        <td>
                        <button onclick="showSTable('<?php echo $track; ?>')" id = "btn<?php echo $track; ?>" class = "darktext bebas-neue sbtns <?php if($numer == $countTrack){?> activebtn <?php } ?>"><?php echo ($track-1); ?>-<?php echo substr($track,2,3); ?></button>
                        </td>
                        <?php
                    $numer++;
                }
                ?>
                </tr>
            </div>
            <?php
        }
    }
?>
<?php
foreach ($myseasons as $s){
    $sql2 = "SELECT * FROM dates AS D NATURAL JOIN comps AS C NATURAL JOIN results AS R NATURAL JOIN 
    (SELECT dist, track, MIN(time) AS best 
        FROM results NATURAL JOIN comps
        WHERE season = '$s' AND skaterID = $skaterID AND time > 0 AND time < 2000000 GROUP BY dist, track) AS T 
        WHERE R.time = T.best AND skaterID = $skaterID ORDER BY R.dist ASC, R.track ASC;";
        #$sql = "SELECT fName, lName, country FROM athletes WHERE athleteID = '$athleteID';";
        // Executing the sql query
        $result2 = mysqli_query($conn, $sql2);
        // Verify that SQL Query is executed or not
        if($result2 == TRUE) {
        // Count the number of rows which will be a way to verify if there is data in the database
        $count2 = mysqli_num_rows($result2);
        // Initialize display of Athlete Number 
        if($count2 > 0){
        ?>
        <div id = "table<?php echo $s; ?>" class = "s-table<?php if ($s == end($myseasons)){ ?> active <?php } ?>">
        <table class = "bannertable arimo darktext">
                <tr class = "darktext bestbox-subbanner">
                    <th width = "20%">Race</th>
                    <th width = "20%">Time</th>
                    <th width = "40%">Competition</th>
                    <th width = "20%">Date</th>
                </tr>
            <?php
            $displayNum = 1;
            while($rows2 = mysqli_fetch_assoc($result2)){
                $time = $rows2['best']/1000;
                $dist = $rows2['dist'];
                $disc = $rows2['disc'];
                $track = $rows2['track'];
                $comp = $rows2['compName'];
                $season = $rows2['season'];
                $date = $rows2['date'];
                ?>
                <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>
                    <td><?php echo $dist; ?>m (<?php echo $track; ?>)</td>
                    <?php
                    if ($time == round($time, 0)){
                        ?><td><?php echo gmdate("i:s", $time); ?>.00</td><?php
                    }
                    else{
                        $decimals = end(explode(".", $time));
                        $moreO = 3-strlen($decimals);
                        ?><td><?php echo gmdate("i:s", $time); ?>.<?php echo $decimals.str_repeat("0",$moreO); ?></td><?php
                    }                      
                    ?>
                    <td><?php echo $comp; ?></td>
                    <td><?php echo $date; ?></td>
                </tr>
                <?php
                $displayNum++;
            }
            ?>
            </table>
        </div>
        <?php
        }
        }
}
?>
</div>

<div class = "bestbox">
<div class = "bebas-neue darktext bestbox-banner">Results</div>
<?php
$sql2 = "SELECT *
            FROM results NATURAL JOIN dates NATURAL JOIN comps
            WHERE skaterID = '$skaterID'
            ORDER BY date DESC, disc, track, dist ASC;";
    #$sql = "SELECT fName, lName, country FROM athletes WHERE athleteID = '$athleteID';";
    // Executing the sql query
    $result2 = mysqli_query($conn, $sql2);
    // Verify that SQL Query is executed or not
    if($result2 == TRUE) {
        $displayNum = 1;
        // Count the number of rows which will be a way to verify if there is data in the database
        $count2 = mysqli_num_rows($result2);
        // Initialize display of Athlete Number 
        if($count2 > 0){
            ?>
            <table class = "bannertable arimo darktext">
            <?php
            $oldcomp = "";
            while($rows2 = mysqli_fetch_assoc($result2)){
                $fulltime = $rows2['time'];
                $time = $fulltime/1000;
                $dist = $rows2['dist'];
                $track = $rows2['track'];
                $compName = $rows2['compName'];
                $season = $rows2['season'];
                $date = $rows2['date'];
                if ($compName != $oldcomp){
                    $displayNum = 1;
                    $oldcomp = $compName;
                    ?>
                    <tr class = "subtable darktext bestbox-subbanner">
                        <th width = "50%" style = "text-align: left"><?php echo $compName?></th>
                        <th style = "text-align: right"></th>
                        <th style = "text-align: right"><?php echo $date?></th>
                    </tr>
                    <?php
                }

                ?>
                <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>
                        <td style = "text-align: left"><?php echo $dist; ?>m (<?php echo $track;?>)</td>
                        <?php
                    if ($time == 0 or $time == NULL or $time > 600){ ?>
                        <td style = "text-align: right" class = "row-left"><span class = "dangertext">NO TIME</span></td>
                        <td></td>
                        <?php
                    }
                    else { 
                        if ($time == round($time, 0)){
                            ?><td style = "text-align: right;"><?php echo gmdate("i:s", $time); ?>.00</td><?php
                        }
                        else{
                            $decimals = end(explode(".", $time));
                            $moreO = 3-strlen($decimals);
                            ?><td><?php echo gmdate("i:s", $time); ?>.<?php echo $decimals.str_repeat("0",$moreO); ?></td><?php
                        }                           
                        $isPB = "SELECT * FROM results NATURAL JOIN dates WHERE skaterID = '$skaterID' AND track = '$track' AND dist = '$dist' AND date <= '$date' AND time < '$fulltime' AND time > 0;"; 
                        $isSB = "SELECT * FROM results NATURAL JOIN dates NATURAL JOIN comps WHERE season = '$season' AND skaterID = '$skaterID' AND track = '$track' AND dist = '$dist' AND date <= '$date' AND time < '$fulltime' AND time > 0;"; 
    
                        $result3 = mysqli_query($conn, $isPB);
                        $result4 = mysqli_query($conn, $isSB);
                        // Verify that SQL Query is executed or not
                        if($result3 == TRUE and $result4 == TRUE) {
                            // Count the number of rows which will be a way to verify if there is data in the database
                            $count3 = mysqli_num_rows($result3);
                            $count4 = mysqli_num_rows($result4);
                            // Initialize display of Athlete Number 
                            if ($count3 == 0){
                                ?>
                                <td style = "text-align: right;">PB SB</td>
                                <?php
                            }
                            else if ($count4 == 0){
                                ?>
                                <td style = "text-align: right;">SB</td>
                                <?php
                            }
                            else {
                                ?>
                                <td style = "text-align: right;"></td>
                                <?php
                            }
                        }
                    } ?>
                </tr>
                <?php
                $displayNum++;
            }
            ?></table><?php
        }
    }
?>
</div>


<!-- <a href = "search.php?f=<#?php echo $fName[0]; ?>&l=<#?php echo $lName[0]; ?>">back to search</a> -->

<script>
    function showTable(id) {
        let tableid = "table".concat(id);
        const tables = document.querySelectorAll('.pb-table');
        tables.forEach(table => table.classList.remove('active'));
        document.getElementById(tableid).classList.add('active');

        let buttonid = "btn".concat(id);
        const buttons = document.querySelectorAll('.pbtns');
        buttons.forEach(button => button.classList.remove('activebtn'));
        document.getElementById(buttonid).classList.add('activebtn');
    }
</script>

<script>
    function showSTable(id) {
        let tableid = "table".concat(id);
        const tables = document.querySelectorAll('.s-table');
        tables.forEach(table => table.classList.remove('active'));
        document.getElementById(tableid).classList.add('active');

        let buttonid = "btn".concat(id);
        const buttons = document.querySelectorAll('.sbtns');
        buttons.forEach(button => button.classList.remove('activebtn'));
        document.getElementById(buttonid).classList.add('activebtn');
    }
</script>


</div>
<?php include('footer.php'); ?>
