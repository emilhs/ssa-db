<?php 
    // Start Session
    session_start();
    
    // Create constants to store non-repeating values
    // Get Heroku ClearDB connection information
    $cleardb_url = parse_url(getenv("CLEARDB_DATABASE_URL"));
    $cleardb_server = $cleardb_url["jj820qt5lpu6krut.cbetxkdyhwsb.us-east-1.rds.amazonaws.com"];
    $cleardb_username = $cleardb_url["g5epois4jr3fvwjm"];
    $cleardb_password = $cleardb_url["fknimuhilplydf54"];
    $cleardb_db = substr($cleardb_url["i4zt2ijecbj7ykgu"],1);
    $active_group = 'default';
    $query_builder = TRUE;
    // Connect to DB
    $conn = mysqli_connect($cleardb_server, $cleardb_username, $cleardb_password, $cleardb_db);
    
    //$db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error()); //Selecting Database
    
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