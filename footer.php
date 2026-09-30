<?php $page = basename($_SERVER['PHP_SELF']); $base = isset($base) ? $base : ''; ?>
<link rel="stylesheet" href="<?php echo $base; ?>css/footer.css">
<footer class = "site-footer">
    <div class = "site-footer-links">
        <?php if ($page !== 'about.php') { ?><a href = "<?php echo $base; ?>about.php">About</a><?php } ?>
        <?php if ($page !== 'signin.php') { ?><a href = "<?php echo $base; ?>signin.php">Login</a><?php } ?>
    </div>
    <p class = "site-footer-byline">
        &copy; Emil Hodzic-Santor for Speed Skating Alberta, <?php echo date('Y'); ?><br>
        Photos: <a href = "https://www.luvcanphotography.com" target = "_blank" rel = "noopener"><u>LuvCan Photography</u></a>
    </p>
</footer>
<?php if (isset($conn)) { mysql_close($conn); }
