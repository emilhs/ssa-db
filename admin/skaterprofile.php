<?php include('navbar.php');
if (isset($_GET['id'])){
    $skaterID = $_GET["id"]; 
}



?>
<a href = "index.php">Go back home</a>

<div><p><?php echo $fName?> <?php echo $lName?></p></div><?php
$sql = "SELECT * FROM skaters WHERE skaterID = '$skaterID';";
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
    } else {
        header('location:'.SITEURL.'viewskaters.php');
    }
}

?><div><p>info</p></div>
<?php
$sql2 = "SELECT * FROM skaters WHERE skaterID = '$skaterID';";
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
            <form action="" method="post" enctype="multipart/form-data">
            <table> 
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $fName = $rows2['fName'];
                $lName = $rows2['lName'];
                $age = $rows2['age'];
                $ageCat = $rows2['ageCat'];
                $gender = $rows2['gender'];
                $club = $rows2['club'];
                $dob = $rows2['dob'];
                ?>
                <tr>
                    <td><input type = "text" name = "fName" value = "<?php echo $fName; ?>"></input></td>
                    <td><input type = "text" name = "lName" value = "<?php echo $lName; ?>"></input></td>
                    <td><input type = "text" name = "age" value = "<?php echo $age; ?>"></input></td>
                    <td><input type = "text" name = "ageCat" value = "<?php echo $ageCat; ?>"></input></td>
                    <td><input type = "text" name = "gender" value = "<?php echo $gender; ?>"></input></td>
                    <td><input type = "text" name = "club" value = "<?php echo $club; ?>"></input></td>
                    <td><input type = "date" name = "dob" value = "<?php echo $dob; ?>"></input></td>
                </tr>
                <?php
            }
            ?>
            </table>
            <input type = "submit" value="Update Skater" name="update"></input>
            </form>
            <?php

        }
    }
?>

<?php

if (isset($_POST["update"]) ) {
    $fName = $_POST["fName"];
    $lName = $_POST["lName"];
    $age = $_POST["age"];
    $ageCat = $_POST["ageCat"];
    $gender = $_POST["gender"];
    $club = $_POST["club"];
    $dob = $_POST["dob"];

    echo $fName;
    echo $lName;
    echo $age;
    echo $ageCat;
    echo $gender;
    echo $club;
    echo $dob;

    $sql = "UPDATE skaters SET fName = '$fName', lName = '$lName',age = '$age',
                                ageCat = '$ageCat', gender = '$gender',
                                club = '$club', dob = '$dob'
                    WHERE skaterID = '$skaterID';";

    echo $sql;

    $result1 = mysqli_query($conn, $sql) or die(mysqli_error());

    echo "Success!";
}



?>