<?php
require_once __DIR__ . '/database.php';

/**
 * Ensure audit_logs table exists (idempotent).
 */
function ensureAuditSchema(PDO $pdo): void {
	$pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT UNSIGNED NULL,
		username VARCHAR(150) NULL,
		role VARCHAR(50) NULL,
		module VARCHAR(100) NOT NULL,
		action VARCHAR(100) NOT NULL,
		status VARCHAR(20) NOT NULL,
		ip_address VARCHAR(45) NULL,
		user_agent TEXT NULL,
		old_values JSON NULL,
		new_values JSON NULL,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY module_action (module, action),
		KEY created_at (created_at)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
}

/**
 * Insert an audit log entry.
 * @param string $module e.g. 'auth','orders','products','users','profile','checkout','settings'
 * @param string $action e.g. 'login_success','login_failure','update_status'
 * @param string $status 'success'|'failure'|'info'
 * @param array $oldValues associative array of old values (optional)
 * @param array $newValues associative array of new values (optional)
 */
function audit_log(string $module, string $action, string $status = 'info', array $oldValues = [], array $newValues = []): void {
	try {
		$pdo = getDBConnection();
		ensureAuditSchema($pdo);

		$userId = $_SESSION['user_id'] ?? null;
		$username = $_SESSION['username'] ?? null;
		$role = $_SESSION['role'] ?? null;
		$ip = $_SERVER['REMOTE_ADDR'] ?? null;
		$ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

		$stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, username, role, module, action, status, ip_address, user_agent, old_values, new_values)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->execute([
			$userId ? (int)$userId : null,
			$username,
			$role,
			$module,
			$action,
			$status,
			$ip,
			$ua,
			$oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : null,
			$newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : null,
		]);
	} catch (Throwable $e) {
		// Fail-safe: do not break primary flow due to logging issues
		error_log('[audit_log] failed: ' . $e->getMessage());
	}
}
