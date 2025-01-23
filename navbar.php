<?php 
include('config/constants.php'); 
include('config/functions.php'); 

?>
<html>
    <meta charset="UTF-8">
    <head>
        <title>Alberta Speed Skating Results</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <div class = "header">
        <a href = "index.php" class = "left">
            <img id = "homelogo" src="images/TrimmedorgLogo2024.png" alt="">
            <p class = "bebas-neue" id = "title"><span class = "darktext">Speed Skating</span> <span class = "bluetext">Alberta</span> <span class = "darktext">Results Database</span></p>
        </a>
        <div class = "right">
            <a class = "darktext bebas-neue" href = "about.php">About the Site</a>
            <br>
            <a class = "bluetext bebas-neue" href = "signin.php">Sign in as Admin</a>
        </div>  
    </div>
</html>
<?php
header('Content-Type: text/html; charset=utf-8');

