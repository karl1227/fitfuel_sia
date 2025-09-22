<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';

$pdo = getDBConnection();

// Get order ID from URL parameter
$order_id = (int)($_GET['id'] ?? 0);

if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// Fetch order details with customer information
$orderQuery = "
    SELECT 
        o.*,
        u.username,
        u.email
    FROM orders o 
    JOIN users u ON u.user_id = o.user_id 
    WHERE o.order_id = ?
";

$orderStmt = $pdo->prepare($orderQuery);
$orderStmt->execute([$order_id]);
$order = $orderStmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Fetch order items with product details
$itemsQuery = "
    SELECT 
        oi.*,
        p.name as product_name,
        p.images,
        p.description as product_description,
        c.name as category_name,
        sc.name as subcategory_name
    FROM order_items oi
    JOIN products p ON p.product_id = oi.product_id
    LEFT JOIN categories c ON c.category_id = p.category_id
    LEFT JOIN subcategories sc ON sc.subcategory_id = p.subcategory_id
    WHERE oi.order_id = ?
";

$itemsStmt = $pdo->prepare($itemsQuery);
$itemsStmt->execute([$order_id]);
$order_items = $itemsStmt->fetchAll();

// Parse shipping address
$shipping_address = [];
if ($order['shipping_address']) {
    try {
        $shipping_address = json_decode($order['shipping_address'], true) ?: [];
    } catch (Exception $e) {
        $shipping_address = [];
    }
}

// Generate waybill information
$waybill_info = null;
if ($order['status'] === 'processing' || $order['status'] === 'shipped' || $order['status'] === 'delivered') {
    $waybill_info = generateWaybill($order, $shipping_address);
}

// Set headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="invoice_' . ($order['custom_order_id'] ?? $order['order_id']) . '.pdf"');

// Generate waybill API call (placeholder for actual implementation)
function generateWaybill($order_data, $shipping_address) {
    // This is a placeholder for actual waybill API integration
    // You would integrate with services like:
    // - LBC Express API
    // - J&T Express API
    // - Grab Express API
    // - Local courier services
    
    $waybill_data = [
        'order_id' => $order_data['order_id'],
        'custom_order_id' => $order_data['custom_order_id'],
        'recipient_name' => $shipping_address['full_name'] ?? '',
        'recipient_phone' => $shipping_address['phone'] ?? '',
        'recipient_address' => $shipping_address['address'] ?? '',
        'recipient_city' => $shipping_address['city'] ?? '',
        'recipient_state' => $shipping_address['state'] ?? '',
        'recipient_postal_code' => $shipping_address['postal_code'] ?? '',
        'package_weight' => '1.0', // Default weight in kg
        'package_value' => $order_data['total_amount'],
        'service_type' => 'standard',
        'payment_method' => $order_data['payment_method']
    ];
    
    // Example API call structure (replace with actual API)
    /*
    $api_url = 'https://api.courier-service.com/waybill';
    $api_key = 'your_api_key_here';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($waybill_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($result && isset($result['tracking_number'])) {
        return $result;
    }
    */
    
    // Return mock data for demonstration
    return [
        'tracking_number' => 'WB' . str_pad($order_data['order_id'], 8, '0', STR_PAD_LEFT),
        'carrier' => 'FitFuel Express',
        'estimated_delivery' => date('Y-m-d', strtotime('+3 days')),
        'status' => 'created'
    ];
}

