<?php
// user/profile.php - FR-5: a logged-in customer views and updates their profile
include('../includes/session.php');
include('../includes/config.php');

// FR-3: customer pages are for logged-in users only
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please log in to view your profile.';
    header('Location: login.php');
    exit;
}
$user_id = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare($conn,
    "SELECT customer_id, first_name, last_name, address, town, postal_code, phone
     FROM customers WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$profile = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$fields = ['first_name', 'last_name', 'address', 'town', 'postal_code', 'phone'];
$form = [];
foreach ($fields as $f) {
    $form[$f] = $profile[$f] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $f) {
        $form[$f] = trim($_POST[$f] ?? '');
    }

    $errors = [];
    if ($form['first_name'] === '' || mb_strlen($form['first_name']) > 50) {
        $errors[] = 'First name is required (max. 50 characters).';
    }
    if ($form['last_name'] === '' || mb_strlen($form['last_name']) > 50) {
        $errors[] = 'Last name is required (max. 50 characters).';
    }
    if ($form['address'] === '' || mb_strlen($form['address']) > 150) {
        $errors[] = 'Delivery address is required (max. 150 characters).';
    }
    if ($form['town'] === '' || mb_strlen($form['town']) > 50) {
        $errors[] = 'Town / City is required (max. 50 characters).';
    }
    if (!preg_match('/^[A-Za-z0-9\- ]{3,10}$/', $form['postal_code'])) {
        $errors[] = 'Postal code should be 3 to 10 letters, numbers or dashes.';
    }
    $phone_digits = preg_replace('/\D/', '', $form['phone']);
    if (!preg_match('/^[0-9+\-\s()]+$/', $form['phone']) || strlen($phone_digits) < 7 || strlen($phone_digits) > 15 || strlen($form['phone']) > 20) {
        $errors[] = 'Contact number should have 7 to 15 digits (you can use +, spaces, dashes and brackets).';
    }

    if ($errors) {
        // keep what they typed; alert.php shows the messages
        $_SESSION['message'] = implode('<br>', $errors);
    } else {
        if ($profile) {
            $sql  = "UPDATE customers
                     SET first_name = ?, last_name = ?, address = ?, town = ?, postal_code = ?, phone = ?
                     WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ssssssi', $form['first_name'], $form['last_name'], $form['address'],
                $form['town'], $form['postal_code'], $form['phone'], $user_id);
        } else {
            $sql  = "INSERT INTO customers (first_name, last_name, address, town, postal_code, phone, user_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ssssssi', $form['first_name'], $form['last_name'], $form['address'],
                $form['town'], $form['postal_code'], $form['phone'], $user_id);
        }
        mysqli_stmt_execute($stmt);

        $_SESSION['success'] = 'Profile saved.';
        header('Location: profile.php');
        exit;
    }
}

$is_complete = ($form['first_name'] !== '' && $form['last_name'] !== '' && $form['address'] !== ''
    && $form['town'] !== '' && $form['postal_code'] !== '' && $form['phone'] !== '');

include('../includes/header.php');
?>

<h1 align="center" class="mb-4">My Profile</h1>

<div class="row col-md-6 mx-auto">
    <?php include('../includes/alert.php'); ?>

    <?php if (!$is_complete && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
        <p class="text-muted">Please complete your profile so we know where to deliver your orders.</p>
    <?php endif; ?>

    <form action="profile.php" method="POST">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Customer ID</label>
                <input type="text" class="form-control" value="<?= $profile ? '#' . $profile['customer_id'] : 'Assigned when you save'; ?>" disabled>
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label">Email</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" disabled>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" maxlength="50"
                       value="<?= htmlspecialchars($form['first_name']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" maxlength="50"
                       value="<?= htmlspecialchars($form['last_name']); ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Delivery Address</label>
            <input type="text" class="form-control" id="address" name="address" maxlength="150"
                   value="<?= htmlspecialchars($form['address']); ?>" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="town" class="form-label">Town / City</label>
                <input type="text" class="form-control" id="town" name="town" maxlength="50"
                       value="<?= htmlspecialchars($form['town']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="postal_code" class="form-label">Postal Code</label>
                <input type="text" class="form-control" id="postal_code" name="postal_code" maxlength="10"
                       value="<?= htmlspecialchars($form['postal_code']); ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Contact Number</label>
            <input type="tel" class="form-control" id="phone" name="phone" maxlength="20"
                   value="<?= htmlspecialchars($form['phone']); ?>" required>
        </div>

        <button type="submit" class="btn btn-dark w-100">Save Profile</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>