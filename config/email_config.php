<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

function getMailer(): PHPMailer {
	$mail = new PHPMailer(true);

	// Always use SMTP with Gmail
	$mail->isSMTP();
	$mail->Host = 'smtp.gmail.com';
	$mail->SMTPAuth = true;
	$mail->Username = 'siafitfuel@gmail.com';
	$mail->Password = 'Siafitfuel123!';
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port = 587;

	$mail->setFrom('siafitfuel@gmail.com', 'FitFuel');
	$mail->CharSet = 'UTF-8';

	return $mail;
}


