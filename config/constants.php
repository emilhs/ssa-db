<?php 
    // Start Session
    session_start();
    
    define('LOCALHOST', 'localhost');
    define('DB_USERNAME', "eejkmtmwqd");
    define('DB_PASSWORD', "R8JFuYWf3q");
    define('DB_NAME', "eejkmtmwqd");

    $conn = mysqli_connect(LOCALHOST,DB_USERNAME,DB_PASSWORD) or die(mysqli_error()); //Database Connection
    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error()); //Selecting Database

    if (!mysqli_set_charset($conn, "utf8mb4")) {
        die("Error loading character set utf8mb4: " . mysqli_error($conn));
    }

    // if ($conn->connect_error) {
    //     die('Connect Error (' . $conn->connect_errno . ') ' . $conn->connect_error);
    // }
    
    // // Set the character set to UTF-8
    // if (!conn->set_charset("utf8mb4")) {
    //     printf("Error loading character set utf8mb4: %s\n", $conn->error);
    //     exit();
    // }

    # --------------

    $letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
    $ages = array("Active Start", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "Senior");
    $ageCats = array("Active Start", "Child", "Youth", "Neo-Junior", "Junior", "Senior");
    $alldiscs = array("ST");
    $alldistances = array(50, 100, 200, 300, 400, 500, 600, 777, 800, 1000, 1500, 2000, 3000, 5000, 10000);
    $Alltracks = array(100,111,400);
    $only100 = array();
    $only111 = array();
    $only400 = array();
    $distances100 = array(50, 100, 200, 300, 400, 500, 800, 1500);
    $distances111 = array(500, 1000, 1500);
    $distances400 = array(100, 200, 400, 500, 800, 1000, 1500, 2800, 3000, 5000);
    $pagelength = 20;

    $discSort = array(1 => "Short Track", 2 => "Mass Start", 3 => "Olympic Style");
    $ageSort = array("Active Start"=>array("Active Start"), "Child"=>array("6", "7", "8", "9", "10"), "Youth" =>array("11","12","13"), "Neo-Junior" =>array("14","15"), "Junior"=>array("16","17","18"), "Senior"=>array("19", "20", "30", "40", "50", "60"));
    $genderSort = array("Active Start"=>array("M", "F"), "Child"=>array("M", "F"), "Youth" =>array("M","F"), "Neo-Junior" =>array("M","F"), "Junior"=>array("M","F"), "Senior"=>array("M","F"));
    $defaultRank = array("Active Start"=>"100m200", "Child"=>"100m200m400", "Youth"=>"400m800m1500", "Neo-Junior"=>"500m1500", "Junior"=>"500m1500", "Senior"=>"500m1500");
    $defaultTrack = array("Active Start"=>"100", "Child"=>"100", "Youth"=>"100", "Neo-Junior"=>"111", "Junior"=>"111", "Senior"=>"111");
?>