<?php include('navbar.php');

if (isset($_POST["makealberta"])) {
    $clubName = $_POST["clubName"];

    if ($clubName != NULL){
        $sql = "UPDATE club SET alberta = TRUE
        WHERE clubName = '$clubName';";
        $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    }
}

if (isset($_POST["notalberta"])) {
    $clubName = $_POST["clubName"];

    if ($clubName != NULL){
        $sql = "UPDATE club SET alberta = FALSE
        WHERE clubName = '$clubName';";
        $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    }
}


$sqlCurrent = "SELECT DISTINCT clubName FROM club ORDER BY clubName ASC;";
$current = array();
$result = mysqli_query($conn, $sqlCurrent);
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
            $club = $rows['clubName'];
            $current[] = $club;
        }
        ?>
        </table>
        <?php
    }
}

$sqlAll = "SELECT DISTINCT club FROM skaters ORDER BY club ASC;";
$result = mysqli_query($conn, $sqlAll);
// Verify that SQL Query is executed or not
if($result == TRUE) {
    // Count the number of rows which will be a way to verify if there is data in the database
    $count = mysqli_num_rows($result);
    // Initialize display of Athlete Number 
    if($count > 0){
        while($rows = mysqli_fetch_assoc($result)){
            $clubName = $rows['club'];
            #echo in_array($clubName, $current);
            if (!in_array($clubName, $current)){
                $clubInsert = "INSERT INTO club SET clubName = '$clubName', alberta = NULL";
                $result3 = mysqli_query($conn, $clubInsert) or die(mysqli_error());
            }
        }
    }
}
?>
<div class = "menuH">
    <p class = "bebas-neue darktext padded text-center medsize">Flag if the Following Clubs are in Alberta:</p>
    <?php
    $sql2 = "SELECT * FROM club ORDER BY alberta ASC, clubName ASC;";
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
                    <th class = "row-left">Club</th>
                    <th class = "row-right">Alberta</th>
                </tr>    
            <?php
            while($rows2 = mysqli_fetch_assoc($result2)){
                $clubName = $rows2['clubName'];
                $alberta = $rows2['alberta'];
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?>>    
                        <input type = "hidden" value = '<?php echo $clubName; ?>' name = "clubName">
                        <td class = "row-left"><?php echo $clubName?></td>
                        <td class = "row-right">
                        <?php
                            if ($alberta == NULL) {
                                ?>
                                <input class = "filesubmission bebas-neue darktext" type = "submit" value="Not Alberta" name="notalberta">
                                <input class = "filesubmission bebas-neue darktext" type = "submit" value="Alberta" name="makealberta">
                                <?php
                            }
                            else{
                                if ($alberta > 0){
                                    ?>
                                    <input class = "filesubmission bebas-neue darktext" type = "submit" value="Not Alberta" name="notalberta">
                                    <button class = "filesubmission-selected bebas-neue darktext">Alberta</button>
                                    <?php
                                }
                                else {
                                    ?>
                                    <button class = "filesubmission-selected bebas-neue darktext">Not Alberta</button>
                                    <input class = "filesubmission bebas-neue darktext" type = "submit" value="Alberta" name="makealberta">
                                    <?php
                                }
                            }

                        ?>
                        </td>
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

$getinfo = "SELECT * FROM club;"


?>