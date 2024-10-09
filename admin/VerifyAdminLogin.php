<?php
    // Check Authorization
    if(!isset($_SESSION['user-admin'])) {
        // Set the error message indicating that the user is not logged in and redirect to login page. 
        header('location:'.SITEURL.'signin.php');
    }
    else {
        $userID = $_SESSION['user-admin'];
        $sql = "SELECT * FROM admin_table WHERE adminID = '$userID;'";
        // Executing the sql query
        $result = mysqli_query($conn, $sql);
        if ($result == TRUE) {
            // For everything in the database, display
            $rows = mysqli_fetch_assoc($result);
            // Store database details in variables. 
            $username = $rows['username'];
        }
        else{
            header('location:'.SITEURL.'signin.php');
        }
    }
?>