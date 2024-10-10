<?php include('navbar.php'); ?>

<div class = "menuH">
    <p class = "bebas-neue darktext pagetitle">Submit Results to SSA Database</p>
    <form id="uploadForm" action="addcomp.php" method="post" enctype="multipart/form-data">
    
    <input type="file" id="fileInput" name="csv" style="display: none;">
    <button type="button" class = "bebas-neue darktext filesubmission" id="customButton">Select File</button>
    <span class = "fileout arimo medsize darktext" id="fileName"></span>
    
    <button class = "bebas-neue darktext filesubmission" type="submit" id="submitButton" name = "submit" disabled>Submit</button>
    </form>
</div>

<script>
document.getElementById('customButton').addEventListener('click', function() {
    document.getElementById('fileInput').click();
});

document.getElementById('fileInput').addEventListener('change', function() {
    const fileInput = document.getElementById('fileInput');
    const customButton = document.getElementById('customButton');
    const fileName = document.getElementById('fileName');
    const submitButton = document.getElementById('submitButton');

    if (fileInput.files.length > 0) {
        customButton.classList.remove('filesubmission');
        customButton.classList.add('filesubmission-selected');
        fileName.textContent = fileInput.files[0].name;
        submitButton.disabled = false;
    } else {
        customButton.classList.remove('filesubmission-selected');
        customButton.classList.add('filesubmission');
        fileName.textContent = '';
        submitButton.disabled = true;
    }
});
</script>

<?php include('../fixedfooter.php');