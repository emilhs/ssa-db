<?php include('navbar.php');

if (isset($_GET['y'])){
    $currSeason = $_GET["y"]; 
}
?>

<div class = "menuH">
    <p class = "bebas-neue darktext padded text-center medsize">Select a Season:</p>
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
                    <th class = "row-right"></th>
                </tr>    
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $compID = $rows2['compID'];
                $compName = $rows2['compName'];
                $location = $rows2['location'];
                $disc = $rows2['disc'];
                $date = $rows2['date'];
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>    
                        <td class = "row-left"><input class = "filltable-wide" type = "text" name = "fName" value="<?php echo $compName; ?>"></input></td>
                        <td>
                            <select class = "filltable" name = "track" form = "result">
                            <?php
                                foreach ($alldiscs as $d){
                                    ?>
                                    <option <?php if ($d == $disc){ ?> selected <?php } ?>value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                    <?php
                                }
                            ?>
                            </select>
                        </td>
                        <td><input class = "filltable-wide" type = "text" name = "location" value ="<?php echo $location; ?>"></input></td>
                        <td><input class = "filltable-wide" type = "date" value = "<?php echo $date; ?>"></input></td>
                        <td class = "row-right"><input class = "filesubmission-long bebas-neue darktext" type = "submit" value="Update Competition" name="updatecomp"></input></td>
                    </tr>
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