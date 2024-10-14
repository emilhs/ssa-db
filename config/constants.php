<?php 
    // Start Session
    session_start();
    

    //Get Heroku ClearDB connection information
    // define('SITEURL', 'http://localhost/ssa-db/');
    // define('LOCALHOST', 'localhost');
    #$cleardb_url = parse_url(getenv("JAWSDB_URL"));
    $cleardb_url = parse_url(getenv("JAWSDB_URL"));
    $hostname = "jj820qt5lpu6krut.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
    $username = "g5epois4jr3fvwjm";
    $password = "fknimuhilplydf54";
    $database = "i4zt2ijecbj7ykgu";

    // $conn = new mysqli($hostname, $username, $password, $database, 3306) or die(mysqli_error());
    #$conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD);
    #$db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error()); //Selecting Database
    // Check connection
    // if ($conn->connect_error) {
    //     die("Connection failed: " . $conn->connect_error);
    // }
    // echo "Connection was successfully established!";

    // #$db_select = mysqli_select_db($conn, $cleardb_url["path"]) or die(mysqli_error());
    // Create constants to store non-repeating values
    //Get Heroku ClearDB connection information
    // define('SITEURL', 'http://localhost/ssa-db/');
    // define('LOCALHOST', 'localhost');
    // define('DB_USERNAME', 'root');
    // define('DB_PASSWORD', '');
    // define('DB_NAME', 'new_ssa_db');
    // $active_group = 'default';
    // $query_builder = TRUE;
    // Connect to DB
    $conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD);
    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error()); //Selecting Database
    
    $letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
    $ages = array("Active Start", "6-8", "9-10", "11", "12", "13", "B1", "B2", "A1", "A2", "M", "F");
    $ageCats = array("Active Start", "Child", "Youth", "Neo-Junior", "Junior", "Senior");
    $alldiscs = array("ST");
    $alldistances = array(50, 100, 200, 300, 400, 500, 800, 1000, 1500);
    $Alltracks = array(100,111);
    $only100 = array(50, 100, 200, 300, 400, 800);
    $only111 = array(1000);
    $distances100 = array(50, 100, 200, 300, 400, 500, 800, 1500);
    $distances111 = array(500, 1000, 1500);
    $pagelength = 20;

    $ageSort = array("Active Start"=>array("Active Start"), "Child"=>array("6-8", "9-10"), "Youth" =>array("11","12","13"), "Neo-Junior" =>array("B1","B2"), "Junior"=>array("A1","A2"), "Senior"=>array("Neo-Senior","Senior", "Masters"));
    $genderSort = array("Active Start"=>array("Mixte"), "Child"=>array("Mixte"), "Youth" =>array("M","F"), "Neo-Junior" =>array("M","F"), "Junior"=>array("M","F"), "Senior"=>array("M","F"));
    $defaultRank = array("Active Start"=>"100m200", "Child"=>"100m200m400", "Youth"=>"400m800m1500", "Neo-Junior"=>"500m1500", "Junior"=>"500m1500", "Senior"=>"500m1500");
    $defaultTrack = array("Active Start"=>"100", "Child"=>"100", "Youth"=>"100", "Neo-Junior"=>"111", "Junior"=>"111", "Senior"=>"111");
?>