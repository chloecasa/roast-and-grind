<?php

require_once "includes/session.php";
require_once "includes/config.php";


if (!isset($_SESSION['cart_products']) || count($_SESSION['cart_products']) == 0) {
    $_SESSION['message'] = 'Your cart is empty.';
    header('Location: shop.php');
    exit;
}


$sql = "SELECT i.item_name, i.sell_price, i.img_path, s.quantity AS qty
        FROM item i INNER JOIN stock s USING (item_id)
        WHERE i.item_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $item_id);

$problems = [];
$lines    = [];
$total    = 0;

foreach ($_SESSION['cart_products'] as $cart_itm) {
    $item_id = (int) $cart_itm['item_id'];
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $name = htmlspecialchars($cart_itm['item_name']);

    if (!$row) {
        $problems[] = "$name is no longer available. Please remove it from your cart.";
    } elseif ($cart_itm['item_qty'] > $row['qty']) {
        $problems[] = "Not enough stock for $name: only {$row['qty']} left, but you have {$cart_itm['item_qty']} in your cart.";
    } else {
        $subtotal = $row['sell_price'] * $cart_itm['item_qty'];
        $total   += $subtotal;
        $lines[]  = [
            'id'       => $item_id,
            'name'     => $row['item_name'], 
            'price'    => $row['sell_price'], 
            'qty'      => $cart_itm['item_qty'], 
            'subtotal' => $subtotal,
            'img'      => $row['img_path']
        ];
    }
}

if ($problems) {
    $_SESSION['message'] = implode('<br>', $problems) . '<br>Order not placed. Please update your cart.';
    header('Location: shop.php?cart=open');
    exit;
}


$customer = null;
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    $cust_q = mysqli_query($conn, "SELECT * FROM customers WHERE user_id = " . intval($_SESSION['user_id']) . " LIMIT 1");
    if ($cust_q && mysqli_num_rows($cust_q) > 0) {
        $customer = mysqli_fetch_assoc($cust_q);
    }
}

include('includes/header.php');
?>