// Simple PDF generation using basic HTML/CSS that can be printed as PDF
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - Order #<?php echo htmlspecialchars($order['custom_order_id'] ?? $order['order_id']); ?></title>
    <style>
        @page {
            margin: 1cm;
            size: A4;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            color: #333; 
            font-size: 12px;
            line-height: 1.4;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .company-name { 
            font-size: 28px; 
            font-weight: bold; 
            color: #000; 
            margin-bottom: 5px; 
        }
        
        .company-tagline { 
            font-size: 14px; 
            color: #666; 
            margin-bottom: 10px; 
        }
        
        .company-contact { 
            font-size: 11px; 
            color: #666; 
        }
        
        .invoice-title { 
            font-size: 24px; 
            font-weight: bold; 
            margin-bottom: 20px; 
            text-align: center;
        }
        
        .invoice-info { 
            display: table; 
            width: 100%; 
            margin-bottom: 30px; 
        }
        
        .info-section { 
            display: table-cell; 
            width: 50%; 
            vertical-align: top;
            padding-right: 20px;
        }
        
        .info-label { 
            font-weight: bold; 
            margin-bottom: 3px; 
            font-size: 11px;
        }
        
        .info-value { 
            margin-bottom: 8px; 
            font-size: 11px;
        }
        
        .customer-section { 
            margin-bottom: 25px; 
        }
        
        .section-title { 
            font-size: 14px; 
            font-weight: bold; 
            margin-bottom: 8px; 
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
        
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            font-size: 11px;
        }
        
        .items-table th, .items-table td { 
            border: 1px solid #000; 
            padding: 6px; 
            text-align: left; 
        }
        
        .items-table th { 
            background-color: #f0f0f0; 
            font-weight: bold; 
            text-align: center;
        }
        
        .items-table .qty { 
            text-align: center; 
            width: 50px; 
        }
        
        .items-table .price, .items-table .total { 
            text-align: right; 
            width: 80px; 
        }
        
        .summary { 
            margin-top: 20px; 
            width: 100%;
        }
        
        .summary-row { 
            display: table-row; 
            margin-bottom: 3px; 
        }
        
        .summary-label {
            display: table-cell;
            width: 80%;
            padding: 3px 0;
        }
        
        .summary-value {
            display: table-cell;
            width: 20%;
            text-align: right;
            padding: 3px 0;
        }
        
        .summary-total { 
            font-weight: bold; 
            font-size: 14px; 
            border-top: 2px solid #000; 
            padding-top: 8px; 
            margin-top: 8px;
        }
        
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 10px; 
            color: #666; 
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        
        .waybill-section { 
            margin-top: 30px; 
            padding: 15px; 
            background-color: #f9f9f9; 
            border: 1px solid #ccc;
        }
        
        .waybill-title { 
            font-size: 14px; 
            font-weight: bold; 
            margin-bottom: 8px; 
            text-transform: uppercase;
        }
        
        .waybill-info {
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">FITFUEL</div>
        <div class="company-tagline">Your Ultimate Fitness Partner</div>
        <div class="company-contact">Email: info@fitfuel.com | Phone: +63 123 456 7890</div>
    </div>
    
    <div class="invoice-title">INVOICE</div>
    
    <div class="invoice-info">
        <div class="info-section">
            <div class="info-label">Invoice Number:</div>
            <div class="info-value"><?php echo htmlspecialchars($order['custom_order_id'] ?? 'FF-' . str_pad($order['order_id'], 6, '0', STR_PAD_LEFT)); ?></div>
            <div class="info-label">Order ID:</div>
            <div class="info-value">#<?php echo $order['order_id']; ?></div>
            <div class="info-label">Payment Status:</div>
            <div class="info-value"><?php echo ucfirst($order['payment_status']); ?></div>
        </div>
        <div class="info-section">
            <div class="info-label">Date:</div>
            <div class="info-value"><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></div>
            <div class="info-label">Status:</div>
            <div class="info-value"><?php echo ucfirst($order['status']); ?></div>
            <div class="info-label">Payment Method:</div>
            <div class="info-value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'] ?? 'N/A')); ?></div>
        </div>
    </div>
    
    <div class="customer-section">
        <div class="section-title">Bill To:</div>
        <div><?php echo htmlspecialchars($order['username']); ?></div>
        <div><?php echo htmlspecialchars($order['email']); ?></div>
    </div>
    
    <?php if (!empty($shipping_address)): ?>
    <div class="customer-section">
        <div class="section-title">Ship To:</div>
        <div><?php echo htmlspecialchars($shipping_address['full_name'] ?? ''); ?></div>
        <?php if (!empty($shipping_address['phone'])): ?>
        <div><?php echo htmlspecialchars($shipping_address['phone']); ?></div>
        <?php endif; ?>
        <div><?php echo htmlspecialchars($shipping_address['address'] ?? ''); ?></div>
        <div><?php echo htmlspecialchars(($shipping_address['city'] ?? '') . ', ' . ($shipping_address['state'] ?? '') . ' ' . ($shipping_address['postal_code'] ?? '')); ?></div>
    </div>
    <?php endif; ?>
    
    <table class="items-table">
        <thead>
            <tr>
                <th class="qty">Qty</th>
                <th>Description</th>
                <th class="price">Unit Price</th>
                <th class="total">Total</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            foreach ($order_items as $item): 
                $item_total = (float)$item['price'] * (int)$item['quantity'];
                $subtotal += $item_total;
            ?>
            <tr>
                <td class="qty"><?php echo (int)$item['quantity']; ?></td>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td class="price">₱<?php echo number_format((float)$item['price'], 2); ?></td>
                <td class="total">₱<?php echo number_format($item_total, 2); ?></td>
                <td><?php echo htmlspecialchars($item['category_name'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Subtotal:</span>
            <span class="summary-value">₱<?php echo number_format($subtotal, 2); ?></span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Shipping:</span>
            <span class="summary-value">₱0.00</span>
        </div>
        <div class="summary-row summary-total">
            <span class="summary-label">TOTAL:</span>
            <span class="summary-value">₱<?php echo number_format($subtotal, 2); ?></span>
        </div>
    </div>
    
    <?php if ($waybill_info): ?>
    <div class="waybill-section">
        <div class="waybill-title">Shipping Information</div>
        <div class="waybill-info"><strong>Tracking Number:</strong> <?php echo htmlspecialchars($waybill_info['tracking_number']); ?></div>
        <div class="waybill-info"><strong>Carrier:</strong> <?php echo htmlspecialchars($waybill_info['carrier']); ?></div>
        <?php if ($order['estimated_delivery_date']): ?>
        <div class="waybill-info"><strong>Estimated Delivery:</strong> <?php echo date('M j, Y', strtotime($order['estimated_delivery_date'])); ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <div>Thank you for your business!</div>
        <div>For any questions regarding this invoice, please contact us at info@fitfuel.com</div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>