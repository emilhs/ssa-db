<?php include('navbar.php'); 

//Check whether the submit button on the form is clicked
if(isset($_POST['submit'])) {
      // Gather all of the login form inputs
      $username = $_POST['usr'];
      $password = $_POST['pwd'];

      // Look the admin up by username (prepared statement)
      $stmt = mysqli_prepare($conn, "SELECT adminID, password FROM admin_table WHERE username = ?;");
      mysqli_stmt_bind_param($stmt, 's', $username);
      mysqli_stmt_execute($stmt);
      $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

      if ($row) {
        $stored = $row['password'];
        // Newer passwords use password_hash(); older ones are plain MD5 and are upgraded on a successful login
        $isModern = (strlen($stored) > 0 && $stored[0] === '$');
        $valid = $isModern ? password_verify($password, $stored) : hash_equals($stored, md5($password));
        if ($valid) {
          $adminID = $row['adminID'];
          if (!$isModern) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $up = mysqli_prepare($conn, "UPDATE admin_table SET password = ? WHERE adminID = ?;");
            mysqli_stmt_bind_param($up, 'si', $newHash, $adminID);
            mysqli_stmt_execute($up);
          }
          if (!headers_sent()) { session_regenerate_id(true); }
          $_SESSION['user-admin'] = $adminID; // To check whether the user is logging in or not and logout will unset it.
          // Redirect to Admin Home
          header('Location: admin/index.php');
          die();
        }
      }
}
if (isset($_POST['submit'])) {
    // Reaching this point means the sign-in did not succeed
    $loginError = 'Incorrect username or password.';
}
?>
<link rel="stylesheet" href="css/search.css?v=<?php echo filemtime(__DIR__ . '/css/search.css'); ?>">

<main class = "search-page">
    <div class = "search-card signin-card">
        <div class = "search-card-title bebas-neue">Sign In</div>
        <form action = "signin.php" method = "post" class = "signin-form">
            <?php if (!empty($loginError)) { ?><p class = "signin-error"><?php echo htmlspecialchars($loginError); ?></p><?php } ?>
            <label>Username
                <input type = "text" name = "usr" autocomplete = "username" autocapitalize = "off" required autofocus>
            </label>
            <label>Password
                <input type = "password" name = "pwd" autocomplete = "current-password" required>
            </label>
            <button class = "wide-btn bebas-neue" type = "submit" name = "submit" value = "1">Sign In</button>
        </form>
    </div>
</main>

<?php include('footer.php'); ?>