<div class="checkout-wrapper">
    <h1 class="checkout-title">Review & Checkout</h1>

    <?php include('includes/alert.php'); ?>

    <div class="row g-4">
       
        <div class="col-lg-7">
            <div class="checkout-card">
                <div class="checkout-card-header d-flex justify-content-between align-items-center">
                    <span>Order Summary</span>
                    <a href="shop.php?cart=open" style="font-family: 'Alinore', sans-serif; color: var(--arch-brown); font-size: 1.15rem; text-decoration: underline;">
                        Edit Cart
                    </a>
                </div>

                
                <?php foreach ($lines as $l): 
                    $imgSrc = "item/images/" . $l['img'];
                    if (!file_exists($imgSrc) || empty($l['img'])) {
                        $imgSrc = "https://placehold.co/180x180/EDEAE1/743014?text=" . urlencode($l['name']);
                    }
                ?>
                    <div class="checkout-item-row">
                        <img src="<?= $imgSrc; ?>" alt="<?= htmlspecialchars($l['name']); ?>" class="checkout-item-img">
                        <div>
                            <h4 class="checkout-item-name"><?= htmlspecialchars($l['name']); ?></h4>
                            <div class="checkout-item-sub">
                                ₱<?= number_format($l['price'], 2); ?> &times; <?= $l['qty']; ?>
                            </div>
                        </div>
                        <div class="checkout-item-total">
                            ₱<?= number_format($l['subtotal'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>

               
                <div class="d-flex justify-content-between align-items-center  pt-3 mt-4">
                    <span style="font-family: 'Alinore', sans-serif; font-size: 1.5rem; text-transform: uppercase; color: var(--text-brown);">Amount Payable:</span>
                    <span style="font-family: 'Alinore', sans-serif; font-size: 2.2rem; font-weight: bold; color: #4D6443;">
                        ₱<?= number_format($total, 2); ?>
                    </span>
                </div>
            </div>
        </div>

        
        <div class="col-lg-5">
            <div class="checkout-card">
                <div class="checkout-card-header">Delivery & Payment</div>

            
                <div class="bg-white rounded-3 p-3 mb-4 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-family: 'Alinore', sans-serif; font-size: 1.1rem; color: #78716C; text-transform: uppercase;">
                            Shipping Address
                        </span>
                        <?php if ($isLoggedIn): ?>
                            <a href="user/profile.php" style="font-family: 'Alinore', sans-serif; font-size: 0.95rem; color: var(--arch-brown);">[EDIT]</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($isLoggedIn && $customer && !empty($customer['address'])): ?>
                        <div style="font-weight: bold; color: var(--text-brown); font-size: 1.15rem;">
                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                        </div>
                        <div style="color: #57534E; font-size: 0.95rem; line-height: 1.4;">
                            <?= htmlspecialchars($customer['address'] . ', ' . $customer['town'] . ' ' . $customer['postal_code']); ?><br>
                            Phone: <?= htmlspecialchars($customer['phone']); ?>
                        </div>
                    <?php elseif ($isLoggedIn): ?>
                        <div class="text-danger small mb-1">No delivery address saved yet!</div>
                        <a href="user/profile.php" class="btn btn-sm btn-outline-dark" style="font-family: 'Alinore', sans-serif;">
                            Add Shipping Address
                        </a>
                    <?php else: ?>
                       
                        <div style="color: #78716C; font-size: 0.95rem;">
                            <span class="badge bg-warning text-dark mb-1">Guest Customer</span><br>
                            An account is required to dispatch your order.
                        </div>
                    <?php endif; ?>
                </div>

             
                <form method="POST" action="place_order.php" id="orderForm">
                    <input type="hidden" name="payment_method" id="paymentMethodInput" value="Cash on Delivery">

                    <label class="form-label fw-bold" style="font-family: 'Alinore', sans-serif; font-size: 1.15rem; color: var(--text-brown); text-transform: uppercase;">
                        Select Payment Method
                    </label>

               
                    <div class="payment-option-card active" id="cardCOD" onclick="selectPayment('Cash on Delivery')">
                        <div>
                            <h5 class="payment-opt-title"><i class="fa-solid fa-money-bill-wave me-2 text-success"></i>Cash on Delivery</h5>
                            <p class="payment-opt-desc">Pay cash upon parcel arrival</p>
                        </div>
                        <i class="fa-solid fa-check-circle fs-4 text-success" id="checkCOD"></i>
                    </div>

                
                    <div class="payment-option-card" id="cardGCash" onclick="openPaymentModal('gcashModal')">
                        <div>
                            <h5 class="payment-opt-title"><i class="fa-solid fa-mobile-screen me-2 text-primary"></i>GCash</h5>
                            <p class="payment-opt-desc" id="descGCash">Click to view store GCash & enter reference no.</p>
                        </div>
                        <i class="fa-solid fa-circle text-muted fs-4" id="checkGCash"></i>
                    </div>

                
                    <div class="payment-option-card" id="cardBank" onclick="openPaymentModal('bankModal')">
                        <div>
                            <h5 class="payment-opt-title"><i class="fa-solid fa-building-columns me-2 text-warning"></i>Bank Transfer</h5>
                            <p class="payment-opt-desc" id="descBank">BDO / BPI Bank Transfer</p>
                        </div>
                        <i class="fa-solid fa-circle text-muted fs-4" id="checkBank"></i>
                    </div>

                 
                    <div class="payment-option-card" id="cardCredit" onclick="openPaymentModal('cardModal')">
                        <div>
                            <h5 class="payment-opt-title"><i class="fa-regular fa-credit-card me-2 text-info"></i>Credit / Debit Card</h5>
                            <p class="payment-opt-desc" id="descCredit">Visa / Mastercard</p>
                        </div>
                        <i class="fa-solid fa-circle text-muted fs-4" id="checkCredit"></i>
                    </div>

                
                    <?php if ($isLoggedIn): ?>
                        <button type="submit" class="checkout-submit-btn">
                            Place Order • ₱<?= number_format($total, 2); ?>
                        </button>
                    <?php else: ?>
                        <button type="button" class="checkout-submit-btn" data-bs-toggle="modal" data-bs-target="#authRequiredModal">
                            Place Order • ₱<?= number_format($total, 2); ?>
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade custom-checkout-modal" id="authRequiredModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="d-flex justify-content-end">
                <button type="button" class="modal-close-btn" data-bs-dismiss="modal">[CLOSE]</button>
            </div>
            <i class="fa-solid fa-mug-hot fa-3x mb-3 text-warning"></i>
            <h3 class="modal-title mb-2">Account Required</h3>
            <p style="font-family: 'Alinore', sans-serif; font-size: 1.2rem; color: #E6D8CC; line-height: 1.5;" class="mb-4">
                Please log in or register an account so we know your delivery address and contact number to dispatch your order.
                <br><strong class="text-white">Your cart items are safely kept!</strong>
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="user/login.php" class="modal-pill-btn">Log In</a>
                <a href="user/register.php" class="modal-pill-btn" style="background-color: #3F5E38; color: #FFF;">Register</a>
            </div>
        </div>
    </div>
</div>


<div class="modal fade custom-checkout-modal" id="gcashModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 border-secondary">
                <h4 class="modal-title mb-0"><i class="fa-solid fa-mobile-screen me-2 text-warning"></i>GCash Payment</h4>
                <button type="button" class="modal-close-btn" data-bs-dismiss="modal">[CLOSE]</button>
            </div>

            <!-- Clear High-Contrast Amount Box -->
            <div class="p-3 rounded-3 mb-3" style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.1);">
                <div style="color: #FED7AA; font-family: 'Alinore', sans-serif; font-size: 1.05rem; letter-spacing: 0.5px;">
                    TOTAL AMOUNT TO SEND:
                </div>
                <div style="font-family: 'Alinore', sans-serif; font-size: 2.2rem; color: #BEF264; font-weight: bold;">
                    ₱<?= number_format($total, 2); ?>
                </div>
                
                <div class="mt-3 pt-2 border-top border-secondary text-white small" style="line-height: 1.6;">
                    <span style="color: #FDBA74; font-family: 'Alinore'; text-transform: uppercase;">Store Receiver Account:</span><br>
                    <strong>Merchant Name:</strong> ROAST & GRIND COFFEE SHOP<br>
                    <strong>GCash Number:</strong> 0917-123-4567
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-white">Your GCash Account Name</label>
                <input type="text" id="gcashAccountName" class="form-control" placeholder="e.g. Juan Dela Cruz" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-white">GCash Reference No. (13 Digits)</label>
                <input type="text" id="gcashRefNo" class="form-control" placeholder="e.g. 1002938475610" maxlength="13" required>
            </div>

            <button type="button" class="modal-pill-btn w-100" onclick="saveGCashDetails()">
                Confirm GCash Payment
            </button>
        </div>
    </div>
</div>


<div class="modal fade custom-checkout-modal" id="bankModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 border-secondary">
                <h4 class="modal-title mb-0"><i class="fa-solid fa-building-columns me-2 text-warning"></i>Bank Transfer</h4>
                <button type="button" class="modal-close-btn" data-bs-dismiss="modal">[CLOSE]</button>
            </div>

           
            <div class="p-3 rounded-3 mb-3" style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.1);">
                <div style="color: #FED7AA; font-family: 'Alinore', sans-serif; font-size: 1.05rem; letter-spacing: 0.5px;">
                    TRANSFER AMOUNT:
                </div>
                <div style="font-family: 'Alinore', sans-serif; font-size: 2.2rem; color: #BEF264; font-weight: bold;">
                    ₱<?= number_format($total, 2); ?>
                </div>

                <div class="mt-3 pt-2 border-top border-secondary text-white small" style="line-height: 1.6;">
                    <span style="color: #FDBA74; font-family: 'Alinore'; text-transform: uppercase;">Store Bank Accounts:</span><br>
                    <strong>BDO Unibank:</strong> 0012-3456-7890<br>
                    <strong>BPI (Bank of the PI):</strong> 4321-9876-5432<br>
                    <strong>Account Name:</strong> Roast & Grind Specialty Coffee Corp.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-white">Your Bank (Sent From)</label>
                <select id="bankNameInput" class="form-select" required>
                    <option value="BDO">BDO Unibank</option>
                    <option value="BPI">BPI</option>
                    <option value="Metrobank">Metrobank</option>
                    <option value="UnionBank">UnionBank</option>
                    <option value="Maya Bank">Maya Bank</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-white">Transfer Reference / Confirmation Number</label>
                <input type="text" id="bankRefNo" class="form-control" placeholder="e.g. BDO-9823412" maxlength="16" required>
            </div>

            <button type="button" class="modal-pill-btn w-100" onclick="saveBankDetails()">
                Confirm Bank Details
            </button>
        </div>
    </div>
