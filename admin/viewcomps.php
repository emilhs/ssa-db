<?php include('navbar.php');

if (isset($_POST["addcomp"])) {
    $compName = $_POST["compName"];
    $location = $_POST["location"];
    $date = $_POST["date"];

    $year = $date[0].$date[1].$date[2].$date[3];
    $month = $date[5].$date[6];

    // EC
    if ($month > 6){
        $season = $year + 1;
    }
    else {
        $season = $year;
    }

    $date = date("Ymd", strtotime($date));
    echo $date;

    $sqlcheck = "SELECT * FROM comps WHERE compName = '$compName' AND location = '$location' AND season = '$season';";
    $result0 = mysqli_query($conn, $sqlcheck) or die(mysqli_error());

    if ($result0 == TRUE){
        $count0 = mysqli_num_rows($result0);
        if ($count0 > 0){
            echo "ALREADY EXISTS";
        }
        else {
            $sql1 = "INSERT INTO comps SET compName = '$compName', location = '$location', season = '$season';";
            $result1 = mysqli_query($conn, $sql1) or die(mysqli_error());
            $sqlget = "SELECT compID FROM comps WHERE compName = '$compName' AND location = '$location' AND season = '$season';";
            $result2 = mysqli_query($conn, $sqlget) or die(mysqli_error());
            $count2 = mysqli_num_rows($result2);
            if ($count2 == 1){
                $rows2 = mysqli_fetch_assoc($result2);
                $compID = $rows2['compID'];

                $sqlinsert = "INSERT INTO dates SET compID = '$compID', dayID = 1, date = '$date';";
                echo $sqlinsert;
                $result3 = mysqli_query($conn, $sqlinsert) or die(mysqli_error());
                echo "SUCCESS";
            }
            else {
                "SOMETHING WENT WRONG";
            }
        }
    }



    // $result1 = mysqli_query($conn, $sql1) or die(mysqli_error());
    // echo "Success!"

}

if (isset($_POST["updatecomp"]) ) {
    $compID = $_POST["compID"];
    $compName = $_POST["compName"];
    $location = $_POST["location"];
    $date = $_POST["date"];

    $date = date("Ymd", strtotime($date));
    $year = $date[0].$date[1].$date[2].$date[3];
    $month = $date[4].$date[5];

    if ($month > 6){
        $season = $year + 1;
    }
    else{
        $season = $year;
    }

    $sql1 = "UPDATE comps SET compName = '$compName', location = '$location', season = '$season'
    WHERE compID = '$compID';";
    $result1 = mysqli_query($conn, $sql1) or die(mysqli_error());

    $sql2 = "UPDATE dates SET date = '$date'
            WHERE compID = '$compID';";
    $result2 = mysqli_query($conn, $sql2) or die(mysqli_error());
}

if (isset($_POST["inseries"])) {
    $compID = $_POST["compID"];
    if ($compID != NULL){
        $sql = "UPDATE comps SET series = TRUE
        WHERE compID = '$compID';";
        $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    }
}

if (isset($_POST["notinseries"])) {
    $compID = $_POST["compID"];
    if ($compID != NULL){
        $sql = "UPDATE comps SET series = FALSE
        WHERE compID = '$compID';";
        $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    }
}

if (isset($_GET['y'])){
    $currSeason = $_GET["y"]; 
}
?>

<div class = "menuH">

