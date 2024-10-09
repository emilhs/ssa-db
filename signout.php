<?php
    // Ensure that database connection is accessed in this file. 
    include('config/constants.php');

    // Destroy the session (which will unset the current session)
    session_destroy();

    // Redirect to login page. 
    header('location:'.SITEURL.'signin.php');
?>