</div>


<div class="modal fade custom-checkout-modal" id="cardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 border-secondary">
                <h4 class="modal-title mb-0"><i class="fa-regular fa-credit-card me-2 text-warning"></i>Card Payment</h4>
                <button type="button" class="modal-close-btn" data-bs-dismiss="modal">[CLOSE]</button>
            </div>

           
            <div class="p-3 rounded-3 mb-3" style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.1);">
                <div style="color: #FED7AA; font-family: 'Alinore', sans-serif; font-size: 1.05rem; letter-spacing: 0.5px;">
                    CHARGE AMOUNT:
                </div>
                <div style="font-family: 'Alinore', sans-serif; font-size: 2.2rem; color: #BEF264; font-weight: bold;">
                    ₱<?= number_format($total, 2); ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-white">Name on Card</label>
                <input type="text" id="cardHolderName" class="form-control" placeholder="e.g. Juan Dela Cruz" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-white">Card Number (16 Digits)</label>
                <input type="text" id="cardNumberInput" class="form-control" placeholder="4123 4567 8901 2345" maxlength="19" required>
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <label class="form-label small fw-bold text-white">Expiry (MM/YY)</label>
                    <input type="text" id="cardExpiryInput" class="form-control" placeholder="12/28" maxlength="5" required>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-white">CVV (3 Digits)</label>
                    <input type="password" id="cardCvvInput" class="form-control" placeholder="•••" maxlength="3" required>
                </div>
            </div>

            <button type="button" class="modal-pill-btn w-100" onclick="saveCardDetails()">
                Confirm Card Payment
            </button>
        </div>
    </div>
