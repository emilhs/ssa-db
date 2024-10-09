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
<div>
    <div><p>find a skater</p></div>
    <p>first name begins with:</p>
    <div><?php 
        foreach ($letters as $x){ 
            if ($lValid){
                if ($x == $flet){ 
                    ?>
                    <a href="viewskaters.php?l=<?php echo $llet?>"><?php echo $x?></a>
                    <?php 
                }else{ 
                    ?>
                    <a href="viewskaters.php?f=<?php echo $x?>&l=<?php echo $llet;?>"><?php echo $x?></a>
                    <?php 
                }
            }
            else {
                if ($x == $flet){ 
                    ?>
                    <a href="viewskaters.php"><?php echo $x?></a>
                    <?php 
                }else{ 
                    ?>
                    <a href="viewskaters.php?f=<?php echo $x;?>"><?php echo $x?></a>
                    <?php 
                }
            }
        } ?>
    </div>
    <p class = "selector">last name begins with:</p>
    <div class = "letterset text-center"><?php 
        foreach ($letters as $x){
            if ($fValid){
                if ($x == $llet){ 
                    ?>
                    <a href="viewskaters.php?f=<?php echo $flet?>"><?php echo $x?></a>
                    <?php 
                }else{ 
                    ?>
                    <a href="viewskaters.php?f=<?php echo $flet?>&l=<?php echo $x;?>"><?php echo $x?></a>
                    <?php 
                }
            }
            else {
                if ($x == $llet){ 
                    ?>
                    <a href="viewskaters.php"><?php echo $x?></a>
                    <?php 
                }else{ 
                    ?>
                    <a href="viewskaters.php?l=<?php echo $x;?>"><?php echo $x?></a>
                    <?php 
                }
            }
        } ?>
    </div>
</div>

    <?php
    if ($fValid or $lValid){
        if ($fValid and $lValid){
            $sql = "SELECT * FROM skaters WHERE fName LIKE '$flet%' AND lName LIKE '$llet%';";
        }
        else if ($lValid){
            $sql = "SELECT * FROM skaters WHERE lName LIKE '$llet%';";
        }
        else if ($fValid){
            $sql = "SELECT * FROM skaters WHERE fName LIKE '$flet%';";
        }
        $result = mysqli_query($conn, $sql) or die(mysqli_error());
        $count = mysqli_num_rows($result);
        $displayNum = 1;
        if($count > 0) {
                    ?>
                    <br>
                    <table>
                        <tr>
                            <td>First Name</td>
                            <td>Last Name</td>
                            <td>Club</td>
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
                        <tr onclick="window.location='skaterprofile.php?id=<?php echo $skaterID?>';">
                            <td><?php echo $fName; ?></td>
                            <td><?php echo $lName; ?></td>
                            <td><?php echo $club; ?></td>
                        </tr>
                    <?php
                    }?>
            </table></div><?php
        }
    }
    ?>
<a href ="index.php" class = "btn sm-splash text-center">go home</a>
<script>
    const advancedCheckbox = document.getElementById('advanced');
    const advancedOptions = document.getElementById('advancedOptions');

    advancedCheckbox.addEventListener('change', function() {
        if (this.checked) {
            advancedOptions.style.display = 'block';
        } else {
            advancedOptions.style.display = 'none';
        }
    });
</script>
</body>