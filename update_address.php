<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit(); }
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { echo json_encode(['success'=>false,'message'=>'Invalid JSON']); exit(); }

$user_id = (int)$_SESSION['user_id'];
$address_id = isset($input['address_id']) ? (int)$input['address_id'] : 0;
if ($address_id <= 0) { echo json_encode(['success'=>false,'message'=>'address_id is required']); exit(); }

try {
	$pdo = getDBConnection();
	$pdo->beginTransaction();

	// Ensure address belongs to user
	$own = $pdo->prepare('SELECT address_id FROM shipping_addresses WHERE address_id = ? AND user_id = ?');
	$own->execute([$address_id, $user_id]);
	if (!$own->fetch()) { $pdo->rollBack(); echo json_encode(['success'=>false,'message'=>'Address not found']); exit(); }

	// Optionally set default: clear others then set this one
	$is_default = !empty($input['is_default']) ? 1 : 0;
	if ($is_default === 1) {
		$pdo->prepare('UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?')->execute([$user_id]);
	}

	$upd = $pdo->prepare('UPDATE shipping_addresses SET full_name=?, phone=?, address_line1=?, address_line2=?, address_line3=?, city=?, state=?, postal_code=?, country=?, is_default=?, updated_at = CURRENT_TIMESTAMP WHERE address_id = ?');
	$ok = $upd->execute([
		trim($input['full_name'] ?? ''),
		trim($input['phone'] ?? ''),
		trim($input['address_line1'] ?? ''),
		($input['address_line2'] ?? null),
		($input['address_line3'] ?? null),
		trim($input['city'] ?? ''),
		trim($input['state'] ?? ''),
		trim($input['postal_code'] ?? ''),
		'Philippines',
		$is_default,
		$address_id
	]);
	if (!$ok) { $pdo->rollBack(); echo json_encode(['success'=>false,'message'=>'Failed to update address']); exit(); }

	$pdo->commit();
	echo json_encode(['success'=>true]);
} catch (Throwable $e) {
	if (isset($pdo)) $pdo->rollBack();
	echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
}
