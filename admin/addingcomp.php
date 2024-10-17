<?php include('navbar.php');

if (isset($_POST["upload"]) ) {
    $location = $_POST["location"];
    $compDisc = $_POST["disc"];
    $season = $_POST["season"];
    $compName = $_POST["compName"];
    $skaters = $_POST["skaters"];
    $dates = $_POST["dates"];

    // echo var_dump($skaters);
    #organize SKATERS string into array
    $unorganized = explode("!~!", $skaters);
    
    $skaters = array();
    $i = 1;
    while ($i < sizeof($unorganized)){
        $newpart = array_slice($unorganized, $i, 10);
        $skaters[] = $newpart;
        $i = $i + 10;
    }

    $sql = "SELECT * FROM comps WHERE compName = '$compName' AND season = '$season' AND location = '$location';";
    $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    $count1 = mysqli_num_rows($result1);

    if ($count1 > 0) {
        echo "Identical competition already exists, please rename and try again";
    }
    else if ($count1 == 0){
        $compsql = "INSERT INTO comps SET compName = '$compName', season = '$season', location = '$location';";
        // Executing the query
        $result2 = mysqli_query($conn, $compsql) or die(mysqli_error());

        $getcompID = "SELECT * from comps WHERE compName = '$compName' AND season = '$season' AND location = '$location';";
        $result3 = mysqli_query($conn, $getcompID) or die(mysqli_error());
        $count3 = mysqli_num_rows($result3);
        if ($count3 == 1){
            while($rows = mysqli_fetch_assoc($result3)){
                $compID = $rows['compID'];

                # INSERT DAYS
                $daycount = 1;
                foreach ($dates as $d){
                    $daysql = "INSERT INTO dates SET compID = '$compID', dayID = '$daycount', date = '$d';";
                    // Executing the query
                    $result = mysqli_query($conn, $daysql) or die(mysqli_error());
                    $daycount++;
                }

                $oldskater = "";
                $olddate = "";
                foreach ($skaters as $s){
                    $splitinfo = $s;
                    $date = $splitinfo[0];
                    $age = $splitinfo[1];
                    $gender = $splitinfo[2];
                    $fName = $splitinfo[3];
                    $lName = $splitinfo[4];
                    $club = $splitinfo[5];
                    $dist = $splitinfo[6];
                    $track = $splitinfo[7];
                    $time = $splitinfo[8];
                    $rVal = $splitinfo[9];

                    if ($time == "NULL"){
                         $time = 0;
                    }

                    $getdate = "SELECT dayID FROM dates WHERE compID = '$compID' AND date = '$date';";
                    $result = mysqli_query($conn, $getdate) or die(mysqli_error());
                    $count = mysqli_num_rows($result);
                    if ($count == 1){
                        $rows = mysqli_fetch_assoc($result);
                        $dayID = $rows['dayID'];
                        $newskater = array($age, $gender, $fName, $lName, $club);
                        if ($newskater != $oldskater){
                            $oldskater = $newskater;
                            # Check exact match in skater DB
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
                        if ($skaterID > 0){
                            $resultsql = "INSERT INTO results SET compID = '$compID', dayID = '$dayID', skaterID = '$skaterID', dist = '$dist', track = '$track', time = '$time', disc = '$rVal';";
                            $result = mysqli_query($conn, $resultsql) or die(mysqli_error());
                        }
                        else {
                            echo $fName;
                            echo $lName;
                        }
                    }
                }

                ?>
                    <div class = "menuH">
                    <p class = "bebas-neue darktext pagetitle">Success!</p>
                    <p class = "arimo darktext medsize">Uploaded data can now be viewed by users and edited by admins.</p>
                    <div class = "buttons text-center">
                    <a class = "bebas-neue filesubmission-long darktext" href = "submit.php">Add Another Competition</a>
                    <br>
                    <a class = "bebas-neue filesubmission-long darktext" href = "viewskaters.php">Edit Skaters and Times</a>
                    <br>
                    <a class = "bebas-neue filesubmission-long darktext" href = "viewcomps.php">Edit Competitions</a>
                    </div>
                    </div>
                <?php
                # ROWS OF DATA

            }
        }
        else { 
            ?>
            <p class = "arimo darktext medsize">There was an error - try again!</p>
            <div class = "buttons text-center">
                    <a class = "bebas-neue filesubmission-long darktext" href = "submit.php">Add Another Competition</a>
            </div>
            <?php
        }
    }
}
else{
    echo "error";
}

include("../fixedfooter.php");