<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to save address']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request - no JSON data received']);
    exit();
}

// Debug: Log the received data
error_log("Save address request received: " . json_encode($input));

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Check if this is an edit (has address_id) or new address
    $is_edit = isset($input['address_id']) && !empty($input['address_id']);
    $address_id = $is_edit ? (int)$input['address_id'] : null;

    // Only check address limit for NEW addresses, not edits
    if (!$is_edit) {
        $count_sql = "SELECT COUNT(*) as count FROM shipping_addresses WHERE user_id = ?";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute([$user_id]);
        $address_count = $count_stmt->fetch()['count'];
        
        if ($address_count >= 3) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Maximum of 3 addresses allowed per user']);
            exit();
        }
    }
    
    // Ensure PSGC columns exist
    try {
        $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipping_addresses'")
                    ->fetchAll(PDO::FETCH_COLUMN);
        if (is_array($cols)) {
            $psgc_columns = [
                'region_name' => 'varchar(100)',
                'region_code' => 'varchar(10)',
                'province_name' => 'varchar(100)',
                'province_code' => 'varchar(10)',
                'city_muni_name' => 'varchar(100)',
                'city_muni_code' => 'varchar(10)',
                'barangay_name' => 'varchar(100)',
                'barangay_code' => 'varchar(10)'
            ];
            
            foreach ($psgc_columns as $col => $type) {
                if (!in_array($col, $cols, true)) {
                    $pdo->exec("ALTER TABLE shipping_addresses ADD COLUMN $col $type NULL AFTER postal_code");
                }
            }
        }
    } catch (Throwable $e) {
        error_log("PSGC column creation error: " . $e->getMessage());
    }
    
    // Handle default address logic
    $is_default = isset($input['is_default']) ? (int)$input['is_default'] : 0;

    // If setting as default, clear other defaults first
    if ($is_default === 1) {
        $clear_default = $pdo->prepare("UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?");
        $clear_default->execute([$user_id]);
    }

    // Extract PSGC data
    $region_name = $input['region_name'] ?? '';
    $region_code = $input['region_code'] ?? '';
    $province_name = $input['province_name'] ?? '';
    $province_code = $input['province_code'] ?? '';
    $city_muni_name = $input['city_muni_name'] ?? '';
    $city_muni_code = $input['city_muni_code'] ?? '';
    $barangay_name = $input['barangay_name'] ?? '';
    $barangay_code = $input['barangay_code'] ?? '';

    if ($is_edit) {
        // Update existing address
        $update_sql = "UPDATE shipping_addresses SET
                       full_name = ?, phone = ?, address_line1 = ?, address_line2 = ?, address_line3 = ?,
                       city = ?, state = ?, postal_code = ?, country = 'Philippines', is_default = ?,
                       region_name = ?, region_code = ?, province_name = ?, province_code = ?,
                       city_muni_name = ?, city_muni_code = ?, barangay_name = ?, barangay_code = ?,
                       updated_at = CURRENT_TIMESTAMP
                       WHERE address_id = ? AND user_id = ?";

        $address_line1 = $input['street_address'] ?? '';
        $address_line2 = null;
        $address_line3 = null;

        $update_stmt = $pdo->prepare($update_sql);
        $result = $update_stmt->execute([
            $input['full_name'] ?? '',
            $input['phone'] ?? '',
            $address_line1,
            $address_line2,
            $address_line3,
            $city_muni_name, // Use PSGC city name
            $region_name,    // Use PSGC region name
            $input['postal_code'] ?? '',
            $is_default,
            $region_name,
            $region_code,
            $province_name,
            $province_code,
            $city_muni_name,
            $city_muni_code,
            $barangay_name,
            $barangay_code,
            $address_id,
            $user_id
        ]);

        if (!$result || $update_stmt->rowCount() === 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to update address or address not found']);
            exit();
        }
    } else {
        // Create new address
        $insert_sql = "INSERT INTO shipping_addresses
                       (user_id, full_name, phone, address_line1, address_line2, address_line3,
                        city, state, postal_code, country, is_default,
                        region_name, region_code, province_name, province_code,
                        city_muni_name, city_muni_code, barangay_name, barangay_code,
                        created_at, updated_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Philippines', ?,
                               ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";

        $address_line1 = $input['street_address'] ?? '';
        $address_line2 = null;
        $address_line3 = null;

        $insert_stmt = $pdo->prepare($insert_sql);
        $result = $insert_stmt->execute([
            $user_id,
            $input['full_name'] ?? '',
            $input['phone'] ?? '',
            $address_line1,
            $address_line2,
            $address_line3,
            $city_muni_name, // Use PSGC city name
            $region_name,    // Use PSGC region name
            $input['postal_code'] ?? '',
            $is_default,
            $region_name,
            $region_code,
            $province_name,
            $province_code,
            $city_muni_name,
            $city_muni_code,
            $barangay_name,
            $barangay_code
        ]);

        if (!$result) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to create address']);
            exit();
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Address saved successfully']);
    
} catch (Throwable $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Error in save_address.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
