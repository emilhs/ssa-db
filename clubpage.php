<?php include('config/constants.php'); 
include('config/functions.php'); 

if (isset($_GET['club'])){
    $currClub = $_GET["club"]; 
}
if (isset($_GET['y'])){
    $currSeasons = array_filter(explode("s", $_GET["y"])); 
}else{
    $currSeasons = array();
}

$sql = "SELECT season, club, COUNT(skaterID) as regd FROM skaters WHERE club = '$currClub' GROUP BY season ORDER BY season ASC;";

$sql0 = "SELECT MAX(season) as maxs, MIN(season) as mins, COUNT(DISTINCT skaterID) as regd FROM skaters WHERE club = '$currClub';";

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
            <meta charset="UTF-8">
            <head>
                <title><?php echo strtoupper($currClub); ?> Club Overview</title>
                <link rel="stylesheet" href="css/profilestyle.css">
            </head>
            <div class = "header">
                <a href = "index.php" class = "left">
                    <img id = "homelogo" src="images/TrimmedorgLogo2024.png" alt="">
                    <p class = "bebas-neue" id = "title"><span class = "darktext">Speed Skating</span> <span class = "bluetext">Alberta</span> <span class = "darktext">Results Database</span></p>
                </a>
                <div class = "right">
                    <a class = "darktext bebas-neue" href = "about.php">About the Site</a>
                    <br>
                    <a class = "bluetext bebas-neue" href = "signin.php">Sign in as Admin</a>
                </div>  
            </div>
        </html>

        <?php
        $rows0 = mysqli_fetch_assoc($result0);
        $mins = $rows0['mins'];
        $maxs = $rows0['maxs'];
        $tregd = $rows0['regd'];
        ?>

        <table class = "darktext profiletbl">
            <tr class = "bebas-neue darktext pagetitle">
                <td colspan = "2" style = "text-align:center;"><?php echo $currClub?></td>
            </tr>
            <tr class = "boldtext arimo">
                <td style = "text-align:right;"><?php echo ($mins-1); ?>-<?php echo ($maxs%1000); ?></td>
                <td style = "text-align:left;"><?php echo $tregd; ?> skaters</td>
            </tr>
        <?php
        while($rows = mysqli_fetch_assoc($result)){
            // Store database details in variables. 
            $season = $rows['season'];
            $club = $rows['club'];
            $regd = $rows['regd'];
            ?>
            <tr class = "arimo">
                <td style = "text-align:right;"><?php echo ($season-1)."-".($season%1000)?></td>
                <td style = "text-align:left;"><?php echo $regd; ?> skaters</td>
            </tr>
            <?php
            $enum++;
        }
        ?>
        </table>
        <?php
    } else {
        header('location: clubs.php');
    }
}
?>

<div class = "bestbox">
<div class = "bebas-neue darktext bestbox-banner">Select Season</div>
<div class = "btn-tbl">
<tr>
<?php
$result = mysqli_query($conn, $sql);
if ($result == TRUE){
    $count = mysqli_num_rows($result);
    if ($count > 0){
        while($rows = mysqli_fetch_assoc($result)){
            $season = ($rows['season']);
            if (in_array($season, $currSeasons)){
                ?>
                <td>
                <button onclick="document.location='clubpage.php?club=<?php echo $currClub; ?>&y=<?php echo implode('s',(array_diff($currSeasons, array($season)))); ?>'" class = "darktext bebas-neue pbtns activebtn"><?php echo ($season%1000-1)."-".($season%1000); ?></button>
                </td>
                <?php
            }
            else {
                ?>
                <td>
                <button onclick="document.location='clubpage.php?club=<?php echo $currClub; ?>&y=<?php echo implode('s',$currSeasons).'s'.$season; ?>'" class = "darktext bebas-neue pbtns"><?php echo ($season%1000-1)."-".($season%1000); ?></button>
                </td>
                <?php
            }
        }
    }
}
?>
</tr>
</div>
</div>

<?php
$seasoncall = "season = ".implode(" OR season = ", $currSeasons);
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
                        FROM (SELECT fName, lName, skaterID FROM skaters WHERE (".$seasoncall.") AND club = '".$club."' AND age >= $a AND gender = '$g') AS S
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
                        FROM (SELECT fName, lName, skaterID FROM skaters WHERE (".$seasoncall.") AND club = '".$club."' AND age = '$a' AND gender = '$g') AS S
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

<?php include("footer.php");