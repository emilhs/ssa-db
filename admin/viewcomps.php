<?php include('navbar.php');

if (isset($_GET['y'])){
    $currSeason = $_GET["y"]; 
}
?>

<p>select season</p>
<?php
$sql = "SELECT DISTINCT season FROM comps;";
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
                ?>
                <a href = "viewcomps.php?y=<?php echo $season; ?>"><?php echo $season; ?></a>
                <?php 
            }
        }
    }
?>

<p>select a comp to edit</p>
<?php
$sql2 = "SELECT * FROM comps WHERE season = '$currSeason';";
    #$sql = "SELECT fName, lName, country FROM athletes WHERE athleteID = '$athleteID';";
    // Executing the sql query
    $result2 = mysqli_query($conn, $sql2);
    // Verify that SQL Query is executed or not
    if($result2 == TRUE) {
        // Count the number of rows which will be a way to verify if there is data in the database
        $count2 = mysqli_num_rows($result2);
        // Initialize display of Athlete Number 
        if($count2 > 0){
            while($rows2 = mysqli_fetch_assoc($result2)){
                $compID = $rows2['compID'];
                $compName = $rows2['compName'];
                $location = $rows2['location'];
                $disc = $rows2['disc'];
                ?>
                <h3>
                    <?php echo $compName; ?>
                    <?php echo $disc; ?> 
                    <?php echo $location; ?>
                </h3>
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
                            ?>
                            <a href = "viewresults.php?comp=<?php echo $compID; ?>&day=<?php echo $dayID; ?>">
                                <?php echo $date; ?>
                            </a>

                            <?php
                        }
                    }
                } 
            }
        }
    }
?>