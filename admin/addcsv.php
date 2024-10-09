<?php include('navbar.php'); ?>

<html>
<body>
<p>Please confirm you are adding the following results:</p>
<?php 
if (isset($_POST["submit"]) ) {
    if ( isset($_FILES["csv"])) {
        if ($_FILES["csv"]["error"] > 0) {
            echo "Return Code: " . $_FILES["csv"]["error"] . "<br />";

        }
        else {
            echo "Upload: " . $_FILES["csv"]["name"] . "<br />";
            echo "Type: " . $_FILES["csv"]["type"] . "<br />";
            echo "Size: " . ($_FILES["csv"]["size"] / 1024) . " Kb<br />";
            $filename = $_FILES["csv"]["tmp_name"];
            echo "Temp file: ".$filename. "<br />";
            $file = fopen($filename, "r");
            ?>
            <p>The following data will be added to the database:</p>
            <?php 
            if (($getData = fgetcsv($file, 10000, ",")) !== FALSE){
                ?><p>Competition: <?php echo $getData[1];?></p><?php
                ?><p>Discipline: <?php echo $getData[2];?></p><?php
            }
            ?>
            <table>
            <?php
            $olddate = " ";
            $oldfname = " ";
            $oldlname = " ";
            while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
            {
                $date = $getData[3];

                $rawName = $getData[6];
                $names = explode(",",$rawName);
                $fName = explode("(", $names[1])[0];
                $lName = $names[0];

                $rawCat = explode("(", $names[1])[1];
                $gender = explode(")",end(explode(" ", $rawCat)))[0];
                $agecat = explode(" ", $rawCat)[0];
                $age = explode(" ", $rawCat)[1];

                $club = $getData[7];

                $rawDist = $getData[10];
                $dists = explode(" ", $rawDist);
                $dist = $dists[0];

                $track = explode(")",explode("(", $dists[1])[1])[0];
                if ($track != 111){
                    $track = 100;
                }

                $rawtime = $getData[13];
                $mins = explode(":",$rawtime)[0];
                $secs = explode(":",$rawtime)[1];

                if (!is_numeric($mins) or !is_numeric($secs)){
                    $time = "NA";
                }
                else{
                    $time = $mins*60 + $secs;
                }

                #$rawtime = $getData[13];
                if ($date != $olddate){
                    $olddate = $date;
                    ?>
                    <tr>
                    <td colspan = 9><b>Date: <?php echo $date?></b></td>
                    </tr>
                    <tr>
                    <td colspan = 9><br></td>
                    </tr>
                    <?php
                }

                if ($fName == $oldfname and $lName == $oldlname){
                    ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo $dist?></td>
                        <td><?php echo $track?></td>
                        <td><?php echo $time?></td>
                    </tr>
                    <?php
                }
                else{
                    $oldfname = $fName;
                    $oldlname = $lName;
                    ?>
                    <tr>
                        <td><?php echo $age?></td>
                        <td><?php echo $agecat?></td>
                        <td><?php echo $gender?></td>
                        <td><?php echo $fName?></td>
                        <td><?php echo $lName?></td>
                        <td><?php echo $club?></td>
                        <td><?php echo $dist?></td>
                        <td><?php echo $track?></td>
                        <td><?php echo $time?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </table>
            <?php
        }
    }
}
else {
    echo "No file";
}
?>

<form action="addcsv.php" method="post" enctype="multipart/form-data">
  <input type="submit" value="Confirm Results" name="confirm">
</form>

</body>
</html>