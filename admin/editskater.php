<?php include('navbar.php');
if (isset($_GET['id'])){
    $skaterID = $_GET["id"]; 
}

if (isset($_POST["updateinfo"]) ) {
    $fName = $_POST["fName"];
    $lName = $_POST["lName"];
    $age = $_POST["age"];
    $gender = strtoupper($_POST["gender"]);
    $club = $_POST["club"];
    $dob = $_POST["dob"];
    $season = $_POST["season"];

    $sqlspecific = "SELECT MAX(dob) AS dob FROM skaters WHERE skaterID = '$skaterID' AND season = '$season';";
    $resultskater = mysqli_query($conn, $sqlspecific) or die(mysqli_error());
    if($resultskater == TRUE) {
        $countseason = mysqli_num_rows($resultskater);
        if($countseason == 1){
            $rows = mysqli_fetch_assoc($resultskater);
            $mydob = $rows['dob'];
        }
    }

    // echo "CHANGE";
    // echo $mydob;

    // echo "TO";
    // echo empty($dob);

    # NEW BIRTHDAY
    if ($dob != $mydob){
        if (empty($dob)){
            $sql2 = "UPDATE skaters SET dob = NULL WHERE skaterID = '$skaterID';";
            $result2 = mysqli_query($conn, $sql2) or die(mysqli_error());
        }
        else{
            $sql2 = "UPDATE skaters SET dob = '$dob' WHERE skaterID = '$skaterID';";
            $result2 = mysqli_query($conn, $sql2) or die(mysqli_error());
        
            # GET SEASONS
            $sqlseason = "SELECT season FROM skaters WHERE skaterID = '$skaterID' ORDER BY season ASC;";
            $resultseason = mysqli_query($conn, $sqlseason) or die(mysqli_error());
            if($resultseason == TRUE) {
                $countseason = mysqli_num_rows($resultseason);
                if($countseason > 0){
                    while($rows2 = mysqli_fetch_assoc($resultseason)){
                        # SEASON
                        $myseason = $rows2['season'];
                        $age = get_agecat($dob, $myseason);
                        # IS THIS THE SEASON OF INTEREST?
                        if ($myseason == $season){
                            # EDIT SPECIFICS
                            $sql = "UPDATE skaters SET fName = '$fName', lName = '$lName',age = '$age', gender = '$gender', club = '$club', dob = '$dob'
                            WHERE skaterID = '$skaterID' AND season = '$myseason';";
                            $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
                        }
                        else{
                            # JUST UPDATE AGE
                            $sql = "UPDATE skaters SET age = '$age', dob = '$dob'
                            WHERE skaterID = '$skaterID' AND season = '$myseason';";
                            $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
                        }
                    }
                }
            }
        }
    } 
    else {
        $sql2 = "UPDATE skaters SET gender = '$gender' WHERE skaterID = '$skaterID';";
        $result2 = mysqli_query($conn, $sql2) or die(mysqli_error());

        $sql = "UPDATE skaters SET fName = '$fName', lName = '$lName', age = '$age', gender = '$gender',
        club = '$club'
        WHERE skaterID = '$skaterID' AND season = '$season';";
        $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    }
}

