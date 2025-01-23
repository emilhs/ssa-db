<?php include('navbar.php');

if (isset($_POST["clearflag"]) ) {
    $skaterID = $_POST["skaterID"];

    $sql = "UPDATE skaters SET checkInfo = FALSE
    WHERE skaterID = '$skaterID';";

    $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
}
?>
<div class = "menuH">
    <?php
    $sql = "SELECT * FROM skaters JOIN club on club = clubName WHERE checkinfo = TRUE AND alberta = TRUE AND dob IS NOT NULL ORDER BY RAND() LIMIT 1;";
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
            $skaterID = $rows['skaterID'];
            $fName = $rows['fName'];
            $lName = $rows['lName'];
            $gender = $rows['gender'];
            $club = $rows['club'];
            ?>
            
            <div class = "title">
            <p class = "bebas-neue darktext pagetitle"><span class = "darktext"><?php echo $fName?></span> <span class = "bluetext"><?php echo $lName?></span></p>
            </div>

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
                            <tr>
                                <td><p class = "text-center bebas-neue filesubmission-selected"><?php echo ($season-1); ?>-<?php echo $season; ?></p></td>
                                <td><?php echo $fName; ?></td>
                                <td><?php echo $lName; ?></td>
                                <td><?php echo $age; ?></td>
                                <td><?php echo $gender; ?></td>
                                <td><?php echo $club; ?></td>
                                <td><?php echo $dob; ?></td>
                            </tr>
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
                                <input class = "filesubmission bebas-neue darktext" type = "submit" value="Approve Info" name="clearflag"></input>
                            </form>
                            <td class = "row-right"><a class = "filesubmission-opp bebas-neue darktext" href = "editskater.php?id=<?php echo $skaterID;?>">Edit</a></td>
                            <?php
                        }
                    }
                }
        }
        else{
            echo "Everything looks good";
        }
    }
include("../fixedfooter.php");