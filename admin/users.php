<?php
// admin/users.php - MP2: Admin User Management
require_once "../includes/admin_check.php";
require_once "../includes/config.php";


$sql = "SELECT u.user_id, u.email, u.role, u.status, u.created_at, c.first_name, c.last_name 
        FROM users u 
        LEFT JOIN customers c ON u.user_id = c.user_id 
        ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $sql);

require_once "../includes/header.php";
?>

<div class="admin-items-wrapper">
    <h1 class="admin-items-title">User Management</h1>
    <p class="admin-items-subtitle">Update user roles and deactivate accounts</p>

    <?php require_once "../includes/alert.php"; ?>

    <div class="admin-items-box mt-4">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th class="text-start">User Email</th>
                    <th class="text-start">Customer Name</th>
                    <th class="text-center">Role</th>
                    <th class="text-center">Account Status</th>
                    <th class="text-center">Joined Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td style="font-family: 'Alinore', sans-serif; font-size: 1.15rem; color: #54220E;">
                            <?= htmlspecialchars($row['email']); ?>
                        </td>
                        <td style="font-family: 'Aqila', serif; font-size: 1.2rem; text-transform: uppercase;">
                            <?= htmlspecialchars(trim($row['first_name'] . ' ' . $row['last_name'])) ?: '<span style="color:#A8A29E; font-family:\'Alinore\';">No Profile Yet</span>'; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($row['role'] === 'admin'): ?>
                                <span class="badge" style="background-color: #4D6443; color: white;">Admin</span>
                            <?php else: ?>
                                <span class="badge" style="background-color: #E6E2D8; color: #54220E;">User</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($row['status'] === 'active'): ?>
                                <span class="badge" style="background-color: #D1FAE5; color: #065F46;">Active</span>
                            <?php else: ?>
                                <span class="badge" style="background-color: #FEE2E2; color: #991B1B;">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center" style="font-family: 'Alinore', sans-serif;">
                            <?= date('M j, Y', strtotime($row['created_at'])); ?>
                        </td>
                        <td class="text-end">
                            <?php if ((int)$row['user_id'] !== (int)$_SESSION['user_id']): ?>
                                
                                <button type="button" class="admin-action-btn" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $row['user_id']; ?>">
                                    <i class="fa-solid fa-user-pen"></i> Edit
                                </button>
                            <?php else: ?>
                                <span class="badge bg-secondary">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                   
                    <div class="modal fade custom-checkout-modal" id="editUserModal<?= $row['user_id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 border-secondary">
                                    <h4 class="modal-title mb-0">Update User</h4>
                                    <button type="button" class="modal-close-btn" data-bs-dismiss="modal">[CLOSE]</button>
                                </div>
                                <form method="POST" action="user_update.php">
                                    <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-white">User Email</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($row['email']); ?>" disabled>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-white">Account Role</label>
                                        <select name="role" class="form-select" style="cursor: pointer;">
                                            <option value="user" <?= $row['role'] === 'user' ? 'selected' : ''; ?>>Customer (User)</option>
                                            <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-white">Account Status</label>
                                        <select name="status" class="form-select" style="cursor: pointer;">
                                            <option value="active" <?= $row['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?= $row['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive (Deactivated)</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="modal-pill-btn w-100">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

   
    <div class="dashboard-actions-bar">
        <a href="dashboard.php" class="dashboard-btn-outline">
            &larr; Back to Dashboard
        </a>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
