<?php include('navbar.php'); 

$currDists = array();
if (isset($_GET['y'])){
    $currSeason = $_GET["y"]; 
}
if (isset($_GET['a'])){
    $currAges = array_filter(explode("y", $_GET["a"])); 
    $currAges = array_values($currAges);
}else{
    $currAges = array();
}
if (isset($_GET['d'])){
    $currDists = array_filter(explode("m",$_GET["d"])); 
    asort($currDists);
    $currDists = array_values($currDists);
}
if (isset($_GET['g'])){
    $currGender = $_GET['g'];
}
if (isset($_GET['aC'])){
    $currCat = $_GET["aC"]; 
}
if (isset($_GET['t'])){
    $currTrack = $_GET["t"]; 
    if ($currTrack == 100){
        $avaldists = $distances100;
        $currDists = array_intersect($currDists, $distances100);
    }
    else if ($currTrack == 111){
        $avaldists = $distances111;
        $currDists = array_intersect($currDists, $distances111);
    }
    else if ($currTrack == 400){
        $avaldists = $distances400;
        $currDists = array_intersect($currDists, $distances400);
    }
    else if ($currTrack == NULL){
        $avaldists = $alldistances;
    }
}
else {
    $avaldists = $alldistances;
}

?>
<p class = "bebas-neue darktext pagetitle"><span class = "darktext">Ranking List</span></p>

