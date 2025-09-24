<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit(); }
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { echo json_encode(['success'=>false,'message'=>'Invalid JSON']); exit(); }
$address_id = isset($input['address_id']) ? (int)$input['address_id'] : 0;
if ($address_id <= 0) { echo json_encode(['success'=>false,'message'=>'address_id is required']); exit(); }
$user_id = (int)$_SESSION['user_id'];

try {
	$pdo = getDBConnection();
	$pdo->beginTransaction();

	// verify ownership
	$own = $pdo->prepare('SELECT address_id FROM shipping_addresses WHERE address_id = ? AND user_id = ?');
	$own->execute([$address_id, $user_id]);
	if (!$own->fetch()) { $pdo->rollBack(); echo json_encode(['success'=>false,'message'=>'Address not found']); exit(); }

	$pdo->prepare('UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?')->execute([$user_id]);
	$pdo->prepare('UPDATE shipping_addresses SET is_default = 1, updated_at = CURRENT_TIMESTAMP WHERE address_id = ?')->execute([$address_id]);

	$pdo->commit();
	echo json_encode(['success'=>true]);
} catch (Throwable $e) {
	if (isset($pdo)) $pdo->rollBack();
	echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
}
