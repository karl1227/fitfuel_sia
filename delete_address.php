<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { 
    echo json_encode(['success'=>false,'message'=>'Not authenticated']); 
    exit(); 
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { 
    echo json_encode(['success'=>false,'message'=>'Invalid JSON']); 
    exit(); 
}

$user_id = (int)$_SESSION['user_id'];
$address_id = isset($input['address_id']) ? (int)$input['address_id'] : 0;

if ($address_id <= 0) { 
    echo json_encode(['success'=>false,'message'=>'address_id is required']); 
    exit(); 
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    // Check if address belongs to user
    $own = $pdo->prepare('SELECT address_id, is_default FROM shipping_addresses WHERE address_id = ? AND user_id = ?');
    $own->execute([$address_id, $user_id]);
    $address = $own->fetch();
    
    if (!$address) { 
        $pdo->rollBack(); 
        echo json_encode(['success'=>false,'message'=>'Address not found']); 
        exit(); 
    }

    // Check if this is the default address
    if ($address['is_default'] == 1) {
        // Count total addresses for this user
        $count = $pdo->prepare('SELECT COUNT(*) as count FROM shipping_addresses WHERE user_id = ?');
        $count->execute([$user_id]);
        $total = $count->fetch()['count'];
        
        if ($total > 1) {
            // Set another address as default before deleting
            $setDefault = $pdo->prepare('UPDATE shipping_addresses SET is_default = 1 WHERE user_id = ? AND address_id != ? LIMIT 1');
            $setDefault->execute([$user_id, $address_id]);
        }
    }

    // Delete the address
    $del = $pdo->prepare('DELETE FROM shipping_addresses WHERE address_id = ? AND user_id = ?');
    $ok = $del->execute([$address_id, $user_id]);
    
    if (!$ok) { 
        $pdo->rollBack(); 
        echo json_encode(['success'=>false,'message'=>'Failed to delete address']); 
        exit(); 
    }

    $pdo->commit();
    echo json_encode(['success'=>true,'message'=>'Address deleted successfully']);
    
} catch (Throwable $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
}
?>