<div class = "ui-container">
    <div class = "options">
        <!-- <p class = "arimo darktext smallsize"><span class = "darktext">Specify the Ranking List</span></p>
        <br> -->
        <p class = "selector-label top-selector-label bebas-neue whitetext text-center smallsize">Select <span class = "whitetext">Season:</span></p>
        <?php
        $sql2 = "SELECT DISTINCT season FROM comps ORDER BY season ASC;";
            #$sql = "SELECT fName, lName, country FROM athletes WHERE athleteID = '$athleteID';";
            // Executing the sql query
            $result2 = mysqli_query($conn, $sql2);
            // Verify that SQL Query is executed or not
            if($result2 == TRUE) {
                // Count the number of rows which will be a way to verify if there is data in the database
                $count2 = mysqli_num_rows($result2);
                // Initialize display of Athlete Number 
                if($count2 > 0){
                    while($rows2 = mysqli_fetch_assoc($result2)){
                        $season = $rows2['season'];
                        if ($season == $currSeason){
                            $url = "y=&aC=".$currCat."&a=".implode("y",$currAges)."&g=".$currGender."&t=".$currTrack."&d=".implode("m", $currDists);
                            ?>
                            <a class = "bebas-neue darktext selectorbtn-selected" href = "ranking.php?<?php echo $url; ?>">
                                <?php echo ($season-1); ?>-<?php echo $season; ?>
                            </a>
                            <?php 
                        }
                        else {
                            $url = "y=".$season."&aC=".$currCat."&a=".implode("y",$currAges)."&g=".$currGender."&t=".$currTrack."&d=".implode("m", $currDists);
                            ?>
                            <a class = "bebas-neue darktext selectorbtn" href = "ranking.php?<?php echo $url;?>">
                                <?php echo ($season-1); ?>-<?php echo $season; ?>
                            </a>
                            <?php 
                        }
                    }
                }
            }
        ?>
        <p class = "selector-label bebas-neue whitetext text-center smallsize">Select <span class = "whitetext">Age Category:</span></p>
        <?php
            foreach ($ageCats as $c){
                if ($c == $currCat){
                    $url = "y=".$currSeason."&aC=&a=&g=".$currGender."&t=".$currTrack."&d=".implode("m", $currDists);
                    ?>
                    <a class = "bebas-neue darktext selectorbtn-selected" href = "ranking.php?<?php echo $url; ?>"><?php echo $c; ?></a>                    <?php 
                }
                else {
                    #$url = "y=".$currSeason."&aC=".$c."&a=".implode("y",$ageSort[$c])."&g=".$currGender."&t=".$defaultTrack[$c]."&d=".$defaultRank[$c];
                    $url = "y=".$currSeason."&aC=".$c."&a=&g=".$currGender."&t=".$defaultTrack[$c]."&d=".$defaultRank[$c];
                    ?>
                    <a class = "bebas-neue darktext selectorbtn" href = "ranking.php?<?php echo $url; ?>"><?php echo $c; ?></a>
                    <?php 
                }
            }
        ?>
        <p class = "selector-label bebas-neue whitetext text-center smallsize">Select <span class = "whitetext">Age:</span></p>
        <?php
            if ($currCat != NULL){
                foreach ($ageSort[$currCat] as $a){
                    if (in_array($a, $currAges)){
                        $newAges = array_diff($currAges, array($a));
                        $url = "y=".$currSeason."&aC=".$currCat."&a=".implode("y", $newAges)."&g=".$currGender."&t=".$currTrack."&d=".implode("m",$currDists);
                        ?>
                        <a class = "bebas-neue darktext selectorbtn-selected" href = "ranking.php?<?php echo $url; ?>"><?php echo $a; ?></a>
                        <?php 
                    }
                    else {
                        $url = "y=".$currSeason."&aC=".$currCat."&a=".implode("y", array_merge($currAges, array($a)))."&g=".$currGender."&t=".$currTrack."&d=".implode("m",$currDists);
                        ?>
                        <a class = "bebas-neue darktext selectorbtn" href = "ranking.php?<?php echo $url; ?>"><?php echo $a; ?></a>
                        <?php 
                    }
                }
            }
            else {
                ?><p class = "arimo darktext text-center padded smallsize">Select an age category to see more</p><?php
            }
        ?>
        <p class = "selector-label bebas-neue whitetext text-center smallsize">Select <span class = "whitetext">Gender:</span></p>
        <?php
            if ($currCat != NULL){
                foreach ($genderSort[$currCat] as $g){
                    if (sizeof($genderSort[$currCat]) == 1 or $currGender == $g){
                        $currGender = $g;
                        $url = "y=".$currSeason."&aC=".$currCat."&a=".implode("y", $currAges)."&g=&t=".$currTrack."&d=".implode("m",$currDists);
                        ?>
                        <a class = "bebas-neue darktext selectorbtn-selected" href = "ranking.php?<?php echo $url; ?>"><?php echo $g; ?></a>
                        <?php 
                    }
                    else {
                        $url = "y=".$currSeason."&aC=".$currCat."&a=".implode("y", $currAges)."&g=".$g."&t=".$currTrack."&d=".implode("m",$currDists);
                        ?>
                        <a class = "bebas-neue darktext selectorbtn" href = "ranking.php?<?php echo $url; ?>"><?php echo $g; ?></a>
                        <?php 
                    }
                }
            }
            else {
                ?><p class = "arimo darktext text-center padded smallsize">Select an age category to see more</p><?php
            }
        ?>
    <p class = "selector-label bebas-neue whitetext text-center smallsize">Select <span class = "whitetext">Track:</span></p>
        <?php 
            foreach (array(100, 111, 400) as $t){
                if ($currTrack == $t){
                    ?>
                    <a class = "bebas-neue whitetext selectorbtn-selected" href = ""><?php echo $t; ?></a>
                    <?php
                }
                else {
                    $url = "y=".$currSeason."&aC=".$currCat."&a=".implode("y",$currAges)."&g=".$currGender."&t=".$t."&d=".implode("m",$currDists);
                    ?>
                    <a class = "bebas-neue darktext selectorbtn" href = "ranking.php?<?php echo $url; ?>"><?php echo $t; ?></a>
                    <?php
                }
            }
        ?>
        <p class = "selector-label bebas-neue whitetext text-center smallsize">Select <span class = "whitetext">Distance(s):</span></p>
        <?php
            if ($currTrack != NULL){
                if ($currCat == "Senior" or $currCat == "Junior" or $currCat == "Neo-Junior"){
                    foreach ($avaldists as $d){
                        if (in_array($d, $currDists)){
                            $url = "y=".$currSeason."&aC=".$currCat."&a=".implode("y",$currAges)."&g=".$currGender."&t=".$currTrack."&d=".implode("m", array_diff($currDists, array($d)));
                            ?>
                            <a class = "bebas-neue whitetext selectorbtn-selected" href = "ranking.php?<?php echo $url; ?>"><?php echo $d; ?></a>
                            <?php 
                        }
                        else{
                            $url = "y=".$currSeason."&aC=".$currCat."&a=".implode("y",$currAges)."&g=".$currGender."&t=".$currTrack."&d=".implode("m", array_merge($currDists, array($d)));
                            ?>
                            <a class = "bebas-neue darktext selectorbtn" href = "ranking.php?<?php echo $url; ?>"><?php echo $d; ?></a>
                            <?php 
                        }
                    }
                }
                else {
                    foreach ($avaldists as $d){
                        if (in_array($d, $currDists)){
                            $url = "y=".$currSeason."&aC=".$currCat."&a=".implode("y",$currAges)."&g=".$currGender."&t=".$currTrack."&d=".$d;
                            ?>
                            <a class = "bebas-neue whitetext selectorbtn-selected" href = "ranking.php?<?php echo $url; ?>"><?php echo $d; ?></a>
                            <?php 
                        }
                        else{
                            $url = "y=".$currSeason."&aC=".$currCat."&a=".implode("y",$currAges)."&g=".$currGender."&t=".$currTrack."&d=".$d;
                            ?>
                            <a class = "bebas-neue darktext selectorbtn" href = "ranking.php?<?php echo $url; ?>"><?php echo $d; ?></a>
                            <?php 
                        }
                    }
                }
            }
            else {
                ?><p class = "arimo darktext text-center padded smallsize">Select a track see more</p><?php
            }
        ?>
    </div>
    <div class = "ranking">
    <?php
        if (sizeof($currAges) > 0 and $currTrack > 0 and sizeof($currDists) > 0){

            if ($currSeason > 0){
                $counter = 0;
                foreach ($currDists as $d){
                    $currLetter = $letters[$counter];
                    $nextLetter = $letters[$counter+1];
    
                    $selectQ1[] = $currLetter.".".$currLetter."time";
                    if ($currTrack == 111){
                        $samalog = $d/500;
                        $selectQ2[] = $currLetter.".".$currLetter."time/".$samalog;
                    }
                    else{
                        $selectQ2[] = $currLetter.".".$currLetter."time";
                    }
                    $whereQ[] = $currLetter."time > 0 AND ".$currLetter."time < 3000000";
                    if ($d != end($currDists)){
                        $onQ[] = $currLetter.".skaterID"."=".$nextLetter.".skaterID";
                    }
                    $fromQ[] = "(SELECT skaterID, dayID, compID, raceID, MIN(time) AS ".$currLetter."time FROM results AS res NATURAL JOIN comps AS comps WHERE season = ".$currSeason." AND dist = ".$d." AND track = ".$currTrack." AND time IS NOT NULL AND time > 0 GROUP BY skaterID) AS ".$currLetter; 
                    $counter++;
                }
            }
            else {
                $counter = 0;
                foreach ($currDists as $d){
                    $currLetter = $letters[$counter];
                    $nextLetter = $letters[$counter+1];
    
                    $selectQ1[] = $currLetter.".".$currLetter."time";
                    if ($currTrack == 111){
                        $samalog = $d/500;
                        $selectQ2[] = $currLetter.".".$currLetter."time/".$samalog;
                    }
                    else{
                        $selectQ2[] = $currLetter.".".$currLetter."time";
                    }
                    $whereQ[] = $currLetter."time > 0 AND ".$currLetter."time < 3000000";
                    if ($d != end($currDists)){
                        $onQ[] = $currLetter.".skaterID"."=".$nextLetter.".skaterID";
                    }
                    $fromQ[] = "(SELECT skaterID, dayID, compID, raceID, MIN(time) AS ".$currLetter."time FROM results AS res NATURAL JOIN comps AS comps WHERE dist = ".$d." AND track = ".$currTrack." AND time IS NOT NULL AND time > 0 GROUP BY skaterID) AS ".$currLetter; 
                    $counter++;
                }
            }

            $WhereQuery = implode(" AND ", $whereQ);
            $SelectQuery = implode(", ",$selectQ1).", MIN(".implode("+", $selectQ2).")";
            $FromQuery = implode(" JOIN ", $fromQ);
            if (sizeof($currDists) > 1){
                $OnQuery = implode(" AND ", $onQ);
                $rank = "SELECT A.skaterID, ".$SelectQuery." AS rankTime 
                FROM ".$FromQuery." 
                ON ".$OnQuery."
                WHERE ".$WhereQuery."
                GROUP BY A.skaterID
                ORDER BY rankTime ASC;";
            }
            else {
                $rank = "SELECT A.skaterID, ".$SelectQuery." AS rankTime 
                FROM ".$FromQuery."
                WHERE ".$WhereQuery."
                GROUP BY A.skaterID
                ORDER BY rankTime ASC;";
            }

            $result = mysqli_query($conn, $rank) or die(mysqli_error());
            $count = mysqli_num_rows($result);
            $displayNum = 1;
            if($count > 0) {
                ?>
                <table class = "darktext rankresult arimo">
                    <tr class = "toprow">
                        <th class = "row-left">#</th>
                        <th class = "row-mid">First Name</th>
                        <th class = "row-mid">Last Name</th>
                        <th class = "row-mid">Club</th>
                        <?php
                        foreach ($currDists as $d){
                            ?>
                            <th <?php if($d == end($currDists) and sizeof($currDists) == 1){?> class = "row-right" <?php } else { ?> class = "row-mid" <?php } ?>><?php echo $d; ?>m</th>
                            <?php
                        }
                        if (sizeof($currDists) > 1){
                            ?>
                            <th class = "row-right">Combined</th>
                            <?php
                        }
                        ?>
                    </tr>
                <?php
                while($rows = mysqli_fetch_assoc($result)){
                    // Store database details in variables. 
                    $skaterID = $rows['skaterID'];
                    $Atime = $rows['Atime'];
                    $Btime = $rows['Btime'];
                    $rankTime = $rows['rankTime'];

                    if ($currSeason > 0){
                        $addQuery = "";
                        if ($currCat != NULL){
                            if ($currCat == "Senior" and sizeof($currAges) > 1) {
                                $addQuery = $addQuery." AND season = '".$currSeason."' AND (age >= '".implode("OR age >= '", $currAges)."')";
                            }
                            else {
                                $addQuery = $addQuery." AND season = '".$currSeason."' AND (age = '".implode("' OR age = '", $currAges)."')";
                            }
                        }
                        if ($currGender != NULL AND in_array($currGender, array("M", "F"))){
                            $addQuery = $addQuery." AND gender = '".$currGender."'";
                        }
                    }
                    else {
                        $addQuery = "";
                        if ($currCat != NULL){
                            if ($currCat == "Senior" and sizeof($currAges) > 1) {
                                $addQuery = $addQuery." AND (age >= '".implode("OR age >= '", $currAges)."')";
                            }
                            else {
                                $addQuery = $addQuery." AND (age = '".implode("' OR age = '", $currAges)."')";
                            }
                        }
                        if ($currGender != NULL AND in_array($currGender, array("M", "F"))){
                            $addQuery = $addQuery." AND gender = '".$currGender."'";
                        }
                    }

                    $skatersql = "SELECT * FROM skaters AS A NATURAL JOIN (SELECT clubName AS club FROM club WHERE alberta = TRUE) AS B WHERE skaterID = '$skaterID'".$addQuery." ORDER BY season DESC LIMIT 1;";

                    // Executing the sql query
                    $result2 = mysqli_query($conn, $skatersql);
                    // Verify that SQL Query is executed or not
                    if($result2 == TRUE) {
                        // Count the number of rows which will be a way to verify if there is data in the database
                        $count2 = mysqli_num_rows($result2);
                        // Initialize display of Athlete Number 
                        if($count2 == 1) {
                            // For everything in the database, display
                            $rows2 = mysqli_fetch_assoc($result2);
                            // Store database details in variables. 
                            $fName = $rows2['fName'];
                            $lName = $rows2['lName'];
                            $season = $rows2['season'];
                            $gender = $rows2['gender'];
                            $age = $rows2['age'];
                            $club = $rows2['club'];
                        # PRINT ROW STATEMENTS
                        ?>
                        <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?> onclick="window.location='athlete.php?id=<?php echo $skaterID?>';">
                            <td class = "row-left"><?php echo $displayNum; ?></td>
                            <td class = ""><?php echo $fName; ?></td>
                            <td class = ""><?php echo $lName; ?></td>
                            <td class = ""><?php echo $club; ?></td>
                            <?php 
                            $counter = 0;
                            foreach ($currDists as $d){
                                $showtime = $rows["".$letters[$counter]."time"]/1000;
                                ?>
                                <td <?php if($d == end($currDists) and sizeof($currDists) == 1){?> class = "row-right" <?php } else { ?> class = "" <?php } ?> >
                                <?php
                                if ($showtime == round($showtime, 0)){
                                    echo gmdate("i:s", $showtime).".00";
                                }
                                else{
                                    echo gmdate("i:s", $showtime).".".end(explode(".", $showtime));
                                }          
                                ?>
                                </td>
                                <?php
                                $counter++;
                            }
                            if (sizeof($currDists) > 1){ ?>
                                <td class = "row-right"><?php echo round($rankTime/1000,3); ?></td>
                            <?php 
                            }?>
                        </tr>
                        <?php
                        $displayNum++;
                        }
                    }
                }
                ?>
                </table>
                <?php
            }
            if ($displayNum == 1){ ?>
                <p class = "arimo darktext text-center medsize">No skaters found for the selected query</p>
            <?php }
        }
        else {
        ?><p class = "arimo darktext text-center medsize">Select an age, gender, track, and distance to begin ranking</p><?php
        }
    ?>
    </div>
</div>
<!-- 
RANKING LIST SAMALOG
 -->

<?php include("footer.php");