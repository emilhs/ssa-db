<?php include('navbar2.php');
if (isset($_GET['y'])){
    $currSeason = $_GET["y"]; 
}
?>

<div class = "bestbox">
<div class = "bebas-neue darktext bestbox-banner">Club List:</div>
<!-- <p class = "arimo desc darktext ">Select one of the following Alberta clubs, listed by total registrants in the database.</p> -->
<?php
$sql = "SELECT clubName, COUNT(skaterID) AS regd FROM skaters JOIN club ON club = clubName WHERE alberta = TRUE GROUP BY clubName ORDER BY regd DESC;";
    // Executing the sql query
    $result = mysqli_query($conn, $sql);
    // Verify that SQL Query is executed or not
    if($result == TRUE) {
        // Count the number of rows which will be a way to verify if there is data in the database
        $count = mysqli_num_rows($result);
        // Initialize display of Athlete Number 
        $enum = 0;
        if($count > 0){
            ?>
            <table class = "cbtn-tbl">
            <?php
            while($rows = mysqli_fetch_assoc($result)){
                $club = $rows['clubName'];
                $regd = $rows['regd'];
                ?> 
                <tr>
                    <td>
                        <button class = "darktext bebas-neue cbtns <?php if($enum%2==0){?> oddrow <?php } ?>" onclick = "document.location='clubpage.php?club=<?php echo $club; ?>'"><?php echo $club; ?> (<?php echo $regd; ?>)</button>
                    </td>
                </tr>
                <?php 
                $enum++;
            }
            ?>
            </table>
            <?php
        }
    }
?>
</div>

<?php include("footer.php");