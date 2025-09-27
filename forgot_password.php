<?php 
 

require_once 'config/database.php';
require_once 'config/audit_logger.php';

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
    
    // Store reset token in database
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), created_at = NOW()");
    $stmt->execute([$email, $resetToken, $expiresAt]);
    
    // Log password reset request
    $auditLogger->logPasswordReset($email, false); // false = request, not completion
    
    // Send email with reset link
    $resetLink = "http://localhost/fitfuel_sia/reset_password.php?token=" . $resetToken;
    
    $subject = "FitFuel - Password Reset Request";
    $message = "
    <html>
    <head>
        <title>Password Reset Request</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #059669; margin: 0;'>FitFuel</h1>
            </div>
            
            <h2 style='color: #333; margin-bottom: 20px;'>Password Reset Request</h2>
            
            <p>Hello " . htmlspecialchars($user['username']) . ",</p>
            
            <p>We received a request to reset your password for your FitFuel account. If you made this request, click the button below to reset your password:</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . $resetLink . "' style='background-color: #059669; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Reset Password</a>
            </div>
            
            <p>Or copy and paste this link into your browser:</p>
            <p style='word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 5px;'>" . $resetLink . "</p>
            
            <p><strong>Important:</strong></p>
            <ul>
                <li>This link will expire in 1 hour</li>
                <li>If you didn't request this password reset, please ignore this email</li>
                <li>Your password will remain unchanged until you create a new one</li>
            </ul>
            
            <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
            
            <p style='font-size: 12px; color: #666; text-align: center;'>
                This email was sent from FitFuel. If you have any questions, please contact our support team.
            </p>
        </div>
    </body>
    </html>
    ";
    
    // Log the reset link for debugging (always works)
    error_log("Password reset link for " . $email . ": " . $resetLink);
    
    // Try to send email (may not work in local development)
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: FitFuel <noreply@fitfuel.com>',
        'Reply-To: support@fitfuel.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $mailSent = @mail($email, $subject, $message, implode("\r\n", $headers));
    
    // Always return success since we log the reset link
    echo json_encode(['success' => true, 'message' => 'Password reset link has been sent to your email address. Check your PHP error log for the reset link if email sending is not configured.']);
    
} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?>
