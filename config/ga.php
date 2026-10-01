<?php
// Google Analytics 4 (visitor numbers).
// 1. In Google Analytics create a GA4 property and a Web data stream for results.speedskatingalberta.ca
// 2. Copy its Measurement ID (it looks like G-ABC123XYZ4) and paste it between the quotes below.
// Leave it as it is (or empty) to keep tracking switched off.
$GA_MEASUREMENT_ID = 'G-FH9Y18RJE8';

// Prints the tracking snippet. Call it inside <head>. Admin pages never include it, and a signed-in admin
// browsing the public pages isn't counted.
function ga_tag() {
    global $GA_MEASUREMENT_ID;
    if (!preg_match('/^G-[A-Z0-9]{6,12}$/', (string)$GA_MEASUREMENT_ID) || $GA_MEASUREMENT_ID === 'G-XXXXXXXXXX') return;
    if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user-admin'])) return;
    ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $GA_MEASUREMENT_ID; ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo $GA_MEASUREMENT_ID; ?>');
    </script>
    <?php
}
