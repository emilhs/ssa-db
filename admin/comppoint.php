<?php include('navbar.php');

if (isset($_GET['comp'])){
    $currComp = $_GET["comp"]; 
}

if (isset($_POST["moveup"]) ) {
    $p1 = $_POST["p1"];
    $p2 = $_POST["p2"];

    echo $p1;
    echo $p2;
    echo $currComp;

    if ($p1 < $p2 and $currComp > 0){
        $sqltotop = "SELECT * FROM points WHERE compID = '$currComp' AND pointIndex >= '$p1' AND pointIndex <= '$p2';";
        $sqltobot = "SELECT * FROM points WHERE compID = '$currComp' AND (pointIndex < '$p1' OR pointIndex > '$p2');";

        echo $sqltotop;

        $resultTop = mysqli_query($conn, $sqltotop);
        $resultBot = mysqli_query($conn, $sqltobot);

        $delquery = "DELETE FROM points WHERE compID = '$currComp';";
        $resultD = mysqli_query($conn, $delquery) or die(mysqli_error());

        if($resultTop == TRUE) {
            $count = mysqli_num_rows($resultTop);
            echo "to top send ".$count;
            if ($count > 0){
                while($rows = mysqli_fetch_assoc($resultTop)){
                    $skaterID = $rows['skaterID'];
                    $points = $rows['points'];
                    $insquery = "INSERT INTO points SET skaterID = '$skaterID', points = '$points', compID = '$currComp';";
                    $resultI = mysqli_query($conn, $insquery) or die(mysqli_error());
                }
            }
        }

        if($resultBot == TRUE) {
            $count = mysqli_num_rows($resultBot);
            echo "to bottom send ".$count;
            if ($count > 0){
                while($rows = mysqli_fetch_assoc($resultBot)){
                    $skaterID = $rows['skaterID'];
                    $points = $rows['points'];
                    $insquery = "INSERT INTO points SET skaterID = '$skaterID', points = '$points', compID = '$currComp';";
                    $resultI = mysqli_query($conn, $insquery) or die(mysqli_error());
                }
            }
        }

    }
    else {
        echo "Select a valid range";
    }

    // $dist = $_POST["dist"];
    // $disc = $_POST["disc"];

    // $oldtrack = $_POST["oldtrack"];
    // $olddist = $_POST["olddist"];
    // $olddisc = $_POST["olddisc"];

    // $sql00 = "UPDATE results SET dist = '$dist', track = '$track', disc = '$disc'
    // WHERE track = '$oldtrack' AND dist = '$olddist' AND compID = '$compID' AND disc = '$olddisc';";
    
    // $result00 = mysqli_query($conn, $sql00) or die(mysqli_error());
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

    <?php
        ?><p class = "bebas-neue darktext padded text-center medsize">Select Range:</p>
        <form action="" method="post" enctype="multipart/form-data">
        <?php        
        $sql = "SELECT DISTINCT skaterID, fName, lName, club, pointIndex FROM points NATURAL JOIN results NATURAL JOIN skaters WHERE compID = '$currComp' AND season = '$currSeason' ORDER BY pointIndex ASC;";
        echo $sql;
        // Executing the sql query
        $result = mysqli_query($conn, $sql);
        // Verify that SQL Query is executed or not
        if($result == TRUE) {
            // Count the number of rows which will be a way to verify if there is data in the database
            $count = mysqli_num_rows($result);
            // Initialize display of Athlete Number 
            ?>
            <p>Starting skater</p>
            <select name = "p1">
            <?php
            if ($count > 0){    
                while($rows = mysqli_fetch_assoc($result)){
                    $fName = $rows['fName'];
                    $lName = $rows['lName'];
                    $club = $rows['club'];
                    $skaterID = $rows['skaterID'];
                    $pointIndex = $rows['pointIndex'];
                    ?>
                    <option value="<?php echo $pointIndex; ?>"><?php echo $fName." ".$lName; ?> (<?php echo $club; ?>)</option>
                    <?php 
                }
            }
            ?>
            </select>
            <?php
        }
        $result = mysqli_query($conn, $sql);
        // Verify that SQL Query is executed or not
        if($result == TRUE) {
            // Count the number of rows which will be a way to verify if there is data in the database
            $count = mysqli_num_rows($result);
            // Initialize display of Athlete Number 
            ?>
            <p>End skater</p>
            <select name = "p2">
            <?php
            if ($count > 0){    
                while($rows = mysqli_fetch_assoc($result)){
                    $fName = $rows['fName'];
                    $lName = $rows['lName'];
                    $club = $rows['club'];
                    $skaterID = $rows['skaterID'];
                    $pointIndex = $rows['pointIndex'];
                    ?>
                    <option value="<?php echo $pointIndex; ?>"><?php echo $fName." ".$lName; ?> (<?php echo $club; ?>)</option>
                    <?php 
                }
            }
            ?>
            </select>
            <?php
        }
    ?>
    <input class = "filesubmission bebas-neue darktext" type = "submit" value="Move to Top" name="moveup"></input></td>
    </form>

    <p class = "bebas-neue darktext padded text-center medsize">Point Results (Latest to Earliest):</p>
    <?php
    $sql2 = "SELECT DISTINCT skaterID, fName, lName, club, points FROM points NATURAL JOIN results NATURAL JOIN skaters WHERE compID = '$currComp' AND season = '$currSeason' ORDER BY pointIndex ASC;";
    echo $sql2;
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
                    <th class = "row-mid">Points</th>
                    <th class = "row-right"></th>
                    <th class = "row-right"></th>
                </tr>    
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $fName = $rows2['fName'];
                $lName = $rows2['lName'];
                $club = $rows2['club'];
                $points = $rows2['points'];
                ?>
                <tr>
                        <td><?php echo $fName; ?> <?php echo $lName; ?> (<?php echo $club; ?>)</td>
                        <td><?php echo $points; ?></td>
                        <td></td>
                        <td></td>
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
