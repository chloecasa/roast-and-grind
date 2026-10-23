<?php
// user/profile.php - MP5: No HTML5 Validation / MP2: Upload/Update Photo
require_once "../includes/session.php";
require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please log in to view your profile.';
    header('Location: login.php');
    exit;
}
$user_id = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare($conn,
    "SELECT customer_id, first_name, last_name, address, town, postal_code, phone, profile_photo
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

    // MP5: Strict PHP Validation (Browser validation removed)
    $errors = [];
    if ($form['first_name'] === '' || mb_strlen($form['first_name']) > 50) {
        $errors[] = 'First name is required and cannot exceed 50 characters.';
    }
    if ($form['last_name'] === '' || mb_strlen($form['last_name']) > 50) {
        $errors[] = 'Last name is required and cannot exceed 50 characters.';
    }
    if ($form['address'] === '' || mb_strlen($form['address']) > 150) {
        $errors[] = 'Delivery address is required and cannot exceed 150 characters.';
    }
    if ($form['town'] === '' || mb_strlen($form['town']) > 50) {
        $errors[] = 'Town / City is required and cannot exceed 50 characters.';
    }
    if ($form['postal_code'] === '' || !preg_match('/^[A-Za-z0-9\- ]{3,10}$/', $form['postal_code'])) {
        $errors[] = 'Postal code is required (3 to 10 letters, numbers or dashes).';
    }
    if ($form['phone'] === '') {
        $errors[] = 'Contact number is required.';
    } else {
        $phone_digits = preg_replace('/\D/', '', $form['phone']);
        if (!preg_match('/^[0-9+\-\s()]+$/', $form['phone']) || strlen($phone_digits) < 7 || strlen($phone_digits) > 15 || strlen($form['phone']) > 20) {
            $errors[] = 'Contact number should have 7 to 15 digits (you can use +, spaces, dashes and brackets).';
        }
    }

    if ($errors) {
        $_SESSION['message'] = implode('<br>', $errors);
    } else {
        // Handle optional photo update
        $photo_query_part = "";
        $photo_filename = null;
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $new_name = time() . "_prof_" . $user_id . ".$ext";
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], __DIR__ . '/images/' . $new_name)) {
                    $photo_filename = $new_name;
                    $photo_query_part = ", profile_photo = ?";
                }
            }
        }

        if ($profile) {
            if ($photo_filename) {
                $sql = "UPDATE customers SET first_name=?, last_name=?, address=?, town=?, postal_code=?, phone=?, profile_photo=? WHERE user_id=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'sssssssi', $form['first_name'], $form['last_name'], $form['address'], $form['town'], $form['postal_code'], $form['phone'], $photo_filename, $user_id);
            } else {
                $sql = "UPDATE customers SET first_name=?, last_name=?, address=?, town=?, postal_code=?, phone=? WHERE user_id=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'ssssssi', $form['first_name'], $form['last_name'], $form['address'], $form['town'], $form['postal_code'], $form['phone'], $user_id);
            }
        } else {
            $photo_val = $photo_filename ?? 'default_avatar.png';
            $sql = "INSERT INTO customers (first_name, last_name, address, town, postal_code, phone, profile_photo, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sssssssi', $form['first_name'], $form['last_name'], $form['address'], $form['town'], $form['postal_code'], $form['phone'], $photo_val, $user_id);
        }
        mysqli_stmt_execute($stmt);

        $_SESSION['success'] = 'Profile saved successfully.';
        header('Location: profile.php');
        exit;
    }
}

$is_complete = ($form['first_name'] !== '' && $form['last_name'] !== '' && $form['address'] !== ''
    && $form['town'] !== '' && $form['postal_code'] !== '' && $form['phone'] !== '');

// Fetch image for display
$display_img = (!empty($profile['profile_photo']) && file_exists(__DIR__ . '/images/' . $profile['profile_photo']))
    ? "images/" . $profile['profile_photo']
    : "https://placehold.co/120x120/743014/FED7AA?text=User";

require_once "../includes/header.php";
?>

<div class="profile-wrapper">
    <h1 class="profile-title">My Profile</h1>
    <p class="profile-subtitle">Manage your default shipping address and contact details</p>

    <?php require_once "../includes/alert.php"; ?>

    <div class="profile-card">
        <?php if (!$is_complete && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
            <div class="profile-notice">
                <i class="fa-solid fa-circle-info"></i>
                <span>Please complete your delivery address so we can ship your coffee orders.</span>
            </div>
        <?php endif; ?>

        <!-- ENCTYPE added for photo upload -->
        <form action="profile.php" method="POST" enctype="multipart/form-data">

            <!-- Profile Picture Display & Upload -->
            <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom border-secondary">
                <img src="<?= htmlspecialchars($display_img); ?>" alt="Profile Photo" 
                     style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--arch-brown);">
                <div class="flex-grow-1">
                    <label for="profile_photo" class="profile-label">Update Profile Picture</label>
                    <input type="file" class="form-control profile-input" id="profile_photo" name="profile_photo" accept=".jpg,.jpeg,.png" style="padding: 10px 20px !important;">
                </div>
            </div>

            <!-- Account Info -->
            <div class="row mb-3">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="profile-label">Customer ID</label>
                    <input type="text" class="form-control profile-input" 
                           value="<?= $profile ? '#' . $profile['customer_id'] : 'Auto-assigned'; ?>" disabled>
                </div>
                <div class="col-md-8">
                    <label class="profile-label">Registered Email</label>
                    <input type="text" class="form-control profile-input" 
                           value="<?= htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" disabled>
                </div>
            </div>

            <!-- MP5: NO HTML5 REQUIRED/MAXLENGTH ATTRIBUTES BELOW -->
            <div class="row mb-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="first_name" class="profile-label">First Name</label>
                    <input type="text" class="form-control profile-input" id="first_name" name="first_name" 
                           value="<?= htmlspecialchars($form['first_name']); ?>" placeholder="e.g. Juan">
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="profile-label">Last Name</label>
                    <input type="text" class="form-control profile-input" id="last_name" name="last_name" 
                           value="<?= htmlspecialchars($form['last_name']); ?>" placeholder="e.g. Dela Cruz">
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="profile-label">Street Address / Unit</label>
                <input type="text" class="form-control profile-input" id="address" name="address" 
                       value="<?= htmlspecialchars($form['address']); ?>" placeholder="e.g. Block 5 Lot 2, Pine Street">
            </div>

            <div class="row mb-3">
                <div class="col-md-7 mb-3 mb-md-0">
                    <label for="town" class="profile-label">Town / City</label>
                    <input type="text" class="form-control profile-input" id="town" name="town" 
                           value="<?= htmlspecialchars($form['town']); ?>" placeholder="e.g. Baguio City">
                </div>
                <div class="col-md-5">
                    <label for="postal_code" class="profile-label">Postal Code</label>
                    <input type="text" class="form-control profile-input" id="postal_code" name="postal_code" 
                           value="<?= htmlspecialchars($form['postal_code']); ?>" placeholder="e.g. 2600">
                </div>
            </div>

            <div class="mb-4">
                <label for="phone" class="profile-label">Contact Phone Number</label>
                <input type="text" class="form-control profile-input" id="phone" name="phone" 
                       value="<?= htmlspecialchars($form['phone']); ?>" placeholder="e.g. 0917-123-4567">
            </div>

            <button type="submit" class="profile-submit-btn">
                Save Profile
            </button>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
