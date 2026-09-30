<?php 
include('config/constants.php'); 
include('config/functions.php'); 

?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Alberta Speed Skating Results</title>
        <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
    </head>
    <?php include('header.php'); ?>
</html>
<?php
header('Content-Type: text/html; charset=utf-8');

