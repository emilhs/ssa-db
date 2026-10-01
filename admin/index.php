<?php include('navbar.php'); ?>

<main class = "admin-home">
    <div class = "admin-welcome">
        <span>Signed in as <strong><?php echo htmlspecialchars($username); ?></strong></span>
        <span class = "admin-welcome-links"><a href = "password.php">Change Password</a><a href = "../index.php">View Public Site</a><a class = "admin-signout" href = "../signout.php">Sign Out</a></span>
    </div>
    <div class = "admin-grid">
        <?php foreach ($adminSections as $title => $links) { ?>
        <section class = "admin-card">
            <div class = "admin-card-title bebas-neue"><?php echo $title; ?></div>
            <?php foreach ($links as $l) { ?>
                <a class = "admin-link" href = "<?php echo $l[0]; ?>"><span><?php echo $l[1]; ?></span><?php if ($l[0] === 'checks.php' && $checkCount > 0) { ?><span class = "admin-badge soft"><?php echo $checkCount; ?></span><?php } ?></a>
            <?php } ?>
        </section>
        <?php } ?>
    </div>
</main>

<?php include('../footer.php'); ?>
