<?php
// includes/send_receipt.php - Generates PDF and sends via PHPMailer + Mailtrap
require_once __DIR__ . '/fpdf/fpdf.php';

// Manually load PHPMailer classes
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PDF_Receipt extends FPDF {
    function Header() {
        $this->SetFillColor(116, 48, 20); // Terracotta Brown
        $this->Rect(0, 0, 210, 40, 'F');
        
        // Built-in elegant serif font
        $this->SetFont('Times', 'B', 24);
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

    $pdf->SetFont('Times', 'B', 14);
    $pdf->SetTextColor(84, 34, 14);
    $pdf->Cell(100, 8, 'Order Receipt #' . $order_id, 0, 0);
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(90, 8, 'Date: ' . date('M j, Y g:i A', strtotime($order['order_date'])), 0, 1, 'R');
    $pdf->Cell(100, 6, 'Billed To: ' . $customer_name, 0, 0);
    $pdf->Cell(90, 6, 'Payment: ' . $order['payment_method'], 0, 1, 'R');
    $pdf->Ln(8);

    $pdf->SetFillColor(77, 100, 67); // Moss Green
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(95, 10, '  Item Description', 1, 0, 'L', true);
    $pdf->Cell(30, 10, 'Price', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Qty', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Subtotal', 1, 1, 'R', true);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(68, 64, 60);
    $fill = false;
    
    $html_items_list = "";
    
    while ($item = mysqli_fetch_assoc($items_res)) {
        $subtotal = $item['unit_price'] * $item['quantity'];
        
        $pdf->SetFillColor(245, 243, 236);
        $pdf->Cell(95, 9, '  ' . $item['item_name'], 1, 0, 'L', $fill);
        $pdf->Cell(30, 9, 'Php ' . number_format($item['unit_price'], 2), 1, 0, 'C', $fill);
        $pdf->Cell(25, 9, $item['quantity'], 1, 0, 'C', $fill);
        $pdf->Cell(40, 9, 'Php ' . number_format($subtotal, 2) . '  ', 1, 1, 'R', $fill);
        $fill = !$fill;
        
        $html_items_list .= "<li>{$item['quantity']}x {$item['item_name']} - Php " . number_format($subtotal, 2) . "</li>";
    }

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(84, 34, 14);
    $pdf->Cell(150, 12, 'Grand Total: ', 0, 0, 'R');
    $pdf->Cell(40, 12, 'Php ' . number_format($order['total_amount'], 2) . '  ', 0, 1, 'R');

    $pdf_filename = __DIR__ . '/../temp_receipt.pdf'; 
    $pdf->Output('F', $pdf_filename);

    // 3. Send Email via PHPMailer + Mailtrap
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = '5e8c883b1cd6cd'; 
        $mail->Password   = '85f40699177030'; 
        $mail->Port       = 2525;

        $mail->setFrom('orders@roastandgrind.com', 'Roast & Grind');
        $mail->addAddress($customer_email, $customer_name);
        $mail->isHTML(true);
        $mail->Subject = "Order Update: Your transaction is now {$new_status}";
        
       // Clean, Professional Boutique Email (Web-Safe & 100% Client Compatible)
        $mail->Body = "
        <div style='background-color: #F5F3EC; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <table style='max-width: 600px; width: 100%; margin: 0 auto; background: #FFFFFF; border-radius: 16px; overflow: hidden; border: 1px solid #E2DCD5; border-collapse: separate;'>
                <!-- Header -->
                <tr>
                    <td style='background-color: #743014; padding: 30px; text-align: center;'>
                        <h1 style='color: #FED7AA; margin: 0; font-family: Georgia, \"Times New Roman\", serif; font-size: 28px; letter-spacing: 2px;'>ROAST &amp; GRIND</h1>
                        <p style='color: #FDE8D0; margin: 6px 0 0; font-size: 12px; letter-spacing: 1px; text-transform: uppercase;'>Specialty Coffee &amp; Brewing Gear</p>
                    </td>
                </tr>
                <!-- Content -->
                <tr>
                    <td style='padding: 35px 30px;'>
                        <h2 style='color: #743014; font-family: Georgia, \"Times New Roman\", serif; font-size: 20px; margin-top: 0;'>Hello " . htmlspecialchars($customer_name) . ",</h2>
                        <p style='color: #44403C; font-size: 15px; line-height: 1.6; margin-bottom: 25px;'>
                            Your Roast &amp; Grind order <strong>#{$order_id}</strong> has been updated to: <span style='display: inline-block; padding: 3px 10px; background-color: #E7EFE5; color: #2B4E26; border-radius: 20px; font-weight: bold;'>{$new_status}</span>
                        </p>
                        
                        <div style='background-color: #FBF9F5; border: 1px solid #EFECE6; padding: 20px; border-radius: 12px; margin: 25px 0;'>
                            <h3 style='color: #743014; font-family: Georgia, \"Times New Roman\", serif; font-size: 16px; margin: 0 0 12px;'>Order Summary:</h3>
                            <ul style='color: #57534E; padding-left: 20px; margin: 0 0 15px; font-size: 14px; line-height: 1.8;'>
                                {$html_items_list}
                            </ul>
                            <div style='border-top: 1px dashed #D6D3D1; padding-top: 12px;'>
                                <strong style='color: #743014; font-size: 15px;'>Grand Total: </strong>
                                <strong style='color: #4D6443; font-size: 16px;'>Php " . number_format($order['total_amount'], 2) . "</strong>
                            </div>
                        </div>
                        
                        <p style='color: #57534E; font-size: 14px; line-height: 1.6;'>
                            We have attached your official <strong>PDF transaction receipt</strong> to this email for your records.
                        </p>
                        <p style='color: #57534E; font-size: 14px; line-height: 1.6; margin-bottom: 0;'>
                            Warm regards,<br>
                            <strong style='color: #743014;'>The Roast &amp; Grind Team</strong>
                        </p>
                    </td>
                </tr>
                <!-- Footer -->
                <tr>
                    <td style='background-color: #743014; padding: 18px; text-align: center; color: #FED7AA; font-size: 11px;'>
                        &copy; " . date('Y') . " Roast &amp; Grind. Specialty Coffee &amp; Gear.
                    </td>
                </tr>
            </table>
        </div>";

        $mail->addAttachment($pdf_filename, "Receipt_Order_{$order_id}.pdf");
        $mail->send();
    } catch (Exception $e) {
        // Silently continue if email fails
    }

    if (file_exists($pdf_filename)) unlink($pdf_filename);
}
