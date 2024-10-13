<?php include('navbar.php'); 

//Check whether the submit button on the form is clicked
if(isset($_POST['submit'])) {
      // Gather all of the login form inputs
      $username = $_POST['usr'];
      $password = md5($_POST['pwd']);

      // Run a SQL statement to check if username and password exists within the admin table
      $sql = "SELECT * FROM admin_table WHERE username = '$username' AND password = '$password';";
      // Execute SQL Query
      $result = mysqli_query($conn, $sql);
      // Count the number of rows to see if entry exists
      $count = mysqli_num_rows($result);
  
      if($count == 1) {
        $row = mysqli_fetch_assoc($result);
        $adminID = $row['adminID'];
        // User available and Login Successful
        $_SESSION['user-admin'] = $adminID; // To check whether the user is logging in or not and logout will unset it. 
        // Redirect to Admin Home
        header('location:'.SITEURL.'admin/');
        die();
      }
}
?>
<div class = "menuH">
        <p class = "bebas-neue darktext pagetitle">Admin Sign-In</p>
        <form action="signin.php" method="post" enctype="multipart/form-data">
                <p class = "bebas-neue darktext text-center medsize">Username:</p>
                <input class = "arimo login" type = "text" name = "usr"></input>
                <p class = "bebas-neue darktext text-center medsize">Password:</p>
                <input class = "arimo login" type = "password" name = "pwd"></input>
                <br>
                <br>
                <input class = "bebas-neue enterbtn darktext" type = "submit" value="Sign in as admin" name="submit"></input>
        </form>
</div>
<?php 
include('footer.php'); ?>


