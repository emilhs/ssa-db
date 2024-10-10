<?php include('navbar.php');
if (isset($_GET['id'])){
    $skaterID = $_GET["id"]; 
}

if (isset($_POST["updateinfo"]) ) {
    $fName = $_POST["fName"];
    $lName = $_POST["lName"];
    $age = $_POST["age"];
    $ageCat = $_POST["ageCat"];
    $gender = $_POST["gender"];
    $club = $_POST["club"];
    $dob = $_POST["dob"];
    $season = $_POST["season"];

    if ($dob != NULL){
        $sql = "UPDATE skaters SET fName = '$fName', lName = '$lName',age = '$age',
        ageCat = '$ageCat', gender = '$gender',
        club = '$club', dob = '$dob'
        WHERE skaterID = '$skaterID' AND season = '$season';";
    }
    else {
        $sql = "UPDATE skaters SET fName = '$fName', lName = '$lName', age = '$age',
        ageCat = '$ageCat', gender = '$gender',
        club = '$club'
        WHERE skaterID = '$skaterID' AND season = '$season';";
    }
    $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
}

?>
<div class = "menuH">
    <?php
    $sql = "SELECT * FROM skaters WHERE skaterID = '$skaterID' ORDER BY season DESC LIMIT 1;";
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
        } else {
            header('location:'.SITEURL.'viewskaters.php');
        }
    }
    $sql2 = "SELECT * FROM skaters WHERE skaterID = '$skaterID';";
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
                        <th class = "row-mid">Age Category</th>
                        <th class = "row-mid">Gender</th>
                        <th class = "row-mid">Club</th>
                        <th class = "row-mid">Date of Birth</th>
                        <th class = "row-right"></th>
                    </tr>
                <?php
                while($rows2 = mysqli_fetch_assoc($result2)){
                    $fName = $rows2['fName'];
                    $lName = $rows2['lName'];
                    $age = $rows2['age'];
                    $ageCat = $rows2['ageCat'];
                    $gender = $rows2['gender'];
                    $club = $rows2['club'];
                    $dob = $rows2['dob'];
                    $season = $rows2['season'];
                    ?>
                    <form action="" method="post" enctype="multipart/form-data">
                    <tr>
                        <input type = "hidden" value ="<?php echo $season; ?>" name = "season">
                        <td><p class = "text-center bebas-neue filesubmission-selected"><?php echo ($season-1); ?>-<?php echo $season; ?></p></td>
                        <td><input class = "filltable" type = "text" name = "fName" value = "<?php echo $fName; ?>"></input></td>
                        <td><input class = "filltable" type = "text" name = "lName" value = "<?php echo $lName; ?>"></input></td>
                        <td><input class = "filltable" type = "text" name = "age" value = "<?php echo $age; ?>"></input></td>
                        <td><input class = "filltable" type = "text" name = "ageCat" value = "<?php echo $ageCat; ?>"></input></td>
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
                <?php
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
                    <th class = "row-right"></td>
                </tr>
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $time = $rows2['time'];
                $dist = $rows2['dist'];
                $track = $rows2['track'];
                $compName = $rows2['compName'];
                $season = $rows2['season'];
                $date = $rows2['date'];
                $compID = $rows2['compID'];
                $dayID = $rows2['dayID'];
                ?>
                    <form action="" method="post" enctype="multipart/form-data" name = "result">
                        <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>
                        <input type = "hidden" value ="<?php echo $compID; ?>" name = "season">
                            <td class = "row-left">
                                <input class = "subtable" type = "number" name = "mins" step = "1" value = <?php echo round(gmdate("i", $time)); ?>>
                                <input class = "subtable" type = "number" name = "secs" step = "1" value = <?php echo round(gmdate("s", $time)); ?>>
                                <input class = "subtable" type = "number" name = "msecs" step = "1" value = <?php echo end(explode(".", $time)); ?>>
                            </td>
                        <td>
                            <select class = "filltable" name = "track" form = "result">
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
                            <select class = "filltable" name = "distance" form = "result">
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
</div>

<?php include("../footer.php");