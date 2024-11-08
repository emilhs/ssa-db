<?php include('navbar.php');

if (isset($_POST["updateresult"]) ) {
    $compID = $_POST["compID"];
    $raceID = $_POST["raceID"];

    $mins = $_POST["mins"];
    $secs = $_POST["secs"];
    $msecs = $_POST["msecs"];
    $dist = $_POST["dist"];
    $disc = $_POST["disc"];
    $track = $_POST["track"];

    $secs = ($mins*60)+$secs;
    $fulltime = $secs.".".$msecs;
    $fulltime = $fulltime * 1000;

    $sql = "UPDATE results SET time = '$fulltime', dist = '$dist', disc = '$disc', track = '$track'
    WHERE raceID = '$raceID';";
    
    $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
}

if (isset($_GET['comp'])){
    $currComp = $_GET["comp"]; 
}
?>

<div class = "menuH">
    <?php
        $sql = "SELECT * FROM comps WHERE compID = '$currComp';";
        #$sql = "SELECT fName, lName, country FROM athletes WHERE athleteID = '$athleteID';";
        // Executing the sql query
        $result = mysqli_query($conn, $sql);
        // Verify that SQL Query is executed or not
        if($result == TRUE) {
            ?><p class = "bebas-neue darktext padded text-center medsize">Selected Competition:</p><?php
            // Count the number of rows which will be a way to verify if there is data in the database
            $count = mysqli_num_rows($result);
            // Initialize display of Athlete Number 
            if ($count > 0){
                while($rows = mysqli_fetch_assoc($result)){
                    $compName = $rows['compName'];
                    ?>
                    <p><?php echo $compName; ?></p>
                    <?php 
                }
            }
        }
    ?>
    <p class = "bebas-neue darktext padded text-center medsize">Results (Latest to Earliest):</p>
    <?php
    $sql2 = "SELECT * FROM results NATURAL JOIN skaters WHERE compID = '$currComp' ORDER BY raceID ASC;";
    #$sql = "SELECT fName, lName, country FROM athletes WHERE athleteID = '$athleteID';";
    // Executing the sql query
    $result2 = mysqli_query($conn, $sql2);
    // Verify that SQL Query is executed or not
    if($result2 == TRUE) {
        // Count the number of rows which will be a way to verify if there is data in the database
        $count2 = mysqli_num_rows($result2);
        // Initialize display of Athlete Number 
        if($count2 > 0){
            $displayNum = 1;
            ?>
            <table>
                <tr class = "toprow">
                    <th class = "row-left">Name</th>
                    <th class = "row-mid">Distance</th>
                    <th class = "row-mid">Track</th>
                    <th class = "row-mid">Time</th>
                    <th></th>
                    <th>Discipline</th>
                    <th class = "row-right"></th>
                </tr>    
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $compID = $rows2['compID'];
                $raceID = $rows2['raceID'];
                $skaterID = $rows2['skaterID'];
                $fName = $rows2['fName'];
                $lName = $rows2['lName'];
                $club = $rows2['club'];
                $time = $rows2['time']/1000;
                $track = $rows2['track'];
                $dist = $rows2['dist'];
                $disc = $rows2['disc'];
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>    
                        <td><?php echo $fName; ?> <?php echo $lName; ?> (<?php echo $club; ?>)</td>
                        <td>
                            <select class = "filltable" name = "dist">
                                <?php
                                    foreach ($alldistances as $d){
                                        ?>
                                        <option <?php if ($d == $dist){ ?> selected <?php } ?>value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </td>
                        <td>
                            <select class = "filltable" name = "track">
                                <?php
                                    foreach ($Alltracks as $t){
                                        ?>
                                        <option <?php if ($t == $track){ ?> selected <?php } ?>value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </td>
                        <td>
                                <input class = "subtable" type = "number" name = "mins" step = "1" value = <?php echo round(gmdate("i", $time)); ?>>
                                <input class = "subtable" type = "number" name = "secs" step = "1" value = <?php echo round(gmdate("s", $time)); ?>>
                                <input class = "subtable" type = "number" name = "msecs" step = "1" value = <?php echo end(explode(".", $time)); ?>>
                        </td>
                        <td>
                            <select class = "filltable" name = "disc">
                                <?php
                                    foreach (array_keys($discSort) as $d){
                                        ?>
                                        <option <?php if ($d == $disc){ ?> selected <?php } ?>value="<?php echo $d; ?>"><?php echo $discSort[$d]; ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </td>
                        <input type = "hidden" name = "raceID" value = <?php echo $raceID; ?>>
                        <td class = "row-right"><input class = "filesubmission bebas-neue darktext" type = "submit" value="Update Result" name="updateresult"></input></td>
                    </tr>
                </form>
                <?php
            }
            ?>
            </table>
            <?php
        }
    }
?>
</div>
<?php

include("../footer.php");