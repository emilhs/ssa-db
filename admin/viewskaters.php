<?php include('navbar.php'); 

$fValid = FALSE;
$lValid = FALSE;

if (isset($_POST["newskater"])) {
    $fName = $_POST["fName"];
    $lName = $_POST["lName"];
    $gender = $_POST["gender"];
    $age = $_POST["age"];
    $club = $_POST["club"];
    $dob = $_POST["dob"];
    $dob = date("Ymd", strtotime($dob));
    $season = $_POST["season"];

    $sql = "SELECT * FROM skaters WHERE gender = '$gender' AND fName = '$fName' AND lName = '$lName' AND club = '$club' AND age = '$age' AND season = '$season';";
                            $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
                            $count1 = mysqli_num_rows($result1);
    
                            # There is no exact match
                            if ($count1 == 0){
                                # Check existence in any season, just based on name
                                $sql = "SELECT * FROM skaters WHERE fName = '$fName' AND lName = '$lName';";
                                $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
                                $count2 = mysqli_num_rows($result1);

                                # No appearance in any DB
                                if ($count2 == 0){
                                    $skater = "INSERT INTO skaters SET age = '$age', gender = '$gender', fName = '$fName', lName = '$lName', club = '$club', season = '$season', checkInfo = TRUE;";
                                    $result2 = mysqli_query($conn, $skater) or die(mysqli_error());

                                    # retrieve ID
                                    $getskaterID = "SELECT * from skaters WHERE age = '$age' AND gender = '$gender' AND fName = '$fName' AND lName = '$lName' AND club = '$club' AND season = '$season';";
                                    $result3 = mysqli_query($conn, $getskaterID) or die(mysqli_error());
                                    $count3 = mysqli_num_rows($result3);
                                    if ($count3 == 1){
                                        $rows = mysqli_fetch_assoc($result3);
                                        $skaterID = $rows['skaterID'];
                                    }
                                }
                                # This name appears in the DB
                                else if ($count2 > 0){
                                    # First, does this info appear in the season of interest
                                    $relevantinfo = "SELECT * FROM skaters WHERE fName = '$fName' AND lName = '$lName' AND season = '$season';";
                                    $result0 = mysqli_query($conn, $relevantinfo) or die(mysqli_error());
                                    $count0 = mysqli_num_rows($result0);

                                    # there is a match for season and name
                                    if ($count0 == 1){
                                        $rows = mysqli_fetch_assoc($result0);
                                        # get details
                                        $skaterID = $rows['skaterID'];
                                        # there was some contradiction in details so we flag
                                        $skater = "UPDATE skaters SET checkInfo = TRUE WHERE skaterID = '$skaterID' AND season = '$season';";
                                        $result2 = mysqli_query($conn, $skater) or die(mysqli_error());
                                    }
                                    else {
                                        # get most recent info (info from most recent season) based on fname and lname
                                        $getskaterID = "SELECT skaterID, gender, club, age, MAX(season) AS season FROM skaters WHERE fName = '$fName' AND lName = '$lName' GROUP BY fName, lName ORDER BY season ASC;";
                                        $result3 = mysqli_query($conn, $getskaterID) or die(mysqli_error());
                                        $count3 = mysqli_num_rows($result3);
                                         # there is just one skater, get ID right away
                                        if ($count3 == 1){
                                            # is info from most recent season the same as what is being inserted? 
                                            $rows = mysqli_fetch_assoc($result3);
                                            $skaterID = $rows['skaterID'];
                                            $oldseason = $rows['season'];
                                            $oldclub = $rows['season'];
                                            $oldage = $rows['age'];

                                            $FLAG = FALSE;
                                            # if club or age has changed
                                            if ($club != $oldclub or $age != $oldage){
                                                $FLAG = TRUE;
                                            }
                                            # if new season, add to DB
                                            if ($season != $oldseason){
                                                $skater = "INSERT INTO skaters SET fname = '$fName', lname = '$lName', age = '$age', gender = '$gender', club = '$club', skaterID = '$skaterID', season = '$season', checkInfo = '$FLAG';";
                                                $result2 = mysqli_query($conn, $skater) or die(mysqli_error());
                                            }
                                            # set FLAG to True if Flag is true
                                            else if ($FLAG == TRUE){
                                                $skater = "UPDATE skaters SET checkInfo = '$FLAG' WHERE skaterID = '$skaterID' AND season = '$season';";
                                                $result2 = mysqli_query($conn, $skater) or die(mysqli_error());
                                            }
                                        }
                                        # there are multiple skaters with the same name, choose the one that is most similar
                                        # /* WORK IN PROGRESS -- I DON"T THINK THIS IS A CURRENT ISSUE */
                                        else if ($count3 > 1){
                                            echo "DUPLICATE FOUND - CONTACT EMIL";
                                            echo $fName;
                                            echo $lName;
                                            // echo $fName;
                                            // echo $lName;
                                            // $counter = 1;
                                            // while($rows = mysqli_fetch_assoc($result3)){
                                            //     $currentskaterID = $rows['skaterID'];
                                            //     $currentgender = $rows['skaterID'];
                                            //     $currentclub = $rows['skaterID'];
                                            //     $currentage = $rows['age'];

                                            //     $counter++;
                                            // }
                                        }
                                    }
                                }
                            }
                            # There is an exact match
                            else if ($count1 == 1){
                                $rows = mysqli_fetch_assoc($result1);
                                $skaterID = $rows['skaterID'];
                            }
}

