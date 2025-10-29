<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/config/email_config.php';

header('Content-Type: application/json');

function respond($ok, $msg) {
	echo json_encode(['success' => $ok, 'message' => $msg]);
	exit;
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	respond(false, 'Please provide a valid email address.');
}

try {
	$pdo = getDBConnection();
	// Create table if not exists
	$pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		email VARCHAR(255) NOT NULL UNIQUE,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY(id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

	// Insert or ignore if already exists
	$stmt = $pdo->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
	$stmt->execute([$email]);

	// Send confirmation email to subscriber
	$mail = getMailer();
	$mail->addAddress($email);
	$mail->Subject = 'Welcome to FitFuel Newsletter';
	$mail->isHTML(true);
	$mail->Body = '<p>Thanks for subscribing to <strong>FitFuel</strong>! You\'ll now receive fitness tips, product updates, and exclusive offers.</p>';
	$mail->AltBody = 'Thanks for subscribing to FitFuel!';
	try { $mail->send(); } catch (Throwable $e) { /* ignore non-critical email failure */ }

	// Notify admin (if configured)
	$adminEmail = getenv('MAIL_ADMIN') ?: null;
	if ($adminEmail) {
		$notify = getMailer();
		$notify->addAddress($adminEmail);
		$notify->Subject = 'New Newsletter Subscriber';
		$notify->isHTML(true);
		$notify->Body = '<p>New subscriber: ' . htmlspecialchars($email, ENT_QUOTES) . '</p>';
		try { $notify->send(); } catch (Throwable $e) { /* ignore */ }
	}

	respond(true, 'Subscribed successfully!');
} catch (Throwable $e) {
	respond(false, 'Subscription failed. Please try again later.');
}


