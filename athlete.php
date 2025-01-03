<?php include('navbar.php'); 
if (isset($_GET['id'])){
    $skaterID = $_GET["id"]; 
}

$sql = "SELECT fName, lName, age, club, gender, season FROM skaters WHERE skaterID = '$skaterID' ORDER BY season DESC LIMIT 1";

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
        header('location: search.php');
    }
}
?>
<div class = "title">
<p class = "bebas-neue darktext pagetitle"><span class = "darktext"><?php echo $fName?></span> <span class = "bluetext"><?php echo $lName?></span></p>
<table class = "darktext searchresult arimo">
    <tr class = "toprow">
        <th class = "row-left">Last Active</th>
        <th class = "row-mid">Age</th>
        <th class = "row-mid">Gender</th>
        <th class = "row-right">Club</th>
    </tr>
    <tr>
        <td class = "row-left"><?php echo ($season-1)?>-<?php echo ($season)?></td>
        <td><?php echo $age?></td>
        <td><?php echo $gender?></td>
        <td class = "row-right"><?php echo $club; ?></td>
    </tr>
</table>
<p class = "arimo darktext text-center tinysize">Note: skater information (age, club) is not updated until a race is done.</p>
</div>
<div class = "text-center padded">
<span class = "bebas-neue darktext text-center medsize">Personal Bests:</span>
</div>

<?php 
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
            <div class = "text-center lowpad">
            <?php
            while($rowsTrack = mysqli_fetch_assoc($resultTrack)){
                $track = $rowsTrack['track'];
                if (in_array($track, $Alltracks)){
                    $mytracks[] = $track;
                    ?>
                    <button onclick="showTable('<?php echo $track; ?>')" id = "btn<?php echo $track; ?>" class = "bebas-neue darktext thinbtn <?php if($numer == 1){?> activebtn <?php } ?>"><?php echo $track; ?></button>
                    <?php
                    $numer++;
                }
            }
            ?>
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
                $time = $rows2['best']/1000;
                $dist = $rows2['dist'];
                $track = $rows2['track'];
                $comp = $rows2['compName'];
                $season = $rows2['season'];
                $date = $rows2['date'];
                ?>
                <tr>
                    <?php
                    if ($time == round($time, 0)){
                        ?><td><?php echo gmdate("i:s", $time); ?>.00</td><?php
                    }
                    else{
                        ?><td><?php echo gmdate("i:s", $time); ?>.<?php echo end(explode(".", $time));?></td><?php
                    }                    
                    ?>
                    <td><?php echo $dist; ?>m (<?php echo $track;?>)</td>
                    <td><?php echo $comp; ?></td>
                    <td><?php echo ($season-1)?>-<?php echo $season; ?></td>
                    <td><?php echo $date; ?></td>
                </tr>
                <?php
            }
            ?>
            </table>
        </div>
        <?php
        }
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
                $time = $rows2['time']/1000;
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
                        if ($time == round($time, 0)){
                            ?><td><?php echo gmdate("i:s", $time); ?>.00</td><?php
                        }
                        else{
                            ?><td><?php echo gmdate("i:s", $time); ?>.<?php echo end(explode(".", $time));?></td><?php
                        }                    
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

<script>
    function showTable(id) {
        let tableid = "table".concat(id);
        const tables = document.querySelectorAll('.pb-table');
        tables.forEach(table => table.classList.remove('active'));
        document.getElementById(tableid).classList.add('active');

        let buttonid = "btn".concat(id);
        const buttons = document.querySelectorAll('.thinbtn');
        buttons.forEach(button => button.classList.remove('activebtn'));
        document.getElementById(buttonid).classList.add('activebtn');
    }
</script>

<?php include('footer.php'); ?>
