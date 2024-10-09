<?php 
    // Start Session
    session_start();
    
    // Create constants to store non-repeating values
    define('SITEURL', 'http://localhost/ssa-db/');
    define('LOCALHOST', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'new_ssa_db');

    $conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD) or (mysqli_error()); //Database Connection    
    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error()); //Selecting Database
    
    $letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
    $ages = array("Active Start", "6-8", "9-10", "11", "12", "13", "B1", "B2", "A1", "A2", "M", "F");
    $ageCats = array("Active Start", "Child", "Neo-Junior", "Junior", "Neo-Senior", "Senior", "Masters");
    $alldistances = array(50, 100, 200, 300, 400, 500, 800, 1000, 1500);
    $only100 = array(50, 100, 200, 300, 400, 800);
    $only111 = array(1000, 1500);
    $distances100 = array(50, 100, 200, 300, 400, 500, 800);
    $distances111 = array(500, 1000, 1500);
    $pagelength = 20;

?>