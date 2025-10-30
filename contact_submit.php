<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/config/email_config.php';

header('Content-Type: application/json');

function reply($ok, $msg){ echo json_encode(['success'=>$ok,'message'=>$msg]); exit; }

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
	reply(false, 'Please provide your name, a valid email, and a message.');
}

try {
	$pdo = getDBConnection();
	$pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		name VARCHAR(150) NOT NULL,
		email VARCHAR(255) NOT NULL,
		phone VARCHAR(50) DEFAULT NULL,
		message TEXT NOT NULL,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

	$stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
	$stmt->execute([$name, $email, $phone, $message]);

	// Notify admin via email
	try {
		$mail = getMailer();
		$admin = 'fitfuelsia@gmail.com'; // Admin email for contact form
		$mail->addAddress($admin);
		$mail->Subject = 'New Contact Message - ' . $name;
		$mail->isHTML(true);
		$mail->Body = '<p><strong>Name:</strong> ' . htmlspecialchars($name, ENT_QUOTES) . '</p>' .
			'<p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES) . '</p>' .
			'<p><strong>Phone:</strong> ' . htmlspecialchars($phone, ENT_QUOTES) . '</p>' .
			'<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message, ENT_QUOTES)) . '</p>';
		$mail->AltBody = "Name: $name\nEmail: $email\nPhone: $phone\nMessage: $message";
		$mail->send();
	} catch (Throwable $e) {
		// ignore email errors
	}

	reply(true, 'Message sent successfully!');
} catch (Throwable $e) {
	reply(false, 'Failed to send message. Please try again later.');
}


