<?php include('navbar.php'); 
#ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
?>

<div class = "menuH">
    <p class = "bebas-neue darktext pagetitle">Submit Results to SSA Database</p>

<?php 
if (isset($_POST["submit"]) ) {
    if (isset($_FILES["csv"])) {
        if ($_FILES["csv"]["error"] > 0) {
            echo "Return Code: " . $_FILES["csv"]["error"] . "<br/>";
        }
        else {
            $filename = $_FILES["csv"]["tmp_name"];
            $file = fopen($filename, "r");
            ?>
            <br>
            <p class = "arimo darktext smallsize">The following competition will be added to the database:</p>
            <form action="addingcomp.php" method="post" enctype="multipart/form-data">
            
            <?php
            if (($getData = fgetcsv($file, 10000, ",")) !== FALSE){ ?>
                <br>
                <table class = "searchresult darktext arimo">
                    <tr class = "toprow smallsize">
                        <th class = "row-left"><p>Location:</p></th>
                        <th class = "row-right"><p>Competition Name:</p></th>
                    </tr>   
                    <tr>
                        <th><input class = "filltable login" type = "text" name = "location"></input></th>
                        <th><input class = "filltable login" type = "text" name = "compName" value = "<?php echo $getData[1]; ?>"></input></th>
                        </th>
                    </tr>   
                </table>
                <?php
                rewind($file);
            }
            ?>
            <br>
            <p class = "arimo darktext smallsize">The following data will be added to the database:</p>
            <br>
            <table class = "darktext rankresult arimo">
                <tr class = "toprow">
                    <td class = "row-left">Age</td>
                    <td class = "row-mid">Gender</td>
                    <td class = "row-mid">Name</td>
                    <td class = "row-mid">Surname</td>
                    <td class = "row-mid">Club</td>
                    <td class = "row-mid">Distance</td>
                    <td class = "row-mid">Track</td>
                    <td class = "row-mid">Type</td>
                    <td class = "row-right">Time</td>
                </tr>

            <?php
            $olddate = "";
            $dates = array();
            $oldskater = array();
            $topass = "";
            $skaters = array();
            $counter = 0;

            $displayNum = 1;

            while (($getData = fgetcsv($file, 10000, ",")) !== FALSE){
                $date = date("Ymd", strtotime($getData[3]));

                $rawName = $getData[6];
                $names = explode(",",$rawName);
                
                $possible_fNames = explode("(", $names[1]);

                if (sizeof($possible_fNames) == 2){
                    $fName = trim(str_replace("'", "", $possible_fNames[0]));
                    $rawCat = explode("(", $names[1])[1];
                    $age = explode(")", (explode(" ", $rawCat)[1]))[0];
                    $gender = explode(")", (end(explode(" ", $rawCat))))[0];
                }
                else {
                    $fName = trim(str_replace("'", "", $possible_fNames[0]."(".$possible_fNames[1]));
                    $rawCat = $possible_fNames[2];
                    #$age = ($possible_fNames[2]);
                    $age = explode("(",explode(" ", $rawCat)[1])[0];
                    $gender = explode(")",end(explode(" ", $rawCat)))[0];
                }

                if ($age == "Start"){
                    $age = "Active Start";
                }

                $lName = trim(str_replace("'", "", $names[0]));

                
                #$age = explode(" ", $rawCat)[1];

                $club = $getData[7];

                $rawDist = $getData[10];

                #echo $rawDist;

                $dists = explode(" ", $rawDist);

                $track1 = 0;
                $i = 0;
                while (!is_numeric($dists[$i]) and ($i < sizeof($dists))){
                    $i++;
                }

                // echo $i;
                if ($i == 0){
                    $dist = $dists[$i];
                    $i = $i+1;
                    while (sizeof($dists) > $i){
                        $poss = explode(")",explode("(", $dists[$i])[1])[0];
                        if ($poss != NULL and $poss > $track1){
                            $track1 = $poss;
                        }
                        $i = $i+1;
                    }
                    // if (sizeof($dists) > ($i+1)){
                    //     $track1 = explode(")",explode("(", $dists[$i+1])[1])[0];
                    //     if (sizeof($dists) > ($i+3)){
                    //         $track2 = explode(")",explode("(", $dists[$i+3])[1])[0];
                    //         if (sizeof($dists) > ($i+4)){
                    //             $track3 = explode(")",explode("(", $dists[$i+4])[1])[0];
                    //             if (sizeof($dists) > ($i+5)){
                    //                 $track4 = explode(")",explode("(", $dists[$i+5])[1])[0];
                    //             }
                    //         }
                    //     }
                    // }
                    #echo $dist;
                }
                else if ($i == sizeof($dists)){
                    $dist = "NULL";
                    if (str_contains($rawDist, "m")){
                        $dist = explode("m", $rawDist)[0];
                        if (str_contains($dist, " ")){
                            $dist = end(explode(" ", $dist));
                        }
                    }
                    if (str_contains($rawDist, "-")){
                        $dist = explode("-", $rawDist)[0];
                        if (str_contains($dist, " ")){
                            $dist = end(explode(" ", $dist));
                        }
                    }
                }
                else {
                    $dist = $dists[$i];
                    while (sizeof($dists) > $i){
                        $poss = explode(")",explode("(", $dists[$i])[1])[0];
                        if ($poss > $track1){
                            $track1 = $poss;
                        }
                        $i = $i+1;
                    }
                }

                if ($dist < 50){
                    $dist = $dist * 400;
                }

                $track = $track1;
                if ($track == 0){
                    if ($age == "Senior" or $age == "Junior"){
                        $track = 100;
                    }
                    else {
                        $track = 100;
                    }
                }

                $rawtime = $getData[13];
                $mins = explode(":",$rawtime)[0];
                $secs = explode(":",$rawtime)[1];

                if (!is_numeric($mins) or !is_numeric($secs)){
                    $time = "NULL";
                }
                else{
                    $time = $mins*60 + $secs;
                }

                $rType = "NULL";
                $rVal = NULL;
                if ($track == 100 or $track == 111){
                    $rType = "ST";
                    $rVal = 1;
                }
                else if ($track == 400){
                    $rType = "LT";
                    if (str_contains(strtoupper($rawDist), "MASS START")){
                        $rType = "MS";
                        $rVal = 2;
                    }
                    else if (str_contains(strtoupper($rawDist), "OLYMPIC STYLE")){
                        $rType = "OS";
                        $rVal = 3;
                    }
                }

                ?>
                <tr>
                    <td><?php echo $age?></td>
                    <td><?php echo $gender?></td>
                    <td><?php echo $fName?></td>
                    <td><?php echo $lName?></td>
                    <td><?php echo $club?></td>
                    <td><?php echo $dist?></td>
                    <td><?php echo $track?></td>
                    <td><?php echo $rType?></td>
                    <td><?php echo $time?></td>
                </tr>
                <?php
                
                $topass = $topass."!~!".$date."!~!".$age."!~!".$gender."!~!".$fName."!~!".$lName."!~!".$club."!~!".$dist."!~!".$track."!~!".$time."!~!".$rVal;
                if ($date != $olddate){

                    $year = $date[0].$date[1].$date[2].$date[3];
                    $month = $date[4].$date[5];

                    if ($month > 6){
                        $season = $year + 1;
                    }
                    else {
                        $season = $year;
                    }

                    $olddate = $date;
                    ?>
                    <input type="hidden" name="dates[]" value="<?php echo $date; ?>">
                    <?php
                }
            }
            ?>
            </table>
            <input type = "hidden" value = "<?php echo $topass; ?>" name = "skaters">
            <input type = "hidden" value="<?php echo $season?>" name="season">
            <input class = "filesubmission-wide darktext bebas-neue" type = "submit" value="Upload Competition" name="upload"></input>
            </form>
            <?php
        }
    }
}
else { 
    ?>
    <p class = "bebas-neue dangertext pagetitle">File not found!</p>
<?php 
}
?>
</div>

</body>
</html>