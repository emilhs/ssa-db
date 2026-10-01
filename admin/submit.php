<?php include('navbar.php'); ?>
<link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime(__DIR__ . '/../css/admin.css'); ?>">

<main class = "admin-page admin-narrow">
    <h1 class = "admin-h1 bebas-neue">Add a Competition</h1>
    <p class = "admin-hint">Choose the results CSV file. You'll get to review everything before it is saved.</p>

    <form id = "uploadForm" action = "addcomp.php" method = "post" enctype = "multipart/form-data" class = "pw-card upload-card">
        <input type = "file" id = "fileInput" name = "csv" accept = ".csv,text/csv" style = "display: none;">
        <button type = "button" class = "pair-btn" id = "customButton">Select file</button>
        <span class = "upload-name" id = "fileName">No file chosen</span>
        <button class = "pair-btn primary" type = "submit" id = "submitButton" name = "submit" value = "1" disabled>Continue</button>
    </form>
</main>

<script>
document.getElementById('customButton').addEventListener('click', function () {
    document.getElementById('fileInput').click();
});
document.getElementById('fileInput').addEventListener('change', function () {
    const input = document.getElementById('fileInput');
    const has = input.files.length > 0;
    document.getElementById('fileName').textContent = has ? input.files[0].name : 'No file chosen';
    document.getElementById('customButton').classList.toggle('sel', has);
    document.getElementById('submitButton').disabled = !has;
});
</script>

<?php include('../footer.php'); ?>
