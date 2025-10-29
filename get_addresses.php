<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit(); }

try {
  $pdo = getDBConnection();
  $stmt = $pdo->prepare("SELECT address_id, full_name, phone, address_line1, address_line2, address_line3, city, state, postal_code, is_default
                         FROM shipping_addresses WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC, created_at DESC");
  $stmt->execute([ (int)$_SESSION['user_id'] ]);
  $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  echo json_encode(['success'=>true,'addresses'=>$addresses]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
}
?>