</div>

<script>
const paymentInput = document.getElementById('paymentMethodInput');

function clearChecks() {
    document.querySelectorAll('.payment-option-card').forEach(el => el.classList.remove('active'));
    document.getElementById('checkCOD').className   = 'fa-solid fa-circle text-muted fs-4';
    document.getElementById('checkGCash').className = 'fa-solid fa-circle text-muted fs-4';
    document.getElementById('checkBank').className  = 'fa-solid fa-circle text-muted fs-4';
}

function selectPayment(method) {
    paymentInput.value = method; 
    clearChecks();
    document.getElementById('cardCOD').classList.add('active');
    document.getElementById('checkCOD').className = 'fa-solid fa-check-circle fs-4 text-success';
}

function openPaymentModal(modalId) {
    const modalEl = new bootstrap.Modal(document.getElementById(modalId));
    modalEl.show();
}

function saveGCashDetails() {
    const name = document.getElementById('gcashAccountName').value.trim();
    const ref  = document.getElementById('gcashRefNo').value.trim();

    if (!name || !ref) {
        alert("Please enter both your GCash Name and Reference Number.");
        return;
    }

   
    paymentInput.value = "GCash";
    
    clearChecks();
    document.getElementById('cardGCash').classList.add('active');
    document.getElementById('descGCash').innerHTML = '<span class="text-success fw-bold">✓ Ref #' + ref + ' confirmed</span>';
    document.getElementById('checkGCash').className = 'fa-solid fa-check-circle fs-4 text-success';

    bootstrap.Modal.getInstance(document.getElementById('gcashModal')).hide();
}

function saveCardDetails() {
    const nameInput = document.getElementById('cardHolderName');
    const numInput  = document.getElementById('cardNumberInput');

    const name = nameInput ? nameInput.value.trim() : '';
    const num  = numInput ? numInput.value.replace(/\s+/g, '') : '';

    if (!name || num.length < 15) {
        alert("Please enter a valid cardholder name and 16-digit card number.");
        return;
    }

    const lastFour = num.slice(-4);

  
    paymentInput.value = "Credit Card";

    clearChecks();

  
    const cardEl = document.getElementById('cardCredit');
    if (cardEl) cardEl.classList.add('active');

    const descEl = document.getElementById('descCredit');
    if (descEl) descEl.innerHTML = '<span class="text-success fw-bold">✓ Card ending in ' + lastFour + ' confirmed</span>';

    const checkEl = document.getElementById('checkCredit');
    if (checkEl) checkEl.className = 'fa-solid fa-check-circle fs-4 text-success';

    
    const modalEl = document.getElementById('cardModal');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInstance.hide();
    }
}


<?php if (!$isLoggedIn): ?>
window.addEventListener('DOMContentLoaded', () => {
    const authModal = new bootstrap.Modal(document.getElementById('authRequiredModal'));
    authModal.show();
});
<?php endif; ?>
</script>

<?php include('includes/footer.php'); ?>
