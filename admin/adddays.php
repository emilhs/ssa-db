<?php include('navbar.php'); ?>

<html>
<body>
<p>The following competition will be added to the database:</p>
<?php 
if (isset($_POST["csv"]) ) {
    echo $_POST["csv"];
}
else {
    echo "No file";
}
?>

<form action="adddays.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="csv" id="csv" value = <?php echo $_FILES["csv"];?>>
  <input type="submit" value="Upload Results" name="submit">
</form>

</body>
</html>