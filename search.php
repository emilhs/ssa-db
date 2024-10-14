<?php include('navbar.php'); 

$fValid = FALSE;
$lValid = FALSE;

if (isset($_GET['f'])){
    $flet = $_GET["f"]; 
    if (in_array($flet, $letters)){
        $fValid = TRUE;
    }
}
if (isset($_GET['l'])){
    $llet = $_GET["l"]; 
    if (in_array($llet, $letters)){
        $lValid = TRUE;
    }
}

?>

<body>
<div class = "menuH">
    <p class = "bebas-neue darktext pagetitle">Skater Search</p>
    <p class = "arimo desc darktext ">Only skaters that raced one of the <a class = "intextlink" href = "competitions.php">competitions</a> in the database can be found.</p>
    <p class = "bebas-neue darktext text-center medsize">First Name:</p>
    <div><?php 
        foreach ($letters as $x){ 
                if ($x == $flet){ 
                    ?>
                    <a class = "letterbutton-selected bebas-neue darktext" href="search.php?l=<?php echo $llet?>"><?php echo $x?></a>
                    <?php 
                }else{ 
                    ?>
                    <a class = "letterbutton bebas-neue darktext" href="search.php?f=<?php echo $x?>&l=<?php echo $llet;?>"><?php echo $x?></a>
                    <?php 
                }
        } ?>
    </div>
    <p class = "bebas-neue darktext text-center medsize">Last Name:</p>
    <div><?php 
        foreach ($letters as $x){
                if ($x == $llet){ 
                    ?>
                    <a class = "letterbutton-selected bebas-neue darktext" href="search.php?f=<?php echo $flet?>"><?php echo $x?></a>
                    <?php 
                }else{ 
                    ?>
                    <a class = "letterbutton bebas-neue darktext" href="search.php?f=<?php echo $flet?>&l=<?php echo $x;?>"><?php echo $x?></a>
                    <?php 
                }
        } ?>
    </div>
    <p class = "bebas-neue darktext text-center medsize">Skaters:</p>
        <?php
        if ($fValid or $lValid){
                if ($fValid and $lValid){
                    $sql = "SELECT fName, lName, club, skaterID, max(season) AS season FROM skaters WHERE fName LIKE '$flet%' AND lName LIKE '$llet%'  GROUP BY skaterID ORDER BY lName, fName;";
                }
                else if ($lValid){
                    $sql = "SELECT fName, lName, club, skaterID, max(season) AS season FROM skaters WHERE lName LIKE '$llet%'  GROUP BY skaterID ORDER BY lName, fName;";
                }
                else if ($fValid){
                    $sql = "SELECT fName, lName, club, skaterID, max(season) AS season FROM skaters WHERE fName LIKE '$flet%'  GROUP BY skaterID ORDER BY lName, fName;";
                }

                $result = mysqli_query($conn, $sql) or die(mysqli_error());
                $count = mysqli_num_rows($result);
                $displayNum = 1;
                if($count > 0) {
                            ?>
                            <table class = "darktext searchresult arimo">
                                <tr class = "toprow">
                                    <th class = "row-left">First Name</th>
                                    <th class = "row-mid">Last Name</th>
                                    <th class = "row-right">Club</th>
                                    <th class = "row-right"></th>
                                </tr>    
                            <?php
                            // For everything in the database, display
                            while($rows = mysqli_fetch_assoc($result)){
                                // Store database details in variables. 
                                $fName = $rows['fName'];
                                $lName = $rows['lName'];
                                $club = $rows['club'];
                                $skaterID = $rows['skaterID'];
                                # $cumTime = $rows['cumTime'];
                                ?>
                                <tr <?php if($displayNum%2==0){?> class = "odd" <?php } ?> onclick="window.location='athlete.php?id=<?php echo $skaterID?>';">
                                    <td class = "row-left"><?php echo $fName; ?></td>
                                    <td><?php echo $lName; ?></td>
                                    <td class = "row-right"><?php echo $club; ?></td>
                                </tr>
                            <?php
                            $displayNum++;
                            }?>
                    </table></div><?php
                }
                else{?>
                    <p class = "arimo darktext text-center medsize">No skaters found for the selected letter(s)</p>
                <?php }
        } 
        else {
        ?>
    <p class = "arimo darktext text-center medsize">Search for a skater by their first name or last name</p>
    <?php
        }
        ?>
</div>
<?php include('footer.php');