<?php include('../config/constants.php'); ?>
<?php include('VerifyAdminLogin.php'); ?>

<html>
    <head>
        <title>Alberta Speed Skating Results</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <div class = "header">
        <a href = "index.php" class = "left">
            <img id = "homelogo" src="../images/TrimmedorgLogo2024.png" alt="">
            <p class = "bebas-neue" id = "title"><span class = "darktext">Speed Skating</span> <span class = "bluetext">Alberta</span> <span class = "darktext">Results Database</span></p>
        </a>
        <div class = "right">
            <a class = "darktext bebas-neue" href = "../about.php">Signed in as <span class = "bluetext"><?php echo $username;?></span></a>
            <br>
            <a class = "dangertext bebas-neue" href = "../signout.php">Sign Out</a>
        </div>  
    </div>
</html>
