<?php include_once('../config/constants.php'); ?>
<?php include_once('../config/functions.php'); ?>
<?php include_once('VerifyAdminLogin.php'); ?>
<?php
$adminPage = basename($_SERVER['PHP_SELF']);
$pageTitle = 'Admin';
$base = '../';
$backHref = 'index.php';
$backLabel = 'Admin Home';
$hideBack = ($adminPage === 'index.php');

// Skaters / competitions that need attention (shown as a badge on Skater Data Checks)
include_once('lib.php');
$checkCount = count(attention_skaters($conn)) + count(bad_comps($conn));

// Every admin page. Add a line here to add it to the admin home and the toolbar.
$adminSections = array(
    'Competitions' => array(
        array('submit.php', 'Add a Competition'),
        array('viewcomps.php', 'Edit Competitions'),
    ),
    'Skaters' => array(
        array('viewskaters.php', 'Edit Skaters and Results'),
        array('merge.php', 'Merge Duplicates'),
        array('checks.php', 'Skater Data Checks'),
    ),
    'Clubs' => array(
        array('viewclubs.php', 'Assign Province'),
    ),
    'Admins' => array(
        array('addadmin.php', 'Add another Admin'),
    ),
    'Backup' => array(
        array('export.php', 'Export Results (CSV)'),
        array('lighten.php', 'Lighten Database'),
    ),
);
?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SSA Admin</title>
        <link rel="stylesheet" href="../css/style.css?v=<?php echo filemtime(__DIR__ . '/../css/style.css'); ?>">
        <link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime(__DIR__ . '/../css/admin.css'); ?>">
    </head>
    <?php include('../header.php'); ?>
    <?php if ($adminPage !== 'index.php') { ?>
    <nav class = "admin-bar">
        <div class = "admin-bar-links">
            <?php foreach ($adminSections as $links) { foreach ($links as $l) { ?>
                <a class = "<?php echo ($adminPage === $l[0]) ? 'active' : ''; ?>" href = "<?php echo $l[0]; ?>"><?php echo $l[1]; ?><?php if ($l[0] === 'checks.php' && $checkCount > 0) { ?><span class = "admin-badge soft"><?php echo $checkCount; ?></span><?php } ?></a>
            <?php } } ?>
        </div>
        <div class = "admin-bar-user">
            <span>Signed in as <strong><?php echo htmlspecialchars($username); ?></strong></span>
            <a class = "<?php echo ($adminPage === 'password.php') ? 'active-user' : ''; ?>" href = "password.php">Change Password</a>
            <a href = "../index.php">Public Site</a>
            <a class = "admin-signout" href = "../signout.php">Sign Out</a>
        </div>
    </nav>
    <?php } ?>
</html>
<?php
header('Content-Type: text/html; charset=utf-8');
