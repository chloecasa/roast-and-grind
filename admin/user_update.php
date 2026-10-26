<?php
// admin/user_update.php - MP2: Process User Role & Status updates
require_once "../includes/admin_check.php";
require_once "../includes/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_user_id = (int)$_POST['user_id'];
    $new_role       = $_POST['role'];
    $new_status     = $_POST['status'];

  
    if ($target_user_id === (int)$_SESSION['user_id']) {
        $_SESSION['message'] = "Action denied: You cannot change your own role or status.";
    } else {
       
        $stmt = mysqli_prepare($conn, "UPDATE users SET role = ?, status = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $new_role, $new_status, $target_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "User account updated successfully.";
        } else {
            $_SESSION['message'] = "Database error: Could not update user.";
        }
    }
}

header("Location: users.php");
exit;
