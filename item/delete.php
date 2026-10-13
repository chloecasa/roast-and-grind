<?php
// item/delete.php - FR-10: Delete Product & Inventory Record
require_once "../includes/config.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}

$item_id = intval($_GET['id']);


mysqli_begin_transaction($conn);

try {
   
    $sql_stock = "DELETE FROM stock WHERE item_id = ?";
    $stmt_stock = mysqli_prepare($conn, $sql_stock);
    mysqli_stmt_bind_param($stmt_stock, "i", $item_id);
    mysqli_stmt_execute($stmt_stock);
    mysqli_stmt_close($stmt_stock);

    
    $sql_item = "DELETE FROM item WHERE item_id = ?";
    $stmt_item = mysqli_prepare($conn, $sql_item);
    mysqli_stmt_bind_param($stmt_item, "i", $item_id);
    mysqli_stmt_execute($stmt_item);
    mysqli_stmt_close($stmt_item);

  
    mysqli_commit($conn);

    
    header("Location: ../index.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Error deleting product: " . $e->getMessage());
}
?>
