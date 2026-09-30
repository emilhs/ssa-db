<?php
// Shared header. Set $base = '../' when included from a subfolder, and $pageTitle to override the title.
$base = isset($base) ? $base : '';
$siteTitles = ['ranking.php'=>'Ranking List','series.php'=>'Circuit Ranking List','search.php'=>'Skater Search','competitions.php'=>'Competitions','clubs.php'=>'Clubs','clubpage.php'=>'Clubs'];
$page = basename($_SERVER['PHP_SELF']);
$siteTitle = isset($pageTitle) ? $pageTitle : (isset($siteTitles[$page]) ? $siteTitles[$page] : 'Results Database');
?>
<link rel="stylesheet" href="<?php echo $base; ?>css/header.css?v=<?php echo filemtime(__DIR__ . '/css/header.css'); ?>">
<div class = "header">
    <a href = "<?php echo $base; ?>index.php" class = "left">
        <img id = "homelogo" src="<?php echo $base; ?>images/TrimmedorgLogo2024.png" alt="Speed Skating Alberta">
        <p class = "bebas-neue" id = "title"><span class = "bluetext">SSA</span> <span class = "darktext"><?php echo $siteTitle; ?></span></p>
    </a>
    <?php if ($page !== 'index.php' || $base !== '') { ?><a class = "backlink" href = "<?php echo $base; ?>index.php">Home</a><?php } ?>
</div>
