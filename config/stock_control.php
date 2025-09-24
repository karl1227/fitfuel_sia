<?php
/**
 * Stock Control Functions
 * Shared functions for managing stock quantities across the application
 */

/**
 * Deduct stock and log inventory for an order. Idempotent per order via orders.stock_deducted flag.
 */
function applyStockControl(PDO $pdo, int $orderId, int $adminUserId): void {
	$pdo->beginTransaction();
	try {
		$order = $pdo->prepare("SELECT stock_deducted FROM orders WHERE order_id = ? FOR UPDATE");
		$order->execute([$orderId]);
		$row = $order->fetch();
		if (!$row) { throw new RuntimeException('Order not found'); }
		if ((int)$row['stock_deducted'] === 1) {
			$pdo->commit();
			return; // already applied
		}

		$items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
		$items->execute([$orderId]);
		$all = $items->fetchAll();
		foreach ($all as $it) {
			// Reduce product stock
			$upd = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
			$upd->execute([(int)$it['quantity'], (int)$it['product_id']]);
			// Log inventory movement
			$inv = $pdo->prepare("INSERT INTO inventory (product_id, change_type, quantity, reference_id, created_by) VALUES (?, 'stock_out', ?, ?, ?)");
			$inv->execute([(int)$it['product_id'], (int)$it['quantity'], $orderId, $adminUserId]);
		}

		$pdo->prepare("UPDATE orders SET stock_deducted = 1 WHERE order_id = ?")->execute([$orderId]);
		$pdo->commit();
	} catch (Throwable $e) {
		$pdo->rollBack();
		throw $e;
	}
}

/**
 * Restore stock and log inventory for a cancelled/returned order. Idempotent per order via orders.stock_deducted flag.
 */
function restoreStockControl(PDO $pdo, int $orderId, int $adminUserId): void {
	$pdo->beginTransaction();
	try {
		$order = $pdo->prepare("SELECT stock_deducted FROM orders WHERE order_id = ? FOR UPDATE");
		$order->execute([$orderId]);
		$row = $order->fetch();
		if (!$row) { throw new RuntimeException('Order not found'); }
		if ((int)$row['stock_deducted'] === 0) {
			$pdo->commit();
			return; // stock was never deducted
		}

		$items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
		$items->execute([$orderId]);
		$all = $items->fetchAll();
		foreach ($all as $it) {
			// Restore product stock
			$upd = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
			$upd->execute([(int)$it['quantity'], (int)$it['product_id']]);
			// Log inventory movement
			$inv = $pdo->prepare("INSERT INTO inventory (product_id, change_type, quantity, reference_id, created_by) VALUES (?, 'stock_in', ?, ?, ?)");
			$inv->execute([(int)$it['product_id'], (int)$it['quantity'], $orderId, $adminUserId]);
		}

		$pdo->prepare("UPDATE orders SET stock_deducted = 0 WHERE order_id = ?")->execute([$orderId]);
		$pdo->commit();
	} catch (Throwable $e) {
		$pdo->rollBack();
		throw $e;
	}
}

/**
 * Deduct stock immediately during checkout (for immediate stock deduction)
 * This is used when orders are created and stock should be deducted immediately
 */
function deductStockImmediately(PDO $pdo, array $items, int $orderId, int $userId): void {
	foreach ($items as $item) {
		// Reduce product stock
		$upd = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
		$upd->execute([(int)$item['quantity'], (int)$item['product_id']]);
		// Log inventory movement
		$inv = $pdo->prepare("INSERT INTO inventory (product_id, change_type, quantity, reference_id, created_by) VALUES (?, 'stock_out', ?, ?, ?)");
		$inv->execute([(int)$item['product_id'], (int)$item['quantity'], $orderId, $userId]);
	}
	
	// Mark order as having stock deducted
	$pdo->prepare("UPDATE orders SET stock_deducted = 1 WHERE order_id = ?")->execute([$orderId]);
}
