<?php include('navbar.php'); 
if (isset($_GET['id'])){
    $skaterID = $_GET["id"]; 
}

$sql = "SELECT fName, lName, age, agecat, club, gender, season FROM skaters WHERE skaterID = '$skaterID';";
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
    } else {
        header('location:'.SITEURL.'search.php');
    }
}
?>
<div class = "title">
<p class = "bebas-neue darktext pagetitle"><span class = "darktext"><?php echo $fName?></span> <span class = "bluetext"><?php echo $lName?></span></p>
<p class = "text-center arimo darktext medsize"><span class = "darktext">Last Active: <?php echo ($season-1)?>-<?php echo ($season)?> Age: <?php echo $age?> Gender: <?php echo $gender?> Club: <?php echo $club; ?></span></p>
</div>
<p class = "bebas-neue darktext text-center medsize">Personal Bests:</p>
<?php
$sql2 = "SELECT dist, track, compName, season, date, MIN(time) AS best
            FROM results NATURAL JOIN dates NATURAL JOIN comps
            WHERE skaterID = '$skaterID' AND time > 0 AND time < 600
            GROUP BY dist
            ORDER BY dist ASC;";
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
            <table class = "darktext searchresult arimo">
                <tr class = "toprow">
                    <th class = "row-left">Time</td>
                    <th class = "row-mid">Race</td>
                    <th class = "row-mid">Competition</td>
                    <th class = "row-mid">Season</td>
                    <th class = "row-right">Date(s)</td>
                </tr>
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $time = $rows2['best'];
                $dist = $rows2['dist'];
                $track = $rows2['track'];
                $comp = $rows2['compName'];
                $season = $rows2['season'];
                $date = $rows2['date'];
                ?>
                <tr>
                    <td><?php echo gmdate("i:s", $time); ?>.<?php echo end(explode(".", $time));?></td>
                    <td><?php echo $dist; ?>m (<?php echo $track;?>)</td>
                    <td><?php echo $comp; ?></td>
                    <td><?php echo ($season-1)?>-<?php echo $season; ?></td>
                    <td><?php echo $date; ?></td>
                </tr>
                <?php
            }
            ?></table><?php
        }
    }

?>
<p class = "bebas-neue darktext text-center medsize">Results:</p>
<?php
$sql2 = "SELECT *
            FROM results NATURAL JOIN dates NATURAL JOIN comps
            WHERE skaterID = '$skaterID'
            ORDER BY date DESC, track, dist ASC;";
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
            <table class = "darktext searchresult arimo">
                <tr class = "toprow">
                    <th class = "row-left">Time</td>
                    <th class = "row-mid">Race</td>
                    <th class = "row-mid">Competition</td>
                    <th class = "row-mid">Season</td>
                    <th class = "row-right">Date(s)</td>
                </tr>
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $time = $rows2['time'];
                $dist = $rows2['dist'];
                $track = $rows2['track'];
                $compName = $rows2['compName'];
                $season = $rows2['season'];
                $date = $rows2['date'];
                ?>
                <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>
                    <?php
                        if ($time == 0 or $time == NULL or $time > 600){ ?>
                        <td class = "row-left"><span class = "dangertext">NO TIME</span></td>
                        <?php
                    }
                    else { 
                        ?>
                        <td class = "row-left"><?php echo gmdate("i:s", $time); ?>.<?php echo end(explode(".", $time));?></td>
                        <?php
                    } ?>
                    <td><?php echo $dist; ?>m (<?php echo $track;?>)</td>
                    <td><?php echo $compName; ?></td>
                    <td><?php echo ($season-1)?>-<?php echo $season; ?></td>
                    <td class = "row-right"><?php echo $date; ?></td>
                </tr>
                <?php
                $displayNum++;
            }
            ?></table><?php
        }
    }
?>
<br>
<!-- <a href = "search.php?f=<#?php echo $fName[0]; ?>&l=<#?php echo $lName[0]; ?>">back to search</a> -->
<?php include('footer.php');