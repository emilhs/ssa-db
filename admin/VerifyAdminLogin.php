<?php
    // Check Authorization: no session means no access, and nothing below this include may run
    if(!isset($_SESSION['user-admin'])) {
        header('location: ../signin.php');
        exit;
    }
    $userID = (int)$_SESSION['user-admin'];
    $stmtVerify = mysqli_prepare($conn, "SELECT username FROM admin_table WHERE adminID = ?;");
    mysqli_stmt_bind_param($stmtVerify, 'i', $userID);
    mysqli_stmt_execute($stmtVerify);
    $rowVerify = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtVerify));
    if (!$rowVerify) {
        unset($_SESSION['user-admin']);
        header('location: ../signin.php');
        exit;
    }
    $username = $rowVerify['username'];
?>