<table class = "darktext searchresult arimo">
    <p class = "bebas-neue darktext padded text-center medsize">Add Competition:</p>
    <table class = "darktext searchresult arimo">
                <tr class = "toprow">
                    <th class = "row-left">Competition Name</th>
                    <th class = "row-mid">Location</th>
                    <th class = "row-mid">Date</th>
                    <th class = "row-right"></th>
                </tr>    
                <tr>
                    <form action="" method="post" enctype="multipart/form-data">
                        <td><input class = "filltable-wide" type = "text" name = "compName"></input></td>
                        <td><input class = "filltable-wide" type = "text" name = "location"></input></td>
                        <td><input class = "filltable-wide" type = "date" name = "date"></input></td>
                        <td><input class = "filesubmission bebas-neue darktext" type = "submit" value="Add" name="addcomp"></td>
                    </form>
                </tr>
    </table>
    <p class = "bebas-neue darktext padded text-center medsize">Select a Season:</p>
    <?php
        $sql = "SELECT DISTINCT season FROM comps ORDER BY season ASC;";
        #$sql = "SELECT fName, lName, country FROM athletes WHERE athleteID = '$athleteID';";
        // Executing the sql query
        $result = mysqli_query($conn, $sql);
        // Verify that SQL Query is executed or not
        if($result == TRUE) {
            // Count the number of rows which will be a way to verify if there is data in the database
            $count = mysqli_num_rows($result);
            // Initialize display of Athlete Number 
            if($count > 0){
                while($rows = mysqli_fetch_assoc($result)){
                    $season = $rows['season'];
                    if ($season == $currSeason){
                        ?>
                        <a class = "bebas-neue whitetext yearbtn-selected" href = "viewcomps.php"><?php echo ($season-1)?>-<?php echo $season; ?></a>
                        <?php 
                    }
                    else {
                        ?>
                        <a class = "bebas-neue darktext yearbtn" href = "viewcomps.php?y=<?php echo $season; ?>"><?php echo ($season-1)?>-<?php echo $season; ?></a>
                        <?php 
                    }
                }
            }
        }
    ?>
    <p class = "bebas-neue darktext padded text-center medsize">Competitions (Latest to Earliest):</p>
    <?php
    if ($currSeason == NULL){
        $sql2 = "SELECT * FROM comps NATURAL JOIN dates ORDER BY date DESC;";
    }
    else {
        $sql2 = "SELECT * FROM comps NATURAL JOIN dates WHERE season = '$currSeason' ORDER BY date DESC;";
    }
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
            <table class = "darktext searchresult arimo">
                <tr class = "toprow">
                    <th class = "row-left">Competition Name</th>
                    <th class = "row-mid">Discipline</th>
                    <th class = "row-mid">Location</th>
                    <th class = "row-mid">Date</th>
                    <th></th>
                    <th></th>
                    <th class = "row-right"></th>
                </tr>    
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $compID = $rows2['compID'];
                $compName = $rows2['compName'];
                $location = $rows2['location'];
                $date = $rows2['date'];
                $series = $rows2['series'];
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>    
                        <td class = "row-left"><input class = "filltable-wide" type = "text" name = "compName" value="<?php echo $compName; ?>"></input></td>
                        <td>
                        <?php
                            $sql5 = "SELECT DISTINCT disc FROM comps NATURAL JOIN results WHERE compID = '$compID';";
                            $result5 = mysqli_query($conn, $sql5);
                            
                            // Verify that SQL Query is executed or not
                            if($result5 == TRUE) {
                                // Count the number of rows which will be a way to verify if there is data in the database
                                $count5 = mysqli_num_rows($result5);
                                if ($count5 > 0){
                                    $discs = array();
                                    while($rows5 = mysqli_fetch_assoc($result5)){
                                        $disc = $rows5['disc'];
                                        $disc = strval($disc);
                                        $discs[] = $discSort[$disc];
                                    }
                                }
                            }
                            if ($count5 > 0){
                            echo implode(', ',$discs); 
                            }
                            else{
                                ?>
                                <span class = "danger">More Info Needed</span>
                                <?php
                            }
                        ?>
                        </td>
                        <td><input class = "filltable-wide" type = "text" name = "location" value ="<?php echo $location; ?>"></input></td>
                        <td><input class = "filltable-wide" type = "date" name = "date" value = "<?php echo $date; ?>"></input></td>
                        <td><input class = "filesubmission-long bebas-neue darktext" type = "submit" value="Update Competition" name="updatecomp"></input></td>
                        <td>
                        <?php
                            if ($series == NULL or $series <= 0) {
                                ?>
                                <button class = "filesubmission-selected bebas-neue darktext">Not in Series</button>
                                <input class = "filesubmission bebas-neue darktext" type = "submit" value="In Series" name="inseries">
                                <?php
                            }
                            else{
                                ?>
                                    <input class = "filesubmission bebas-neue darktext" type = "submit" value="Not in Series" name="notinseries">
                                    <button class = "filesubmission-selected bebas-neue darktext">In Series</button>
                                    <?php
                                }
                        ?>
                        </td>
                        <td class = "row-right">
                            <a class = "filesubmission bebas-neue darktext" href = "fullcomp.php?comp=<?php echo $compID;?>">Access Competition</a>
                        </td>
                    </tr>
                    <input type = "hidden" name = "compID" value = <?php echo $compID; ?>>
                </form>
                <?php 
                $sql3 = "SELECT * FROM dates NATURAL JOIN comps WHERE compID = '$compID';";
                $result3 = mysqli_query($conn, $sql3);
                // Verify that SQL Query is executed or not
                if($result3 == TRUE) {
                    // Count the number of rows which will be a way to verify if there is data in the database
                    $count3 = mysqli_num_rows($result3);
                    // Initialize display of Athlete Number 
                    if($count3 > 0){
                        while($rows3 = mysqli_fetch_assoc($result3)){
                            $compID = $rows3['compID'];
                            $dayID = $rows3['dayID'];
                            $date = $rows3['date'];
                        }
                    }
                } 
                $displayNum++;
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