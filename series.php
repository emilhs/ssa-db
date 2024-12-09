<?php include('navbar.php'); 

$currDists = array();
if (isset($_GET['y'])){
    $currSeason = $_GET["y"]; 
}
if (isset($_GET['a'])){
    $currAge = $_GET["a"]; 
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
        $currDists = array_diff($currDists, $only111);
    }
    else if ($currTrack == 111){
        $avaldists = $distances111;
        $currDists = array_diff($currDists, $only100);
    }
    else if ($currTrack == NULL){
        $avaldists = $alldistances;
    }
}
else {
    $avaldists = $alldistances;
}

?>
<p class = "bebas-neue darktext pagetitle"><span class = "darktext">Provincial Circuit Ranking List</span></p>

<div class = "menuH">
<p class = "bebas-neue darktext text-center medsize smallpadding">Select a Season:</p>
<?php
        $sql2 = "SELECT DISTINCT season FROM comps WHERE series = TRUE ORDER BY season ASC;";
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
                        if ($count2 == 1){
                            $currSeason = $season;
                        }
                        if ($season == $currSeason){
                            $url = "y=&aC=".$currCat."&a=".$currAge."&g=".$currGender."&t=".$currTrack."&d=".implode("m", $currDists);
                            ?>
                            <a class = "bebas-neue whitetext yearbtn-small-selected" href = "series.php?<?php echo $url; ?>">
                                <?php echo ($season-1); ?>-<?php echo $season; ?>
                            </a>
                            <?php 
                        }
                        else {
                            $url = "y=".$season."&aC=".$currCat."&a=".$currAge."&g=".$currGender."&t=".$currTrack."&d=".implode("m", $currDists);
                            ?>
                            <a class = "bebas-neue whitetext yearbtn-small" href = "series.php?<?php echo $url;?>">
                                <?php echo ($season-1); ?>-<?php echo $season; ?>
                            </a>
                            <?php 
                        }
                    }
                }
            }
        ?>
<?php
$rankingcomps = array();
if ($currSeason > 0){
    $sql10 = "SELECT * FROM comps NATURAL JOIN dates WHERE series = TRUE AND season = $currSeason ORDER BY date ASC;";
    $result10 = mysqli_query($conn, $sql10);
    if($result10 == TRUE) {
        $count10 = mysqli_num_rows($result10);
        // Initialize display of Athlete Number 
        $compIndex = 1;
        if($count10 > 0) {
            ?><p class = "arimo smalltext darktext"><b>Eligible Competitions Include:</b></p><?php 
            // For everything in the database, display
            while ($rows10 = mysqli_fetch_assoc($result10)){
                $compID = $rows10['compID'];
                $rankingcomps[] = $compID;
                $compName = $rows10['compName'];
                # PRINT ROW STATEMENTS
                ?><td class = "text-center"><p class = "arimo smalltext darktext"><?php echo $compName; ?> (C<?php echo $compIndex; ?>) <b>Completed</b></p></td><?php
                $compIndex++;
            }
        }
        ?><td class = "text-center"><p class = "arimo smalltext darktext">Debby Fisher's RU Fast (C4)</p></td><?php
        ?><td class = "text-center"><p class = "arimo smalltext darktext">SSA ST Provincial Championships (C5)</p></td><?php
    }
}
?>
</div>

