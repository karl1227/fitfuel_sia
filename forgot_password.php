<?php
require_once 'config/database.php';
require_once 'config/audit_logger.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email address is required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit();
}

try {
    $pdo = getDBConnection();
    $auditLogger = new AuditLogger();
    
    // Check if user exists with this email
    $stmt = $pdo->prepare("SELECT user_id, username, email FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // For security, don't reveal if email exists or not
        echo json_encode(['success' => true, 'message' => 'If an account with that email exists, a password reset link has been sent.']);
        exit();
    }
    
    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
    
    // Debug: Log token generation
    error_log("Password reset request - Email: " . $email);
    error_log("Password reset request - Token: " . $resetToken);
    error_log("Password reset request - Expires: " . $expiresAt);
    
    // Store reset token in database
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), created_at = NOW()");
    $stmt->execute([$email, $resetToken, $expiresAt]);
    
    // Log password reset request
    $auditLogger->logPasswordReset($email, false); // false = request, not completion
    
    // Send email with reset link using PHPMailer
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $resetLink = $protocol . '://' . $host . $scriptPath . '/reset_password.php?token=' . $resetToken;
    
    // Debug: Log reset link
    error_log("Password reset request - Reset Link: " . $resetLink);
    
    // Include PHPMailer files
    require_once './PHPMailer/src/Exception.php';
    require_once './PHPMailer/src/PHPMailer.php';
    require_once './PHPMailer/src/SMTP.php';
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'siafitfuel@gmail.com';
        $mail->Password   = 'felclcbkazuspzde';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('siafitfuel@gmail.com', 'FitFuel');
        $mail->addAddress($user['email']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Password Reset Request - FitFuel";
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
        <div style="max-width: 500px; margin: auto; background: #ffffff; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);">
            <h2 style="color: #333;">🔐 Password Reset Request</h2>
            <p style="font-size: 16px; color: #555;">
            Hello ' . htmlspecialchars($user['username']) . ', <br> 
            We received a request to reset your password for your FitFuel account. If you made this request, click the button below to reset your password:
            </p>
            <div style="margin: 30px 0;">
                <a href="' . $resetLink . '" style="background-color: #059669; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">Reset Password</a>
            </div>
            <p style="font-size: 14px; color: #999;">
            Or copy and paste this link into your browser:<br>
            <span style="word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 5px; display: inline-block; margin-top: 10px;">' . $resetLink . '</span>
            </p>
            <div style="text-align: left; margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 5px;">
                <p style="font-size: 14px; color: #666; margin: 0;"><strong>Important:</strong></p>
                <ul style="font-size: 14px; color: #666; margin: 10px 0;">
                    <li>This link will expire in 1 hour</li>
                    <li>If you didn\'t request this password reset, please ignore this email</li>
                    <li>Your password will remain unchanged until you create a new one</li>
                </ul>
            </div>
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
            <p style="font-size: 12px; color: #999;">
            This email was sent from FitFuel. If you have any questions, please contact our support team.
            </p>
        </div>
        </div>';
        
        $mail->send();
        
        // Log the reset link for debugging
        error_log("Password reset link for " . $email . ": " . $resetLink);
        
        echo json_encode(['success' => true, 'message' => 'Password reset link has been sent to your email address.']);
        
    } catch (Exception $e) {
        // Log the reset link for debugging even if email fails
        error_log("Password reset link for " . $email . ": " . $resetLink);
        error_log("Email sending failed: " . $e->getMessage());
        
        echo json_encode(['success' => true, 'message' => 'Password reset link has been sent to your email address. Check your PHP error log for the reset link if email sending failed.']);
    }
    
} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?>
