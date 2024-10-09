<?php include('navbar.php'); ?>

<div class = "menuH">
    <p class = "bebas-neue darktext pagetitle">Submit Results to SSA Database</p>
    <form action="addcomp.php" method="post" enctype="multipart/form-data">
    <p class = "arimo darktext smallsize">Add a valid .csv of results:</p>
    <div class = "filesubmission">
        <input class = "arimo darktext smallsize" type="file" name="csv" id="csv">
    </div>
    <input class = "bebas-neue enterbtn darktext" type="submit" value="Upload Results" name="submit">
    </form>
</div>

<?php include('../fixedfooter.php');