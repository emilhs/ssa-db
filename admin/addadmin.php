<?php include('navbar.php'); ?>

<div class = "menuH">
        <p class = "bebas-neue darktext pagetitle">Add Administrator</p>
        <form action="addadmin.php" method="post" enctype="multipart/form-data">
                <p class = "bebas-neue darktext text-center medsize">Username:</p>
                <input class = "arimo login" type = "text" name = "usr"></input>
                <p class = "bebas-neue darktext text-center medsize">Password:</p>
                <input class = "arimo login" type = "password" name = "pwd"></input>
                <br>
                <br>
                <input class = "bebas-neue enterbtn darktext" type = "submit" value="Create Admin" name="submit"></input>
        </form>
</div>
<?php
// Using PHP to process the form and add items into the database
                        // If the button was clicked then
                        if(isset($_POST['submit'])) {
                            // Get the data from database
                            $username = $_POST['usr'];
                            $password = md5($_POST['pwd']); //Code to encrypt password

                            //Block of code to check if username is already in database, if it is, then return an error message and end the process. 
                            $sql2 = "SELECT * FROM admin_table WHERE username = '$username';";

                            // Execute this query
                            $result2 = mysqli_query($conn, $sql2) or die(mysqli_error());

                            // Count the number of rows, if it is greater than 0 then return the error message and end the process.
                            $count = mysqli_num_rows($result2);
                            if($count == 1) {
                                // Means that username already exists in the database and this is not a valid username. 
                                $_SESSION['already-exists'] = "<div class='error'>The username you have selected already exists, please enter a new username</div>";
                                // Redirect to add-admin page
                                header('location:'.SITEURL.'Administrator/AddAdministrator.php');
                                // Stop the process
                                die();
                            }

                            // SQL Query to insert a new admin into the database
                            $sql = "INSERT INTO admin_table SET
                            username = '$username',
                            password = '$password';";

                            // Executing the query
                            $result = mysqli_query($conn, $sql) or die(mysqli_error());
                        }
?>

<?php include ('../fixedfooter.php');