<div class = "ui-container">
    <div class = "options">
        <p class = "selector-label top-selector-label bebas-neue whitetext text-center smallsize">Select <span class = "whitetext">Age Category:</span></p>
        <?php
            foreach ($ageCats as $c){
                if ($c != "Senior"){
                if ($c == $currCat){
                    $url = "y=".$currSeason."&aC=&a=&g=";
                    ?>
                    <a class = "bebas-neue darktext selectorbtn-selected" href = "series.php?<?php echo $url; ?>"><?php echo $c; ?></a>                    <?php 
                }
                else {
                    $url = "y=".$currSeason."&aC=".$c."&a=&g=";
                    ?>
                    <a class = "bebas-neue darktext selectorbtn" href = "series.php?<?php echo $url; ?>"><?php echo $c; ?></a>
                    <?php 
                }
                }
            }
        ?>
        <p class = "selector-label bebas-neue whitetext text-center smallsize">Select <span class = "whitetext">Age:</span></p>
        <?php
            if ($currCat != NULL){
                foreach ($ageSort[$currCat] as $a){
                    if (sizeof($ageSort[$currCat]) == 1 or $currAge == $a){
                        $currAge = $a;
                        $url = "y=".$currSeason."&aC=".$currCat."&a=&g=";
                        ?>
                        <a class = "bebas-neue darktext selectorbtn-selected" href = "series.php?<?php echo $url; ?>"><?php echo $a; ?></a>
                        <?php 
                    }
                    else {
                        $url = "y=".$currSeason."&aC=".$currCat."&a=".$a."&g=";
                        ?>
                        <a class = "bebas-neue darktext selectorbtn" href = "series.php?a=<?php echo $url; ?>"><?php echo $a; ?></a>
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
                if ($currCat == "Active Start"){
                    $currGender = "Mixte";
                    ?>
                    <a class = "bebas-neue darktext selectorbtn-selected" href = "">Mixte</a>
                    <?php 
                }
                else {
                    foreach ($genderSort[$currCat] as $g){
                        if (sizeof($genderSort[$currCat]) == 1 or $currGender == $g){
                            $currGender = $g;
                            ?>
                            <a class = "bebas-neue darktext selectorbtn-selected" href = ""><?php echo $g; ?></a>
                            <?php 
                        }
                        else {
                            $url = "y=".$currSeason."&aC=".$currCat."&a=".$currAge."&g=".$g;
                            ?>
                            <a class = "bebas-neue darktext selectorbtn" href = "series.php?<?php echo $url; ?>"><?php echo $g; ?></a>
                            <?php 
                        }
                    }
                }
            }
            else {
                ?><p class = "arimo darktext text-center padded smallsize">Select an age category to see more</p><?php
            }
        ?>
    </div>
    <div class = "ranking">
    <?php
        $cPtsArray = array();
        $sumArray = array();
        $idArray = array();
        $maxArray = array();
        if ($currGender > 0 and $currAge > 0 AND $currSeason > 0){
            $lettercount = 1;
            foreach ($rankingcomps as $c){
                if ($currGender == "M" or $currGender == "F"){
                    $cPtsArray[] = "(SELECT A.skaterID, 
                    62-2*RANK() OVER (ORDER BY 
                    CASE WHEN points = prev_points THEN prev_pointIndex
                      ELSE pointIndex 
                    END) AS C".$lettercount."
                    FROM 
                        (SELECT *, 
                            LAG(points) OVER (ORDER BY pointIndex) AS prev_points,
                            LAG(pointIndex) OVER (ORDER BY pointIndex) AS prev_pointIndex
                            FROM points WHERE compID = '".$c."'
                        ) AS A 
                        NATURAL JOIN 
                        (SELECT * FROM skaters WHERE gender = '".$currGender."' AND age = '".$currAge."' AND season = '".$currSeason."') AS S
                        JOIN
                        club AS P
                        WHERE P.clubName = S.club AND P.alberta = TRUE) AS ".$letters[$lettercount-1]."
                        ON BASE.skaterID = ".$letters[$lettercount-1].".skaterID";
                    $sumArray[] = "COALESCE(C".$lettercount.",0)";
                    $detsumArray[] = "CASE WHEN C".$lettercount." > 0 THEN C".$lettercount." ELSE 0 END AS C".$lettercount;
                    $lettercount++;
                }
                else {
                    $cPtsArray[] = "(SELECT A.skaterID, 
                    62-2*RANK() OVER (ORDER BY 
                    CASE WHEN points = prev_points THEN prev_pointIndex
                      ELSE pointIndex 
                    END) AS C".$lettercount."
                    FROM 
                        (SELECT *, 
                            LAG(points) OVER (ORDER BY pointIndex) AS prev_points,
                            LAG(pointIndex) OVER (ORDER BY pointIndex) AS prev_pointIndex
                            FROM points WHERE compID = '".$c."'
                        ) AS A 
                        NATURAL JOIN 
                        (SELECT * FROM skaters WHERE age = '".$currAge."' AND season = '".$currSeason."') AS S
                        JOIN
                        club AS P
                        WHERE P.clubName = S.club AND P.alberta = TRUE) AS ".$letters[$lettercount-1]."
                        ON BASE.skaterID = ".$letters[$lettercount-1].".skaterID";
                    $sumArray[] = "COALESCE(C".$lettercount.",0)";
                    $detsumArray[] = "CASE WHEN C".$lettercount." > 0 THEN C".$lettercount." ELSE 0 END AS C".$lettercount;
                    $lettercount++;
                }
            }

            if ($currGender == "M" or $currGender == "F"){
                $cPts = "SELECT BASE.fName, BASE.lName, BASE.skaterID, BASE.club,".implode(",",$detsumArray).",".implode("+",$sumArray)." AS TOTAL
                FROM 
                (SELECT * FROM club) AS CLUB
                JOIN
                (SELECT * FROM skaters WHERE gender = '".$currGender."' AND age = '".$currAge."' AND season = '".$currSeason."') AS BASE 
                ON CLUB.clubName = BASE.club AND CLUB.alberta = TRUE
                LEFT JOIN 
                ".implode(" LEFT JOIN ",$cPtsArray)."
                ORDER BY TOTAL DESC;";
            }
            else {
                $cPts = "SELECT BASE.fName, BASE.lName, BASE.skaterID, BASE.club,".implode(",",$detsumArray).",".implode("+",$sumArray)." AS TOTAL
                FROM 
                (SELECT * FROM club) AS CLUB
                JOIN
                (SELECT * FROM skaters WHERE age = '".$currAge."' AND season = '".$currSeason."') AS BASE 
                ON CLUB.clubName = BASE.club AND CLUB.alberta = TRUE
                LEFT JOIN 
                ".implode(" LEFT JOIN ",$cPtsArray)."
                ORDER BY TOTAL DESC;";
            }

            $result = mysqli_query($conn, $cPts) or die(mysqli_error());
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
                        $c = 1;
                        while ($c < $lettercount){
                            ?>
                            <th class = "row-mid">C<?php echo $c; ?></th>
                            <?php
                            $c++;
                        }
                        while ($c <= 5){
                            ?>
                            <th class = "row-mid">C<?php echo $c; ?></th>
                            <?php
                            $c++;
                        }
                        ?>
                        <th class = "row-right">Combined</th>
                    </tr>
                <?php
                while($rows = mysqli_fetch_assoc($result)){
                    // Store database details in variables. 
                    $skaterID = $rows['skaterID'];
                    $fName = $rows['fName'];
                    $lName = $rows['lName'];
                    $club = $rows['club'];
                    $TOTAL = $rows['TOTAL'];
                    ?>
                    <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?> onclick="window.location='athlete.php?id=<?php echo $skaterID?>';">
                            <td class = "row-left"><?php echo $displayNum; ?></td>
                            <td><?php echo $fName; ?></td>
                            <td><?php echo $lName; ?></td>
                            <td><?php echo $club; ?></td>
                            <?php
                            $c = 1;
                            while ($c < $lettercount){
                                $colname = "C".$c;
                                ?>
                                <td><?php echo $rows[$colname]; ?></td>
                                <?php
                                $c++;
                            }
                            while ($c <= 5){
                                ?>
                                <td>0</td>
                                <?php
                                $c++;
                            }
                            ?>
                            <td class = "row-right"><b><?php echo $TOTAL; ?></b></td>
                    </tr>
                    <?php
                    $displayNum++;
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
        ?><p class = "arimo darktext text-center medsize">Select an age category, age, and gender to begin ranking</p><?php
        }
    ?>
    </div>
</div>

<?php include("footer.php");