if (isset($_GET['f'])){
    $flet = $_GET["f"]; 
    if (in_array($flet, $letters)){
        $fValid = TRUE;
    }
}
if (isset($_GET['l'])){
    $llet = $_GET["l"]; 
    if (in_array($llet, $letters)){
        $lValid = TRUE;
    }
}

?>

<body>
<p class = "bebas-neue darktext text-center medsize">Add Skater:</p>
            <table class = "darktext searchresult arimo">
                <tr class = "toprow">
                    <th class = "row-left">First Name</th>
                    <th class = "row-mid">Last Name</th>
                    <th class = "row-mid">Age</th>
                    <th class = "row-mid">Gender</th>
                    <th class = "row-mid">Club</th>
                    <th class = "row-mid">DOB</th>
                    <th class = "row-mid">Season</th>
                    <th class = "row-right"></th>
                </tr>    
                <form action="" method="post" enctype="multipart/form-data">
                    <tr>    
                    <td><input class = "filltable-wide" type = "text" name = "fName"></input></td>
                    <td><input class = "filltable-wide" type = "text" name = "lName"></input></td>
                    <td><input class = "filltable-wide" type = "text" name = "age"></input></td>
                    <td><input class = "filltable-wide" type = "text" name = "gender"></input></td>
                    <td><input class = "filltable-wide" type = "text" name = "club"></input></td>
                    <td><input class = "filltable-wide" type = "date" name = "dob"></input></td>
                        <td>
                            <select class = "filltable" name = "season">
                                <?php
                                    $sql2 = "SELECT DISTINCT season FROM comps;";
                                    $result = mysqli_query($conn, $sql2);
                                    if($result == TRUE) {
                                        $count = mysqli_num_rows($result);
                                        if($count > 0){
                                            while($rows = mysqli_fetch_assoc($result)){
                                                $s = $rows["season"];
                                                ?>
                                                <option><?php echo $s; ?></option>
                                                <?php
                                            }
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                        <td class = "row-right"><input class = "filesubmission bebas-neue darktext" type = "submit" value="Add Skater" name="newskater"></input></td>
                    </tr>
                </form>
</table>


<div class = "menuH">
    <p class = "bebas-neue darktext pagetitle">Skater Search</p>
    <p class = "arimo desc darktext ">Only skaters that raced one of the <a class = "intextlink" href = "competitions.php">competitions</a> in the database can be found.</p>
    <p class = "bebas-neue darktext text-center medsize">First Name:</p>
    <div><?php 
        foreach ($letters as $x){ 
                if ($x == $flet){ 
                    ?>
                    <a class = "letterbutton-selected bebas-neue darktext" href="viewskaters.php?l=<?php echo $llet?>"><?php echo $x?></a>
                    <?php 
                }else{ 
                    ?>
                    <a class = "letterbutton bebas-neue darktext" href="viewskaters.php?f=<?php echo $x?>&l=<?php echo $llet;?>"><?php echo $x?></a>
                    <?php 
                }
        } ?>
    </div>
    <p class = "bebas-neue darktext text-center medsize">Last Name:</p>
    <div><?php 
        foreach ($letters as $x){
                if ($x == $llet){ 
                    ?>
                    <a class = "letterbutton-selected bebas-neue darktext" href="viewskaters.php?f=<?php echo $flet?>"><?php echo $x?></a>
                    <?php 
                }else{ 
                    ?>
                    <a class = "letterbutton bebas-neue darktext" href="viewskaters.php?f=<?php echo $flet?>&l=<?php echo $x;?>"><?php echo $x?></a>
                    <?php 
                }
        } ?>
    </div>
    <p class = "bebas-neue darktext text-center medsize">Skaters:</p>
        <?php
        if ($fValid or $lValid){
                if ($fValid and $lValid){
                    $sql = "SELECT fName, lName, skaterID, club, MAX(checkInfo) AS checkInfo FROM skaters WHERE fName LIKE '$flet%' AND lName LIKE '$llet%' GROUP BY skaterID ORDER BY lName, fName;";
                }
                else if ($lValid){
                    $sql = "SELECT fName, lName, skaterID, club, MAX(checkInfo) AS checkInfo FROM skaters WHERE lName LIKE '$llet%' GROUP BY skaterID ORDER BY lName, fName;";
                }
                else if ($fValid){
                    $sql = "SELECT fName, lName, skaterID, club, MAX(checkInfo) AS checkInfo FROM skaters WHERE fName LIKE '$flet%' GROUP BY skaterID ORDER BY lName, fName;";
                }
                $result = mysqli_query($conn, $sql) or die(mysqli_error());
                $count = mysqli_num_rows($result);
                $displayNum = 1;
                if($count > 0) {
                            ?>
                            <table class = "darktext searchresult arimo">
                                <tr class = "toprow">
                                    <th class = "row-left">First Name</th>
                                    <th class = "row-mid">Last Name</th>
                                    <th class = "row-mid">Club</th>
                                    <th class = "row-right"></th>
                                </tr>    
                            <?php
                            // For everything in the database, display
                            while($rows = mysqli_fetch_assoc($result)){
                                // Store database details in variables. 
                                $fName = $rows['fName'];
                                $lName = $rows['lName'];
                                $club = $rows['club'];
                                $skaterID = $rows['skaterID'];
                                $FLAG = $rows['checkInfo'];
                                ?>
                                <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?> onclick="window.location='editskater.php?id=<?php echo $skaterID?>';">
                                    <td class = "row-left"><?php echo $fName; ?></td>
                                    <td><?php echo $lName; ?></td>
                                    <td><?php echo $club; ?></td>
                                    <?php
                                    if ($FLAG != NULL and $FLAG == 1){
                                        ?>
                                        <td class = "row-right"><p class = "dangertext">CHECK INFO</p></td>
                                        <?php
                                    }
                                    else{ 
                                        ?>
                                        <td class = "row-right"></td>
                                        <?php
                                    }
                                    ?>
                                </tr>
                            <?php
                            $displayNum++;
                            }?>
                    </table></div><?php
                }
                else{?>
                    <p class = "arimo darktext text-center medsize">No skaters found for the selected letter(s)</p>
                <?php }
        } 
        else {
        ?>
    <p class = "arimo darktext text-center medsize">Search for a skater by their first name or last name</p>
    <?php
        }
        ?>
</div>
<?php include('../footer.php');