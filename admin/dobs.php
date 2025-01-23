<?php include('navbar.php');

if (isset($_POST["updateinfo"]) ) {
    $dob = $_POST["dob"];
    $skaterID = $_POST["skaterID"];
    $gender = strtoupper($_POST["gender"]);

    if (empty($dob)){
        echo "REACHED";
        $sql2 = "UPDATE skaters SET gender = '$gender' WHERE skaterID = '$skaterID';";
        $result2 = mysqli_query($conn, $sql2) or die(mysqli_error());
    }
    else {
        # NEW BIRTHDAY
        $sql2 = "UPDATE skaters SET dob = '$dob', gender = '$gender' WHERE skaterID = '$skaterID';";
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
                    $sql = "UPDATE skaters SET age = '$age' WHERE skaterID = '$skaterID' AND season = '$myseason';";
                    $result2 = mysqli_query($conn, $sql) or die(mysqli_error());
                    echo "SUCCESS";
                }
            }
        }
    }
}

?>
<div class = "menuH">
    <?php
    $sql = "SELECT * FROM skaters JOIN club on club = clubName WHERE alberta = TRUE AND dob IS NULL ORDER BY RAND() LIMIT 1;";
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
            $gender = $rows['gender'];
            $club = $rows['club'];
            $skaterID = $rows['skaterID'];
            ?>
            <div class = "title">
            <p class = "bebas-neue darktext pagetitle"><span class = "darktext"><?php echo $fName?></span> <span class = "bluetext"><?php echo $lName?></span></p>
            <p class = "bebas-neue darktext medsize"><span class = "darktext"><?php echo $club?></span></p>
            <table class = "darktext searchresult-nogap arimo">
                <tr class = "toprow">
                    <th class = "row-mid">Date of Birth</th>
                </tr>
                <form action="" method="post" enctype="multipart/form-data">
                <tr>
                    <input type = "hidden" value ="<?php echo $skaterID; ?>" name = "skaterID">
                    <td><input class = "filltable" type = "date" name = "dob" value = ""></input></td>
                </tr>
                <tr class = "toprow">
                    <th class = "row-mid">Gender</th>
                </tr>
                <tr>
                    <td><input class = "filltable" type = "text" name = "gender" value = "<?php echo $gender; ?>"></input></td>
                </tr>
                <tr>
                    <td><input class = "filesubmission bebas-neue darktext" type = "submit" value="Update Info" name="updateinfo"></input></td>
                </tr>
            </form>
            </div>
            <?php
        }
        else {
            ?>
            Everyone has a birthday currently.
            <?php
        }
    }
    ?>
<?php



?>