if (isset($_POST["updatetime"]) ) {
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

if (isset($_POST["clearflag"]) ) {
    $skaterID = $_POST["skaterID"];

    $sql = "UPDATE skaters SET checkInfo = FALSE
    WHERE skaterID = '$skaterID';";

    $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
}

if (isset($_POST["inherit"])){
    $childSkater = $_POST["child"];

    echo "Absorb ".$childSkater." into ".$skaterID;

    $sqlR = "UPDATE results SET skaterID = '$skaterID' WHERE skaterID = '$childSkater';";
    $resultR = mysqli_query($conn, $sqlR) or die(mysqli_error());

    $sqlP = "UPDATE points SET skaterID = '$skaterID' WHERE skaterID = '$childSkater';";
    $resultP = mysqli_query($conn, $sqlP) or die(mysqli_error());

    $sqlN = "DELETE FROM skaters WHERE skaterID = '$childSkater';";
    $resultN = mysqli_query($conn, $sqlN) or die(mysqli_error());

    echo "success";
}
?>

<div class = "menuH">
    <?php
    $sql = "SELECT *, MIN(season) as MIN FROM skaters WHERE skaterID = '$skaterID' ORDER BY season DESC LIMIT 1;";
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
            ?>
            
            <div class = "title">
            <p class = "bebas-neue darktext pagetitle"><span class = "darktext"><?php echo $fName?></span> <span class = "bluetext"><?php echo $lName?></span></p>
            </div>

            <?php
        }
    }
    ?>

    <?php
        $sql = "SELECT DISTINCT fName, lName, skaterID FROM skaters WHERE skaterID != '$skaterID';";
        #$sql = "SELECT fName, lName, country FROM athletes WHERE athleteID = '$athleteID';";
        // Executing the sql query
        $result = mysqli_query($conn, $sql);
        // Verify that SQL Query is executed or not
        if($result == TRUE) {
            ?><p class = "bebas-neue darktext padded text-center medsize">Inherit:</p><?php
            // Count the number of rows which will be a way to verify if there is data in the database
            $count = mysqli_num_rows($result);
            // Initialize display of Athlete Number 
            ?>
            <form action="" method="post" enctype="multipart/form-data">
            <select class = "filltable" name = "child">
            <?php
            if ($count > 0){    
                while($rows = mysqli_fetch_assoc($result)){
                    $oppskaterID = $rows['skaterID'];
                    $oppfName = $rows['fName'];
                    $opplName = $rows['lName'];
                    ?>
                    <option value="<?php echo $oppskaterID; ?>"><?php echo $oppfName." ".$opplName; ?></option>
                    <?php 
                }
            }
            ?>
            </select>
            <input class = "filesubmission bebas-neue darktext" type = "submit" value="Inherit Skater" name="inherit"></input></td>
            </form>
            <?php
        }
    ?>

    <?php
    $sql2 = "SELECT * FROM skaters WHERE skaterID = '$skaterID' ORDER BY season DESC;";
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
                <p class = "bebas-neue darktext text-center medsize">Edit Info by Season:</p>
                <table class = "darktext searchresult-nogap arimo">
                    <tr class = "toprow">
                        <th class = "row-left">Season</th>
                        <th class = "row-mid">Name</th>
                        <th class = "row-mid">Surname</th>
                        <th class = "row-mid">Age</th>
                        <th class = "row-mid">Gender</th>
                        <th class = "row-mid">Club</th>
                        <th class = "row-mid">Date of Birth</th>
                        <th class = "row-right"></th>
                    </tr>
                <?php
                $FLAG = FALSE;
                while($rows2 = mysqli_fetch_assoc($result2)){
                    $fName = $rows2['fName'];
                    $lName = $rows2['lName'];
                    $age = $rows2['age'];
                    $gender = $rows2['gender'];
                    $club = $rows2['club'];
                    $dob = $rows2['dob'];
                    $season = $rows2['season'];
                    $checkInfo = $rows2['checkInfo'];
                    if ($checkInfo){
                        $FLAG = TRUE;
                    }
                    ?>
                    <form action="" method="post" enctype="multipart/form-data">
                    <tr>
                        <input type = "hidden" value ="<?php echo $season; ?>" name = "season">
                        <td><p class = "text-center bebas-neue filesubmission-selected"><?php echo ($season-1); ?>-<?php echo $season; ?></p></td>
                        <td><input class = "filltable" type = "text" name = "fName" value = "<?php echo $fName; ?>"></input></td>
                        <td><input class = "filltable" type = "text" name = "lName" value = "<?php echo $lName; ?>"></input></td>
                        <td><input class = "filltable" type = "text" name = "age" value = "<?php echo $age; ?>"></input></td>
                        <td><input class = "filltable" type = "text" name = "gender" value = "<?php echo $gender; ?>"></input></td>
                        <td><input class = "filltable" type = "text" name = "club" value = "<?php echo $club; ?>"></input></td>
                        <td><input class = "filltable" type = "date" name = "dob" value = "<?php echo $dob; ?>"></input></td>
                        <td><input class = "filesubmission bebas-neue darktext" type = "submit" value="Update Info" name="updateinfo"></input></td>
                    </tr>
                    </form>
                    <?php
                }
                ?>
                </table>
                <br>
                <?php
                if ($FLAG){ 
                    ?>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type = "hidden" name = "skaterID" value = <?php echo $skaterID; ?>>
                        <input class = "filesubmission-opp bebas-neue danger" type = "submit" value="Approve Info" name="clearflag"></input>
                    </form>
                    <?php
                }
            }
        }
    ?>
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
            <p class = "bebas-neue darktext text-center medsize">Edit Results for <?php echo $fName; ?> <?php echo $lName; ?>:</p>
            <table class = "darktext searchresult-nogap arimo">
                <tr class = "toprow">
                    <th class = "row-left">Time</td>
                    <th class = "row-mid">Track</td>
                    <th class = "row-mid">Distance</td>
                    <th class = "row-mid">Competition</td>
                    <th class = "row-mid">Season</td>
                    <th class = "row-mid">Date(s)</td>
                    <th class = "row-mid">Discipline</td>
                    <th class = "row-right"></td>
                </tr>
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $time = $rows2['time']/1000;
                $dist = $rows2['dist'];
                $track = $rows2['track'];
                $compName = $rows2['compName'];
                $season = $rows2['season'];
                $date = $rows2['date'];
                $compID = $rows2['compID'];
                $dayID = $rows2['dayID'];
                $disc = $rows2['disc'];
                $raceID = $rows2['raceID'];
                ?>
                    <form action="" method="post" enctype="multipart/form-data" name = "result">
                        <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>
                        <input type = "hidden" value ="<?php echo $raceID; ?>" name = "raceID">
                            <td class = "row-left">
                            <?php            
                                if ($time == round($time, 0)){
                                    ?>
                                    <input class = "subtable" type = "number" name = "mins" step = "1" value = <?php echo round(gmdate("i", $time)); ?>>
                                    <input class = "subtable" type = "number" name = "secs" step = "1" value = <?php echo round(gmdate("s", $time)); ?>>
                                    <input class = "subtable" type = "number" name = "msecs" step = "1" value = "00">
                                    <?php
                                }
                                else{
                                    ?>
                                    <input class = "subtable" type = "number" name = "mins" step = "1" value = <?php echo round(gmdate("i", $time)); ?>>
                                    <input class = "subtable" type = "number" name = "secs" step = "1" value = <?php echo round(gmdate("s", $time)); ?>>
                                    <input class = "subtable" type = "number" name = "msecs" step = "1" value = <?php echo end(explode(".", $time)); ?>>
                                    <?php
                                }        
                                ?>
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
                            <td><?php echo $compName; ?></td>
                            <td><?php echo ($season-1)?>-<?php echo $season; ?></td>
                            <td><?php echo $date; ?></td>
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
                            <td class = "row-right"><input class = "filesubmission bebas-neue darktext" type = "submit" value="Update Result" name="updatetime"></input></td>
                        </tr>
                    </form>
                </tr>
                <?php
                $displayNum++;
            }
            ?></table><?php
        }
    }
?>
<br>
<a class = "btn" href = "viewskaters.php?f=<?php echo $fName[0]; ?>&l=<?php echo $lName[0]; ?>">Back to Search</a>
</div>

<?php include("../footer.php");