<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

function getMailer(): PHPMailer {
	$mail = new PHPMailer(true);

	$useSmtp     = getenv('SMTP_ENABLED') === '1';
	$smtpHost    = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
	$smtpPort    = (int)(getenv('SMTP_PORT') ?: 587);
	$smtpUser    = getenv('SMTP_USERNAME') ?: '';
	$smtpPass    = getenv('SMTP_PASSWORD') ?: '';
	$smtpSecure  = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_STARTTLS;
	$fromEmail   = getenv('MAIL_FROM') ?: 'no-reply@fitfuel.local';
	$fromName    = getenv('MAIL_FROM_NAME') ?: 'FitFuel';

	if ($useSmtp && $smtpUser && $smtpPass) {
		$mail->isSMTP();
		$mail->Host       = $smtpHost;
		$mail->SMTPAuth   = true;
		$mail->Username   = $smtpUser;
		$mail->Password   = $smtpPass;
		$mail->SMTPSecure = $smtpSecure;
		$mail->Port       = $smtpPort;
	} else {
		$mail->isMail();
	}

	$mail->setFrom($fromEmail, $fromName);
	$mail->CharSet = 'UTF-8';

	return $mail;
}


