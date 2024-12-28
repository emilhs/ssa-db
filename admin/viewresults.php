<?php include('navbar.php');



if (isset($_GET['comp']) & isset($_GET['day'])){
    $currComp = $_GET["comp"]; 
    $currDay = $_GET["day"]; 
}
?>

<p>select a time or skater to edit</p>

<?php 
$sql = "SELECT * FROM results NATURAL JOIN skaters WHERE compID = $currComp AND dayID = $currDay;";

$result = mysqli_query($conn, $sql);
// Verify that SQL Query is executed or not
if($result == TRUE) {
    // Count the number of rows which will be a way to verify if there is data in the database
    $count = mysqli_num_rows($result);
    // Initialize display of Athlete Number 
    if($count > 0){
        ?>
        <table>
        <?php
        while($rows = mysqli_fetch_assoc($result)){
            $fName = $rows['fName'];
            $lName = $rows['lName'];
            $dist = $rows['dist'];
            $track = $rows['track'];
            $time = $rows['time'];
            $raceID = $rows['raceID'];
            ?>
            <tr>
                <td><?php echo $fName;?></td>
                <td><?php echo $lName;?></td>
                <td><?php echo $dist;?></td>
                <td><?php echo $track;?></td>
                <td><?php echo $time;?></td>
            </tr>
            <?php 
        }
        ?>
        </table>
        <?php
    }
}


?>