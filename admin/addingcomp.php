<?php include('navbar.php');

if (isset($_POST["upload"]) ) {
    $location = $_POST["location"];
    $season = $_POST["season"];
    $compName = $_POST["compName"];
    $disc = $_POST["disc"];
    $skaters = $_POST["skaters"];
    $dates = $_POST["dates"];
    // echo var_dump($skaters);

    if (strtoupper($disc) == "SHORT TRACK"){
        $disc = "ST";
    }

    #organize SKATERS string into array
    $unorganized = explode("!~!", $skaters);
    
    $skaters = array();
    $i = 1;
    while ($i < sizeof($unorganized)){
        $newpart = array_slice($unorganized, $i, 10);
        $skaters[] = $newpart;
        $i = $i + 10;
    }

    $sql = "SELECT * FROM comps WHERE compName = '$compName' AND disc = '$disc' AND season = '$season' AND location = '$location';";
    $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    $count1 = mysqli_num_rows($result1);

    if ($count1 > 0) {
        echo "Identical competition already exists, please rename and try again";
    }
    else if ($count1 == 0){
        $compsql = "INSERT INTO comps SET compName = '$compName', disc = '$disc', season = '$season', location = '$location';";
        // Executing the query
        $result2 = mysqli_query($conn, $compsql) or die(mysqli_error());

        $getcompID = "SELECT * from comps WHERE compName = '$compName' AND disc = '$disc' AND season = '$season' AND location = '$location';";
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
                    $agecat = $splitinfo[2];
                    $gender = $splitinfo[3];
                    $fName = $splitinfo[4];
                    $lName = $splitinfo[5];
                    $club = $splitinfo[6];
                    $dist = $splitinfo[7];
                    $track = $splitinfo[8];
                    $time = $splitinfo[9];

                    if ($time == "NULL"){
                         $time = 0;
                    }

                    $getdate = "SELECT dayID FROM dates WHERE compID = '$compID' AND date = '$date';";
                    $result = mysqli_query($conn, $getdate) or die(mysqli_error());
                    $count = mysqli_num_rows($result);
                    if ($count == 1){
                        $rows = mysqli_fetch_assoc($result);
                        $dayID = $rows['dayID'];
                        $newskater = array($age, $agecat, $gender, $fName, $lName, $club);
                        if ($newskater != $oldskater){
                            $oldskater = $newskater;
                            # check existence in current season DB - based on gender, fName, lName, club, age, season
                            $sql = "SELECT * FROM skaters WHERE gender = '$gender' AND fName = '$fName' AND lName = '$lName' AND club = '$club' AND age = '$age' AND season = '$season';";
                            $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
                            $count1 = mysqli_num_rows($result1);
    
                            # if not in current season DB
                            if ($count1 == 0){
                                # check existence in previous seasons DB, ignore season and age and gender.
                                $sql = "SELECT * FROM skaters WHERE fName = '$fName' AND lName = '$lName';";
                                $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
                                $count1 = mysqli_num_rows($result1);
                                # if not in previous seasons - create.
                                if ($count1 == 0){
                                    $skater = "INSERT INTO skaters SET age = '$age', ageCat = '$agecat', gender = '$gender', fName = '$fName', lName = '$lName', club = '$club', season = '$season';";
                                    $result2 = mysqli_query($conn, $skater) or die(mysqli_error());

                                    # GET ID NOW
                                    $getskaterID = "SELECT * from skaters WHERE age = '$age' AND ageCat = '$agecat' AND gender = '$gender' AND fName = '$fName' AND lName = '$lName' AND club = '$club' AND season = '$season';";
                                    $result3 = mysqli_query($conn, $getskaterID) or die(mysqli_error());
                                    $count3 = mysqli_num_rows($result3);
                                    if ($count3 == 1){
                                        while($rows = mysqli_fetch_assoc($result3)){
                                            $skaterID = $rows['skaterID'];
                                        }
                                    }
                                }
                                # if in previous seasons - get skaterID
                                if ($count1 > 0){
                                    # use info for each skater from most recent season
                                    $getskaterID = "SELECT skaterID, gender, club, age, ageCat, MAX(season) as season from skaters WHERE fName = '$fName' AND lName = '$lName' GROUP BY fName, lName ORDER BY season ASC;";
                                    $result3 = mysqli_query($conn, $getskaterID) or die(mysqli_error());
                                    $count3 = mysqli_num_rows($result3);
                                    # there is just one skater, get ID right away
                                    if ($count3 == 1){
                                        $rows = mysqli_fetch_assoc($result3);
                                        $skaterID = $rows['skaterID'];
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
                                        //     $currentagecat = $rows['agecat'];

                                        //     $counter++;
                                        // }
                                    }
                                }
                            }
                            # if in current season DB
                            else if ($count1 == 1){
                                # GET ID NOW
                                $getskaterID = "SELECT * from skaters WHERE age = '$age' AND ageCat = '$agecat' AND gender = '$gender' AND fName = '$fName' AND lName = '$lName' AND club = '$club';";
                                $result3 = mysqli_query($conn, $getskaterID) or die(mysqli_error());
                                $count3 = mysqli_num_rows($result3);
                                if ($count3 == 1){
                                    while($rows = mysqli_fetch_assoc($result3)){
                                        $skaterID = $rows['skaterID'];
                                    }
                                }
                            }
                        }
                        if ($skaterID >= 0){
                            $resultsql = "INSERT INTO results SET compID = '$compID', dayID = '$dayID', skaterID = '$skaterID', dist = '$dist', track = '$track', time = '$time';";
                            $result = mysqli_query($conn, $resultsql) or die(mysqli_error());
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
            echo "There was an error";
        }
    }
}
else{
    echo "error";
}

include("../fixedfooter.php");