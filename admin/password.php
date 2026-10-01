<?php
include_once('../config/constants.php');
include_once('../config/functions.php');
include_once('VerifyAdminLogin.php');
include_once('lib.php');

$error = '';
$done = false;

if (isset($_POST['change'])) {
    $old = $_POST['old'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT password FROM admin_table WHERE adminID = ?;");
    $adminID = (int)$userID;
    mysqli_stmt_bind_param($stmt, 'i', $adminID);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $stored = $row ? $row['password'] : '';
    $isModern = (strlen($stored) > 0 && $stored[0] === '$');
    $oldOk = $row && ($isModern ? password_verify($old, $stored) : hash_equals($stored, md5($old)));

    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $error = 'Your session expired, please try again.';
    } elseif (!$oldOk) {
        $error = 'Your current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'The new password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = "The new passwords don't match.";
    } elseif ($new === $old) {
        $error = 'The new password must be different from your current one.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $up = mysqli_prepare($conn, "UPDATE admin_table SET password = ? WHERE adminID = ?;");
        mysqli_stmt_bind_param($up, 'si', $hash, $adminID);
        mysqli_stmt_execute($up);
        session_regenerate_id(true);
        $done = true;
    }
}
$token = csrf_token();

include('navbar.php');
?>
<link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime(__DIR__ . '/../css/admin.css'); ?>">

<main class = "admin-page admin-narrow">
    <h1 class = "admin-h1 bebas-neue">Change Password</h1>
    <p class = "admin-hint">Signed in as <strong><?php echo htmlspecialchars($username); ?></strong></p>

    <?php if ($done) { ?><p class = "admin-msg ok">Your password has been changed.</p><?php } ?>
    <?php if ($error) { ?><p class = "admin-msg err"><?php echo htmlspecialchars($error); ?></p><?php } ?>

    <form method = "post" class = "pw-card" autocomplete = "off">
        <input type = "hidden" name = "csrf" value = "<?php echo $token; ?>">
        <label>Current password
            <input type = "password" name = "old" autocomplete = "current-password" required>
        </label>
        <label>New password <small>at least 8 characters</small>
            <input type = "password" name = "new" autocomplete = "new-password" minlength = "8" required>
        </label>
        <label>Confirm new password
            <input type = "password" name = "confirm" autocomplete = "new-password" minlength = "8" required>
        </label>
        <button class = "pair-btn primary" type = "submit" name = "change" value = "1">Change Password</button>
    </form>
</main>

<?php include('../footer.php'); ?>
