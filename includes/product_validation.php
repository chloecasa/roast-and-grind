<?php
// includes/product_validation.php - server-side checks shared by item/store.php (FR-8) and item/update.php (FR-9, FR-14)

const PRODUCT_CATEGORIES = ['Coffee Beans', 'Drip Bags', 'Brewing Gear'];   // FR-12: one category per product
const PRODUCT_IMAGE_MAX_BYTES = 5 * 1024 * 1024;                            // 5 MB per image

// Checks the text and number fields. Returns [list of error messages, cleaned values].
function validateProductFields(array $in)
{
    $errors = [];

    $name        = trim($in['item_name'] ?? '');
    $category    = trim($in['category'] ?? '');
    $origin      = trim($in['origin'] ?? '');
    $description = trim($in['description'] ?? '');
    $cost        = trim($in['cost_price'] ?? '');
    $sell        = trim($in['sell_price'] ?? '');
    $quantity    = trim($in['quantity'] ?? '');

    if ($name === '' || mb_strlen($name) > 150) {
        $errors[] = 'Product name is required (max. 150 characters).';
    }
    if (!in_array($category, PRODUCT_CATEGORIES, true)) {
        $errors[] = 'Please choose a valid category.';
    }
    if ($origin === '' || mb_strlen($origin) > 100) {
        $errors[] = 'Origin is required (max. 100 characters). Use N/A for gear.';
    }
    if ($description === '' || mb_strlen($description) > 2000) {
        $errors[] = 'Description is required (max. 2000 characters).';
    }
    // money: zero or more, up to 2 decimal places
    if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $cost)) {
        $errors[] = 'Cost price must be a number that is 0 or more (up to 2 decimals).';
    }
    if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $sell) || (float) $sell <= 0) {
        $errors[] = 'Selling price must be a number greater than 0 (up to 2 decimals).';
    }
    // stock: a whole number, 0 or more
    if (!preg_match('/^\d{1,7}$/', $quantity)) {
        $errors[] = 'Stock quantity must be a whole number that is 0 or more.';
    }

    $clean = [
        'item_name'   => $name,
        'category'    => $category,
        'origin'      => $origin,
        'description' => $description,
        'cost_price'  => (float) $cost,
        'sell_price'  => (float) $sell,
        'quantity'    => (int) $quantity
    ];
    return [$errors, $clean];
}

// Checks one uploaded image. Returns an error message, or null when the file is fine.
// Both the file extension and the real file content must be JPG/JPEG or PNG.
function validateImageUpload($file, $label)
{
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "$label is too large (max. 5 MB).";
        case UPLOAD_ERR_PARTIAL:
            return "$label was only partly uploaded. Please try again.";
        default:
            return "$label could not be uploaded.";
    }

    if ($file['size'] > PRODUCT_IMAGE_MAX_BYTES) {
        return "$label is too large (max. 5 MB).";
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
        return "$label must be a JPG, JPEG or PNG file.";
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        return "$label is not a valid JPG or PNG image.";
    }
    return null;
}

// Safe stored file name: time + original name with odd characters replaced
function productImageName($prefix, $originalName)
{
    return time() . "_" . $prefix . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
}