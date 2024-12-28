<?php include('navbar.php');

if (isset($_POST["addresult"]) ) {
    $compID = $_POST["compID"];
    $skaterID = $_POST["skaterID"];

    echo $skaterID;
    echo $compID;

    $mins = $_POST["mins"];
    $secs = $_POST["secs"];
    $msecs = $_POST["msecs"];
    $dist = $_POST["dist"];
    $disc = $_POST["disc"];
    $track = $_POST["track"];

    $secs = ($mins*60)+$secs;
    $fulltime = $secs.".".$msecs;
    $fulltime = $fulltime * 1000;

    echo $fulltime;

    $sql = "INSERT INTO results SET skaterID = '$skaterID', compID = '$compID', dayID = '1', time = '$fulltime', dist = '$dist', disc = '$disc', track = '$track';";
    
    echo $sql;

    $result1 = mysqli_query($conn, $sql) or die(mysqli_error());

    $_POST["addresult"] = NULL;
}

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

if (isset($_POST["removeresult"]) ) {
    $raceID = $_POST["raceID"];

    $sql = "DELETE FROM results WHERE raceID = '$raceID';";
    $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
}

if (isset($_POST["updaterace"]) ) {
    $compID = $_POST["compID"];

    $track = $_POST["track"];
    $dist = $_POST["dist"];

    $oldtrack = $_POST["oldtrack"];
    $olddist = $_POST["olddist"];

    $sql00 = "UPDATE results SET dist = '$dist', track = '$track'
    WHERE track = '$oldtrack' AND dist = '$olddist' AND compID = '$compID';";
    
    $result00 = mysqli_query($conn, $sql00) or die(mysqli_error());
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
                    $currSeason = $rows['season'];
                    ?>
                    <p><?php echo $compName; ?></p>
                    <?php 
                }
            }
        }
    ?>
    <p class = "bebas-neue darktext padded text-center medsize">Races:</p>
    <?php
    $sql2 = "SELECT DISTINCT track, dist FROM results WHERE compID = '$currComp';";
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
                    <th class = "row-mid">Distance</th>
                    <th class = "row-mid">Track</th>
                    <th class = "row-right"></th>
                </tr>    
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $track = $rows2['track'];
                $dist = $rows2['dist'];
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>    
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
                        <input type = "hidden" name = "oldtrack" value = <?php echo $track; ?>>
                        <input type = "hidden" name = "olddist" value = <?php echo $dist; ?>>
                        <input type = "hidden" name = "compID" value = <?php echo $currComp; ?>>
                        <td class = "row-right"><input class = "filesubmission bebas-neue darktext" type = "submit" value="Update Race" name="updaterace"></input></td>
                    </tr>
                </form>
                <?php
            }
            ?>
            </table>
            <?php
        }
        else {
            echo "Nothing to see yet";
        }
    }
    ?>

    <?php
        $sqlnames = "SELECT DISTINCT skaterID, fName, lName FROM skaters WHERE season = '$currSeason' ORDER BY lName, fName ASC;";
        $resultnames = mysqli_query($conn, $sqlnames);
        $countnames = mysqli_num_rows($resultnames);
    ?>

    <p class = "bebas-neue darktext padded text-center medsize">Results (Latest to Earliest):</p>
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
                <form action="" method="post" enctype="multipart/form-data">
                    <tr>    
                        <td>
                            <select class = "filltable" name = "skaterID">
                                <?php
                                    if($countnames > 0){
                                        while($rowsnames = mysqli_fetch_assoc($resultnames)){
                                            $skaterID = $rowsnames["skaterID"];
                                            $fName = $rowsnames["fName"];
                                            $lName = $rowsnames["lName"];
                                            ?>
                                            <option value="<?php echo $skaterID; ?>"><?php echo $fName." ".$lName; ?></option>
                                            <?php
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                        <td>
                            <select class = "filltable" name = "dist">
                                <?php
                                    foreach ($alldistances as $d){
                                        ?>
                                        <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
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
                                        <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </td>
                        <td>
                                <input class = "subtable" type = "number" name = "mins" step = "1">
                                <input class = "subtable" type = "number" name = "secs" step = "1">
                                <input class = "subtable" type = "number" name = "msecs" step = "1">
                        </td>
                        <td>
                            <select class = "filltable" name = "disc">
                                <?php
                                    foreach (array_keys($discSort) as $d){
                                        ?>
                                        <option value="<?php echo $d; ?>"><?php echo $discSort[$d]; ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </td>
                        <input type = "hidden" name = "compID" value = <?php echo $currComp; ?>>
                        <td class = "row-right"><input class = "filesubmission bebas-neue darktext" type = "submit" value="Add Result" name="addresult"></input></td>
                    </tr>
                </form>
    </table>

    <?php
    $sql2 = "SELECT * FROM results NATURAL JOIN skaters WHERE compID = '$currComp' AND season = '$currSeason' ORDER BY raceID ASC;";
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
                    <th></th>
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
                    <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>    
                    <form action="" method="post" enctype="multipart/form-data">
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
                    </form>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type = "hidden" name = "raceID" value = <?php echo $raceID; ?>>
                        <td class = "row-right"><input class = "filesubmission-opp bebas-neue darktext" type = "submit" value="Remove Result" name="removeresult"></input></td>
                    </form>
                    </tr>
                <?php
            }
            ?>
            </table>
            <?php
        }
        else {
            echo "Nothing to see yet";
        }
    }
?>
</div>
<?php

include("../footer.php");