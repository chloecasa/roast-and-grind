<?php
// includes/send_receipt.php - Generates PDF and sends via PHPMailer + Mailtrap
require_once __DIR__ . '/fpdf/fpdf.php';

// Manually load PHPMailer classes (No Composer)
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PDF_Receipt extends FPDF {
    function Header() {
        $this->SetFillColor(116, 48, 20); // Terracotta Brown
        $this->Rect(0, 0, 210, 40, 'F');
        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(254, 215, 170); // Warm Cream
        $this->Cell(0, 20, 'ROAST & GRIND', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(255, 245, 234);
        $this->Cell(0, -5, 'Specialty Coffee - Official Transaction Receipt', 0, 1, 'C');
        $this->Ln(25);
    }
    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(120, 113, 108);
        $this->Cell(0, 10, 'Thank you for brewing with intention! www.roastandgrind.com', 0, 0, 'C');
    }
}

function sendOrderReceiptEmail($conn, $order_id, $customer_email, $customer_name, $new_status) {
    // 1. Fetch order & items
    $stmt = mysqli_prepare($conn, "SELECT order_date, total_amount, payment_method FROM orders WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    $item_stmt = mysqli_prepare($conn, "SELECT item_name, unit_price, quantity FROM order_items WHERE order_id = ?");
    mysqli_stmt_bind_param($item_stmt, 'i', $order_id);
    mysqli_stmt_execute($item_stmt);
    $items_res = mysqli_stmt_get_result($item_stmt);

    // 2. Generate PDF
    $pdf = new PDF_Receipt();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(84, 34, 14);
    $pdf->Cell(100, 8, 'Order Receipt #' . $order_id, 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(90, 8, 'Date: ' . date('M j, Y g:i A', strtotime($order['order_date'])), 0, 1, 'R');
    $pdf->Cell(100, 6, 'Billed To: ' . $customer_name, 0, 0);
    $pdf->Cell(90, 6, 'Payment: ' . $order['payment_method'], 0, 1, 'R');
    $pdf->Ln(8);

    $pdf->SetFillColor(77, 100, 67); // Moss Green
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(95, 10, '  Item Description', 1, 0, 'L', true);
    $pdf->Cell(30, 10, 'Price', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Qty', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Subtotal', 1, 1, 'R', true);

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(68, 64, 60);
    $fill = false;
    
    // Build HTML email list simultaneously
    $html_items_list = "";
    
    while ($item = mysqli_fetch_assoc($items_res)) {
        $subtotal = $item['unit_price'] * $item['quantity'];
        
        // Add to PDF
        $pdf->SetFillColor(245, 243, 236);
        $pdf->Cell(95, 9, '  ' . $item['item_name'], 1, 0, 'L', $fill);
        $pdf->Cell(30, 9, 'Php ' . number_format($item['unit_price'], 2), 1, 0, 'C', $fill);
        $pdf->Cell(25, 9, $item['quantity'], 1, 0, 'C', $fill);
        $pdf->Cell(40, 9, 'Php ' . number_format($subtotal, 2) . '  ', 1, 1, 'R', $fill);
        $fill = !$fill;
        
        // Add to Email HTML
        $html_items_list .= "<li>{$item['quantity']}x {$item['item_name']} - Php " . number_format($subtotal, 2) . "</li>";
    }

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(84, 34, 14);
    $pdf->Cell(150, 12, 'Grand Total: ', 0, 0, 'R');
    $pdf->Cell(40, 12, 'Php ' . number_format($order['total_amount'], 2) . '  ', 0, 1, 'R');

    $pdf_filename = sys_get_temp_dir() . '/Receipt_Order_' . $order_id . '.pdf';
    $pdf->Output('F', $pdf_filename);

    // 3. Send Email via PHPMailer + Mailtrap
    $mail = new PHPMailer(true);
    try {
        // Mailtrap SMTP Settings (Update Username and Password with your Mailtrap credentials)
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'YOUR_MAILTRAP_USERNAME'; // <--- REPLACE THIS
        $mail->Password   = 'YOUR_MAILTRAP_PASSWORD'; // <--- REPLACE THIS
        $mail->Port       = 2525;

        // Email setup
        $mail->setFrom('orders@roastandgrind.com', 'Roast & Grind');
        $mail->addAddress($customer_email, $customer_name);
        $mail->isHTML(true);
        $mail->Subject = "Order Update: Your transaction is now {$new_status}";
        
        // Boutique HTML Email Body
        $mail->Body = "
            <h2 style='color:#743014;'>Hello {$customer_name},</h2>
            <p>Your Roast & Grind order <strong>#{$order_id}</strong> has been updated to: <strong>{$new_status}</strong>.</p>
            <h3>Order Details:</h3>
            <ul>{$html_items_list}</ul>
            <h3 style='color:#4D6443;'>Grand Total: Php " . number_format($order['total_amount'], 2) . "</h3>
            <p>We have attached your official PDF receipt to this email.</p>
            <p>Warm regards,<br><strong>The Roast & Grind Team</strong></p>
        ";

        // Attach the PDF
        $mail->addAttachment($pdf_filename, "Receipt_Order_{$order_id}.pdf");
        
        $mail->send();
    } catch (Exception $e) {
        // Silently fail or log if email doesn't send, so it doesn't crash the app
    }

    if (file_exists($pdf_filename)) unlink($pdf_filename);
}
